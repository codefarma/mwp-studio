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
 * @Wordpress\Options\Field( title="Authorized Users", name="authorized_users", type="text", default="1" )
 * @Wordpress\Options\Field( title="Editable File Types", name="editable_file_types", type="text", default="php,css,less,js,html,xml,txt,md,pot,mo,gitignore,buildignore,json,lock" )
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