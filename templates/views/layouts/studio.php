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

<div id="mwp-studio-container" data-view-model="mwp-studio" class="mwp-studio mwp-bootstrap" 
	data-bind="layout: { 
		applyDefaultStyles: true, 
		north: { 
			resizable: false, 
			closable: false, 
			spacing_open: 0 
		},
		south: { 
			size: localStorage.getItem( 'mwp-studio-south-size' ) || 250, 
			onresize: function( key, pane ) {
				localStorage.setItem( 'mwp-studio-south-size', pane.outerHeight() );
			} 
		}
	}">
	<div class="ui-layout-north">
		<?php echo $this->getTemplateContent( 'views/components/navbar' ) ?>
	</div>
	<div class="ui-layout-center" data-bind="event: { 'resize': function() { if ( activeFile() ) { setTimeout( function(){ activeFile().model().editor.resize(); }, 200 ); } } }">
		<div class="column col-md-3" style="height: 100%">
			<?php echo $this->getTemplateContent( 'views/components/resource-browser' ) ?>
		</div>
		
		<div class="column col-md-6" style="height: 100%">
			<?php echo $this->getTemplateContent( 'views/components/files-editor' ) ?>
		</div>
		
		<div class="column col-md-3" style="height: 100%">
			<?php echo $this->getTemplateContent( 'views/components/studio-toolbox' ) ?>
		</div>			
	</div>
	<div class="ui-layout-south">
		<?php echo $this->getTemplateContent( 'views/components/south-pane' ) ?>
		<?php echo $this->getTemplateContent( 'views/components/south-pane/statusbar' ) ?>
	</div>
</div>

<?php 
	/* Templates */
	echo $this->getTemplateContent( 'dialogs/class-form' );
	echo $this->getTemplateContent( 'dialogs/template-form' );
	echo $this->getTemplateContent( 'dialogs/stylesheet-form' );
	echo $this->getTemplateContent( 'dialogs/javascript-form' );
	echo $this->getTemplateContent( 'dialogs/create-plugin-form' );
?>