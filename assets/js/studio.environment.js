/**
 * Plugin Javascript Module
 *
 * Created     August 8, 2017
 *
 * @package    MWP Studio
 * @author     Kevin Carwile
 * @since      0.0.0
 */

(function( $, undefined ) {
	
	"use strict";

	var studio;
	mwp.on( 'mwp-studio.ready', function(c){ studio = c; } );
	
	var CollectorModel   = mwp.model.get( 'mwp-studio-collector' );
	var CollectibleModel = mwp.model.get( 'mwp-studio-collectible' );
	var FileTree         = mwp.model.get( 'mwp-studio-filetree' );
	var FileTreeNode     = mwp.model.get( 'mwp-studio-filetree-node' );
	var MenuItem         = mwp.model.get( 'mwp-studio-menu-item' );
	
	/**
	 * [Model] Base Framework
	 *
	 * This class should be extended to provide framework specific support and
	 * studio elements for projects
	 */
	var GenericEnvironment = mwp.model.set( 'mwp-studio-generic-environment', CollectorModel.extend(
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
			 * Studio menu items
			 */
			this.studioMenuItems = ko.computed( function() {
				var project = studio.viewModel.currentProject();
				var items = project ? self.getStudioMenuItems( project.model() ) : [];
				return _.map( items, function( itemData ) { return kb.viewModel( new MenuItem( itemData ) ); } );
			});
			
			/**
			 * Studio projects menu
			 */
			this.projectsMenu = ko.computed( function()
			{
				var menu = self.getProjectsMenu();
				return _.map( menu, function( itemData ) { return kb.viewModel( new MenuItem( itemData ) ); } );
			});
			
			/**
			 * Project menu items
			 */
			this.projectMenuItems = ko.computed( function() {
				var project = studio.viewModel.currentProject();
				var items = project ? self.getProjectMenuItems( project.model() ) : [];
				return _.map( items, function( itemData ) { return kb.viewModel( new MenuItem( itemData ) ); } );
			});
			
			/**
			 * File context actions
			 */
			this.fileContextActions = ko.computed( function() {
				var project = studio.viewModel.currentProject();
				return project ? self.getFileContextActions( project.model() ) : [];
			});
			
			/**
			 * Studio pane tabs
			 */
			this.studioPaneTabs = ko.computed( function() {
				var project = studio.viewModel.currentProject();
				return project ? self.getStudioPaneTabs( project.model() ) : [];
			});
			
		},
		
		/**
		 * Get projects menu
		 *
		 * @return	array
		 */
		getProjectsMenu: function()
		{
			var plugins = _.filter( studio.viewModel.projects(), function( project ) { return project.model().get('type') == 'plugin'; } );
			var themes = _.filter( studio.viewModel.projects(), function( project ) { return project.model().get('type') == 'theme'; } );			
			var menu = [];
			
			/* Plugin submenu */
			var pluginMenu = {
				classes: 'plugin-projects-menu',
				type: 'submenu',
				title: 'Plugins',
				icon: 'fa fa-plug',
				subitems: []
			};
			
			_.each( plugins, function( plugin ) {
				pluginMenu.subitems.push({
					type: 'action',
					title: plugin.name(),
					icon: 'fa fa-angle-right',
					callback: function() {
						plugin.model().switchTo();
					}					
				});
			});

			menu.push( pluginMenu );
			menu.push({	type: 'divider'	});
			
			/* Theme submenu */
			var themeMenu = {
				classes: 'theme-projects-menu',
				type: 'submenu',
				title: 'Themes',
				icon: 'fa fa-paint-brush',
				subitems: []
			};
			
			_.each( themes, function( theme ) 
			{
				if ( theme.template() ) {
					return;
				}
				
				var child_themes = _.map( _.filter( themes, function( _theme ) { return _theme.template() == theme.key(); } ), function( _theme ) {
					return {
						type: 'action',
						title: _theme.name(),
						icon: 'fa fa-angle-right',
						callback: function() {
							_theme.model().switchTo();
						}
					};
				});
				
				var item = {
					type: child_themes.length ? 'submenu' : 'action',
					title: theme.name(),
					icon: child_themes.length ? 'fa fa-angle-double-right' : 'fa fa-angle-right',
					subitems: child_themes,
					callback: function() {
						theme.model().switchTo();
					}
				};
				
				themeMenu.subitems.push( item );
			});
		
			menu.push( themeMenu );
			
			return menu;
		},
		
		/**
		 * Get project menu elements
		 * 
		 * @param	Project		project			The project to get menu items for
		 * @return	array
		 */
		getStudioMenuItems: function( project )
		{
			return [
			{
				type: 'dropdown',
				title: 'Studio',
				icon: 'fa fa-window-restore',
				subitems: [
				{
					type: 'header',
					title: 'Projects'
				},
				{
					type: 'action',
					title: 'Create New Project',
					icon: 'fa fa-coffee',
					callback: function() {
						studio.openWindow( 'create-project', function() { return studio.newProjectWindow(); } );
					}
				},
				{
					type: 'submenu',
					title: 'Open A Project',
					icon: 'fa fa-folder-open',
					subitems: this.getProjectsMenu()
				}]
			},
			{
				type: 'dropdown',
				title: 'Project',
				icon: project.get('type') == 'theme' ? 'fa fa-paint-brush' : 'fa fa-plug',
				subitems: this.getProjectMenuItems( project ),
			},
			{
				type: 'dropdown',
				title: 'Editor',
				icon: 'fa fa-code',
				subitems: [
				{
					type: 'action',
					title: 'Full Screen',
					icon: 'fa fa-arrows-alt',
					callback: function() {
						var layout = studio.viewModel.studioLayout();
						layout.close('west');
						layout.close('east');
						$(layout.panes.center[0]).layout().close('south');
					}
				},
				{
					type: 'action',
					title: 'Settings',
					icon: 'fa fa-cog',
					callback: function() {
						studio.openWindow( 'editor-settings', function() { return studio.editorSettingsWindow(); } );
					}
				}]
			},
			{
				type: 'dropdown',
				title: 'View',
				icon: 'fa fa-tv',
				subitems: [
				{
					type: 'action',
					title: 'View Left Pane',
					icon: 'fa fa-arrow-circle-left',
					callback: function() {
						var layout = studio.viewModel.studioLayout();
						layout.open('west');
					}
					
				},
				{
					type: 'action',
					title: 'View Right Pane',
					icon: 'fa fa-arrow-circle-right',
					callback: function() {
						var layout = studio.viewModel.studioLayout();
						layout.open('east');
					}
					
				},
				{
					type: 'action',
					title: 'View South Pane',
					icon: 'fa fa-arrow-circle-down',
					callback: function() {
						var layout = studio.viewModel.studioLayout();
						$(layout.panes.center[0]).layout().open('south');
					}
					
				},
				{
					type: 'divider'
				},
				{
					type: 'action',
					title: 'View All Panes',
					icon: 'fa fa-arrows',
					callback: function() {
						var layout = studio.viewModel.studioLayout();
						layout.open('west');
						layout.open('east');
						$(layout.panes.center[0]).layout().open('south');
					}
					
				}]
			},
			{
				type: 'dropdown',
				title: 'Tools',
				icon: 'fa fa-cogs',
				subitems: [
				{
					type: 'action',
					title: 'Web Browser',
					icon: 'fa fa-globe',
					callback: function() {
						studio.openWindow( 'web-browser', function() { return studio.browserWindow(); } );
					}
				},
				{
					type: 'action',
					title: 'Search',
					icon: 'fa fa-search',
					callback: function() {
						studio.openWindow( 'search', function() { return studio.searchWindow(); } );
					}
				},
				{
					type: 'action',
					title: 'Update Code Index',
					icon: 'fa fa-database',
					callback: function() {
						studio.updateCodeIndex().done( function( response ) {
							if ( response.success ) {
								studio.updateStatus();
								studio.openDialog( 'alert', { title: 'Notice', message: 'Task scheduled. Processing will now continue as a background process.' });
							}
						});
					}
				}]
			},
			{
				type: 'dropdown',
				title: 'Help',
				icon: 'fa fa-question-circle',
				subitems: [
				{
					type: 'action',
					title: 'About',
					icon: 'fa fa-info-circle',
					callback: function() {
						studio.openWindow( 'about', function() { return studio.aboutWindow(); } );
					}
				}]
			}];
		},
		
		/**
		 * Get project menu elements
		 * 
		 * @param	Project		project			The project to get menu items for
		 * @return	array
		 */
		getProjectMenuItems: function( project )
		{
			var self = this;
			return [{
				title: 'Meta Information',
				type: 'header'
			},
			{
				title: 'Edit Project Info',
				type: 'action',
				icon: 'fa fa-info-circle',
				callback: function() {
					studio.openWindow( 'edit-' + project.get('type') + '-' + project.get('slug'), function() { return self.editProjectWindow( project ); } );
				}
			}];
		},
		
		/**
		 * Get the edit project window settings
		 *
		 * @param	Project			project					The project to edit
		 * @return	object
		 */
		editProjectWindow: function( project )
		{	
			var mockProject = new Backbone.Model( project.toJSON() );

			return {
				title: '<i class="fa fa-' + ( project.get('type') == 'theme' ? 'paint-brush' : 'plug' ) + '"></i> Edit ' + project.get('name'),
				bodyContent: $(studio.local.templates.dialogs['edit-project']),
				viewModel: {
					project: {
						file:        project.get('file'),
						slug:        project.get('slug'),
						type:        project.get('type'),
						name:        ko.observable( project.get('name') ),
						description: ko.observable( project.get('description') ),
						url:         ko.observable( project.get('url') ),
						author:      ko.observable( project.get('author') ),
						author_url:  ko.observable( project.get('author_url') )
					}
				},
				submit: function( _window ) {
					var deferredEdit = $.Deferred();
					var vm = _window.options.viewModel;
					var _project = {};
					
					// Map project observables to json
					$.each( vm.project, function( key, observable ) {
						_project[key] = typeof observable == 'function' ? observable() : observable;
					});
					
					studio.ajax({
						data: {
							action: 'mwp_studio_edit_project',
							project: _project
						}
					}).done( function( response ) {
						if ( response.success ) {
							project.set( 'name', _project.name );
							project.set( 'description', _project.description );
							project.set( 'url', _project.url );
							project.set( 'author', _project.author );
							project.set( 'author_url', _project.author_url );

							deferredEdit.resolve(true);
						} else {
							if ( response.message ) {
								studio.openDialog( 'alert', { title: 'Error Saving Data', message: response.message } );
							}
							deferredEdit.resolve(false);
						}
					});
					
					return deferredEdit;
				}
			};
		},
		
		/**
		 * Get file context actions
		 *
		 * @param	Project		project			The project to get file context menus for
		 * @return	object
		 */
		getFileContextActions: function( project )
		{
			return {
				/**
				 * Edit the file in the editor
				 */
				createFile: {
					name: 'Create New File',
					iconClass: 'fa-plus',
					onClick: function( node ) {
						studio.openDialog( 'prompt', {
							title: 'Enter new filename: ',
							callback: function( newname ) { 
								if ( newname ) {
									$.when( node.model.createFile( newname ) ).done( function( newFile ) {
										if ( newFile.collection ) {
											newFile.collection.trigger( 'updated' );
										}
										newFile.switchTo();
									});
								}
							}
						});
					}
				},
				
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
				},
				
				/**
				 * Copy a file
				 */
				copyFile: {
					name: 'Copy File',
					iconClass: 'fa-copy',
					onClick: function( node ) {
						$.when( node.model.copyFile() ).done( function( fileCopy ) {
							fileCopy.switchTo();
						});
					},
					isShown: function( node ) {
						return node.model.get('type') == 'file';
					}
				},
				
				/**
				 * Rename a file
				 */
				renameFile: {
					name: 'Rename File',
					iconClass: 'fa-i-cursor',
					onClick: function( node ) {
						studio.openDialog( 'prompt', {
							title: 'Rename File: ' + node.model.get('text'),
							value: node.model.get('text'),
							callback: function( newname ) { 
								if ( newname ) {
									$.when( node.model.renameFile( newname ) ).done( function() {
										if ( node.model.collection ) {
											node.model.collection.trigger( 'updated' );
										}
									});
								}
							}
						});
					},
					isShown: function( node ) {
						return node.model.get('type') == 'file';
					}
				},

				/**
				 * Delete a file
				 */
				deleteFile: {
					name: 'Delete File',
					iconClass: 'fa-trash',
					onClick: function( node ) {
						studio.openDialog( 'dialog', {
							size: 'large',
							title: 'Warning! Delete File: ' + node.model.get('text'),
							message: "Are you sure you want to delete this file?",
							buttons: {
								cancel: {
									label: 'Cancel',
									className: 'btn-default'
								},
								no: {
									label: 'No',
									className: 'btn-primary'
								},
								yes: {
									label: 'Yes',
									className: 'btn-danger',
									callback: function() { 
										node.model.deleteFile(); 
									}
								}
							}
						});
						
					},
					isShown: function( node ) {
						return node.model.get('type') == 'file';
					}
				},
				
				/**
				 * Refresh the file tree
				 */
				refreshFileTree: {
					name: 'Refresh Files',
					iconClass: 'fa-refresh',
					onClick: function( node ) {
						studio.viewModel.currentProject().model().fetchFileTree();
					}
				},
				
				/**
				 * Reindex the directory
				 */
				syncIndex: {
					name: 'Sync to code index',
					iconClass: 'fa-database',
					onClick: function( node ) {
						$.when( node.model.syncIndex() ).done( function( response ) {
							if ( response.success ) {
								if ( response.background ) {
									studio.openDialog( 'alert', { title: 'Notice', message: 'Task scheduled. Processing will now continue as a background process.' });
								} else {
									studio.openDialog( 'alert', { title: 'Notice', message: 'Success. Processing complete.' });
								}
							}
						});
					},
					isShown: function( node ) {
						return node.model.get('type') == 'dir' || node.model.get('ext') == 'php';
					}
				}
			};
		},
		
		/**
		 * Get studio pane tabs
		 *
		 * @param	Project		project			The project to get studio pane tabs for
		 * @return	array
		 */
		getStudioPaneTabs: function( project )
		{
			return [
			{
				id: 'project-info',
				title: 'Project Info',
				icon: 'fa fa-info-circle',
				viewModel: studio.viewModel,
				template: $(studio.local.templates.panetabs['project-info']),
				refreshContent: function() 
				{
					
				}
			}];
		}
	}));

	// Add Generic Environment
	mwp.on( 'mwp-studio.init', function( studio ) {
		studio.environments.add( new GenericEnvironment({ id: 'generic' }) );
	});
	
})( jQuery );
 