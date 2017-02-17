<?php
/**
 * Main PHP file used to for initial calls to zeen101's Leaky Paywall classes and functions.
 *
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */
 
/*
Plugin Name: Leaky Paywall - IP Exceptions
Plugin URI: http://zeen101.com/
Description: Allow visitors from specified IP addresses to view articles and posts without having to subscribe.
Author: zeen101 Development Team
Version: 1.4.0
Author URI: http://zeen101.com/
Tags:
*/

//Define global variables...
if ( !defined( 'ZEEN101_STORE_URL' ) )
	define( 'ZEEN101_STORE_URL',	'http://zeen101.com' );
	
define( 'LP_IPE_NAME', 			'Leaky Paywall - IP Exceptions' );
define( 'LP_IPE_SLUG', 			'leaky-paywall-ip-exceptions' );
define( 'LP_IPE_VERSION', 		'1.4.0' );
define( 'LP_IPE_DB_VERSION', 	'1.0.0' );
define( 'LP_IPE_URL', 			plugin_dir_url( __FILE__ ) );
define( 'LP_IPE_PATH', 			plugin_dir_path( __FILE__ ) );
define( 'LP_IPE_BASENAME', 		plugin_basename( __FILE__ ) );
define( 'LP_IPE_REL_DIR', 		dirname( LP_IPE_BASENAME ) );

/**
 * Instantiate Pigeon Pack class, require helper files
 *
 * @since 1.0.0
 */
function leaky_paywall_ip_exceptions_plugins_loaded() {
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	require_once( 'class.php' );

	if ( is_plugin_active( 'issuem-leaky-paywall/issuem-leaky-paywall.php' ) 
		|| is_plugin_active( 'leaky-paywall/leaky-paywall.php' ) ) {
		// Instantiate the Pigeon Pack class
		if ( class_exists( 'Leaky_Paywall_IP_Exceptions' ) ) {
			
			global $leaky_paywall_ip_exceptions;
			
			$leaky_paywall_ip_exceptions = new Leaky_Paywall_IP_Exceptions();
			
			require_once( 'functions.php' );
				
			//Internationalization
			load_plugin_textdomain( 'issuem-lp-ipe', false, LP_IPE_REL_DIR . '/i18n/' );
				
		}
	} else {
		add_action( 'admin_notices', 'leaky_paywall_ip_exceptions_requirement_nag' );
	}

}
add_action( 'plugins_loaded', 'leaky_paywall_ip_exceptions_plugins_loaded', 4815162342 ); //wait for the plugins to be loaded before init

function leaky_paywall_ip_exceptions_requirement_nag() {
	?>
	<div id="leaky-paywall-requirement-nag" class="update-nag">
		<?php _e( 'You must have the Leaky Paywall plugin activated to use the Leaky Paywall IP Exceptions plugin.' ); ?>
	</div>
	<?php
}
