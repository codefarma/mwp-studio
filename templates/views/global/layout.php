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
			<ul class="breadcrumb" data-bind="with: currentPlugin()">
				<li><i class="fa fa-sitemap"></i> &nbsp;&nbsp;<span data-bind="text: name"></span>
			</ul>
			
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Files</a></li>
				<li role="presentation"><a href="#classes" aria-controls="files" role="tab" data-toggle="tab">Classes</a></li>
				<li role="presentation"><a href="#views" aria-controls="files" role="tab" data-toggle="tab">Views/Templates</a></li>
			</ul>
			<div class="panel panel-default tabbed-panel">
				<div class="panel-body">
					<div class="tab-content">
						<div id="files" role="tabpanel" class="tab-pane active">
							<?php echo $this->getTemplateContent( 'views/components/filetree' ) ?>
						</div>
						<div id="classes" role="tabpanel" class="tab-pane">
						
						</div>
						<div id="views" role="tabpanel" class="tab-pane">
						
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8 ace-editors">
			<ul class="breadcrumb" data-bind="foreach: activeFileBreadcrumbs">
				<li><span data-bind="text: $data"></span></li>
			</ul>
		
			<ul class="nav nav-tabs" data-bind="foreach: openFiles" role="tablist">
				<li role="presentation" class="">
					<a aria-controls="files" role="tab" data-bind="attr: { href: '#' + id(), id: 'id-' + id() }, event: { 'shown.bs.tab': function(){ ko.dataFor(jQuery('#'+id())[0]).model().editor.focus(); } }" data-toggle="tab">
						<i data-bind="attr: { class: icon }"></i>
						<span>
							<span data-bind="text: text"></span>
							<span data-bind="visible: model().changed">*</span>
						</span>
						<span title="Close" class="btn btn-xxs btn-tab-xs" href="#" data-bind="click: function() { model().closeFile(); }"><i class="fa fa-close"></i></span>
						<span title="Save" class="btn btn-xxs btn-tab-xs btn-success" href="#" data-bind="visible: model().changed, click: function() { model().saveFile(); }"><i class="fa fa-check"></i></span>
					</a>
				</li>
			</ul>
			<div class="panel panel-default tabbed-panel" style="min-height: 500px;" data-bind="foreach: openFiles">
				<div data-bind="attr: { id: id() }" role="tabpanel" class="tab-pane">
					<div style="min-height: 500px;" class="tabbed-editor" data-bind="aceEditor: { file: $data, options: { switchTo: function() { jQuery('#id-'+id()).tab('show'); } } }"></div>
				</div>
			</div>
		</div>
	</div>
	
</div>