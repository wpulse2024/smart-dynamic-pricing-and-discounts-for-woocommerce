<?php

namespace WpulsePricingRules\Includes\Exclusions;

/**
 * Service to check if a product is on the global exclusion list.
 * Used by the pricing engine before applying ANY rule to a line item.
 */
class ExclusionService {

    /** @var array<string, array<int, true>>|null Cache: type => [ object_id => true ] */
    private static $cache = null;

    /**
     * Check if a product ID is excluded (global list).
     */
    public static function isProductExcluded(int $productId): bool {
        if ($productId <= 0) {
            return false;
        }
        self::ensureCache();
        return isset(self::$cache['product'][$productId]);
    }

    /**
     * Check if a category (term) ID is excluded (global list).
     */
    public static function isCategoryExcluded(int $categoryId): bool {
        if ($categoryId <= 0) {
            return false;
        }
        self::ensureCache();
        return isset(self::$cache['category'][$categoryId]);
    }

    /**
     * Check if a tag (term) ID is excluded (global list).
     */
    public static function isTagExcluded(int $tagId): bool {
        if ($tagId <= 0) {
            return false;
        }
        self::ensureCache();
        return isset(self::$cache['tag'][$tagId]);
    }

    /**
     * Check if a WooCommerce product is excluded by the global exclusion list.
     * Returns true if the product itself, or any of its categories, or any of its tags
     * is in the exclusion list – in that case the pricing engine must not apply any rule to this product.
     */
    public static function isWCProductExcluded($product): bool {
        if (!$product || !is_object($product)) {
            return false;
        }
        $product_id = $product->get_id();
        if (self::isProductExcluded($product_id)) {
            return true;
        }
        $category_ids = self::getProductTermIds($product_id, 'product_cat');
        foreach ($category_ids as $cat_id) {
            if (self::isCategoryExcluded($cat_id)) {
                return true;
            }
        }
        $tag_ids = self::getProductTermIds($product_id, 'product_tag');
        foreach ($tag_ids as $tag_id) {
            if (self::isTagExcluded($tag_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reset in-memory cache (e.g. after admin adds/removes exclusions in same request).
     */
    public static function resetCache(): void {
        self::$cache = null;
    }

    private static function ensureCache(): void {
        if (self::$cache !== null) {
            return;
        }
        $rows = ExclusionRepository::getAll();
        self::$cache = [
            'product'  => [],
            'category' => [],
            'tag'      => [],
        ];
        foreach ($rows as $row) {
            $type = $row['exclusion_type'];
            $id   = (int) $row['object_id'];
            if (isset(self::$cache[$type])) {
                self::$cache[$type][$id] = true;
            }
        }
    }

    private static function getProductTermIds(int $productId, string $taxonomy): array {
        $terms = wp_get_post_terms($productId, $taxonomy);
        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }
        return array_map(function ($t) {
            return (int) $t->term_id;
        }, $terms);
    }
}
