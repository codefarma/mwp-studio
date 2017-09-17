/**
 * MWP Support Javascript Module
 *
 * Created     September 8, 2017
 *
 * @package    Wordpress Plugin Studio
 * @author     Kevin Carwile
 * @since      0.0.0
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
	var FileTree = mwp.model.get( 'mwp-studio-filetree' );
	var FileTreeNode = mwp.model.get( 'mwp-studio-filetree-node' );
	var Studio = mwp.controller.model.get( 'mwp-studio' );
	
	/**
	 * Studio Augmentation
	 */
	Studio.override(
	{
		/**
		 * Get the editor settings window configuration
		 *
		 * @param	function		parent				The parent method
		 * @return	object
		 */
		newProjectWindow: function( parent )
		{
			var config = parent();
			config.viewModel.pluginFrameworks.push({ name: 'Modern Wordpress (MWP)', value: 'mwp' });
			return config;
		}
		
	});
	
	/**
	 * Modern Wordpress Environment
	 */
	var MWPEnvironment = mwp.model.set( 'mwp-studio-mwp-environment', GenericEnvironment.extend(
	{
		/**
		 * Get project menu elements
		 * 
		 * @param	Project		project			The project to get menu items for
		 * @return	array
		 */
		getProjectMenuItems: function( project )
		{
			var self = this;
			var elements = MWPEnvironment.__super__.getProjectMenuItems.call( this, project );
			
			elements.push(
			{
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
		 * @param	Project		project			The project to get file context menus for
		 * @return	object
		 */
		getFileContextActions: function( project )
		{
			var self = this;
			var actions = MWPEnvironment.__super__.getFileContextActions.call( this, project ) || {};
			
			_.extend( actions, 
			{
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
						self.addClassDialog( node.model.getParent( FileTree ).project, suggestedNamespace );
					},
					isShown: function( node ) {
						var file = node.model;
						return file.get('type') == 'dir' 
							&& file.rootDir(0) == 'classes';
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
						while( model.get('name') !== 'templates' && typeof model.getParent == 'function' ) { 
							namespaces.unshift( model.get('name') );
							model = model.getParent();
						}
						
						var suggestedNamespace = namespaces.length ? namespaces.join('/') + '/' : '';
						self.addTemplateDialog( node.model.getParent( FileTree ).project, suggestedNamespace );
					},
					isShown: function( node ) {
						var file = node.model;
						return file.get('type') == 'dir' 
							&& node.model.rootDir(0) == 'templates';
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
						self.addCSSDialog( node.model.getParent( FileTree ).project );					
					},
					isShown: function( node ) {
						var file = node.model;
						return file.rootDir(0) == 'assets' 
							&& ( file.rootDir(1) === undefined || file.rootDir(1) == 'css' );
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
						self.addJSDialog( node.model.getParent( FileTree ).project );
					},
					isShown: function( node ) {
						var file = node.model;
						return file.rootDir(0) == 'assets' 
							&& ( file.rootDir(1) === undefined || file.rootDir(1) == 'js' );
					}
				}
			});
			
			return actions;
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
			var plugin = plugin || studio.viewModel.currentProject().model();
			
			var viewModel = {
				plugin: kb.viewModel( plugin ),
				classname: ko.observable( namespace )
			};
			
			var dialogContent = $( studio.local.templates.dialogs['create-class'] ).wrapAll( '<div>' ).parent();
			
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
			var plugin = plugin || studio.viewModel.currentProject().model();
			
			var viewModel = {
				plugin: kb.viewModel( plugin ),
				filepath: ko.observable( filepath )
			};
			
			var dialogContent = $( studio.local.templates.dialogs['create-template'] ).wrapAll( '<div>' ).parent();
			
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
			var plugin = plugin || studio.viewModel.currentProject().model();
			
			var viewModel = {
				plugin: kb.viewModel( plugin ),
				filename: ko.observable( filename )
			};
			
			var dialogContent = $( studio.local.templates.dialogs['create-stylesheet'] ).wrapAll( '<div>' ).parent();
			
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
			var plugin = plugin || studio.viewModel.currentProject().model();
			
			var viewModel = {
				plugin: kb.viewModel( plugin ),
				filename: ko.observable( filename )
			};
			
			var dialogContent = $( studio.local.templates.dialogs['create-javascript'] ).wrapAll( '<div>' ).parent();
			
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
			var plugin = viewModel.plugin ? viewModel.plugin.model() : studio.viewModel.currentPlugin().model();
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
										var _plugin = studio.plugins.add( response.plugin );
										_plugin.switchToPlugin();
										dialogInteraction.resolve( _plugin );
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
		 * @param	string			plugin			Plugin slug
		 * @param	string			classname		Classname to create
		 * @return	$.Deferred
		 */
		createClass: function( plugin, classname )
		{
			return $.ajax({
				url: studio.local.ajaxurl,
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
				url: studio.local.ajaxurl,
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
				url: studio.local.ajaxurl,
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
				url: studio.local.ajaxurl,
				data: {
					action: 'mwp_studio_add_js',
					plugin: plugin,
					filename: filename
				}
			});
		}		
	}));
	
	// Add MWP Environment
	mwp.on( 'mwp-studio.init', function( studio ) {
		studio.environments.add( new MWPEnvironment({ id: 'mwp' }) );
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
		}
	});

})( jQuery );
 