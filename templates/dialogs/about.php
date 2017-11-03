<?php
/**
 * Plugin HTML Template
 *
 * Created:  September 17, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/about' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<h1>Modern Wordpress Studio</h1>
<p>&copy; 2017</p>

<table class="table" style="margin-top:35px;">
	<tr><td>WP Version</td><td><?php echo get_bloginfo('version'); ?></td></tr>	
	<tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
</table>