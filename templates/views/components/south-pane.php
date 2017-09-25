<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 18, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/south-pane' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="column" style="height: 100%">

	<ul class="nav nav-tabs" role="tablist" data-bind="
		foreach: { 
			data: env().studioPaneTabs, 
			afterRender: function(elements, tab) { 
				jQuery('.ui-layout-south').trigger('resize');
				if( jQuery( elements[1] ).is( 'li.active' ) ) {
					tab.initialized = true;
					tab.refreshContent();
				}				
			}
		}">
		<li role="presentation" data-bind="css: { active: $index() == 0 }">
			<a role="tab" data-toggle="tab" data-bind="
				attr: { href: '#' + id },
				event: {
					'shown.bs.tab': function(tab, e) {
						if ( ! tab.initialized ) {
							tab.initialized = true;
							tab.refreshContent();
						}
						jQuery(e.target)
							.closest('.ui-layout-pane')
							.trigger('resize');
					}
				}
				"><span data-bind="if: typeof icon !== 'undefined'"><i data-bind="attr: { class: icon }"></i> </span><span data-bind="text: title"></span>
			</a>
		</li>
	</ul>

	<div class="panel panel-default tabbed-panel full-height" data-bind="fillPaneContainer: { pane: '.ui-layout-south', container: '.column' }" style="margin-bottom: 0;">
		<div class="tab-content full-height" data-bind="foreach: env().studioPaneTabs">
			<div role="tabpanel" class="tab-pane full-height" style="overflow-y: auto" data-bind="
				attr: { id: id }, 
				css: { active: $index() == 0 }, 
				template: { 
					nodes: template, 
					data: viewModel 
				}"></div>
		</div>
	</div>
</div>
