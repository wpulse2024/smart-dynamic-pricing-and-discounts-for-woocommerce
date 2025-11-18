<?php

namespace SmartDynamicPricing\Core;

/**
 * Action class for handling plugin actions
 */
class Action
{
    /**
     * Handle the action
     */
    public function handle(): void
    {
        // Add action logic here
        add_action('plugins_loaded', function() {
            global $wpdb;
            new \SmartDynamicPricing\Services\DynamicPricingManager($wpdb);
            new \SmartDynamicPricing\Handler\HandleOfferMessageOnCart();
        });
    }
}
