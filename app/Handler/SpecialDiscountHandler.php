<?php

namespace SmartDynamicPricingDiscounts\Handler;

use WC_Cart;
use SmartDynamicPricingDiscounts\Helpers\Helper;

class SpecialDiscountHandler
{
      /**
     * Core logic to apply each offer.
     * Returns true if a discount was applied.
     */
    public function handle(WC_Cart $cart, $cart_item, $offer, $rule_name)
    {
        $condition = $offer['condition'] ?? [];
        $reward = $offer['reward'] ?? [];

        $buy = (int) ($condition['purchaseQuantity'] ?? 0);
        $repeat = (bool) ($condition['repeat'] ?? false);

        $get = (int) ($reward['discountedItems'] ?? 0);
        $discount_value = (float) ($reward['discountValue'] ?? 0);
        $discount_type = $reward['discountType'] ?? 'percentage';
        $discount_product_type = $reward['discount_product_type'] ?? 'same_product';

        if ($buy <= 0 || $get <= 0) {
            return ['success' => false];
        }

        $base_product_id = $cart_item['product_id'];
        $qty = $cart_item['quantity'];
        $base_product = $cart_item['data'];
        $base_price = (float) $base_product->get_regular_price();

        // --- CASE 1: same product ---
        if ($discount_product_type === 'same_product') {
            $group_size = $buy + $get;
            $times = $repeat ? floor($qty / $group_size) : ($qty >= $group_size ? 1 : 0);
            if ($times <= 0) return ['success' => false];

            $discounted_qty = $times * $get;
            $full_price_qty = $qty - $discounted_qty;

            $discounted_price = Helper::calculate_discounted_price($base_price, $discount_type, $discount_value);

            $new_total = ($full_price_qty * $base_price) + ($discounted_qty * $discounted_price);
            $base_product->set_price(round($new_total / $qty, wc_get_price_decimals()));

            $totalDiscount = ($base_price * $qty) - $new_total;
            return ['success' => true, 'base_product' => $base_product, 'totalDiscount' => $totalDiscount, 'rule_name' => $rule_name];
        }

        // --- CASE 2: specific products or categories ---
        if (in_array($discount_product_type, ['specific_products', 'specific_categories'], true)) {
            $target_products = $reward['specific_products'] ?? [];
            $target_categories = $reward['specific_categories'] ?? [];

            foreach ($cart->get_cart() as $target_key => $target_item) {
                $target_product_id = $target_item['product_id'];

                if (
                    ($discount_product_type === 'specific_products' && !in_array($target_product_id, $target_products, true)) ||
                    ($discount_product_type === 'specific_categories' && !has_term($target_categories, 'product_cat', $target_product_id))
                ) {
                    continue;
                }

                $available_to_discount = min($get, $target_item['quantity']);
                $group_size = $buy + $get;
                $times = $repeat ? floor($qty / $group_size) : ($qty >= $buy ? 1 : 0);
                if ($times <= 0) continue;

                $discount_qty = min($times * $available_to_discount, $target_item['quantity']);
                $target_product = $target_item['data'];
                $target_base_price = (float) $target_product->get_regular_price();
                $discounted_price = $this->calculate_discounted_price($target_base_price, $discount_type, $discount_value);

                $full_price_qty = $target_item['quantity'] - $discount_qty;
                $new_total = ($full_price_qty * $target_base_price) + ($discount_qty * $discounted_price);
                $target_product->set_price(round($new_total / $target_item['quantity'], wc_get_price_decimals()));

                $totalDiscount = ($target_base_price * $target_item['quantity']) - $new_total;
                $this->showsApplyDiscountMessage($totalDiscount, $rule_name, $target_product);

                return ['success' => true, 'base_product' => $base_product, 'totalDiscount' => $totalDiscount, 'rule_name' => $rule_name];
            }
        }

        return ['success' => false];
    }
}
