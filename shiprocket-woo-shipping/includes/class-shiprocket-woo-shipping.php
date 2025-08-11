<?php
/**
 * Shiprocket Shipping Method
 *
 * @package shiprocket-woo-shipping
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
				$this->method_title       = __( 'Shiprocket Shipping Method', 'shiprocket-woo-shipping' ); 
				$this->method_description = __( 'Get live rates and ship with Shiprocket.', 'shiprocket-woo-shipping' ); 

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
               // add_filter( 'woocommerce_package_rates', array( $this, 'add_package_contents_weight' ), 10, 2 );
			}

			/**
			 * Define settings fields for this shipping method.
			 *
			 * @return void
			 */
			public function init_form_fields() {
				$this->form_fields = array(
					'api_key' => array(
						'title'       => __( 'Shiprocket API Key', 'shiprocket-woo-shipping' ),
						'type'        => 'text',
						'description' => __( 'Enter your Shiprocket API Key from Settings > API in your Shiprocket dashboard.', 'shiprocket-woo-shipping' ),
						'default'     => '',
						'desc_tip'    => true,
					),
					'pickup_postcode' => array(
						'title'       => __( 'Pickup Postcode', 'shiprocket-woo-shipping' ),
						'type'        => 'text',
						'description' => __( 'Your warehouse/pickup location postcode. Auto-filled from WooCommerce store address.', 'shiprocket-woo-shipping' ),
						'default'     => $this->get_shop_postcode(),
						'desc_tip'    => true,
					),
					'show_pincode_check' => array(
						'title'       => __( 'Show Pincode Check', 'shiprocket-woo-shipping' ),
						'type'        => 'checkbox',
						'label'       => __( 'Enable pincode serviceability check on product pages', 'shiprocket-woo-shipping' ),
						'default'     => 'no',
					),
					'show_top_courier' => array(
						'title'       => __( 'Show Top Courier', 'shiprocket-woo-shipping' ),
						'type'        => 'checkbox',
						'label'       => __( 'Show only top rated 5 courier providers.', 'shiprocket-woo-shipping' ),
						'default'     => 'yes',
					),
					'cache_duration' => array(
						'title'       => __( 'Cache Duration (minutes)', 'shiprocket-woo-shipping' ),
						'type'        => 'number',
						'description' => __( 'How long to cache shipping rates to improve performance.', 'shiprocket-woo-shipping' ),
						'default'     => '10',
						'desc_tip'    => true,
					),
				);
			}

			/**
			 * Get shop postcode from WooCommerce settings.
			 *
			 * @return string Shop postcode.
			 */
			private function get_shop_postcode() {
				$postcode = get_option('woocommerce_store_postcode');
				
				// Fallback to base location if store postcode is not set
				if (empty($postcode)) {
					$base_location = wc_get_base_location();
					$postcode = WC()->countries->get_base_postcode();
				}
				
				return $postcode ? $postcode : '';
			}

			/**
			 * Process admin options (save settings).
			 *
			 * @return bool Was anything saved?
			 */
			public function process_admin_options() {
				// Validate API key
				$api_key = sanitize_text_field( $_POST['woocommerce_woo_shiprocket_shipping_api_key'] );
				
				if ( !empty( $api_key ) ) {
					// Test API key with a simple serviceability check
					$test_response = $this->test_api_key( $api_key );
					
					if ( !$test_response ) {
						WC_Admin_Settings::add_error( __( 'Invalid Shiprocket API Key. Please check your API key from Shiprocket dashboard.', 'shiprocket-woo-shipping' ) );
						return false;
					} else {
						WC_Admin_Settings::add_message( __( 'Shiprocket API Key validated successfully!', 'shiprocket-woo-shipping' ) );
					}
				}

				// Continue with the default saving process
				return parent::process_admin_options();
			}

			/**
			 * Test API key validity.
			 *
			 * @param string $api_key The API key to test.
			 * @return bool True if valid, false otherwise.
			 */
			private function test_api_key( $api_key ) {
				// Simple test call to verify API key
				$response = wp_remote_get( 'https://apiv2.shiprocket.in/v1/courier/companies', array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type' => 'application/json',
					),
					'timeout' => 30,
				) );

				if ( is_wp_error( $response ) ) {
					return false;
				}

				$response_code = wp_remote_retrieve_response_code( $response );
				return $response_code === 200;
			}


			/**
			 * Calculate shipping function.
			 *
			 * @access public
			 * @param array $package
			 * @return void
			 */
			public function calculate_shipping( $package = array() ) {
                $pincode = $package['destination']['postcode'];
                $weight = WC()->cart->get_cart_contents_weight(); 
				$Dimensions = $this->GetLengthBreadthHeight($package);
				$total_amount = WC()->cart->get_cart_contents_total();

                // Make API call to Shiprocket to get rates for the pincode and weight
                $rates = woo_shiprocket_get_rates( $pincode, $weight, $Dimensions, $total_amount ); // This function will be in the new file

                if ( ! empty( $rates ) ) {
                    foreach ( $rates as $rate ) {
                        $this->add_rate( array(
                            'id'    => $this->id . '_' . $rate['id'],
                            'label' => $rate['name'],
                            'cost'  => $rate['cost'], 
                        ) );
                    }
                } else {
                    // If no rates are found, display an error message
                    wc_add_notice( __( 'No shipping rates found for your pincode.', 'shiprocket-woo-shipping' ), 'error' );
                }
			}

			public function GetLengthBreadthHeight ($package = array())
			{
				$length = 0;
				$breadth = 0;
				$height = 0;
				foreach ($package['contents'] as $item_id => $values) {
					$_product = $values['data'];
					$length += $_product->get_length() * $values['quantity'];
					$breadth += $_product->get_width() * $values['quantity'];
					$height += $_product->get_height() * $values['quantity'];
				}
				return array('length' => $length, 'breadth' => $breadth, 'height' => $height);

			}
           
		} // end class WC_Shiprocket_Shipping_Method
	}
}

