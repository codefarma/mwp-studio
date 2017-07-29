<?php
/**
 * Plugin Class File
 *
 * @vendor: Kevin Carwile
 * @package: Wordpress Plugin Studio
 * @author: Kevin Carwile
 * @link: 
 * @since: May 1, 2017
 */
namespace MWP\Studio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Plugin Class
 */
class Plugin extends \Modern\Wordpress\Plugin
{
	/**
	 * Instance Cache - Required
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var string		Plugin Name
	 */
	public $name = 'Wordpress Plugin Studio';
	
	/**
	 * Main Stylesheet
	 * @Wordpress\Stylesheet(deps={"mwp-bootstrap"})
	 */
	public $mainStyle = 'assets/css/style.css';
	
	/**
	 * Main Javascript Controller
	 * @Wordpress\Script( deps={"mwp", "mwp-bootstrap", "knockback"} )
	 */
	public $mainScript = 'assets/js/main.js';
	
	/**
	 * Bootflat JS
	 * @Wordpress\Script(deps={"mwp-bootstrap"})
	 */
	public $bootflatJS1 = 'assets/bootflat/js/icheck.min.js';
	
	/**
	 * Bootflat JS
	 * @Wordpress\Script(deps={"mwp-bootstrap"})
	 */
	public $bootflatJS2 = 'assets/bootflat/js/jquery.fs.selecter.min.js';
	
	/**
	 * Bootflat JS
	 * @Wordpress\Script(deps={"mwp-bootstrap"})
	 */
	public $bootflatJS3 = 'assets/bootflat/js/jquery.fs.stepper.min.js';
	
	/**
	 * Fontawesome CSS
	 * @Wordpress\Stylesheet
	 */
	public $fontawesome = 'assets/fontawesome/css/font-awesome.min.css';
	
	/**
	 * Bootflat CSS
	 * @Wordpress\Stylesheet(deps={"mwp-bootstrap"})
	 */
	public $bootflatCSS = 'assets/bootflat/css/bootflat.min.css';
	
	/**
	 * Bootstrap Treeview CSS
	 * @Wordpress\Stylesheet(deps={"mwp-bootstrap"})
	 */
	public $bootstrapTreeviewCSS = 'assets/css/bootstrap-treeview.min.css';
	
	/**
	 * Bootstrap Treeview JS
	 * @Wordpress\Script(deps={"mwp-bootstrap"})
	 */
	public $bootstrapTreeviewJS = 'assets/js/bootstrap-treeview.min.js';
	
	/**
	 * Enqueue scripts and stylesheets
	 * 
	 * @Wordpress\Action( for="admin_enqueue_scripts" )
	 *
	 * @return	void
	 */
	public function enqueueScripts()
	{
		// Bootflat UI
		$this->useStyle( $this->bootflatCSS );
		$this->useScript( $this->bootflatJS1 );
		$this->useScript( $this->bootflatJS2 );
		$this->useScript( $this->bootflatJS3 );
		
		// Bootstrap Treeview
		$this->useStyle( $this->bootstrapTreeviewCSS );
		$this->useScript( $this->bootstrapTreeviewJS );
		
		$this->useStyle( $this->fontawesome );
		
		// Studio
		$this->useStyle( $this->mainStyle );
		$this->useScript( $this->mainScript );
	}
	
	/**
	 * Output admin page
	 *
	 * @return	void
	 */
	public function output( $content )
	{
		echo $this->getTemplateContent( 'views/global/layout', array(
			'content' => $content,
		));
	}
}