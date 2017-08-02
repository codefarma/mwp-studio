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
 * $content = $plugin->getTemplateContent( 'views/components/files-editor' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	string		$title		The provided title
 * @param	string		$content	The provided content
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div data-view-model="mwp-studio" class="ace-editors">
	<div class="breadcrumb">
		<div class="btn-group pull-right">
		  <button type="button" class="btn btn-default" disabled="disabled" data-bind="css: { 'btn-success': activeFile() && activeFile().model().edited() }, disable: ( ! activeFile() ) || ( ! activeFile().model().edited() ), click: function() { activeFile().model().saveFile(); activeFile().model().editor.focus(); }"><i class="fa fa-floppy-o"></i> Save</button>
		  <button type="button" class="btn btn-default dropdown-toggle" data-bind="css: { 'btn-success': activeFile() && activeFile().model().edited() }" data-toggle="dropdown">
			<span class="caret"></span>
			<span class="sr-only">Toggle Dropdown</span>
		  </button>
		  <ul class="dropdown-menu" role="menu">
			<li><a href="#">Save All</a></li>
		  </ul>
		</div>
		<ul class="breadcrumb" style="margin-bottom: 0; padding: 5px 0;" data-bind="foreach: activeFileBreadcrumbs">
			<li><span data-bind="text: $data"></span></li>
		</ul>
	</div>

	<ul class="nav nav-tabs" data-bind="foreach: openFiles" role="tablist">
		<li role="presentation" class="">
			<a aria-controls="files" role="tab" data-bind="attr: { href: '#' + id(), id: 'id-' + id() }, event: { 'shown.bs.tab': function(){ ko.dataFor(jQuery('#'+id())[0]).model().editor.focus(); } }" data-toggle="tab">
				<i data-bind="attr: { class: icon }"></i>
				<span>
					<span data-bind="text: text"></span>
					<span data-bind="visible: model().edited">*</span>
				</span>
				<span title="Close" class="btn btn-xxs btn-tab-xs" href="#" data-bind="click: function() { model().closeFile(); }, css: { 'btn-danger': model().edited }"><i class="fa fa-close"></i></span>
			</a>
		</li>
	</ul>
	<div class="panel panel-default tabbed-panel" style="min-height: 500px;" data-bind="foreach: openFiles">
		<div data-bind="attr: { id: id() }" role="tabpanel" class="tab-pane">
			<div style="min-height: 500px;" class="tabbed-editor" data-bind="aceEditor: { file: $data, options: { switchTo: function() { jQuery('#id-'+id()).tab('show'); } } }"></div>
		</div>
	</div>
</div>