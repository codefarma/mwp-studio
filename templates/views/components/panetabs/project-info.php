<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 13, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/panetabs/project-info', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="project-info full-height overflow-hidden" data-bind="with: currentProject()">
    <span class="project-name" data-bind="text: name"></span> <span class="label label-info" data-bind="text: version"></span>
	<div class="project-description" data-bind="html: description"></div>
</div>