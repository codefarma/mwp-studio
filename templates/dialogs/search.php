<?php
/**
 * Plugin HTML Template
 *
 * Created:  November 16, 2017
 *
 * @package  MWP Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/search' ); 
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

<div class="search-window">
	<input type="text" class="search-input form-control" data-bind="
		textInput: phrase, 
		enterKey: doSearch
	">
	<div class="search-results-listing">
		
	</div>
</div>