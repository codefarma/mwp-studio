<?php
/**
 * Plugin Name: Wordpress Plugin Studio
 * Depends: lib-modern-framework
 * Description: A graphical design studio for building wordpress plugins
 * Version: 0.0.0
 * Author: Kevin Carwile
 * Author URI: 
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Load Only Once */
if ( ! class_exists( 'MWPStudioPlugin' ) )
{
	class MWPStudioPlugin
	{
		public static function init()
		{
			/* Plugin Core */
			$plugin	= \MWP\Studio\Plugin::instance();
			$plugin->setPath( rtrim( plugin_dir_path( __FILE__ ), '/' ) );

			/* Plugin Settings */
			$settings = \MWP\Studio\Settings::instance();
			$plugin->addSettings( $settings );
			
			$dashboard = \MWP\Studio\Controllers\Dashboard::instance();
			$ajaxHandlers = \MWP\Studio\AjaxHandlers::instance();
			
			/* Connect annotated resources to wordpress core */
			$framework = \Modern\Wordpress\Framework::instance()
				->attach( $plugin )
				->attach( $dashboard )
				->attach( $ajaxHandlers )
				//->attach( $settings )
				;
			
			/* Enable Widgets */
			\MWP\Studio\BasicWidget::enableOn( $plugin );
		}
		
		public static function status() {
			if ( ! class_exists( 'ModernWordpressFramework' ) ) {
				echo '<td colspan="3" class="plugin-update colspanchange">
						<div class="update-message notice inline notice-error notice-alt">
							<p><strong style="color:red">INOPERABLE.</strong> Please activate <a href="' . admin_url( 'plugins.php?page=tgmpa-install-plugins' ) . '"><strong>Modern Framework for Wordpress</strong></a> to enable the operation of this plugin.</p>
						</div>
					  </td>';
			}
		}
	}
	
	/* Autoload Classes */
	require_once 'vendor/autoload.php';
	
	/* Bundled Framework */
	if ( file_exists( __DIR__ . '/framework/plugin.php' ) ) {
		include_once 'framework/plugin.php';
	}

	/* Register plugin dependencies */
	include_once 'includes/plugin-dependency-config.php';
	
	/* Register plugin status notice */
	add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), array( 'MWPStudioPlugin', 'status' ) );
	
	/**
	 * DO NOT REMOVE
	 *
	 * This plugin depends on the modern wordpress framework.
	 * This block ensures that it is loaded before we init.
	 */
	if ( class_exists( 'ModernWordpressFramework' ) ) {
		MWPStudioPlugin::init();
	}
	else {
		add_action( 'modern_wordpress_init', array( 'MWPStudioPlugin', 'init' ) );
	}
	
}

