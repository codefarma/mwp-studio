/**
 * Plugin Javascript Module
 *
 * Created     August 8, 2017
 *
 * @package    Wordpress Plugin Studio
 * @author     Kevin Carwile
 * @since      {build_version}
 */

/**
 * Studio Models
 */
(function( $, undefined ) {
	
	"use strict";
	
	var studioController;
	
	mwp.on( 'mwp-studio.ready', function( controller ) {
		studioController = controller;
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
			 * @var	bool		File is in a conflicting state
			 */
			this.conflicted = ko.observable( false );
			
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
			
			/**
			 * Ask user to resolve conflicts immediately in a focused editor
			 *
			 * @param	bool		conflicted			Whether the file is conflicted or not
			 * @return	void
			 */
			this.conflicted.subscribe( function( conflicted ) {
				if ( conflicted ) {
					if ( studioController.viewModel.activeFile() && studioController.viewModel.activeFile().id() == self.get('id') ) {
						self.resolveConflict();
					}
				}
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
				// Pushing onto open files causes editor to open the file
				studioController.viewModel.openFiles.push( this.fileViewModel );
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
				url: studioController.local.ajaxurl,
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
					url: studioController.local.ajaxurl,
					data: { 
						action: 'mwp_studio_save_file_content',
						path: self.get('path'),
						content: this.editor.getValue()
					}
				}).done( function( response ) 
				{
					if ( response.success ) {
						self.conflicted( false );
						self.edited( false );
						self.set( 'modified', response.modified );
					}
				});
			}
			
			return $.Deferred().promise();
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
					var viewModel = studioController.viewModel;
					var tabindex = viewModel.openFiles.indexOf( self.fileViewModel );
					self.editorReady = $.Deferred();
					self.editor.destroy();
					self.editor = null;
					viewModel.openFiles.remove( self.fileViewModel );
					self.open(false);
					self.conflicted(false);
					self.edited(false);
					
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
		 * Synchronize with disk, check if file has been modified
		 *
		 *	@return	$.Deferred		
		 *		@param	bool	True if file is synced, false if it is in conflict
		 */
		checkSync: function()
		{
			var self = this;
			
			var check = $.Deferred();
			
			$.ajax({
				url: studioController.local.ajaxurl,
				method: 'post',
				data: {
					action: 'mwp_studio_sync_file',
					path: self.get('path')
				}
			})
			.then( function( response ) {
				if ( response.success ) {
					if ( self.get('modified') < response.modified ) {
						self.conflicted( true );
						check.resolve( false );
					}
					else
					{
						self.conflicted( false );
						check.resolve( true );
					}
				}
			});
			
			return check.promise();
		},
		
		/**
		 * Resolve a conflicting state between the editor and the file on disk
		 *
		 * @return	$.Deferred
		 *   @param		bool		Dialog response
		 */
		resolveConflict: function()
		{
			var self = this;
			var response = $.Deferred();
			
			var message = self.get('name') + ' has been modified on the disk. Do you want to reload it?';
			if ( self.edited() ) {
				message = message + ' If you do, you will lose your unsaved changes from this editor.';
			}
			
			bootbox.confirm({
				size: 500,
				title: 'File has been modified',
				message: message,
				callback: function( yes ) {
					if ( yes ) {
						self.reloadFile();
					}
					response.resolve( yes );
				}
			});
			
			return response;
		},
		
		/**
		 * Reload the file in the editor
		 *
		 * @return	$.Deferred
		 */
		reloadFile: function() 
		{
			var self = this;
			
			return $.when( self.getContent() ).then( function( response ) {
				if ( response.success ) {
					self.set( 'modified', response.modified );
					self.conflicted( false );
					if ( self.editor ) {
						self.editor.setValue( response.content );
						self.editor.getSession().setUndoManager( new ace.UndoManager() )
						self.editor.gotoLine(1);
						self.edited( false );
					}
				}
			});
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
						studioController.addClassDialog( node.model.getParent( FileTree ).plugin, suggestedNamespace );
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
						studioController.addTemplateDialog( node.model.getParent( FileTree ).plugin, suggestedNamespace );
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
						studioController.addCSSDialog( node.model.getParent( FileTree ).plugin );					
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
						studioController.addJSDialog( node.model.getParent( FileTree ).plugin );
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
			this.studio = studioController.interfaces.get(this.get('framework')) || studioController.interfaces.get('generic');
			
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
			studioController.viewModel.currentPlugin( studioController.viewModel.plugins()[ i ] );
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
				url: studioController.local.ajaxurl,
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
					_.each( studioController.viewModel.openFiles(), function( file ) 
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
	
})( jQuery );
 