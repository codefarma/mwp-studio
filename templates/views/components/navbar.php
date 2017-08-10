<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 2, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/navbar' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<nav class="navbar navbar-default" role="navigation" data-view-model="mwp-studio">
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
		  <ul class="dropdown-menu" role="menu" data-bind="foreach: $root.env().studioMenuElements">
		    <li data-bind="if: type == 'header', visible: type == 'header'" class="dropdown-header"><span data-bind="text: title"></span></li>
			<li data-bind="if: type == 'action', visible: type == 'action'">
				<a href="#" data-bind="click: callback">
					<i data-bind="if: $element.icon, attr: { class: icon }"></i> 
					<span data-bind="text: title"></span>
				</a>
			</li>
			<li data-bind="if: type == 'divider', visible: type == 'divider'" class="divider" role="separator"></li>
		  </ul>
		</li>
	  </ul>
	</div>
  </div>
</nav>
	