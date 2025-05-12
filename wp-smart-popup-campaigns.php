<?php
/**
 * Plugin Name:       WP Smart Popup Campaigns
 * Plugin URI:        https://nadimtuhin.com/
 * Description:       Create and manage popup campaigns using images or custom HTML.
 * Version:           1.0.0
 * Author:            Nadim Tuhin
 * Author URI:        https://nadimtuhin.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-smart-popup
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPSP_VERSION', '1.0.0' );
define( 'WPSP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require WPSP_PLUGIN_DIR . 'includes/class-wpsp-cpt.php';
require WPSP_PLUGIN_DIR . 'admin/class-wpsp-admin.php';
require WPSP_PLUGIN_DIR . 'public/class-wpsp-public.php';

function run_wpsp_smart_popup_campaigns() {
    new WPSP_CPT();
    new WPSP_Admin( 'wp-smart-popup', WPSP_VERSION );
    new WPSP_Public( 'wp-smart-popup', WPSP_VERSION );
}
add_action( 'plugins_loaded', 'run_wpsp_smart_popup_campaigns' );

function wpsp_activate() {
    $cpt = new WPSP_CPT();
    $cpt->register_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpsp_activate' );

function wpsp_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wpsp_deactivate' ); 