<?php

namespace WpulsePricingRules\Includes\Engine;

use WpulsePricingRules\Includes\Exclusions\ExclusionService;
use WpulsePricingRules\Includes\Engine\RuleSchedule;

/**
 * Matches cart line (product) to rule targets and global exclusion list.
 * Excluded products are not modified and do not count toward thresholds unless config allows.
 */
class TargetMatcher {

    /**
     * Check if a cart line (product) is matched by rule targets.
     *
     * @param array $line Context cart line (product_id, variation_id, categories, tags, ...).
     * @param array $targets Rule targets (type, products, categories, tags, include/exclude).
     * @return bool
     */
    public static function lineMatchesTargets(array $line, array $targets): bool {
        $type = $targets['type'] ?? 'all';
        $product_id = (int) ($line['product_id'] ?? 0);
        $categories = $line['categories'] ?? [];
        $tags = $line['tags'] ?? [];

        if ($type === 'all') {
            return true;
        }

        if ($type === 'products' && !empty($targets['products'])) {
            $ids = array_map('intval', (array) $targets['products']);
            $match = in_array($product_id, $ids, true);
            return self::applyExclude($match, $targets, 'products');
        }

        if ($type === 'categories' && !empty($targets['categories'])) {
            $ids = array_map('intval', (array) $targets['categories']);
            $match = !empty(array_intersect($categories, $ids));
            return self::applyExclude($match, $targets, 'categories');
        }

        if ($type === 'tags' && !empty($targets['tags'])) {
            $ids = array_map('intval', (array) $targets['tags']);
            $match = !empty(array_intersect($tags, $ids));
            return self::applyExclude($match, $targets, 'tags');
        }

        if ($type === 'variations' && !empty($targets['variations'])) {
            $variation_id = (int) ($line['variation_id'] ?? 0);
            if ($variation_id === 0) {
                return false;
            }
            $ids = array_map('intval', (array) $targets['variations']);
            $match = in_array($variation_id, $ids, true);
            return self::applyExclude($match, $targets, 'variations');
        }

        return true;
    }

    private static function applyExclude(bool $match, array $targets, string $key): bool {
        $exclude = !empty($targets['exclude']);
        if ($exclude) {
            return !$match;
        }
        return $match;
    }

    /**
     * Match a product on the product page (no cart context).
     * For variation rules on a variable product, returns true if any child variation is targeted.
     *
     * @param \WC_Product $product
     * @param array       $targets
     * @return bool
     */
    public static function productPageMatchesTargets( \WC_Product $product, array $targets ): bool {
        $type = $targets['type'] ?? 'all';

        if ( $type === 'variations' ) {
            if ( $product->is_type( 'variable' ) ) {
                $variation_ids   = array_map( 'intval', (array) $product->get_children() );
                $target_var_ids  = array_map( 'intval', (array) ( $targets['variations'] ?? [] ) );
                $match = ! empty( array_intersect( $variation_ids, $target_var_ids ) );
                return self::applyExclude( $match, $targets, 'variations' );
            }
            // Simple product on a variation-targeted rule – never matches.
            return false;
        }

        // For all other types re-use the standard line match (variation_id not needed there).
        $product_id = (int) $product->get_id();
        $line = [
            'product_id'   => $product_id,
            'variation_id' => 0,
            'categories'   => RuleSchedule::getTermIds( $product_id, 'product_cat' ),
            'tags'         => RuleSchedule::getTermIds( $product_id, 'product_tag' ),
        ];
        return self::lineMatchesTargets( $line, $targets );
    }

    /**
     * Check if product is on the global exclusion list (skip from ALL rules).
     */
    public static function isGloballyExcluded($product): bool {
        if (!$product || !is_object($product)) {
            return false;
        }
        return ExclusionService::isWCProductExcluded($product);
    }

    /**
     * Check if product is excluded by rule-level exclusions.
     */
    public static function isExcludedByRule($product, array $exclusions): bool {
        if (empty($exclusions['enabled']) || empty($exclusions['ids'])) {
            return false;
        }
        $type = $exclusions['type'] ?? 'products';
        $ids = array_map('intval', (array) $exclusions['ids']);
        $product_id = $product->get_id();
        if ($type === 'products') {
            return in_array($product_id, $ids, true);
        }
        if ($type === 'categories') {
            $term_ids = RuleSchedule::getTermIds($product_id, 'product_cat');
            return !empty(array_intersect($term_ids, $ids));
        }
        if ($type === 'tags') {
            $term_ids = RuleSchedule::getTermIds($product_id, 'product_tag');
            return !empty(array_intersect($term_ids, $ids));
        }
        return false;
    }

}
