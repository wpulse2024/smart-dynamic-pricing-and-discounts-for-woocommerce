<?php

namespace SmartDynamicPricing\Helpers;

class ValidateApplyDiscount
{
    public static function validate($rule, $cart_item, $cart_item_key)
    {
        // 1️⃣ Check if rule is active
        if (($rule->status ?? '') !== 'active') {
            return false;
        }

        $product_id = $cart_item['product_id'];
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }

        // 2️⃣ Handle product inclusion logic
        if (!self::passesInclusionCheck($rule, $product_id, $product)) {
            if(Arr::get($rule->product_scope, 'scopeType', '') != 'all_products') {
                return false;
            }
        }

        // 3️⃣ Handle product exclusion logic
        if (!self::passesExclusionCheck($rule, $product_id, $product)) {
            return false;
        }

        // 4️⃣ Handle user scope logic
        if (!self::passesUserScopeCheck($rule, $cart_item, $cart_item_key)) {
            return false;
        }

        // 5️⃣ Handle schedule logic
        if (!self::passesScheduleCheck($rule)) {
            return false;
        }

        // ✅ All checks passed
        return true;
    }

    public static function isValidProduct($rule, $product)
    {
        // 1️⃣ Check if rule is active
        if (($rule->status ?? '') !== 'active') {
            return false;
        }

        $product_id = $product->get_id();
        if (!$product) {
            return false;
        }

        // 2️⃣ Handle product inclusion logic
        if (!self::passesInclusionCheck($rule, $product_id, $product)) {
            if(Arr::get($rule->product_scope, 'scopeType', '') != 'all_products') {
                return false;
            }
        }

        // 3️⃣ Handle product exclusion logic
        if (!self::passesExclusionCheck($rule, $product_id, $product)) {
            return false;
        }

        // ✅ All checks passed
        return true;
    }

    /**
     * Check if product matches inclusion scope
     */
    protected static function passesInclusionCheck($rule, $product_id, $product)
    {
        $scope = $rule->product_scope ?? null;

        if (!$scope) return false;

        switch (Arr::get($scope, 'scopeType')) {
            case 'specific_products':
                return in_array($product_id, Arr::get($scope, 'inclusion.products', []), true);

            case 'product_categories':
                return self::hasIntersection(
                    $product->get_category_ids(),
                    Arr::get($scope, 'inclusion.categories', [])
                );

            case 'product_tags':
                return self::hasIntersection(
                    $product->get_tag_ids(),
                    Arr::get($scope, 'inclusion.tags', [])
                );

            default:
                return false;
        }
    }

    /**
     * Check if product violates exclusion scope (for all_products type)
     */
    protected static function passesExclusionCheck($rule, $product_id, $product)
    {
        $scope = $rule->product_scope ?? null;
        if (Arr::get($scope, 'scopeType', '') !== 'all_products') {
            return true; // only applies to all_products type
        }

        $exclusion = Arr::get($scope, 'exclusion', []) ?? null;

        if (!$exclusion) {
            return true;
        }

        switch (Arr::get($exclusion, 'type')) {
            case 'specific_products':
                return !in_array($product_id, Arr::get($exclusion, 'products', []), true);

            case 'product_categories':
                return !self::hasIntersection(
                    $product->get_category_ids(),
                    Arr::get($exclusion, 'categories', [])
                );

            case 'product_tags':
                return !self::hasIntersection(
                    $product->get_tag_ids(),
                    Arr::get($exclusion, 'tags', [])
                );

            default:
                return true;
        }
    }

    protected static function passesUserScopeCheck($rule, $cart_item, $cart_item_key)
    {
        $scope = $rule->user_scope ?? null;

        if (!$scope) return false;

        $user = wp_get_current_user();
        //check if user is not logged in and scope is not all users
        if (!$user->exists() && Arr::get($scope, 'scopeType') !== 'all_users') {
            //show an woocommerce notice
            if (!is_cart()) {
                return;
            }
            wc_add_notice(__('You must be logged in to apply this discount', 'smart-dynamic-pricing'), 'notice');
            return false;
        }
        $user_id = $user->ID;
        $user_roles = $user->roles;

        switch (Arr::get($scope, 'scopeType')) {
            case 'specific_users':
                return in_array($user_id, Arr::get($scope, 'users', []), true);

            case 'user_roles':
                return self::hasIntersection($user_roles, Arr::get($scope, 'roles', []));

            default:
                return true;
        }
    }

    protected static function passesScheduleCheck($rule)
    {
        $schedule = $rule->schedule ?? null;
        if (empty($schedule)) {
            return true; // No schedule = always active
        }
    
        $start = Arr::get($schedule, 'start');
        $end = Arr::get($schedule, 'end');
        $daysOfWeek = Arr::get($schedule, 'daysOfWeek', []);
        // $specificDates = Arr::get($schedule, 'specificDates', []); // optional future use
    
        $now = new \DateTime('now');
        $today = $now->format('Y-m-d');
        $dayOfWeek = $now->format('l'); // Monday, Tuesday, etc.

        // --- Check start & end date ---
        if ($start && $today < $start) {
            return false; // Not started yet
        }
        if ($end && $today > $end) {
            return false; // Already expired
        }
        // --- Check day of week if defined ---
        if (!empty($daysOfWeek) && !in_array($dayOfWeek, $daysOfWeek, true)) {
            return false;
        }
    
        // --- (Optional) check specific date list ---
        // if (!empty($specificDates) && !in_array($today, $specificDates, true)) {
        //     return false;
        // }
    
        return true;
    }
    

    /**
     * Utility: check if two arrays have any common values
     */
    protected static function hasIntersection(array $a, array $b)
    {
        return (bool) array_intersect($a, $b);
    }
}
