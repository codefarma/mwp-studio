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

	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#hooked-actions" aria-controls="tasks" role="tab" data-toggle="tab">Hooked Actions</a></li>
		<li role="presentation"><a href="#hooked-filters" aria-controls="tasks" role="tab" data-toggle="tab">Hooked Filters</a></li>
		<li role="presentation"><a href="#shortcodes" aria-controls="tasks" role="tab" data-toggle="tab">Shortcodes</a></li>
		<li role="presentation"><a href="#post-types" aria-controls="tasks" role="tab" data-toggle="tab">Post Types</a></li>
		<li role="presentation"><a href="#meta-boxes" aria-controls="tasks" role="tab" data-toggle="tab">Meta Boxes</a></li>
		<li role="presentation"><a href="#ajax-handlers" aria-controls="tasks" role="tab" data-toggle="tab">Ajax Handlers</a></li>
		<li role="presentation"><a href="#api-endpoints" aria-controls="tasks" role="tab" data-toggle="tab">API Endpoints</a></li>
	</ul>

	<div class="panel panel-default tabbed-panel" data-bind="fillPaneContainer: { pane: '.ui-layout-south', container: '.column' }" style="overflow-y: scroll; margin-bottom: 0">
		<div id="hooked-actions" role="tabpanel" class="tab-pane active">
			<?php echo $this->getTemplateContent( 'views/components/south-pane/actionlist' ) ?>
		</div>
		<div id="hooked-filters" role="tabpanel" class="tab-pane">

		</div>
		<div id="shortcodes" role="tabpanel" class="tab-pane">

		</div>
		<div id="post-types" role="tabpanel" class="tab-pane">

		</div>
		<div id="meta-boxes" role="tabpanel" class="tab-pane">

		</div>
		<div id="ajax-handlers" role="tabpanel" class="tab-pane">

		</div>
		<div id="api-endpoints" role="tabpanel" class="tab-pane">

		</div>
	</div>
</div>
