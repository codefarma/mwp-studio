<?php
/**
 * Plugin HTML Template
 *
 * Created:  July 29, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/filetree' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div data-view-model="mwp-studio" class="mwp-bootstrap">
	<div class="file-treeview" data-bind="with: currentPlugin()">
		<div class="treeview" data-bind="treeView: filenodes(), contextMenu: { selector: '#files .list-group-item[data-nodeId]', config: mwp.model.get('mwp-studio-filetree-node').contextMenu }"></div>
	</div>
</div>