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

class Leaky_Paywall_IP_Exceptions
{

	/**
	 * Class constructor, puts things in motion
	 *
	 * @since 1.0.0
	 */
	function __construct()
	{

		$settings = $this->get_settings();

		add_filter('leaky_paywall_filter_is_restricted', array($this, 'maybe_allow_access'), 5, 3);
		add_action('leaky_paywall_after_general_settings', array($this, 'settings_div'));
		add_filter('leaky_paywall_update_settings_settings', array($this, 'update_settings_div'), 10, 2);

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 15 );
		add_action( 'init', array($this, 'register_post_type'));
		add_action( 'add_meta_boxes', array($this, 'create_meta_box'));
		add_action( 'save_post', array($this, 'save_meta'));

	}

	public function maybe_allow_access($is_restricted, $restriction_settings, $post_id)
	{

		if (leaky_paywall_ip_allows_access()) {
			$is_restricted = false;
		}

		return $is_restricted;
	}

	public function ip_allows_access()
	{

		$settings = $this->get_settings();
		$ip_address = leaky_paywall_get_ip_address();
		$ip_address_long = (float)sprintf("%u", ip2long($ip_address));


		$allowed_ips = explode("\n", $settings['allowed_ip_addresses']);

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

	/**
	 * Get zeen101's Leaky Paywall - IP Exceptions options
	 *
	 * @since 1.0.0
	 */
	function get_settings()
	{

		$defaults = array(
			'allowed_ip_addresses' => '',
		);

		$defaults = apply_filters('leaky_paywall_ip_exceptions_default_settings', $defaults);

		$settings = get_option('issuem-leaky-paywall-ip-exceptions');

		return wp_parse_args($settings, $defaults);
	}

	/**
	 * Update zeen101's Leaky Paywall options
	 *
	 * @since 1.0.0
	 */
	function update_settings($settings)
	{

		update_option('issuem-leaky-paywall-ip-exceptions', $settings);
	}

	/**
	 * Create and Display settings page
	 *
	 * @since 1.0.0
	 */
	function settings_div()
	{

		// Get the user options
		$settings = $this->get_settings();

		if ( !$settings['allowed_ip_addresses'] ) {
			return;
		}

		// Display HTML form for the options below
?>
		<div id="modules" class="postbox">

			<div class="handlediv" title="Click to toggle"><br /></div>

			<h3 class="hndle"><span><?php _e('IP Exceptions', 'issuem-lp-ipe'); ?></span></h3>

			<div class="inside">

				<table id="leaky_paywall_ip_exceptions" class="form-table">

					<tr>
						<th><?php _e('Allowed IP Addresses', 'issuem-lp-ipe'); ?></th>
						<td>
							<textarea id="allowed_ip_addresses" class="regular-text code" cols="50" rows="10" name="allowed_ip_addresses"><?php echo $settings['allowed_ip_addresses']; ?></textarea>
							<p class="description"><?php printf(__('Examples: %s', 'issuem-lp-ipe'), '<br />192.168.0.0<br />192.168.0.0-192.168.0.255<br />192.168.0.*<br />192.168.0.x<br />192.168.0.0/24'); ?></p>
						</td>
					</tr>

				</table>

			</div>

		</div>
<?php

	}

	function update_settings_div($settings, $current_tab)
	{

		if ($current_tab !== 'general') {
			return $settings;
		}

		// Get the user options
		$ip_settings = $this->get_settings();

		if (!empty($_REQUEST['allowed_ip_addresses']))
			$ip_settings['allowed_ip_addresses'] = str_replace(',', "\n", trim($_REQUEST['allowed_ip_addresses']));
		else
			$ip_settings['allowed_ip_addresses'] = '';

		$this->update_settings($ip_settings);

		return $settings;
	}

	public function register_post_type() {

		$labels = array(    
			'name' 					=> __( 'IP Exceptions', 'issuem-lp-ipe' ),
			'singular_name' 		=> __( 'IP Exceptions', 'issuem-lp-ipe' ),
			'add_new' 				=> __( 'Add New', 'issuem-lp-ipe' ),
			'add_new_item' 			=> __( 'Add New', 'issuem-lp-ipe' ),
			'edit_item' 			=> __( 'Edit', 'issuem-lp-ipe' ),
			'new_item' 				=> __( 'New', 'issuem-lp-ipe' ),
			'view_item' 			=> __( 'View', 'issuem-lp-ipe' ),
			'search_items' 			=> __( 'Search', 'issuem-lp-ipe' ),
			'not_found' 			=> __( 'No IP Exceptions found', 'issuem-lp-ipe' ),
			'not_found_in_trash' 	=> __( 'No IP Exceptions found in trash', 'issuem-lp-ipe' ), 
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __( 'IP Exceptions', 'issuem-lp-ipe' )
		);
		
		$args = array(
			// 'label' 				=> 'ip_exception',
			'labels' 				=> $labels,
			'description' 			=> __( 'IP Exceptions', 'issuem-lp-ipe' ),
			'public'				=> false,
			'publicly_queryable' 	=> false,
			'exclude_fromsearch' 	=> false,
			'show_ui' 				=> true,
			'show_in_menu' 			=> false,
			// 'capability_type' 		=> array( 'coupon', 'coupons' ),
			// 'map_meta_cap' 			=> true,
			'hierarchical' 			=> false,
			'supports' 				=> array( 'title' ),
			// 'register_meta_box_cb' 	=> 'add_leaky_paywall_coupons_metaboxes',
			'has_archive' 			=> false,
			'rewrite' 				=> array( 'slug' => 'lp-ip-exception' ),
			);
	
		register_post_type( 'lp_ip_exception', $args );

	}

	/**
	 * Initialize IssueM Admin Menu
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
						
		add_submenu_page( 'issuem-leaky-paywall', __( 'IP Exceptions', 'issuem-lp-ipe' ), __( 'IP Exceptions', 'issuem-lp-ipe' ), apply_filters( 'manage_issuem_settings', 'manage_options' ), 'edit.php?post_type=lp_ip_exception' );
				
	}

	public function create_meta_box()
	{
		add_meta_box('lp-exceptions-data', 'IP Exceptions Settings', array($this, 'meta_box_display'), 'lp_ip_exception', 'normal');
	}

	public function meta_box_display( $post )
	{

		$status = get_post_meta( $post->ID, '_ip_address_status', true );
		$allowed_ip_addresses = get_post_meta( $post->ID, '_allowed_ip_addresses', true );
		$ip_admin_notes = get_post_meta( $post->ID, '_ip_admin_notes', true );

		wp_nonce_field( 'lp_ip_exceptions_meta_box_nonce', 'lp_ip_exceptions_meta_box_field' ); 

		?>

		<table class="form-table">
			<tr valign="top">
				<th>
					<?php _e( 'Status', 'issuem-lp-ipe' ); ?>
				</th>
				<td>
					<select name="ip_address_status">
						<option value="active" <?php selected( 'active', $status ); ?>>Active</option>
						<option value="inactive" <?php selected( 'inactive', $status ); ?>>Inactive</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th>
					Allowed Addresses
				</th>
				<td>
					<textarea class="regular-text code" cols="50" rows="10" id="allowed_ip_addresses" name="lp_allowed_ip_addresses"><?php echo esc_html( $allowed_ip_addresses ); ?></textarea>
					<p class="description">Enter each rule on a new line.</p>

					<p class="description">
					Examples:<br>
					192.168.0.0<br>
					192.168.0.0-192.168.0.255<br>
					192.168.0.*<br>
					192.168.0.x<br>
					192.168.0.0/24
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th>
					Admin Notes
				</th>
				<td>
					<textarea class="regular-text code" cols="50" rows="5" id="ip_admin_notes" name="ip_admin_notes"><?php echo esc_html( $ip_admin_notes ); ?></textarea>
					
				</td>
			</tr>
		</table>
		<?php 
	}

	public function save_meta( $post_id )
	{
		
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 
		
		// if our nonce isn't there, or we can't verify it, bail 
		if( !isset( $_POST['lp_ip_exceptions_meta_box_field'] ) || !wp_verify_nonce( $_POST['lp_ip_exceptions_meta_box_field'], 'lp_ip_exceptions_meta_box_nonce' ) ) return; 
		
		// if our current user can't edit this post, bail  
		if( !current_user_can( 'edit_post' ) ) return;
		
		if ( isset( $_POST['ip_address_status'] ) ) {
			update_post_meta( $post_id, '_ip_address_status', str_replace(',', "\n", trim( $_POST['ip_address_status']) ) );
		}  

		if ( isset( $_POST['lp_allowed_ip_addresses'] ) ) {
			update_post_meta( $post_id, '_allowed_ip_addresses', str_replace(',', "\n", trim( $_POST['lp_allowed_ip_addresses']) ) );
		}  

		if ( isset( $_POST['ip_admin_notes'] ) ) {
			update_post_meta( $post_id, '_ip_admin_notes', str_replace(',', "\n", trim( $_POST['ip_admin_notes']) ) );
		}  

	}
}
