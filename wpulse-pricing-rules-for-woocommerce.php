<?php
/**
 * Plugin Name: WPulse Pricing Rules for WooCommerce
 * Description: A powerful WooCommerce addon to create smart dynamic pricing, discounts, and tiered pricing rules with an intuitive Vue 3 admin interface.
 * Version: 1.1.1
 * Author: WPulse
 * Author URI: https://profiles.wordpress.org/wpulse/
 * Text Domain: wpulse-pricing-rules-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * Requires Plugins: woocommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPULSE_PRICING_RULES_VERSION', '1.1.1');
define('WPULSE_PRICING_RULES_FILE', __FILE__);
define('WPULSE_PRICING_RULES_PATH', plugin_dir_path(__FILE__));
define('WPULSE_PRICING_RULES_URL', plugin_dir_url(__FILE__));
define('WPULSE_PRICING_RULES_BASENAME', plugin_basename(__FILE__));

// Autoload dependencies
if (file_exists(WPULSE_PRICING_RULES_PATH . 'vendor/autoload.php')) {
    require_once WPULSE_PRICING_RULES_PATH . 'vendor/autoload.php';
}

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

// Bootstrap the plugin
use WpulsePricingRules\Core\Plugin;

/**
 * Initialize the plugin
 */
function wpulse_pricing_rules_init() {
    return Plugin::getInstance();
}

// Initialize the plugin
$GLOBALS['wpulse-pricing-rules-for-woocommerce'] = wpulse_pricing_rules_init();

// Activation and deactivation hooks
register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);
