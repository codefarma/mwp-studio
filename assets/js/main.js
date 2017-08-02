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
				controller: this,
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
							breadcrumbs.unshift( currentNode.plugin.get('basedir') );
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
		},
		
		/**
		 * Add a new php class
		 *
		 * @param	object		node		The plugin to add the class to
		 * @param	string		namespace	Optional suggested namespace
		 * @return	$.Deferred
		 */
		addClassDialog: function( plugin, namespace )
		{
			var self = this;
			var plugin = plugin || self.viewModel.currentPlugin().model();
			
			var viewModel = {
				plugin: kb.viewModel( plugin ),
				classname: ko.observable( namespace )
			};
			
			var dialogTemplate = $('#studio-tmpl-class-form').html();
			var dialogContent = $( dialogTemplate ).wrap( '<div>' ).parent();
			
			return this.createDialog( 'Class', dialogContent, viewModel, function() { 
				if ( ! viewModel.classname() ) { return false; }
				return self.createClass( plugin.get('slug'), viewModel.classname() ); 
			}, { size: 500 });
		},
		
		/**
		 * Add a new html template
		 *
		 * @param	object		node		The plugin to add the class to
		 * @param	string		namespace	Optional suggested base path
		 * @return	$.Deferred
		 */
		addTemplateDialog: function( plugin, filepath )
		{
			var self = this;
			var plugin = plugin || self.viewModel.currentPlugin().model();
			
			var viewModel = {
				plugin: kb.viewModel( plugin ),
				filepath: ko.observable( filepath )
			};
			
			var dialogTemplate = $('#studio-tmpl-template-form').html();
			var dialogContent = $( dialogTemplate ).wrap( '<div>' ).parent();
			
			return this.createDialog( 'Template', dialogContent, viewModel, function() { 
				if ( ! viewModel.filepath() ) { return false; }
				return self.createTemplate( plugin.get('slug'), viewModel.filepath() ); 
			}, { size: 500 });
		},
		
		/**
		 * Add a new css file dialog
		 *
		 * @param	object		node		The plugin to add the file to
		 * @param	string		filename	Optional suggested filename
		 * @return	$.Deferred
		 */
		addCSSDialog: function( plugin, filename )
		{
			var self = this;
			var plugin = plugin || self.viewModel.currentPlugin().model();
			
			var viewModel = {
				plugin: kb.viewModel( plugin ),
				filename: ko.observable( filename )
			};
			
			var dialogTemplate = $('#studio-tmpl-stylesheet-form').html();
			var dialogContent = $( dialogTemplate ).wrap( '<div>' ).parent();
			
			return this.createDialog( 'Stylesheet File', dialogContent, viewModel, function() { 
				if ( ! viewModel.filename() ) { return false; }
				return self.createCSS( plugin.get('slug'), viewModel.filename() ); 
			}, { size: 500 });
		},
		
		/**
		 * Add a new javascript file dialog
		 *
		 * @param	object		node		The plugin to add the file to
		 * @param	string		filename	Optional suggested filename
		 * @return	$.Deferred
		 */
		addJSDialog: function( plugin, filename )
		{
			var self = this;
			var plugin = plugin || self.viewModel.currentPlugin().model();
			
			var viewModel = {
				plugin: kb.viewModel( plugin ),
				filename: ko.observable( filename )
			};
			
			var dialogTemplate = $('#studio-tmpl-javascript-form').html();
			var dialogContent = $( dialogTemplate ).wrap( '<div>' ).parent();
			
			return this.createDialog( 'Javascript Module', dialogContent, viewModel, function() { 
				if ( ! viewModel.filename() ) { return false; }
				return self.createJS( plugin.get('slug'), viewModel.filename() ); 
			}, { size: 500 });
		},
		
		/**
		 * Show a dialog
		 *
		 * @param	string		title			The title of the item being created
		 * @param	jQuery		dialogContent	The jquery wrapped dialog content
		 * @param	function	creator			The creator function that creates the resource
		 * @param	object		extraOptions	Any extra options to pass to the bootbox dialog
		 * @return	$.Deferred
		 */
		createDialog: function( title, dialogContent, viewModel, creator, extraOptions )
		{
			var dialogInteraction = $.Deferred();
			var plugin = viewModel.plugin.model() || self.viewModel.currentPlugin().model();
			var dialog;
			
			viewModel.enterKeySubmit = function( data, event ) {
				if ( event.which == 13 ) {
					dialog.modal('hide');
					opts.buttons.ok.callback();
				}
				return true;
			}
			
			ko.applyBindings( viewModel, dialogContent[0] );
			
			var opts = {
				size: 'large',
				title: 'Add New ' + title,
				message: dialogContent,
				buttons: 
				{
					cancel: {
						'label': 'Cancel',
					},
					
					ok: {
						label: 'Create ' + title,
						className: 'btn-success',
						callback: function() {
							$.when( creator() ).done( function( response ) 
							{
								if ( response.success ) 
								{
									// Look for existing parent node
									var parent = plugin.fileTree.findChild( 'nodes', function( node ) {
										return node.get('id') === response.file.parent_id;
									});
									
									if ( parent ) 
									{
										// Possible it exists if the file was deleted on the host but remained
										// open in the editor
										var file = parent.nodes.get( response.file.id );
										
										if ( ! file ) {
											// Attach to existing parent. Easy.
											file = new FileTreeNode( response.file );
											parent.nodes.add( file );
										}
										else
										{
											file.edited(true);
										}
										
										file.switchTo(true);
										dialogInteraction.resolve( file );
									}
									else
									{
										// Refresh the whole file tree and then find the correct file. 
										$.when( plugin.fetchFileTree() ).done( function() {
											var file = plugin.fileTree.findChild( 'nodes', function( node ) {
												return node.get('id') === response.file.id;
											});
											
											if ( file ) 
											{
												file.switchTo(true);
												dialogInteraction.resolve( file );
											}
										});
									}									
								}
								else if ( response.success === false ) {
									bootbox.alert({
										size: 'Small',
										title: 'Add New ' + title + ' Failed',
										message: response.message
									});
								}
							});
						}
					}
				}
			};

			extraOptions = extraOptions || {};
			dialog = bootbox.dialog( _.extend( opts, extraOptions ) );
			
			return dialogInteraction;		
		},
		
		/**
		 * Create a new php class
		 * 
		 * @param	string			plugin			Plugin slug
		 * @param	string			classname		Classname to create
		 * @return	$.Deferred
		 */
		createClass: function( plugin, classname )
		{
			return $.ajax({
				url: this.local.ajaxurl,
				data: {
					action: 'mwp_studio_add_class',
					plugin: plugin,
					classname: classname
				}
			});
		},
	
		/**
		 * Create a new php template
		 * 
		 * @param	string			plugin			Plugin slug
		 * @param	string			template		Template to create
		 * @return	$.Deferred
		 */
		createTemplate: function( plugin, template )
		{
			return $.ajax({
				url: this.local.ajaxurl,
				data: {
					action: 'mwp_studio_add_template',
					plugin: plugin,
					template: template
				}
			});
		},
		
		/**
		 * Create a new css file
		 * 
		 * @param	string			plugin			Plugin slug
		 * @param	string			filename		Filename to create
		 * @return	$.Deferred
		 */
		createCSS: function( plugin, filename )
		{
			return $.ajax({
				url: this.local.ajaxurl,
				data: {
					action: 'mwp_studio_add_css',
					plugin: plugin,
					filename: filename
				}
			});
		},

		/**
		 * Create a new javascript file
		 * 
		 * @param	string			plugin			Plugin slug
		 * @param	string			filename		Filename to create
		 * @return	$.Deferred
		 */
		createJS: function( plugin, filename )
		{
			return $.ajax({
				url: this.local.ajaxurl,
				data: {
					action: 'mwp_studio_add_js',
					plugin: plugin,
					filename: filename
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
		 * @var	array			Collectible attribute definitions
		 */
		_collectibles: [],
		
		/**
		 * Define the collectible properties for this model
		 *
		 * @param	array		collectibles			An array of attributes to make collectible
		 * @return	void
		 */
		defineCollectibles: function( collectibles )
		{
			var self = this;
			self._collectibles = collectibles;
			
			$.each( self._collectibles, function( i, collectible )
			{
				var items = self.get( collectible.attribute );
				var collection = self.createCollection( items, collectible.options );
				var observable = kb.collectionObservable( collection );			
				self[ collectible.attribute ] = collection;
				self.set( collectible.attribute, observable );
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
		 * Find a child node
		 *
		 * @param	string		attribute	The child attribute to search in
		 * @param	function	test		A function to test for the correct child to return
		 * @return	FileTreeNode|null
		 */
		findChild: function( attribute, test )
		{
			var self = this;
			var found = null;
			
			if ( self[attribute] instanceof Backbone.Collection ) {
				$.each( self[attribute].models, function( i, model ) {
					if ( test(model) ) {
						found = model;
						return false;
					}
					if ( model instanceof CollectorModel && model[attribute] instanceof Backbone.Collection ) {
						found = model.findChild( attribute, test );
						if ( found ) {
							return false;
						}
					}
				});
			}
			
			return found;
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
			
			$.each( self._collectibles, function( i, collectible ) {
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
			this.edited = ko.observable( false );
			
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
					self.edited(false);
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
			var self = this;

			if ( this.open() ) 
			{
				var closeit = function() 
				{
					var viewModel = mainController.viewModel;
					var tabindex = viewModel.openFiles.indexOf( self.fileViewModel );
					self.editorReady = $.Deferred();
					self.editor.destroy();
					self.editor = null;
					viewModel.openFiles.remove( self.fileViewModel );
					self.open(false);
					
					// If we're closing the active file, we need to pick a new active file
					if ( viewModel.activeFile() === self.fileViewModel )
					{
						var openFileCount = viewModel.openFiles().length;
						
						if ( openFileCount == 0 ) {
							viewModel.activeFile( null );
						}
						else
						{
							// pick the new file in the same index position, or the last file
							tabindex = tabindex >= openFileCount ? openFileCount - 1 : tabindex;
							viewModel.openFiles()[tabindex].model().switchTo();
						}
					}
				};
				
				if ( this.edited() ) 
				{
					bootbox.dialog( {
						size: 'large',
						title: 'Close File: ' + self.get('text'),
						message: "This file has been edited. Do you want to save it before closing?",
						buttons: {
							cancel: {
								label: 'Cancel',
								className: 'btn-default'
							},
							no: {
								label: 'No',
								className: 'btn-danger',
								callback: closeit
							},
							yes: {
								label: 'Yes',
								className: 'btn-success',
								callback: function() { $.when( self.saveFile() ).done( closeit ); }
							}
						}
					});
				}
				else
				{
					closeit();
				}
			}
		},
		
		/**
		 * Switch to / open file in the editor
		 *
		 * @param	bool		reveal			Reveal the file in the treeview
		 * @return	$.Deferred
		 */
		switchTo: function( reveal )
		{
			var self = this;
			return $.when( this.openFile() ).then( function( editor, options ) {
				if ( typeof options.switchTo == 'function' ) {
					options.switchTo(); 
				}
				if ( reveal ) {
					var treeview = $('.mwp-studio .file-treeview .treeview').treeview(true);
					if ( typeof treeview.search == 'function' ) {
						treeview.search( self.get('text'), { exactMatch: true, ignoreCase: false, revealResults: true } );
						setTimeout( function() { treeview.clearSearch(); }, 3000 );
					}
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
				 * Add new php class
				 */
				addClass: 
				{
					name: 'Create New Class',
					iconClass: 'fa-file-code-o',
					onClick: function( node ) {
						var model = node.model;
						var namespaces = [];
						while( model.get('name') !== 'classes' ) { 
							namespaces.unshift( model.get('name') );
							model = model.getParent();
						}
						
						var suggestedNamespace = namespaces.length ? namespaces.join('\\') + '\\' : '';
						mainController.addClassDialog( node.model.getParent( FileTree ).plugin, suggestedNamespace );
					},
					isShown: function( node ) {
						var file = node.model;
						var plugin = file.getParent( FileTree ).plugin;
						return file.get('type') == 'dir' 
							&& file.rootDir(0) == 'classes' 
							&& plugin.get('framework') == 'mwp';
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
						var model = node.model;
						var namespaces = [];
						while( model.get('name') !== 'templates' && model instanceof CollectibleModel ) { 
							namespaces.unshift( model.get('name') );
							model = model.getParent();
						}
						
						console.log( namespaces );
						var suggestedNamespace = namespaces.length ? namespaces.join('/') + '/' : '';
						mainController.addTemplateDialog( node.model.getParent( FileTree ).plugin, suggestedNamespace );
					},
					isShown: function( node ) {
						var file = node.model;
						var plugin = file.getParent( FileTree ).plugin;
						return file.get('type') == 'dir' 
							&& node.model.rootDir(0) == 'templates' 
							&& plugin.get('framework') == 'mwp';
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
						mainController.addCSSDialog( node.model.getParent( FileTree ).plugin );					
					},
					isShown: function( node ) {
						var file = node.model;
						var plugin = file.getParent( FileTree ).plugin;
						return file.rootDir(0) == 'assets' 
							&& ( file.rootDir(1) === undefined || file.rootDir(1) == 'css' ) 
							&& plugin.get('framework') == 'mwp';
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
						mainController.addJSDialog( node.model.getParent( FileTree ).plugin );
					},
					isShown: function( node ) {
						var file = node.model;
						var plugin = file.getParent( FileTree ).plugin;
						return file.rootDir(0) == 'assets' 
							&& ( file.rootDir(1) === undefined || file.rootDir(1) == 'js' ) 
							&& plugin.get('framework') == 'mwp';
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
					plugin: self.get('basedir')
				}
			})
			.then( function( data ) {
				if ( data.nodes ) {
					self.fileTree.nodes.reset();
					self.fileTree.nodes.set( data.nodes );
					
					// Merge in open files
					_.each( mainController.viewModel.openFiles(), function( file ) 
					{
						var parent = self.fileTree.findChild( 'nodes', function( node ) { 
							return node.get('id') == file.parent_id(); 
						});
						
						if ( parent ) {
							file.model().collection = undefined;
							parent.nodes.remove( file.id() );
							parent.nodes.add( file.model() );
						}
					});
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
						file.edited(false);
						file.editor = editor;
						file.editorReady.resolve( editor, options );
					});
					
					// Track file changes
					editor.on( 'change', function() {
						file.edited(true);
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
 