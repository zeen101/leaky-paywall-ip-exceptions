<?php

/**
 * Main PHP file used to for initial calls to Leaky Paywall - IP Exceptions classes and functions.
 *
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */

/*
Plugin Name: Leaky Paywall - IP Exceptions
Plugin URI: https://leakypaywall.com/
Description: Allow visitors from specified IP addresses to view articles and posts without having to subscribe.
Author: Leaky Paywall
Version: 1.5.0
Author URI: https://leakypaywall.com/
Tags:
*/

//Define global variables...
if (!defined('ZEEN101_STORE_URL'))
	define('ZEEN101_STORE_URL',	'https://zeen101.com');

define('LP_IPE_NAME', 			'Leaky Paywall - IP Exceptions');
define('LP_IPE_SLUG', 			'leaky-paywall-ip-exceptions');
define('LP_IPE_VERSION', 		'1.5.0');
define('LP_IPE_DB_VERSION', 	'1.0.0');
define('LP_IPE_URL', 			plugin_dir_url(__FILE__));
define('LP_IPE_PATH', 			plugin_dir_path(__FILE__));
define('LP_IPE_BASENAME', 		plugin_basename(__FILE__));
define('LP_IPE_REL_DIR', 		dirname(LP_IPE_BASENAME));

/**
 * Instantiate Pigeon Pack class, require helper files
 *
 * @since 1.0.0
 */
function leaky_paywall_ip_exceptions_plugins_loaded()
{

	include_once(ABSPATH . 'wp-admin/includes/plugin.php');

	require_once('class.php');

	if (
		is_plugin_active('issuem-leaky-paywall/issuem-leaky-paywall.php')
		|| is_plugin_active('leaky-paywall/leaky-paywall.php')
	) {

		if (class_exists('Leaky_Paywall_IP_Exceptions')) {

			global $leaky_paywall_ip_exceptions;

			$leaky_paywall_ip_exceptions = new Leaky_Paywall_IP_Exceptions();

			require_once('functions.php');
			require_once('include/admin/settings.php');
			require_once('include/updates.php');

			//Internationalization
			load_plugin_textdomain('issuem-lp-ipe', false, LP_IPE_REL_DIR . '/i18n/');
		}

		// Upgrade function based on EDD updater class
		if (!class_exists('EDD_LP_Plugin_Updater')) {
			include(dirname(__FILE__) . '/include/EDD_LP_Plugin_Updater.php');
		}

		$license = new Leaky_Paywall_License_Key(LP_IPE_SLUG, LP_IPE_NAME);

		$settings = $license->get_settings();
		$license_key = trim($settings['license_key']);
		$edd_updater = new EDD_LP_Plugin_Updater(ZEEN101_STORE_URL, __FILE__, array(
			'version' 	=> LP_IPE_VERSION, // current version number
			'license' 	=> $license_key,
			'item_name' => LP_IPE_NAME,
			'author' 	=> 'Zeen101 Development Team'
		));
	} else {
		add_action('admin_notices', 'leaky_paywall_ip_exceptions_requirement_nag');
	}
}
add_action('plugins_loaded', 'leaky_paywall_ip_exceptions_plugins_loaded', 4815162344); //wait for the plugins to be loaded before init

function leaky_paywall_ip_exceptions_requirement_nag()
{
?>
	<div id="leaky-paywall-requirement-nag" class="update-nag">
		<?php _e('You must have the Leaky Paywall plugin activated to use the Leaky Paywall IP Exceptions plugin.'); ?>
	</div>
<?php
}
