<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 22, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/web-browser' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div data-bind="style: { height: bodyHeight() + 'px' }">
<iframe class="web-browser-iframe" data-bind="attr: { src: url }"></iframe>
</div>