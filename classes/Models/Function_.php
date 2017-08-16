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

/**
 * Function_ Class
 */
class Function_
{
	/**
	 * @var	array		Multitons cache (needs to be defined in subclasses also)
	 */
	protected static $multitons = array();
	
	/**
	 * @var	string		Table name
	 */
	public static $table = 'studio_function_catalog';
	
	/**
	 * @var	array		Table columns
	 */
	public static $columns = array(
	    'id',
	    'name',
	    'class',
	    'type',
	    'args',
	    'data' => array( 'format' => 'JSON' ),
	    'file',
	    'line',
	    'catalog_time',
	);
	
	/**
	 * @var	string		Table primary key
	 */
	public static $key = 'id';
	
	/**
	 * @var	string		Table column prefix
	 */
	public static $prefix = 'function_';
	
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
}
