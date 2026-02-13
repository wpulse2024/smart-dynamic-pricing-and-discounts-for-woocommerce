<?php

namespace WpulsePricingRules\Helpers;

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
            $scope = self::ensureArray($rule->product_scope ?? null);
            if (Arr::get($scope, 'scopeType', '') !== 'all_products') {
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
            $scope = self::ensureArray($rule->product_scope ?? null);
            if (Arr::get($scope, 'scopeType', '') !== 'all_products') {
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
     * Validate rule for cart-level discounts (user scope + schedule only; no product scope).
     */
    public static function validateCartLevel($rule): bool
    {
        if (($rule->status ?? '') !== 'active') {
            return false;
        }
        $user = wp_get_current_user();
        $scope = self::ensureArray($rule->user_scope ?? null);
        if (!$scope) {
            return false;
        }
        if (!$user->exists() && Arr::get($scope, 'scopeType') !== 'all_users') {
            return false;
        }
        if (Arr::get($scope, 'scopeType') === 'specific_users' && !self::inArrayLoose($user->ID, Arr::get($scope, 'users', []))) {
            return false;
        }
        if (Arr::get($scope, 'scopeType') === 'user_roles' && !self::hasIntersection($user->roles, Arr::get($scope, 'roles', []))) {
            return false;
        }
        return self::passesScheduleCheck($rule);
    }

    /**
     * Check if product matches inclusion scope
     */
    protected static function passesInclusionCheck($rule, $product_id, $product)
    {
        $scope = self::ensureArray($rule->product_scope ?? null);

        if (!$scope) {
            return false;
        }

        switch (Arr::get($scope, 'scopeType')) {
            case 'all_products':
                return true;

            case 'specific_products':
                return self::inArrayLoose($product_id, Arr::get($scope, 'inclusion.products', []));

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
        $scope = self::ensureArray($rule->product_scope ?? null);
        if (Arr::get($scope, 'scopeType', '') !== 'all_products') {
            return true; // only applies to all_products type
        }

        $exclusion = Arr::get($scope, 'exclusion', []) ?? null;

        if (!$exclusion) {
            return true;
        }

        switch (Arr::get($exclusion, 'type')) {
            case 'specific_products':
                return !self::inArrayLoose($product_id, Arr::get($exclusion, 'products', []));

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
        $scope = self::ensureArray($rule->user_scope ?? null);

        if (!$scope) {
            return false;
        }

        $user = wp_get_current_user();
        // Check if user is not logged in and scope is not all users
        if (!$user->exists() && Arr::get($scope, 'scopeType') !== 'all_users') {
            if (is_cart()) {
                wc_add_notice(__('You must be logged in to apply this discount', 'wpulse-pricing-rules-for-woocommerce'), 'notice');
            }
            return false;
        }
        $user_id = $user->ID;
        $user_roles = $user->roles;

        switch (Arr::get($scope, 'scopeType')) {
            case 'specific_users':
                return self::inArrayLoose($user_id, Arr::get($scope, 'users', []));

            case 'user_roles':
                return self::hasIntersection($user_roles, Arr::get($scope, 'roles', []));

            default:
                return true;
        }
    }

    protected static function passesScheduleCheck($rule)
    {
        $schedule = self::ensureArray($rule->schedule ?? null);
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

    /**
     * Ensure value is array (handles objects from JSON/unserialize)
     */
    protected static function ensureArray($value): ?array
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }
        if (is_object($value)) {
            return json_decode(json_encode($value), true);
        }
        return null;
    }

    /**
     * Check if value exists in array with loose type comparison (handles int vs string IDs)
     */
    protected static function inArrayLoose($needle, array $haystack): bool
    {
        foreach ($haystack as $item) {
            if ((int) $item === (int) $needle) {
                return true;
            }
        }
        return false;
    }
}
