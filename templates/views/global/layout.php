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
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		  <div class="navbar-brand" data-bind="with: currentPlugin()"><span data-bind="text: name"></span></div>
		</div>
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		  <ul class="nav navbar-nav">
			<li class="dropdown">
			  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Plugin <b class="caret"></b></a>
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
		  <div class="navbar-text navbar-right"><h4 style="display:inline">MWP Studio</h4></div>
		</div><!-- /.navbar-collapse -->
	  </div><!-- /.container-fluid -->
	</nav>
	
	<div class="row">
		<div class="col-md-4">
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
			<ul class="nav nav-tabs" data-bind="foreach: openFiles" role="tablist">
				<li role="presentation" class=""><a aria-controls="files" role="tab" data-bind="text: text, attr: { href: '#' + id(), id: 'id-' + id() }" data-toggle="tab"></a></li>
			</ul>
			<div class="panel panel-default tabbed-panel" data-bind="foreach: openFiles">
				<div data-bind="attr: { id: id() }" role="tabpanel" class="tab-pane">
					<div class="tabbed-editor" data-bind="aceEditor: { model: model(), showtab: '#id-' + id() }" style="width: 100%; height: 500px;"></div>
				</div>
			</div>
		</div>
	</div>
	
</div>