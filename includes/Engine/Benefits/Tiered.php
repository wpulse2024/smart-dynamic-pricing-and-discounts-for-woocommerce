<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;
use WpulsePricingRules\Includes\Engine\TargetMatcher;

/**
 * Line-item benefit: tiered (quantity tiers with percent_off and/or fixed_off per tier).
 */
class Tiered {

    public static function apply(array $row, array $rule_data, Context $context): void {
        $benefit = $rule_data['benefit'] ?? [];
        $tiers = $benefit['tiers'] ?? [];
        if (empty($tiers)) {
            return;
        }
        $targets = $rule_data['targets'] ?? [];
        $exclusions = $rule_data['exclusions'] ?? [];

        foreach ($context->cart_lines as $line) {
            $cart_item = $line['cart_item'] ?? null;
            $product = isset($cart_item['data']) ? $cart_item['data'] : null;
            if (!$product) {
                continue;
            }
            if (TargetMatcher::isGloballyExcluded($product)) {
                continue;
            }
            if (!TargetMatcher::lineMatchesTargets($line, $targets)) {
                continue;
            }
            if (TargetMatcher::isExcludedByRule($product, $exclusions)) {
                continue;
            }
            $qty = (int) $line['quantity'];
            $price = (float) $line['price'];
            if ($qty <= 0 || $price <= 0) {
                continue;
            }
            $tier = self::findTierForQty($qty, $tiers);
            if ($tier === null) {
                continue;
            }
            $pct = (float) ($tier['percent_off'] ?? 0);
            $fixed = (float) ($tier['fixed_off'] ?? 0);
            $adjustment = -$price * ($pct / 100) * $qty - $fixed * $qty;
            $new_price = max(0, $price + ($adjustment / $qty));
            PercentOff::setLinePrice($cart_item, $new_price, $row['id']);
        }
    }

    private static function findTierForQty(int $qty, array $tiers): ?array {
        foreach ($tiers as $t) {
            $min = (int) ($t['min'] ?? 0);
            $max = (int) ($t['max'] ?? 0);
            if ($qty >= $min && ($max === 0 || $qty <= $max)) {
                return $t;
            }
        }
        return null;
    }
}
