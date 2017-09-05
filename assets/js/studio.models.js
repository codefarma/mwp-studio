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
	
	var studio;
	
	mwp.on( 'mwp-studio.ready', function( controller ) {
		studio = controller;
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
		 * Set collections directly when using the set method on collectibles
		 *
		 * @param	object|string		attributes			An attribute name or hash of attributes to set
		 * @param	mixed				options				The attribute value if providing attribute name as string
		 * @return	void
		 */
		set: function( attributes, options ) 
		{
			var self = this;
			var collectibleAttributes = _.map( this._collectibles, function( collectible ) { return collectible.attribute; } );
			
			// Set any collections and remove the attribute
			if ( typeof attributes == 'object' ) 
			{
				$.each( attributes, function( key, value ) {
					if ( _.contains( collectibleAttributes, key ) ) {
						self[ key ].set( value );
						delete attributes[ key ];
					}
				});
			}
			else
			{
				if ( _.contains( collectibleAttributes, attributes ) ) {
					self[ attributes ].set( options );
					return;
				}
			}

			return Backbone.Model.prototype.set.call( this, attributes, options );
		},

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
				var observableCollection = kb.collectionObservable( collection );			
				self[ collectible.attribute ] = collection;
				self.attributes[ collectible.attribute ] = observableCollection;
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
					if ( typeof model.findChild == 'function' && model[attribute] instanceof Backbone.Collection ) {
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
						if ( typeof parent.getParent == 'function' ) {
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
			this.loading = ko.observable( false );
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
		 * Constructor
		 *
		 * @return	object
		 */
		constructor: function( attributes )
		{
			if ( attributes && attributes.id ) {
				var node = FileTreeNode.cache.get( attributes.id );
				if ( node ) {
					node.set( attributes );
					return node;
				}
			}
			
			Backbone.Model.apply( this, arguments );
			FileTreeNode.cache.add( this );
		},
		
		/**
		 * Initialize
		 *
		 * @return	void
		 */
		initialize: function()
		{
			var self = this;
			
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
				$.when( node.model.switchTo() ).done( function() {
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
					if ( studio.viewModel.activeFile() && studio.viewModel.activeFile().id() == self.get('id') ) {
						self.resolveConflict();
					}
				}
			});
		},
		
		/**
		 * Lazy create file view models to improve performance and memory footprint
		 *
		 * @return	kb.viewModel
		 */
		getFileViewModel: function()
		{
			if ( this.fileViewModel === undefined ) {
				this.fileViewModel = kb.viewModel( this );
			}
			
			return this.fileViewModel;
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
				studio.viewModel.openFiles.push( this.getFileViewModel() );
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
				url: studio.local.ajaxurl,
				data: { 
					action: 'mwp_studio_get_file_content',
					filepath: self.get('path')
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
					url: studio.local.ajaxurl,
					data: { 
						action: 'mwp_studio_save_file_content',
						filepath: self.get('path'),
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
					var tabindex = studio.viewModel.openFiles.indexOf( self.getFileViewModel() );
					self.editorReady = $.Deferred();
					self.editor.destroy();
					self.editor = null;
					studio.viewModel.openFiles.remove( self.getFileViewModel() );
					self.open(false);
					self.conflicted(false);
					self.edited(false);
					
					// If we're closing the active file, we need to pick a new active file
					if ( studio.viewModel.activeFile() === self.getFileViewModel() )
					{
						var openFileCount = studio.viewModel.openFiles().length;
						
						if ( openFileCount == 0 ) {
							studio.viewModel.activeFile( null );
						}
						else
						{
							// pick the new file in the same index position, or the last file
							tabindex = tabindex >= openFileCount ? openFileCount - 1 : tabindex;
							studio.viewModel.openFiles()[tabindex].model().switchTo();
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
		 * Reindex file
		 *
		 * @return	$.Deferred
		 */
		reindex: function()
		{
			var self = this;
			return $.ajax({
				url: studio.local.ajaxurl,
				data: { action: 'mwp_studio_rebuild_catalog', path: self.get('path') }
			});
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
				url: studio.local.ajaxurl,
				method: 'post',
				data: {
					action: 'mwp_studio_sync_file',
					filepath: self.get('path')
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
				
				return response;
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
				
				return response;
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
			return $.when( this.openFile() ).done( function( editor, options ) {
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
		 * Multiton Cache
		 */
		cache: new Backbone.Collection(),
		
		/**
		 * Load a file given a path
		 *
		 * @param	string			filepath			The path of the file to load
		 * @return	$.Deferred
		 */
		loadFile: function( filepath )
		{
			var deferredFile = $.Deferred();
			var file = FileTreeNode.cache.findWhere({ path: filepath });
			
			if ( file ) {
				deferredFile.resolve( file );
			}
			else
			{
				$.ajax({
					url: studio.local.ajaxurl,
					data: {
						action: 'mwp_studio_load_file',
						filepath: filepath
					}
				}).done( function( response ) {
					if ( response.file ) {
						deferredFile.resolve( new FileTreeNode( response.file ) );
					} else {
						deferredFile.reject();
					}
				});
			}
			
			return deferredFile.promise();
		},
		
		/**
		 * Load a file given a callback
		 *
		 * @param	string				name			The callback name
		 * @param	string|undefined	class			The callback class
		 * @return	$.Deferred
		 */
		loadCallbackFile: function( callback_name, callback_class )
		{
			var deferredFile = $.Deferred();
			
			$.ajax({
				url: studio.local.ajaxurl,
				data: {
					action: 'mwp_studio_get_function',
					callback_name: callback_name,
					callback_class: callback_class
				}
			}).done( function( response ) {
				if ( response.callback ) {
					FileTreeNode.loadFile( response.callback.function_file ).done( function( file ) {
						deferredFile.resolve( file, response.callback );
					});
				} else {
					deferredFile.reject();
				}
			});

			return deferredFile.promise();
		}
		
	}));
	
	/**
	 * [Model] Menu Item
	 *
	 * @var	string		type			header,action,divider,submenu,dropdown
	 * @var string		icon			Icon class
	 * @var string		title			Menu item title
	 * @var	string		classes			Element classes
	 * @var	function	callback		Click handler
	 */
	var MenuItem = mwp.model.set( 'mwp-studio-menu-item', CollectorModel.extend( CollectibleModel.prototype ).extend(
	{
		/**
		 * Initialize
		 *
		 * @return	void
		 */
		initialize: function()
		{
			var self = this;
			
			this.set( 'type', this.get('type') || 'action' );
			this.set( 'icon', this.get('icon') || '' );
			this.set( 'title', this.get('title') || '' );
			this.set( 'classes', this.get('classes') || '' );
			this.set( 'callback', this.get('callback') || function(){} );
			
			/**
			 * Collectible attributes
			 * @var	Collections
			 */
			this.defineCollectibles([
				{ attribute: 'subitems', options: { model: MenuItem } }
			]);
		},
		
		/**
		 * Get a the html dom as a jquery object
		 *
		 * @return	jQuery
		 */
		getHtmlDom: function()
		{
			var type = this.get('type');
			var template = $( studio.local.templates.menus[ type ] );
			
			ko.applyBindings( kb.viewModel( this ), template[0] );
			
			return template;
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
			var self = this;
			
			this.env = studio.environments.get( this.get('environment') ) || 
				studio.environments.get('generic');
			
			this.fileTree = _.extend( new FileTree(), {
				plugin: this,
				initialized: false
			});

			this.actions = ko.observableArray([]).extend({ progressiveFilter: { batchSize: 50 }, rateLimit: 50 });
			this.filters = ko.observableArray([]).extend({ progressiveFilter: { batchSize: 50 }, rateLimit: 50 });
			this.shortcodes = ko.observableArray([]);
			this.posttypes = ko.observableArray([]);
			
			this.actions.loading = ko.observable(false);
			this.filters.loading = ko.observable(false);
			
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
			studio.viewModel.currentPlugin( studio.viewModel.plugins()[ i ] );
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
			this.fetchCatalogItems('actions');
		},
		
		/**
		 * Fetch the most recent file tree
		 *
		 * @return	$.Deferred
		 */
		fetchFileTree: function()
		{
			var self = this;
			
			this.fileTree.loading( true );
			
			return $.ajax({
				method: 'post',
				url: studio.local.ajaxurl,
				data: { 
					action: 'mwp_studio_fetch_filetree',
					dirpath: self.get('basedir')
				}
			})
			.done( function( data ) {
				if ( data.nodes ) {
					self.fileTree.nodes.reset();
					self.fileTree.nodes.set( data.nodes );
					self.fileTree.initialized = true;					
					self.fileTree.loading( false );
				}
			});
		},
		
		/**
		 * Fetch items from the catalog
		 *
		 * @param	string		datatype			The type of items to fetch
		 * @return	$.Deferred
		 */
		fetchItemCatalog: function( datatype )
		{
			var self = this;
			
			switch( datatype ) {
				case 'actions':	this.actions.loading(true); break;
				case 'filters': this.filters.loading(true); break;
			}
			
			return $.ajax({
				method: 'post',
				url: studio.local.ajaxurl,
				data: { 
					action: 'mwp_studio_load_catalog_items',
					basepath: self.get('basedir'),
					datatype: datatype
				}
			})
			.done( function( response ) {
				if ( response.success ) {
					switch( datatype ) {
						case 'actions':
							self.actions.loading(false);
							self.actions.filterProgressive( response.results );
							break;
							
						case 'filters':
							self.filters.loading(false);
							self.filters.filterProgressive( response.results );
							break;
					}
				}
			});
		}
	});
	
})( jQuery );
 