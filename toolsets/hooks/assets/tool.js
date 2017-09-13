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

	var studio;
	mwp.on( 'mwp-studio.ready', function(c){ studio = c; } );
	
	var GenericEnvironment = mwp.model.get( 'mwp-studio-generic-environment' );
	var Project            = mwp.model.get( 'mwp-studio-project' );
	var Studio             = mwp.controller.model.get( 'mwp-studio' );
	
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
				icon: 'fa fa-bolt',
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
				icon: 'fa fa-filter',
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
	 * Enhance the project model
	 */
	Project.override(
	{
		/**
		 * Initialize
		 *
		 * @param	function		_super			The overridden method callback
		 * @return	void
		 */
		initialize: function( _super )
		{
			_super.apply( this, arguments );
			
			this.actions = ko.observableArray([]).extend({ progressiveFilter: { batchSize: 50 }, rateLimit: 50 });
			this.filters = ko.observableArray([]).extend({ progressiveFilter: { batchSize: 50 }, rateLimit: 50 });
			
			this.actions.loading = ko.observable(false);
			this.filters.loading = ko.observable(false);			
		}
	});
	
	/**
	 * Enhance the studio controller
	 */
	Studio.override(
	{
		/**
		 * Initialize
		 *
		 * @param	function		_super			The overridden method callback
		 * @return	void
		 */
		init: function( _super )
		{
			var self = this;
			
			_super.apply( this, arguments );

			/**
			 * Extend the studio view model
			 */
			_.extend( this.viewModel, 
			{
				/**
				 * Searched hook observable
				 */
				hookSearch: ko.searchObservable( function( hook_name ) 
				{
						return $.ajax({
							url: self.local.ajaxurl,
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
						value: self.viewModel.hookSearch(), 
						callback: function( hook_name ) { 
							if ( hook_name ) { 
								self.viewModel.hookSearch( hook_name );
							}
						}
					});
				}
			});
			
			/**
			 * Extend the hookSearch observable with a grouped results computation
			 */
			this.viewModel.hookSearch.groupedResults = ko.computed( function() 
			{
				var results = _.map( self.viewModel.hookSearch.results().results || [], function( hook ) {
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
			this.viewModel.hookSearch.subscribe( function( hook_name ) 
			{
				// Open the east layout pane
				if ( self.viewModel.studioLayout() ) {
					self.viewModel.studioLayout().open('east');
				}
				
				// Uncollapse the hook inspector panel
				$('.inspectorCollapse').collapse('show');
			}, 
			null, 'beforeChange');			
		}	
	
	});
	
	/**
	 * Add ace editor highlighting rules
	 *
	 * @param	string		type			The rules type (comments, javascript, php, html)
	 * @param	object		rules			The rules object
	 */
	mwp.on( 'ace.rules', function( type, rules ) 
	{
		switch( type ) 
		{
			case 'comments':
			
				rules.$rules.start.push({
					token: "comment.doc",
					regex : "(Action|Filter)[\\s]*\\([\\s]*for=['\"]",
					next: "mwp_hook"
				});
				
				rules.addRules({
					"mwp_hook" : [
						{token : "wp_hook_name", regex : /[^'"]+/},
						{token : "comment.doc", regex : "['\"]", next : "start"},
						{defaultToken : "comment.doc"}		
					]
				});
				break;
				
			case 'php':
				
				rules.$rules.start.splice( 1, 0, {
					token: "wp_hook_call",
					regex : "(do_action|add_action|apply_filters|add_filter)\\([\\s]*['\"]",
					next: "wp_hook"
				});
				
				rules.addRules({
					"wp_hook" : [
						{token : "wp_hook_name", regex : /[^'"]+/},
						{token : "string", regex : "['\"]", next : "start"},
						{defaultToken : "string"}		
					]
				});
				break;
		}
	});

	/**
	 * Search hooks when they are clicked in the editor
	 */
	$(document).on( 'click', '.ace_wp_hook_name', function() {
		studio.viewModel.hookSearch( $(this).text() );
	});	
	
})( jQuery );
 