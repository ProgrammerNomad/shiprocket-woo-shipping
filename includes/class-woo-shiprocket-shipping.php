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
                add_filter( 'woocommerce_package_rates', array( $this, 'add_package_contents_weight' ), 10, 2 );
			}

			/**
			 * Define settings fields for this shipping method.
			 *
			 * @return void
			 */
			public function init_form_fields() {
				$this->form_fields = array(
					'email' => array(
						'title'       => __( 'Shiprocket Email', 'woo-shiprocket-shipping' ),
						'type'        => 'text',
						'description' => __( 'Enter your Shiprocket Email.', 'woo-shiprocket-shipping' ),
						'default'     => '',
					),
					'password' => array(
						'title'       => __( 'Shiprocket Password', 'woo-shiprocket-shipping' ),
						'type'        => 'password',
						'description' => __( 'Enter your Shiprocket Password.', 'woo-shiprocket-shipping' ),
						'default'     => '',
					),
					'token' => array(
						'title'       => __( 'Shiprocket Token', 'woo-shiprocket-shipping' ),
						'type'        => 'textarea',
						'description' => __( 'Shiprocket Token (generated on save)', 'woo-shiprocket-shipping' ),
						'default'     => '',
						'custom_attributes' => array(
							'readonly' => 'readonly'
						),
					),
					'show_pincode_check' => array(
						'title'       => __( 'Show Pincode Check', 'woo-shiprocket-shipping' ),
						'type'        => 'checkbox',
						'label'       => __( 'Enable pincode serviceability check on product pages', 'woo-shiprocket-shipping' ),
						'default'     => 'no',
					),
				);
			}

			/**
			 * Process admin options (save settings).
			 *
			 * @return bool Was anything saved?
			 */
			public function process_admin_options() {
				// Get the entered email and password
				$email = sanitize_text_field( $_POST['woocommerce_woo_shiprocket_shipping_email'] );
				$password = sanitize_text_field( $_POST['woocommerce_woo_shiprocket_shipping_password'] );

				// Make API call to Shiprocket to generate token with raw JSON body
				$response = wp_remote_post( 'https://apiv2.shiprocket.in/v1/external/auth/login', array(
					'headers' => array( 'Content-Type' => 'application/json' ), // Set Content-Type header
					'body'    => json_encode( array( // Encode body as JSON string
						'email'    => $email,
						'password' => $password,
					) ),
				) );

				if ( is_wp_error( $response ) ) {
					// Handle API error (e.g., display a notice)
					WC_Admin_Settings::add_error( sprintf( __( 'Shiprocket API Error: %s', 'woo-shiprocket-shipping' ), $response->get_error_message() ) );
					return false;
				}

				$body = json_decode( wp_remote_retrieve_body( $response ) );
				$code = wp_remote_retrieve_response_code( $response ); // Get the HTTP status code

				// Check if the status code is 403 (Forbidden)
				if ( $code == 403 ) {
					$error_message = __( 'Invalid Shiprocket email or password.', 'woo-shiprocket-shipping' );
					WC_Admin_Settings::add_error( $error_message );
					return false; 
				} else if ( ! isset( $body->token ) ) { // Check for other token generation errors
					$error_message = __( 'Failed to generate Shiprocket token.', 'woo-shiprocket-shipping' );
					WC_Admin_Settings::add_error( $error_message );
					return false; 
				}

				// Update the token field in the settings
				$_POST['woocommerce_woo_shiprocket_shipping_token'] = $body->token; 

				// Continue with the default saving process
				return parent::process_admin_options();
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
                $weight = .300; //$package['contents_weight'];

                // Make API call to Shiprocket to get rates for the pincode and weight
                $rates = woo_shiprocket_get_rates( $pincode, $weight ); // This function will be in the new file

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
                    wc_add_notice( __( 'No shipping rates found for your pincode.', 'woo-shiprocket-shipping' ), 'error' );
                }
			}

            /**
             * Add package contents weight to the package data.
             *
             * @param array  $rates    Shipping rates.
             * @param array  $package Package data.
             *
             * @return array Modified shipping rates.
             */
            public function add_package_contents_weight( $rates, $package ) {
                // Calculate the total weight of the package contents
                $weight = 0;
                foreach ( $package['contents'] as $item ) {
                    $weight += $item['data']->get_weight() * $item['quantity'];
                }

                // Add the 'contents_weight' key to the $package array
                $package['contents_weight'] = $weight;

                return $rates;
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
    wp_enqueue_script( 'woo-shiprocket-shipping-ajax', plugins_url( '../js/woo-shiprocket-shipping.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
    wp_localize_script( 'woo-shiprocket-shipping-ajax', 'woo_shiprocket_shipping_params', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'woo-shiprocket-shipping-nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'woo_shiprocket_shipping_enqueue_scripts' ); // Enqueue on frontend

/**
 * Enqueue custom CSS for cart and checkout pages.
 */
function woo_shiprocket_enqueue_custom_css() {
    if ( is_cart() || is_checkout() ) {
        wp_enqueue_style( 'woo-shiprocket-custom-css', plugins_url( '../css/woo-shiprocket-custom.css', __FILE__ ) );
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
    check_ajax_referer( 'woo-shiprocket-shipping-nonce', 'nonce' );

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