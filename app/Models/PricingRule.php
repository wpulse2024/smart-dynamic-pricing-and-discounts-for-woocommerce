<?php

namespace WpulsePricingRules\App\Models;

/**
 * Pricing rule model.
 * Schema management is handled exclusively by Installer::install().
 */
class PricingRule extends Model {

    protected static string $table = 'wpulse_pricing_rules';
}
