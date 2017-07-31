<?php
/**
 * Plugin HTML Template
 *
 * Created:  May 1, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/global/layout' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	array		$output		Output components
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div data-view-model="mwp-studio" class="mwp-studio mwp-bootstrap wrap">

	<nav class="navbar navbar-default" role="navigation">
	  <div class="container-fluid">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
		  <span class="navbar-brand">
			<i class="fa fa-wordpress fa-lg"></i>
			<h4 style="display:inline">MWP</h4>
		  </span>
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		</div>
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		  <ul class="nav navbar-nav">
			<li class="dropdown">
			  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Studio <b class="caret"></b></a>
			  <ul class="dropdown-menu" role="menu">
				<li><a href="#">New Plugin</a></li>
			    <li class="dropdown-submenu">
				  <a tabindex="-1" href="javascript:;">Switch Plugin</a>
				  <ul data-bind="foreach: plugins" class="dropdown-menu">
					<li><a href="javascript:;" data-bind="click: function(){ model().switchToPlugin(); }"><span data-bind="text: name"></span></a></li>
				  </ul>
				</li>
				<li class="divider"></li>
				<li class="dropdown-header">Operations</li>
				<li><a href="#">Edit Plugin Info</a></li>
				<li><a href="#">Edit Database</a></li>
				<li><a href="#">Edit Dependencies</a></li>
				<li class="divider"></li>
				<li><a href="#">Build Plugin</a></li>
				<li class="divider"></li>
				<li class="disabled"><a href="#">Delete Plugin</a></li>
			  </ul>
			</li>
		  </ul>
		</div><!-- /.navbar-collapse -->
	  </div><!-- /.container-fluid -->
	</nav>
	
	<div class="row">
		<div class="col-md-3">
			<div class="breadcrumb" data-bind="with: currentPlugin()">
				<div class="btn-group btn-flex">
				  <button type="button" style="border-right: 0;" class="btn btn-default" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-sitemap"></i> &nbsp;&nbsp;<span data-bind="text: name"></span></button>
				  <ul class="dropdown-menu pull-left" data-bind="foreach: $root.plugins">
					<li><a href="javascript:;" data-bind="click: function(){ model().switchToPlugin(); }"><i class="fa fa-folder-open"></i> &nbsp;&nbsp;<span data-bind="text: name"></span></a></li>
				  </ul>
				  <div class="btn-group">
					  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu pull-right">
					    <li class="dropdown-header">Meta Information</li>
						<li><a href="#"><i class="fa fa-info-circle"></i> Edit Plugin Info</a></li>
						<li><a href="#"><i class="fa fa-database"></i> Edit Database Tables</a></li>
						<li><a href="#"><i class="fa fa-sitemap"></i> Edit Dependencies</a></li>
						<li class="divider" role="separator"></li>
						<li class="dropdown-header">Resources</li>
						<li><a href="#" data-bind="click: function(){ $root.controller.addClassDialog(); }"><i class="fa fa-code"></i> Add PHP Class</a></li>
						<li><a href="#"><i class="fa fa-code"></i> Add HTML Template</a></li>
						<li><a href="#"><i class="fa fa-code"></i> Add Stylesheet</a></li>
						<li><a href="#"><i class="fa fa-code"></i> Add Javascript</a></li>
						<li class="divider" role="separator"></li>
						<li><a href="#"><i class="fa fa-cogs"></i> Build Plugin</a></li>
					  </ul>
				  </div>
				</div>
			</div>
			
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Files</a></li>
				<li role="presentation"><a href="#classes" aria-controls="files" role="tab" data-toggle="tab">Classes</a></li>
				<li role="presentation"><a href="#views" aria-controls="files" role="tab" data-toggle="tab">Templates</a></li>
			</ul>
			<div class="panel panel-default tabbed-panel" style="max-height: 500px; overflow-y: scroll;">
				<div id="files" role="tabpanel" class="files-tabpanel tab-pane active">
					<?php echo $this->getTemplateContent( 'views/components/filetree' ) ?>
				</div>
				<div id="classes" role="tabpanel" class="tab-pane">
				
				</div>
				<div id="views" role="tabpanel" class="tab-pane">
				
				</div>
			</div>
		</div>
		<div class="col-md-6 ace-editors">
			
			<div class="breadcrumb">
				<div class="btn-group pull-right">
				  <button type="button" class="btn btn-default" disabled="disabled" data-bind="css: { 'btn-success': activeFile() && activeFile().model().edited() }, disable: ( ! activeFile() ) || ( ! activeFile().model().edited() ), click: function() { activeFile().model().saveFile(); activeFile().model().editor.focus(); }"><i class="fa fa-floppy-o"></i> Save</button>
				  <button type="button" class="btn btn-default dropdown-toggle" data-bind="css: { 'btn-success': activeFile() && activeFile().model().edited() }" data-toggle="dropdown">
					<span class="caret"></span>
					<span class="sr-only">Toggle Dropdown</span>
				  </button>
				  <ul class="dropdown-menu" role="menu">
					<li><a href="#">Save All</a></li>
				  </ul>
				</div>
				<ul class="breadcrumb" style="margin-bottom: 0; padding: 5px 0;" data-bind="foreach: activeFileBreadcrumbs">
					<li><span data-bind="text: $data"></span></li>
				</ul>
			</div>
		
			<ul class="nav nav-tabs" data-bind="foreach: openFiles" role="tablist">
				<li role="presentation" class="">
					<a aria-controls="files" role="tab" data-bind="attr: { href: '#' + id(), id: 'id-' + id() }, event: { 'shown.bs.tab': function(){ ko.dataFor(jQuery('#'+id())[0]).model().editor.focus(); } }" data-toggle="tab">
						<i data-bind="attr: { class: icon }"></i>
						<span>
							<span data-bind="text: text"></span>
							<span data-bind="visible: model().edited">*</span>
						</span>
						<span title="Close" class="btn btn-xxs btn-tab-xs" href="#" data-bind="click: function() { model().closeFile(); }, css: { 'btn-danger': model().edited }"><i class="fa fa-close"></i></span>
					</a>
				</li>
			</ul>
			<div class="panel panel-default tabbed-panel" style="min-height: 500px;" data-bind="foreach: openFiles">
				<div data-bind="attr: { id: id() }" role="tabpanel" class="tab-pane">
					<div style="min-height: 500px;" class="tabbed-editor" data-bind="aceEditor: { file: $data, options: { switchTo: function() { jQuery('#id-'+id()).tab('show'); } } }"></div>
				</div>
			</div>
		</div>
		
		<div class="col-md-3 mwp-toolbox">
			<div class="breadcrumb">
				<h5>Toolbox</h5>
			</div>
		</div>
		
	</div>
	
</div>

<?php echo $this->getTemplateContent( 'dialogs/class-form' ) ?>