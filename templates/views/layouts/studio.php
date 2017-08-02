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
 * $content = $plugin->getTemplateContent( 'views/layouts/studio' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<div data-view-model="mwp-studio" class="mwp-studio mwp-bootstrap wrap">
	<?php echo $this->getTemplateContent( 'views/components/navbar' ) ?>

	<div class="row">
		<div class="col-md-3">
			<?php echo $this->getTemplateContent( 'views/components/resource-browser' ) ?>
		</div>
		
		<div class="col-md-6">
			<?php echo $this->getTemplateContent( 'views/components/files-editor' ) ?>
		</div>
		
		<div class="col-md-3">
			<?php echo $this->getTemplateContent( 'views/components/studio-toolbox' ) ?>
		</div>
		
	</div>
</div>

<?php 
	echo $this->getTemplateContent( 'dialogs/class-form' );
	echo $this->getTemplateContent( 'dialogs/template-form' );
	echo $this->getTemplateContent( 'dialogs/stylesheet-form' );
	echo $this->getTemplateContent( 'dialogs/javascript-form' );
?>