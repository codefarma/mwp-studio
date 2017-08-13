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

use PhpParser\NodeVisitorAbstract;

/**
 * AbstractAnalyzer Class
 */
abstract class AbstractAnalyzer extends NodeVisitorAbstract
{
	/**
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
	 * @var	NodeTraverser
	 */
	protected $traverser;
	
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
	 * Set traverser
	 *
	 * @param	NodeTraverser		$traverser			The traverser this analyzer is attached to
	 * @return	void
	 */
	public function setTraverser( $traverser )
	{
		$this->traverser = $traverser;
	}
	
	/**
	 * Get traverser
	 *
	 * @return	NodeTraverser
	 */
	public function getTraverser()
	{
		return $this->traverser;
	}
	
	/**
	 * Get data
	 *
	 * @return	array
	 */
	public function getData()
	{
		return $this->data;
	}
	 
	/**
	 * Reset data
	 *
	 * @return	void
	 */
	public function resetData()
	{
		$this->data = array();
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
