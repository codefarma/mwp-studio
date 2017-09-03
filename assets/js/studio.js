/**
 * Plugin Javascript Module
 *
 * Created     May 1, 2017
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
	
	$('html').addClass('mwp-bootstrap');
	$(document).on( 'click', '#collapse-menu', function() {
		$(window).resize();
	});

	var CollectorModel    = mwp.model.get( 'mwp-studio-collector' );
	var CollectibleModel  = mwp.model.get( 'mwp-studio-collectible' );
	var FileTree          = mwp.model.get( 'mwp-studio-filetree' );
	var FileTreeNode      = mwp.model.get( 'mwp-studio-filetree-node' );
	var Plugin            = mwp.model.get( 'mwp-studio-plugin' );
	var GenericInterface  = mwp.model.get( 'mwp-studio-generic-interface' );
	
	/**
	 * @var	bool		Flag indicating if a process polling is active
	 */
	var process_polling = false;
	
	/**
	 * Main Controller
	 *
	 * The init() function is called after the page is fully loaded.
	 *
	 * Data passed into your script from the server side is available
	 * by the studio.local property inside your controller:
	 *
	 * > var ajaxurl = studio.local.ajaxurl;
	 *
	 * The viewModel of your controller will be bound to any HTML structure
	 * which uses the data-view-model attribute and names this controller.
	 *
	 * Example:
	 *
	 * <div data-view-model="mwp-studio">
	 *   <span data-bind="text: title"></span>
	 * </div>
	 */
	var studio = mwp.controller( 'mwp-studio', 
	{
		/**
		 * @var	Collection
		 */
		environments: new Backbone.Collection(),
		
		/**
		 * Initialization function
		 *
		 * @return	void
		 */
		init: function()
		{
			var self = this;
			
			/**
			 * @var	Collection		Plugins list
			 */
			this.plugins = new Backbone.Collection( [], { model: Plugin } );
			
			/**
			 * View Model
			 */
			this.viewModel = 
			{
				_controller:   this,
				plugins:       kb.collectionObservable( this.plugins ),
				currentPlugin: ko.observable(),
				openFiles:     ko.observableArray(),
				activeFile:    ko.observable(),
				env:           function() { return self.env(); },
				statustext:    ko.observable(''),
				processStatus: ko.observable(),
				searchPhrase:  ko.searchObservable( function( phrase ) {
					return $.ajax({
						url: self.local.ajaxurl,
						data: {
							action: 'mwp_studio_search',
							phrase: phrase
						}
					});
				}),
				hookSearch:    ko.searchObservable( function( hook_name ) {
					return $.ajax({
						url: self.local.ajaxurl,
						data: {
							action: 'mwp_studio_hook_results',
							search: hook_name
						}
					});
				})
			};
			
			/**
			 * More View Model
			 */
			_.extend( this.viewModel, 
			{
				/**
				 * Active file breadcrumbs
				 * @return	array
				 */
				activeFileBreadcrumbs: ko.computed( function() 
				{
					var breadcrumbs = [];
					var currentFile = self.viewModel.activeFile();
					
					if ( currentFile ) 
					{
						var currentNode = currentFile.model();
						
						while( typeof currentNode.getParent == 'function' && currentNode.getParent() ) {
							breadcrumbs.unshift( currentNode.get('text') );
							currentNode = currentNode.getParent();
						}
						
						if ( currentNode instanceof FileTree ) {
							breadcrumbs.unshift( currentNode.plugin.get('basedir').replace(/^\//,'') );
						}
					}
					
					return breadcrumbs.length ? breadcrumbs : [ 'No file open' ];
				})
			});
			
			// Custom ace editor handlers
			this.initAceHandlers( $(document) );	
			
			/**
			 * Load plugins
			 *
			 * - Select last active plugin after initial load -or-
			 * - Select first plugin in the list
			 */
			this.loadPlugins().done( function() {
				var plugin_id = localStorage.getItem( 'mwp-studio-current-plugin' );
				var index = self.plugins.indexOf( self.plugins.get( plugin_id ) );
				
				if ( index == -1 ) { index = 0; }				
				self.viewModel.currentPlugin( self.viewModel.plugins()[index] );
			});
			
			/**
			 * Lazy load plugin resources only after it becomes active
			 *
			 */
			this.viewModel.currentPlugin.subscribe( function( pluginView ) 
			{
				var plugin = pluginView.model();
				
				if ( ! plugin.fileTree.initialized ) {
					plugin.fetchFileTree();
				}
				
				// Remember last active plugin
				localStorage.setItem( 'mwp-studio-current-plugin', plugin.get('id') );
			});	

			// Start our ticker
			this.heartbeat();
		},
		
		/**
		 * Initialize ace editor custom handlers
		 *
		 * @param	jQuery		scope			The scope on which to listen for events
		 * @return	void
		 */
		initAceHandlers: function( scope )
		{
			var self = this;
			
			// Hook name is clicked
			scope.on( 'click', '.ace_wp_hook_name', function() {
				self.viewModel.hookSearch( $(this).text() );
			});	
		},
		
		/**
		 * Get the current studio environment
		 *
		 * @return	Environment
		 */
		env: function()
		{
			if ( this.viewModel.currentPlugin() ) {
				return this.viewModel.currentPlugin().model().env;
			}
			
			return this.environments.get('generic');
		},
		
		/**
		 * Proxy ajax requests for abstract functionality implementation
		 *
		 * @param	object			options			The ajax options
		 * @return	$.ajax
		 */
		ajax: function( options )
		{
			options.url = this.local.ajaxurl;
			return $.ajax( options );
		},
		
		/**
		 * Heartbeat
		 *
		 * @return	void
		 */
		heartbeat: function() 
		{
			var self = this;			
			var checkups = [];
			
			/* Check on open files */
			_.each( this.viewModel.openFiles(), function( fileview ) {
				if ( ! fileview.model().conflicted() ) {
					checkups.push( fileview.model().checkSync() );
				}
			});
			
			/* Tick Tock. */
			$.when.apply( this, checkups ).done( function() 
			{
				$.ajax({ url: self.local.cron_url });
				$.ajax({ url: self.local.ajaxurl, data: { action: 'mwp_studio_statuscheck' } }).done( function( status ) 
				{
					if ( status.statustext ) {
						self.viewModel.statustext( status.statustext );
					}
					
					if ( status.processing && ! process_polling ) {
						self.startProcessPolling( status.processing );
					}
				});
				setTimeout( function() { self.heartbeat(); }, self.local.heartbeat_interval );
			});
		},
		
		/**
		 * Status watch
		 * 
		 * @param	object			process				The process info provided from the backend
		 * @return	void
		 */
		startProcessPolling: function( process )
		{
			process_polling = true;
			
			var self = this;
			var timeout = 1500;
			
			/**
			 * Poll the backend for an update on this process
			 *
			 * If the status has not changed, we will progressively slow down the poll interval,
			 * and we will keep the poll alive for as long as the process is reporting that it
			 * is not yet complete.
			 */
			var poll = function() 
			{
				$.ajax({
					url: self.local.ajaxurl,
					data: { action: 'mwp_studio_process_status', process: process }
				}).done( function( status ) 
				{
					var current_status = self.viewModel.processStatus();
					
					// Progressively slow down the poll if a process hasn't changed status
					if ( JSON.stringify( current_status ) == JSON.stringify( status ) && timeout < 60000 ) {
						timeout = timeout + 500;
					}
					else
					{
						timeout = 1500;
						self.viewModel.processStatus( status );
					}
					
					status.complete === false ? setTimeout( poll, timeout ) : process_polling = false;
				});
			};
			
			poll();
		},
		
		/**
		 * Load available studio plugins from the backend
		 *
		 * @return	$.Deferred
		 */
		loadPlugins: function()
		{
			var self = this;
			
			return $.ajax({
				method: 'post',
				url: this.local.ajaxurl,
				data: { action: 'mwp_studio_load_plugins' }
			})
			.then( function( data ) {
				if ( data.plugins ) {
					self.plugins.add( data.plugins );
				}
			});
		}
		
	});
	
	/**
	 * Custom knockout bindings
	 */
	_.extend( ko.bindingHandlers, 
	{
		/**
		 * NetEye Activity Indicator
		 * 
		 * @see: https://github.com/live627/jquery-plugins-1/tree/master/activity-indicator
		 */
		studioActivity: {
			update: function( element, valueAccessor, allBindingsAccessor ) {
				var opts = ko.utils.unwrapObservable( valueAccessor() );				
				$(element).studioActivity(opts);
			}
		},
		
		/**
		 * Bootstrap Treeview
		 *
		 * @see: https://github.com/jonmiles/bootstrap-treeview
		 */
		treeView: {
			update: function( element, valueAccessor, allBindingsAccessor )	{
				if ( $.fn.treeview != undefined ) {
					var treenodes = ko.utils.unwrapObservable( valueAccessor() );
					var tree = $(element).treeview( { data: treenodes } );
					tree.on( 'nodeChecked nodeCollapsed nodeDisabled nodeEnabled nodeExpanded nodeSelected nodeUnchecked nodeUnselected', function( event, node ) {
						if ( node.model instanceof Backbone.Model ) {
							node.model.trigger( event.type, event, tree, node );
						}
					});
					
				}
			}
		},
		
		/**
		 * Ace Editor
		 *
		 * @see: https://ace.c9.io/
		 */
		aceEditor: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				if ( typeof ace != 'undefined' ) {
					var setup = ko.utils.unwrapObservable( valueAccessor() );
					var options = setup.options || {};
					var fileview = setup.file;
					var file = fileview.model();
					var editor = ace.edit(element);
					
					editor.setShowPrintMargin(false);
					
					if ( file.get('mode') ) {
						editor.getSession().setMode( 'ace/mode/' + file.get('mode') );
					}
					
					// Initialize the editor with the file content
					file.getContent().done( function( response ) {
						if ( response.success ) {
							file.set( 'modified', response.modified );
							editor.setValue( response.content );
							editor.getSession().setUndoManager( new ace.UndoManager() )
							editor.gotoLine(1);
							file.conflicted( false );
							file.edited( false );
							file.editor = editor;
							file.editorReady.resolve( editor, options );
						}
					});
					
					// Track file changes
					editor.on( 'change', function() {
						file.edited(true);
					});

					// Track the currently active editor
					editor.on( 'focus', function() {
						if ( studio.viewModel.activeFile() !== fileview ) {
							studio.viewModel.activeFile( fileview );
							file.conflicted() ? file.resolveConflict() : file.checkSync();
						}
					});
				}
			}
		},
		
		/**
		 * Bootstrap Context Menu
		 *
		 * @see: https://github.com/dgoguerra/bootstrap-menu
		 */
		contextMenu: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				var options = ko.utils.unwrapObservable( valueAccessor() );
				if ( typeof BootstrapMenu != 'undefined' ) {
					var menu = new BootstrapMenu( options.selector, _.extend({
						fetchElementData: function( el ) {
							return ko.dataFor( el );
						}
					}, options.config ));
				}				
			}
		},
		
		/**
		 * Render a bootstrap menu given an array of [MenuItem]
		 *
		 * 
		 */
		bootstrapMenu: {
			update: function( element, valueAccessor, allBindingsAccessor ) {
				var items = ko.utils.unwrapObservable( valueAccessor() );
				var menu = $(element).html('');
				
				_.each( items, function( menuItem ) {
					menu.append( '<!-- ko stopBinding: true -->' );
					menu.append( menuItem.model().getHtmlDom() );
					menu.append( '<!-- /ko -->' );
				});
			}
		},
		
		/**
		 * jQuery UI Layout
		 *
		 * @see: http://layout.jquery-dev.com
		 */
		layout: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				var options = ko.utils.unwrapObservable( valueAccessor() );
				
				options.center__onresize = 
				options.north__onresize =
				options.east__onresize =
				options.south__onresize = 
				options.west__onresize = function( pane_key, pane ) 
				{
					pane.trigger( 'resize', arguments );
					if ( pane_key in options && typeof options[ pane_key ].onresize == 'function' ) {
						options[ pane_key ].onresize.apply( this, arguments );
					}
				};
				
				$(element).layout( options );			
			}
		},
		
		/**
		 * Make an element size to fill out the remaining height inside a pane container
		 *
		 * @example: <div data-bind="fillPaneContainer: { 'pane': 'ui-layout-pane', container: '.column' }"></div>
		 */
		fillPaneContainer: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				var options = ko.utils.unwrapObservable( valueAccessor() );
				var container = $(element).closest(options.container);
				var fitElement = function() { $(element).css({ height: container.innerHeight() - ( $(element).offset().top - container.offset().top ) }); };
				$(element).closest(options.pane).on( 'resize', fitElement );
				fitElement();
			}		
		}
	});
	
	/**
	 * Custom knockout extenders
	 */
	_.extend( ko.extenders, 
	{
		/**
		 * Fill up an observable array incrementally with a timeout delay so that the
		 * operation can be performed with minimized blocking
		 *
		 * @see: https://github.com/thinkloop/knockout-js-progressive-filter
		 */
		progressiveFilter: function(target, args) 
		{
			var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame || function(callback) { setTimeout(callback, args.timeout || 200); },
				currentCount = 0,
				props = {};

			args = args || {};
			target.progressiveFilter = props;

			props.unfilteredCollection = [];
			props.unfilteredCollectionIndex = 0;
			props.isFiltering = ko.observable(false);
			props.filterFunction = args.filterFunction;
			props.batchSize = Math.max(parseInt(args.batchSize, 10), 1);

			props.add = args.addFunction || function(item) { target.peek().push(item); };
			props.clear = args.clearFunction || function() { target([]); };

			target.isFiltered = function(item) {
				return !props.filterFunction || props.filterFunction(item);
			};

			target.filter = function(unfilteredCollection) {
				var filteredCollection = [],
					i;
				for (i = 0; i < unfilteredCollection.length; i++) {
					if (target.isFiltered(unfilteredCollection[i])) {
						filteredCollection.push(unfilteredCollection[i]);
					}
				}
				props.clear();
				target(filteredCollection);
			};

			target.filterProgressive = function(unfilteredCollection) {
				props.unfilteredCollection = unfilteredCollection.slice(0);
				props.unfilteredCollectionIndex = 0;
				currentCount = 0;
				props.clear();
				if (!props.isFiltering.peek()) {
					props.isFiltering(true);
					requestAnimationFrame(doFilter);
				}
			};

			function doFilter() {
				var item;

				for (props.unfilteredCollectionIndex; props.unfilteredCollectionIndex < props.unfilteredCollection.length; props.unfilteredCollectionIndex++) {
					item = props.unfilteredCollection[props.unfilteredCollectionIndex];
					if (item && target.isFiltered(item)) {
						props.add(item);
						break;
					}
				}

				currentCount++;
				props.unfilteredCollectionIndex++;

				if (props.unfilteredCollectionIndex < props.unfilteredCollection.length) {
					if (currentCount >= props.batchSize) {
						target.valueHasMutated();
						currentCount = 0;
						requestAnimationFrame(doFilter);
					}
					else {
						currentCount++;
						doFilter();
					}
					return;
				}
				else {
					target.valueHasMutated();
					currentCount = 0;
					props.unfilteredCollectionIndex = 0;
					props.isFiltering(false);
				}
			}
		}	
	});
	
	/**
	 * Extend Knockout.js
	 */
	_.extend( ko, 
	{
		/**
		 * Create a search observable with a search function that returns search results
		 *
		 * @param	function		search				The search function (should return a deferred object)
		 * @param	function		interval			The debounce wait period
		 * @return	observable
		 */
		searchObservable: function( search, interval )
		{
			var observable = _.extend( ko.observable(''), {
				loading: ko.observable(false),
				results: ko.observable([])
			});
			
			// Keep track of the most recent search. Only update results for the latest request.
			var latestSearch;
			
			observable.subscribe( _.debounce( function( value ) {
				observable.loading(true);
				var currentSearch = latestSearch = search( value );
				$.when( currentSearch ).done( function( results ) {
					if ( currentSearch === latestSearch ) {
						if ( typeof results !== 'undefined' ) {
							observable.results( results );
						}
						observable.loading(false);
					}
				});
			}, interval || 500 ));
			
			return observable;
		}
	});
	
	/**
	 * Fit the studio to the window when page is loaded
	 */
	$(document).ready( function() {
		$(window).resize( function() {
			$('#mwp-studio-container').css({height: $(window).height() - 32});
		}).resize();
		
		setTimeout( function() { $(window).resize(); }, 200 );
	});
	
})( jQuery );
 