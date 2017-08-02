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
 * $content = $plugin->getTemplateContent( 'dialogs/template-form' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<script type="text/template" id="studio-tmpl-template-form">
    <div class="form-group">
	  <label for="classname">Template Name</label>
	  <div class="input-group">
		<div class="input-group-addon" data-bind="text: plugin.slug() + '/templates/'"></div>
		<input type="text" placeholder="views/name" class="form-control" data-bind="textInput: filepath, event:{ keypress: enterKeySubmit }" id="classname" />
	    <div class="input-group-addon">.php</div>
	  </div>
	  </div>
	</div>	
</script>