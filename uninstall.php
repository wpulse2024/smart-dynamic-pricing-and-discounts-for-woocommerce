<?php
/**
 * Uninstall script for My Plugin
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include the plugin's uninstall method
if (file_exists(plugin_dir_path(__FILE__) . 'app/Core/Plugin.php')) {
    require_once plugin_dir_path(__FILE__) . 'app/Core/Plugin.php';
    \SmartPricing\Core\Plugin::uninstall();
}
