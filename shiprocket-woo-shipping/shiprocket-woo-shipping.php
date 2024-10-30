<?php
/**
 * Plugin Name: Shiprocket Woo Shipping
 * Plugin URI:  https://github.com/ProgrammerNomad/shiprocket-woo-shipping
 * Description: Integrate Shiprocket shipping rates into your WooCommerce store.
 * Version:     1.0.1
 * Author:      Shiv Singh
 * Author URI:  https://github.com/ProgrammerNomad/
 * Text Domain: shiprocket-woo-shipping
 * Domain Path: /languages
 * License:     GPLv2 or later
 * @package shiprocket-woo-shipping
 */

// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-shiprocket-woo-shipping.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/shiprocket-woo-shipping-pincode.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/shiprocket-woo-shipping-rates.php'; // Include the new file
}