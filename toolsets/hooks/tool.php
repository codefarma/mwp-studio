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
namespace MWP\Studio\Toolset;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Framework;
use MWP\Studio\Plugin;
use MWP\Studio\Tool;
use MWP\Studio\AjaxHandlers;

class HookInspector extends Tool
{
	/**
	 * Hook Inspector JS
	 *
	 * @Wordpress\Script( handle="mwp-studio-hook-inspector", deps={"mwp-studio"} )
	 */
	public $inspectorToolJS;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->inspectorToolJS = 'toolsets/' . $this->getToolsetDir() . '/assets/tool.js';
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
		$this->getPlugin()->useScript( $this->inspectorToolJS );
	}
	
	/**
	 * Add the toolbox component to the studio
	 *
	 * @Wordpress\Filter( for="mwp_studio_toolbox_components" )
	 *
	 * @param	array		$components				Toolbox components
	 * @return	array
	 */
	public function getToolboxComponents( $components )
	{
		$components[ 'hook-inspector' ] = array(
			'panelClass' => '',
			'panelHeadingClass' => '',
			'panelBodyClass' =>'',
			'panelCollapseClass' => 'inspectorCollapse',
			'panelTitle' => 'Hook Inspector',
			'panelIcon' => 'fa fa-search-plus',
			'panelContent' => $this->getToolTemplate( 'hook-inspector' ),
		);
		
		return $components;
	}
	
	/**
	 * Add our templates to the studio javascript controller
	 *
	 * @Wordpress\Filter( for="studio_controller_params" )
	 *
	 * @param	array		$params				The studio javascript localized parameters
	 * @return	array
	 */
	public function addInspectorTemplates( $params )
	{
		$params['templates']['panetabs']['hooked-actions'] = $this->getToolTemplate( 'pane-tab-hooks', array( 'hook_type' => 'actions' ) );
		$params['templates']['panetabs']['hooked-filters'] = $this->getToolTemplate( 'pane-tab-hooks', array( 'hook_type' => 'filters' ) );
		
		return $params;
	}
	
	/**
	 * Get results for a hook
	 *
	 * @Wordpress\AjaxHandler( action="mwp_studio_hook_results", for={"users"} )
	 *
	 * @return	void
	 */
	public function hookResults()
	{
		AjaxHandlers::instance()->authorize();		
		
		$search = $_REQUEST['search'];
		$db = Framework::instance()->db();
		$results = array();
		$hooks = \MWP\Studio\Models\Hook::loadWhere( array( 'hook_name=%s', $search ) );
		
		wp_send_json( array( 'results' => array_map( function( $hook ) 
		{ 
			if ( ! $file = $hook->getFile() ) {
				$file = new \MWP\Studio\Models\File;
				$file->file = $hook->file;
			}
			
			return array_merge( $hook->getStudioModel(), array(
				'callback_signature' => $hook->callback_name ? $this->getToolTemplate( 'hook-callback-signature', array( 'hook' => $hook ) ) : '',
				'callback_location' => $file->getLocation(),
				'callback_location_slug' => $file->getLocationSlug(),
			));
		}, $hooks ) ) );
	}
	
	/**
	 * Load actions/filters catalog results
	 *
	 * @Wordpress\Filter( for="mwp_studio_load_catalog_items" )
	 *
	 * @param	array			$results				The loaded catalog results
	 * @return	array
	 */
	public function loadCatalogItems( $results )
	{
		$datatype = $_REQUEST['datatype'];
		$basepath = $_REQUEST['basepath'];
		
		switch( $datatype ) 
		{
			case 'actions':
				
				foreach( \MWP\Studio\Models\Hook::loadWhere( array( "hook_file LIKE %s AND hook_type IN ('add_action','do_action')", $basepath . '%' ) ) as $hook ) 
				{
					$results[] = array_merge( $hook->getStudioModel(), array( 
						'callback_signature' => $hook->callback_name ? $this->getToolTemplate( 'hook-callback-signature', array( 'hook' => $hook ) ) : ''
					));
				}
				break;

			case 'filters':

				foreach( \MWP\Studio\Models\Hook::loadWhere( array( "hook_file LIKE %s AND hook_type IN ('add_filter','apply_filters')", $basepath . '%' ) ) as $hook ) {
					$results[] = array_merge( $hook->getStudioModel(), array( 
						'callback_signature' => $hook->callback_name ? $this->getToolTemplate( 'hook-callback-signature', array( 'hook' => $hook ) ) : ''
					));
				}
				break;
			
		}
		
		return $results;
	}
}

Framework::instance()->attach( new HookInspector() );