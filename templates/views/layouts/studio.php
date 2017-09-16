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
 * $content = $plugin->getTemplateContent( 'views/layouts/studio' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div id="mwp-studio-container" data-view-model="mwp-studio" class="mwp-studio mwp-bootstrap" style="display:none" data-bind="
	visible: true,
	layout: {
		applyDefaultStyles: true, 
		north: { 
			resizable: false, 
			closable: false, 
			spacing_open: 0 
		},
		east: {
			size: localStorage.getItem( 'mwp-studio-east-size' ) || '20%',
			onresize: function( key, pane ) {
				localStorage.setItem( 'mwp-studio-east-size', pane.outerWidth() );
			}
		},
		west: {
			size: localStorage.getItem( 'mwp-studio-west-size' ) || '20%',
			onresize: function( key, pane ) {
				localStorage.setItem( 'mwp-studio-west-size', pane.outerWidth() );
			}
		}
	},
	callback: function( bindings ) {
		studioLayout( jQuery(this).layout() );
	}">
	<div class="ui-layout-north">
		<?php echo $this->getTemplateContent( 'views/components/navbar' ) ?>
	</div>
	<div class="ui-layout-east outer-east">
		<?php echo $this->getTemplateContent( 'views/components/east-pane' ) ?>
	</div>
	<div class="ui-layout-center padding-0"
		data-bind="event: { 
			resize: function() { 
				if ( activeFile() ) { setTimeout( function(){ activeFile().model().editor.resize(); }, 200 ); } 
			} 
		},
		layout: {
			applyDefaultStyles: true,
			south: { 
				size: localStorage.getItem( 'mwp-studio-south-size' ) || 250, 
				onresize: function( key, pane ) {
					localStorage.setItem( 'mwp-studio-south-size', pane.outerHeight() );
				}
			}
		}">
		<div class="ui-layout-center inner-center">
			<?php echo $this->getTemplateContent( 'views/components/center-pane' ) ?>
		</div>
		<div class="ui-layout-south inner-south overflow-hidden">
			<?php echo $this->getTemplateContent( 'views/components/south-pane' ) ?>
			<?php echo $this->getTemplateContent( 'views/components/statusbar' ) ?>
		</div>
	</div>
	<div class="ui-layout-west outer-west overflow-hidden">
		<?php echo $this->getTemplateContent( 'views/components/west-pane' ) ?>
	</div>
	<div class="window-labels" data-role="window-labels"></div>
</div>