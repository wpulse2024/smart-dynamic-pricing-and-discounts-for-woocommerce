<?php

namespace WpulsePricingRules\Includes\Engine;

/**
 * Evaluates rule condition groups.
 * Groups are OR'd together; within each group, items use the group's logic (AND or OR).
 */
class ConditionEvaluator {

    /**
     * Evaluate rule conditions against context.
     *
     * @param array $rule Decoded rule (conditions.groups).
     * @param Context $context
     * @return bool
     */
    public static function evaluate(array $rule, Context $context): bool {
        $groups = $rule['conditions']['groups'] ?? [];
        if (empty($groups)) {
            return true;
        }
        foreach ($groups as $group) {
            if (self::evaluateGroup($group, $context)) {
                return true;
            }
        }
        return false;
    }

    private static function evaluateGroup(array $group, Context $context): bool {
        $items = $group['items'] ?? [];
        $logic = isset($group['logic']) && strtolower($group['logic']) === 'or' ? 'or' : 'and';
        if (empty($items)) {
            return $logic === 'or' ? false : true;
        }
        $results = [];
        foreach ($items as $item) {
            $results[] = self::evaluateItem($item, $context);
        }
        if ($logic === 'and') {
            return !in_array(false, $results, true);
        }
        return in_array(true, $results, true);
    }

    private static function evaluateItem(array $item, Context $context): bool {
        $type = $item['type'] ?? '';
        $op = $item['operator'] ?? '=';
        $val = $item['value'] ?? null;

        switch ($type) {
            case 'cart_subtotal':
                return self::compare($context->cart_subtotal, $op, is_numeric($val) ? (float) $val : 0);
            case 'cart_quantity':
                return self::compare($context->cart_quantity, $op, is_numeric($val) ? (int) $val : 0);
            case 'cart_items_count':
                return self::compare($context->cart_items_count, $op, is_numeric($val) ? (int) $val : 0);
            case 'total_amount_spent':
                return self::compare($context->customer_total_spent, $op, is_numeric($val) ? (float) $val : 0);
            case 'order_count':
                return self::compare($context->customer_order_count, $op, is_numeric($val) ? (int) $val : 0);
            case 'product_in_cart':
                $product_ids = is_array($val) ? array_map('intval', $val) : [ (int) $val ];
                $cart_product_ids = [];
                foreach ($context->cart_lines as $line) {
                    $cart_product_ids[] = (int) ($line['product_id'] ?? 0);
                }
                $match = !empty(array_intersect($product_ids, $cart_product_ids));
                return $op === 'not_in' ? !$match : $match;
            case 'user_role':
                $roles = is_array($val) ? array_map('strval', $val) : [ (string) $val ];
                $match = !empty(array_intersect($context->user_roles, $roles));
                return $op === 'not_in' ? !$match : $match;
            case 'user_id':
                $ids = is_array($val) ? array_map('intval', $val) : [ (int) $val ];
                $match = in_array($context->user_id, $ids, true);
                return $op === 'not_in' ? !$match : $match;
            case 'page':
                $match = (string) $val === $context->page;
                return $match;
            case 'shipping_country':
                $countries = is_array($val) ? $val : [ $val ];
                $match = $context->shipping_country && in_array($context->shipping_country, $countries, true);
                return $op === 'not_in' ? !$match : $match;
            case 'coupon':
                $codes = is_array($val) ? $val : [ $val ];
                $match = !empty(array_intersect($context->applied_coupons, $codes));
                return $op === 'not_in' ? !$match : $match;
            default:
                return true;
        }
    }

    private static function compare($left, string $op, $right): bool {
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
            case 'in':
                return (string) $left === (string) $right;
            case '!=':
            case 'not_in':
                return (string) $left !== (string) $right;
            default:
                return (string) $left === (string) $right;
        }
    }
}
