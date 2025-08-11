=== Shiprocket WooCommerce Shipping ===
Contributors: Shiv Singh
Donate link: https://github.com/ProgrammerNomad
Tags: shipping, shiprocket, woocommerce, courier, delivery, logistics, india, pincode, real-time rates
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Modern, secure integration with Shiprocket's official API for real-time shipping rates and delivery estimates in your WooCommerce store.

== Description ==

Transform your WooCommerce store with modern Shiprocket integration! This plugin uses the **official Shiprocket API** with secure API User authentication to deliver real-time shipping rates and seamless customer experience.

**🚀 Why Choose This Plugin?**

* **Official API Integration**: Uses Shiprocket's official API User authentication method
* **Production Ready**: Built following official API documentation and security best practices
* **Auto-Configuration**: Smart pickup location detection from your store settings
* **Performance Optimized**: Bearer token caching reduces loading times
* **Customer Focused**: Smooth checkout experience with accurate rates

**⚡ Key Features:**

* **Real-time Shipping Rates**: Live rates calculated based on weight, dimensions, and distance
* **Smart Pickup Detection**: Automatically uses your WooCommerce store address
* **Pincode Serviceability**: Customers can check delivery options on product pages
* **Same-Day Delivery Detection**: Highlights fast delivery options when available
* **Memory Smart**: Remembers customer pincode for seamless experience
* **Top Courier Filtering**: Optional display of only top-rated courier services
* **Intelligent Caching**: Configurable caching improves performance
* **API Validation**: Automatic API key verification on settings save

**🛡️ Security & Performance:**

* Official API User authentication with Bearer token system
* Automatic token refresh and 23-hour caching
* Enhanced error handling and logging
* Input sanitization throughout
* Production-ready architecture
* WordPress coding standards compliant

== Screenshots ==

1. **WooCommerce Settings:** (Screenshot of the Shiprocket Shipping settings page in WooCommerce)
2. **Pincode Check on Product Page:** (Screenshot of the pincode serviceability check on a product page)
3. **Dynamic Shipping Methods on Checkout:** (Screenshot of the checkout page showing dynamic shipping methods)

== Installation ==

**Quick Setup (5 minutes):**

1. **Install Plugin:**
   * Download from [GitHub Releases](https://github.com/ProgrammerNomad/shiprocket-woo-shipping/releases)
   * Upload via WordPress admin: `Plugins → Add New → Upload Plugin`
   * Activate the plugin

2. **Create API User:**
   * Login to your [Shiprocket Dashboard](https://shiprocket.in/)
   * Navigate to `Settings → API User`
   * Click "Create New API User"
   * Fill in the required details and save
   * Note down the **API User Email** and **Password**

3. **Configure Settings:**
   * Go to `WooCommerce → Settings → Shipping → Shiprocket`
   * Enter your **API User Email** and **API User Password**
   * **Pickup Postcode** auto-fills from your store address
   * Adjust other settings as needed
   * Save (plugin validates credentials automatically)

4. **Test Integration:**
   * Add products to cart and test checkout
   * Verify shipping rates display correctly
   * Test pincode check on product pages

**That's it! Your store now has live Shiprocket integration.**

== Frequently Asked Questions ==

= How do I get my Shiprocket API credentials? =
Login to your Shiprocket dashboard, go to Settings → API User, create a new API User, and note down the email and password. Enter these in the plugin settings.

= Will this work with my existing shipping methods? =
Yes! This plugin adds Shiprocket as an additional shipping method. Your existing methods will continue to work.

= Does it support Cash on Delivery (COD)? =
Yes, the plugin supports both prepaid and COD orders with accurate rate calculations.

= What if my pincode is not serviceable? =
The plugin will show appropriate messages to customers when their pincode is not serviceable by Shiprocket.

= Can I customize which couriers are shown? =
Yes! You can choose to show all available couriers or only the top 5 rated ones for better customer experience.

= Does it cache shipping rates for better performance? =
Absolutely! The plugin includes intelligent caching (default 10 minutes) to improve loading speeds and reduce API calls.

= Are my API credentials secure? =
Yes, the plugin uses official API User authentication with Bearer tokens (no API key storage) and follows WordPress security best practices. Tokens are automatically refreshed and cached securely.

= Can customers check delivery time before adding to cart? =
Yes! Enable the pincode check feature, and customers can verify delivery options directly on product pages.

= What if I need support? =
Visit our [GitHub repository](https://github.com/ProgrammerNomad/shiprocket-woo-shipping) to report issues or get help from the community.

== Support ==

If you have any questions or need assistance, please [open an issue on GitHub](https://github.com/ProgrammerNomad/shiprocket-woo-shipping/issues).

== Contributing ==

Contributions are welcome! Feel free to fork the [repository](https://github.com/ProgrammerNomad/shiprocket-woo-shipping) and submit pull requests.

== Changelog ==

= 1.0.7 =
**Official API Compliance Update**
* MAJOR: Complete implementation of official Shiprocket API User authentication
* NEW: API User authentication system (replaces deprecated API key method)
* NEW: Bearer token authentication following official API documentation
* NEW: Automatic token refresh and 23-hour caching system
* NEW: Comprehensive API User creation guide in plugin settings
* SECURITY: Enhanced authentication security with official Shiprocket methods
* IMPROVEMENT: Updated all API calls to use Bearer token authentication
* MIGRATION: Seamless transition from old API key to new API User method
* DOCUMENTATION: Updated readme and help sections for official API usage

= 1.0.6 =
**WordPress Native Update System**
* NEW: WordPress native update checker for automatic plugin updates
* NEW: Rich plugin information display in WordPress admin
* NEW: Enhanced GitHub Actions automation for seamless releases
* IMPROVEMENT: Complete integration with WordPress update system
* IMPROVEMENT: Automatic update notifications in WordPress admin
* ENHANCEMENT: Comprehensive update information with changelogs
* FEATURE: Automatic version synchronization between GitHub and WordPress
* OPTIMIZATION: Improved release workflow with better error handling

= 1.0.5 =
**UX Enhancement Update**
* NEW: Comprehensive API setup guide directly on settings page
* NEW: Plugin action links for quick access to Settings, Documentation, and Support
* NEW: Plugin meta links for Changelog, Shiprocket Dashboard, and GitHub rating
* IMPROVEMENT: Enhanced settings page with step-by-step API key instructions
* IMPROVEMENT: Better user onboarding with visual help sections
* ENHANCEMENT: Mobile-responsive help section design
* FEATURE: Direct links to Shiprocket Dashboard and GitHub resources
* OPTIMIZATION: Improved admin interface styling and user experience

= 1.0.4 =
**Major Update: Modern API Integration**
* NEW: Official Shiprocket API key authentication (replaces email/password method)
* SECURITY: Enhanced input validation and error handling throughout
* PERFORMANCE: Added intelligent caching system with configurable duration
* FEATURE: Auto-pickup location detection from WooCommerce store settings
* IMPROVEMENT: API key validation on settings save with user feedback
* ENHANCEMENT: Better courier selection with improved performance sorting
* OPTIMIZATION: Reduced API calls and improved loading speeds
* COMPATIBILITY: Updated for WordPress 6.6 and WooCommerce 8.0+

= 1.0.3 =
* Updated plugin features and bug fixes
* Improved shipping rate calculations
* Enhanced error handling

= 1.0.2 =
* Minor bug fixes and improvements
* Better compatibility with WooCommerce updates

= 1.0.1 =
* Fixed issues with pincode validation
* Improved user interface

= 1.0.0 =
* Initial release with basic Shiprocket integration
* Real-time shipping rates
* Pincode serviceability check
* Basic courier selection