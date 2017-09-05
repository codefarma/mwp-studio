<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 5, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/hook/callback-signature', array( 'hook' => $hook ) ); 
 * ```
 * 
 * @param	Hook		$hook		The hook the popover is for
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<?php if ( $hook->callback_type == 'method' ) : ?>
<pre class="cb-signature">class <?php echo $hook->callback_class ?: '?' ?> {
  func <?php echo $hook->callback_name ?>();
}</pre>
<?php else: ?>
  <?php if ( $hook->callback_class ) : ?>
  <pre class="cb-signature">namespace <?php echo $hook->callback_class ?>;
func <?php echo $hook->callback_name ?>();</pre>
  <?php else: ?>
  <pre class="cb-signature">func <?php echo $hook->callback_name ?>();</pre>
  <?php endif; ?>
<?php endif; ?>
</div>
