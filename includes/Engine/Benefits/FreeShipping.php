<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;

/**
 * Free shipping benefit: set session flag so woocommerce_package_rates filter can zero out costs.
 */
class FreeShipping {

    /**
     * Set session flag so RuleEngine's package_rates filter applies free shipping.
     * Does not modify cart; engine reads this flag in onPackageRates.
     */
    public static function apply(array $row, array $rule_data, Context $context): void {
        $session = WC()->session;
        if (!$session) {
            return;
        }
        $session->set('wpulse_free_shipping', true);
    }
}
