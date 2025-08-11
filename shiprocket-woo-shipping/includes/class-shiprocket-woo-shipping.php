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
					   'api_user_email' => array(
						   'title'       => __( 'Shiprocket API User Email', 'shiprocket-woo-shipping' ),
						   'type'        => 'text',
						   'description' => __( 'Enter your Shiprocket API User email (created from Settings → API → Add New API User).', 'shiprocket-woo-shipping' ),
						   'default'     => '',
						   'desc_tip'    => true,
					   ),
					   'api_user_password' => array(
						   'title'       => __( 'Shiprocket API User Password', 'shiprocket-woo-shipping' ),
						   'type'        => 'password',
						   'description' => __( 'Enter your Shiprocket API User password (sent to your registered email after creating API user).', 'shiprocket-woo-shipping' ),
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
			 * Generate settings HTML including help information.
			 *
			 * @return void
			 */
			public function generate_settings_html( $form_fields = array(), $echo = true ) {
				// Generate the standard settings form
				$html = parent::generate_settings_html( $form_fields, false );
				
				// Add help information after the form
				$html .= $this->get_api_help_section();
				
				if ( $echo ) {
					echo $html;
				}
				
				return $html;
			}

			/**
			 * Get API help section HTML.
			 *
			 * @return string Help section HTML.
			 */
			private function get_api_help_section() {
				ob_start();
				?>
				<div class="shiprocket-api-help" style="margin-top: 20px; padding: 20px; background: #f8f9fa; border: 1px solid #e1e5e9; border-radius: 6px;">
					<h3 style="margin-top: 0; color: #1d2327;">🚀 <?php _e( 'Getting Your Shiprocket API Key', 'shiprocket-woo-shipping' ); ?></h3>
					
					<div style="display: grid; gap: 15px;">
						<div>
							<h4 style="margin: 0 0 8px 0; color: #135e96;">📋 <?php _e( 'Step-by-Step Guide:', 'shiprocket-woo-shipping' ); ?></h4>
							<ol style="margin: 8px 0 0 20px;">
								<li><?php _e( 'Login to your', 'shiprocket-woo-shipping' ); ?> <a href="https://app.shiprocket.in/dashboard" target="_blank" style="color: #2271b1; text-decoration: none;"><?php _e( 'Shiprocket Dashboard', 'shiprocket-woo-shipping' ); ?> ↗</a></li>
								<li><?php _e( 'Navigate to', 'shiprocket-woo-shipping' ); ?> <strong><?php _e( 'Settings → API', 'shiprocket-woo-shipping' ); ?></strong></li>
								<li><?php _e( 'Copy your', 'shiprocket-woo-shipping' ); ?> <strong><?php _e( 'API Key', 'shiprocket-woo-shipping' ); ?></strong> <?php _e( 'from the API section', 'shiprocket-woo-shipping' ); ?></li>
								<li><?php _e( 'Paste it in the', 'shiprocket-woo-shipping' ); ?> <strong><?php _e( 'Shiprocket API Key', 'shiprocket-woo-shipping' ); ?></strong> <?php _e( 'field above', 'shiprocket-woo-shipping' ); ?></li>
								<li><?php _e( 'Click', 'shiprocket-woo-shipping' ); ?> <strong><?php _e( 'Save changes', 'shiprocket-woo-shipping' ); ?></strong> <?php _e( '(plugin will validate your API key automatically)', 'shiprocket-woo-shipping' ); ?></li>
							</ol>
						</div>

						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
							<div style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
								<h4 style="margin: 0 0 8px 0; color: #d63384;">🚨 <?php _e( 'Important Notes:', 'shiprocket-woo-shipping' ); ?></h4>
								<ul style="margin: 8px 0 0 20px; font-size: 14px;">
									<li><?php _e( 'You need an active Shiprocket account', 'shiprocket-woo-shipping' ); ?></li>
									<li><?php _e( 'API key replaces email/password authentication', 'shiprocket-woo-shipping' ); ?></li>
									<li><?php _e( 'Keep your API key secure and private', 'shiprocket-woo-shipping' ); ?></li>
									<li><?php _e( 'Plugin validates API key when you save settings', 'shiprocket-woo-shipping' ); ?></li>
								</ul>
							</div>

							<div style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
								<h4 style="margin: 0 0 8px 0; color: #198754;">✅ <?php _e( 'Features Enabled:', 'shiprocket-woo-shipping' ); ?></h4>
								<ul style="margin: 8px 0 0 20px; font-size: 14px;">
									<li><?php _e( 'Real-time shipping rates at checkout', 'shiprocket-woo-shipping' ); ?></li>
									<li><?php _e( 'Pincode serviceability check on products', 'shiprocket-woo-shipping' ); ?></li>
									<li><?php _e( 'Auto-pickup location from store settings', 'shiprocket-woo-shipping' ); ?></li>
									<li><?php _e( 'Intelligent caching for better performance', 'shiprocket-woo-shipping' ); ?></li>
								</ul>
							</div>
						</div>

						<div style="padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin-top: 10px;">
							<h4 style="margin: 0 0 8px 0; color: #856404;">💡 <?php _e( 'Need Help?', 'shiprocket-woo-shipping' ); ?></h4>
							<p style="margin: 8px 0; font-size: 14px;">
								<?php _e( 'Having trouble? Check our', 'shiprocket-woo-shipping' ); ?>
								<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping/wiki" target="_blank" style="color: #856404; font-weight: 600;"><?php _e( 'Documentation', 'shiprocket-woo-shipping' ); ?> ↗</a>
								<?php _e( 'or', 'shiprocket-woo-shipping' ); ?>
								<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping/issues" target="_blank" style="color: #856404; font-weight: 600;"><?php _e( 'Report an Issue', 'shiprocket-woo-shipping' ); ?> ↗</a>
							</p>
						</div>

						<div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
							<p style="margin: 0; font-size: 14px; color: #6c757d;">
								<?php _e( 'Made with', 'shiprocket-woo-shipping' ); ?> ❤️ <?php _e( 'for the WooCommerce community', 'shiprocket-woo-shipping' ); ?> |
								<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping" target="_blank" style="color: #6c757d;"><?php _e( 'View on GitHub', 'shiprocket-woo-shipping' ); ?> ↗</a>
							</p>
						</div>
					</div>
				</div>
				<?php
				return ob_get_clean();
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
			return parent::process_admin_options();
		}

		/**
		 * Display help section after settings.
		 */
		public function admin_options() {
			parent::admin_options();
			?>
			<div class="shiprocket-api-help" style="margin-top: 20px; padding: 20px; background: #f8f9fa; border: 1px solid #e1e5e9; border-radius: 6px;">
				<h3 style="margin-top: 0; color: #1d2327;">🚀 <?php _e( 'Setting Up Shiprocket API User', 'shiprocket-woo-shipping' ); ?></h3>
				
				<div style="display: grid; gap: 15px;">
					<div>
						<h4 style="margin: 0 0 8px 0; color: #135e96;">📋 <?php _e( 'Step-by-Step API User Creation:', 'shiprocket-woo-shipping' ); ?></h4>
						<ol style="margin: 8px 0 0 20px;">
							<li><?php _e( 'Login to your', 'shiprocket-woo-shipping' ); ?> <a href="https://app.shiprocket.in/dashboard" target="_blank" style="color: #2271b1; text-decoration: none;"><?php _e( 'Shiprocket Dashboard', 'shiprocket-woo-shipping' ); ?> ↗</a></li>
							<li><?php _e( 'From the left-hand menu, go to:', 'shiprocket-woo-shipping' ); ?> <strong><?php _e( 'Settings → API → Add New API User', 'shiprocket-woo-shipping' ); ?></strong></li>
							<li><?php _e( 'Click on', 'shiprocket-woo-shipping' ); ?> <strong><?php _e( '"Create API User"', 'shiprocket-woo-shipping' ); ?></strong></li>
							<li><?php _e( 'Enter a unique email address (different from your main Shiprocket login)', 'shiprocket-woo-shipping' ); ?></li>
							<li><?php _e( 'Select the relevant API modules you want to access', 'shiprocket-woo-shipping' ); ?></li>
							<li><?php _e( 'Click', 'shiprocket-woo-shipping' ); ?> <strong><?php _e( '"Create User"', 'shiprocket-woo-shipping' ); ?></strong></li>
							<li><?php _e( 'The password will be sent to your registered email address', 'shiprocket-woo-shipping' ); ?></li>
							<li><?php _e( 'Paste the API user email and password in the fields above', 'shiprocket-woo-shipping' ); ?></li>
							<li><?php _e( 'Click', 'shiprocket-woo-shipping' ); ?> <strong><?php _e( 'Save changes', 'shiprocket-woo-shipping' ); ?></strong> <?php _e( '(plugin will validate your credentials automatically)', 'shiprocket-woo-shipping' ); ?></li>
						</ol>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
						<div style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
							<h4 style="margin: 0 0 8px 0; color: #d63384;">🚨 <?php _e( 'Important Notes:', 'shiprocket-woo-shipping' ); ?></h4>
							<ul style="margin: 8px 0 0 20px; font-size: 14px;">
								<li><?php _e( 'You need an active Shiprocket account', 'shiprocket-woo-shipping' ); ?></li>
								<li><?php _e( 'API User email must be different from your main login', 'shiprocket-woo-shipping' ); ?></li>
								<li><?php _e( 'Password is sent to your registered email (not API user email)', 'shiprocket-woo-shipping' ); ?></li>
								<li><?php _e( 'Keep your API credentials secure and private', 'shiprocket-woo-shipping' ); ?></li>
							</ul>
						</div>

						<div style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
							<h4 style="margin: 0 0 8px 0; color: #198754;">✅ <?php _e( 'Features Enabled:', 'shiprocket-woo-shipping' ); ?></h4>
							<ul style="margin: 8px 0 0 20px; font-size: 14px;">
								<li><?php _e( 'Real-time shipping rates at checkout', 'shiprocket-woo-shipping' ); ?></li>
								<li><?php _e( 'Pincode serviceability check on products', 'shiprocket-woo-shipping' ); ?></li>
								<li><?php _e( 'Auto-pickup location from store settings', 'shiprocket-woo-shipping' ); ?></li>
								<li><?php _e( 'Intelligent caching for better performance', 'shiprocket-woo-shipping' ); ?></li>
							</ul>
						</div>
					</div>

					<div style="padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin-top: 10px;">
						<h4 style="margin: 0 0 8px 0; color: #856404;">💡 <?php _e( 'Need Help?', 'shiprocket-woo-shipping' ); ?></h4>
						<p style="margin: 8px 0; font-size: 14px;">
							<?php _e( 'Having trouble? Check our', 'shiprocket-woo-shipping' ); ?>
							<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping/wiki" target="_blank" style="color: #856404; font-weight: 600;"><?php _e( 'Documentation', 'shiprocket-woo-shipping' ); ?> ↗</a>
							<?php _e( 'or', 'shiprocket-woo-shipping' ); ?>
							<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping/issues" target="_blank" style="color: #856404; font-weight: 600;"><?php _e( 'Report an Issue', 'shiprocket-woo-shipping' ); ?> ↗</a>
							<?php _e( '| Official API Docs:', 'shiprocket-woo-shipping' ); ?>
							<a href="https://apidocs.shiprocket.in" target="_blank" style="color: #856404; font-weight: 600;"><?php _e( 'Shiprocket API', 'shiprocket-woo-shipping' ); ?> ↗</a>
						</p>
					</div>
					<div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
						<p style="margin: 0; font-size: 14px; color: #6c757d;">
							<?php _e( 'Made with', 'shiprocket-woo-shipping' ); ?> ❤️ <?php _e( 'for the WooCommerce community', 'shiprocket-woo-shipping' ); ?> |
							<a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping" target="_blank" style="color: #6c757d;"><?php _e( 'View on GitHub', 'shiprocket-woo-shipping' ); ?> ↗</a>
						</p>
					</div>
				</div>
			</div>
			<?php
		}		   /**
			* Calculate shipping rates.
			*
			* @param array $package Package information.
			*/
		   public function calculate_shipping( $package = array() ) {
			   $destination_postcode = $package['destination']['postcode'];
			   $weight = 0;
			   $total_amount = 0;

		   // Calculate total weight and amount
		   foreach ( $package['contents'] as $item_id => $values ) {
			   $product = $values['data'];
			   $product_weight = floatval( $product->get_weight() ?: 0 );
			   $product_price = floatval( $product->get_price() ?: 0 );
			   $quantity = intval( $values['quantity'] ?: 1 );
			   
			   $weight += $product_weight * $quantity;
			   $total_amount += $product_price * $quantity;
		   }

		   // Get dimensions
		   $dimensions = $this->GetLengthBreadthHeight( $package );

		   // Ensure minimum weight for API compatibility
		   if ( $weight <= 0 ) {
			   $weight = 0.1; // Set minimum weight of 100 grams
		   }

		   // Get shipping rates
		   $rates = woo_shiprocket_get_rates( $destination_postcode, $weight, $dimensions, $total_amount );

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
					$product_length = floatval( $_product->get_length() ?: 0 );
					$product_width = floatval( $_product->get_width() ?: 0 );
					$product_height = floatval( $_product->get_height() ?: 0 );
					$quantity = intval( $values['quantity'] ?: 1 );
					
					$length += $product_length * $quantity;
					$breadth += $product_width * $quantity;
					$height += $product_height * $quantity;
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
 * Enqueue admin styles for settings page.
 */
function shiprocket_woo_shipping_admin_styles( $hook ) {
    // Only load on WooCommerce settings pages
    if ( $hook === 'woocommerce_page_wc-settings' && isset( $_GET['section'] ) && $_GET['section'] === 'woo_shiprocket_shipping' ) {
        // Add some inline CSS for better styling
        $css = '
        <style>
        .shiprocket-api-help h3 { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        .shiprocket-api-help h4 { 
            display: flex; 
            align-items: center; 
            gap: 6px; 
        }
        .shiprocket-api-help a {
            text-decoration: none;
            font-weight: 500;
        }
        .shiprocket-api-help a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .shiprocket-api-help > div > div:nth-child(2) {
                grid-template-columns: 1fr;
            }
        }
        </style>';
        echo $css;
    }
}
add_action( 'admin_head', 'shiprocket_woo_shipping_admin_styles' );

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