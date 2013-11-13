<?php
/**
 * Registers IssueM's Leaky Paywall class
 *
 * @package IssueM's Leaky Paywall
 * @since 1.0.0
 */

/**
 * This class registers the main issuem functionality
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'IssueM_Leaky_Paywall_IP_Exceptions' ) ) {
	
	class IssueM_Leaky_Paywall_IP_Exceptions {
		
		private $plugin_name	= ISSUEM_LP_IPE_NAME;
		private $plugin_slug	= ISSUEM_LP_IPE_SLUG;
		private $basename		= ISSUEM_LP_IPE_BASENAME;
		
		/**
		 * Class constructor, puts things in motion
		 *
		 * @since 1.0.0
		 */
		function __construct() {
					
			$settings = $this->get_settings();
			
			add_action( 'wp', array( $this, 'process_requests' ), 5 );
			
			add_action( 'issuem_leaky_paywall_settings_form', array( $this, 'settings_div' ) );
			add_action( 'issuem_leaky_paywall_update_settings', array( $this, 'update_settings_div' ) );
			
		}
		
		function process_requests() {
			
			global $dl_pluginissuem_leaky_paywall;
			
			$settings = $this->get_settings();
			$ip_address = issuem_leaky_paywall_get_ip_address();
			$ip_address_long = (float)sprintf( "%u", ip2long( $ip_address ) );
			
			$allowed_ips = explode( "\n", $settings['allowed_ip_addresses'] );
			
			foreach( $allowed_ips as $ip ) {
				$ip = trim( $ip );
				if ( $ip === $ip_address ) {
					remove_action( 'wp', array( $dl_pluginissuem_leaky_paywall, 'process_requests' ) );
					return;
				}	
				if ( false !== strpos( $ip, '*' ) ) {
					$start = (float)sprintf( "%u", ip2long( str_replace( '*', '0', $ip ) ) );
					$end = (float)sprintf( "%u", ip2long( str_replace( '*', '254', $ip ) ) );
					if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
						remove_action( 'wp', array( $dl_pluginissuem_leaky_paywall, 'process_requests' ) );
						return;
					}
				}
				if ( false !== stripos( $ip, 'x' ) ) {
					$start = (float)sprintf( "%u", ip2long( str_ireplace( 'x', '0', $ip ) ) );
					$end = (float)sprintf( "%u", ip2long( str_ireplace( 'x', '254', $ip ) ) );
					if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
						remove_action( 'wp', array( $dl_pluginissuem_leaky_paywall, 'process_requests' ) );
						return;
					}
				}
				if ( false !== stripos( $ip, '-' ) ) {
					list( $start, $end ) = explode( '-', $ip, 2 );
					$start = (float)sprintf( "%u", ip2long( $start ) );
					$end = (float)sprintf( "%u", ip2long( $end ) );
					if ( $ip_address_long >= $start && $ip_address_long <= $end ) {
						remove_action( 'wp', array( $dl_pluginissuem_leaky_paywall, 'process_requests' ) );
						return;
					}
				}
				if ( false !== strpos( $ip, '/' ) ) {
					list( $net, $mask ) = explode( '/', $ip, 2 );
					$net = ip2long( $net );
					$mask = ~( ( 1 << ( 32 - $mask ) ) - 1 );
					$ip_net = $ip_address_long & $mask;
					if ( $ip_net === $net ) {
						remove_action( 'wp', array( $dl_pluginissuem_leaky_paywall, 'process_requests' ) );
						return;
					}
				}
			}
			
		}
		
		/**
		 * Get IssueM's Leaky Paywall - IP Exceptions options
		 *
		 * @since 1.0.0
		 */
		function get_settings() {
			
			$defaults = array( 
				'allowed_ip_addresses' => '',
			);
		
			$defaults = apply_filters( 'issuem_leaky_paywall_ip_exceptions_default_settings', $defaults );
			
			$settings = get_option( 'issuem-leaky-paywall-ip-exceptions' );
												
			return wp_parse_args( $settings, $defaults );
			
		}
		
		/**
		 * Update IssueM's Leaky Paywall options
		 *
		 * @since 1.0.0
		 */
		function update_settings( $settings ) {
			
			update_option( 'issuem-leaky-paywall-ip-exceptions', $settings );
			
		}
		
		/**
		 * Create and Display IssueM settings page
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
                
                <h3 class="hndle"><span><?php _e( 'Leaky Paywall - IP Exceptions', 'issuem-lp-ipe' ); ?></span></h3>
                
                <div class="inside">
                
                <table id="issuem_leaky_paywall_ip_exceptions">
                
                    <tr>
                        <th><?php _e( 'Allowed IP Addresses', 'issuem-lp-ipe' ); ?></th>
                        <td>
                        <textarea id="allowed_ip_addresses" class="regular-text code" cols="50" rows="10" name="allowed_ip_addresses"><?php echo $settings['allowed_ip_addresses']; ?></textarea>
                        <p class="description"><?php printf( __( 'Examples: %s', 'issuem-lp-ipe' ), '<br />192.168.0.0<br />192.168.0.0-192.168.0-192.168.0.254<br />192.168.0.*<br />192.168.0.x<br />192.168.0.0/24' ); ?></p>
                        </td>
                    </tr>
                    
                </table>
                                                                  
                <p class="submit">
                    <input class="button-primary" type="submit" name="update_issuem_leaky_paywall_settings" value="<?php _e( 'Save Settings', 'issuem-lp-ipe' ) ?>" />
                </p>

                </div>
                
            </div>
			<?php
			
		}
		
		function update_settings_div() {
		
			// Get the user options
			$settings = $this->get_settings();
				
			if ( !empty( $_REQUEST['allowed_ip_addresses'] ) )
				$settings['allowed_ip_addresses'] = str_replace( ',', "\n", trim( $_REQUEST['allowed_ip_addresses'] ) );
			else
				$settings['allowed_ip_addresses'] = '';
			
			$this->update_settings( $settings );
			
		}
		
	}
	
}