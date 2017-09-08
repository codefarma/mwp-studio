<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 5, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
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
