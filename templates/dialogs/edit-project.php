<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 24, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/edit-project' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="form-horizontal">
	<div class="form-group">
		<label for="project-name" class="col-sm-3">Project Name</label>
		<div class="col-sm-9">
			<input type="text" id="project-name" data-bind="textInput: project.name" class="form-control" />
		</div>
	</div>
	<div class="form-group">
		<label for="project-url" class="col-sm-3">Project URL</label>
		<div class="col-sm-9">
			<input type="text" id="project-url" data-bind="textInput: project.url" class="form-control" />
		</div>
	</div>
	<div class="form-group">
		<label for="project-description" class="col-sm-3">Description</label>
		<div class="col-sm-9">
			<input type="text" id="project-description" data-bind="textInput: project.description" class="form-control" />
		</div>
	</div>
	<div class="form-group">
		<label for="project-author" class="col-sm-3">Author Name</label>
		<div class="col-sm-9">
			<input type="text" id="project-author" data-bind="textInput: project.author" class="form-control" />
		</div>
	</div>
	<div class="form-group">
		<label for="project-author-url" class="col-sm-3">Author URL</label>
		<div class="col-sm-9">
			<input type="text" id="project-author-url" data-bind="textInput: project.author_url" class="form-control" />
		</div>
	</div>
</div>