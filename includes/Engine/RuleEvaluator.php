<?php

namespace WpulsePricingRules\Includes\Engine;

use WpulsePricingRules\Includes\DB\RulesRepository;
use WpulsePricingRules\Includes\Exclusions\ExclusionService;

/**
 * Applies pricing rules to cart/checkout.
 * Hooks: woocommerce_before_calculate_totals, woocommerce_cart_calculate_fees, woocommerce_package_rates.
 */
class RuleEvaluator {

    /** @var bool Prevents running twice in same request */
    private static $run_completed = false;

    public static function register(): void {
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'onBeforeCalculateTotals'], 20, 3);
        add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'onCartCalculateFees'], 20, 2);
        add_filter('woocommerce_package_rates', [__CLASS__, 'onPackageRates'], 20, 2);
    }

    public static function onBeforeCalculateTotals($cart, $data = null): void {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (!$cart instanceof \WC_Cart) {
            return;
        }
        if (self::$run_completed) {
            return;
        }
        $rules = self::getActiveRules();
        $context = self::buildContext($cart);
        $applied = [];
        foreach ($rules as $row) {
            if (!self::ruleMatches($row, $context)) {
                continue;
            }
            $rule_data = json_decode($row['rule_json'], true);
            if (!is_array($rule_data)) {
                continue;
            }
            $stacking = $rule_data['stacking'] ?? [];
            $can_stack = $stacking['can_stack_with_other_rules'] ?? true;
            if (!empty($applied) && !$can_stack) {
                continue;
            }
            self::applyRuleToCart($row, $rule_data, $cart);
            $applied[] = $row['id'];
            if (!empty($stacking['stop_processing'])) {
                break;
            }
        }
        self::$run_completed = true;
    }

    public static function onCartCalculateFees($cart, $data = null): void {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (!$cart instanceof \WC_Cart) {
            return;
        }
        $rules = self::getActiveRules();
        $context = self::buildContext($cart);
        foreach ($rules as $row) {
            if (!self::ruleMatches($row, $context)) {
                continue;
            }
            $rule_data = json_decode($row['rule_json'], true);
            if (!is_array($rule_data)) {
                continue;
            }
            $benefit = $rule_data['benefit'] ?? [];
            $kind = $benefit['kind'] ?? '';
            if ($kind === 'cart_percent_off' || $kind === 'cart_fixed_off') {
                self::applyCartDiscountFee($row, $rule_data, $cart);
            }
        }
    }

    public static function onPackageRates($rates, $package): array {
        if (empty($rates) || !is_array($rates)) {
            return $rates;
        }
        $cart = WC()->cart;
        if (!$cart) {
            return $rates;
        }
        $rules = self::getActiveRules();
        $context = self::buildContext($cart);
        foreach ($rules as $row) {
            if (!self::ruleMatches($row, $context)) {
                continue;
            }
            $rule_data = json_decode($row['rule_json'], true);
            if (!is_array($rule_data)) {
                continue;
            }
            $benefit = $rule_data['benefit'] ?? [];
            if (($benefit['kind'] ?? '') === 'free_shipping') {
                foreach ($rates as $rate_id => $rate) {
                    if ($rate instanceof \WC_Shipping_Rate) {
                        $rates[$rate_id]->set_cost(0);
                        $rates[$rate_id]->set_taxes([]);
                    }
                }
                break;
            }
        }
        return $rates;
    }

    /**
     * @return array<int, array>
     */
    private static function getActiveRules(): array {
        $all = RulesRepository::all('priority', 'ASC');
        $active = [];
        foreach ($all as $row) {
            if (($row['status'] ?? '') !== 'active') {
                continue;
            }
            $rule = json_decode($row['rule_json'] ?? '{}', true);
            if (!is_array($rule) || !self::ruleInSchedule($rule)) {
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

    private static function buildContext($cart): array {
        $context = [
            'cart_subtotal' => $cart ? (float) $cart->get_subtotal() : 0,
            'user_id'       => get_current_user_id(),
            'user_roles'    => [],
            'is_checkout'   => is_checkout(),
        ];
        if ($context['user_id']) {
            $user = get_userdata($context['user_id']);
            if ($user && !empty($user->roles)) {
                $context['user_roles'] = $user->roles;
            }
        }
        return $context;
    }

    private static function ruleMatches(array $row, array $context): bool {
        $rule = json_decode($row['rule_json'], true);
        if (!is_array($rule)) {
            return false;
        }
        $groups = $rule['conditions']['groups'] ?? [];
        if (empty($groups)) {
            return true;
        }
        foreach ($groups as $group) {
            $items = $group['items'] ?? [];
            $logic = $group['logic'] ?? 'and';
            $group_ok = $logic === 'and';
            foreach ($items as $item) {
                $type = $item['type'] ?? '';
                $op = $item['operator'] ?? '=';
                $val = $item['value'] ?? null;
                $match = false;
                if ($type === 'cart_subtotal') {
                    $match = self::compare($context['cart_subtotal'], $op, is_numeric($val) ? (float) $val : 0);
                } elseif ($type === 'user_role') {
                    $roles = is_array($val) ? $val : [$val];
                    $match = !empty(array_intersect($context['user_roles'], $roles));
                    if ($op === 'not_in') {
                        $match = !$match;
                    }
                } elseif ($type === 'user_id') {
                    $ids = is_array($val) ? array_map('intval', $val) : [ (int) $val ];
                    $match = in_array($context['user_id'], $ids, true);
                    if ($op === 'not_in') {
                        $match = !$match;
                    }
                } elseif ($type === 'page' && $val === 'checkout') {
                    $match = $context['is_checkout'];
                }
                if ($logic === 'and') {
                    $group_ok = $group_ok && $match;
                } else {
                    $group_ok = $group_ok || $match;
                }
            }
            if ($group_ok) {
                return true;
            }
        }
        return false;
    }

    private static function compare($left, $op, $right): bool {
        switch ($op) {
            case '>=':
                return $left >= $right;
            case '>':
                return $left > $right;
            case '<=':
                return $left <= $right;
            case '<':
                return $left < $right;
            case '=':
            default:
                return (string) $left === (string) $right;
        }
    }

    private static function applyRuleToCart(array $row, array $rule_data, $cart): void {
        $benefit = $rule_data['benefit'] ?? [];
        $kind = $benefit['kind'] ?? '';
        $targets = $rule_data['targets'] ?? [];
        $exclusions = $rule_data['exclusions'] ?? [];

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            if (!$product || !self::productMatchesTargets($product, $targets)) {
                continue;
            }
            // Global exclusion list: skip if product/category/tag is excluded from ALL rules.
            if (ExclusionService::isWCProductExcluded($product)) {
                continue;
            }
            if (!empty($exclusions['enabled']) && self::productIsExcluded($product, $exclusions)) {
                continue;
            }
            $qty = (int) $cart_item['quantity'];
            if ($qty <= 0) {
                continue;
            }
            $price = (float) $product->get_price('edit');
            if ($price <= 0) {
                continue;
            }
            $adjustment = 0;
            switch ($kind) {
                case 'percent_off':
                    $pct = isset($benefit['percent']) ? (float) $benefit['percent'] : 0;
                    $adjustment = -$price * ($pct / 100);
                    break;
                case 'tiered':
                    $tiers = $benefit['tiers'] ?? [];
                    $tier = self::findTierForQty($qty, $tiers);
                    if ($tier !== null) {
                        $pct = $tier['percent_off'] ?? 0;
                        $fixed = $tier['fixed_off'] ?? 0;
                        $adjustment = -$price * ($pct / 100) * $qty - $fixed * $qty;
                    }
                    break;
                case 'x_for_y':
                    $buy_qty = (int) ($benefit['buy_qty'] ?? 0);
                    $pay_qty = (int) ($benefit['pay_qty'] ?? 0);
                    if ($buy_qty > 0 && $pay_qty < $buy_qty) {
                        $free_count = $qty - self::payCountForXForY($qty, $buy_qty, $pay_qty);
                        $adjustment = -$price * $free_count;
                    }
                    break;
                case 'nth_percent_off':
                    $nth = (int) ($benefit['nth'] ?? 1);
                    $pct = (float) ($benefit['percent'] ?? 0);
                    $each_set = !empty($benefit['apply_to_each_set']);
                    if ($nth <= 0) {
                        break;
                    }
                    $num_nth_units = (int) floor($qty / $nth);
                    if ($each_set) {
                        $adjustment = -$num_nth_units * $price * ($pct / 100);
                    } else {
                        $adjustment = $qty >= $nth ? -$price * ($pct / 100) : 0;
                    }
                    break;
                case 'category_discounts':
                    $product_cat_ids = self::getProductTermIds($product, 'product_cat');
                    $category_discounts = $benefit['category_discounts'] ?? [];
                    foreach ($category_discounts as $cd) {
                        $cat_ids = array_map('intval', (array) ($cd['category_ids'] ?? []));
                        if (empty($cat_ids) || empty(array_intersect($product_cat_ids, $cat_ids))) {
                            continue;
                        }
                        $apply_type = $cd['apply_type'] ?? 'percent';
                        $val = isset($cd['value']) ? (float) $cd['value'] : 0;
                        if ($apply_type === 'percent') {
                            $adjustment += -$price * ($val / 100) * $qty;
                        } else {
                            $adjustment += -$val * $qty;
                        }
                    }
                    break;
                default:
                    break;
            }
            if ($adjustment !== 0) {
                $new_price = max(0, $price + ($adjustment / $qty));
                $cart_item['data']->set_price($new_price);
            }
        }

        if ($kind === 'free_gift' && !empty($benefit['product_ids'])) {
            self::maybeAddFreeGifts($cart, $benefit['product_ids'], $rule_data);
        }
    }

    private static function productMatchesTargets($product, array $targets): bool {
        $type = $targets['type'] ?? 'all';
        if ($type === 'all') {
            return true;
        }
        $product_id = $product->get_id();
        if ($type === 'products' && !empty($targets['products'])) {
            return in_array($product_id, (array) $targets['products'], true);
        }
        if ($type === 'categories' && !empty($targets['categories'])) {
            $terms = wp_get_post_terms($product_id, 'product_cat');
            $ids = array_map(function ($t) {
                return (int) $t->term_id;
            }, $terms);
            return !empty(array_intersect($ids, (array) $targets['categories']));
        }
        return true;
    }

    private static function productIsExcluded($product, array $exclusions): bool {
        if (empty($exclusions['enabled']) || empty($exclusions['ids'])) {
            return false;
        }
        $type = $exclusions['type'] ?? 'products';
        $ids = array_map('intval', (array) $exclusions['ids']);
        if ($type === 'products') {
            return in_array($product->get_id(), $ids, true);
        }
        if ($type === 'categories') {
            $term_ids = self::getProductTermIds($product, 'product_cat');
            return !empty(array_intersect($term_ids, $ids));
        }
        if ($type === 'tags') {
            $term_ids = self::getProductTermIds($product, 'product_tag');
            return !empty(array_intersect($term_ids, $ids));
        }
        return false;
    }

    private static function getProductTermIds($product, string $taxonomy): array {
        $product_id = $product->get_id();
        $terms = wp_get_post_terms($product_id, $taxonomy);
        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }
        return array_map(function ($t) {
            return (int) $t->term_id;
        }, $terms);
    }

    private static function findTierForQty(int $qty, array $tiers): ?array {
        foreach ($tiers as $t) {
            $min = (int) ($t['min'] ?? 0);
            $max = (int) ($t['max'] ?? 0);
            if ($qty >= $min && ($max === 0 || $qty <= $max)) {
                return $t;
            }
        }
        return null;
    }

    private static function payCountForXForY(int $qty, int $buy_qty, int $pay_qty): int {
        if ($buy_qty <= 0) {
            return $qty;
        }
        $sets = (int) floor($qty / $buy_qty);
        $remainder = $qty % $buy_qty;
        return $sets * $pay_qty + $remainder;
    }

    private static function applyCartDiscountFee(array $row, array $rule_data, $cart): void {
        $benefit = $rule_data['benefit'] ?? [];
        $kind = $benefit['kind'] ?? '';
        $subtotal = (float) $cart->get_subtotal();
        $amount = 0;
        if ($kind === 'cart_percent_off') {
            $pct = (float) ($benefit['percent'] ?? 0);
            $amount = -$subtotal * ($pct / 100);
        } elseif ($kind === 'cart_fixed_off') {
            $fixed = (float) ($benefit['amount'] ?? 0);
            $amount = -$fixed;
        }
        if ($amount < 0) {
            $cart->add_fee(__('Discount', 'wpulse-pricing-rules-for-woocommerce') . ' (' . $row['name'] . ')', $amount, false);
        }
    }

    private static function maybeAddFreeGifts($cart, array $product_ids, array $rule_data): void {
        foreach ($product_ids as $pid) {
            $pid = (int) $pid;
            if ($pid <= 0) {
                continue;
            }
            $in_cart = false;
            foreach ($cart->get_cart() as $item) {
                if ((int) $item['product_id'] === $pid) {
                    $in_cart = true;
                    break;
                }
            }
            if (!$in_cart) {
                $cart->add_to_cart($pid, 1, 0, [], ['wpulse_free_gift' => true]);
            }
        }
    }
}
