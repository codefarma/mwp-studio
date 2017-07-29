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
	 * Load available studio plugins
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_load_plugins", for={"users"} )
	 *
	 * @return	void
	 */
	public function loadStudioPlugins()
	{
		$_plugins = get_plugins();
		$plugins = array();
		
		foreach( $_plugins as $file => $data )
		{
			$slug = str_replace( "/" . basename( $file ), "", $file );
			$_data = array( 'slug' => $slug );
			foreach( $data as $key => $value ) {
				$_data[strtolower($key)] = $value;
			}
			
			$plugins[] = $_data;
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
		$basedir = WP_PLUGIN_DIR . '/' . basename( $_REQUEST['plugin'] );
		
		if ( ! is_dir( $basedir ) ) {
			wp_send_json( array( 'success' => false ) );
		}
		
		$read_directory = function( $basedir ) use ( &$read_directory )
		{
			$nodes = array();
			
			foreach( scandir( $basedir ) as $file ) 
			{
				if ( in_array( $file, array( '.', '..' ) ) ) {
					continue;
				}
				
				if ( is_dir( $basedir . '/' . $file ) ) 
				{
					$nodes[] = array( 
						'type' => 'dir',
						'text' => $file,
						'icon' => 'fa fa-folder',
						'selectable' => false,
						'state' => array(
							'expanded' => false,
						),
						'nodes' => $read_directory( $basedir . '/' . $file )
					);
				}
				else
				{
					$parts = explode( '.', $file );
					$ext = array_pop( $parts );
					$icon = 'fa fa-file-o';
					$selectable = false;
					
					if ( in_array( $ext, array( 'php', 'css', 'js', 'html', 'xml' ) ) ) {
						$icon = 'fa fa-file-code-o';
						$selectable = true;
					}
					
					if ( in_array( $ext, array( 'txt', 'md' ) ) ) {
						$icon = 'fa fa-file-text-o';
						$selectable = true;
					}
					
					if ( in_array( $ext, array( 'zip', 'tar', 'phar' ) ) ) {
						$icon = 'fa fa-file-archive-o';
					}
					
					if ( in_array( $ext, array( 'png', 'jpg', 'jpeg', 'bmp' ) ) ) {
						$icon = 'fa fa-file-image-o';
					}
					
					if ( in_array( $ext, array( 'pdf' ) ) ) {
						$icon = 'fa fa-file-pdf-o';
					}
					
					if ( in_array( $ext, array( 'mp4', 'mpeg', 'mpg', 'flv' ) ) ) {
						$icon = 'fa fa-file-video-o';
					}
					
					if ( in_array( $ext, array( 'mp3', 'wav' ) ) ) {
						$icon = 'fa fa-file-audio-o';
					}
					
					$nodes[] = array(
						'type' => 'file',
						'icon' => $icon,
						'selectable' => $selectable,
						'text' => $file,
					);
				}
			}
			
			return $nodes;
		};
		
		wp_send_json( array( 'nodes' => $read_directory( $basedir ) ) );
	}
	
}
