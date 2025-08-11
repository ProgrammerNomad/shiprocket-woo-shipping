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
					'email' => array(
						'title'       => __( 'Shiprocket Email', 'shiprocket-woo-shipping' ),
						'type'        => 'text',
						'description' => __( 'Enter your Shiprocket Email.', 'shiprocket-woo-shipping' ),
						'default'     => '',
					),
					'password' => array(
						'title'       => __( 'Shiprocket Password', 'shiprocket-woo-shipping' ),
						'type'        => 'password',
						'description' => __( 'Enter your Shiprocket Password.', 'shiprocket-woo-shipping' ),
						'default'     => '',
					),
					'token' => array(
						'title'       => __( 'Shiprocket Token', 'shiprocket-woo-shipping' ),
						'type'        => 'textarea',
						'description' => __( 'Shiprocket Token (generated on save)', 'shiprocket-woo-shipping' ),
						'default'     => '',
						'custom_attributes' => array(
							'readonly' => 'readonly'
						),
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
					'body'    => wp_json_encode( array( // Encode body as JSON string
						'email'    => $email,
						'password' => $password,
					) ),
				) );

				if ( is_wp_error( $response ) ) {
					// Handle API error (e.g., display a notice)
					/* translators: %s: The error message from the Shiprocket API. */
					WC_Admin_Settings::add_error( sprintf( __( 'Shiprocket API Error: %s', 'shiprocket-woo-shipping' ), $response->get_error_message() ) );
					return false;
				}

				$body = json_decode( wp_remote_retrieve_body( $response ) );
				$code = wp_remote_retrieve_response_code( $response ); // Get the HTTP status code

				// Check if the status code is 403 (Forbidden)
				if ( $code == 403 ) {
					$error_message = __( 'Invalid Shiprocket email or password.', 'shiprocket-woo-shipping' );
					WC_Admin_Settings::add_error( $error_message );
					return false; 
				} else if ( ! isset( $body->token ) ) { // Check for other token generation errors
					$error_message = __( 'Failed to generate Shiprocket token.', 'shiprocket-woo-shipping' );
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
					$product_length = is_numeric($_product->get_length()) ? (float)$_product->get_length() : 0;
					$product_width = is_numeric($_product->get_width()) ? (float)$_product->get_width() : 0;
					$product_height = is_numeric($_product->get_height()) ? (float)$_product->get_height() : 0;
					
					$length += $product_length * $values['quantity'];
					$breadth += $product_width * $values['quantity'];
					$height += $product_height * $values['quantity'];
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
 * Enqueue custom CSS for cart, checkout, and product pages.
 */
function woo_shiprocket_enqueue_custom_css() {
    if ( is_cart() || is_checkout() || is_product() ) {
        wp_enqueue_style( 'woo-shiprocket-custom-css', plugins_url( '../css/woo-shiprocket-custom.css', __FILE__ ), array(), '1.0.0' );
        
        // Enqueue pincode check CSS on product pages
        if ( is_product() ) {
            wp_enqueue_style( 'woo-shiprocket-pincode-css', plugins_url( '../css/pincode-check.css', __FILE__ ), array(), '1.0.0' );
        }
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