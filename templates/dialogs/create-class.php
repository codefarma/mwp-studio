<?php
/**
 * Plugin HTML Template
 *
 * Created:  July 30, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/class-form' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="form-group">
  <label for="classname">Class Name</label>
  <div class="input-group">
	<div class="input-group-addon" data-bind="text: plugin.namespace() + '\\'"></div>
	<input type="text" class="form-control" placeholder="Class\Name" data-bind="textInput: classname, event:{ keypress: enterKeySubmit }" id="classname" />
  </div>
</div>
