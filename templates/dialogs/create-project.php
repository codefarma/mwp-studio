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
 * $content = $plugin->getTemplateContent( 'dialogs/create-project' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="form-group">
	<label for="name">Plugin Name</label>
	<input type="text" class="form-control" data-bind="textInput: name" id="name" />
</div>
<div class="form-group">
	<label for="description">Description</label>
	<input type="text" class="form-control" data-bind="textInput: description" id="description" />
</div>
<div class="form-group">
	<label for="author">Author</label>
	<input type="text" class="form-control" data-bind="textInput: author" id="author" />
</div>
<div class="form-group">
	<label for="authorurl">Author URL</label>
	<input type="text" class="form-control" data-bind="textInput: authorurl" id="authorurl" />
</div>
<div class="form-group">
	<label for="pluginurl">Plugin URL</label>
	<input type="text" class="form-control" data-bind="textInput: pluginurl" id="pluginurl" />
</div>
<div class="form-group">
	<label for="slug">Slug</label>
	<input type="text" class="form-control" data-bind="textInput: slug" id="slug" />
</div>

