<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 20, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/statusbar' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="ui-layout-statusbar">
	<div class="col-xs-4">
		<div data-bind="with: processStatus">
			<i data-bind="attr: { class: icon }"></i> <span data-bind="text: status"></span>
		</div>
	</div>
	<div class="col-xs-4"></div>
	<div class="col-xs-4">
		<span data-bind="text: statustext"></span>
	</div>
</div>