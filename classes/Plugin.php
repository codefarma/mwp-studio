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

use Modern\Wordpress\Task;
use MWP\Studio\Models;
use MWP\Studio\Analyzers\Agent;

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
	 * @Wordpress\Script( handle="mwp-studio-environment", deps={"mwp-studio-models"} )
	 */
	public $studioInterfaces = 'assets/js/studio.environment.js';

	/**
	 * Main Javascript Controller
	 * @Wordpress\Script( handle="mwp-studio", deps={"mwp-studio-environment"} )
	 */
	public $studioController = 'assets/js/studio.js';
	
	/**
	 * jQuery Layout JS
	 * @Wordpress\Script( deps={"jquery","jquery-ui-draggable"} )
	 */
	public $jqueryLayout = 'assets/js/lib/jquery.layout.js';
	
	/**
	 * Ace code editor
	 * @Wordpress\Script
	 */
	public $aceEditor = 'assets/ace/src-min-noconflict/ace.js';
	
	/**
	 * Activity Indicator
	 * @Wordpress\Script( deps={"jquery"} )
	 */
	public $activityIndicatorJS = 'assets/js/lib/jquery.activity-indicator-1.0.0.min.js';
	
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
	public $bootstrapTreeviewJS = 'assets/js/lib/bootstrap-treeview.min.js';
	
	/**
	 * Bootstrap Context Menu JS
	 * @Wordpress\Script(deps={"mwp-bootstrap"})
	 * @see: https://github.com/dgoguerra/bootstrap-menu
	 */
	public $bootstrapContextmenuJS = 'assets/js/lib/bootstrap-contextmenu.min.js';
	
	/**
	 * Bootbox JS
	 * @Wordpress\Script(deps={"mwp-bootstrap"})
	 * @see: http://bootboxjs.com
	 */
	public $bootboxJS = 'assets/js/lib/bootstrap-bootbox.min.js';

	/**
	 * Init
	 *
	 * @Wordpress\Action( for="plugins_loaded" )
	 *
	 * @return	void
	 */
	public function wp_init()
	{
		foreach( scandir( $this->pluginFile( 'toolsets' ) ) as $toolset ) {
			if ( is_dir( $this->pluginFile( 'toolsets/' . $toolset ) ) ) {
				if ( is_file( $this->pluginFile( "toolsets/{$toolset}/tool.php" ) ) ) {
					include_once $this->pluginFile( "toolsets/{$toolset}/tool.php" ); 
				}
			}
		}	
	}
	
	/**
	 * Load Studio UI?
	 *
	 * @return	bool
	 */
	public function loadStudioUI()
	{
		$screen = get_current_screen();		
		return $screen->id === 'dashboard_page_mwp-studio-dashboard';	
	}
	
	/**
	 * Enqueue scripts and stylesheets
	 * 
	 * @Wordpress\Action( for="admin_enqueue_scripts" )
	 *
	 * @return	void
	 */
	public function enqueueScripts()
	{	
		if ( $this->loadStudioUI() )
		{			
			// jQuery Layout
			$this->useScript( $this->jqueryLayout );
			
			// Activity Indicator
			$this->useScript( $this->activityIndicatorJS );
			
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
			$this->useScript( $this->studioController, apply_filters( 'studio_controller_params', array( 
				'cron_url' => rtrim( get_site_url(), '/' ) . '/wp-cron.php',
				'heartbeat_interval' => 20000,
				'templates' => array
				(
					'menus' => array(
						'header'            => $this->getTemplateContent( 'snippets/menus/item-header' ),
						'action'            => $this->getTemplateContent( 'snippets/menus/item-action' ),
						'divider'           => $this->getTemplateContent( 'snippets/menus/item-divider' ),
						'submenu'           => $this->getTemplateContent( 'snippets/menus/item-submenu' ),
						'dropdown'          => $this->getTemplateContent( 'snippets/menus/item-dropdown' ),
					),
					'dialogs' => array(
						'create-plugin'     => $this->getTemplateContent( 'dialogs/create-plugin' ),
						'create-class'      => $this->getTemplateContent( 'dialogs/create-class' ),
						'create-javascript' => $this->getTemplateContent( 'dialogs/create-javascript' ),
						'create-stylesheet' => $this->getTemplateContent( 'dialogs/create-stylesheet' ),
						'create-template'   => $this->getTemplateContent( 'dialogs/create-template' ),
					),
					'panetabs' => array(),
				),
			)));
		}
	}
	
	/**
	 * Get plugin info
	 *
	 * @param	string		$file			The plugin filename
	 * @return	array
	 */
	public function getPluginInfo( $file )
	{
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		$core_data = get_plugin_data( $file );
		$composite_data = array();		
		$basedir = str_replace( ABSPATH, '', str_replace( "/" . basename( $file ), "", $file ) );
		$composite_data[ 'pluginfile' ] = $file;
		$composite_data[ 'basedir' ] = $basedir;
		$composite_data[ 'environment' ] = 'generic';
		
		foreach( $core_data as $key => $value )	{
			$composite_data[ strtolower( $key ) ] = $value;		
		}
		
		if ( file_exists( ABSPATH . $basedir  . '/data/plugin-meta.php' ) )
		{
			$plugin_data = json_decode( include( ABSPATH . $basedir  . '/data/plugin-meta.php' ), true );
			if ( is_array( $plugin_data ) ) {
				$composite_data[ 'environment' ] = 'mwp';
				$composite_data = array_merge( $plugin_data, $composite_data );
			}
		}
		
		$composite_data[ 'id' ] = md5( $basedir );
		
		return apply_filters( 'mwp_studio_plugin_info', $composite_data );
	}
	
	/**
	 * Get theme info
	 *
	 * @param	string		$theme			The theme
	 * @return	array
	 */
	public function getThemeInfo( $theme )
	{
		$theme_info = array();
		
		$theme_info['key']         = $theme->get_stylesheet();
		$theme_info['name']        = $theme->get('Name');
		$theme_info['uri']         = $theme->get('ThemeURI');
		$theme_info['description'] = $theme->get('Description');
		$theme_info['author']      = $theme->get('Author');
		$theme_info['author_url']  = $theme->get('AuthorURI');
		$theme_info['version']     = $theme->get('Version');
		$theme_info['template']    = $theme->get('Template');
		$theme_info['status']      = $theme->get('Status');
		$theme_info['tags']        = array_map( 'trim', explode( ',', $theme->get('Tags') ) );
		$theme_info['text_domain'] = $theme->get('TextDomain');
		$theme_info['domain_path'] = $theme->get('DomainPath');
		$theme_info['basedir']     = str_replace( ABSPATH, '', $theme->get_stylesheet_directory() );
		$theme_info['slug']        = $theme->get_stylesheet;
		$theme_info['environment'] = 'generic';
		$theme_info['id']          = md5( $theme_info['basedir'] );
		
		return apply_filters( 'mwp_studio_theme_info', $theme_info );
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
		$relative_path = str_replace( ABSPATH, '', $fullpath );
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
				'ext'        => $ext,
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
	 * Add the wordpress code analyzer skips
	 *
	 * @Wordpress\Filter( for="mwp_studio_analyzer_skips", args=2 )
	 *
	 * @param	array			$skips			Array of existing skips
	 * @param	string			$dirpath		Directory being analyzed
	 * @return	array
	 */
	public function pluginAnalyzerFileSkips( $skips, $dirpath )
	{
		$skips = array_merge( $skips, array( '.git', '.svn' ) );
		
		if ( is_file( $dirpath . '/data/plugin-meta.php' ) ) {
			$skips = array_merge( $skips, array( 'vendor', 'data', 'tests', 'framework', 'boilerplate', 'annotations' ) );
		}
		
		return $skips;
	}
	
	/**
	 * Queue the catalog of all wordpress code
	 * 
	 * @return	void
	 */
	public function catalogEverything()
	{
		/**
		 * Wordpress core
		 */
		Task::queueTask(
			array( 'action' => 'mwp_studio_catalog_directory' ),
			array( 'fullpath' => rtrim( ABSPATH, '/\\' ), 'recurse' => false )
		);
		Task::queueTask(
			array( 'action' => 'mwp_studio_catalog_directory' ),
			array( 'fullpath' => rtrim( ABSPATH, '/\\' ) . '/wp-admin', 'recurse' => true )
		);
		Task::queueTask(
			array( 'action' => 'mwp_studio_catalog_directory' ),
			array( 'fullpath' => rtrim( ABSPATH, '/\\' ) . '/wp-includes', 'recurse' => true )
		);
		
		/**
		 * Plugin folder
		 */
		Task::queueTask(
			array( 'action' => 'mwp_studio_catalog_directory' ),
			array( 'fullpath' => WP_PLUGIN_DIR, 'recurse' => true )
		);
		
		/**
		 * Theme folder
		 */
		Task::queueTask(
			array( 'action' => 'mwp_studio_catalog_directory' ),
			array( 'fullpath' => get_theme_root(), 'recurse' => true )
		);		
	}
	
	/**
	 * Finds or creates a task monitor
	 *
	 * @param	string			$type				The monitor type
	 * @param	bool			$create				Whether an active monitor should be created if not found
	 * @return	Task|null
	 */
	public function getActiveMonitor( $type, $create=true )
	{
		foreach( $this->getMonitors() as $monitor ) {
			if ( $monitor->action == 'mwp_studio_' . $type . '_monitor' and $monitor->completed == 0 and $monitor->fails < 3 ) {
				return $monitor;
			}
		}
		
		if ( $create ) {
			return Task::queueTask( array( 'action' => 'mwp_studio_' . $type . '_monitor', 'tag' => 'mwp_studio_monitor' ), array() );
		}
		
		return null;
	}
	
	/**
	 * Get all current monitors
	 *
	 * @return	array
	 */
	public function getMonitors()
	{
		return Task::loadWhere( array( 'task_tag=%s', 'mwp_studio_monitor' ) );
	}
	
	/**
	 * Catalog files from a directory (setup)
	 *
	 * @Wordpress\Action( for="mwp_studio_catalog_directory_setup" )
	 *
	 * @return	void
	 */
	public function catalogDirectorySetup( $task )
	{
		if ( $fullpath = $task->getData( 'fullpath' ) and is_dir( $fullpath ) )
		{
			if ( ! $task->getData( 'initialized' ) )
			{			
				$skips = array_merge( array( '.', '..' ), apply_filters( 'mwp_studio_analyzer_skips', array(), $fullpath ) );
				$files = array();
				
				$task->log( 'Analyze files in: ' . $fullpath );
				
				foreach( scandir( $fullpath ) as $file ) 
				{
					if ( in_array( $file, $skips ) ) {
						$task->log( 'Skipping: ' . $fullpath . '/' . $file );
						continue;
					}
					
					if ( is_dir( $fullpath . '/' . $file ) )
					{
						if ( $task->getData( 'recurse' ) ) {
							Task::queueTask(
								array( 'action' => 'mwp_studio_catalog_directory' ),
								array( 'fullpath' => $fullpath . '/' . $file, 'recurse' => true )
							);
							$task->log( 'Added task to catalog sub-directory: ' . $file );
						}
					}
					else
					{
						$parts = explode( '.', $file );
						$ext = array_pop( $parts );
						if ( $ext == 'php' ) {
							$files[] = $fullpath . '/' . $file;
						}
					}
				}
				
				$monitor = $this->getActiveMonitor( 'catalog' );
				$db      = \Modern\Wordpress\Framework::instance()->db();
				$data    = json_decode( $db->get_results( "SELECT task_data FROM {$db->base_prefix}queued_tasks WHERE task_id={$monitor->id()}" )[0]->task_data, true );
				$monitor->setData( 'files_count', $data['files_count'] + count( $files ) );
				$monitor->setData( 'files_left', $data['files_left'] + count( $files ) );
				$monitor->save();
				
				$task->setData( 'files', $files );
				$task->setData( 'initialized', true );
				$task->priority = 4;
				$task->next_start = time() + 10;
			}
		}
		else
		{
			$task->log( 'Invalid directory path: ' . $fullpath );
			$task->abort();
		}
	}
	
	/**
	 * Catalog files from a directory
	 * 
	 * @Wordpress\Action( for="mwp_studio_catalog_directory" )
	 *
	 * @param	Task		$task			The catalog task
	 * @return	void
	 */
	public function catalogDirectory( $task )
	{
		$agent = Agent::instance();		
		$files = $task->getData( 'files' );
		
		if ( empty( $files ) ) {
			$task->log( 'No more files to catalog' );
			return $task->complete();
		}
		
		$file = array_shift( $files );
		$task->setData( 'files', $files );
		
		$agent->analyzeFile( $file );
		$agent->saveAnalysis();
		
		$db      = \Modern\Wordpress\Framework::instance()->db();
		$monitor = $this->getActiveMonitor( 'catalog' );
		$data    = json_decode( $db->get_results( "SELECT task_data FROM {$db->base_prefix}queued_tasks WHERE task_id={$monitor->id()}" )[0]->task_data, true );
		$monitor->setData( 'files_left', $data['files_left'] - 1 );
		$monitor->save();
		
		$task->log( 'Analyzed: ' . $file );
	}
	
	/**
	 * A task that will monitor all other catalog tasks
	 *
	 * @Wordpress\Action( for="mwp_studio_catalog_monitor" )
	 *
	 * @param	Task		$task			The task
	 * @return	void
	 */
	public function catalogMonitor( $task )
	{
		$criteria = array( 'task_action=%s AND task_completed=0 AND task_fails<3', 'mwp_studio_catalog_directory' );

		if ( ! Task::countWhere( $criteria ) ) {
			$task->complete();
		}
		else
		{
			$task->next_start = time() + 60;
		}
	}
	
	/**
	 * Save analysis data
	 *
	 * @Wordpress\Action( for="mwp_studio_save_analysis" )
	 *
	 * @param	Agent		$agent			The analysis agent
	 * @return	void
	 */
	public function saveAnalysisData( $agent )
	{
		$data = $agent->getAnalysis();
		
		if ( isset( $data['files'] ) and ! empty( $data['files'] ) ) 
		{
			foreach( $data['files'] as $file ) {
				Models\Function_::deleteWhere( array( 'function_file=%s', $file['file'] ) );
				Models\Class_::deleteWhere( array( 'class_file=%s', $file['file'] ) );
				
				$_files = Models\File::loadWhere( array( 'file_file=%s', $file['file'] ) );
				$_file = ! empty( $_files ) ? $_files[0] : new Models\File;
				$_file->file = $file['file'];
				$_file->type = ( strpos( $file['file'], str_replace( ABSPATH, '', WP_PLUGIN_DIR ) ) === 0 ? 'plugin' : ( strpos( $file['file'], str_replace( ABSPATH, '', get_theme_root() ) ) === 0 ? 'theme' : 'core' ) );
				$_file->data = array();
				$_file->last_analyzed = time();
				$_file->save();
			}
		}
		
		if ( isset( $data['functions'] ) and ! empty( $data['functions'] ) ) {
			$this->saveAnalysisModels( $data['functions'], 'MWP\Studio\Models\Function_' );
		}
		
		if ( isset( $data['classes'] ) and ! empty( $data['classes'] ) ) {
			$this->saveAnalysisModels( $data['classes'], 'MWP\Studio\Models\Class_' );
		}
		
	}
	
	/**
	 * Save analysis models data
	 *
	 * @param	array			$models			An array of models
	 * @param	string			$modelClass		The model class
	 * @return	array
	 */
	public function saveAnalysisModels( $models, $modelClass )
	{
		$created_models = array();
		foreach( $models as $model_info )
		{
			$model = new $modelClass();
			foreach( $model_info as $key => $value )
			{
				$model->{$key} = $value;
			}
			$model->save();
			$created_models[] = $model;
		}
		
		return $created_models;
	}
	
	/**
	 * Output studio
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