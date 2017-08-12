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
<nav class="navbar navbar-default" role="navigation" data-view-model="mwp-studio" style="margin-bottom: 0">
  <div class="container-fluid">
	<div class="navbar-header">
	  <span class="navbar-brand">
		<i class="fa fa-wordpress fa-lg"></i>
		<h4 style="display:inline">MWP</h4>
	  </span>
	  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#studio-navbar">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	  </button>
	</div>
	<div class="collapse navbar-collapse" id="studio-navbar">
	  <ul class="nav navbar-nav" data-bind="bootstrapMenu: $root.env().studioMenuItems"></ul>
	</div>
  </div>
</nav>
	