// includes/class-woo-shiprocket-shipping.php

<?php
/**
 * Shiprocket Shipping Method
 *
 * @package woo-shiprocket-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function woo_shiprocket_shipping_init() {
	if ( ! class_exists( 'WC_Shiprocket_Shipping_Method' ) ) {
		class WC_Shiprocket_Shipping_Method extends WC_Shipping_Method {
			/**
			 * Constructor for the shipping class
			 *
			 * @access public
			 * @return void
			 */
			public function __construct() {
				$this->id                 = 'woo_shiprocket_shipping'; 
				$this->method_title       = __( 'Shiprocket Shipping Method', 'woo-shiprocket-shipping' ); 
				$this->method_description = __( 'Get live rates and ship with Shiprocket.', 'woo-shiprocket-shipping' ); 

				$this->enabled            = "yes"; 
				$this->title              = "Shiprocket"; 

				$this->init();
			}

			/**
			 * Init your settings
			 *
			 * @access public
			 * @return void
			 */
			function init() {
				// Load the settings API
				$this->init_form_fields(); 
				$this->init_settings(); 

				// Save settings in admin if you have any defined
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			/**
			 * Define settings fields for this shipping method.
			 *
			 * @return void
			 */
			public function init_form_fields() {
				$this->form_fields = array(
					'api_key' => array(
						'title'       => __( 'Shiprocket API Key', 'woo-shiprocket-shipping' ),
						'type'        => 'text',
						'description' => __( 'Enter your Shiprocket API Key.', 'woo-shiprocket-shipping' ),
						'default'     => '',
					),
					// ... Add more fields for other Shiprocket settings (e.g., pickup location)
				);
			}


			/**
			 * calculate_shipping function.
			 *
			 * @access public
			 * @param array $package
			 * @return void
			 */
			public function calculate_shipping( $package = array() ) {
				// ... (Implementation to fetch rates from Shiprocket API)

				// Example (replace with actual Shiprocket API call):
				$rate = array(
					'id'       => $this->id,
					'label'    => $this->title,
					'cost'     => '10', // Get shipping cost from Shiprocket API
					'calc_tax' => 'per_order',
				);

				// Register the rate
				$this->add_rate( $rate );
			}
		}
	}
}

add_action( 'woocommerce_shipping_init', 'woo_shiprocket_shipping_init' );

function add_woo_shiprocket_shipping_method( $methods ) {
	$methods['woo_shiprocket_shipping'] = 'WC_Shiprocket_Shipping_Method';
	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_woo_shiprocket_shipping_method' );