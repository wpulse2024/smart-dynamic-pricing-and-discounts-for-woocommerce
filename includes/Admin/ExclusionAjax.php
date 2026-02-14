<?php

namespace WpulsePricingRules\Includes\Admin;

use WpulsePricingRules\Includes\Exclusions\ExclusionRepository;

/**
 * AJAX handlers for exclusion list (nonce + manage_woocommerce).
 */
class ExclusionAjax {

    const NONCE_ACTION = 'wpulse_exclusion';

    /**
     * Add exclusion(s). Expects exclusion_type (product|category|tag) and object_ids (array or single id).
     */
    public static function addExclusion(): void {
        self::checkRequest();
        $type = isset($_POST['exclusion_type']) ? sanitize_key($_POST['exclusion_type']) : '';
        $ids  = isset($_POST['object_ids']) ? $_POST['object_ids'] : (isset($_POST['object_id']) ? [$_POST['object_id']] : []);
        if (!in_array($type, ['product', 'category', 'tag'], true)) {
            wp_send_json_error(['message' => __('Invalid exclusion type.', 'wpulse-pricing-rules-for-woocommerce')]);
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        $added = 0;
        foreach ($ids as $object_id) {
            if (ExclusionRepository::add($type, $object_id) !== null) {
                $added++;
            }
        }
        \WpulsePricingRules\Includes\Exclusions\ExclusionService::resetCache();
        wp_send_json_success([
            'message' => sprintf(
                /* translators: %d: number of items */
                _n('%d exclusion added.', '%d exclusions added.', $added, 'wpulse-pricing-rules-for-woocommerce'),
                $added
            ),
            'added'   => $added,
        ]);
    }

    /**
     * Delete one exclusion by id.
     */
    public static function deleteExclusion(): void {
        self::checkRequest();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            wp_send_json_error(['message' => __('Invalid id.', 'wpulse-pricing-rules-for-woocommerce')]);
        }
        if (!ExclusionRepository::delete($id)) {
            wp_send_json_error(['message' => __('Could not delete exclusion.', 'wpulse-pricing-rules-for-woocommerce')]);
        }
        \WpulsePricingRules\Includes\Exclusions\ExclusionService::resetCache();
        wp_send_json_success(['message' => __('Exclusion removed.', 'wpulse-pricing-rules-for-woocommerce')]);
    }

    /**
     * Get full exclusion list with names (for UI refresh).
     */
    public static function getExclusions(): void {
        self::checkRequest();
        $list = ExclusionPage::getListWithNames();
        wp_send_json_success($list);
    }

    /**
     * Search products for selector (search param).
     */
    public static function searchProducts(): void {
        self::checkRequest();
        $search   = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
        $per_page = min(200, max(5, (int) (isset($_GET['per_page']) ? $_GET['per_page'] : 50)));
        $q        = new \WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'orderby'        => 'title',
            'order'          => 'ASC',
            's'              => $search,
        ]);
        $list = [];
        foreach ($q->posts as $p) {
            $product = wc_get_product($p->ID);
            if (!$product) {
                continue;
            }
            $list[] = [
                'id'   => $product->get_id(),
                'name' => $product->get_name(),
            ];
        }
        wp_send_json_success($list);
    }

    /**
     * Search categories (product_cat) for selector.
     */
    public static function searchCategories(): void {
        self::checkRequest();
        $search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
        $args   = [
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ];
        if ($search !== '') {
            $args['search'] = $search;
        }
        $terms = get_terms($args);
        if (is_wp_error($terms)) {
            wp_send_json_success([]);
        }
        $list = [];
        foreach ($terms as $term) {
            $list[] = [
                'id'   => (int) $term->term_id,
                'name' => $term->name,
            ];
        }
        wp_send_json_success($list);
    }

    /**
     * Search tags (product_tag) for selector.
     */
    public static function searchTags(): void {
        self::checkRequest();
        $search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
        $args   = [
            'taxonomy'   => 'product_tag',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ];
        if ($search !== '') {
            $args['search'] = $search;
        }
        $terms = get_terms($args);
        if (is_wp_error($terms)) {
            wp_send_json_success([]);
        }
        $list = [];
        foreach ($terms as $term) {
            $list[] = [
                'id'   => (int) $term->term_id,
                'name' => $term->name,
            ];
        }
        wp_send_json_success($list);
    }

    private static function checkRequest(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Permission denied.', 'wpulse-pricing-rules-for-woocommerce')], 403);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_send_json_error(['message' => __('Security check failed.', 'wpulse-pricing-rules-for-woocommerce')], 403);
        }
    }
}
