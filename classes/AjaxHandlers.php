<?php
/**
 * Plugin Class File
 *
 * Created:   July 28, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Studio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Framework;
use MWP\Framework\Pattern\Singleton;

/**
 * AjaxHandlers Class
 */
class AjaxHandlers extends Singleton
{
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
		$this->setPlugin( $plugin ?: \MWP\Studio\Plugin::instance() );
	}
	
	/**
	 * Authorize access
	 *
	 * @return	void
	 */
	public function authorize()
	{
		$authorized_users = array_map( 'intval', array_filter( explode( ',', $this->getPlugin()->getSetting( 'authorized_users' ) ) ) );
		if ( ! in_array( get_current_user_id(), $authorized_users ) )
		{
			exit('unauthorized');
		}
	}
	
	/**
	 * Load available studio projects
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_load_projects", for={"users"} )
	 *
	 * @return	void
	 */
	public function loadStudioProjects()
	{
		$this->authorize();
		
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$studio = \MWP\Studio\Plugin::instance();
		$projects = array();
		
		foreach( get_plugins() as $file => $data )
		{
			$project = $studio->getPluginInfo( WP_PLUGIN_DIR . '/' . $file );
			$projects[] = $project;
		}
		
		foreach( wp_get_themes() as $theme_key => $theme )
		{			
			$project = $studio->getThemeInfo( $theme );
			$projects[] = $project;
		}
		
		wp_send_json( array( 'projects' => $projects ) );
	}
	
	/**
	 * Fetch the files contained within a plugin
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_fetch_filetree", for={"users"} )
	 *
	 * @return	void
	 */
	public function fetchFiletree()
	{
		$this->authorize();
		
		$treepath = ABSPATH . str_replace( '../', '', $_REQUEST['treepath'] );
		
		if ( is_file( $treepath ) ) {
			wp_send_json( array( 'nodes' => array( $this->getPlugin()->getFileNodeInfo( $treepath ) ) ) );
		}
		
		wp_send_json( $this->getPlugin()->getFileNodeInfo( $treepath ) );
	}
	
	/**
	 * Fetch the files contained within a plugin
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_load_file", for={"users"} )
	 *
	 * @return	void
	 */
	public function fetchFile()
	{
		$this->authorize();
		
		$filepath = ABSPATH . str_replace( '../', '', $_REQUEST['filepath'] );
		
		if ( ! is_file( $filepath ) ) {
			wp_send_json( array( 'success' => false ) );
		}
		
		wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $filepath ) ) );
	}

	/**
	 * Get the 
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_get_function", for={"users"} )
	 *
	 * @return	void
	 */
	public function getFunction()
	{
		$this->authorize();
		
		$callback_name  = wp_unslash( $_REQUEST['callback_name'] );
		$callback_class = wp_unslash( $_REQUEST['callback_class'] );
		$where = $callback_class ? array( 'function_name=%s AND function_class=%s', $callback_name, $callback_class ) : array( 'function_name=%s AND ( function_class IS NULL OR function_class = "" )', $callback_name );
		
		$functions = \MWP\Studio\Models\Function_::loadWhere( $where );	
		
		wp_send_json( array( 'callback' => $functions[0] ? $functions[0]->dataArray() : null ) );
	}
	
	/**
	 * Get the content of a file 
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_get_file_content", for={"users"} )
	 *
	 * @return	void
	 */
	public function getFileContent()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['filepath'] );
		$file = ABSPATH . $file_path;
		
		if ( file_exists( $file ) and is_file( $file ) )
		{
			wp_send_json( array( 'success' => true, 'file' => $file, 'content' => file_get_contents( $file ), 'modified' => filemtime( $file ) ) );
		}
		else
		{
			wp_send_json( array( 'success' => false, 'content' => '' ) );
		}
	}
	
	/**
	 * Synchronize the modification state of a file
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_sync_file", for={"users"} )
	 *
	 * @return	void
	 */
	public function synchronizeFile()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['filepath'] );
		$file = ABSPATH . $file_path;
		
		if ( file_exists( $file ) and is_file( $file ) )
		{
			wp_send_json( array( 'success' => true, 'modified' => filemtime( $file ) ) );
		}
		else
		{
			wp_send_json( array( 'success' => false ) );
		}
	}
	
	/**
	 * Save the content of a file
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_save_file_content", for={"users"} )
	 *
	 * @return	void
	 */
	public function saveFileContent()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['filepath'] );
		$file = ABSPATH . $file_path;
		
		file_put_contents( $file, wp_unslash( $_REQUEST['content'] ) );
		
		$parts = explode( '.', $file );
		$ext = array_pop( $parts );
		
		if ( $ext == 'php' ) {
			try	{
				$agent = \MWP\Studio\Analyzers\Agent::instance();
				$agent->analyzeFile( $file );
				$agent->saveAnalysis();
			}
			catch( \Exception $e ) { }
		}
		
		wp_send_json( array( 'success' => true, 'modified' => filemtime( $file ) ) );
	}

	/**
	 * Create a file
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_create_file", for={"users"} )
	 *
	 * @return	void
	 */
	public function createFile()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['filepath'] );
		$dir = ABSPATH . $file_path;

		if ( is_file( $dir ) ) {
			$dir = dirname( $dir );
		}

		$filename = str_replace( '../', '', $_REQUEST['filename'] );
		$filename = str_replace( '\\', '/', $filename );
		$filename = explode( '/', $filename );
		$filename = array_pop( $filename );
		$newfile = $dir . '/' . $filename;
		
		if ( is_file ( $newfile ) ) {
			wp_send_json( array( 'success' => false, 'message' => __( 'That file already exists', 'mwp-studio' ) ) );
		}
		
		if ( file_put_contents( $newfile, '' ) !== false ) {
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $newfile ) ) );
		} else {
			wp_send_json( array( 'success' => false, 'message' => __( 'Could not create the file. Perhaps a permission issue?', 'mwp-studio' ) ) );
		}
	}

	/**
	 * Copy a file
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_copy_file", for={"users"} )
	 *
	 * @return	void
	 */
	public function copyFile()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['filepath'] );
		$file = ABSPATH . $file_path;
		$filename = basename( $file );
		$dirname = dirname( $file );
		$ext = '';
		
		if ( strpos( $filename, '.' ) !== false ) {
			$parts = explode( '.', $file );
			$ext = '.' . array_pop( $parts );
			$file = implode( '.', $parts );
		}
		
		if ( is_file( $file . $ext ) ) 
		{
			$x = 2;
			$newfile = $file . '(copy)' . $ext;
			while( file_exists( $newfile ) ) {
				$newfile = $file . '(copy ' . $x++ . ')' . $ext;
			}
			
			if ( copy( $file . $ext, $newfile ) ) {
				wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $newfile ) ) );
			} else {
				wp_send_json( array( 'success' => false, 'message' => __( 'Could not copy the file. Perhaps a permission issue?', 'mwp-studio' ) ) );
			}
		}
		else
		{
			wp_send_json( array( 'success' => false, 'message' => __( 'Only files can be copied.', 'mwp-studio' ) ) );
		}
	}

	/**
	 * Rename a file
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_rename_file", for={"users"} )
	 *
	 * @return	void
	 */
	public function renameFile()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['filepath'] );
		$file = ABSPATH . $file_path;
		$filename = basename( $file );
		$dirname = dirname( $file );
		
		$newname = str_replace( '../', '', $_REQUEST['newname'] );
		$newname = str_replace( '\\', '/', $newname );
		$newname = explode( '/', $newname );
		$newname = array_pop( $newname );
		$newfile = $dirname . '/' . $newname;
		
		if ( is_file ( $newfile ) ) {
			wp_send_json( array( 'success' => false, 'message' => __( 'That filename already exists', 'mwp-studio' ) ) );
		}
		
		if ( is_file( $file ) ) 
		{
			if ( rename( $file, $newfile ) ) {
				wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $newfile ) ) );
			} else {
				wp_send_json( array( 'success' => false, 'message' => __( 'Could not rename the file. Perhaps a permission issue?', 'mwp-studio' ) ) );
			}
		}
		else
		{
			wp_send_json( array( 'success' => false, 'message' => __( 'Only files can be renamed.', 'mwp-studio' ) ) );
		}
	}

	/**
	 * Delete a file
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_delete_file", for={"users"} )
	 *
	 * @return	void
	 */
	public function deleteFile()
	{
		$this->authorize();
		
		$file_path = str_replace( '../', '', $_REQUEST['filepath'] );
		$file = ABSPATH . $file_path;
		
		if ( is_file( $file ) ) 
		{
			if ( unlink( $file ) ) {
				wp_send_json( array( 'success' => true ) );
			} else {
				wp_send_json( array( 'success' => false, 'message' => __( 'Could not delete the file. Perhaps a permission issue?', 'mwp-studio' ) ) );
			}
		}
		else
		{
			wp_send_json( array( 'success' => false, 'message' => __( 'Only files can be deleted.', 'mwp-studio' ) ) );
		}
	}
	
	/**
	 * Do a studio search
	 * 
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_search", for={"users"} )
	 * 
	 * @return	void
	 */
	public function studioSearch()
	{
		$this->authorize();
		
		$phrase = $_REQUEST['phrase'];
		$results = apply_filters( 'mwp_studio_search_results', array(), $phrase );
		
		if ( ! empty( $results ) ) {
			wp_send_json( array( 'success' => true, 'results' => $results ) );
		}
		
		wp_send_json( array( 'success' => false, 'message' => array( 'title' => 'Notice', 'message' => 'No results found.' ) ) );
	}
	
	/**
	 * Create a new project
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_create_project", for={"users"} )
	 *
	 * @return	void
	 */
	public function createProject()
	{
		$this->authorize();
		
		$options = wp_unslash( $_REQUEST['options'] );
		
		try 
		{
			$project = apply_filters( 'mwp_studio_create_project', null, $options );
			
			if ( ! empty( $project ) ) {
				wp_send_json( array( 'success' => true, 'project' => $project ) );
			}
			
			wp_send_json( array( 'success' => false, 'message' => 'No creation engine available for the chosen project.' ) );
		}
		catch( \Exception $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}
	
	/**
	 * Create a new project
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_edit_project", for={"users"} )
	 *
	 * @return	void
	 */
	public function editProject()
	{
		$this->authorize();
		
		$project_info = wp_unslash( $_REQUEST['project'] );
		$errors = apply_filters( 'mwp_studio_edit_project_validation_errors', array(), $project_info );
		
		if ( ! empty( $errors ) ) {
			wp_send_json( array( 'success' => false, 'message' => '<ul><li>' . implode( '</li><li>', $errors ) . '</li></ul>' ) );
		}
		
		do_action( 'mwp_studio_update_project', $project_info );
		
		wp_send_json( array( 'success' => true ) );
	}
	
	/**
	 * Build a new project version
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_build_mwp_project", for={"users"} )
	 *
	 * @return	void
	 */
	public function buildProject()
	{
		$this->authorize();
		
		try 
		{
			$zipfile = \MWP\Framework\Plugin::createBuild( $_REQUEST['slug'], array( 'nobundle' => ! $_REQUEST['bundle'], 'version-update' => $_REQUEST['version'] ) );
			wp_send_json( array( 'success' => true, 'version' => $_REQUEST['version'], 'file' => str_replace( ABSPATH, '', $zipfile ) ) );
		}
		catch( \Exception $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
	}
	
	/**
	 * Create a new php class
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_add_class", for={"users"} )
	 *
	 * @return	void
	 */
	public function createPHPClass()
	{
		$this->authorize();
		
		$framework = \MWP\Framework\Framework::instance();
		$classname = wp_unslash( $_REQUEST['classname'] );
		$plugin = wp_unslash( $_REQUEST['plugin'] );
		
		try 
		{
			$class_file = $framework->createClass( $plugin, $classname );
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $class_file ) ) );
		}
		catch( \ErrorException $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}
	
	/**
	 * Create a new php template
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_add_template", for={"users"} )
	 *
	 * @return	void
	 */
	public function createPHPTemplate()
	{
		$this->authorize();
		
		$framework = \MWP\Framework\Framework::instance();
		$template = wp_unslash( $_REQUEST['template'] );
		$plugin = wp_unslash( $_REQUEST['plugin'] );
		
		try 
		{
			$template_file = $framework->createTemplate( $plugin, $template );
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $template_file ) ) );
		}
		catch( \ErrorException $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}	
	
	/**
	 * Create a new css file
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_add_css", for={"users"} )
	 *
	 * @return	void
	 */
	public function createCSS()
	{
		$this->authorize();
		
		$framework = \MWP\Framework\Framework::instance();
		$filename = wp_unslash( $_REQUEST['filename'] );
		$plugin = wp_unslash( $_REQUEST['plugin'] );
		
		try 
		{
			$css_file = $framework->createStylesheet( $plugin, $filename );
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $css_file ) ) );
		}
		catch( \ErrorException $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}

	/**
	 * Create a new javascript file
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_add_js", for={"users"} )
	 *
	 * @return	void
	 */
	public function createJS()
	{
		$this->authorize();
		
		$framework = \MWP\Framework\Framework::instance();
		$filename = wp_unslash( $_REQUEST['filename'] );
		$plugin = wp_unslash( $_REQUEST['plugin'] );
		
		try 
		{
			$javascript_file = $framework->createJavascript( $plugin, $filename );
			wp_send_json( array( 'success' => true, 'file' => $this->getPlugin()->getFileNodeInfo( $javascript_file ) ) );
		}
		catch( \ErrorException $e )
		{
			wp_send_json( array( 'success' => false, 'message' => $e->getMessage() ) );
		}
		
	}
	
	/**
	 * Load items from the catalog
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_load_catalog_items", for={"users"} )
	 *
	 * @return	void
	 */
	public function loadFromCatalog()
	{
		$this->authorize();
		
		$datatype = $_REQUEST['datatype'];
		$basepath = $_REQUEST['basepath'];
		$results = array();
		
		switch( $datatype ) 
		{
			
		}
		
		wp_send_json( array( 
			'success' => true, 
			'datatype' => $datatype,
			'basepath' => $basepath,
			'results' => apply_filters( 'mwp_studio_load_catalog_items', $results ),
		));
	}

	/**
	 * Check the status of the backend
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_statuscheck", for={"users"} )
	 *
	 * @return	void
	 */
	public function statusCheck()
	{
		$this->authorize();
		
		$status = array();
		$monitor = $this->getPlugin()->getActiveMonitor( 'catalog', false );
		
		if ( $monitor ) {
			$status[ 'processing' ] = array( 'name' => 'catalog' );
		}
		
		wp_send_json( $status );
	}

	/**
	 * Check the status of a backend process
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_process_status", for={"users"} )
	 *
	 * @return	void
	 */
	public function processStatus()
	{
		$this->authorize();
		
		$response = array(
			'icon' => '',
			'status' => '',
		);
		$process_name = isset( $_REQUEST['process']['name'] ) ? $_REQUEST['process']['name'] : null;
		$monitor = $this->getPlugin()->getActiveMonitor( $process_name, false );
		
		if ( $monitor ) 
		{
			$status = $monitor->data;
			$response['complete'] = ( $monitor->completed > 0 );
			
			switch( $monitor->getData('status') )
			{
				case 'Analyzing':
					$completed_count = $status['files_count'] - $status['files_left'];
					$complete_pct = $status['files_count'] ? floor( ( $completed_count / $status['files_count'] ) * 100 ) : 0;
					
					$response['mode'] = 'analyzing';
					$response['icon'] = ( $status['files_left'] > 0 ? 'fa fa-refresh fa-spin' : 'fa fa-check' );
					$response['status'] = ( $status['files_left'] > 0 ? "Updating" : "Updated" ) . " code index: " . ( $status['files_count'] - $status['files_left'] ) . " of " . $status['files_count'] . " files analyzed. ({$complete_pct}%) ";
					break;
					
				case 'Scanning':
					$response['mode'] = 'scanning';
					$response['icon'] = 'fa fa-refresh fa-spin';
					$response['status'] = 'Scanning for file changes... ' . ( $status['files_count'] > 0 ? "{$status['files_count']} files found." : "" );
					break;
					
				case 'Scheduled':
					$response['mode'] = 'waiting';
					$response['icon'] = 'fa fa-hourglass-half';
					$response['status'] = 'Code indexing will begin shortly.';
					break;					
			}
		}
		
		wp_send_json( $response );
	}
	
	/**
	 * Rebuild catalog
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_sync_catalog", for={"users"} )
	 *
	 * @return	void
	 */
	public function rebuildCatalog()
	{
		$this->authorize();		
		$path = $_REQUEST['path'];
		
		if ( $path == 'all' ) {
			$this->getPlugin()->syncCodeIndex();
			wp_send_json( array( 'success' => true, 'background' => true ) );
		}
		else if ( is_dir( ABSPATH . $path ) ) {
			\MWP\Framework\Task::queueTask(
				array( 'action' => 'mwp_studio_catalog_directory' ),
				array( 'fullpath' => ABSPATH . $path, 'recurse' => true )
			);
			wp_send_json( array( 'success' => true, 'background' => true ) );
		}
		else if ( is_file( ABSPATH . $path ) ) {
			$parts = explode( '.', $path );
			$ext = array_pop( $parts );
			if ( $ext == 'php' ) {
				$agent = \MWP\Studio\Analyzers\Agent::instance();
				$agent->analyzeFile( ABSPATH . $path );
				$agent->saveAnalysis();
			}
			wp_send_json( array( 'success' => true, 'background' => false ) );
		}
	}
	
	/**
	 * Do a fuzzy search
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_studio_search", for={"users"} )
	 *
	 * @return	void
	 */
	public function search()
	{
		$this->authorize();		
		
		$search = strtolower( $_REQUEST['phrase'] );
		$db = \MWP\Framework\Framework::instance()->db();
		$results = array(
			'hooks' => array(),
		);
		
		if ( $search ) {
			$words = array_filter( explode( ' ', $search ) );
			$hooks_like = array_map( function( $word ) { return "hook_names.hook_name LIKE '%" . $db::_real_escape( $word ) . "%'"; }, $words );
			
			$results['hooks'] = $db->get_results( "SELECT * FROM ( SELECT DISTINCT(hook_name) FROM {$db->base_prefix}studio_hook_catalog WHERE 1 ) AS hook_names WHERE " . implode( ' AND ', $hooks_like ) );
		}
		
		wp_send_json( array( 'results' => $results ) );
	}
}
