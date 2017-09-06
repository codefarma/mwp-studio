<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 3, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/panetabs/hooks', array( 'hook_type' => 'actions' ) ); 
 * ```
 * 
 * @param	Plugin		$this		    The plugin instance which is loading this template
 *
 * @param	string		$hook_type	    The type of hooks to display
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<div class="full-height" data-bind="with: currentPlugin()">
  <div class="full-height" data-bind="studioActivity: model().<?php echo $hook_type ?>.loading() || model().<?php echo $hook_type ?>.progressiveFilter.isFiltering()">
	<table class="table table-striped table-fixed pane-table studio-hooks-table">
		<thead>
			<tr>
				<th style="width: 100px">Type</th>
				<th>Action</th>
				<th>Callback</th>
				<th>File</th>
			</tr>
		</thead>
		<tbody data-bind="foreach: model().<?php echo $hook_type ?>, fillPaneContainer: { pane: '.ui-layout-south', container: '.tab-pane' }">
			<tr>
				<td style="width: 100px">
					<span data-bind="text: hook_type" style="padding-right:15px"></span>
				</td>
				<td class="overflow-ellipsis">
					<a href="#" style="margin-right: 10px" data-bind="
						click: function() { $root.hookSearch( hook_name ); },
						jquery: { tooltip: { title: '<i class=\'fa fa-search-plus\'></i> Inspect Hook', html: true } }
						"><i class="fa fa-search-plus"></i></a>
					<a href="#" 
						data-bind="
						text: hook_name,
						jquery: { tooltip: { title: '<i class=\'fa fa-search-plus\'></i> Inspect Code', html: true } },
						click: function() {
							mwp.model.get('mwp-studio-filetree-node').loadFile( hook_file ).done( function( file ) {
								file.switchTo().done( function( editor ) {
									editor.gotoLine( hook_line );
									setTimeout( function() { editor.gotoLine( hook_line ); }, 500 );
								});
							});
						}
					"></a>
				</td>
				<td class="overflow-ellipsis">
					<span data-bind="if: hook_callback_type !== 'closure'">
						<a href="#" data-toggle="popover" data-trigger="hover" data-bind="
							text: hook_callback_name,
							jquery: {
								popover: {
									title: '<i class=\'fa fa-search-plus\'></i> Inspect Callback',
									content: callback_signature,
									html: true,
									placement: 'left',
									trigger: 'hover'
								}
							},
							click: function() { 
								mwp.model.get('mwp-studio-filetree-node').loadCallbackFile( hook_callback_name, hook_callback_class ).done( function( file, callback ) {
									file.switchTo().done( function() {
										file.editor.gotoLine( callback.function_line );
										setTimeout( function() { file.editor.gotoLine( callback.function_line ); }, 500 );
									});
								}).fail( function() {
									bootbox.alert({title: 'File Not Found', message: 'The callback function could not be explicitly located.'});
								});
							}
						"></a>
					</span>
					<span data-bind="if: hook_callback_type == 'closure'">{closure}</span>
				</td>
				<td class="overflow-ellipsis">
					<a href="#" data-bind="
						text: hook_file.split('/').pop(),
						jquery: {
							tooltip: {
								title: hook_file.split('/').slice(0,-1).join('/') + '/',
								placement: 'left',
								trigger: 'hover'
							}
						},
						click: function() {
							mwp.model.get('mwp-studio-filetree-node').loadFile( hook_file ).done( function( file ) {
								file.switchTo();
							});
						}
					"></a>
				</td>
			</tr>
		</tbody>
	</table>
  </div>
</div>
