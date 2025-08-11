<?php
/**
 * Plugin Updater Class for Shiprocket WooCommerce Shipping
 *
 * @package shiprocket-woo-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Shiprocket_Plugin_Updater {
    
    private $plugin_file;
    private $plugin_slug;
    private $version;
    private $update_url;
    
    public function __construct( $plugin_file ) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename( $plugin_file );
        $this->version = $this->get_plugin_version();
        $this->update_url = 'https://raw.githubusercontent.com/ProgrammerNomad/shiprocket-woo-shipping/main/update-info.json';
        
        // Hook into WordPress update system
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
        add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );
        
        // Add admin notice for updates
        add_action( 'admin_notices', array( $this, 'update_notice' ) );
    }
    
    /**
     * Get plugin version from plugin header.
     *
     * @return string Plugin version.
     */
    private function get_plugin_version() {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data( $this->plugin_file );
        return $plugin_data['Version'];
    }
    
    /**
     * Check for plugin updates.
     *
     * @param object $transient WordPress update transient.
     * @return object Modified transient.
     */
    public function check_for_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }
        
        // Get remote version info
        $remote_info = $this->get_remote_info();
        
        if ( $remote_info && version_compare( $this->version, $remote_info['version'], '<' ) ) {
            $transient->response[ $this->plugin_slug ] = (object) array(
                'slug'         => dirname( $this->plugin_slug ),
                'new_version'  => $remote_info['version'],
                'url'          => 'https://github.com/ProgrammerNomad/shiprocket-woo-shipping',
                'package'      => $remote_info['download_url'],
                'plugin'       => $this->plugin_slug,
                'tested'       => isset( $remote_info['tested'] ) ? $remote_info['tested'] : '6.6',
                'requires_php' => isset( $remote_info['requires_php'] ) ? $remote_info['requires_php'] : '7.4',
                'compatibility' => array(),
            );
        }
        
        return $transient;
    }
    
    /**
     * Get remote plugin information.
     *
     * @return array|false Remote plugin info or false on failure.
     */
    private function get_remote_info() {
        $cache_key = 'shiprocket_plugin_update_info';
        $cached_info = get_transient( $cache_key );
        
        if ( false !== $cached_info ) {
            return $cached_info;
        }
        
        $response = wp_remote_get( $this->update_url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            ),
        ) );
        
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            // Cache failure for shorter time to retry sooner
            set_transient( $cache_key, false, 1 * HOUR_IN_SECONDS );
            return false;
        }
        
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( ! $body || ! isset( $body['version'] ) ) {
            return false;
        }
        
        // Cache for 12 hours
        set_transient( $cache_key, $body, 12 * HOUR_IN_SECONDS );
        
        return $body;
    }
    
    /**
     * Provide plugin information for the update API.
     *
     * @param false|object $false
     * @param string $action
     * @param object $args
     * @return false|object
     */
    public function plugin_info( $false, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $false;
        }
        
        if ( ! isset( $args->slug ) || $args->slug !== dirname( $this->plugin_slug ) ) {
            return $false;
        }
        
        $remote_info = $this->get_remote_info();
        
        if ( ! $remote_info ) {
            return $false;
        }
        
        return (object) array(
            'name'              => 'Shiprocket WooCommerce Shipping',
            'slug'              => dirname( $this->plugin_slug ),
            'version'           => $remote_info['version'],
            'author'            => '<a href="https://github.com/ProgrammerNomad">Shiv Singh</a>',
            'homepage'          => 'https://github.com/ProgrammerNomad/shiprocket-woo-shipping',
            'short_description' => 'Modern, secure integration with Shiprocket\'s official API for real-time shipping rates and delivery estimates.',
            'sections'          => array(
                'description' => $this->get_plugin_description( $remote_info ),
                'changelog'   => $this->get_plugin_changelog( $remote_info ),
                'installation' => $this->get_installation_instructions(),
                'faq' => $this->get_faq_section(),
            ),
            'download_link'     => $remote_info['download_url'],
            'requires'          => isset( $remote_info['requires'] ) ? $remote_info['requires'] : '5.0',
            'tested'            => isset( $remote_info['tested'] ) ? $remote_info['tested'] : '6.6',
            'requires_php'      => isset( $remote_info['requires_php'] ) ? $remote_info['requires_php'] : '7.4',
            'last_updated'      => date( 'Y-m-d H:i:s' ),
            'added'             => '2024-01-01',
            'banners'           => array(),
            'icons'             => array(),
            'contributors'      => array( 'programmernomad' => 'https://github.com/ProgrammerNomad' ),
        );
    }
    
    /**
     * Get plugin description for popup.
     *
     * @param array $remote_info Remote plugin info.
     * @return string Plugin description.
     */
    private function get_plugin_description( $remote_info ) {
        $description = isset( $remote_info['sections']['description'] ) ? $remote_info['sections']['description'] : '';
        
        if ( empty( $description ) ) {
            $description = '<h3>🚀 Modern Shiprocket Integration for WooCommerce</h3>
            <p>Transform your WooCommerce store with modern Shiprocket integration! This plugin uses the <strong>official Shiprocket API</strong> with secure authentication to deliver real-time shipping rates and seamless customer experience.</p>
            
            <h4>✨ Key Features:</h4>
            <ul>
                <li><strong>Real-time Shipping Rates:</strong> Live rates calculated based on weight, dimensions, and distance</li>
                <li><strong>Smart Pickup Detection:</strong> Automatically uses your WooCommerce store address</li>
                <li><strong>Pincode Serviceability:</strong> Customers can check delivery options on product pages</li>
                <li><strong>API Key Authentication:</strong> Secure, official Shiprocket API integration</li>
                <li><strong>Intelligent Caching:</strong> Performance optimized with configurable caching</li>
                <li><strong>Enhanced User Experience:</strong> Comprehensive setup guide and plugin action links</li>
            </ul>
            
            <h4>🛡️ Security & Performance:</h4>
            <ul>
                <li>No password storage - uses secure API keys</li>
                <li>Production-ready architecture</li>
                <li>WordPress coding standards compliant</li>
                <li>Enhanced error handling and validation</li>
            </ul>';
        }
        
        return $description;
    }
    
    /**
     * Get plugin changelog for popup.
     *
     * @param array $remote_info Remote plugin info.
     * @return string Plugin changelog.
     */
    private function get_plugin_changelog( $remote_info ) {
        return isset( $remote_info['sections']['changelog'] ) ? $remote_info['sections']['changelog'] : 'Latest updates and improvements.';
    }
    
    /**
     * Get installation instructions.
     *
     * @return string Installation instructions.
     */
    private function get_installation_instructions() {
        return '<ol>
            <li><strong>Download and Install:</strong> Upload the plugin ZIP file through WordPress admin</li>
            <li><strong>Get API Key:</strong> Login to your <a href="https://app.shiprocket.in/dashboard" target="_blank">Shiprocket Dashboard</a> → Settings → API</li>
            <li><strong>Configure:</strong> Go to WooCommerce → Settings → Shipping → Shiprocket</li>
            <li><strong>Enter API Key:</strong> Paste your API key and save (plugin validates automatically)</li>
            <li><strong>Test:</strong> Add products to cart and verify shipping rates appear at checkout</li>
        </ol>';
    }
    
    /**
     * Get FAQ section.
     *
     * @return string FAQ content.
     */
    private function get_faq_section() {
        return '<dl>
            <dt><strong>How do I get my Shiprocket API key?</strong></dt>
            <dd>Login to your Shiprocket dashboard, go to Settings → API, and copy your API key.</dd>
            
            <dt><strong>Does this work with existing shipping methods?</strong></dt>
            <dd>Yes! This plugin adds Shiprocket as an additional shipping method alongside your existing ones.</dd>
            
            <dt><strong>Is it secure?</strong></dt>
            <dd>Absolutely! Uses official API key authentication with no password storage and follows WordPress security best practices.</dd>
            
            <dt><strong>Can customers check delivery before ordering?</strong></dt>
            <dd>Yes! Enable the pincode check feature and customers can verify delivery options on product pages.</dd>
            
            <dt><strong>Need help?</strong></dt>
            <dd>Visit our <a href="https://github.com/ProgrammerNomad/shiprocket-woo-shipping/issues" target="_blank">GitHub repository</a> for support and documentation.</dd>
        </dl>';
    }
    
    /**
     * Show admin notice for available updates.
     */
    public function update_notice() {
        // Only show on relevant admin pages
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->id, array( 'plugins', 'update-core' ), true ) ) {
            return;
        }
        
        // Check if update is available
        $remote_info = $this->get_remote_info();
        if ( ! $remote_info || ! version_compare( $this->version, $remote_info['version'], '<' ) ) {
            return;
        }
        
        // Don't show if WordPress is already showing update
        $update_plugins = get_site_transient( 'update_plugins' );
        if ( isset( $update_plugins->response[ $this->plugin_slug ] ) ) {
            return;
        }
        
        $update_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $this->plugin_slug ), 'upgrade-plugin_' . $this->plugin_slug );
        
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Shiprocket WooCommerce Shipping:</strong> ';
        echo sprintf( 
            __( 'Version %s is available! <a href="%s" class="button-primary">Update Now</a> or <a href="%s" target="_blank">view details</a>.', 'shiprocket-woo-shipping' ),
            esc_html( $remote_info['version'] ),
            esc_url( $update_url ),
            esc_url( 'https://github.com/ProgrammerNomad/shiprocket-woo-shipping/releases/tag/v' . $remote_info['version'] )
        );
        echo '</p>';
        echo '</div>';
    }
    
    /**
     * Clear update cache after plugin update.
     *
     * @param object $upgrader
     * @param array $hook_extra
     */
    public function clear_cache( $upgrader, $hook_extra ) {
        if ( isset( $hook_extra['plugins'] ) && is_array( $hook_extra['plugins'] ) ) {
            if ( in_array( $this->plugin_slug, $hook_extra['plugins'], true ) ) {
                delete_transient( 'shiprocket_plugin_update_info' );
                
                // Also clear WordPress plugin update cache
                delete_site_transient( 'update_plugins' );
            }
        }
    }
    
    /**
     * Force check for updates (useful for testing).
     */
    public function force_update_check() {
        delete_transient( 'shiprocket_plugin_update_info' );
        delete_site_transient( 'update_plugins' );
        
        // Trigger update check
        wp_update_plugins();
    }
}
