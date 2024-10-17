<?php
/**
 * Shiprocket Shipping Method
 *
 * @package woo-shiprocket-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Woo_Shiprocket_Shipping_Method
 */
class Woo_Shiprocket_Shipping_Method extends WC_Shipping_Method {

	/**
	 * Constructor for the shipping method.
	 */
	public function __construct() {
		$this->id                 = 'woo_shiprocket_shipping';
		$this->method_title       = __( 'Shiprocket', 'woo-shiprocket-shipping' );
		$this->method_description = __( 'Get live rates and ship with Shiprocket.', 'woo-shiprocket-shipping' );

		// Availability & Countries
		$this->availability = 'including';
		$this->countries    = array( 'IN' ); // India by default.

		$this->init();

		$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
		$this->title   = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Shiprocket', 'woo-shiprocket-shipping' );
	}

	/**
	 * Initialize settings.
	 *
	 * @return void
	 */
	public function init() {
		// Load the settings API.
		$this->init_form_fields();
		$this->init_settings();

		// Save settings in admin if you have any defined.
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Define settings field for this shipping method.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woo-shiprocket-shipping' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Shiprocket Shipping', 'woo-shiprocket-shipping' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Title', 'woo-shiprocket-shipping' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woo-shiprocket-shipping' ),
				'default'     => __( 'Shiprocket', 'woo-shiprocket-shipping' ),
				'desc_tip'    => true,
			),
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
	 * Calculate shipping function.
	 *
	 * @param array $package Package information.
	 *
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

		// Register the rate.
		$this->add_rate( $rate );
	}
}