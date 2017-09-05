<?php
/**
 * Plugin Class File
 *
 * Created:   August 14, 2017
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
 * Hook Class
 */
class Hook extends ActiveRecord
{
	/**
	 * @var	array		Multitons cache (needs to be defined in subclasses also)
	 */
	protected static $multitons = array();
	
	/**
	 * @var	string		Table name
	 */
	public static $table = 'studio_hook_catalog';
	
	/**
	 * @var	array		Table columns
	 */
	public static $columns = array(
	    'id',
	    'name',
	    'type',
	    'callback_name',
	    'callback_class',
	    'callback_type',
	    'data' => array( 'format' => 'JSON' ),
	    'file',
	    'line',
	    'args',
	    'priority',
	    'catalog_time',
	);
	
	/**
	 * @var	string		Table primary key
	 */
	public static $key = 'id';
	
	/**
	 * @var	string		Table column prefix
	 */
	public static $prefix = 'hook_';
	
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
	 * @var		Callback function
	 */
	protected $callback;
	
	/**
	 * Get the callback function
	 *
	 * @return	Function_
	 */
	public function getCallback()
	{
		if ( ! $this->callback_name and ! $this->callback_class ) {
			return false;
		}
		
		if ( isset( $this->callback ) ) {
			return $this->callback;
		}
		
		$where = $this->callback_class ? array( 'function_name=%s AND function_class=%s', $this->callback_name, $this->callback_class ) : array( 'function_name=%s AND function_class IS NULL', $this->callback_name );
		$functions = \MWP\Studio\Models\Function_::loadWhere( $where );
		
		if ( count( $functions ) ) {
			$this->callback = $functions[0];
		}
		else
		{
			$this->callback = false;
		}
		
		return $this->callback;
	}
	
	/**
	 * Get the file record for this hook
	 *
	 * @return	File
	 */
	public function getFile()
	{
		return \MWP\Studio\Models\File::loadByPath( $this->file );
	}
	
	/**
	 * Get the content to display in the hook popover
	 *
	 * @return	array
	 */
	public function getStudioModel()
	{
		$data = $this->dataArray();
		$data['hook_data'] = $this->data;
		
		return apply_filters( 'studio_model_hook', $data, $this );
	}
	
}
