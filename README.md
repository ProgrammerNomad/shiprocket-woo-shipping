# Shiprocket WooCommerce Shipping Plugin

A modern, secure WooCommerce shipping plugin that integrates seamlessly with Shiprocket's official API to provide real-time shipping rates and delivery estimates.

## 🚀 **Official API Integration**

This plugin uses the **official Shiprocket API** with secure API User authentication, following Shiprocket's official documentation and current best practices for production WordPress plugins.

## ✨ **Key Features**

* **🔐 Secure API Integration:** Uses official Shiprocket API User authentication (Bearer token)
* **📦 Real-time Shipping Rates:** Live rates based on weight, dimensions, and distance
* **📍 Smart Pickup Location:** Automatically uses your WooCommerce store address
* **⚡ Performance Optimized:** Intelligent caching system reduces API calls
* **🎯 Pincode Serviceability:** Customers can check delivery options on product pages
* **💾 Smart Memory:** Remembers customer pincode using localStorage
* **🚚 Delivery Intelligence:** Shows same-day delivery and estimated delivery times
* **🏆 Top Courier Selection:** Optional filtering to show only top-rated couriers
* **🔧 Easy Setup:** Automatic validation and configuration

## 🛡️ **Security & Performance**

* Official API User authentication with Bearer tokens
* Automatic token refresh and caching (23 hours)
* Enhanced error handling and logging
* Intelligent caching (configurable duration)
* Input sanitization throughout
* Production-ready architecture

## 📋 **Requirements**

* WordPress 5.0+
* WooCommerce 3.0+
* PHP 7.4+
* Active Shiprocket account with API access

## 🔧 **Installation**

1. **Download & Install:**
   - Download the latest release ZIP from [GitHub Releases](https://github.com/ProgrammerNomad/shiprocket-woo-shipping/releases)
   - Upload to WordPress: `Plugins → Add New → Upload Plugin`
   - Activate the plugin

2. **Create API User:**
   - Login to your [Shiprocket Dashboard](https://shiprocket.in/)
   - Navigate to `Settings → API User`
   - Click "Create New API User"
   - Fill in the required details and save
   - Note down the **API User Email** and **Password**

3. **Configure Plugin:**
   - Go to `WooCommerce → Settings → Shipping → Shiprocket`
   - Enter your **API User Email** and **API User Password**
   - **Pickup Postcode** auto-fills from your store address
   - Configure other options as needed
   - Save settings (plugin validates credentials automatically)

4. **Test Integration:**
   - Add products to cart and test checkout
   - Verify shipping rates appear correctly
   - Test pincode check on product pages

## ⚙️ **Configuration Options**

| Setting | Description | Default |
|---------|-------------|---------|
| **API User Email** | Your Shiprocket API User email | Required |
| **API User Password** | Your Shiprocket API User password | Required |
| **Pickup Postcode** | Auto-filled from store address | Auto-detected |
| **Pincode Check** | Enable pincode verification on products | Disabled |
| **Top Couriers Only** | Show only top 5 rated couriers | Enabled |
| **Cache Duration** | How long to cache rates (minutes) | 10 minutes |

## 🎯 **Core Features**

### 1. **Product Page Delivery Check**
- Customers enter pincode to check serviceability
- Shows same-day delivery availability
- Displays estimated delivery time
- Remembers pincode for future visits

### 2. **Checkout Shipping Rates**
- Real-time rates based on cart weight and dimensions
- Intelligent courier selection
- Performance-optimized with caching
- Dynamic updates based on customer location

## 🤝 **Support & Contributing**

- **Issues:** [Report bugs or request features](https://github.com/ProgrammerNomad/shiprocket-woo-shipping/issues)
- **Discussions:** [Join community discussions](https://github.com/ProgrammerNomad/shiprocket-woo-shipping/discussions)
- **Contributing:** Fork the repository and submit pull requests
- **Documentation:** [Wiki & Guides](https://github.com/ProgrammerNomad/shiprocket-woo-shipping/wiki)

## 📝 **Changelog**

### v1.0.7 (Latest)
* 🔥 **MAJOR:** Official Shiprocket API compliance implementation
* 🔐 **NEW:** API User authentication (replaces deprecated API key method)
* 🎯 **NEW:** Bearer token authentication following official API documentation
* ⚡ **NEW:** Automatic token refresh and 23-hour caching system
* 📚 **NEW:** Comprehensive API User creation guide in settings
* 🛡️ **SECURITY:** Enhanced authentication security with official methods
* 📖 **IMPROVEMENT:** Updated documentation to reflect official API usage
* 🔄 **MIGRATION:** Seamless transition from old API key to new API User method

### v1.0.6
* ✨ **NEW:** WordPress native update checker for automatic plugin updates
* 🔄 **NEW:** Rich plugin information display in WordPress admin
* 🤖 **NEW:** Enhanced GitHub Actions automation for seamless releases
* 🔗 **IMPROVEMENT:** Complete integration with WordPress update system
* 📢 **IMPROVEMENT:** Automatic update notifications in WordPress admin
* 📋 **ENHANCEMENT:** Comprehensive update information with changelogs
* ⚙️ **FEATURE:** Automatic version synchronization between GitHub and WordPress
* 🚀 **OPTIMIZATION:** Improved release workflow with better error handling

### v1.0.5
* ✨ **NEW:** Comprehensive API setup guide on settings page
* 🔗 **NEW:** Plugin action links for quick access to Settings, Documentation, Support
* 📋 **NEW:** Plugin meta links for Changelog, Shiprocket Dashboard, GitHub rating
* 🎨 **IMPROVEMENT:** Enhanced settings page with step-by-step instructions
* 📱 **IMPROVEMENT:** Mobile-responsive help section design
* 🚀 **ENHANCEMENT:** Better user onboarding and admin interface

### v1.0.4
* ✨ **NEW:** Modern API key authentication (replaces email/password)
* 🔐 **Security:** Enhanced input validation and error handling
* 🚀 **Performance:** Added intelligent caching system
* 📍 **Smart:** Auto-pickup location from WooCommerce store settings
* 🛡️ **Validation:** API key verification on settings save
* 📊 **Improved:** Better courier selection and performance sorting

### v1.0.3
* Updated plugin features and bug fixes
* Improved shipping rate calculations

### v1.0.0
* Initial release with basic Shiprocket integration

## 📄 **License**

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 🔗 **Links**

* **Plugin Repository:** [GitHub](https://github.com/ProgrammerNomad/shiprocket-woo-shipping)
* **Latest Release:** [Download](https://github.com/ProgrammerNomad/shiprocket-woo-shipping/releases/latest)
* **Shiprocket Dashboard:** [Login](https://app.shiprocket.in/dashboard)
* **WordPress Plugin Directory:** Coming Soon

---

**Made with ❤️ for the WooCommerce community**
