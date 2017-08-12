<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 12, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'snippets/menus/item-dropdown' ); 
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
<li data-bind="attr: { class: classes() + ' dropdown' }">
  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
    <i data-bind="if: icon(), attr: { class: icon() }"></i> 
    <span data-bind="text: title"></span> <b class="caret"></b>
  </a>
  <ul data-bind="bootstrapMenu: subitems()()" role="menu" class="dropdown-menu"></ul>
</li>