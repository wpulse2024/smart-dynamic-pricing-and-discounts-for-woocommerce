<?php

namespace WpulsePricingRules\Includes\Engine;

/**
 * Cart and request context for rule evaluation.
 * Built once per engine run and passed to condition/target/benefit logic.
 */
class Context {

    /** @var int */
    public $user_id;

    /** @var array<int, string> */
    public $user_roles;

    /** @var float Cart subtotal (before our adjustments). */
    public $cart_subtotal;

    /** @var int Total cart quantity (all items). */
    public $cart_quantity;

    /** @var int Number of distinct cart line items. */
    public $cart_items_count;

    /** @var float Customer lifetime total spent (0 for guests). */
    public $customer_total_spent;

    /** @var int Customer order count (0 for guests). */
    public $customer_order_count;

    /** @var string|null Shipping country code. */
    public $shipping_country;

    /** @var string|null Shipping state. */
    public $shipping_state;

    /** @var string 'cart'|'checkout'|'other' */
    public $page;

    /** @var array<string> Applied coupon codes. */
    public $applied_coupons;

    /** @var array<int, array> Product lines: product_id, variation_id, qty, categories, tags, price (per-unit). */
    public $cart_lines;

    /** @var \WC_Cart Reference for benefit handlers. */
    public $cart;

    /**
     * Build context from current cart and request.
     */
    public static function fromCart(\WC_Cart $cart): self {
        $ctx = new self();
        $ctx->cart = $cart;
        $ctx->user_id = get_current_user_id();
        $ctx->user_roles = [];
        if ($ctx->user_id) {
            $user = get_userdata($ctx->user_id);
            if ($user && !empty($user->roles)) {
                $ctx->user_roles = array_map('strval', $user->roles);
            }
        }

        $ctx->cart_subtotal = (float) $cart->get_subtotal();
        $ctx->cart_quantity = (int) $cart->get_cart_contents_count();
        $ctx->cart_items_count = is_array($cart->get_cart()) ? count($cart->get_cart()) : 0;
        $ctx->customer_total_spent = 0.0;
        $ctx->customer_order_count = 0;
        if ($ctx->user_id && function_exists('wc_get_customer_order_count')) {
            $ctx->customer_order_count = (int) wc_get_customer_order_count($ctx->user_id);
            if (class_exists('WC_Customer')) {
                $customer = new \WC_Customer($ctx->user_id);
                $ctx->customer_total_spent = (float) $customer->get_total_spent();
            }
        }
        $ctx->applied_coupons = $cart->get_applied_coupons();
        if (!is_array($ctx->applied_coupons)) {
            $ctx->applied_coupons = [];
        }

        $ctx->shipping_country = null;
        $ctx->shipping_state = null;
        if (WC()->customer) {
            $ctx->shipping_country = WC()->customer->get_shipping_country();
            $ctx->shipping_state = WC()->customer->get_shipping_state();
        }

        if (is_checkout()) {
            $ctx->page = 'checkout';
        } elseif (is_cart()) {
            $ctx->page = 'cart';
        } else {
            $ctx->page = 'other';
        }

        $ctx->cart_lines = [];
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = isset($cart_item['data']) ? $cart_item['data'] : null;
            if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
                continue;
            }
            $product_id = (int) $product->get_id();
            $variation_id = isset($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : 0;
            $qty = (int) ($cart_item['quantity'] ?? 0);
            $price = (float) $product->get_price('edit');
            $categories = self::getProductTermIds($product_id, 'product_cat');
            $tags = self::getProductTermIds($product_id, 'product_tag');
            $ctx->cart_lines[] = [
                'cart_item_key'  => $cart_item_key,
                'cart_item'      => $cart_item,
                'product_id'     => $product_id,
                'variation_id'   => $variation_id,
                'quantity'       => $qty,
                'categories'     => $categories,
                'tags'           => $tags,
                'price'          => $price,
            ];
        }

        return $ctx;
    }

    /**
     * Build context for product/shop display (no cart). Used to evaluate which single rule
     * would apply so we only show that rule's message. Cart-dependent conditions will fail.
     */
    public static function forProductPage(): self {
        $ctx = new self();
        $ctx->cart = null;
        $ctx->user_id = get_current_user_id();
        $ctx->user_roles = [];
        if ($ctx->user_id) {
            $user = get_userdata($ctx->user_id);
            if ($user && !empty($user->roles)) {
                $ctx->user_roles = array_map('strval', $user->roles);
            }
        }
        $ctx->cart_subtotal = 0.0;
        $ctx->cart_quantity = 0;
        $ctx->cart_items_count = 0;
        $ctx->customer_total_spent = 0.0;
        $ctx->customer_order_count = 0;
        if ($ctx->user_id && function_exists('wc_get_customer_order_count')) {
            $ctx->customer_order_count = (int) wc_get_customer_order_count($ctx->user_id);
            if (class_exists('WC_Customer')) {
                $customer = new \WC_Customer($ctx->user_id);
                $ctx->customer_total_spent = (float) $customer->get_total_spent();
            }
        }
        $ctx->applied_coupons = [];
        $ctx->shipping_country = null;
        $ctx->shipping_state = null;
        if (function_exists('WC') && WC()->customer) {
            $ctx->shipping_country = WC()->customer->get_shipping_country();
            $ctx->shipping_state = WC()->customer->get_shipping_state();
        }
        if (function_exists('is_checkout') && is_checkout()) {
            $ctx->page = 'checkout';
        } elseif (function_exists('is_cart') && is_cart()) {
            $ctx->page = 'cart';
        } else {
            $ctx->page = 'other';
        }
        $ctx->cart_lines = [];
        return $ctx;
    }

    private static function getProductTermIds(int $productId, string $taxonomy): array {
        $terms = wp_get_post_terms($productId, $taxonomy);
        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }
        return array_map(function ($t) {
            return (int) $t->term_id;
        }, $terms);
    }
}
