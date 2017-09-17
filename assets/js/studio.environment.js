/**
 * Plugin Javascript Module
 *
 * Created     August 8, 2017
 *
 * @package    Wordpress Plugin Studio
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
			
			menu.push({	type: 'header',	title: 'Plugins', icon: 'fa fa-plug' });
			
			_.each( plugins, function( plugin ) {
				menu.push({
					type: 'action',
					title: plugin.name(),
					icon: 'fa fa-angle-right',
					callback: function() {
						plugin.model().switchTo();
					}					
				});
			});

			menu.push({	type: 'divider'	});
			menu.push({	type: 'header',	title: 'Themes', icon: 'fa fa-paint-brush' });
			
			_.each( themes, function( theme ) {
				menu.push({
					type: 'action',
					title: theme.name(),
					icon: 'fa fa-angle-right',
					callback: function() {
						theme.model().switchTo();
					}					
				});
			});
		
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
				icon: 'fa fa-server',
				subitems: [{
					type: 'action',
					title: 'New Project',
					icon: 'fa fa-plus-circle',
					callback: function() {
					
					}
				},
				{
					type: 'divider',
				},
				{
					type: 'submenu',
					title: 'Open Project',
					icon: 'fa fa-folder-open',
					subitems: this.getProjectsMenu()
				}]
			},
			{
				type: 'dropdown',
				title: 'Editor',
				icon: 'fa fa-keyboard-o',
				subitems: [
				{
					type: 'action',
					title: 'Full Screen',
					icon: 'fa fa-window-maximize',
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
						studio.openWindow( 'editor-settings', studio.getEditorSettingsWindow() );
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
					icon: 'fa fa-angle-double-left',
					callback: function() {
						var layout = studio.viewModel.studioLayout();
						layout.open('west');
					}
					
				},
				{
					type: 'action',
					title: 'View Right Pane',
					icon: 'fa fa-angle-double-right',
					callback: function() {
						var layout = studio.viewModel.studioLayout();
						layout.open('east');
					}
					
				},
				{
					type: 'action',
					title: 'View South Pane',
					icon: 'fa fa-angle-double-down',
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
					icon: 'fa fa-arrows-alt',
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
					title: 'Update Code Index',
					icon: 'fa fa-database',
					callback: function() {
						$.ajax({
							url: studio.local.ajaxurl,
							data: { action: 'mwp_studio_sync_catalog', path: 'all' }
						}).done( function( response ) {
							if ( response.success ) {
								studio.updateStatus();
								bootbox.alert({ title: 'Notice', message: 'Task scheduled. Processing will now continue as a background process.' });
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
			return [{
				title: 'Meta Information',
				type: 'header'
			},
			{
				title: 'Edit Project Info',
				type: 'action',
				icon: 'fa fa-info-circle',
				callback: function() {
				
				}
			}];
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
				 * Reindex the directory
				 */
				syncIndex: {
					name: 'Sync code index',
					iconClass: 'fa-database',
					onClick: function( node ) {
						$.when( node.model.syncIndex() ).done( function( response ) {
							if ( response.success ) {
								if ( response.background ) {
									bootbox.alert({ title: 'Notice', message: 'Task scheduled. Processing will now continue as a background process.' });
								} else {
									bootbox.alert({ title: 'Notice', message: 'Success. Processing complete.' });
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
 