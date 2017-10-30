/**
 * Plugin Javascript Module
 *
 * Created     May 1, 2017
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
	
	// Apply mwp bootstrap styles to the whole document
	$('html').addClass('mwp-bootstrap');
	
	// Wordpress collapse side menu
	$(document).on( 'click', '#collapse-menu', function() {
		$(window).resize();
	});

	// Bootbox center dialogs
	$('body').on( 'shown.bs.modal', '.modal', function() {
		var box = $(this);
		box.css({
			'top': '50%',
			'margin-top': function () {
				return -(box.height() / 2);
			}
		});
	});
	
	var CollectorModel    = mwp.model.get( 'mwp-studio-collector' );
	var CollectibleModel  = mwp.model.get( 'mwp-studio-collectible' );
	var FileTree          = mwp.model.get( 'mwp-studio-filetree' );
	var FileTreeNode      = mwp.model.get( 'mwp-studio-filetree-node' );
	var Project           = mwp.model.get( 'mwp-studio-project' );
	var GenericInterface  = mwp.model.get( 'mwp-studio-generic-interface' );
	
	/**
	 * @var	bool		Flag indicating if a process polling is active
	 */
	var process_polling = false;
	
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
	var Studio = mwp.controller.model( 'mwp-studio', 
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
			 * @var	Collection		Projects list
			 */
			this.projects = new Backbone.Collection( [], { model: Project } );
			
			/**
			 * View Model
			 */
			this.viewModel = 
			{
				studioLoading:  ko.observable(true),
				studioLayout:   ko.observable(),
				projects:       kb.collectionObservable( this.projects ),
				currentProject: ko.observable(),
				openFiles:      ko.observableArray(),
				activeFile:     ko.observable(),
				env:            function() { return self.env(); },
				statustext:     ko.observable(''),
				processStatus:  ko.observable(),
				searchPhrase:   ko.searchObservable( function( phrase ) {
					return $.ajax({
						method: 'post',
						url: self.local.ajaxurl,
						data: {
							action: 'mwp_studio_search',
							phrase: phrase
						}
					});
				})
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
					var currentFile = self.viewModel.activeFile();
					var breadcrumbs = currentFile ? currentFile.path().split('/') : [];
					
					return breadcrumbs.length ? breadcrumbs : [ 'No file open' ];
				})
			});
			
			/**
			 * @var WindowManager
			 */
			this.windowManager = new WindowManager({
				container: $('[data-role="window-labels"]').eq(0),
				windowTemplate: this.local.templates.dialogs['window-template']
			});

			/**
			 * Load projects
			 *
			 * - Select last active project after initial load -or-
			 * - Select first project in the list
			 */
			setTimeout( function() 
			{
				self.loadProjects().done( function() 
				{
					var project_id = localStorage.getItem( 'mwp-studio-current-project' );
					var index = self.projects.indexOf( self.projects.get( project_id ) );
					
					if ( index == -1 ) { index = 0; }
					self.viewModel.currentProject( self.viewModel.projects()[index] );
				});
			}, 1250 );
			
			/**
			 * Lazy load project resources only after it becomes active
			 *
			 */
			this.viewModel.currentProject.subscribe( function( projectView ) 
			{
				var project = projectView.model();
				
				if ( ! project.fileTree.initialized ) {
					$.when( project.fetchFileTree() ).done( function() {
						self.viewModel.studioLoading( false );
					});
				}
				
				// Remember last active project
				localStorage.setItem( 'mwp-studio-current-project', project.get('id') );
			});

			// Start our ticker
			this.heartbeat();
		},
		
		/**
		 * Get the current studio environment
		 *
		 * @return	Environment
		 */
		env: function()
		{
			if ( this.viewModel.currentProject() ) {
				return this.viewModel.currentProject().model().env;
			}
			
			return this.environments.get('generic');
		},
		
		/**
		 * Open a bootbox dialog
		 *
		 * @param	string			type			Dialog type
		 * @param	object			options			Dialog options
		 * @return	bootbox
		 */
		openDialog: function( type, options )
		{
			var create = bootbox[ type ];
			var box = create( options );
			
			box.css({
				'top': '50%',
				'margin-top': function () {
					return -(box.height() / 2);
				}
			});
			
			return box;
		},
		
		/**
		 * Open a studio ui window
		 * 
		 * @param	string|null		id					Window id
		 * @param   object|funcion 	options             Window options or function to get options
		 * @return  Window
		 */
		openWindow: function( id, options )
		{
			var _window;
			
			// Look for existing window by this id
			if ( id ) {
				_window = this.windowManager.findWindowByID( id );
				if ( _window ) {
					_window.restore();
					this.windowManager.setFocused( _window );
					return _window;
				}
			}
			
			return this.createWindow( id, options );
		},
		
		/**
		 * Create a new studio ui window
		 * 
		 * @param	string|null		id					Window id
		 * @param   object|funcion 	options             Window options or function to get options
		 * @return  Window
		 */
		createWindow: function( id, options )
		{
			var _window;
			
			// Optionally use callback to get the window options
			if ( typeof options === 'function' ) {
				options = options();
			}
			
			// Apply defaults
			options = $.extend( true, 
			{
				viewModel:     {},
				modal:         false,
				bodyContent:   $(''),
				footerContent: $('<button type="button" class="btn btn-default pull-left" data-dismiss="window">Cancel</button><button data-submit="window" type="button" class="btn btn-primary">Save</button>'),
				maximizable:   false, 
				minimizable:   true, 
				resizable:     {}, 
				draggable:     { handle: '.window-header' },
				dimensions:    { width: 600 },
				submit:        function() { return true; }
			}, 
			options, { id: id || undefined } );
			
			/* Apply the view model to the body content */
			if ( options.viewModel ) {
				options.bodyContent = $(options.bodyContent).wrapAll('<div>').parent();
				if ( options.bodyContent[0] ) {
					ko.applyBindings( options.viewModel, options.bodyContent[0] );
				}
				options.bodyContent = $(options.bodyContent).children();
			}
			
			/* Wrap the submit function to provide user feedback */
			var submitFn = options.submit;
			options.submit = function( _window ) {
				var submitResult = submitFn( _window );
				var submitButton = _window.getElement().find('[data-submit="window"]');
				
				submitButton.attr('disabled', true);
				submitButton.prepend( '<i class="fa fa-refresh fa-spin" style="margin-right: 5px"></i>' );
				$.when( submitResult ).done( function() {
					submitButton.find('i.fa-refresh').remove();
					submitButton.removeAttr('disabled');
				});
				return submitResult;
			}
			
			/**
			 * Create the window and apply options
			 */
		    var _window = this.windowManager.createWindow( options );
			var element = $( _window.$el );
			
			if ( id ) {
				element.attr( 'id', 'window-' + id );
			}
			
			if ( options.minimizable ) {
				_window.on( 'bsw.minimize', function() { 
					_window.$windowTab.removeClass('label-primary');
					_window.$windowTab.addClass('label-default');
				});
			}
			
			if ( options.resizable ) {
				element.resizable( $.extend( true, { minWidth: element.outerWidth(), minHeight: element.outerHeight(), maxWidth: $(window).width(), maxHeight: $(window).height() }, options.resizable ) );
				_window.on( 'bsw.maximize', function() { element.resizable( 'disable' ); });
				_window.on( 'bsw.restore', function() { element.resizable( 'enable' ); });
				element.on( 'resize', function() { _window.sizeBody(true); element.trigger( 'resized' ); });
			}
			
			if ( options.draggable ) {
				element.draggable( options.draggable );
				_window.on( 'bsw.maximize', function() { element.draggable( 'disable' ); });
				_window.on( 'bsw.restore', function() { element.draggable( 'enable' ); });
			}
			
			if ( options.dimensions ) {
				_window.resize( options.dimensions );
				if ( ! ( options.dimensions.left || options.dimensions.top ) ) {
					_window.centerWindow();
				}
			}
			
			if ( typeof options.init == 'function' ) {
				options.init( _window );
			}
			
			return _window;
		},

		/**
		 * Get the editor settings window configuration
		 *
		 * @return	object
		 */
		editorSettingsWindow: function()
		{
			var self = this;
			
			return {
				title: 'Editor Settings',
				bodyContent: this.local.templates.dialogs['editor-settings'],
				viewModel: {
					tabsType: ko.observable( localStorage.getItem( 'mwp-studio-editor-tabs-type' ) || 'tab' ),
					tabsSize: ko.observable( localStorage.getItem( 'mwp-studio-editor-tabs-size' ) || 4 ),
					lineWrap: ko.observable( localStorage.getItem( 'mwp-studio-editor-line-wrap' ) == 'true' )
				},
				submit: function( _window ) {
					var viewModel = _window.options.viewModel;
					localStorage.setItem( 'mwp-studio-editor-tabs-type', viewModel.tabsType() );
					localStorage.setItem( 'mwp-studio-editor-tabs-size', viewModel.tabsSize() );
					localStorage.setItem( 'mwp-studio-editor-line-wrap', viewModel.lineWrap() );
					_.each( self.viewModel.openFiles(), function( file ) {
						file.model().updateEditorOptions();
					});
				},
				dimensions: { width: 500 }
			};
		},
		
		/**
		 * Get the editor settings window configuration
		 *
		 * @return	object
		 */
		newProjectWindow: function()
		{
			var self = this;
			
			var parentThemes = _.map( _.filter( this.projects.models, 
				function( project ) {
					return project.get('type') == 'theme' && project.get('template') == '';
				}), 
				function( theme ) {
					return { name: theme.get('name'), value: theme.get('key') };
				}
			);
			
			return {
				title: '<i class="fa fa-coffee"></i> Create A New Project',
				bodyContent: $(this.local.templates.dialogs['create-project']),
				footerContent: $('<button type="button" class="btn btn-default pull-left" data-dismiss="window">Cancel</button><button data-submit="window" type="button" class="btn btn-primary">Create</button>'),
				viewModel: {
					projectTypes:     ko.observableArray( [{ name: 'Plugin', value: 'plugin' }, { name: 'Theme', value: 'theme' }, { name: 'Child Theme', value: 'child-theme' }] ),
					projectType:      ko.observable( 'plugin' ),
					pluginFrameworks: ko.observableArray( [{ name: 'None', value: 'none' }] ),
					pluginFramework:  ko.observable( 'none' ),
					themeFrameworks:  ko.observableArray( [{ name: 'None', value: 'none' }] ),
					themeFramework:   ko.observable( 'none' ),
					parentThemes:     ko.observableArray( parentThemes ),
					parentTheme:      ko.observable( parentThemes[0].value ),
					name:             ko.observable( name || '' ),
					description:      ko.observable( '' ),
					author:           ko.observable( localStorage.getItem( 'mwp-studio-vendor-author' ) || '' ),
					authorurl:        ko.observable( localStorage.getItem( 'mwp-studio-vendor-authorurl' ) || '' ),
					projecturl:       ko.observable( '' ),
					slug:             ko.observable( '' ),
				},
				getSubmitParams: function( _window )
				{
					var viewModel = _window.options.viewModel;
					
					return {
						type:            viewModel.projectType(),
						pluginFramework: viewModel.pluginFramework(),
						themeFramework:  viewModel.themeFramework(),
						parentTheme:     viewModel.parentTheme(),
						name:            viewModel.name(),
						description:     viewModel.description(),
						author:          viewModel.author(),
						author_url:      viewModel.authorurl(),
						url:             viewModel.projecturl(),
						slug:            viewModel.slug()
					};
				},
				submit: function( _window ) 
				{
					var viewModel = _window.options.viewModel;
					
					// validation
					if ( ! viewModel.name() ) { 
						self.openDialog( 'alert', { title: '<i class="fa fa-exclamation-triangle" style="color:red"></i> More Information Needed', message: 'You must enter a name for this project.' } );
						return false; 
					}
					
					localStorage.setItem( 'mwp-studio-vendor-author', viewModel.author() || '' );
					localStorage.setItem( 'mwp-studio-vendor-authorurl', viewModel.authorurl() || '' );
					
					var deferredSubmit = $.Deferred();
					
					self.createProject( _window.options.getSubmitParams( _window ) ).done( function( response ) 
					{
						if ( response.success ) {
							deferredSubmit.resolve(true);
							var project = new Project( response.project );
							self.projects.add( project );
							project.switchTo();
						} else {
							deferredSubmit.resolve(false);
							self.openDialog( 'alert', { title: '<i class="fa fa-exclamation-triangle" style="color:red"></i> Processing Error', message: response.message || 'The project could not be created.' } );
						}
					});
					
					return deferredSubmit;
				},
				dimensions: { width: $(window).width() > 750 ? 750 : $(window).width() * .90 }
			};
		},
		
		/**
		 * Get a web browser window
		 *
		 * @param	string			url			Starting url
		 * @return	object
		 */
		browserWindow: function( url )
		{
			return {
				title: '<i class="fa fa-globe"></i> Web Browser',
				bodyContent: $(this.local.templates.dialogs['web-browser']),
				footerContent: $(''),
				viewModel: {
					url: ko.observable( url || studio.local.site_url ),
					bodyHeight: ko.observable(0)
				},
				resizable: {
					minHeight: 300,
					minWidth: 400
				},
				maximizable: true,
				dimensions: {
					width: $(window).width() / 2,
					height:$(window).height() / 1.5
				},
				init: function( _window ) {
					var updateHeight = function() { _window.options.viewModel.bodyHeight( _window.options.elements.body.height() ); };
					_window.getElement().on( 'resized', updateHeight ).trigger( 'resized' );
					_window.on( 'bsw.maximize bsw.restore', function() { _window.sizeBody(true); updateHeight(); } );
				}
			};
		},
		
		/**
		 * About the studio window
		 *
		 * @return	object
		 */
		aboutWindow: function()
		{
			return {
				title: '<i class="fa fa-info-circle"></i> About MWP Studio',
				bodyContent: this.local.templates.dialogs['about'],
				footerContent: '<button type="button" class="btn btn-primary" data-dismiss="window">Ok</button>'
			};
		},
		
		/**
		 * Create a new project
		 * 
		 * @param	object			options			Plugin options
		 * @return	$.Deferred
		 */
		createProject: function( options )
		{
			return this.ajax({
				method: 'post',
				data: {
					action: 'mwp_studio_create_project',
					options: options
				}
			});
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
			
			/* Check on open files */
			_.each( this.viewModel.openFiles(), function( fileview ) {
				if ( ! fileview.model().conflicted() ) {
					checkups.push( fileview.model().checkSync() );
				}
			});
			
			/* Tick Tock. */
			$.when.apply( this, checkups ).done( function() 
			{
				$.ajax({ url: self.local.cron_url });
				self.updateStatus();
				setTimeout( function() { self.heartbeat(); }, self.local.heartbeat_interval );
			});
		},
		
		/**
		 * Check and update statusbar
		 *
		 * @return	$.Deferred
		 */
		updateStatus: function()
		{
			var self = this;
			
			return $.ajax({ url: self.local.ajaxurl, data: { action: 'mwp_studio_statuscheck' }, method: 'post' }).done( function( status ) 
			{
				if ( status.statustext ) {
					self.viewModel.statustext( status.statustext );
				}
				
				if ( status.processing && ! process_polling ) {
					self.startProcessPolling( status.processing );
				}
			});		
		},
		
		/**
		 * Status watch
		 * 
		 * @param	object			process				The process info provided from the backend
		 * @return	void
		 */
		startProcessPolling: function( process )
		{
			process_polling = true;
			
			var self = this;
			var timeout = 2500;
			
			/**
			 * Poll the backend for an update on this process
			 *
			 * If the status has not changed, we will progressively slow down the poll interval,
			 * and we will keep the poll alive for as long as the process is reporting that it
			 * is not yet complete.
			 */
			var poll = function() 
			{
				$.ajax({
					method: 'post',
					url: self.local.ajaxurl,
					data: { action: 'mwp_studio_process_status', process: process }
				}).done( function( status ) 
				{
					var current_status = self.viewModel.processStatus();
					
					// Progressively slow down the poll if a process hasn't changed status
					if ( JSON.stringify( current_status ) == JSON.stringify( status ) && timeout < 20000 ) {
						timeout = timeout + 500;
					}
					else
					{
						timeout = 2500;
						self.viewModel.processStatus( status );
					}
					
					status.complete === false ? setTimeout( poll, timeout ) : process_polling = false;
				});
			};
			
			poll();
		},
		
		/**
		 * Load available studio projects from the backend
		 *
		 * @return	$.Deferred
		 */
		loadProjects: function()
		{
			var self = this;
			
			return $.ajax({
				method: 'post',
				url: this.local.ajaxurl,
				data: { action: 'mwp_studio_load_projects' }
			})
			.then( function( data ) {
				if ( data.projects ) {
					self.projects.add( data.projects );
				}
			});
		}
		
	});
	
	/**
	 * Custom knockout bindings
	 */
	_.extend( ko.bindingHandlers, 
	{
		/**
		 * NetEye Activity Indicator
		 * 
		 * @see: https://github.com/live627/jquery-plugins-1/tree/master/activity-indicator
		 */
		studioActivity: {
			update: function( element, valueAccessor, allBindingsAccessor ) {
				var opts = ko.utils.unwrapObservable( valueAccessor() );				
				$(element).studioActivity(opts);
			}
		},
		
		/**
		 * Bootstrap Treeview
		 *
		 * @see: https://github.com/jonmiles/bootstrap-treeview
		 */
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
		
		/**
		 * Ace Editor
		 *
		 * @see: https://ace.c9.io/
		 */
		aceEditor: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				if ( typeof ace != 'undefined' ) {
					var setup = ko.utils.unwrapObservable( valueAccessor() );
					var options = setup.options || {};
					var fileview = setup.file;
					var file = fileview.model();
					var editor = ace.edit(element);
					
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
							file.updateEditorOptions();
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
		
		/**
		 * Bootstrap Context Menu
		 *
		 * @see: https://github.com/dgoguerra/bootstrap-menu
		 */
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
		
		/**
		 * Render a bootstrap menu given an array of [MenuItem]
		 *
		 * 
		 */
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
		
		/**
		 * jQuery UI Layout
		 *
		 * @see: http://layout.jquery-dev.com
		 */
		layout: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				var options = ko.utils.unwrapObservable( valueAccessor() );
				
				options.center__onresize = 
				options.north__onresize =
				options.east__onresize =
				options.south__onresize = 
				options.west__onresize = function( pane_key, pane ) 
				{
					pane.trigger( 'resize', arguments );
					if ( pane_key in options && typeof options[ pane_key ].onresize == 'function' ) {
						options[ pane_key ].onresize.apply( this, arguments );
					}
				};
				
				$(element).layout( options );			
			}
		},
		
		/**
		 * Make an element size to fill out the remaining height inside a pane container
		 *
		 * @example: <div data-bind="fillPaneContainer: { 'pane': 'ui-layout-pane', container: '.column' }"></div>
		 */
		fillPaneContainer: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				var options = ko.utils.unwrapObservable( valueAccessor() );
				var container = $(element).closest(options.container);
				var fitElement = function() { $(element).css({ height: container.innerHeight() - ( $(element).offset().top - container.offset().top ) }); };
				$(element).closest(options.pane).on( 'resize', fitElement );
				fitElement();
			}		
		},
		
		/**
		 * Bind an arbitrary callback
		 */
		init: {
			init: function( element, valueAccessor, allBindingsAccessor ) {
				var callback = ko.utils.unwrapObservable( valueAccessor() );
				if ( typeof callback == 'function' ) {
					callback.call( element, allBindingsAccessor );
				}
			}
		},
		
		/**
		 * Bind an arbitrary callback
		 */
		callback: {
			update: function( element, valueAccessor, allBindingsAccessor ) {
				var callback = ko.utils.unwrapObservable( valueAccessor() );
				if ( typeof callback == 'function' ) {
					callback.call( element, allBindingsAccessor );
				}
			}
		},
		
		/**
		 * jQuery proxy
		 */
		jquery: {
			update: function( element, valueAccessor, allBindingsAccessor ) {
				var options = ko.utils.unwrapObservable( valueAccessor() );
				var el = $(element);
				$.each( options, function( key, props ) {
					if ( typeof el[key] == 'function' ) {
						el[key](props);
					}
				});
			}
		}
	});
	
	/**
	 * Custom knockout extenders
	 */
	_.extend( ko.extenders, 
	{
		/**
		 * Fill up an observable array incrementally with a timeout delay so that the
		 * operation can be performed with minimized blocking
		 *
		 * @see: https://github.com/thinkloop/knockout-js-progressive-filter
		 */
		progressiveFilter: function(target, args) 
		{
			var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame || function(callback) { setTimeout(callback, args.timeout || 200); },
				currentCount = 0,
				props = {};

			args = args || {};
			target.progressiveFilter = props;

			props.unfilteredCollection = [];
			props.unfilteredCollectionIndex = 0;
			props.isFiltering = ko.observable(false);
			props.filterFunction = args.filterFunction;
			props.batchSize = Math.max(parseInt(args.batchSize, 10), 1);

			props.add = args.addFunction || function(item) { target.peek().push(item); };
			props.clear = args.clearFunction || function() { target([]); };

			target.isFiltered = function(item) {
				return !props.filterFunction || props.filterFunction(item);
			};

			target.filter = function(unfilteredCollection) {
				var filteredCollection = [],
					i;
				for (i = 0; i < unfilteredCollection.length; i++) {
					if (target.isFiltered(unfilteredCollection[i])) {
						filteredCollection.push(unfilteredCollection[i]);
					}
				}
				props.clear();
				target(filteredCollection);
			};

			target.filterProgressive = function(unfilteredCollection) {
				props.unfilteredCollection = unfilteredCollection.slice(0);
				props.unfilteredCollectionIndex = 0;
				currentCount = 0;
				props.clear();
				if (!props.isFiltering.peek()) {
					props.isFiltering(true);
					requestAnimationFrame(doFilter);
				}
			};

			function doFilter() {
				var item;

				for (props.unfilteredCollectionIndex; props.unfilteredCollectionIndex < props.unfilteredCollection.length; props.unfilteredCollectionIndex++) {
					item = props.unfilteredCollection[props.unfilteredCollectionIndex];
					if (item && target.isFiltered(item)) {
						props.add(item);
						break;
					}
				}

				currentCount++;
				props.unfilteredCollectionIndex++;

				if (props.unfilteredCollectionIndex < props.unfilteredCollection.length) {
					if (currentCount >= props.batchSize) {
						target.valueHasMutated();
						currentCount = 0;
						requestAnimationFrame(doFilter);
					}
					else {
						currentCount++;
						doFilter();
					}
					return;
				}
				else {
					target.valueHasMutated();
					currentCount = 0;
					props.unfilteredCollectionIndex = 0;
					props.isFiltering(false);
				}
			}
		}	
	});
	
	/**
	 * Extend Knockout.js
	 */
	_.extend( ko, 
	{
		/**
		 * Create a search observable with a search function that returns search results
		 *
		 * @param	function		search				The search function (should return a deferred object)
		 * @param	function		interval			The debounce wait period
		 * @return	observable
		 */
		searchObservable: function( search, interval, immediate )
		{
			var observable = _.extend( ko.observable(''), {
				loading: ko.observable(false),
				results: ko.observable([])
			});
			
			// Keep track of the most recent search. Only update results for the latest request.
			var latestSearch;
			
			observable.subscribe( _.debounce( function( value ) {
				observable.loading(true);
				var currentSearch = latestSearch = search( value );
				$.when( currentSearch ).done( function( results ) {
					if ( currentSearch === latestSearch ) {
						if ( typeof results !== 'undefined' ) {
							observable.results( results );
						}
						observable.loading(false);
					}
				});
			}, interval || 500, immediate ));
			
			return observable;
		}
	});
	
	/**
	 * Fit the studio to the window when page is loaded
	 */
	$(document).ready( function() 
	{
		var _window = $(window);
		var container = $('#mwp-studio-container');
		_window.resize( function() {
			container.css({ height: _window.height() - 32, width: _window.width() - container.offset().left });
		}).resize();
		
		setTimeout( function() { $(window).resize(); }, 200 );
	});
	
})( jQuery );
 