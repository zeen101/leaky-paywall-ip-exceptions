<?php
/**
 * Main PHP file used to for initial calls to zeen101's Leaky Paywall classes and functions.
 *
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */
 
/*
Plugin Name: zeen101's Leaky Paywall - IP Exceptions
Plugin URI: http://zeen101.com/
Description: A premium add-on for Leaky Paywall for WordPress.
Author: zeen101 Development Team
Version: 1.0.0
Author URI: http://zeen101.com/
Tags:
*/

//Define global variables...
if ( !defined( 'ZEEN101_STORE_URL' ) )
	define( 'ZEEN101_STORE_URL',	'http://zeen101.com' );
	
define( 'LP_IPE_NAME', 			'Leaky Paywall - IP Exceptions' );
define( 'LP_IPE_SLUG', 			'leaky-paywall-ip-exceptions' );
define( 'LP_IPE_VERSION', 		'1.0.0' );
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
function issuem_leaky_paywall_ip_exceptions_plugins_loaded() {
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'issuem/issuem.php' ) )
		define( 'ACTIVE_LP_IPE', true );
	else
		define( 'ACTIVE_LP_IPE', false );

	require_once( 'class.php' );

	// Instantiate the Pigeon Pack class
	if ( class_exists( 'Leaky_Paywall_IP_Exceptions' ) ) {
		
		global $dl_pluginissuem_leaky_paywall_ip_exceptions;
		
		$dl_pluginissuem_leaky_paywall_ip_exceptions = new Leaky_Paywall_IP_Exceptions();
		
		require_once( 'functions.php' );
			
		//Internationalization
		load_plugin_textdomain( 'issuem-lp-ipe', false, LP_IPE_REL_DIR . '/i18n/' );
			
	}

}
add_action( 'plugins_loaded', 'issuem_leaky_paywall_ip_exceptions_plugins_loaded', 4815162342 ); //wait for the plugins to be loaded before init
