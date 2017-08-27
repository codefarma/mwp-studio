<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 18, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/south-pane' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="column" style="height: 100%">

	<ul class="nav nav-tabs" role="tablist" data-bind="foreach: { data: env().studioPaneTabs, afterRender: function() { jQuery('.ui-layout-south').trigger('resize'); } }">
		<li role="presentation" data-bind="css: { active: $index() == 0 }"><a data-bind="attr: { href: '#' + id }, text: title" role="tab" data-toggle="tab"></a></li>
	</ul>

	<div class="panel panel-default tabbed-panel full-height" data-bind="fillPaneContainer: { pane: '.ui-layout-south', container: '.column' }" style="overflow-y: scroll; margin-bottom: 0">
		<div class="tab-content full-height" data-bind="foreach: env().studioPaneTabs">
			<div data-bind="attr: { id: id }, css: { active: $index() == 0 }, template: { nodes: template, data: viewModel }" role="tabpanel" class="tab-pane full-height"></div>
		</div>
	</div>
</div>
