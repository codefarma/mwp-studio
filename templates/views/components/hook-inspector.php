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
<div class="panel panel-default">
	<div class="panel-heading"><i class="fa fa-search-plus"></i> Hook Inspector</div>
	<div class="panel-body">
		<a href="#" title="Search hooks" class="pull-right" data-bind="
			click: function() {
				bootbox.prompt({ 
					title: 'Hook Search', 
					value: hookSearch(), 
					callback: function( hook_name ) {
						hookSearch( hook_name );
					}
				}); 
			}">
			<i class="fa fa-search"></i>
		</a>
		<h4 style="margin: 0 0 5px 0; font-weight: normal;">Hook Name</h4>
		
		<pre style="color: #888; padding: 6px 9px" data-bind="visible: hookSearch() == ''">none</pre>
		<pre style="color: green; padding: 6px 9px;" data-bind="
			visible: hookSearch() !== '',
			text: hookSearch, 
			studioActivity: { 
				active: hookSearch.loading(), 
				align: 'right', 
				width: 2, 
				length: 4, 
				space: 1, 
				segments: 10 
			}"></pre>
		
		<div data-bind="visible: ( hookSearch() !== '' ) && ( ! hookSearch.loading() ) && ( ! hookSearch.results().results.length )">
			<blockquote class="text-danger" style="font-size: 1em">No references found.</blockquote>
		</div>
		
		<div data-bind="if: ! hookSearch.loading()">
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
								<div data-bind="foreach: hookSearch.groupedResults().do_action.groups">
									<h4 data-bind="text: location + ( slug ? ' / ' + slug : '')"></h4>
									<ul data-bind="foreach: hooks">
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
								<div data-bind="foreach: hookSearch.groupedResults().add_action.groups">
									<h4 data-bind="text: location + ( slug ? ' / ' + slug : '')"></h4>
									<ul data-bind="foreach: hooks">
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
													jquery: {
														popover: {
															content: callback_signature,
															html: true,
															placement: 'top',
															trigger: 'hover'
														}
													},
													click: function() { 
														mwp.model.get('mwp-studio-filetree-node').loadCallbackFile( hook_callback_name, hook_callback_class ).done( function( file, callback ) {
															file.switchTo().done( function() {
																file.editor.gotoLine( callback.function_line );
																setTimeout( function() { file.editor.gotoLine( callback.function_line ); }, 500 );
															});
														}).fail( function() {
															bootbox.alert({title: 'File Not Found', message: 'The callback function could not be explicitly located.'});
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
								<div data-bind="foreach: hookSearch.groupedResults().apply_filters.groups">
									<h4 data-bind="text: location + ( slug ? ' / ' + slug : '')"></h4>
									<ul data-bind="foreach: hooks">
										<li class="overflow-ellipsis">
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
											">apply_filters</a> in <span class="text-info" data-bind="text: hook_file.split('/').pop(), attr: { title: hook_file + ' : line ' + hook_line }"></span>
										</li>
									</ul>
								</div>
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
								<div data-bind="foreach: hookSearch.groupedResults().add_filter.groups">
									<h4 data-bind="text: location + ( slug ? ' / ' + slug : '')"></h4>
									<ul data-bind="foreach: hooks">
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
													jquery: {
														popover: {
															content: callback_signature,
															html: true,
															placement: 'top',
															trigger: 'hover'
														}
													},
													click: function() { 
														mwp.model.get('mwp-studio-filetree-node').loadCallbackFile( hook_callback_name, hook_callback_class ).done( function( file, callback ) {
															file.switchTo().done( function() {
																file.editor.gotoLine( callback.function_line );
																setTimeout( function() { file.editor.gotoLine( callback.function_line ); }, 500 );
															});
														}).fail( function() {
															bootbox.alert({title: 'File Not Found', message: 'The callback function could not be explicitly located.'});
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
	</div>
</div>