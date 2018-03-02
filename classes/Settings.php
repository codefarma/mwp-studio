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
 * @MWP\WordPress\Options
 * @MWP\WordPress\Options\Section( title="General Settings" )
 * @MWP\WordPress\Options\Field( title="Automatic Index Update Interval (seconds)", name="auto_index_interval", type="text", default=300 )
 * @MWP\WordPress\Options\Field( title="Studio Heartbeat Interval (seconds)", name="heartbeat_interval", type="text", default=20 )
 * @MWP\WordPress\Options\Field( title="Authorized Users", name="authorized_users", type="text", default="1" )
 * @MWP\WordPress\Options\Field( title="Editable File Types", name="editable_file_types", type="text", default="php,css,less,js,html,xml,txt,md,pot,gitignore,buildignore,json,lock" )
 */
class Settings extends \MWP\Framework\Plugin\Settings
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