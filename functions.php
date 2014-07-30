<?php
/**
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */
 
if ( !function_exists( 'leaky_paywall_get_ip_address' ) ) {
		
	function leaky_paywall_get_ip_address() {
	
		$methods = array(
						'HTTP_CLIENT_IP',
						'HTTP_X_FORWARDED_FOR',
						'HTTP_X_FORWARDED',
						'HTTP_X_CLUSTER_CLIENT_IP',
						'HTTP_FORWARDED_FOR',
						'HTTP_FORWARDED',
						'REMOTE_ADDR'
					);
	
		foreach ( $methods as $key ) {
	
			if ( true === array_key_exists( $key, $_SERVER ) ) {
	
				foreach ( explode( ',', $_SERVER[$key] ) as $ip ) {
	
					$ip = trim( $ip ); // just to be safe
	
					if ( strrpos( $ip, ':' ) )
						$ip = substr( $ip, strrpos( $ip, ':' ) + 1 );
	
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false )
						return $ip;
	
				}
	
			}
	
		}
	
	}

}

if ( !function_exists( 'wp_print_r' ) ) { 

	/**
	 * Helper function used for printing out debug information
	 *
	 * HT: Glenn Ansley @ iThemes.com
	 *
	 * @since 1.0.0
	 *
	 * @param int $args Arguments to pass to print_r
	 * @param bool $die TRUE to die else FALSE (default TRUE)
	 */
    function wp_print_r( $args, $die = true ) { 
	
        $echo = '<pre>' . print_r( $args, true ) . '</pre>';
		
        if ( $die ) die( $echo );
        	else echo $echo;
		
    }   
	
}