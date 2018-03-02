<?php
/**
 * Plugin Class File
 *
 * Created:   August 13, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Studio\Toolset;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;
use MWP\Framework\Framework;

use MWP\Studio\Analyzers\AbstractAnalyzer;
use MWP\Studio\AjaxHandlers;
use MWP\Studio\Plugin;
use MWP\Studio\Tool;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

/**
 * Hook Inspector Tool
 */
class MWPSupport extends Tool
{
	/**
	 * MWP Framework Support JS
	 *
	 * @MWP\WordPress\Script( handle="mwp-studio-mwp-support", deps={"mwp-studio"} )
	 */
	public $javascript;
	
	/**
	 * CSS
	 *
	 * @MWP\WordPress\Stylesheet
	 */
	public $stylesheet;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->javascript = $this->relativePath( 'assets/tool.js' );
		$this->stylesheet = $this->relativePath( 'assets/tool.css' ); 
	}
	
	/**
	 * Enqueue scripts and stylesheets
	 * 
	 * @MWP\WordPress\Action( for="admin_enqueue_scripts" )
	 *
	 * @return	void
	 */
	public function enqueueScripts()
	{
		if ( $this->getPlugin()->loadStudioUI() ) 
		{
			$this->getPlugin()->useScript( $this->javascript );
			$this->getPlugin()->useStyle( $this->stylesheet );
		}
	}
	
	/**
	 * Add the toolbox component to the studio
	 *
	 * @MWP\WordPress\Filter( for="mwp_studio_toolbox_components" )
	 *
	 * @param	array		$components				Toolbox components
	 * @return	array
	 */
	public function getToolboxComponents( $components )
	{
		return $components;
	}
	
	/**
	 * Add parameters to the studio javascript local object
	 *
	 * @MWP\WordPress\Filter( for="studio_controller_params" )
	 *
	 * @param	array		$params				The studio javascript localized parameters
	 * @return	array
	 */
	public function addStudioParams( $params )
	{
		$params['templates']['dialogs']['mwp-create-class']      = $this->getToolTemplate( 'dialogs/create-class' );
		$params['templates']['dialogs']['mwp-create-javascript'] = $this->getToolTemplate( 'dialogs/create-javascript' );
		$params['templates']['dialogs']['mwp-create-stylesheet'] = $this->getToolTemplate( 'dialogs/create-stylesheet' );
		$params['templates']['dialogs']['mwp-create-template']   = $this->getToolTemplate( 'dialogs/create-template' );
		$params['templates']['dialogs']['mwp-build-plugin']  = $this->getToolTemplate( 'dialogs/build-plugin' );
		$params['templates']['extras']['mwp']['create-project-vendor']  = $this->getToolTemplate( 'extras/create-project-vendor' );
		
		return $params;
	}
	
	/**
	 * Add hooks analyzer to code agent
	 *
	 * @MWP\WordPress\Filter( for="mwp_studio_code_analyzers" )
	 *
	 * @param	array			$analyzers				Existing analyzers
	 * @return	array
	 */
	public function addCodeAnalyzer( $analyzers )
	{
		$analyzers[] = new MWPCodeAnalyzer;
		return $analyzers;
	}
	
	/**
	 * Customize the environment for modern wordpress plugins
	 *
	 * @MWP\WordPress\Filter( for="mwp_studio_plugin_info", args=4 )
	 *
	 * @param	array			$info				The plugin info
	 * @param	string			$file				The plugin file
	 * @param	array			$core_data			The core data about the plugin
	 * @param	string			$basedir			The base directory of the plugin
	 * @return	array
	 */
	public function addPluginInfo( $info, $file, $core_data, $basedir )	
	{
		if ( file_exists( ABSPATH . $basedir  . '/data/plugin-meta.php' ) )
		{
			$plugin_data = json_decode( include( ABSPATH . $basedir  . '/data/plugin-meta.php' ), true );
			if ( is_array( $plugin_data ) ) {
				$info = array_merge( $plugin_data, $info );
				$info[ 'environment' ] = 'mwp';
			}
		}
		
		return $info;
	}
	
	/**
	 * Create a new project
	 *
	 * @MWP\WordPress\Filter( for="mwp_studio_create_project", args=2 )
	 *
	 * @param	array|null			$project 				Project Details
	 * @param	array				$options				Creation options
	 * @return	array
	 */
	public function createProject( $project, $options )
	{
		if ( $options['type'] == 'plugin' and $options['pluginFramework'] == 'mwp' ) 
		{
			$class_file = Framework::instance()->createPlugin( $options );
			$project = Plugin::instance()->getPluginInfo( WP_PLUGIN_DIR . '/' . $options['slug'] . '/plugin.php' );
		}
	
		return $project;
	}
}

/**
 * Hooks Code Analyzer 
 */
class MWPCodeAnalyzer extends AbstractAnalyzer
{
	/**
	 * Entering a node
	 *
	 * @return	void
	 */
    public function enterNode( Node $node ) 
	{
		/**
		 * Modern Wordpress Hooks & Filters
		 */
		if ( $node instanceof Node\Stmt\ClassMethod	) 
		{
			if ( $docBlock = $node->getDocComment() ) 
			{
				if ( preg_match_all( '/@MWP\WordPress\\\(Action|Filter)\((.*)for="(.+)"(.*)\)/sU', $docBlock->getText(), $matches ) ) 
				{
					foreach( $matches[0] as $i => $match ) 
					{
						$hook_priority = 10;
						$hook_args = 1;
						
						if ( preg_match( '/priority=(\d+)/', $match, $m ) ) {
							$hook_priority = $m[1];
						}
						
						if ( preg_match( '/args=(\d+)/', $match, $m ) ) {
							$hook_args = $m[1];
						}
						
						$this->analysis['hooks'][] = array_merge
						(
							$this->getTraverser()->getCurrentFileInfo(),
							array(
								'type' => 'add_' . strtolower($matches[1][$i]),
								'name' => $matches[3][$i],
								'callback_name' => $node->name,
								'callback_class' => $this->getTraverser()->getCurrentClassname(),
								'callback_type' => 'method',
								'line' => $docBlock->getLine(),
								'data' => array( 'register_type' => 'annotation', 'annotation' => $match ),
								'args' => $hook_args,
								'priority' => $hook_priority,
								'catalog_time' => time(),
							)
						);
					}
				}
			}
		}
	}
}

Framework::instance()->attach( new MWPSupport() );