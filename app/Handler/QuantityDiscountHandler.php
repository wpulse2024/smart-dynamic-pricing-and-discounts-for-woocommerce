<?php

namespace SmartDynamicPricing\Handler;

use WC_Cart;
use SmartDynamicPricing\Helpers\Helper;

class QuantityDiscountHandler
{
    public function handle(WC_Cart $cart, $cart_item, $offer, $rule_name)
    {
        $tiers = $offer['tiers'] ?? [];
        $qty = $cart_item['quantity'];
        $product = $cart_item['data'];
        $base_price = (float) $product->get_regular_price();
        $original_total = $qty * $base_price;
        $discounted_total = $original_total;
        $applied_tier = null;
        $discounted_price = 0;
        $discounted_qty = 0;
   
        foreach ($tiers as $tier) {
            $min = (int) ($tier['min'] ?? 0);
            $max = (int) ($tier['max'] ?? 0);
            $discount_type = $tier['discountType'] ?? 'percentage';
            $discount_value = (float) ($tier['value'] ?? 0);

            if ($qty >= $min && $qty <= $max) {
                $applied_tier = $tier;
                break;
            }
        }

        if ($applied_tier) {
            $discounted_price = Helper::calculate_discounted_price($base_price, $discount_type, $discount_value);
            $discounted_qty = $qty;
            $discounted_total = $discounted_price * $discounted_qty;
        }

        if ($discounted_total < $original_total) {
            $product->set_price($discounted_price);
            return ['success' => true, 'base_product' => $product, 'totalDiscount' => $original_total - $discounted_total, 'rule_name' => $rule_name];
        }

        return ['success' => false];
    }
}