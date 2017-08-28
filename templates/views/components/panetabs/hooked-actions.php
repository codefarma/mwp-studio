<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 21, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'panetabs/hooked-actions', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
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
<div class="full-height" data-bind="with: currentPlugin()">
  <div class="full-height" data-bind="studioActivity: model().actions.loading() || model().actions.progressiveFilter.isFiltering()">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Action</th>
				<th>Callback</th>
				<th>File</th>
			</tr>
		</thead>
		<tbody data-bind="foreach: model().actions">
			<tr>
				<td><a href="#" data-bind="click: function() { $root.hookSearch( hook_name ); }, text: hook_name"></a></td>
				<td data-bind="text: hook_callback_name"></td>
				<td>
					<a href="#" 
						data-bind="attr: { title: hook_file }, text: hook_file.split('/').pop(),
						click: function() {
							var file = $parent.model().fileTree.findChild( 'nodes', function( node ) {
								return node.get('path') == hook_file;
							});
							if ( file ) {
								file.switchTo().done( function( editor ) {
									editor.gotoLine( hook_line );
									setTimeout( function() { editor.gotoLine( hook_line ); }, 500 );
								});
							}
						}
					"></a>
				</td>
			</tr>
		</tbody>
	</table>
  </div>
</div>
