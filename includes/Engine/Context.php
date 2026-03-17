<?php

namespace WpulsePricingRules\Includes\Engine;

use WpulsePricingRules\Includes\Engine\RuleSchedule;

/**
 * Cart and request context for rule evaluation.
 * Built once per engine run and passed to condition/target/benefit logic.
 */
class Context {

    /** @var array<int, array{order_count: int, total_spent: float}> */
    private static array $customer_cache = [];

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
        $common = self::buildCommonContext();
        $ctx->user_id = $common['user_id'];
        $ctx->user_roles = $common['user_roles'];
        $ctx->customer_order_count = $common['customer_order_count'];
        $ctx->customer_total_spent = $common['customer_total_spent'];
        $ctx->shipping_country = $common['shipping_country'];
        $ctx->shipping_state = $common['shipping_state'];
        $ctx->page = $common['page'];

        $ctx->cart_subtotal = (float) $cart->get_subtotal();
        $ctx->cart_quantity = (int) $cart->get_cart_contents_count();
        $ctx->cart_items_count = is_array($cart->get_cart()) ? count($cart->get_cart()) : 0;
        $ctx->applied_coupons = $cart->get_applied_coupons();
        if (!is_array($ctx->applied_coupons)) {
            $ctx->applied_coupons = [];
        }

        $ctx->cart_lines = [];
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = isset($cart_item['data']) ? $cart_item['data'] : null;
            if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
                continue;
            }
            if ($product->get_price('edit') === null || $product->get_price('edit') === '') {
                error_log('[wpulse] Product ' . $product->get_id() . ' has no price set, skipping.');
                continue;
            }
            $product_id = (int) $product->get_id();
            $variation_id = isset($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : 0;
            $qty = (int) ($cart_item['quantity'] ?? 0);
            $price = (float) $product->get_price('edit');
            $categories = RuleSchedule::getTermIds($product_id, 'product_cat');
            $tags = RuleSchedule::getTermIds($product_id, 'product_tag');
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
        $common = self::buildCommonContext();
        $ctx->user_id = $common['user_id'];
        $ctx->user_roles = $common['user_roles'];
        $ctx->customer_order_count = $common['customer_order_count'];
        $ctx->customer_total_spent = $common['customer_total_spent'];
        $ctx->shipping_country = $common['shipping_country'];
        $ctx->shipping_state = $common['shipping_state'];
        $ctx->page = $common['page'];
        $ctx->cart_subtotal = 0.0;
        $ctx->cart_quantity = 0;
        $ctx->cart_items_count = 0;
        $ctx->applied_coupons = [];
        $ctx->cart_lines = [];
        return $ctx;
    }

    private static function buildCommonContext(): array {
        $user_id = get_current_user_id();
        $user_roles = [];
        if ($user_id) {
            $user = get_userdata($user_id);
            if ($user && !empty($user->roles)) {
                $user_roles = array_map('strval', $user->roles);
            }
        }
        $customer_order_count = 0;
        $customer_total_spent = 0.0;
        if ($user_id && function_exists('wc_get_customer_order_count')) {
            if (isset(self::$customer_cache[$user_id])) {
                $customer_order_count = self::$customer_cache[$user_id]['order_count'];
                $customer_total_spent = self::$customer_cache[$user_id]['total_spent'];
            } else {
                $customer_order_count = (int) wc_get_customer_order_count($user_id);
                if (class_exists('WC_Customer')) {
                    $customer = new \WC_Customer($user_id);
                    $customer_total_spent = (float) $customer->get_total_spent();
                }
                self::$customer_cache[$user_id] = [
                    'order_count' => $customer_order_count,
                    'total_spent' => $customer_total_spent,
                ];
            }
        }
        $shipping_country = null;
        $shipping_state = null;
        if (function_exists('WC') && WC()->customer) {
            $shipping_country = WC()->customer->get_shipping_country();
            $shipping_state = WC()->customer->get_shipping_state();
        }
        if (function_exists('is_checkout') && is_checkout()) {
            $page = 'checkout';
        } elseif (function_exists('is_cart') && is_cart()) {
            $page = 'cart';
        } else {
            $page = 'other';
        }
        return [
            'user_id'              => $user_id,
            'user_roles'           => $user_roles,
            'customer_order_count' => $customer_order_count,
            'customer_total_spent' => $customer_total_spent,
            'shipping_country'     => $shipping_country,
            'shipping_state'       => $shipping_state,
            'page'                 => $page,
        ];
    }
}
