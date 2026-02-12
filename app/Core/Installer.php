<?php

namespace WpulsePricingRules\Core;

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
            WPULSE_PRICING_RULES_PATH . 'assets/js',
            WPULSE_PRICING_RULES_PATH . 'assets/css',
            WPULSE_PRICING_RULES_PATH . 'languages'
        ];
        
        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                wp_mkdir_p($directory);
            }
        }
    }
}
