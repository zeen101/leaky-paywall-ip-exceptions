<?php

/**
 * Load the base class
 */
class Leaky_Paywall_IP_Exceptions_Settings
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'), 15);
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'create_meta_box'));
        add_action('save_post', array($this, 'save_meta'));

        add_filter('manage_lp_ip_exception_posts_columns', array($this, 'add_lp_ip_exception_columns'));
        add_filter('manage_posts_custom_column', array($this, 'lp_ip_exception_custom_columns'), 10, 2);

        add_action('admin_init', array($this, 'migrate_old_data'));
    }


    /**
     * Get zeen101's Leaky Paywall - IP Exceptions options
     *
     * @since 1.0.0
     */
    public function get_settings()
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
    public function update_settings($settings)
    {

        update_option('issuem-leaky-paywall-ip-exceptions', $settings);
    }

    /**
     * Create and Display settings page
     *
     * @since 1.0.0
     */
    public function settings_div()
    {

        // Get the user options
        $settings = $this->get_settings();

        if (!$settings['allowed_ip_addresses']) {
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

    public function update_settings_div($settings, $current_tab)
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

    public function register_post_type()
    {

        $labels = array(
            'name'                     => __('IP Exceptions', 'issuem-lp-ipe'),
            'singular_name'         => __('IP Exceptions', 'issuem-lp-ipe'),
            'add_new'                 => __('Add New', 'issuem-lp-ipe'),
            'add_new_item'             => __('Add New', 'issuem-lp-ipe'),
            'edit_item'             => __('Edit', 'issuem-lp-ipe'),
            'new_item'                 => __('New', 'issuem-lp-ipe'),
            'view_item'             => __('View', 'issuem-lp-ipe'),
            'search_items'             => __('Search', 'issuem-lp-ipe'),
            'not_found'             => __('No IP Exceptions found', 'issuem-lp-ipe'),
            'not_found_in_trash'     => __('No IP Exceptions found in trash', 'issuem-lp-ipe'),
            'parent_item_colon'     => '',
            'menu_name'             => __('IP Exceptions', 'issuem-lp-ipe')
        );

        $args = array(
            // 'label' 				=> 'ip_exception',
            'labels'                 => $labels,
            'description'             => __('IP Exceptions', 'issuem-lp-ipe'),
            'public'                => false,
            'publicly_queryable'     => false,
            'exclude_fromsearch'     => false,
            'show_ui'                 => true,
            'show_in_menu'             => false,
            // 'capability_type' 		=> array( 'coupon', 'coupons' ),
            // 'map_meta_cap' 			=> true,
            'hierarchical'             => false,
            'supports'                 => array('title'),
            // 'register_meta_box_cb' 	=> 'add_leaky_paywall_coupons_metaboxes',
            'has_archive'             => false,
            'rewrite'                 => array('slug' => 'lp-ip-exception'),
        );

        register_post_type('lp_ip_exception', $args);
    }

    /**
     * Initialize IssueM Admin Menu
     *
     * @since 1.0.0
     */
    public function admin_menu()
    {

        add_submenu_page('issuem-leaky-paywall', __('IP Exceptions', 'issuem-lp-ipe'), __('IP Exceptions', 'issuem-lp-ipe'), apply_filters('manage_issuem_settings', 'manage_options'), 'edit.php?post_type=lp_ip_exception');
    }

    public function create_meta_box()
    {
        add_meta_box('lp-exceptions-data', 'IP Exceptions Settings', array($this, 'settings_meta_box_display'), 'lp_ip_exception', 'normal');
        // add_meta_box('lp-exceptions-reports', 'IP Exceptions Reports', array($this, 'reports_meta_box_display'), 'lp_ip_exception', 'normal');
    }

    public function settings_meta_box_display($post)
    {

        $status = get_post_meta($post->ID, '_ip_address_status', true);
        $allowed_ip_addresses = get_post_meta($post->ID, '_allowed_ip_addresses', true);
        $ip_admin_notes = get_post_meta($post->ID, '_ip_admin_notes', true);

        wp_nonce_field('lp_ip_exceptions_meta_box_nonce', 'lp_ip_exceptions_meta_box_field');

    ?>

        <table class="form-table">
            <tr valign="top">
                <th>
                    <?php _e('Status', 'issuem-lp-ipe'); ?>
                </th>
                <td>
                    <select name="ip_address_status">
                        <option value="active" <?php selected('active', $status); ?>>Active</option>
                        <option value="inactive" <?php selected('inactive', $status); ?>>Inactive</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th>
                    <?php _e('Allowed Addresses', 'issuem-lp-ipe'); ?>
                </th>
                <td>
                    <textarea class="regular-text code" cols="50" rows="10" id="allowed_ip_addresses" name="lp_allowed_ip_addresses"><?php echo esc_html($allowed_ip_addresses); ?></textarea>
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
                    <?php _e('Admin Notes', 'issuem-lp-ipe'); ?>
                </th>
                <td>
                    <textarea class="regular-text code" cols="50" rows="5" id="ip_admin_notes" name="ip_admin_notes"><?php echo esc_html($ip_admin_notes); ?></textarea>

                </td>
            </tr>
        </table>
    <?php
    }

    public function reports_meta_box_display($post)
    {
    ?>
        <input type="text" id="datepicker-start" name="date_start" value="<?php echo $start; ?>"> to <input id="datepicker-end" type="text" name="date_end" value="<?php echo $end; ?>">
        <input type="hidden" name="ad_dropper_action" value="filter_ads">
        <input type="hidden" name="range" value="custom">
        <?php submit_button(__('Filter', 'ad-dropper'), 'secondary', 'submit', false); ?>

        <h3>55</h3>

        <p><strong>Total Number of Times Content Was Viewed during Time Period</strong></p>

<?php
    }

    public function save_meta($post_id)
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // if our nonce isn't there, or we can't verify it, bail
        if (!isset($_POST['lp_ip_exceptions_meta_box_field']) || !wp_verify_nonce($_POST['lp_ip_exceptions_meta_box_field'], 'lp_ip_exceptions_meta_box_nonce')) {
            return;
        }

        // if our current user can't edit this post, bail
        if (!current_user_can('edit_posts')) {
            return;
        }

        if (isset($_POST['ip_address_status'])) {
            update_post_meta($post_id, '_ip_address_status', sanitize_text_field($_POST['ip_address_status']));
        }

        if (isset($_POST['lp_allowed_ip_addresses'])) {
            update_post_meta($post_id, '_allowed_ip_addresses', str_replace(',', "\n", trim($_POST['lp_allowed_ip_addresses'])));
        }

        if (isset($_POST['ip_admin_notes'])) {
            update_post_meta($post_id, '_ip_admin_notes', sanitize_text_field( $_POST['ip_admin_notes']));
        }
    }

    /**
     * Adds extra column for the Views to the Ad Dropper
     *
     * @since 1.0
     */
    public function add_lp_ip_exception_columns($columns)
    {

        return array(
            'cb' => '<input type="checkbox" />',
            'title' => 'Title',
            'status' => 'Status',
            'ip_addresses' => 'IP Addresses',
            'notes' => 'Notes'
        );

        // return array_merge($columns, array('status' => 'Status', 'ip_addresses' => 'IP Addressess'));
    }

    /**
     * Adds the cat ID to the custom column
     *
     * @since 1.0
     */
    public function lp_ip_exception_custom_columns($column_name, $post_id)
    {

        switch ($column_name) {

            case 'status':

                echo esc_html(get_post_meta($post_id, '_ip_address_status', true));

                break;

            case 'ip_addresses':

                $out = get_post_meta($post_id, '_allowed_ip_addresses', true);

                if ($out) {
                    $exp_allowed_ips = explode("\n", $out);
                }

                foreach ($exp_allowed_ips as $ip) {
                    echo esc_html($ip) . '<br>';
                }


                break;

            case 'notes':
                $out = get_post_meta($post_id, '_ip_admin_notes', true);

                echo esc_html($out);

            default:
                break;
        }
    }

    public function migrate_old_data() {

        $old_data = get_option('issuem-leaky-paywall-ip-exceptions');

        if ( isset( $old_data['allowed_ip_addresses'] ) && $old_data['allowed_ip_addresses'] ) {

            // create a default post with the data
            $post_data = array(
                'post_type' => 'lp_ip_exception',
                'post_title' => 'Settings Data',
                'post_status'   => 'publish'
            );

            $new_post_id = wp_insert_post( $post_data );

            update_post_meta($new_post_id, '_ip_address_status', 'active');
            update_post_meta($new_post_id, '_allowed_ip_addresses', str_replace(',', "\n", trim( $old_data['allowed_ip_addresses'])));
            update_post_meta($new_post_id, '_ip_admin_notes', 'migrated from old settings');

            delete_option('issuem-leaky-paywall-ip-exceptions');

        }

    }
}

new Leaky_Paywall_IP_Exceptions_Settings();
