<?php

namespace WpulsePricingRules\Handler;

use WC_Cart;
use WpulsePricingRules\Helpers\Helper;

class HandleGlobalDiscount
{
    public function handle(WC_Cart $cart, $cart_item, $offer, $rule_name)
    {
        $discount_value = (float) ($offer['reward']['value'] ?? 0);
        $discount_type = $offer['reward']['discountType'] ?? 'percentage';
        $qty = $cart_item['quantity'];
        $product = $cart_item['data'];
        $base_price = Helper::get_base_price_for_discount($product);
        $original_total = $qty * $base_price;
        $discounted_price = Helper::calculate_discounted_price($base_price, $discount_type, $discount_value);
        $discounted_total = $discounted_price * $qty;

        // var_dump(['success' => true, 'totalDiscount' => $original_total - $discounted_total, 'rule_name' => $rule_name]);
        if ($discounted_total < $original_total) {
            $product->set_price($discounted_price);
            return ['success' => true, 'base_product' => $product, 'totalDiscount' => $original_total - $discounted_total, 'rule_name' => $rule_name];
        }

        return ['success' => false];
    }
}