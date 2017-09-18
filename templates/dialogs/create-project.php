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

<div class="create-project-window-content">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label>Project Type</label>
				<div class="radio radio-primary" data-bind="foreach: projectTypes">
					<input type="radio" name="create-project-type" data-bind="
						attr: { 
							id: 'create-project-type-' + value,
							value: value
						}, 
						checked: $root.projectType" /> <label data-bind="text: name, attr: { for: 'create-project-type-' + value }"></label><br>
				</div>
			</div>
		</div>
		<div class="col-sm-6" data-bind="visible: projectType() == 'plugin'">
			<div class="form-group">
				<label>Framework</label>
				<div class="radio radio-primary" data-bind="foreach: pluginFrameworks">
					<input type="radio" name="create-project-plugin-framework" data-bind="
						attr: { 
							id: 'create-project-plugin-framework-' + value,
							value: value
						}, 
						checked: $root.pluginFramework" /> <label data-bind="text: name, attr: { for: 'create-project-plugin-framework-' + value }"></label><br>
				</div>
			</div>
		</div>
		<div class="col-sm-6" data-bind="visible: projectType() == 'theme'">
			<div class="form-group">
				<label>Framework</label>
				<div class="radio radio-primary" data-bind="foreach: themeFrameworks">
					<input type="radio" name="create-project-theme-framework" data-bind="
						attr: { 
							id: 'create-project-theme-framework-' + value,
							value: value
						}, 
						checked: $root.themeFramework" /> <label data-bind="text: name, attr: { for: 'create-project-theme-framework-' + value }"></label><br>
				</div>
			</div>
		</div>
		<div class="col-sm-6" data-bind="visible: projectType() == 'child-theme'">
			<div class="form-group">
				<label>Parent Theme</label>
				<select class="form-control" data-bind="foreach: parentThemes, value: parentTheme">
					<option type="radio" name="create-project-child-theme-parent" data-bind="
						text: name,
						attr: { 
							id: 'create-project-child-theme-parent-' + value,
							value: value
						}
						">
					</option>
				</select>
			</div>
		</div>
	</div>

	<div class="row project-name">
		<div class="col-sm-6">
			<div class="form-group">
				<label for="name"><span data-bind="text: projectType" style="text-transform: capitalize"></span> Name</label>
				<input type="text" class="form-control" id="name" data-bind="
					textInput: name, 
					callback: function() { 
						name.subscribe( function( newName ) { 
							if ( slug.custom ) { return; }
							var name_slug = newName.toString().toLowerCase()
								.replace(/\s+/g, '-')
								.replace(/[^\w\-]+/g, '')
								.replace(/\-\-+/g, '-')
								.replace(/^-+/, '')
								.replace(/-+$/, '');
							slug( name_slug );
						});
					}"/>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label for="slug">Slug (Project Directory)</label>
				<input type="text" id="slug" class="form-control" data-bind="
					textInput: slug, 
					callback: function() {
						var input = jQuery(this);
						input.on( 'change', function() {
							slug.custom = input.val() ? true : false;
						});
					}"/>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label for="description">Description</label>
		<input type="text" class="form-control" data-bind="textInput: description" id="description" />
	</div>
	<hr />
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label for="author">Author</label>
				<input type="text" class="form-control" data-bind="textInput: author" id="author" />
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label for="authorurl">Author URL</label>
				<input type="text" class="form-control" data-bind="textInput: authorurl" id="authorurl" />
			</div>
		</div>
	</div>
	<div class="form-group">
		<label for="projecturl"><span data-bind="text: projectType" style="text-transform: capitalize"></span> URL</label>
		<input type="text" class="form-control" data-bind="textInput: projecturl" id="projecturl" />
	</div>
</div>
