<?php
/**
 * Plugin Class File
 *
 * Created:   August 13, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Studio\Analyzers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use PhpParser\NodeTraverser;

/**
 * Traverser Class
 */
class Traverser extends NodeTraverser
{
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
	 * @var	array
	 */
	protected $currentFileInfo = array();
	
	/**
	 * @var	array
	 */
	protected $currentNamespace = array();
	
	/**
	 * @var	string
	 */
	protected $currentClassname = '';
	
	/**
	 * @var	array
	 */
	protected $currentAliases = array();
	
	/**
	 * @var	array
	 */
	protected $currentClassUses = array();
	
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
	 * Set current file info
	 *
	 * @param	array			$info			Current file info
	 * @return	void
	 */
	public function setCurrentFileInfo( $info )
	{
		$this->currentFileInfo = $info;
		$this->currentAliases = array();
	}
	
	/**
	 * Get file info
	 *
	 * @return	array
	 */
	public function getCurrentFileInfo()
	{
		return $this->currentFileInfo;
	}
	
	/**
	 * Set uses
	 *
	 * @param	string		$classname		The fully qualified classname
	 * @return	void
	 */
	public function addClassUses( $classname )
	{
		$this->currentClassUses[] = $classname;
	}
	
	/**
	 * Get uses
	 *
	 * @return	array
	 */
	public function getClassUses()
	{
		return $this->currentClassUses;
	}
	
	/**
	 * Set current classname
	 *
	 * @param	string			$classname		Current classname
	 * @return	void
	 */
	public function setCurrentClassname( $classname )
	{
		$this->currentClassname = $classname;
		
		if ( empty( $classname ) ) {
			$this->currentClassUses = array();
		}
	}
	
	/**
	 * Get current classname
	 *
	 * @param	bool			$namespace			Include the current namespace
	 * @return	string
	 */
	public function getCurrentClassname( $namespace=true )
	{
		return $namespace ? implode( '\\', array_filter( array_merge( $this->getCurrentNamespace(), array( $this->currentClassname ) ) ) ) : $this->currentClassname;
	}
		
	/**
	 * Set current namespace
	 *
	 * @param	string			$namespace		Current namespace
	 * @return	void
	 */
	public function setCurrentNamespace( $namespace )
	{
		$this->currentNamespace = $namespace;
	}
	
	/**
	 * Get current namespace
	 *
	 * @return	string
	 */
	public function getCurrentNamespace()
	{
		return $this->currentNamespace;
	}	
}
