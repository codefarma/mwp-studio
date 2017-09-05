<?php
/**
 * Plugin Class File
 *
 * Created:   August 16, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Studio\Models;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Pattern\ActiveRecord;

/**
 * File Class
 */
class File extends ActiveRecord
{
	/**
	 * @var	array		Multitons cache (needs to be defined in subclasses also)
	 */
	protected static $multitons = array();
	
	/**
	 * @var	string		Table name
	 */
	public static $table = 'studio_file_catalog';
	
	/**
	 * @var	array		Table columns
	 */
	public static $columns = array(
	    'id',
	    'file',
	    'type',
	    'data' => array( 'format' => 'JSON' ),
	    'last_analyzed',
	);
	
	/**
	 * @var	string		Table primary key
	 */
	public static $key = 'id';
	
	/**
	 * @var	string		Table column prefix
	 */
	public static $prefix = 'file_';
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
	 * @var	array
	 */
	protected static $pathcache = array();
	
	/**
	 * Load/cache a file by its path
	 *
	 * @param	string		$filepath			The file path to load
	 * @return	File
	 */
	public static function loadByPath( $filepath )
	{
		if ( isset( static::$pathcache[ $filepath ] ) ) {
			return static::$pathcache[ $filepath ];
		}
		
		$files = static::loadWhere( array( 'file_file=%s', $filepath ) );
		
		if ( count( $files ) ) {
			static::$pathcache[ $filepath ] = $files[0];
			return $files[0];
		}
	}
	
	/**
	 * @var	string
	 */
	protected $location;
	
	/**
	 * Get the location of this file
	 *
	 * @return	string
	 */
	public function getLocation()
	{
		if ( isset( $this->location ) ) {
			return $this->location;
		}
		
		$plugins_basepath = str_replace( ABSPATH, '', WP_PLUGIN_DIR );
		$themes_basepath = str_replace( ABSPATH, '', get_theme_root() ); 
		
		if ( substr( $this->file, 0, strlen( $plugins_basepath ) ) === $plugins_basepath ) 
		{
			$this->location = 'plugin';
		}
		else if ( substr( $this->file, 0, strlen( $themes_basepath ) ) === $themes_basepath )
		{
			$this->location = 'theme';
		}
		else
		{
			$this->location = 'core';
		}
		
		return $this->location;
	}
	
	/**
	 * @var	string
	 */
	protected $location_slug;
	
	/**
	 * Get the slug for the location of this file
	 *
	 * @return	string
	 */
	public function getLocationSlug()
	{
		if ( isset( $this->location_slug ) ) {
			return $this->location_slug;
		}
	
		$this->location_slug = '';
		
		if ( $this->getLocation() == 'plugin' ) {
			$parts = explode( '/', ltrim( str_replace( str_replace( ABSPATH, '', WP_PLUGIN_DIR ), '', $this->file ), '/' ) );
			$this->location_slug = array_shift( $parts );
		}
		
		if ( $this->getLocation() == 'theme' ) {
			$parts = explode( '/', ltrim( str_replace( str_replace( ABSPATH, '', get_theme_root() ), '', $this->file ), '/' ) );
			$this->location_slug = array_shift( $parts );
		}
		
		return $this->location_slug;
	}
	 
}
