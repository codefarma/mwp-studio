<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 22, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/web-browser' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="web-browser-container" data-bind="style: { height: bodyHeight() + 'px' }">
	<div class="web-browser-bar">
		<div class="input-group">
			<div class="input-group-addon">
				<i class="fa fa-globe"></i>
			</div>
			<input type="text" class="form-control" data-bind="value: url" />
			<div class="input-group-addon">
				<a href="#" class="btn btn-link btn-xs" data-bind="
					click: function( viewModel, event ) {
						var iframe = jQuery(event.target).closest('.web-browser-container').find('.web-browser iframe').eq(0)[0];
						iframe.src = iframe.src;
					}">
					<i class="fa fa-refresh"></i>
				</a>
			</div>
		</div>
	</div>
	<div class="web-browser">
		<iframe data-bind="
			init: function() {
				var iframe = this;
				url.subscribe( function( href ) {
					if ( iframe.contentWindow.location.href != href ) {
						iframe.src = href;
					}
				});
				iframe.src = url();
			},
			event: { 
				load: function( viewModel, event ) { 
					viewModel.url( event.target.contentWindow.location.href ); 
				} 
			}">
		</iframe>
	</div>
</div>