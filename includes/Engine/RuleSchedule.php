<?php

namespace WpulsePricingRules\Includes\Engine;

/**
 * Shared schedule and taxonomy helpers used by RuleEngine, Context, TargetMatcher, ProductDiscountMessage.
 */
class RuleSchedule {

    /** @var array<string, int[]> */
    private static array $term_cache = [];

    /**
     * Check whether a rule's schedule is currently active.
     *
     * @param array $rule Decoded rule array.
     */
    public static function inSchedule(array $rule): bool {
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

    /**
     * Get term IDs for a product and taxonomy with static memoization.
     *
     * @param int    $productId
     * @param string $taxonomy e.g. 'product_cat', 'product_tag'
     * @return int[]
     */
    public static function getTermIds(int $productId, string $taxonomy): array {
        $key = $productId . ':' . $taxonomy;
        if (!isset(self::$term_cache[$key])) {
            $terms = wp_get_post_terms($productId, $taxonomy);
            if (is_wp_error($terms) || !is_array($terms)) {
                self::$term_cache[$key] = [];
            } else {
                self::$term_cache[$key] = array_map(function ($t) {
                    return (int) $t->term_id;
                }, $terms);
            }
        }
        return self::$term_cache[$key];
    }

    /**
     * Clear static caches (call at the start of each cart calculation).
     */
    public static function resetCache(): void {
        self::$term_cache = [];
    }
}
