<?php
/**
 * Main PHP file used to for initial calls to IssueM's Leak Paywall classes and functions.
 *
 * @package IssueM's Leak Paywall - IP Exceptions
 * @since 1.0.0
 */
 
/*
Plugin Name: IssueM's Leaky Paywall - IP Exceptions
Plugin URI: http://zeen101.com/
Description: A premium leaky paywall add-on for WordPress and IssueM.
Author: IssueM Development Team
Version: 1.0.0
Author URI: http://zeen101.com/
Tags:
*/

//Define global variables...
if ( !defined( 'ZEEN101_STORE_URL' ) )
	define( 'ZEEN101_STORE_URL',	'http://zeen101.com' );
	
define( 'ISSUEM_LP_IPE_NAME', 		'Leaky Paywall - IP Exceptions' );
define( 'ISSUEM_LP_IPE_SLUG', 		'issuem-leaky-paywall-ip-exceptions' );
define( 'ISSUEM_LP_IPE_VERSION', 	'1.0.0' );
define( 'ISSUEM_LP_IPE_DB_VERSION', 	'1.0.0' );
define( 'ISSUEM_LP_IPE_URL', 		plugin_dir_url( __FILE__ ) );
define( 'ISSUEM_LP_IPE_PATH', 		plugin_dir_path( __FILE__ ) );
define( 'ISSUEM_LP_IPE_BASENAME', 	plugin_basename( __FILE__ ) );
define( 'ISSUEM_LP_IPE_REL_DIR', 	dirname( ISSUEM_LP_IPE_BASENAME ) );

/**
 * Instantiate Pigeon Pack class, require helper files
 *
 * @since 1.0.0
 */
function issuem_leaky_paywall_ip_exceptions_plugins_loaded() {
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'issuem/issuem.php' ) )
		define( 'ISSUEM_ACTIVE_LP_IPE', true );
	else
		define( 'ISSUEM_ACTIVE_LP_IPE', false );

	require_once( 'class.php' );

	// Instantiate the Pigeon Pack class
	if ( class_exists( 'IssueM_Leaky_Paywall_IP_Exceptions' ) ) {
		
		global $dl_pluginissuem_leaky_paywall_ip_exceptions;
		
		$dl_pluginissuem_leaky_paywall_ip_exceptions = new IssueM_Leaky_Paywall_IP_Exceptions();
		
		require_once( 'functions.php' );
			
		//Internationalization
		load_plugin_textdomain( 'issuem-lp-ipe', false, ISSUEM_LP_IPE_REL_DIR . '/i18n/' );
			
	}

}
add_action( 'plugins_loaded', 'issuem_leaky_paywall_ip_exceptions_plugins_loaded', 4815162342 ); //wait for the plugins to be loaded before init
