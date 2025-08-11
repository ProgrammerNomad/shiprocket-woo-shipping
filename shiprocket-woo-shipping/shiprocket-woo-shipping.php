<?php
/**
 * Plugin Name: Shiprocket WooCommerce Shipping
 * Plugin URI:  https://github.com/ProgrammerNomad/shiprocket-woo-shipping
 * Description: Modern, secure integration with Shiprocket's official API for real-time shipping rates and delivery estimates.
 * Version:     1.0.5
 * Author:      Shiv Singh
 * Author URI:  https://github.com/ProgrammerNomad/
 * Text Domain: shiprocket-woo-shipping
 * Domain Path: /languages
 * License:     GPLv2 or later
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * @package shiprocket-woo-shipping
 */

// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-shiprocket-woo-shipping.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/shiprocket-woo-shipping-pincode.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/shiprocket-woo-shipping-rates.php'; // Include the new file
}

// Initialize plugin updater for auto-updates
if ( ! class_exists( 'Shiprocket_Plugin_Updater' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-shiprocket-plugin-updater.php';
}

// Initialize the updater
add_action( 'init', function() {
    if ( class_exists( 'Shiprocket_Plugin_Updater' ) ) {
        new Shiprocket_Plugin_Updater( __FILE__ );
    }
} );

/**
 * Add plugin action links on plugins page.
 *
 * @param array $links Existing action links.
 * @return array Modified action links.
 */
function shiprocket_woo_shipping_plugin_action_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=woo_shiprocket_shipping' ) . '">' . __( 'Settings', 'shiprocket-woo-shipping' ) . '</a>',
        '<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping/wiki" target="_blank">' . __( 'Documentation', 'shiprocket-woo-shipping' ) . '</a>',
        '<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping/issues" target="_blank">' . __( 'Support', 'shiprocket-woo-shipping' ) . '</a>',
    );
    
    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'shiprocket_woo_shipping_plugin_action_links' );

/**
 * Add plugin meta links on plugins page.
 *
 * @param array  $links Existing meta links.
 * @param string $file  Plugin file path.
 * @return array Modified meta links.
 */
function shiprocket_woo_shipping_plugin_row_meta( $links, $file ) {
    if ( plugin_basename( __FILE__ ) === $file ) {
        $row_meta = array(
            'changelog' => '<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping/releases" target="_blank">' . __( 'View Changelog', 'shiprocket-woo-shipping' ) . '</a>',
            'shiprocket' => '<a href="https://shiprocket.in/dashboard" target="_blank">' . __( 'Shiprocket Dashboard', 'shiprocket-woo-shipping' ) . '</a>',
            'rate' => '<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping" target="_blank">⭐ ' . __( 'Rate Plugin', 'shiprocket-woo-shipping' ) . '</a>',
        );
        
        return array_merge( $links, $row_meta );
    }
    
    return $links;
}
add_filter( 'plugin_row_meta', 'shiprocket_woo_shipping_plugin_row_meta', 10, 2 );