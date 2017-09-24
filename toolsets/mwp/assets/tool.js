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
			// get the base config
			var config = parent();
			
			// Add modern wordpress as a plugin framework option
			config.viewModel.pluginFrameworks.push({ name: 'Modern Wordpress (MWP)', value: 'mwp' });
			
			// Add some observables to the view model
			_.extend( config.viewModel, {
				vendor: ko.observable( localStorage.getItem( 'mwp-studio-project-vendor' ) || '' ),
				namespace: ko.observable( '' )
			});
			
			// Auto update the namespace observable for convenience
			ko.computed( function() {
				var vendor_name = config.viewModel.vendor();
				var project_name = config.viewModel.name();
				
				var vendorParts = vendor_name.toString().split(' ').splice(0,2);
				var vendorNS = _.map( vendorParts, function( part ) {
					return part.toString().toLowerCase()
						.replace(/\s+/g, '')
						.replace(/[^\w]+/g, '')
						.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
				}).join('') || 'ModernWordpress';
				
				var projectParts = project_name.toString().split(' ').splice(0,2);
				var projectNS = _.map( projectParts, function( part ) {
					return part.toString().toLowerCase()
						.replace(/\s+/g, '')
						.replace(/[^\w]+/g, '')
						.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
				}).join('');
				
				if ( ! config.viewModel.namespace.custom ) {
					config.viewModel.namespace( vendorNS + '\\' + projectNS );
				}
			});
			
			// Add in our custom html to the config form
			$( studio.local.templates.extras.mwp['create-project-vendor'] ).insertAfter( config.bodyContent.find('.row.project-name') );
			
			// Decorate the submitted data to the backend
			var getSubmitParams = config.getSubmitParams;			
			config.getSubmitParams = function( _window ) {
				var params = getSubmitParams( _window );
				params.vendor = _window.options.viewModel.vendor();
				params.namespace = _window.options.viewModel.namespace();
				return params;
			};
			
			// Decorate the submission handler to persist the vendor name
			var submitFn = config.submit;
			config.submit = function( _window ) {
				localStorage.setItem( 'mwp-studio-project-vendor', _window.options.viewModel.vendor() );				
				return submitFn( _window );
			};
			
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
			},
			{
				type: 'divider'
			},
			{
				type: 'header',
				title: 'Packaging'
			},
			{
				type: 'action',
				title: 'Build New Version',
				icon: 'fa fa-gift',
				callback: function() {
					studio.openWindow( 'build-project-' + project.get('slug'), function() { return self.getBuildWindow( project ); } );
				}
			}
			);
			
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
		 * @param	object		plugin		The plugin to add the class to
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
			
			var dialogContent = $( studio.local.templates.dialogs['mwp-create-class'] ).wrapAll( '<div>' ).parent();
			
			return this.createDialog( 'Class', dialogContent, viewModel, function() { 
				if ( ! viewModel.classname() ) { return false; }
				return self.createClass( plugin.get('slug'), viewModel.classname() ); 
			}, { size: 500 });
		},
		
		/**
		 * Add a new html template
		 *
		 * @param	object		plugin		The plugin to add the class to
		 * @param	string		filepath	Optional suggested base path
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
			
			var dialogContent = $( studio.local.templates.dialogs['mwp-create-template'] ).wrapAll( '<div>' ).parent();
			
			return this.createDialog( 'Template', dialogContent, viewModel, function() { 
				if ( ! viewModel.filepath() ) { return false; }
				return self.createTemplate( plugin.get('slug'), viewModel.filepath() ); 
			}, { size: 500 });
		},
		
		/**
		 * Add a new css file dialog
		 *
		 * @param	object		plugin		The plugin to add the file to
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
			
			var dialogContent = $( studio.local.templates.dialogs['mwp-create-stylesheet'] ).wrapAll( '<div>' ).parent();
			
			return this.createDialog( 'Stylesheet File', dialogContent, viewModel, function() { 
				if ( ! viewModel.filename() ) { return false; }
				return self.createCSS( plugin.get('slug'), viewModel.filename() ); 
			}, { size: 500 });
		},
		
		/**
		 * Add a new javascript file dialog
		 *
		 * @param	object		plugin		The plugin to add the file to
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
			
			var dialogContent = $( studio.local.templates.dialogs['mwp-create-javascript'] ).wrapAll( '<div>' ).parent();
			
			return this.createDialog( 'Javascript Module', dialogContent, viewModel, function() { 
				if ( ! viewModel.filename() ) { return false; }
				return self.createJS( plugin.get('slug'), viewModel.filename() ); 
			}, { size: 500 });
		},
		
		/**
		 * Get the new plugin version build window config
		 *
		 * @param	object		plugin		The plugin to add the file to
		 * @return	object
		 */
		getBuildWindow: function( plugin )
		{
			var plugin = plugin || studio.viewModel.currentProject().model();
			var currentVersion = plugin.get('version') || '0.0.0';
			var versionParts = _.map( currentVersion.split('.'), function( part ) { return parseInt( part ); } );
			var newMajor = _.map( [ versionParts[0] + 1, 0, 0 ], function( part ) { return part.toString(); } ).join('.');
			var newMinor = _.map( [ versionParts[0], versionParts[1] + 1, 0 ], function( part ) { return part.toString(); } ).join('.');
			var newPoint = _.map( [ versionParts[0], versionParts[1], versionParts[2] + 1 ], function( part ) { return part.toString(); } ).join('.');
			var newPatch = _.map( [ versionParts[0], versionParts[1], versionParts[2], (versionParts[3] || 0) + 1 ], function( part ) { return part.toString(); } ).join('.');
			
			var options = {
				modal: true,
				title: '<i class="fa fa-cog"></i> Build A New Plugin Version',
				bodyContent: $(studio.local.templates.dialogs['mwp-build-plugin']),
				footerContent: $('<button type="button" class="btn btn-default pull-left" data-dismiss="window">Cancel</button><button data-submit="window" type="button" class="btn btn-primary">Build</button>'),
				dimensions: { width: 800 },
				viewModel: {
					plugin: kb.viewModel( plugin ),
					buildType: ko.observable( 'point' ),
					buildFramework: ko.observable( true ),
					versions: {
						rebuild: currentVersion,
						point: newPoint,
						minor: newMinor,
						major: newMajor,
						patch: newPatch,
						custom: ko.observable( newPoint )
					}
				},
				submit: function( _window ) {
					var buildDeferred = $.Deferred();
					var vm = _window.options.viewModel;
					
					studio.ajax({
						method: 'post',
						data: {
							action: 'mwp_studio_build_mwp_project',
							type: plugin.get('type'),
							slug: plugin.get('slug'),
							version: vm.versions[ vm.buildType() ],
							bundle: vm.buildFramework()
						}
					}).done( function( response ) {
						if ( response.success ) {
							plugin.set( 'version', response.version );
							window.location.href = studio.local.site_url + '/' + response.file;
							buildDeferred.resolve(true);
						} else {
							buildDeferred.resolve(false);
							if ( response.message ) {
								studio.openDialog( 'alert', { title: 'Build Failure', message: response.message } );
							}
						}
					});
					
					return buildDeferred;
				}
			};
			
			return options;
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
			
			/* Add an enter key submit function to each dialog */
			viewModel.enterKeySubmit = function( data, event ) {
				if ( event.which == 13 ) {
					dialog.modal('hide');
					opts.buttons.ok.callback();
				}
				return true;
			}
			
			var opts = {
				viewModel: viewModel,
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
			dialog = studio.createModal( _.extend( opts, extraOptions ) );
			
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
 