<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;

/**
 * Gift benefit: free_gift (product_ids) or buy_x_get_y.
 * Adds qualifying products to cart with _wpulse_is_gift and _wpulse_gift_rule_id.
 * RuleEngine removes non-qualifying gifts and adds qualifying ones; this performs the add.
 */
class FreeGift {

    /**
     * Add free gift products to cart. Called from RuleEngine after line-item rules.
     * Uses cart_item_data so WooCommerce stores _wpulse_is_gift and _wpulse_gift_rule_id.
     */
    public static function apply(array $row, array $rule_data, Context $context): void {
        $benefit = $rule_data['benefit'] ?? [];
        $kind = $benefit['kind'] ?? '';
        $cart = $context->cart;
        if (!$cart) {
            return;
        }

        $product_ids = [];
        if ($kind === 'free_gift' && !empty($benefit['product_ids'])) {
            $product_ids = array_map('intval', (array) $benefit['product_ids']);
        }
        if ($kind === 'buy_x_get_y' && !empty($benefit['get_selector']['products'])) {
            $product_ids = array_map('intval', (array) $benefit['get_selector']['products']);
        }
        if (empty($product_ids)) {
            return;
        }

        $rule_id = (int) $row['id'];
        foreach ($product_ids as $pid) {
            if ($pid <= 0) {
                continue;
            }
            if (self::isGiftInCart($cart, $pid, $rule_id)) {
                continue;
            }
            $cart->add_to_cart(
                $pid,
                1,
                0,
                [],
                [
                    '_wpulse_is_gift'     => 1,
                    '_wpulse_gift_rule_id' => $rule_id,
                ]
            );
        }
    }

    private static function isGiftInCart(\WC_Cart $cart, int $product_id, int $rule_id): bool {
        foreach ($cart->get_cart() as $item) {
            $item_pid = (int) ($item['product_id'] ?? 0);
            if ($item_pid !== $product_id) {
                continue;
            }
            $gift_rule = isset($item['_wpulse_gift_rule_id']) ? (int) $item['_wpulse_gift_rule_id'] : 0;
            if ($gift_rule === $rule_id) {
                return true;
            }
            // Same product already in cart as gift from any rule – avoid duplicate.
            if (!empty($item['_wpulse_is_gift'])) {
                return true;
            }
        }
        return false;
    }
}
