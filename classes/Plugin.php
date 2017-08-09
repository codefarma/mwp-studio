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
	 * @Wordpress\Script( handle="mwp-studio-models", deps={"mwp", "mwp-bootstrap", "knockback"} )
	 */
	public $studioModels = 'assets/js/studio.models.js';

	/**
	 * Main Javascript Controller
	 * @Wordpress\Script( handle="mwp-studio-interfaces", deps={"mwp-studio-models"} )
	 */
	public $studioInterfaces = 'assets/js/studio.interfaces.js';

	/**
	 * Main Javascript Controller
	 * @Wordpress\Script( handle="mwp-studio-controller", deps={"mwp-studio-interfaces"} )
	 */
	public $studioController = 'assets/js/studio.controller.js';
	
	/**
	 * Ace code editor
	 * @Wordpress\Script
	 */
	public $aceEditor = 'assets/ace/src-min-noconflict/ace.js';
	
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
	 * Bootstrap Context Menu JS
	 * @Wordpress\Script(deps={"mwp-bootstrap"})
	 */
	public $bootstrapContextmenuJS = 'assets/js/bootstrap-contextmenu.min.js';
	
	/**
	 * Bootbox JS
	 * @Wordpress\Script(deps={"mwp-bootstrap"})
	 */
	public $bootboxJS = 'assets/js/bootstrap-bootbox.min.js';
	
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
		
		// Bootstrap context menu
		$this->useScript( $this->bootstrapContextmenuJS );
		
		// Bootbox (Modal Dialogs)
		$this->useScript( $this->bootboxJS );
		
		// Font Awesome
		$this->useStyle( $this->fontawesome );
		
		// Ace Editor
		$this->useScript( $this->aceEditor );
		
		// Studio
		$this->useStyle( $this->mainStyle );
		$this->useScript( $this->studioModels );
		$this->useScript( $this->studioInterfaces );
		$this->useScript( $this->studioController, array( 'heartbeat_interval' => 10000 ) );
	}
	
	/**
	 * Get plugin info
	 *
	 * @param	string		$slug			The plugin slug
	 * @return	array
	 */
	public function getPluginInfo( $file )
	{
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		$core_data = get_plugin_data( $file );
		$composite_data = array();		
		$basedir = str_replace( WP_PLUGIN_DIR, '', str_replace( "/" . basename( $file ), "", $file ) );
		$composite_data[ 'pluginfile' ] = $file;
		$composite_data[ 'basedir' ] = $basedir;
		$composite_data[ 'framework' ] = '';
		
		foreach( $core_data as $key => $value )	{
			$composite_data[ strtolower( $key ) ] = $value;		
		}
		
		if ( file_exists( WP_PLUGIN_DIR . $basedir  . '/data/plugin-meta.php' ) )
		{
			$plugin_data = json_decode( include( WP_PLUGIN_DIR . $basedir  . '/data/plugin-meta.php' ), true );
			if ( is_array( $plugin_data ) ) {
				$composite_data[ 'framework' ] = 'mwp';
				$composite_data = array_merge( $plugin_data, $composite_data );
			}
		}
		
		$composite_data[ 'id' ] = md5( $basedir );
		
		return $composite_data;		
	}
	
	/**
	 * Get the file node info
	 *
	 * @param	string		$fullpath 				File or directory
	 * @return	array()
	 */
	public function getFileNodeInfo( $fullpath )
	{
		$file = basename( $fullpath );
		$relative_path = str_replace( WP_PLUGIN_DIR, '', $fullpath );
		$parent_path = str_replace( '/' . $file, '', $relative_path );
		
		if ( is_file( $fullpath ) )
		{
			$parts = explode( '.', $file );
			$ext = array_pop( $parts );
			$icon = 'fa fa-file-o';
			$selectable = false;
			$mode = null;
			
			if ( in_array( $ext, array( 'php', 'css', 'js', 'html', 'xml' ) ) ) {
				$icon = 'fa fa-file-code-o';
				$selectable = true;
				switch( $ext ) {
					case 'js'  : $mode = 'javascript'; break;
					default    : $mode = $ext;
				}
			}
			
			if ( in_array( $ext, array( 'txt', 'md' ) ) ) {
				$icon = 'fa fa-file-text-o';
				$selectable = true;
				switch( $ext ) {
					case 'txt'  : $mode = 'text'; break;
					case 'md'   : $mode = 'markdown'; break;
				}
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
			
			return array(
				'id'         => md5( $relative_path ),
				'parent_id'  => md5( $parent_path ),
				'name'       => $file,
				'modified'   => filemtime( $fullpath ),
				'type'       => 'file',
				'icon'       => $icon,
				'selectable' => $selectable,
				'text'       => $file,
				'mode'       => $mode,
				'path'       => $relative_path,
			);
		}
		
		if ( is_dir( $fullpath ) )
		{
			$nodes = array();
			
			foreach( scandir( $fullpath ) as $_file ) 
			{
				if ( in_array( $_file, array( '.', '..', '.git', '.svn' ) ) ) {
					continue;
				}
				
				$nodes[] = $this->getFileNodeInfo( $fullpath . '/' . $_file );
			}
			
			return array(
				'id'         => md5( $relative_path ),
				'parent_id'  => md5( $parent_path ),
				'name'       => $file,
				'type'       => 'dir',
				'text'       => $file,
				'icon'       => 'fa fa-folder',
				'selectable' => false,
				'nodes'      => array_filter( $nodes ),
				'path'       => $relative_path,
				'state'      => array(
					'expanded' => false,
				),
			);
		}
	}
	
	/**
	 * Output admin page
	 *
	 * @return	void
	 */
	public function output( $content )
	{
		echo $this->getTemplateContent( 'views/layouts/studio', array(
			'content' => $content,
		));
	}
}