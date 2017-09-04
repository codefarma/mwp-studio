<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 4, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/hook-inspector' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<div class="panel panel-default" data-bind="visible: hookSearch()">
	<div class="panel-heading"><i class="fa fa-search-plus"></i> Hook Inspector</div>
	<div class="panel-body">
		<h4 style="margin: 0 0 5px 0; font-weight: normal;">Hook Name</h4>
		<pre style="color: green;" data-bind="text: hookSearch"></pre>
		
		<!-- Actions -->
		<div data-bind="if: _.filter(hookSearch.results().results,function(result){return result.hook_type.indexOf('action')>0;}).length">
			<h4><i class="fa fa-bolt"></i> Type: <span class="label label-info">Code Action</span></h4>
			<div class="panel-group hook-inspector" role="tablist" aria-multiselectable="true">
				<div class="panel panel-default">
					<div class="panel-heading" role="tab">
						<div class="panel-title">
							<a href="#hook-triggers" 
								role="button" 
								data-toggle="collapse" 
								data-parent=".hook-inspector" 
								aria-expanded="false" 
								aria-controls="hook-triggers">Triggers 
								<span class="badge pull-right" data-bind="text: _.filter( hookSearch.results().results, function( result ) {
									return result.hook_type == 'do_action';
								}).length"></span>
							</a>
						</div>
					</div>
					<div class="panel-collapse collapse" role="tabpanel" id="hook-triggers">
						<div class="panel-body">
							<ul data-bind="
								foreach: _.filter( hookSearch.results().results, function( result ) {
									return result.hook_type == 'do_action';
								})
								">
								<li class="overflow-ellipsis" style="color: #999; line-height: 1.75em;">
									<a href="#" 
										class="label label-success"
										data-bind="
										attr: {
											title: 'do_action( \'' + hook_name + '\' )'
										},
										click: function() {
											mwp.model.get('mwp-studio-filetree-node').loadFile( hook_file ).done( function( file ) {
												file.switchTo().done( function( editor ) {
													editor.gotoLine( hook_line );
													setTimeout( function() { editor.gotoLine( hook_line ); }, 500 );
												});
											});
										}
									">do_action</a> in <span class="text-info" data-bind="text: hook_file.split('/').pop(), attr: { title: hook_file + ' : line ' + hook_line }"></span>
								</li>
							</ul>					
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab">
						<div class="panel-title">
							<a href="#hook-callbacks" 
								role="button" 
								data-toggle="collapse" 
								data-parent=".hook-inspector" 
								aria-expanded="false" 
								aria-controls="hook-callbacks">Callbacks 
								<span class="badge pull-right" data-bind="text: _.filter( hookSearch.results().results, function( result ) {
									return result.hook_type == 'add_action';
								}).length"></span>
							</a>
						</div>
					</div>
					<div class="panel-collapse collapse" role="tabpanel" id="hook-callbacks">
						<div class="panel-body">
							<ul data-bind="
								foreach: _.filter( hookSearch.results().results, function( result ) {
									return result.hook_type == 'add_action';
								})
								">
								<li class="overflow-ellipsis" style="color: #aaa; line-height: 1.75em;">
									<a href="#" 
										class="label label-success"
										data-bind="
										attr: {
											title: 'add_action( \'' + hook_name + '\' )'
										},
										click: function() {
											mwp.model.get('mwp-studio-filetree-node').loadFile( hook_file ).done( function( file ) {
												file.switchTo().done( function( editor ) {
													editor.gotoLine( hook_line );
													setTimeout( function() { editor.gotoLine( hook_line ); }, 500 );
												});
											});
										}
									">add_action</a>
									<span data-bind="if: hook_callback_type !== 'closure'">
										<a href="#" data-bind="
											text: hook_callback_name,
											attr: {
												title: ( hook_callback_class ? 
													( hook_callback_type == 'method' ? hook_callback_class + '::' + hook_callback_name + '()' 
														: hook_callback_class + '\\' + hook_callback_name + '()' 
													) 
													: 'function ' + hook_callback_name + '()' )
											},
											click: function() { 
												mwp.model.get('mwp-studio-filetree-node').loadCallbackFile( hook_callback_name, hook_callback_class ).done( function( file, callback ) {
													file.switchTo().done( function() {
														file.editor.gotoLine( callback.function_line );
														setTimeout( function() { file.editor.gotoLine( callback.function_line ); }, 500 );
													});
												});
											}
										"></a>
									</span>
									<span data-bind="if: hook_callback_type == 'closure'">{closure}</span>								
								</li>
							</ul>					
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Filters -->
		<div data-bind="if: _.filter(hookSearch.results().results,function(result){return result.hook_type.indexOf('filter') > 0;}).length">
			<h4><i class="fa fa-filter"></i> Type: <span class="label label-warning">Data Filter</span></h4>
			<div class="panel-group hook-inspector" role="tablist" aria-multiselectable="true">
				<div class="panel panel-default">
					<div class="panel-heading" role="tab">
						<div class="panel-title">
							<a href="#hook-triggers" 
								role="button" 
								data-toggle="collapse" 
								data-parent=".hook-inspector" 
								aria-expanded="false" 
								aria-controls="hook-triggers">Triggers 
								<span class="badge pull-right" data-bind="text: _.filter( hookSearch.results().results, function( result ) {
									return result.hook_type == 'apply_filters';
								}).length"></span>
							</a>
						</div>
					</div>
					<div class="panel-collapse collapse" role="tabpanel" id="hook-triggers">
						<div class="panel-body">
							<ul data-bind="
								foreach: _.filter( hookSearch.results().results, function( result ) {
									return result.hook_type == 'apply_filters';
								})
								">
								<li>
									<a href="#" 
										class="label label-success"
										data-bind="
										attr: {
											title: 'apply_filters( \'' + hook_name + '\' )'
										},
										click: function() {
											mwp.model.get('mwp-studio-filetree-node').loadFile( hook_file ).done( function( file ) {
												file.switchTo().done( function( editor ) {
													editor.gotoLine( hook_line );
													setTimeout( function() { editor.gotoLine( hook_line ); }, 500 );
												});
											});
										}
									">apply_filters</a> in <span data-bind="text: hook_file.split('/').pop(), attr: { title: hook_file + ' : line ' + hook_line }"></span>

								</li>
							</ul>					
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab">
						<div class="panel-title">
							<a href="#hook-callbacks" 
								role="button" 
								data-toggle="collapse" 
								data-parent=".hook-inspector" 
								aria-expanded="false" 
								aria-controls="hook-callbacks">Callbacks 
								<span class="badge pull-right" data-bind="text: _.filter( hookSearch.results().results, function( result ) {
									return result.hook_type == 'add_filter';
								}).length"></span>
							</a>
						</div>
					</div>
					<div class="panel-collapse collapse" role="tabpanel" id="hook-callbacks">
						<div class="panel-body">
							<ul data-bind="
								foreach: _.filter( hookSearch.results().results, function( result ) {
									return result.hook_type == 'add_filter';
								})
								">
								<li class="overflow-ellipsis" style="color: #aaa; line-height: 1.75em;">
									<a href="#" 
										class="label label-success"
										data-bind="
										attr: {
											title: 'add_filter( \'' + hook_name + '\' )'
										},
										click: function() {
											mwp.model.get('mwp-studio-filetree-node').loadFile( hook_file ).done( function( file ) {
												file.switchTo().done( function( editor ) {
													editor.gotoLine( hook_line );
													setTimeout( function() { editor.gotoLine( hook_line ); }, 500 );
												});
											});
										}
									">add_filter</a> 
									<span data-bind="if: hook_callback_type !== 'closure'">
										<a href="#" data-bind="
											text: hook_callback_name,
											attr: {
												title: ( hook_callback_class ? 
													( hook_callback_type == 'method' ? hook_callback_class + '::' + hook_callback_name + '()' 
														: hook_callback_class + '\\' + hook_callback_name + '()' 
													) 
													: 'function ' + hook_callback_name + '()' )
											},
											click: function() { 
												mwp.model.get('mwp-studio-filetree-node').loadCallbackFile( hook_callback_name, hook_callback_class ).done( function( file, callback ) {
													file.switchTo().done( function() {
														file.editor.gotoLine( callback.function_line );
														setTimeout( function() { file.editor.gotoLine( callback.function_line ); }, 500 );
													});
												});
											}
										"></a>
									</span>
									<span data-bind="if: hook_callback_type == 'closure'">{closure}</span>								
								</li>
							</ul>					
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>