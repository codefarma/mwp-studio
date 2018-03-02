<?php
/**
 * Testing Class
 *
 * To set up testing for your wordpress plugin:
 *
 * @see: http://wp-cli.org/docs/plugin-unit-tests/
 *
 * @package Wordpress Plugin Studio
 */
if ( ! class_exists( 'WP_UnitTestCase' ) )
{
	die( 'Access denied.' );
}

/**
 * Example plugin tests
 */
class MWPStudioPluginTest extends WP_UnitTestCase 
{
	/**
	 * Load Modern Wordpress Framework
	 */
	public function __construct()
	{
		if ( ! file_exists( WP_PLUGIN_DIR . '/mwp-framework/plugin.php' ) )
		{
			die( 'Error: You must first install the MWP Framework plugin to your test suite to run tests on this plugin.' );
		}
		
		require_once WP_PLUGIN_DIR . '/mwp-framework/plugin.php';
	}

	/**
	 * Test that the plugin is a modern wordpress plugin
	 */
	public function test_plugin_class() 
	{
		$plugin = \MWP\Studio\Plugin::instance();
		
		// Check that the plugin is a subclass of MWP\Framework\Plugin 
		$this->assertTrue( $plugin instanceof \MWP\Framework\Plugin );
	}
}
