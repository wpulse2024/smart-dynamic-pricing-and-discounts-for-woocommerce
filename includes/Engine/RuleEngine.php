<?php

namespace WpulsePricingRules\Includes\Engine;

use WpulsePricingRules\Includes\DB\RulesRepository;
use WpulsePricingRules\Includes\Engine\Benefits\PercentOff;
use WpulsePricingRules\Includes\Engine\Benefits\Tiered;
use WpulsePricingRules\Includes\Engine\Benefits\XForY;
use WpulsePricingRules\Includes\Engine\Benefits\NthPercentOff;
use WpulsePricingRules\Includes\Engine\Benefits\CategoryDiscounts;
use WpulsePricingRules\Includes\Engine\Benefits\CartDiscount;
use WpulsePricingRules\Includes\Engine\Benefits\FreeGift;
use WpulsePricingRules\Includes\Engine\Benefits\FreeShipping;

/**
 * Main pricing rule apply engine.
 * Runs on cart/checkout/AJAX: restores original prices, removes previous wpulse fees/gifts,
 * then applies active rules in priority order with stacking/stop_processing and recursion protection.
 */
class RuleEngine {

    /** @var bool Prevents re-entering same hook in one request (recursion protection). */
    private static $run_completed = false;

    /** @var string|null Cart signature from last apply (avoid double-apply same cart in one request). */
    private static $last_cart_signature = null;

