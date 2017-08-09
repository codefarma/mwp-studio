/**
 * Plugin Javascript Module
 *
 * Created     August 8, 2017
 *
 * @package    Wordpress Plugin Studio
 * @author     Kevin Carwile
 * @since      {build_version}
 */

(function( $, undefined ) {
	
	"use strict";

	var studioController;
	
	mwp.on( 'mwp-studio.ready', function( studio ) {
		studioController = studio;
	});
	
	mwp.on( 'mwp-studio.init', function( studio ) {
		studio.interfaces.add( new MWPInterface({ id: 'mwp' }) );
	});
	
	var CollectorModel = mwp.model.get( 'mwp-studio-collector' );
	var CollectibleModel = mwp.model.get( 'mwp-studio-collectible' );
	
	/**
	 * [Model] Base Framework
	 *
	 * This class should be extended to provide framework specific support and
	 * studio elements for projects
	 */
	var GenericInterface = mwp.model.set( 'mwp-studio-generic-interface', CollectorModel.extend(
	{
		/**
		 * Initialize
		 *
		 * @return	void
		 */
		initialize: function()
		{
			var self = this;

			/**
			 * Studio menu elements
			 */
			this.studioMenuElements = ko.computed( function() {
				var plugin = studioController.viewModel.currentPlugin();
				return plugin ? self.getStudioMenuElements( plugin.model() ) : [];
			});
			
			/**
			 * Plugin menu elements
			 */
			this.pluginMenuElements = ko.computed( function() {
				var plugin = studioController.viewModel.currentPlugin();
				return plugin ? self.getPluginMenuElements( plugin.model() ) : [];
			});
			
			/**
			 * File context actions
			 */
			this.fileContextActions = ko.computed( function() {
				var plugin = studioController.viewModel.currentPlugin();
				return plugin ? self.getFileContextActions( plugin.model() ) : [];
			});
		},
		
		/**
		 * Get plugin menu elements
		 * 
		 * @param	Plugin		plugin			The plugin to get menu items for
		 * @return	array
		 */
		getStudioMenuElements: function( plugin )
		{
			return [];
		},
		
		/**
		 * Get plugin menu elements
		 * 
		 * @param	Plugin		plugin			The plugin to get menu items for
		 * @return	array
		 */
		getPluginMenuElements: function( plugin )
		{
			return [{
				title: 'Meta Information',
				type: 'header'
			},
			{
				title: 'Edit Plugin Info',
				type: 'action',
				icon: 'fa fa-info-circle',
				callback: function() {
				
				}
			}];
		},
		
		/**
		 * Get file context actions
		 *
		 * @param	Plugin		plugin			The plugin to get file context menus for
		 * @return	object
		 */
		getFileContextActions: function( plugin )
		{
			return {
			
				/**
				 * Get the context node
				 *
				 * @param	domElement		el		Clicked dom element
				 * @return	object
				 */
				fetchElementData: function( el ) {
					return $(el).closest('.treeview').treeview('getNode', $(el).data('nodeid'));
				},
				
				actions: 
				{
					/**
					 * Edit the file in the editor
					 */
					editFile: {
						name: 'Edit File',
						iconClass: 'fa-pencil',
						onClick: function( node ) {
							node.model.switchTo();
						},
						isShown: function( node ) {
							return node.selectable;
						}
					}
				}
			};
		},
		
		/**
		 * Create a new plugin
		 *
		 * @param	object		node		The plugin to add the class to
		 * @param	string		name		Optional suggested plugin name
		 * @return	$.Deferred
		 */
		createPluginDialog: function( name )
		{
			var self = this;
			
			var viewModel = {
				name:        ko.observable( name || '' ),
				description: ko.observable( '' ),
				vendor:      ko.observable( localStorage.getItem( 'mwp-studio-vendor-name' ) || '' ),
				author:      ko.observable( localStorage.getItem( 'mwp-studio-vendor-author' ) || '' ),
				authorurl:   ko.observable( localStorage.getItem( 'mwp-studio-vendor-authorurl' ) || '' ),
				pluginurl:   ko.observable( '' ),
				slug:        ko.observable( '' ),
				namespace:   ko.observable( localStorage.getItem( 'mwp-studio-vendor-namespace' ) || '' )
			};
			
			var dialogTemplate = $('#studio-tmpl-create-plugin-form').html();
			var dialogContent = $( dialogTemplate ).wrap( '<div>' ).parent();
			
			return this.createDialog( 'Plugin', dialogContent, viewModel, function() 
			{ 
				if ( ! viewModel.name() ) { return false; }
				
				localStorage.setItem( 'mwp-studio-vendor-name', viewModel.vendor() || '' );
				localStorage.setItem( 'mwp-studio-vendor-author', viewModel.author() || '' );
				localStorage.setItem( 'mwp-studio-vendor-authorurl', viewModel.authorurl() || '' );
				localStorage.setItem( 'mwp-studio-vendor-namespace', viewModel.namespace().split('\\')[0] || '' );
				
				var plugin_opts = {
					name:        viewModel.name(),
					description: viewModel.description(),
					vendor:      viewModel.vendor(),
					author:      viewModel.author(),
					author_url:  viewModel.authorurl(),
					plugin_url:  viewModel.pluginurl(),
					slug:        viewModel.slug(),
					namespace:   viewModel.namespace()
				};
				
				return self.createPlugin( plugin_opts ); 
			}, { size: 500 });
		}		

	}));
	
	/**
	 * Modern Wordpress Interface
	 */
	var MWPInterface = mwp.model.set( 'mwp-studio-mwp-interface', GenericInterface.extend(
	{
		/**
		 * Get plugin menu elements
		 * 
		 * @param	Plugin		plugin			The plugin to get menu items for
		 * @return	array
		 */
		getPluginMenuElements: function( plugin )
		{
			var self = this;
			var elements = MWPInterface.__super__.getPluginMenuElements.call( this, plugin );
			
			elements.push({
				type: 'divider'
			},
			{
				type: 'header',
				title: 'Resources'
			},
			{
				type: 'action',
				title: 'Add PHP Class',
				icon: 'fa fa-code',
				callback: function() {
					self.addClassDialog();
				}
			},
			{
				type: 'action',
				title: 'Add HTML Template',
				icon: 'fa fa-code',
				callback: function() {
					self.addTemplateDialog();
				}
			},
			{
				type: 'action',
				title: 'Add CSS Stylesheet',
				icon: 'fa fa-code',
				callback: function() {
					self.addCSSDialog();
				}
			},
			{
				type: 'action',
				title: 'Add Javascript Module',
				icon: 'fa fa-code',
				callback: function() {
					self.addJSDialog();
				}
			});
			
			return elements;
		},
		
		/**
		 * Get file context actions
		 *
		 * @param	Plugin		plugin			The plugin to get file context menus for
		 * @return	object
		 */
		getFileContextActions: function( plugin )
		{
			var self = this;
			var actions = MWPInterface.__super__.getFileContextActions.call( this, plugin );
			
			return actions;
		},
		
		/**
		 * Create a new plugin
		 *
		 * @param	object		node		The plugin to add the class to
		 * @param	string		name		Optional suggested plugin name
		 * @return	$.Deferred
		 */
		createPluginDialog: function( name )
		{
			var self = this;
			
			var viewModel = {
				name:        ko.observable( name || '' ),
				description: ko.observable( '' ),
				vendor:      ko.observable( localStorage.getItem( 'mwp-studio-vendor-name' ) || '' ),
				author:      ko.observable( localStorage.getItem( 'mwp-studio-vendor-author' ) || '' ),
				authorurl:   ko.observable( localStorage.getItem( 'mwp-studio-vendor-authorurl' ) || '' ),
				pluginurl:   ko.observable( '' ),
				slug:        ko.observable( '' ),
				namespace:   ko.observable( localStorage.getItem( 'mwp-studio-vendor-namespace' ) || '' )
			};
			
			var dialogTemplate = $('#studio-tmpl-create-plugin-form').html();
			var dialogContent = $( dialogTemplate ).wrap( '<div>' ).parent();
			
			return this.createDialog( 'Plugin', dialogContent, viewModel, function() 
			{ 
				if ( ! viewModel.name() ) { return false; }
				
				localStorage.setItem( 'mwp-studio-vendor-name', viewModel.vendor() || '' );
				localStorage.setItem( 'mwp-studio-vendor-author', viewModel.author() || '' );
				localStorage.setItem( 'mwp-studio-vendor-authorurl', viewModel.authorurl() || '' );
				localStorage.setItem( 'mwp-studio-vendor-namespace', viewModel.namespace().split('\\')[0] || '' );
				
				var plugin_opts = {
					name:        viewModel.name(),
					description: viewModel.description(),
					vendor:      viewModel.vendor(),
					author:      viewModel.author(),
					author_url:  viewModel.authorurl(),
					plugin_url:  viewModel.pluginurl(),
					slug:        viewModel.slug(),
					namespace:   viewModel.namespace()
				};
				
				return self.createPlugin( plugin_opts ); 
			}, { size: 500 });
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
			var self = this;
			var dialogInteraction = $.Deferred();
			var plugin = viewModel.plugin ? viewModel.plugin.model() : self.viewModel.currentPlugin().model();
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
									if ( response.plugin ) {
										var plugin = self.plugins.add( response.plugin );
										plugin.switchToPlugin();
										dialogInteraction.resolve( plugin );
										return;
									}
									
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
			
			return dialogInteraction.promise();		
		},
		
		/**
		 * Create a new php class
		 * 
		 * @param	object			options			Plugin options
		 * @return	$.Deferred
		 */
		createPlugin: function( options )
		{
			return $.ajax({
				url: this.local.ajaxurl,
				method: 'post',
				data: {
					action: 'mwp_studio_create_plugin',
					options: options
				}
			});
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
	}));
	
})( jQuery );
 