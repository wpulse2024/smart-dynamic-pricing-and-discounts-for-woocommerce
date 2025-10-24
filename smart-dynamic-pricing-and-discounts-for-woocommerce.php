<?php
/**
 * Plugin Name: Smart Dynamic Pricing And Discounts For Woocommerce
 * Description: A powerful WooCommerce addon to create smart dynamic pricing, discounts, and tiered pricing rules with an intuitive Vue 3 admin interface.
 * Version: 1.0.0
 * Author: WPulse
 * Author URI: https://profiles.wordpress.org/wpulse/
 * Text Domain: smart-dynamic-pricing-and-discounts-for-woocommerce
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
define('SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_VERSION', '1.0.0');
define('SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_FILE', __FILE__);
define('SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH', plugin_dir_path(__FILE__));
define('SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_URL', plugin_dir_url(__FILE__));
define('SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_BASENAME', plugin_basename(__FILE__));

// Autoload dependencies
if (file_exists(SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'vendor/autoload.php')) {
    require_once SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'vendor/autoload.php';
}

// Bootstrap the plugin
use SmartDynamicPricingDiscounts\Core\Plugin;

/**
 * Initialize the plugin
 */
function my_plugin_init() {
    return Plugin::getInstance();
}

// Initialize the plugin
$GLOBALS['my_plugin'] = my_plugin_init();

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    require_once SMART_DYNAMIC_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'includes/cli/RenameCommand.php';
    \WP_CLI::add_command('myplugin', 'MyPlugin\\CLI\\RenameCommand');
}

// Activation and deactivation hooks
register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);
