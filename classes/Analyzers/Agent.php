<?php
/**
 * Plugin Class File
 *
 * Created:   August 13, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Studio\Analyzers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Pattern\Singleton;
use PhpParser\ParserFactory;
use PhpParser\Node;

/**
 * Plugin Class
 */
class Agent extends Singleton
{
	/**
	 * @var	instance
	 */
	protected static $_instance;

	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
	 * @var	NodeTraverser
	 */
	protected $traverser;
	
	/**
	 * @var	Parser
	 */
	protected $parser;
	
	/**
	 * @var	array
	 */
	protected $analyzers = array();
	
	/**
 	 * Get plugin
	 *
	 * @return	\Modern\Wordpress\Plugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	 * Set plugin
	 *
	 * @return	this			Chainable
	 */
	public function setPlugin( \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\Modern\Wordpress\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->setPlugin( $plugin ?: \MWP\Studio\Plugin::instance() );
		
		$this->parser    = (new ParserFactory)->create( ParserFactory::PREFER_PHP7 );
		$this->traverser = new Traverser;	
		$this->analyzers = array_merge( array( new BaseAnalyzer ), apply_filters( 'mwp_studio_code_analyzers', array( new WpCodeAnalyzer ) ) );
		
		
		foreach( $this->analyzers as $analyzer ) {
			$analyzer->setTraverser( $this->traverser );
			$this->traverser->addVisitor( $analyzer );
		}
	}
	
	/**
	 * Reset analysis
	 *
	 * @return	void
	 */
	public function resetAnalysis()
	{
		foreach( $this->analyzers as $analyzer ) {
			$analyzer->resetData();
		}		
	}
	
	/**
	 * Get analysis
	 *
	 * @return	array
	 */
	public function getAnalysis()
	{
		$data = array();
		foreach( $this->analyzers as $analyzer ) {
			$data = array_merge_recursive( $data, $analyzer->getAnalysis() );
		}
		
		return $data;	
	}
	
	/**
	 * Run analysis on a plugin
	 *
	 * @param	string		$plugin_slug		The slug of the plugin to analyze
	 * @return	void
	 */
	public function analyzePlugin( $plugin_slug )
	{		
		$pluginpath = WP_PLUGIN_DIR . '/' . $plugin_slug;
		$this->analyzeDirectory( $pluginpath, true, apply_filters( 'mwp_studio_plugin_analyzer_skips', array( 'all' => array( '.git', '.svn' ) ), $plugin_slug ) );	
	}
	
	/**
	 * Run analyzers on php files in a directory
	 * 
	 * @param	string			$fullpath 			The full path to the directory to analyze
	 * @param	bool			$recursive			If subdirectories should also be analyzed
	 * @param	array			$skip				Filenames or directories to skip
	 * @param	int				$depth				The depth of the analysis recursion
	 * @return void
	 */
	public function analyzeDirectory( $fullpath, $recursive=TRUE, $skip=array(), $depth=0 )
	{
		if ( ! is_dir( $fullpath ) ) {
			return array();
		}
		
		$relative_path = str_replace( WP_PLUGIN_DIR, '', $fullpath );
		
		foreach( scandir( $fullpath ) as $file ) 
		{
			$all_skips = isset( $skip['any'] ) ? $skip['any'] : array();
			$level_skips = isset( $skip[ $depth ] ) ? $skip[ $depth ] : array();
			
			if ( in_array( $file, array_merge( array( '.', '..' ), $all_skips, $level_skips ) ) ) {
				continue;
			}
			
			if ( is_dir( $fullpath . '/' . $file ) )
			{
				if ( $recursive ) {
					$this->analyzeDirectory( $fullpath . '/' . $file, $recursive, $skip, $depth + 1 );
				}
			}
			else
			{
				$parts = explode( '.', $file );
				$ext = array_pop( $parts );
				if ( $ext == 'php' ) {
					$this->analyzeFile( $fullpath . '/' . $file );
				}
			}
		}

	}
	
	/**
	 * Analyze a file
	 *
	 * @param	string		$filepath			The php file to analyze
	 * @return	void
	 */
	public function analyzeFile( $filepath )
	{
		$this->traverser->setCurrentFileInfo( array( 'file' => str_replace( ABSPATH, '', $filepath ) ) );
		
		try {
			$this->traverser->traverse( $this->parser->parse( file_get_contents( $filepath ) ) );
		}
		catch( \PhpParser\Error $e ) { 
		
		}
	}
	
}
