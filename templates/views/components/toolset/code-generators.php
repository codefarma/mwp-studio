<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 13, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/toolset/code-generators', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="text-center">

	<button class="btn btn-default">
		<i class="fa fa-2x fa-bolt btn-icon"></i>
		<span class="btn-label">Action Hook</span>
	</button>

	<button class="btn btn-default">
		<i class="fa fa-2x fa-filter btn-icon"></i>
		<span class="btn-label">Data Filter</span>
	</button>

	<button class="btn btn-default">
		<i class="fa fa-2x fa-code btn-icon"></i>
		<span class="btn-label">Shortcode</span>
	</button>

	<button class="btn btn-default">
		<i class="fa fa-2x fa-thumb-tack btn-icon"></i>
		<span class="btn-label">Post Type</span>
	</button>

	<button class="btn btn-default">
		<i class="fa fa-2x fa-square-o btn-icon"></i>
		<span class="btn-label">Meta Box</span>
	</button>

	<button class="btn btn-default">
		<i class="fa fa-2x fa-tag btn-icon"></i>
		<span class="btn-label">Taxonomy</span>
	</button>

	<button class="btn btn-default">
		<i class="fa fa-2x fa-database btn-icon"></i>
		<span class="btn-label">WP Query</span>
	</button>
	
	<button class="btn btn-default">
		<i class="fa fa-2x fa-dashboard btn-icon"></i>
		<span class="btn-label">Dash Widget</span>
	</button>
	
	<button class="btn btn-default">
		<i class="fa fa-2x fa-cogs btn-icon"></i>
		<span class="btn-label">Settings</span>
	</button>
	
	<button class="btn btn-default">
		<i class="fa fa-2x fa-bars btn-icon"></i>
		<span class="btn-label">Menu</span>
	</button>
	
	<button class="btn btn-default">
		<i class="fa fa-2x fa-columns btn-icon"></i>
		<span class="btn-label">Sidebar</span>
	</button>
	
	<button class="btn btn-default">
		<i class="fa fa-2x fa-cube btn-icon"></i>
		<span class="btn-label">Widget</span>
	</button>
	
	
</div>