<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;
use WpulsePricingRules\Includes\Engine\TargetMatcher;

/**
 * Line-item benefit: category_discounts (per-category percent or fixed).
 */
class CategoryDiscounts {

    public static function apply(array $row, array $rule_data, Context $context): void {
        $benefit = $rule_data['benefit'] ?? [];
        $category_discounts = $benefit['category_discounts'] ?? [];
        if (empty($category_discounts)) {
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
            $categories = $line['categories'] ?? [];
            if ($qty <= 0 || $price <= 0) {
                continue;
            }
            $adjustment = 0.0;
            foreach ($category_discounts as $cd) {
                $cat_ids = array_map('intval', (array) ($cd['category_ids'] ?? []));
                if (empty($cat_ids) || empty(array_intersect($categories, $cat_ids))) {
                    continue;
                }
                $apply_type = $cd['apply_type'] ?? 'percent';
                $val = isset($cd['value']) ? (float) $cd['value'] : 0;
                if ($apply_type === 'percent') {
                    $adjustment += -$price * ($val / 100) * $qty;
                } else {
                    $adjustment += -$val * $qty;
                }
            }
            if ($adjustment !== 0.0) {
                $new_price = round(max(0, $price + ($adjustment / $qty)), wc_get_price_decimals());
                PercentOff::setLinePrice($cart_item, $new_price, $row['id']);
            }
        }
    }
}
