<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 12, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'snippets/menus/item-header' ); 
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
<li data-bind="attr: { class: classes() + ' dropdown-header' }">
  <i data-bind="if: icon(), attr: { class: icon() }"></i> 
  <span data-bind="text: title"></span>
</li>