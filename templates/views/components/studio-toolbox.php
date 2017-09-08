<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 2, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/studio-toolbox' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<div data-view-model="mwp-studio" class="mwp-studio-toolbox">
	<div class="breadcrumb">
		<h4 style="margin:9px 0; font-weight: normal;">Toolbox</h4>
	</div>
</div>

<?php 
	foreach( apply_filters( 'mwp_studio_toolbox_components', array() ) as $component ) { 
		echo $component; 
	} 
?>
