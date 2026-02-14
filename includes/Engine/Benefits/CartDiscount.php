<?php

namespace WpulsePricingRules\Includes\Engine\Benefits;

use WpulsePricingRules\Includes\Engine\Context;

/**
 * Cart-level benefit: cart_percent_off or cart_fixed_off (applied as negative fee).
 * Fee removal and re-add is done in RuleEngine; this only adds the fee.
 */
class CartDiscount {

    /**
     * Add cart discount as a negative fee. Call only from cart_calculate_fees.
     * Engine ensures previous wpulse fees are removed before rules run.
     */
    public static function apply(array $row, array $rule_data, Context $context): void {
        $benefit = $rule_data['benefit'] ?? [];
        $kind = $benefit['kind'] ?? '';
        $cart = $context->cart;
        if (!$cart) {
            return;
        }
        $subtotal = (float) $cart->get_subtotal();
        $amount = 0.0;
        if ($kind === 'cart_percent_off') {
            $pct = (float) ($benefit['percent'] ?? 0);
            $amount = -$subtotal * ($pct / 100);
        } elseif ($kind === 'cart_fixed_off') {
            $fixed = (float) ($benefit['amount'] ?? 0);
            $amount = -$fixed;
        }
        if ($amount < 0) {
            $name = '[wpulse] ' . __('Discount', 'wpulse-pricing-rules-for-woocommerce') . ' (' . $row['name'] . ')';
            $cart->add_fee($name, $amount, false);
        }
    }
}
