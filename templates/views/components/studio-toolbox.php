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
 * $content = $plugin->getTemplateContent( 'views/components/studio-toolbox' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<div class="mwp-studio-toolbox">
	<div class="breadcrumb">
		<h4 style="margin:9px 0; font-weight: normal;">Toolbox</h4>
	</div>
	
	<div class="panel-group">
		<?php foreach( apply_filters( 'mwp_studio_toolbox_components', array() ) as $key => $component ): ?>
		<div class="panel panel-default <?php echo $component['panelClass'] ?>">
			<div class="panel-heading <?php echo $component['panelHeadingClass'] ?>" data-target="#<?php echo $key ?>-toolbox-panel" data-toggle="collapse">
				<i class="<?php echo $component['panelIcon'] ?>"></i> <?php echo $component['panelTitle'] ?>
			</div>
			<div id="<?php echo $key ?>-toolbox-panel" class="panel-collapse collapse <?php echo $component['panelCollapseClass'] ?>">
				<div class="panel-body <?php echo $component['panelBodyClass'] ?>">
					<?php echo $component['panelContent'] ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>




