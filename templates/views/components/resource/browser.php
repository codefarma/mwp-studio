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
 * $content = $plugin->getTemplateContent( 'views/components/resource/browser' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="breadcrumb" data-view-model="mwp-studio" data-bind="with: currentPlugin()">
	<div class="btn-group btn-flex">
	  <button type="button" style="border-right: 0;" class="btn btn-default" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-sitemap"></i> &nbsp;&nbsp;<span data-bind="text: name"></span></button>
	  <ul class="dropdown-menu pull-left" data-bind="foreach: $root.plugins">
		<li><a href="#" data-bind="click: function(){ model().switchToPlugin(); }"><i class="fa fa-folder-open"></i> &nbsp;&nbsp;<span data-bind="text: name"></span></a></li>
	  </ul>
	  <div class="btn-group">
		  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
			<span class="sr-only">Toggle Dropdown</span>
		  </button>
		  <ul class="dropdown-menu pull-right" data-bind="bootstrapMenu: $root.env().pluginMenuItems"></ul>
	  </div>
	</div>
</div>

<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#files" aria-controls="files" role="tab" data-toggle="tab">Files</a></li>
</ul>
<div class="panel panel-default tabbed-panel" 
	data-bind="
		fillPaneContainer: { pane: '.ui-layout-pane', container: '.column' },
		studioActivity: ( ! currentPlugin() ? false : { active: currentPlugin().model().fileTree.loading() } )
	" style="overflow-y: scroll; margin-bottom: 0">
	<div id="files" role="tabpanel" class="files-tabpanel tab-pane active">
		<?php echo $this->getTemplateContent( 'views/components/resource/filetree' ) ?>
	</div>
	<div id="classes" role="tabpanel" class="tab-pane">
	
	</div>
	<div id="views" role="tabpanel" class="tab-pane">
	
	</div>
</div>