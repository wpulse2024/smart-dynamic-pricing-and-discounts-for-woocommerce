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

    public static function calculate_discounted_price($base_price, $discount_type, $discount_value)
    {
        if ($discount_type === 'percentage') {
            return $base_price * (1 - ($discount_value / 100));
        }
        if ($discount_type === 'fixed') {
            return max(0, $base_price - $discount_value);
        }
        return $base_price;
    }
}
