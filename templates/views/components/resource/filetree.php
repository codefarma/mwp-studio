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
	<div id="file-treeview" class="file-treeview" data-bind="with: currentProject()">
		<div class="treeview" data-bind="treeView: filenodes(), 
			contextMenu: { 
				selector: '#file-treeview .list-group-item[data-nodeId]', 
				config: {
					fetchElementData: function(el) { return jQuery(el).closest('.treeview').treeview('getNode', jQuery(el).data('nodeid')); },
					actions: $root.env().fileContextActions()
				}
			}
		"></div>
	</div>
</div>