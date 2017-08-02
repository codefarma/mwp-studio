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
		  <ul class="dropdown-menu" role="menu">
			<li><a href="#">Create A New Plugin</a></li>
		    <li class="dropdown-submenu">
			  <a tabindex="-1" href="javascript:;">Open Plugin In Studio</a>
			  <ul data-bind="foreach: plugins" class="dropdown-menu">
				<li><a href="javascript:;" data-bind="click: function(){ model().switchToPlugin(); }"><span data-bind="text: name"></span></a></li>
			  </ul>
			</li>
		  </ul>
		</li>
	  </ul>
	</div>
  </div>
</nav>
	