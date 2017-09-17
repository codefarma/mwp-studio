<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 17, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/editor-settings' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * View Model 
 * 
 * @param	string			tabsType
 * @param	int				tabsSize
 */
?>

<div class="form-group">
	<label>Tabs Type</label>
	<div class="radio radio-primary">
		<input type="radio" name="editor-tabs-type" value="tab" id="editor-tabs-type-tab" data-bind="checked: tabsType"/> <label for="editor-tabs-type-tab">Tabs</label><br>
		<input type="radio" name="editor-tabs-type" value="space" id="editor-tabs-type-space" data-bind="checked: tabsType" /> <label for="editor-tabs-type-space">Spaces</label>
	</div>
</div>

<div class="form-group">
	<label>Number of spaces
	<input type="number" min="1" max="10" data-bind="textInput: tabsSize" class="form-control" />
	</label>
</div>

<div class="form-group">
	<div class="checkbox checkbox-primary">
		<input type="checkbox" id="editor-line-wrap" data-bind="checked: lineWrap" /> <label for="editor-line-wrap">Wrap lines</label>
	</div>
</div>