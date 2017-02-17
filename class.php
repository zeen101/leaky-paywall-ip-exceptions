<?php
/**
 * Registers zeen101's Leaky Paywall class
 *
 * @package Leaky Paywall - IP Exceptions
 * @since 1.0.0
 */

/**
 * This class registers the main issuem functionality
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Leaky_Paywall_IP_Exceptions' ) ) {
	
	class Leaky_Paywall_IP_Exceptions {
		
		/**
		 * Class constructor, puts things in motion
		 *
		 * @since 1.0.0
		 */
		function __construct() {
					
			$settings = $this->get_settings();
			
			add_action( 'wp', array( $this, 'process_requests' ), 5 );
			
			add_action( 'leaky_paywall_after_subscriptions_settings', array( $this, 'settings_div' ) );
			add_filter( 'leaky_paywall_update_settings_settings', array( $this, 'update_settings_div' ), 10, 2 );
			
		}
		
		function process_requests() {
			
			global $leaky_paywall;
			
			$settings = $this->get_settings();
			$ip_address = leaky_paywall_get_ip_address();
			$ip_address_long = (float)sprintf( "%u", ip2long( $ip_address ) );
			
			$allowed_ips = explode( "\n", $settings['allowed_ip_addresses'] );
			
			foreach( $allowed_ips as $ip ) {
				$ip = trim( $ip );
				if ( $ip === $ip_address ) {
					remove_action( 'wp', array( $leaky_paywall, 'process_requests' ) );
					return;
				}	
				if ( false !== strpos( $ip, '*' ) ) {
					$start = (float)sprintf( "%u", ip2long( trim( str_replace( '*', '0', $ip ) ) ) );
					$end = (float)sprintf( "%u", ip2long( trim( str_replace( '*', '255', $ip ) ) ) );
					if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
						remove_action( 'wp', array( $leaky_paywall, 'process_requests' ) );
						return;
					}
				}
				if ( false !== stripos( $ip, 'x' ) ) {
					$start = (float)sprintf( "%u", ip2long( trim( str_ireplace( 'x', '0', $ip ) ) ) );
					$end = (float)sprintf( "%u", ip2long( trim( str_ireplace( 'x', '255', $ip ) ) ) );
					if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
						remove_action( 'wp', array( $leaky_paywall, 'process_requests' ) );
						return;
					}
				}
				if ( false !== stripos( $ip, '-' ) ) {
					list( $start, $end ) = explode( '-', $ip, 2 );
					$start = (float)sprintf( "%u", ip2long( trim( $start ) ) );
					$end = (float)sprintf( "%u", ip2long( trim( $end ) ) );
					if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
						remove_action( 'wp', array( $leaky_paywall, 'process_requests' ) );
						return;
					}
				}
				if ( false !== strpos( $ip, '/' ) ) {
					list( $net, $mask ) = explode( '/', $ip, 2 );
					$net = ip2long( trim( $net ) );
					$mask = ~( ( 1 << ( 32 - trim( $mask ) ) ) - 1 );
					$ip_net = $ip_address_long & $mask;
					if ( $ip_net === $net ) {
						remove_action( 'wp', array( $leaky_paywall, 'process_requests' ) );
						return;
					}
				}
			}
			
		}
		
		/**
		 * Get zeen101's Leaky Paywall - IP Exceptions options
		 *
		 * @since 1.0.0
		 */
		function get_settings() {
			
			$defaults = array( 
				'allowed_ip_addresses' => '',
			);
		
			$defaults = apply_filters( 'leaky_paywall_ip_exceptions_default_settings', $defaults );
			
			$settings = get_option( 'issuem-leaky-paywall-ip-exceptions' );
												
			return wp_parse_args( $settings, $defaults );
			
		}
		
		/**
		 * Update zeen101's Leaky Paywall options
		 *
		 * @since 1.0.0
		 */
		function update_settings( $settings ) {
			
			update_option( 'issuem-leaky-paywall-ip-exceptions', $settings );
			
		}
		
		/**
		 * Create and Display settings page
		 *
		 * @since 1.0.0
		 */
		function settings_div() {
			
			// Get the user options
			$settings = $this->get_settings();
			
			// Display HTML form for the options below
			?>
            <div id="modules" class="postbox">
            
                <div class="handlediv" title="Click to toggle"><br /></div>
                
                <h3 class="hndle"><span><?php _e( 'IP Exceptions', 'issuem-lp-ipe' ); ?></span></h3>
                
                <div class="inside">
                
                <table id="leaky_paywall_ip_exceptions" class="form-table">
                
                    <tr>
                        <th><?php _e( 'Allowed IP Addresses', 'issuem-lp-ipe' ); ?></th>
                        <td>
                        <textarea id="allowed_ip_addresses" class="regular-text code" cols="50" rows="10" name="allowed_ip_addresses"><?php echo $settings['allowed_ip_addresses']; ?></textarea>
                        <p class="description"><?php printf( __( 'Examples: %s', 'issuem-lp-ipe' ), '<br />192.168.0.0<br />192.168.0.0-192.168.0.255<br />192.168.0.*<br />192.168.0.x<br />192.168.0.0/24' ); ?></p>
                        </td>
                    </tr>
                    
                </table>
                                                                  
                </div>
                
            </div>
			<?php
			
		}
		
		function update_settings_div($settings, $current_tab) {
			
			if ( $current_tab !== 'subscriptions' ) {
				return $settings;
			}

			// Get the user options
			$ip_settings = $this->get_settings();
				
			if ( !empty( $_REQUEST['allowed_ip_addresses'] ) )
				$ip_settings['allowed_ip_addresses'] = str_replace( ',', "\n", trim( $_REQUEST['allowed_ip_addresses'] ) );
			else
				$ip_settings['allowed_ip_addresses'] = '';
			
			$this->update_settings( $ip_settings );

			return $settings;
			
		}
		
	}
	
}