    public static function register(): void {
        // A) Line item pricing (cart, checkout, AJAX refresh).
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'onBeforeCalculateTotals'], 9999, 3);
        // B) Cart discounts (fees).
        add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'onCartCalculateFees'], 9999, 2);
        // C) Free shipping (rates filter).
        add_filter('woocommerce_package_rates', [__CLASS__, 'onPackageRates'], 9999, 2);
        // D) Gift handler: remove wpulse gifts before totals so main run can re-add qualifying ones.
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'giftHandler'], 9000, 3);
        // E) Add-to-cart – flag so next totals run is fresh (optional, improves stability).
        add_action('woocommerce_add_to_cart', [__CLASS__, 'maybeFlagRecalc'], 10, 6);
        // F) Cart/checkout updates – flag recalc.
        add_action('woocommerce_cart_updated', [__CLASS__, 'maybeFlagRecalc'], 10);
        add_action('woocommerce_applied_coupon', [__CLASS__, 'maybeFlagRecalc'], 10);
        add_action('woocommerce_removed_coupon', [__CLASS__, 'maybeFlagRecalc'], 10);
        add_action('woocommerce_before_cart_item_quantity_zero', [__CLASS__, 'maybeFlagRecalc'], 10);
        add_action('woocommerce_checkout_update_order_review', [__CLASS__, 'maybeFlagRecalc'], 10);
    }

    /**
     * Remove wpulse gifts so they can be re-evaluated and re-added by main engine.
     * Runs at 9000 so it runs before onBeforeCalculateTotals at 9999.
     */
    public static function giftHandler($cart, $data = null): void {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (!$cart instanceof \WC_Cart) {
            return;
        }
        $to_remove = [];
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (!empty($cart_item['_wpulse_is_gift'])) {
                $to_remove[] = $cart_item_key;
            }
        }
        foreach ($to_remove as $key) {
            $cart->remove_cart_item($key);
        }
    }

    /**
     * Clear applied state so next totals calculation runs (no-op; hash change handles it).
     * Kept for future use (e.g. force recalc via session).
     */
    public static function maybeFlagRecalc(): void {
        // Cart hash / contents change naturally on quantity/coupon/remove, so we don't need to set a flag.
        // If needed: WC()->session->set('wpulse_recalc', true); and check in onBeforeCalculateTotals.
    }

    public static function onBeforeCalculateTotals($cart, $data = null): void {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (!$cart instanceof \WC_Cart) {
            return;
        }

        $signature = self::cartSignature($cart);
        // Recursion protection: if we already ran and cart unchanged, skip.
        if (self::$run_completed && $signature === self::$last_cart_signature) {
            return;
        }

        self::$run_completed = true;
        self::$last_cart_signature = $signature;

        // 1) Restore original prices so we never double-apply on already discounted prices.
        self::restoreOriginalPrices($cart);
        // 2) Clear free shipping flag; rules will set it if applicable.
        $session = WC()->session;
        if ($session) {
            $session->set('wpulse_free_shipping', false);
        }

        $rules = self::getActiveRules();
        $context = Context::fromCart($cart);
        $applied_rule_ids = [];
        foreach ($rules as $row) {
            $rule_data = is_string($row['rule_json'] ?? null) ? json_decode($row['rule_json'], true) : ($row['rule'] ?? []);
            if (!is_array($rule_data)) {
                $rule_data = [];
            }
            if (!self::ruleInSchedule($rule_data)) {
                continue;
            }
            if (!ConditionEvaluator::evaluate($rule_data, $context)) {
                continue;
            }
            $stacking = $rule_data['stacking'] ?? [];
            $can_stack = !isset($stacking['can_stack_with_other_rules']) || $stacking['can_stack_with_other_rules'];
            if (!empty($applied_rule_ids) && !$can_stack) {
                continue;
            }

            self::applyBenefit($row, $rule_data, $context, 'line');
            $applied_rule_ids[] = $row['id'];
            if (!empty($stacking['stop_processing'])) {
                break;
            }
        }

        // Add gifts (benefit handlers that run as part of rule loop already did line-item/fees/shipping;
        // free_gift and x_for_y add to cart here to avoid running in a separate loop).
        foreach ($rules as $row) {
            $rule_data = is_string($row['rule_json'] ?? null) ? json_decode($row['rule_json'], true) : ($row['rule'] ?? []);
            if (!is_array($rule_data)) {
                continue;
            }
            if (!self::ruleInSchedule($rule_data) || !ConditionEvaluator::evaluate($rule_data, $context)) {
                continue;
            }
            $stacking = $rule_data['stacking'] ?? [];
            $can_stack = !isset($stacking['can_stack_with_other_rules']) || $stacking['can_stack_with_other_rules'];
            if (!empty($applied_rule_ids) && !$can_stack && !in_array($row['id'], $applied_rule_ids, true)) {
                continue;
            }
            if (!in_array($row['id'], $applied_rule_ids, true)) {
                continue;
            }
            self::applyBenefit($row, $rule_data, $context, 'gift');
        }

        // Set gift line item prices to 0 (gifts were removed at 9000 and re-added above).
        self::setGiftPricesToZero($cart);
    }

    public static function onCartCalculateFees($cart, $data = null): void {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (!$cart instanceof \WC_Cart) {
            return;
        }
        self::removeWpulseFees($cart);
        $rules = self::getActiveRules();
        $context = Context::fromCart($cart);
        $applied_rule_ids = [];

        foreach ($rules as $row) {
            $rule_data = is_string($row['rule_json'] ?? null) ? json_decode($row['rule_json'], true) : ($row['rule'] ?? []);
            if (!is_array($rule_data)) {
                continue;
            }
            if (!self::ruleInSchedule($rule_data) || !ConditionEvaluator::evaluate($rule_data, $context)) {
                continue;
            }
            $stacking = $rule_data['stacking'] ?? [];
            $can_stack = !isset($stacking['can_stack_with_other_rules']) || $stacking['can_stack_with_other_rules'];
            if (!empty($applied_rule_ids) && !$can_stack) {
                continue;
            }
            $benefit = $rule_data['benefit'] ?? [];
            $kind = $benefit['kind'] ?? '';
            if ($kind === 'cart_percent_off' || $kind === 'cart_fixed_off') {
                CartDiscount::apply($row, $rule_data, $context);
                $applied_rule_ids[] = $row['id'];
            }
            if (!empty($stacking['stop_processing'])) {
                break;
            }
        }
    }

    public static function onPackageRates($rates, $package): array {
        if (empty($rates) || !is_array($rates)) {
            return $rates;
        }
        $session = WC()->session;
        if (!$session || !$session->get('wpulse_free_shipping')) {
            return $rates;
        }
        foreach ($rates as $rate_id => $rate) {
            if ($rate instanceof \WC_Shipping_Rate) {
                $rates[$rate_id]->set_cost(0);
                $rates[$rate_id]->set_taxes([]);
            }
        }
        return $rates;
    }

    private static function cartSignature(\WC_Cart $cart): string {
        $parts = [];
        foreach ($cart->get_cart() as $item) {
            $parts[] = ($item['product_id'] ?? 0) . '-' . ($item['variation_id'] ?? 0) . '-' . ($item['quantity'] ?? 0);
        }
        $parts[] = implode(',', $cart->get_applied_coupons());
        return md5(implode('|', $parts));
    }

    /**
     * Restore each cart item to its original price so rules apply on clean base prices (no double discount).
     * Original prices are stored in session (keyed by cart_item_key) since we cannot set cart item meta from outside.
     */
    private static function restoreOriginalPrices(\WC_Cart $cart): void {
        $session = WC()->session;
        if (!$session) {
            return;
        }
        $stored = $session->get('wpulse_original_prices');
        if (!is_array($stored)) {
            $stored = [];
        }
        $updated = [];
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = isset($cart_item['data']) ? $cart_item['data'] : null;
            if (!$product || !is_object($product)) {
                continue;
            }
            $original = isset($stored[$cart_item_key]) ? (float) $stored[$cart_item_key] : (float) $product->get_price('edit');
            $updated[$cart_item_key] = $original;
            $product->set_price($original);
        }
        $session->set('wpulse_original_prices', $updated);
    }

    private static function removeWpulseFees(\WC_Cart $cart): void {
        $fees = [];
        if (method_exists($cart, 'fees_api')) {
            $fees = $cart->fees_api()->get_fees();
        } elseif (method_exists($cart, 'get_fees')) {
            $fees = $cart->get_fees();
        }
        if (empty($fees) || !is_array($fees)) {
            return;
        }
        $keep = [];
        foreach ($fees as $key => $fee) {
            $name = is_object($fee) && isset($fee->name) ? $fee->name : (string) $fee;
            if (strpos($name, '[wpulse]') !== 0) {
                $keep[$key] = $fee;
            }
        }
        if (method_exists($cart, 'fees_api')) {
            $cart->fees_api()->set_fees($keep);
        }
    }

    private static function setGiftPricesToZero(\WC_Cart $cart): void {
        foreach ($cart->get_cart() as $cart_item) {
            if (empty($cart_item['_wpulse_is_gift'])) {
                continue;
            }
            $product = isset($cart_item['data']) ? $cart_item['data'] : null;
            if ($product && is_object($product)) {
                $product->set_price(0);
            }
        }
    }

    private static function getActiveRules(): array {
        $all = RulesRepository::all('priority', 'ASC');
        $active = [];
        foreach ($all as $row) {
            if (($row['status'] ?? '') !== 'active') {
                continue;
            }
            $active[] = $row;
        }
        return $active;
    }

    private static function ruleInSchedule(array $rule): bool {
        $schedule = $rule['schedule'] ?? [];
        $start = $schedule['start'] ?? '';
        $end = $schedule['end'] ?? '';
        $now = current_time('timestamp');
        if ($start !== '' && strtotime($start) > $now) {
            return false;
        }
        if ($end !== '' && strtotime($end) < $now) {
            return false;
        }
        return true;
    }

    private static function applyBenefit(array $row, array $rule_data, Context $context, string $phase): void {
        $benefit = $rule_data['benefit'] ?? [];
        $kind = $benefit['kind'] ?? '';
        if ($phase === 'gift') {
            if ($kind === 'free_gift' || $kind === 'x_for_y') {
                FreeGift::apply($row, $rule_data, $context);
            }
            return;
        }
        switch ($kind) {
            case 'percent_off':
                PercentOff::apply($row, $rule_data, $context);
                break;
            case 'tiered':
                Tiered::apply($row, $rule_data, $context);
                break;
            case 'x_for_y':
                XForY::apply($row, $rule_data, $context);
                break;
            case 'nth_percent_off':
                NthPercentOff::apply($row, $rule_data, $context);
                break;
            case 'category_discounts':
                CategoryDiscounts::apply($row, $rule_data, $context);
                break;
            case 'cart_percent_off':
            case 'cart_fixed_off':
                // Handled in onCartCalculateFees.
                break;
            case 'free_shipping':
                FreeShipping::apply($row, $rule_data, $context);
                break;
            case 'free_gift':
            case 'x_for_y':
                // Handled in gift phase.
                break;
            default:
                break;
        }
    }
}
