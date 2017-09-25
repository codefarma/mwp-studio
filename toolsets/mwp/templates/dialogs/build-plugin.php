<?php
/**
 * Plugin HTML Template
 *
 * Created:  July 30, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/build-plugin' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<div id="build-project-dialog">
	<div class="row">
		<div class="col-xs-6">
			<h3><?php _e( 'Build Type', 'mwp-studio' ) ?></h3>
			<hr>
			<div class="radio radio-primary">
				<span data-bind="if: plugin.version">
					<input type="radio" id="build-type-rebuild" name="build-type" value="rebuild" data-bind="checked: buildType" /> 
					<label for="build-type-rebuild"><?php _e( 'Rebuild', 'mwp-studio' ) ?> 
						<pre style="display: inline-block; padding: 3px; display: inline; margin-left: 15px;" data-bind="text: plugin.version"></pre>
					</label><br>
				</span>
				
				<input type="radio" id="build-type-patch"  name="build-type" value="patch"  data-bind="checked: buildType" /> 
				<label for="build-type-patch"><?php _e( 'Patch Release', 'mwp-studio' ) ?>
						<pre style="display: inline-block; padding: 3px; display: inline; margin-left: 15px;" data-bind="text: versions.patch"></pre>			
				</label><br>
				
				<input type="radio" id="build-type-point"  name="build-type" value="point"  data-bind="checked: buildType" /> 
				<label for="build-type-point"><?php _e( 'Point Release', 'mwp-studio' ) ?>
						<pre style="display: inline-block; padding: 3px; display: inline; margin-left: 15px;" data-bind="text: versions.point"></pre>			
				</label><br>
				
				<input type="radio" id="build-type-minor"  name="build-type" value="minor"  data-bind="checked: buildType" /> 
				<label for="build-type-minor"><?php _e( 'Minor Release', 'mwp-studio' ) ?>
						<pre style="display: inline-block; padding: 3px; display: inline; margin-left: 15px;" data-bind="text: versions.minor"></pre>			
				</label><br>
				
				<input type="radio" id="build-type-major"  name="build-type" value="major"  data-bind="checked: buildType" /> 
				<label for="build-type-major"><?php _e( 'Major Release', 'mwp-studio' ) ?>
						<pre style="display: inline-block; padding: 3px; display: inline; margin-left: 15px;" data-bind="text: versions.major"></pre>			
				</label><br>
				
				<input type="radio" id="build-type-custom" name="build-type" value="custom" data-bind="checked: buildType" /> 
				<label for="build-type-custom"><?php _e( 'Custom Release', 'mwp-studio' ) ?>
				</label><br>
				<input type="text" class="form-control" data-bind="textInput: versions.custom, visible: buildType() == 'custom'" />
			</div>		
		</div>
		<div class="col-xs-6 text-center">
			<div style="margin-bottom: 45px">
				<h3>Current Version</h3>
				<hr />
				<code style="font-size:1.5em; padding:5px 15px; line-height:1.5em;" class="text-info" data-bind="text: plugin.version ? plugin.version() : 'None'"></code>
			</div>
			<div>
				<h3>New Version</h3>
				<hr />
				<code style="font-size:2em; padding:5px 15px; line-height:2em;" class="text-success" data-bind="text: versions[buildType()]"></code>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<h3><?php _e( 'Build Options', 'mwp-studio' ) ?></h3>
			<hr>
			<div class="checkbox checkbox-primary">
				<input type="checkbox" id="build-option-framework" value="1" data-bind="checked: buildFramework" /> <label for="build-option-framework"><?php _e( 'Bundle MWP Framework into the package', 'mwp-studio' ) ?></label>
			</div>
		</div>
	</div>
</div>