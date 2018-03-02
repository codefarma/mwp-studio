<?php
/**
 * Plugin HTML Template
 *
 * Created:  November 13, 2017
 *
 * @package  MWP Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'snippets/search/result/summary' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * View Model [SearchResult]
 *
 * @var	string			title			The result title
 * @var	string			snippet			The result summary snippet
 * @var string			content			The result details content
 * @var	string			type			The result type
 */
?>

<div class="search-result-summary">
	<strong data-bind="text: title"><strong><br>
	<div class="search-result-snippet" data-bind="text: snippet"></div>
</div>


