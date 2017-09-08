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
	 * Add actions/filters pane tabs to the generic environment
	 *
	 * @param	Project			project			The project to get pane tabs for
	 * @param	function		parent			The overloaded method callback
	 * @return	array
	 */
	GenericEnvironment.overload( 'getStudioPaneTabs', function( project, parent )
	{
		var tabs = parent( project );
		var studio = mwp.controller.get('mwp-studio');
		
		tabs.push(
		{
			id: 'hooked-actions',
			title: 'Actions',
			viewModel: studio.viewModel,
			template: $(studio.local.templates.panetabs['hooked-actions']),
			refreshContent: function() {
				var project = studio.viewModel.currentProject();
				if ( project ) {
					return project.model().fetchItemCatalog( 'actions' );
				}
				
				return $.Deferred();
			}
		},
		{
			id: 'hooked-filters',
			title: 'Filters',
			viewModel: studio.viewModel,
			template: $(studio.local.templates.panetabs['hooked-filters']),
			refreshContent: function() {
				var project = studio.viewModel.currentProject();
				if ( project ) {
					return project.model().fetchItemCatalog( 'filters' );
				}
				
				return $.Deferred();
			}
		});

		return tabs;
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
		 * Connect to ace editor click events on hook names
		 */
		$(document).on( 'click', '.ace_wp_hook_name', function() {
			studio.viewModel.hookSearch( $(this).text() );
		});
		
		/**
		 * Extend the studio view model
		 */
		_.extend( studio.viewModel, 
		{
			hookSearch: ko.searchObservable( function( hook_name ) {
					return $.ajax({
						url: studio.local.ajaxurl,
						data: {
							action: 'mwp_studio_hook_results',
							search: hook_name
						}
					});
			}, 25, true)
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
		 * Open pane when hook is searched
		 */
		studio.viewModel.hookSearch.subscribe( function() {
			$('#mwp-studio-container').layout().open( 'east' );
		});	
	
	});
	
	
})( jQuery );
 