<?php
/**
 * Plugin Class File
 *
 * Created:   July 28, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Studio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Framework;
use Modern\Wordpress\Pattern\Singleton;

/**
 * AjaxHandlers Class
 */
class AjaxHandlers extends Singleton
{
	protected static $_instance;

	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
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
	}
	
	/**
	 * Authorize access
	 *
	 * @return	void
	 */
	public function authorize()
	{
		if ( get_current_user_id() !== 1 )
		{
			exit('unauthorized');
		}
	}
	
	/**
	 * Load available studio plugins
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_load_plugins", for={"users"} )
	 *
	 * @return	void
	 */
	public function loadStudioPlugins()
	{
		$this->authorize();
		
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$studio = \MWP\Studio\Plugin::instance();
		$plugins = array();
		
		foreach( get_plugins() as $file => $data )
		{			
			$plugins[] = $studio->getPluginInfo( WP_PLUGIN_DIR . '/' . $file );
		}
		
		wp_send_json( array( 'plugins' => $plugins ) );
	}

	/**
	 * Fetch the files contained within a plugin
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_fetch_filetree", for={"users"} )
	 *
	 * @return	void
	 */
	public function fetchFiletree()
	{
		$this->authorize();
		
		$basedir = WP_PLUGIN_DIR . str_replace( '../', '', $_REQUEST['plugin'] );
		
		if ( ! is_dir( $basedir ) ) {
			wp_send_json( array( 'success' => false ) );
		}
		
		wp_send_json( $this->getPlugin()->getFileNodeInfo( $basedir ) );
	}
	
	/**
	 * Get the content of a file 
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_get_file_content", for={"users"} )
	 *
	 * @return	void
	 */
	public function getFileContent()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['path'] );
		$file = WP_PLUGIN_DIR . '/' . $file_path;
		
		if ( file_exists( $file ) and is_file( $file ) )
		{
			wp_send_json( array( 'success' => true, 'file' => $file, 'content' => file_get_contents( $file ), 'modified' => filemtime( $file ) ) );
		}
		else
		{
			wp_send_json( array( 'success' => false, 'content' => '' ) );
		}
	}
	
	/**
	 * Synchronize the modification state of a file
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_sync_file", for={"users"} )
	 *
	 * @return	void
	 */
	public function synchronizeFile()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['path'] );
		$file = WP_PLUGIN_DIR . '/' . $file_path;
		
		if ( file_exists( $file ) and is_file( $file ) )
		{
			wp_send_json( array( 'success' => true, 'modified' => filemtime( $file ) ) );
		}
		else
		{
			wp_send_json( array( 'success' => false ) );
		}
	}
	
	/**
	 * Save the content of a file
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_save_file_content", for={"users"} )
	 *
	 * @return	void
	 */
	public function saveFileContent()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['path'] );
		$file = WP_PLUGIN_DIR . '/' . $file_path;
		
		file_put_contents( $file, wp_unslash( $_REQUEST['content'] ) );

		wp_send_json( array( 'success' => true, 'modified' => filemtime( $file ) ) );
	}

	/**
	 * Create a new php class
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_create_plugin", for={"users"} )
	 *
	 * @return	void
	 */
	public function createPlugin()
	{
		$this->authorize();
		
		$framework = \Modern\Wordpress\Framework::instance();
		$studio = \MWP\Studio\Plugin::instance();
		$options = wp_unslash( $_REQUEST['options'] );
		
		try 
		{
			$class_file = $framework->createPlugin( $options );
			wp_send_json( array( 'success' => true, 'plugin' => $studio->getPluginInfo( WP_PLUGIN_DIR . '/' . $options['slug'] . '/plugin.php' ) ) );
		}
		catch( \Exception $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}
	
	/**
	 * Create a new php class
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_add_class", for={"users"} )
	 *
	 * @return	void
	 */
	public function createPHPClass()
	{
		$this->authorize();
		
		$framework = \Modern\Wordpress\Framework::instance();
		$classname = wp_unslash( $_REQUEST['classname'] );
		$plugin = wp_unslash( $_REQUEST['plugin'] );
		
		try 
		{
			$class_file = $framework->createClass( $plugin, $classname );
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $class_file ) ) );
		}
		catch( \ErrorException $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}
	
	/**
	 * Create a new php template
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_add_template", for={"users"} )
	 *
	 * @return	void
	 */
	public function createPHPTemplate()
	{
		$this->authorize();
		
		$framework = \Modern\Wordpress\Framework::instance();
		$template = wp_unslash( $_REQUEST['template'] );
		$plugin = wp_unslash( $_REQUEST['plugin'] );
		
		try 
		{
			$template_file = $framework->createTemplate( $plugin, $template );
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $template_file ) ) );
		}
		catch( \ErrorException $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}	
	
	/**
	 * Create a new css file
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_add_css", for={"users"} )
	 *
	 * @return	void
	 */
	public function createCSS()
	{
		$this->authorize();
		
		$framework = \Modern\Wordpress\Framework::instance();
		$filename = wp_unslash( $_REQUEST['filename'] );
		$plugin = wp_unslash( $_REQUEST['plugin'] );
		
		try 
		{
			$css_file = $framework->createStylesheet( $plugin, $filename );
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $css_file ) ) );
		}
		catch( \ErrorException $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}

	/**
	 * Create a new javascript file
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_add_js", for={"users"} )
	 *
	 * @return	void
	 */
	public function createJS()
	{
		$this->authorize();
		
		$framework = \Modern\Wordpress\Framework::instance();
		$filename = wp_unslash( $_REQUEST['filename'] );
		$plugin = wp_unslash( $_REQUEST['plugin'] );
		
		try 
		{
			$javascript_file = $framework->createJavascript( $plugin, $filename );
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $javascript_file ) ) );
		}
		catch( \ErrorException $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}	
}
