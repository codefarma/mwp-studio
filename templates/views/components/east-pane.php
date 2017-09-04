<?php
/**
 * Plugin HTML Template
 *
 * Created:  August 26, 2017
 *
 * @package  Wordpress Plugin Studio
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/components/east-pane' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<?php echo $this->getTemplateContent( 'views/components/studio-toolbox' ) ?>
<?php echo $this->getTemplateContent( 'views/components/hook-inspector' ) ?>
