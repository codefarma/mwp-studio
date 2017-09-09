/**
 * Hook Inspector Javascript Module
 *
 * Created     September 8, 2017
 *
 * @package    Wordpress Plugin Studio
 * @author     Kevin Carwile
 * @since      {build_version}
 */

/**
 * Controller Design Pattern
 *
 * Note: This pattern has a dependency on the "mwp" script
 * i.e. @Wordpress\Script( deps={"mwp"} )
 */
(function( $, undefined ) {
	
	"use strict";

	var GenericEnvironment = mwp.model.get( 'mwp-studio-generic-environment' );
	
	/**
	 * Enhance the generic environment
	 */
	GenericEnvironment.override(
	{
		/**
		 * Add actions/filters pane tabs to the generic environment
		 *
		 * @param	function		_super			The overridden method callback
		 * @param	Project			project			The project to get pane tabs for
		 * @return	array
		 */
		getStudioPaneTabs: function( _super, project )
		{
			var tabs = _super.apply( this, arguments );
			var studio = mwp.controller.get('mwp-studio');
			
			tabs.push(
			{
				id: 'hooked-actions',
				title: 'Actions',
				viewModel: studio.viewModel,
				template: $(studio.local.templates.panetabs['hooked-actions']),
				refreshContent: function() 
				{
					var _project = studio.viewModel.currentProject();
					if ( _project ) {
						return _project.model().fetchItemCatalog( 'actions' );
					}
					
					return $.Deferred();
				}
			},
			{
				id: 'hooked-filters',
				title: 'Filters',
				viewModel: studio.viewModel,
				template: $(studio.local.templates.panetabs['hooked-filters']),
				refreshContent: function() 
				{
					var _project = studio.viewModel.currentProject();
					if ( _project ) {
						return _project.model().fetchItemCatalog( 'filters' );
					}
					
					return $.Deferred();
				}
			});

			return tabs;
		}
	});
	
	/**
	 * Extend the studio functionality
	 *
	 * @param	controller		studio			The studio controller after it has been initialized
	 * @return	void
	 */
	mwp.on( 'mwp-studio.init', function( studio ) 
	{
		/**
		 * Connect ace editor click events on hook names
		 */
		$(document).on( 'click', '.ace_wp_hook_name', function() {
			studio.viewModel.hookSearch( $(this).text() );
		});
		
		/**
		 * Extend the studio view model
		 */
		_.extend( studio.viewModel, 
		{
			/**
			 * Searched hook observable
			 */
			hookSearch: ko.searchObservable( function( hook_name ) 
			{
					return $.ajax({
						url: studio.local.ajaxurl,
						data: {
							action: 'mwp_studio_hook_results',
							search: hook_name
						}
					});
			}, 25, true ),
			
			/**
			 * Open a hook search dialog
			 *
			 * @return	void
			 */
			hookDialog: function() 
			{
				bootbox.prompt({ 
					title: 'Hook Search', 
					value: studio.viewModel.hookSearch(), 
					callback: function( hook_name ) { 
						if ( hook_name ) { 
							studio.viewModel.hookSearch( hook_name );
						}
					}
				});
			}
		});
		
		/**
		 * Extend the hookSearch observable with a grouped results computation
		 */
		studio.viewModel.hookSearch.groupedResults = ko.computed( function() 
		{
			var results = _.map( studio.viewModel.hookSearch.results().results || [], function( hook ) {
				hook.group = hook.callback_location + '/' + hook.callback_location_slug;
				return hook;
			});
			
			return _.indexBy( _.map( ['do_action','add_action','apply_filters','add_filter'], function( hook_type ) {
				return {
					type: hook_type,
					groups: _.map( _.groupBy( _.where( results, { hook_type: hook_type } ), 'group' ), function( hooks, group_key ) {
						var pieces = group_key.split('/');				
						return { 
							location: pieces[0], 
							slug: pieces[1], 
							hooks: _.sortBy( hooks, function( _hook ) { return parseInt( _hook.hook_priority ); } ) };
					}) };
			}), 'type' );
		});
		
		/**
		 * Presenter Logic
		 *
		 * Show inspector in the toolbox when a new hook is searched
		 */
		studio.viewModel.hookSearch.subscribe( function( hook_name ) 
		{
			if ( hook_name ) {
				// Open the east layout pane
				if ( studio.viewModel.studioLayout() ) {
					studio.viewModel.studioLayout().open('east');
				}
				
				// Uncollapse the hook inspector panel
				$('.inspectorCollapse').collapse('show');
			}
		}, 
		null, 'beforeChange');	
	
	});
	
	
})( jQuery );
 