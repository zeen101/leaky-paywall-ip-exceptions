<?php

/**
 * Registers Leaky Paywall - IP Exceptions class
 *
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */

/**
 * This class registers the main IP Exceptions functionality
 *
 * @since 1.0.0
 */

class Leaky_Paywall_IP_Exceptions
{

	/**
	 * Class constructor, puts things in motion
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		add_filter('leaky_paywall_filter_is_restricted', array($this, 'maybe_allow_access'), 5, 3);
	}

	public function maybe_allow_access($is_restricted, $restriction_settings, $post_id)
	{
		if (leaky_paywall_ip_allows_access()) {
			$is_restricted = false;
		}

		return $is_restricted;
	}

}
