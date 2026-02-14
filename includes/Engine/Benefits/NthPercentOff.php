<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;
use WpulsePricingRules\Includes\Engine\TargetMatcher;

/**
 * Line-item benefit: nth_percent_off (e.g. 50% off 2nd unit, or every Nth unit).
 */
class NthPercentOff {

    public static function apply(array $row, array $rule_data, Context $context): void {
        $benefit = $rule_data['benefit'] ?? [];
        $nth = (int) ($benefit['nth'] ?? 1);
        $percent = (float) ($benefit['percent'] ?? 0);
        $each_set = !empty($benefit['apply_to_each_set']);
        if ($nth <= 0) {
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
            $num_nth_units = (int) floor($qty / $nth);
            if ($each_set) {
                $adjustment = -$num_nth_units * $price * ($percent / 100);
            } else {
                $adjustment = $qty >= $nth ? -$price * ($percent / 100) : 0;
            }
            $new_price = max(0, $price + ($adjustment / $qty));
            PercentOff::setLinePrice($cart_item, $new_price, $row['id']);
        }
    }
}
