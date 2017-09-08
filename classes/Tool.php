<?php
/**
 * Plugin Class File
 *
 * Created:   September 8, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Studio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Tool Class
 */
class Tool
{
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
	 * @var		string
	 */
	protected $toolsetDir;
	
	/**
	 * Get toolset
	 *
	 * @return	string
	 */
	public function getToolsetDir()
	{
		return $this->toolsetDir;
	}
	
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
		$reflection = new \ReflectionClass( get_called_class() );
		$this->toolsetDir = array_slice( explode( '/', str_replace( '\\', '/', $reflection->getFileName() ) ), -2 , 1 )[0];		
		$this->setPlugin( $plugin ?: \MWP\Studio\Plugin::instance() );
	}
	
	/**
	 * Get the content of a template
	 *
	 * @api
	 *
	 * @param	string		$template 			Tool template to load (without file extension)
	 * @param	array		$vars				Variables to extract and make available to template
	 * @return	string
	 */
	public function getToolTemplate( $template, $vars=array() )
	{
		$templateFile = $this->getPlugin()->pluginFile( 'toolsets/' . $this->getToolsetDir() . '/templates/' . $template, 'php' );
		
		if ( is_array( $vars ) )
		{
			unset( $vars[ 'templateFile' ] );
			extract( $vars, EXTR_SKIP );
		}

		if ( file_exists( $templateFile ) ) {
			ob_start();
			include $templateFile;
			return ob_get_clean();
		}
		
		return '[Template could not be found: toolsets/' . $this->getToolsetDir() . '/templates/' . $template . ']';
	}
	
	/**
	 * Get toolsets
	 *
	 * @Wordpress\Filter( for="mwp_studio_get_toolsets" )
	 *
	 * @param	array		$toolsets				Array of toolsets
	 * @return	array
	 */
	public function _getToolsets( $toolsets )
	{
		$toolsets[ $this->getToolset() ] = $this;
		return $toolsets;
	}
	
}
