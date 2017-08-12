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
 * $content = $plugin->getTemplateContent( 'snippets/menus/item-action' ); 
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

/**
 * View Model [ kb.viewModel( MenuItem ) ]
 *
 * @var	string		type			'action'
 * @var string		icon			Icon Class
 * @var string		title			Menu item title
 * @var	string		classes			Element classes
 * @var	function	callback		Click handler
 */
?>
<li data-bind="attr: { class: classes() }">
  <a href="#" data-bind="click: function(){ callback()(); }">
    <i data-bind="if: icon(), attr: { class: icon() }"></i> 
    <span data-bind="text: title"></span>
  </a>
</li>