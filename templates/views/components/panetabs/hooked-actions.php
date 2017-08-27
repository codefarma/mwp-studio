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
	<table class="table">
		<thead>
			<tr>
				<td>Action</td>
				<td>Callback</td>
				<td>Args</td>
				<td>Priority</td>
				<td>File</td>
				<td>Line</td>
			</tr>
		</thead>
		<tbody data-bind="foreach: model().actions">
			<tr>
				<td data-bind="text: hook_name"></td>
				<td data-bind="text: hook_callback_name"></td>
				<td data-bind="text: hook_args"></td>
				<td data-bind="text: hook_priority"></td>
				<td data-bind="text: hook_file"></td>
				<td data-bind="text: hook_line"></td>
			</tr>
		</tbody>
	</table>
  </div>
</div>
