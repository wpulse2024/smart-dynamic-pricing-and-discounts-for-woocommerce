<?php

namespace WpulsePricingRules\Helpers;

/**
 * Helper functions
 */
class Helper
{
    /**
     * Format currency
     */
    public static function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        return number_format($amount, 2) . ' ' . $currency;
    }

    /**
     * Format date
     */
    public static function formatDate(string $date, string $format = 'Y-m-d'): string
    {
        return gmdate($format, strtotime($date));
    }

    /**
     * Generate random string
     */
    public static function randomString(int $length = 10): string
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    /**
     * Sanitize input
     */
    public static function sanitize($input): string
    {
        return sanitize_text_field($input);
    }

    /**
     * Validate email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get current timestamp
     */
    public static function now(): string
    {
        return current_time('mysql');
    }

    /**
     * Convert array to object
     */
    public static function arrayToObject(array $array): object
    {
        return json_decode(json_encode($array));
    }

    /**
     * Convert object to array
     */
    public static function objectToArray($object): array
    {
        return json_decode(json_encode($object), true);
    }

    /**
     * Get the base price for discount calculation. Always uses regular price (never sale price).
     * For variations with empty regular price, falls back to parent product's regular price.
     */
    public static function get_base_price_for_discount($product): float
    {
        $regular = (float) $product->get_regular_price();
        if ($regular > 0) {
            return $regular;
        }
        // Variation may inherit from parent
        if (method_exists($product, 'get_parent_id') && $product->get_parent_id()) {
            $parent = wc_get_product($product->get_parent_id());
            if ($parent) {
                return (float) $parent->get_regular_price();
            }
        }
        return 0;
    }

    public static function calculate_discounted_price($base_price, $discount_type, $discount_value)
    {
        $base_price = (float) $base_price;
        $discount_value = (float) $discount_value;

        if ($discount_type === 'percentage') {
            return $base_price * (1 - ($discount_value / 100));
        }
        // Support both 'fixed' and 'fixed_amount' (Vue form uses fixed_amount)
        if (in_array($discount_type, ['fixed', 'fixed_amount'], true)) {
            return max(0, $base_price - $discount_value);
        }
        return $base_price;
    }
}
