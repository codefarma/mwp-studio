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

	/**
	 * Main Controller
	 *
	 * The init() function is called after the page is fully loaded.
	 *
	 * Data passed into your script from the server side is available
	 * by the mainController.local property inside your controller:
	 *
	 * > var ajaxurl = mainController.local.ajaxurl;
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
	var mainController = mwp.controller( 'mwp-studio', 
	{
		/**
		 * Initialization function
		 *
		 * @return	void
		 */
		init: function()
		{
			var self = this;
			$( 'html' ).addClass( 'mwp-bootstrap' );
			
			/**
			 * @var	Collection
			 */
			this.plugins = new Backbone.Collection( [], { model: Plugin } );
			
			/**
			 * View Model
			 */
			this.viewModel = 
			{
				plugins: kb.collectionObservable( this.plugins ),
				currentPlugin: ko.observable(),
				openFiles: ko.observableArray(),
				activeFile: ko.observable()
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

						while( currentNode instanceof CollectibleModel && currentNode.getParent() ) {
							breadcrumbs.unshift( currentNode.get('text') );
							currentNode = currentNode.getParent();
						}
						
						if ( currentNode instanceof FileTree ) {
							breadcrumbs.unshift( currentNode.plugin.get('slug') );
						}
					}
					
					return breadcrumbs.length ? breadcrumbs : [ 'No file open' ];
				})			
			});
			
			// Load available plugins and make the first one active
			this.loadPlugins().done( function() {
				self.viewModel.currentPlugin( self.viewModel.plugins()[0] );
			});
			
			// Refresh plugins when they become active
			this.viewModel.currentPlugin.subscribe( function( plugin ) {
				plugin.model().refreshStudio();
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
	 * [Model] Collector Model
	 */
	var CollectorModel = mwp.model( 'mwp-studio-collector',
	{
		/**
		 * @var	array			Collectible attributes
		 */
		collectibles: [],
		
		/**
		 * Define the collectible properties for this model
		 *
		 * @param	array		collectibles			An array of attributes to make collectible
		 * @return	void
		 */
		defineCollectibles: function( collectibles )
		{
			var self = this;
			self.collectibles = collectibles;
			
			$.each( self.collectibles, function( i, collectible )
			{
				var items = self.get( collectible.attribute );
				self[ collectible.attribute ] = self.createCollection( items, collectible.options );
				self.set( collectible.attribute, kb.collectionObservable( self[ collectible.attribute ] ) );			
			});
		},
		
		/**
		 * Create a new collection and associate it with this model
		 *
		 * @param	array			models			Initial models to go in collection
		 * @param	Model			ModelClass		The class of model that this collection will contain
		 * @return	Backbone.Collection
		 */
		createCollection: function( models, options )
		{
			var self = this;
			var collection = new Backbone.Collection( models, options );
			collection.associatedModel = this;
			collection.on( 'update sort updated', function() { self.trigger( 'updated' ); } );			
			this.off( null, this._onUpdated ).on( 'change updated', this._onUpdated );
			
			return collection;
		},
		
		/**
		 * Updated handler
		 *
		 * @return	void
		 */
		_onUpdated: function() {
			if ( this.collection && this.collection.associatedModel ) { 
				this.collection.trigger( 'updated' ); 
			}
		},

		/** 
		 * Return this model and its collections as json
		 *
		 * @return	object
		 */
		toJSON: function() 
		{
			var self = this;
			var json = CollectorModel.__super__.toJSON.apply( self );
			
			$.each( self.collectibles, function( i, collectible ) {
				json[ collectible.attribute ] = self[ collectible.attribute ].toJSON();
			});
			
			return json;
		}	
	
	});
	
	/**
	 * [Model] Collectible Model
	 *
	 * Mixin this class to gain access to child helpers such as getParent() and onceIn()
	 */
	var CollectibleModel = mwp.model( 'mwp-studio-collectible', 
	{
		/**
		 * Get the parent model that this models collection is associated with
		 *
		 * @param	function|Model		filter			first parent matching class will be returned, or if provided function returns true
		 * @return	Model
		 */
		getParent: function( filter )
		{
			if ( this.collection ) {
				var parent = this.collection.associatedModel;
				if ( parent != undefined ) {
					if ( filter ) {
						if ( filter.__super__ != undefined ) {
							if ( parent instanceof filter ) {
								return parent;
							}
						} else {
							if ( filter( parent ) ) {
								return parent;
							}
						}
						if ( parent instanceof CollectibleModel ) {
							return parent.getParent( filter );
						}
					} else {
						return parent;
					}
				}
			}
			
			return undefined;
		},
		
		/**
		 * Execute a callback only when associated with a particular type of parent
		 *
		 * @param	Model			model				Execute the callback when this collectible is a child of model
		 * @param	function		callback			The callback to execute
		 * @return	void
		 */
		onceIn: function( model, callback )
		{
			var _this = this;
			
			if ( _this instanceof model ) {
				callback( _this );
			}
			else
			{
				if ( _this.getParent() ) {
					_this.getParent().onceIn( model, callback );
				}
				else
				{
					_this.once( 'add', function() {
						_this.getParent().onceIn( model, callback );
					});
				}
			}
		}	
	});
	
	/**
	 * File sort comparator
	 *
	 * @param	Model		model1			One of two models to compare
	 * @param	Model		model2			Two to two models to compare
	 * @return	int							-1=model1 < model2, 0=model1 == model2, 1=model1 > model2
	 */
	var fileComparator = function( model1, model2 ) 
	{	
		var attr = model1.get('type') == model2.get('type') ? 'text' : 'type';
		var attr1 = model1.get(attr).toLowerCase(); var attr2 = model2.get(attr).toLowerCase();
		
		return attr1 === attr2 ? 0 : ( [ attr1, attr2 ].sort()[0] === attr1 ? -1 : 1 );
	};
	
	/**
	 * [Model] File Tree Node
	 *
	 * @var	string		type		File type (dir, file)
	 */
	var FileTree = mwp.model.set( 'mwp-studio-filetree', CollectorModel.extend(
	{
		/**
		 * Initialize
		 *
		 * @return	void
		 */
		initialize: function()
		{
			var self = this;
			this.defineCollectibles([
				{ attribute: 'nodes', options: { model: FileTreeNode, comparator: fileComparator } }
			]);
			
			this.filenodes = ko.observable( this.toJSON().nodes );
			this.on( 'updated', function(){ self.filenodes( self.toJSON().nodes ); } );
		}
	}));

	/**
	 * [Model] File Tree Node
	 *
	 * @var	string		type		File type (dir, file)
	 */
	var FileTreeNode = mwp.model.set( 'mwp-studio-filetree-node', CollectorModel.extend( CollectibleModel.prototype ).extend(
	{
		/**
		 * @var	object	Ace Editor
		 */
		editor: null,
		
		/**
		 * Initialize
		 *
		 * @return	void
		 */
		initialize: function()
		{
			var self = this;
			
			/**
			 * @var	kb.viewModel
			 */
			this.fileViewModel = kb.viewModel( self );

			/**
			 * @var	bool		File open for editing?
			 */
			this.open = ko.observable( false );
			
			/**
			 * @var	bool		File changed in editor?
			 */
			this.changed = ko.observable( false );
			
			/**
			 * @var	$.Deferred	File editor ready
			 */
			this.editorReady = $.Deferred();
			
			/**
			 * Collectible attributes
			 * @var	Collections
			 */
			if ( this.get('type') == 'dir' ) {
				this.defineCollectibles([
					{ attribute: 'nodes', options: { model: FileTreeNode, comparator: fileComparator } }
				]);
			}
			
			// Listeners
			
			/**
			 * File selected in tree view
			 *
			 * @param	object		event			The event details
			 * @param	Treeview	tree			The treeview instance
			 * @param	object		node			The treeview node
			 * @return	void
			 */
			this.on( 'nodeSelected', function( event, tree, node ) {
				$.when( node.model.switchTo() ).then( function() {
					setTimeout( function() {
						tree.treeview(true).unselectNode( node.nodeId );
					}, 500 );
				});
			});
		},
		
		/**
		 * Open file in editor
		 *
		 * @return	$.Deferred
		 */
		openFile: function()
		{
			if ( ! this.open() ) 
			{
				mainController.viewModel.openFiles.push( this.fileViewModel );
				this.open(true);
			}
			
			// Return deferred
			return this.editorReady;
		},
		
		/**
		 * Retrieve the file content from the backend
		 *
		 * @return	$.Deferred
		 */
		getContent: function()
		{
			var self = this;
			
			return $.ajax({
				method: 'post',
				url: mainController.local.ajaxurl,
				data: { 
					action: 'mwp_studio_get_file_content',
					path: self.get('path')
				}
			});			
		},

		/**
		 * Save the file
		 *
		 * @return	$.Deferred
		 */
		saveFile: function() 
		{
			var self = this;
			
			if ( this.editor ) 
			{
				return $.ajax({
					method: 'post',
					url: mainController.local.ajaxurl,
					data: { 
						action: 'mwp_studio_save_file_content',
						path: self.get('path'),
						content: this.editor.getValue()
					}
				}).done( function() 
				{
					self.changed(false);
				});
			}
			
			return $.Deferred();
		},
		
		/**
		 * Close the file in editor
		 *
		 * @return	void
		 */
		closeFile: function()
		{
			if ( this.open() ) 
			{
				if ( this.changed() ) {
					// Ask to save file first
				}
				
				this.editorReady = $.Deferred();
				this.editor.destroy();
				this.editor = null;
				mainController.viewModel.openFiles.remove( this.fileViewModel );
				this.open(false);
			}
		},
		
		/**
		 * Switch to / open file in the editor
		 *
		 * @return	$.Deferred
		 */
		switchTo: function()
		{
			return $.when( this.openFile() ).then( function( editor, options ) {
				if ( typeof options.switchTo == 'function' ) {
					options.switchTo(); 
				}
			});
		},
		
		/**
		 * Get the root directory inside the plugin for this node
		 *
		 * @param	int			level		The sublevel of the directory to check
		 * @return	string
		 */
		rootDir: function( level ) 
		{
			var dirs = [];
			var level = level || 0;
			var currentNode = this;
			while( currentNode instanceof FileTreeNode ) {
				dirs.unshift( currentNode.get('text') );
				currentNode = currentNode.getParent();
			}
			
			return dirs[level];
		},
		
		/** 
		 * Return this model and its collections as json
		 *
		 * @return	object
		 */
		toJSON: function() 
		{
			var json = FileTreeNode.__super__.toJSON.apply( this );
			json.model = this;
			
			return json;
		}
	},
	{
		/**
		 * Context Menu Options
		 */
		contextMenu: 
		{	
			/**
			 * Get the context node
			 *
			 * @param	domElement		el		Clicked dom element
			 * @return	object
			 */
			fetchElementData: function( el ) {
				return $(el).closest('.treeview').treeview('getNode', $(el).data('nodeid'));
			},
			
			// Context menu actions
			actions: 
			{
				/**
				 * Edit the file in the editor
				 */
				editFile:
				{
					name: 'Edit File',
					iconClass: 'fa-pencil',
					onClick: function( node ) {
						node.model.switchTo();
					},
					isShown: function( node ) {
						return node.selectable;
					}
				},
				
				/**
				 * Add new view template
				 */
				addTemplate: 
				{
					name: 'Add Template',
					iconClass: 'fa-code',
					onClick: function( node ) {
					
					},
					isShown: function( node ) {
						return node.model.rootDir(0) == 'templates';
					}
				},
				
				/**
				 * Add new css resource
				 */
				addCSS: 
				{
					name: 'Add Stylesheet',
					iconClass: 'fa-file-code-o',
					onClick: function( node ) {
					
					},
					isShown: function( node ) {
						var file = node.model;
						return file.rootDir(0) == 'assets' && ( file.rootDir(1) === undefined || file.rootDir(1) == 'css' );
					}
				},

				/**
				 * Add new javascript module
				 */
				addJS: 
				{
					name: 'Add Javascript',
					iconClass: 'fa-file-code-o',
					onClick: function( node ) {
					
					},
					isShown: function( node ) {
						var file = node.model;
						return file.rootDir(0) == 'assets' && ( file.rootDir(1) === undefined || file.rootDir(1) == 'js' );
					}
				},
				
				/**
				 * Add new php class
				 */
				addClass: 
				{
					name: 'Add Class',
					iconClass: 'fa-file-code-o',
					onClick: function( node ) {
					
					},
					isShown: function( node ) {
						var file = node.model;
						return file.rootDir(0) == 'classes';
					}
				}
			}
		}
	}));
	
	/**
	 * [Model] Plugin
	 *
	 * @var	string		name		Plugin name
	 * @var	string		slug		Plugin slug
	 */
	var Plugin = mwp.model( 'mwp-studio-plugin',
	{
		
		/**
		 * Initialize
		 *
		 * @return	void
		 */
		initialize: function()
		{
			/**
			 * @var	Tree
			 */
			this.fileTree = new FileTree();
			this.fileTree.plugin = this;
			this.set( 'filetree', kb.viewModel( this.fileTree ) );
			this.set( 'filenodes', this.fileTree.filenodes );			
		},
		
		/**
		 * Switch to this plugin as the active studio plugin
		 * 
		 * @return	void
		 */
		switchToPlugin: function()
		{
			var i = this.collection.indexOf( this );
			mainController.viewModel.currentPlugin( mainController.viewModel.plugins()[ i ] );
		},
		
		/**
		 * Prepare plugin to become the active studio plugin
		 *
		 * @return	void
		 */
		refreshStudio: function()
		{
			// Load the most current file tree
			this.fetchFileTree();
		},
		
		/**
		 * Fetch the most recent file tree
		 *
		 * @return	$.Deferred
		 */
		fetchFileTree: function()
		{
			var self = this;
			
			return $.ajax({
				method: 'post',
				url: mainController.local.ajaxurl,
				data: { 
					action: 'mwp_studio_fetch_filetree',
					plugin: self.get('slug')
				}
			})
			.done( function( data ) {
				if ( data.nodes ) {
					self.fileTree.nodes.set( data.nodes );
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
					file.getContent().done( function( data ) {
						editor.setValue( data.content );
						editor.getSession().setUndoManager(new ace.UndoManager())
						editor.gotoLine(1);
						file.changed(false);
						file.editor = editor;
						file.editorReady.resolve( editor, options );
					});
					
					// Track file changes
					editor.on( 'change', function() {
						file.changed(true);
					});

					// Track the currently active editor
					editor.on( 'focus', function() {
						mainController.viewModel.activeFile( fileview );
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
		}
		
	});
	
})( jQuery );
 