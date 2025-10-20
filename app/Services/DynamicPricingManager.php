<?php

namespace SmartDynamicPricingDiscounts\Services;

use SmartDynamicPricingDiscounts\Models\Rule;
use SmartDynamicPricingDiscounts\Helpers\ValidateApplyDiscount;
use WC_Cart;

class DynamicPricingManager
{
    protected $rules = [];
    protected $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
        $this->loadRules();
        add_action('woocommerce_before_calculate_totals', [$this, 'apply_cart_discounts'], 10);
    }

    public function loadRules()
    {
        $rules = Rule::all();
        $rules = array_map(function ($rule) {
            $rule->product_scope = maybe_unserialize($rule->product_scope);
            $rule->user_scope = maybe_unserialize($rule->user_scope);
            $rule->schedule = maybe_unserialize($rule->schedule);
            $rule->offers = maybe_unserialize($rule->offers);
            $rule->meta = maybe_unserialize($rule->meta);
            return $rule;
        }, $rules);

        $this->rules = array_filter($rules, fn($r) => $r->status === 'active');
    }

    /**
     * Apply all dynamic pricing discounts before WooCommerce totals are calculated.
     */
    public function apply_cart_discounts(WC_Cart $cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($this->rules as $rule) {
            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                if (!ValidateApplyDiscount::validate($rule, $cart_item, $cart_item_key)) {
                    continue;
                }

                if (empty($rule->offers) || !is_array($rule->offers)) {
                    continue;
                }

                foreach ($rule->offers as $offer) {
                    if (($offer['type'] ?? '') !== 'special_offer') {
                        continue;
                    }

                    $this->apply_offer_to_cart($cart, $cart_item, $offer);
                }
            }
        }
    }

    /**
     * Core logic to apply each offer.
     */
    protected function apply_offer_to_cart(WC_Cart $cart, $cart_item, $offer)
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
            return;
        }

        $base_product_id = $cart_item['product_id'];
        $qty = $cart_item['quantity'];
        $base_product = $cart_item['data'];
        $base_price = (float) $base_product->get_regular_price();

        // --- CASE 1: same product ---
        if ($discount_product_type === 'same_product') {
            $group_size = $buy + $get;
            $times = $repeat ? floor($qty / $group_size) : ($qty >= $group_size ? 1 : 0);
            if ($times <= 0) return;

            $discounted_qty = $times * $get;
            $full_price_qty = $qty - $discounted_qty;

            $discounted_price = $this->calculate_discounted_price($base_price, $discount_type, $discount_value);
            $new_total = ($full_price_qty * $base_price) + ($discounted_qty * $discounted_price);
            $base_product->set_price(round($new_total / $qty, wc_get_price_decimals()));
        }

        // --- CASE 2: specific products (cross-product BOGO) ---
        elseif ($discount_product_type === 'specific_products') {
            $target_products = $reward['specific_products'] ?? [];
            if (empty($target_products)) return;

            foreach ($cart->get_cart() as $target_key => $target_item) {
                $target_product_id = $target_item['product_id'];
                if (!in_array($target_product_id, $target_products, true)) {
                    continue;
                }

                // Determine how many discounted items we can give
                $available_to_discount = min($get, $target_item['quantity']);

                $group_size = $buy + $get;
                $times = $repeat ? floor($qty / $group_size) : ($qty >= $buy ? 1 : 0);
                if ($times <= 0) continue;

                $discount_qty = min($times * $available_to_discount, $target_item['quantity']);
                $target_product = $target_item['data'];
                $target_base_price = (float) $target_product->get_regular_price();
                $discounted_price = $this->calculate_discounted_price($target_base_price, $discount_type, $discount_value);

                // Adjust target product price proportionally
                $full_price_qty = $target_item['quantity'] - $discount_qty;
                $new_total = ($full_price_qty * $target_base_price) + ($discount_qty * $discounted_price);
                $target_product->set_price(round($new_total / $target_item['quantity'], wc_get_price_decimals()));
            }
        }
    }

    /**
     * Compute discounted price for an item.
     */
    protected function calculate_discounted_price($base_price, $discount_type, $discount_value)
    {
        if ($discount_type === 'percentage') {
            return $base_price * (1 - ($discount_value / 100));
        }
        if ($discount_type === 'fixed') {
            return max(0, $base_price - $discount_value);
        }
        return $base_price;
    }
}
