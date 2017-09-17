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
namespace MWP\Studio\Toolset;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Pattern\ActiveRecord;
use Modern\Wordpress\Framework;

use MWP\Studio\Analyzers\AbstractAnalyzer;
use MWP\Studio\AjaxHandlers;
use MWP\Studio\Plugin;
use MWP\Studio\Tool;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

/**
 * Hook Inspector Tool
 */
class HookInspector extends Tool
{
	/**
	 * Hook Inspector JS
	 *
	 * @Wordpress\Script( handle="mwp-studio-hook-inspector", deps={"mwp-studio"} )
	 */
	public $inspectorToolJS;
	
	/**
	 * Hook Inspector CSS
	 *
	 * @Wordpress\Stylesheet
	 */
	public $inspectorToolCSS;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->inspectorToolJS  = $this->relativePath( 'assets/tool.js' );
		$this->inspectorToolCSS = $this->relativePath( 'assets/tool.css' ); 
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
		if ( $this->getPlugin()->loadStudioUI() ) 
		{
			$this->getPlugin()->useScript( $this->inspectorToolJS );
			$this->getPlugin()->useStyle( $this->inspectorToolCSS );
		}
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
		$hooks = HookModel::loadWhere( array( 'hook_name=%s', $search ) );
		
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
				
				foreach( HookModel::loadWhere( array( "hook_file LIKE %s AND hook_type IN ('add_action','do_action')", $basepath . '%' ) ) as $hook ) 
				{
					$results[] = array_merge( $hook->getStudioModel(), array( 
						'callback_signature' => $hook->callback_name ? $this->getToolTemplate( 'hook-callback-signature', array( 'hook' => $hook ) ) : ''
					));
				}
				break;

			case 'filters':

				foreach( HookModel::loadWhere( array( "hook_file LIKE %s AND hook_type IN ('add_filter','apply_filters')", $basepath . '%' ) ) as $hook ) {
					$results[] = array_merge( $hook->getStudioModel(), array( 
						'callback_signature' => $hook->callback_name ? $this->getToolTemplate( 'hook-callback-signature', array( 'hook' => $hook ) ) : ''
					));
				}
				break;
			
		}
		
		return $results;
	}
	
	/**
	 * Add hooks analyzer to code agent
	 *
	 * @Wordpress\Filter( for="mwp_studio_code_analyzers" )
	 *
	 * @param	array			$analyzers				Existing analyzers
	 * @return	array
	 */
	public function addCodeAnalyzer( $analyzers )
	{
		$analyzers[] = new HookInspectorAnalyzer;
		return $analyzers;
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
				HookModel::deleteWhere( array( 'hook_file=%s', $file['file'] ) );
			}
		}
		
		if ( isset( $data['hooks'] ) and ! empty( $data['hooks'] ) ) {
			$this->getPlugin()->saveAnalysisModels( $data['hooks'], 'MWP\Studio\Toolset\HookModel' );
		}		
	}
	
	/**
	 * Delete hook records for detected missing files
	 *
	 * @Wordpress\Action( for="mwp_studio_missing_file" )
	 *
	 * @param	File		$file			The missing file record
	 * @return	void
	 */
	public function removeMissingFile( $file )
	{
		HookModel::deleteWhere( array( 'hook_file=%s', $file->file ) );
	}	
}

/**
 * Hooks Code Analyzer 
 */
