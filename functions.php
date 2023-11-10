<?php

function leaky_paywall_ip_allows_access()
{

	$ip_address = leaky_paywall_get_ip();
	$ip_address_long = (float)sprintf("%u", ip2long($ip_address));

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