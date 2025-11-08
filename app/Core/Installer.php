<?php

namespace SmartPricing\Core;

/**
 * Plugin installer
 */
class Installer
{
    /**
     * Post-install hook for composer
     */
    public static function postInstall(): void
    {
        // Create necessary directories
        $directories = [
            SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'assets/js',
            SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'assets/css',
            SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'languages'
        ];
        
        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                wp_mkdir_p($directory);
            }
        }
        
        // Create .htaccess for public directory
        $htaccessContent = "Options -Indexes\n";
        file_put_contents(SMART_PRICING_AND_DISCOUNTS_FOR_WOOCOMMERCE_PATH . 'public/.htaccess', $htaccessContent);
    }
}
