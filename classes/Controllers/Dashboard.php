<?php
/**
 * Plugin Class File
 *
 * Created:   May 1, 2017
 *
 * @package:  MWP Studio
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Studio\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\Singleton;

/**
 * Main Studio Controller
 *
 * @MWP\WordPress\AdminPage( type="dashboard", menu="MWP Studio", title="MWP Studio", slug="mwp-studio-dashboard" )
 */
class Dashboard extends Singleton
{
	/**
	 * @var 	self
	 */
	protected static $_instance; 
	 
	/**
	 * @var 	\MWP\Framework\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
 	 * Get plugin
	 *
	 * @return	\MWP\Framework\Plugin
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
	public function setPlugin( \MWP\Framework\Plugin $plugin=NULL )
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\MWP\Framework\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \MWP\Framework\Plugin $plugin=NULL )
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
