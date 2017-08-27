<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 26, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/center-pane' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="column col-md-3" style="height: 100%">
	<?php echo $this->getTemplateContent( 'views/components/resource/browser' ) ?>
</div>

<div class="column col-md-6" style="height: 100%">
	<?php echo $this->getTemplateContent( 'views/components/files-editor' ) ?>
</div>

<div class="column col-md-3" style="height: 100%">
	<?php echo $this->getTemplateContent( 'views/components/studio-toolbox' ) ?>
</div>			
