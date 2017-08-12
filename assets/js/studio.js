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

	var CollectorModel    = mwp.model.get( 'mwp-studio-collector' );
	var CollectibleModel  = mwp.model.get( 'mwp-studio-collectible' );
	var FileTree          = mwp.model.get( 'mwp-studio-filetree' );
	var FileTreeNode      = mwp.model.get( 'mwp-studio-filetree-node' );
	var Plugin            = mwp.model.get( 'mwp-studio-plugin' );
	var GenericInterface  = mwp.model.get( 'mwp-studio-generic-interface' );
	
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
			 * @var	Collection
			 */
			this.plugins = new Backbone.Collection( [], { model: Plugin } );
			
			/**
			 * View Model
			 */
			this.viewModel = 
			{
				_controller: this,
				plugins: kb.collectionObservable( this.plugins ),
				currentPlugin: ko.observable(),
				openFiles: ko.observableArray(),
				activeFile: ko.observable(),
				env: function() {
					return self.env();
				}
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
			
			// Load available plugins and select the last active one, or the first if not
			this.loadPlugins().done( function() 
			{
				var plugin_id = localStorage.getItem( 'mwp-studio-current-plugin' );
				var index = self.plugins.indexOf( self.plugins.get( plugin_id ) );
				
				if ( index == -1 ) { index = 0; }				
				self.viewModel.currentPlugin( self.viewModel.plugins()[index] );
			});
			
			// Refresh plugins when they become active
			this.viewModel.currentPlugin.subscribe( function( plugin ) {
				plugin.model().refreshStudio();			
				localStorage.setItem( 'mwp-studio-current-plugin', plugin.id() );
			});	

			// Start the ticker
			this.heartbeat();
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
			_.each( this.viewModel.openFiles(), function( fileview ) {
				if ( ! fileview.model().conflicted() ) {
					checkups.push( fileview.model().checkSync() );
				}
			});
			
			$.when.apply( this, checkups ).done( function() {
				setTimeout( function() { self.heartbeat(); }, self.local.heartbeat_interval );
			});
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
		
		layout: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				var options = ko.utils.unwrapObservable( valueAccessor() );
				
				options.center__onresize = 
				options.north__onresize =
				options.east__onresize =
				options.south__onresize = 
				options.west__onresize = function() { arguments[1].trigger( 'resize', arguments ); };
				
				$(element).layout( options );			
			}
		},
		
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
	
	$(document).ready( function() {
		$(window).resize( function() {
			$('#mwp-studio-container').css({height: $(window).height() - 32});
		}).resize();
		
		setTimeout( function() { $(window).resize(); }, 200 );
	});
	
})( jQuery );
 