<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 2, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
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

<div class="breadcrumb" data-view-model="mwp-studio" data-bind="with: currentProject()" style="min-height: 50px">
	<div class="btn-group btn-flex">
	  <button type="button" style="border-right: 0; max-width: calc(100% - 30px); overflow: hidden;" class="btn btn-default" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<i class="fa" data-bind="css: { 'fa-plug': type() == 'plugin', 'fa-paint-brush': type() == 'theme' }"></i> &nbsp;&nbsp;<span data-bind="text: name"></span>
	  </button>
	  <ul class="dropdown-menu pull-left" data-bind="bootstrapMenu: $root.env().projectsMenu"></ul>
	  <div class="btn-group">
		  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
			<span class="sr-only">Toggle Dropdown</span>
		  </button>
		  <ul class="dropdown-menu pull-right" data-bind="bootstrapMenu: $root.env().projectMenuItems"></ul>
	  </div>
	</div>
</div>

<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#files" aria-controls="files" role="tab" data-toggle="tab"><i class="fa fa-files-o"></i> Files</a></li>
	<li role="presentation"><a href="#components" aria-controls="components" role="tab" data-toggle="tab"><i class="fa fa-puzzle-piece"></i> Components</a></li>
</ul>
<div class="panel panel-default tabbed-panel" 
	data-bind="
		fillPaneContainer: { pane: '.ui-layout-pane', container: '.column' },
		studioActivity: ( ! currentProject() ? false : { active: currentProject().model().fileTree.loading() } )
	" style="overflow-y: scroll; margin-bottom: 0">
	<div class="tab-content full-height">
		<div id="files" role="tabpanel" class="files-tabpanel tab-pane full-height active">
			<?php echo $this->getTemplateContent( 'views/components/resource/filetree' ) ?>
		</div>
		<div id="components" role="tabpanel" class="tab-pane full-height">
			Future home of the component browser.
		</div>
	</div>
</div>