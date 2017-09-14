<?php
/**
 * Plugin HTML Template
 *
 * Created:  July 30, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/javascript-form' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<div class="form-group">
  <label for="filename">Javascript Controller Name</label>
  <div class="input-group">
	<div class="input-group-addon">/assets/js/</div>
	<input type="text" class="form-control" placeholder="controller-name" data-bind="textInput: filename, event:{ keypress: enterKeySubmit }" id="filename" />
  </div>
</div>
