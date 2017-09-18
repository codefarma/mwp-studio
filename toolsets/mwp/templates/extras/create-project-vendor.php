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
 * $content = $plugin->getToolTemplate( 'extras/create-plugin' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="row vendor-name" data-bind="visible: projectType() == 'plugin' && pluginFramework() == 'mwp'">
	<div class="col-sm-6">
		<div class="form-group">
			<label for="vendor">Vendor Name</label>
			<input type="text" class="form-control" id="vendor" data-bind="textInput: vendor" />
		</div>
	</div>
	<div class="col-sm-6">
		<div class="form-group">
			<label for="namespace">Namespace</label>
			<input type="text" id="namespace" class="form-control" data-bind="
				textInput: namespace, 
				callback: function() {
					var input = jQuery(this);
					input.on( 'change', function() {
						namespace.custom = input.val() ? true : false;
					});
				}"/>
		</div>
	</div>
</div>