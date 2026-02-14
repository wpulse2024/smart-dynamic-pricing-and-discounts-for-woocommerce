<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;
use WpulsePricingRules\Includes\Engine\TargetMatcher;

/**
 * Line-item benefit: x_for_y (buy X pay for Y – e.g. 3 for 2).
 */
class XForY {

    public static function apply(array $row, array $rule_data, Context $context): void {
        $benefit = $rule_data['benefit'] ?? [];
        $buy_qty = (int) ($benefit['buy_qty'] ?? 0);
        $pay_qty = (int) ($benefit['pay_qty'] ?? 0);
        if ($buy_qty <= 0 || $pay_qty >= $buy_qty) {
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
            $pay_count = self::payCountForXForY($qty, $buy_qty, $pay_qty);
            $free_count = $qty - $pay_count;
            $adjustment = -$price * $free_count;
            $new_price = max(0, $price + ($adjustment / $qty));
            PercentOff::setLinePrice($cart_item, $new_price, $row['id']);
        }
    }

    private static function payCountForXForY(int $qty, int $buy_qty, int $pay_qty): int {
        if ($buy_qty <= 0) {
            return $qty;
        }
        $sets = (int) floor($qty / $buy_qty);
        $remainder = $qty % $buy_qty;
        return $sets * $pay_qty + $remainder;
    }
}
