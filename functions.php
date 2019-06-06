<?php
/**
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */
 
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


function leaky_paywall_ip_allows_access() {

	$settings = get_option( 'issuem-leaky-paywall-ip-exceptions' );
	$ip_address = leaky_paywall_get_ip_address();
	$ip_address_long = (float)sprintf( "%u", ip2long( $ip_address ) );
	
	$allowed_ips = explode( "\n", $settings['allowed_ip_addresses'] );

	foreach( $allowed_ips as $ip ) {
		$ip = trim( $ip );
		if ( $ip === $ip_address ) {
			return true;
		}	
		if ( false !== strpos( $ip, '*' ) ) {
			$start = (float)sprintf( "%u", ip2long( trim( str_replace( '*', '0', $ip ) ) ) );
			$end = (float)sprintf( "%u", ip2long( trim( str_replace( '*', '255', $ip ) ) ) );
			if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
				return true;
			}
		}
		if ( false !== stripos( $ip, 'x' ) ) {
			$start = (float)sprintf( "%u", ip2long( trim( str_ireplace( 'x', '0', $ip ) ) ) );
			$end = (float)sprintf( "%u", ip2long( trim( str_ireplace( 'x', '255', $ip ) ) ) );
			if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
				return true;
			}
		}
		if ( false !== stripos( $ip, '-' ) ) {
			list( $start, $end ) = explode( '-', $ip, 2 );
			$start = (float)sprintf( "%u", ip2long( trim( $start ) ) );
			$end = (float)sprintf( "%u", ip2long( trim( $end ) ) );
			if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
				return true;
			}
		}
		if ( false !== strpos( $ip, '/' ) ) {
			list( $net, $mask ) = explode( '/', $ip, 2 );
			$net = ip2long( trim( $net ) );
			$mask = ~( ( 1 << ( 32 - trim( $mask ) ) ) - 1 );
			$ip_net = $ip_address_long & $mask;
			if ( $ip_net === $net ) {
				return true;
			}
		}
	}

	return false;
	
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
