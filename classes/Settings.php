<?php
/**
 * Settings Class File
 *
 * @vendor: Kevin Carwile
 * @package: MWP Studio
 * @author: Kevin Carwile
 * @link: 
 * @since: May 1, 2017
 */
namespace MWP\Studio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Plugin Settings
 *
 * @Wordpress\Options
 * @Wordpress\Options\Section( title="General Settings" )
 * @Wordpress\Options\Field( title="Automatic Index Update Interval (seconds)", name="auto_index_interval", type="text", default=300 )
 * @Wordpress\Options\Field( title="Studio Heartbeat Interval (seconds)", name="heartbeat_interval", type="text", default=20 )
 */
class Settings extends \Modern\Wordpress\Plugin\Settings
{
	/**
	 * Instance Cache - Required for singleton
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var string	Settings Access Key ( default: main )
	 */
	public $key = 'main';
	
}