<?php

/**
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */

function leaky_paywall_get_ip_address()
{
	//Just get the headers if we can or else use the SERVER global
	if (function_exists('apache_request_headers')) {
		$headers = apache_request_headers();
	} else {
		$headers = $_SERVER;
	}

	//Get the forwarded IP if it exists
	if (
		array_key_exists('X-Forwarded-For', $headers) &&
		(filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
			filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
	) {
		$the_ip = $headers['X-Forwarded-For'];
	} elseif (
		array_key_exists('HTTP_X_FORWARDED_FOR', $headers) &&
		(filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
			filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
	) {
		$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
	} else {
		$the_ip = $_SERVER['REMOTE_ADDR'];
	}

	if (preg_match('((\d+)\.(\d+)\.(\d+)\.(\d+)$)', $the_ip, $matches)) {
		return $matches[0];
	}

	return esc_sql($the_ip);
}


function leaky_paywall_ip_allows_access()
{

	// $ip_address = leaky_paywall_get_ip_address();
	$ip_address = leaky_paywall_get_ip();
	$ip_address_long = (float)sprintf("%u", ip2long($ip_address));

	// $settings = get_option('issuem-leaky-paywall-ip-exceptions');
	// $allowed_ips = explode("\n", $settings['allowed_ip_addresses']);

	$allowed_ips = leaky_paywall_get_allowed_ips();

	foreach ($allowed_ips as $ip) {
		$ip = trim($ip);
		if ($ip === $ip_address) {
			return true;
		}
		if (false !== strpos($ip, '*')) {
			$start = (float)sprintf("%u", ip2long(trim(str_replace('*', '0', $ip))));
			$end = (float)sprintf("%u", ip2long(trim(str_replace('*', '255', $ip))));
			if ($ip_address_long >= $start && $ip_address_long <= $end) {
				return true;
			}
		}
		if (false !== stripos($ip, 'x')) {
			$start = (float)sprintf("%u", ip2long(trim(str_ireplace('x', '0', $ip))));
			$end = (float)sprintf("%u", ip2long(trim(str_ireplace('x', '255', $ip))));
			if ($ip_address_long >= $start && $ip_address_long <= $end) {
				return true;
			}
		}
		if (false !== stripos($ip, '-')) {
			list($start, $end) = explode('-', $ip, 2);
			$start = (float)sprintf("%u", ip2long(trim($start)));
			$end = (float)sprintf("%u", ip2long(trim($end)));
			if ($ip_address_long >= $start && $ip_address_long <= $end) {
				return true;
			}
		}
		if (false !== strpos($ip, '/')) {
			list($net, $mask) = explode('/', $ip, 2);
			$net = ip2long(trim($net));
			$mask = ~((1 << (32 - trim($mask))) - 1);
			$ip_net = $ip_address_long & $mask;
			if ($ip_net === $net) {
				return true;
			}
		}
	}

	return false;
}

function leaky_paywall_get_allowed_ips() {

	$allowed_ip_addresses = array();

	$args = array(
		'post_type' => 'lp_ip_exception',
		'post_status' => 'publish',
		'meta_key'   => '_ip_address_status',
		'meta_value' => 'active',
		'meta_compare' => '='
	);

	$exceptions = get_posts( $args );

	if ( empty( $exceptions ) ) {
		return $allowed_ip_addresses;
	}

	foreach( $exceptions as $exception ) {

		$allowed = get_post_meta( $exception->ID, '_allowed_ip_addresses', true );

		if ( $allowed ) {
			$exp_allowed_ips = explode("\n", $allowed );
		}

		foreach( $exp_allowed_ips as $ip ) {
			$allowed_ip_addresses[] = $ip;
		}

	}

	return $allowed_ip_addresses;

}