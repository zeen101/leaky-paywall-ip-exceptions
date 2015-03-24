<?php
/**
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */
 
if ( !function_exists( 'leaky_paywall_get_ip_address' ) ) {
		
	function leaky_paywall_get_ip_address() {
		//Just get the headers if we can or else use the SERVER global
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();
		} else {
			$headers = $_SERVER;
		}
		
		//Get the forwarded IP if it exists
		if ( array_key_exists( 'X-Forwarded-For', $headers ) &&
			(
				filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ||
				filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) )
			) {
				$the_ip = $headers['X-Forwarded-For'];
		} elseif (
			array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) &&
			(
				filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ||
				filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 )
			)
			) {
			$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
		} else {
			$the_ip = $_SERVER['REMOTE_ADDR'];
		}
		
		if ( preg_match( '((\d+)\.(\d+)\.(\d+)\.(\d+)$)', $the_ip, $matches ) ) {
			return $matches[0];
		}
		
		return esc_sql( $the_ip );	
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