class HookInspectorAnalyzer extends AbstractAnalyzer
{
	/**
	 * Entering a node
	 *
	 * @return	void
	 */
    public function enterNode( Node $node ) 
	{
		/**
		 * Natural hooks & filters
		 */
        if ( $node instanceof Node\Expr\FuncCall ) 
		{
			$func_name = $node->name->parts[0];
			
			/**
			 * apply_filters, do_action, add_filter, add_action
			 */
			if ( in_array( $func_name, array( 'apply_filters', 'do_action', 'add_filter', 'add_action' ) ) )
			{
				$hook = $node->args[0]->value;
				if ( $hook instanceof Node\Scalar\String_ ) 
				{
					$callback_name = null;
					$callback_class = null;
					$callback_type = null;
					$hook_priority = null;
					$hook_args = null;
					
					/**
					 * Hooks that register callbacks
					 */
					if ( in_array( $func_name, array( 'add_filter', 'add_action' ) ) )
					{
						$callback = $node->args[1]->value;
						
						/**
						 * Analyze Callback
						 */
						{
							// Function name provided
							if ( $callback instanceof Node\Scalar\String_ )
							{
								if ( strpos( $callback->value, '::' ) ) {
									list( $classname, $method ) = explode( '::', $callback->value, 2 );
									$callback_type = 'method';
									$callback_name = $method;
									$callback_class = $classname;
								}
								else
								{
									$callback_type = 'function';
									$callback_name = $callback->value;
								}
							}
							
							// Anonymous function provided
							else if ( $callback instanceof Node\Expr\Closure ) 
							{
								$callback_type = 'closure';
							}
							
							// Class/method array provided
							else if ( $callback instanceof Node\Expr\Array_ ) 
							{
								$callback_type = 'method';
								
								$arg1 = $callback->items[0]->value;
								$arg2 = $callback->items[1]->value;
								
								if ( $arg1 instanceof Node\Scalar\String_ ) {
									$callback_class = $arg1->value;
								}
								else if ( $arg1 instanceof Node\Expr\Variable )
								{
									if ( $arg1->name == 'this' ) {
										$callback_class = $this->getTraverser()->getCurrentClassname();
									}
								}
								else if ( $arg1 instanceof Node\Scalar\MagicConst\Class_ )
								{
									$callback_class = $this->getTraverser()->getCurrentClassname();
								}
								else if ( $arg1 instanceof Node\Expr\ConstFetch ) 
								{
									if ( $arg1->name instanceof Node\Name\FullyQualified ) {
										//$callback_class = implode( '\\', $arg1->name->parts );
									}
								}
								
								if ( $arg2 instanceof Node\Scalar\String_ ) {
									$callback_name = $arg2->value;
								}
							}
						}
						
						/**
						 * Analyze priority
						 *
						 * Use provided number priority for hook, or if not provided, use wordpress default value
						 */
						if ( isset( $node->args[2] ) ) {
							if ( $node->args[2]->value instanceof Node\Scalar\LNumber ) {
								$hook_priority = $node->args[2]->value->value;
							}
						} else {
							$hook_priority = 10;
						}
						
						/**
						 * Analyze arguments
						 *
						 * Use provided number of args for hook, or if not provided, use wordpress default value
						 */
						if ( isset( $node->args[3] ) ) {
							if ( $node->args[3]->value instanceof Node\Scalar\LNumber ) {
								$hook_args = $node->args[3]->value->value;
							}
						} else {
							$hook_args = 1;
						}
					}
					
					/**
					 * Hooks that register callbacks
					 */
					if ( in_array( $func_name, array( 'do_action', 'apply_filters' ) ) )
					{
						$hook_args = count( $node->args ) - 1;
					}
					
					/**
					 * Add hook details to analysis
					 */
					$this->analysis['hooks'][] = array_merge
					(
						$this->getTraverser()->getCurrentFileInfo(),
						array(
							'type' => $func_name,
							'name' => $hook->value,
							'callback_name' => $callback_name,
							'callback_class' => $callback_class,
							'callback_type' => $callback_type,
							'line' => $node->getLine(),
							'data' => array( 'register_type' => 'natural' ),
							'args' => $hook_args,
							'priority' => $hook_priority,
							'catalog_time' => time(),
						)
					);
				}
			}
        }
	}
}

/**
 * Hook Class
 */
class HookModel extends ActiveRecord
{
	/**
	 * @var	array		Multitons cache (needs to be defined in subclasses also)
	 */
	protected static $multitons = array();
	
	/**
	 * @var	string		Table name
	 */
	public static $table = 'studio_hook_catalog';
	
	/**
	 * @var	array		Table columns
	 */
	public static $columns = array(
	    'id',
	    'name',
	    'type',
	    'callback_name',
	    'callback_class',
	    'callback_type',
	    'data' => array( 'format' => 'JSON' ),
	    'file',
	    'line',
	    'args',
	    'priority',
	    'catalog_time',
	);
	
	/**
	 * @var	string		Table primary key
	 */
	public static $key = 'id';
	
	/**
	 * @var	string		Table column prefix
	 */
	public static $prefix = 'hook_';
	
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
		$this->setPlugin( $plugin ?: \MWP\Studio\Plugin::instance() );
	}
	
	/**
	 * @var		Callback function
	 */
	protected $callback;
	
	/**
	 * Get the callback function
	 *
	 * @return	Function_
	 */
	public function getCallback()
	{
		if ( ! $this->callback_name and ! $this->callback_class ) {
			return false;
		}
		
		if ( isset( $this->callback ) ) {
			return $this->callback;
		}
		
		$where = $this->callback_class ? array( 'function_name=%s AND function_class=%s', $this->callback_name, $this->callback_class ) : array( 'function_name=%s AND function_class IS NULL', $this->callback_name );
		$functions = \MWP\Studio\Models\Function_::loadWhere( $where );
		
		if ( count( $functions ) ) {
			$this->callback = $functions[0];
		}
		else
		{
			$this->callback = false;
		}
		
		return $this->callback;
	}
	
	/**
	 * Get the file record for this hook
	 *
	 * @return	File
	 */
	public function getFile()
	{
		return \MWP\Studio\Models\File::loadByPath( $this->file );
	}
	
	/**
	 * Get the content to display in the hook popover
	 *
	 * @return	array
	 */
	public function getStudioModel()
	{
		$data = $this->dataArray();
		$data['hook_data'] = $this->data;
		
		return apply_filters( 'studio_model_hook', $data, $this );
	}
	
}

Framework::instance()->attach( new HookInspector() );