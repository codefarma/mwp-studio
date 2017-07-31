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
 *
 * @param	string		$title		The provided title
 * @param	string		$content	The provided content
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<script type="text/template" id="studio-tmpl-class-form">
  <div>
    <div class="form-group">
	  <label for="classname">Class Name</label>
	  <div class="input-group">
	    <div class="input-group-addon" data-bind="text: plugin.namespace() + '\\'"></div>
		<input type="text" class="form-control" data-bind="textInput: classname" id="classname" />
	  </div>
	  </div>
	</div>
  </div>
</script>