<?php
/**
 * Plugin Class File
 *
 * Created:   August 13, 2017
 *
 * @package:  MWP Studio
 * @author:   Kevin Carwile
 * @since:    0.0.0
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
	protected $analysis = array();
	
	/**
	 * @var 	\MWP\Framework\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
	 * @var	NodeTraverser
	 */
	protected $traverser;
	
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
	 * Get analysis
	 *
	 * @return	array
	 */
	public function getAnalysis()
	{
		return $this->analysis;
	}
	 
	/**
	 * Reset analysis
	 *
	 * @return	void
	 */
	public function resetAnalysis()
	{
		$this->analysis = array();
	}
	
	/**
	 * Constructor
	 *
	 * @param	\MWP\Framework\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \MWP\Framework\Plugin $plugin=NULL )
	{
		$this->setPlugin( $plugin ?: \MWP\Studio\Plugin::instance() );
	}
}
