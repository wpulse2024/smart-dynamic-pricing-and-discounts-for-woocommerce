<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;
use WpulsePricingRules\Includes\Engine\TargetMatcher;

/**
 * Line-item benefit: percent_off (global or targeted).
 */
class PercentOff {

    /**
     * Apply percent off to matching cart lines. Excluded products are skipped.
     */
    public static function apply(array $row, array $rule_data, Context $context): void {
        $benefit = $rule_data['benefit'] ?? [];
        $percent = isset($benefit['percent']) ? (float) $benefit['percent'] : 0;
        if ($percent <= 0) {
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
            $adjustment_per_unit = -$price * ($percent / 100);
            $new_price = max(0, $price + $adjustment_per_unit);
            self::setLinePrice($cart_item, $new_price, $row['id']);
        }
    }

    public static function setLinePrice(array $cart_item, float $new_price, int $rule_id): void {
        $product = $cart_item['data'];
        if (!$product || !is_object($product)) {
            return;
        }
        $product->set_price($new_price);
        // Track applied rule on cart item (stored in session via cart item array).
        if (!isset($cart_item['_wpulse_applied_rule_ids']) || !is_array($cart_item['_wpulse_applied_rule_ids'])) {
            $cart_item['_wpulse_applied_rule_ids'] = [];
        }
        if (!in_array($rule_id, $cart_item['_wpulse_applied_rule_ids'], true)) {
            $cart_item['_wpulse_applied_rule_ids'][] = $rule_id;
        }
    }
}