add_action( 'woocommerce_shipping_init', 'woo_shiprocket_shipping_init' );

function add_woo_shiprocket_shipping_method( $methods ) {
	$methods['woo_shiprocket_shipping'] = 'WC_Shiprocket_Shipping_Method';
	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_woo_shiprocket_shipping_method' );

/**
 * Enqueue JavaScript for AJAX call.
 */
function woo_shiprocket_shipping_enqueue_scripts() {
    wp_enqueue_script( 'shiprocket-woo-shipping-ajax', plugins_url( '../js/shiprocket-woo-shipping.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
    wp_localize_script( 'shiprocket-woo-shipping-ajax', 'woo_shiprocket_shipping_params', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'shiprocket-woo-shipping-nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'woo_shiprocket_shipping_enqueue_scripts' ); // Enqueue on frontend

/**
 * Enqueue custom CSS for cart and checkout pages.
 */
function woo_shiprocket_enqueue_custom_css() {
    if ( is_cart() || is_checkout() ) {
        wp_enqueue_style( 'woo-shiprocket-custom-css', plugins_url( '../css/woo-shiprocket-custom.css', __FILE__ ), array(), '1.0.0' );
    }
}
add_action( 'wp_enqueue_scripts', 'woo_shiprocket_enqueue_custom_css' );


add_action( 'wp_ajax_woo_shiprocket_update_shipping_methods', 'woo_shiprocket_update_shipping_methods' );
add_action( 'wp_ajax_nopriv_woo_shiprocket_update_shipping_methods', 'woo_shiprocket_update_shipping_methods' );

/**
 * AJAX handler to update shipping methods on pincode change.
 *
 * @return void
 */
function woo_shiprocket_update_shipping_methods() {
    check_ajax_referer( 'shiprocket-woo-shipping-nonce', 'nonce' );

    $pincode = sanitize_text_field( $_POST['pincode'] );

    // Get the cart items and calculate the total weight
    $weight = 0;
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $product = $cart_item['data'];
        if ( $product->needs_shipping() ) {
            $weight += $product->get_weight() * $cart_item['quantity'];
        }
    }

    // Fetch Shiprocket rates for the given pincode and weight
    $rates = woo_shiprocket_get_rates( $pincode, $weight );

    // Prepare the shipping methods array
    $shipping_methods = array();
    if ( ! empty( $rates ) ) {
        foreach ( $rates as $rate ) {
            $shipping_methods[ 'woo_shiprocket_shipping_' . $rate['id'] ] = array(
                'id'       => 'woo_shiprocket_shipping_' . $rate['id'],
                'label'    => $rate['name'],
                'cost'     => $rate['cost'],
                'calc_tax' => 'per_order',
            );
        }
    }

    // Update the shipping methods in the WC session
    WC()->session->set( 'shipping_for_package_0', $shipping_methods );

    // Recalculate shipping costs
    WC()->shipping()->calculate_shipping( WC()->cart->get_shipping_packages() );

    // Return the updated shipping methods HTML
    ob_start();
    woocommerce_review_order_before_shipping();
    woocommerce_review_order_shipping();
    woocommerce_review_order_after_shipping();
    $shipping_html = ob_get_clean();

    wp_send_json_success( array( 'shipping_html' => $shipping_html ) );
}