<?php
/**
 * Plugin Name: Shiprocket WooCommerce Shipping
 * Plugin URI:  https://github.com/ProgrammerNomad/shiprocket-woo-shipping
 * Description: Modern, secure integration with Shiprocket's official API for real-time shipping rates and delivery estimates.
 * Version:     1.0.4
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