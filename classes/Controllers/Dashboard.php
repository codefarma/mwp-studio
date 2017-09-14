<?php
/**
 * Plugin Class File
 *
 * Created:   May 1, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Studio\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Pattern\Singleton;

/**
 * Main Studio Controller
 *
 * @Wordpress\AdminPage( type="dashboard", menu="Plugin Studio", title="Wordpress Plugin Studio", slug="mwp-studio-dashboard" )
 */
class Dashboard extends Singleton
{
	/**
	 * @var 	self
	 */
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
		$this->plugin = $plugin ?: \MWP\Studio\Plugin::instance();
	}
	
	/**
	 * Controller index
	 *
	 * @return	void
	 */
	public function do_index()
	{
		$plugin = $this->getPlugin();
		$plugin->output( $plugin->getTemplateContent( 'views/controllers/dashboard' ) );
	}
}
