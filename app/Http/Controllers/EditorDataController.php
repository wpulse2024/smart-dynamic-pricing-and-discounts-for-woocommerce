<?php

namespace WpulsePricingRules\App\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;

/**
 * REST controller for rule editor dropdown data: roles, users, categories, products.
 */
class EditorDataController extends Controller {

    /**
     * GET editor/roles – list of WordPress role names for multiselect.
     */
    public function roles(WP_REST_Request $request): WP_REST_Response {
        $wp_roles = wp_roles();
        $list = [];
        foreach ($wp_roles->roles as $role_key => $role) {
            $list[] = [
                'id'   => $role_key,
                'name' => $role['name'] ?? $role_key,
            ];
        }
        return $this->json($list);
    }

    /**
     * GET editor/users – search users (search query param).
     */
    public function users(WP_REST_Request $request): WP_REST_Response {
        $search = $request->get_param('search');
        $search = is_string($search) ? sanitize_text_field(wp_unslash(trim($search))) : '';
        $per_page = min(50, max(5, (int) $request->get_param('per_page') ?: 20));
        $args = [
            'number'   => $per_page,
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ];
        if ($search !== '') {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
        }
        $user_query = new \WP_User_Query($args);
        $users = $user_query->get_results();
        $list = [];
        foreach ($users as $user) {
            $list[] = [
                'id'    => (int) $user->ID,
                'name'  => $user->display_name ?: $user->user_login,
                'email' => $user->user_email,
            ];
        }
        return $this->json($list);
    }

    /**
     * GET editor/categories – WooCommerce product categories.
     */
    public function categories(WP_REST_Request $request): WP_REST_Response {
        return $this->getTaxonomyOptions('product_cat');
    }

    /**
     * GET editor/tags – WooCommerce product tags.
     */
    public function tags(WP_REST_Request $request): WP_REST_Response {
        return $this->getTaxonomyOptions('product_tag');
    }

    private function getTaxonomyOptions(string $taxonomy): WP_REST_Response {
        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        if (is_wp_error($terms)) {
            return $this->json([]);
        }
        $list = [];
        foreach ($terms as $term) {
            $list[] = [
                'id'   => (int) $term->term_id,
                'name' => $term->name,
            ];
        }
        return $this->json($list);
    }

    /**
     * GET editor/products – search products (search query param).
     */
    public function products(WP_REST_Request $request): WP_REST_Response {
        $search = $request->get_param('search');
        $search = is_string($search) ? sanitize_text_field(wp_unslash(trim($search))) : '';
        $per_page = min(50, max(5, (int) $request->get_param('per_page') ?: 20));
        $q = new \WP_Query([
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
                'id'    => $product->get_id(),
                'name'  => $product->get_name(),
            ];
        }
        return $this->json($list);
    }

    /**
     * GET editor/variable-products – search only variable products (for variation targeting).
     */
    public function variableProducts(WP_REST_Request $request): WP_REST_Response {
        $search = $request->get_param('search');
        $search = is_string($search) ? sanitize_text_field(wp_unslash(trim($search))) : '';
        $per_page = min(50, max(5, (int) $request->get_param('per_page') ?: 20));
        $q = new \WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'orderby'        => 'title',
            'order'          => 'ASC',
            's'              => $search,
            'tax_query'      => [
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => ['variable'],
                ],
            ],
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
        return $this->json($list);
    }

    /**
     * GET editor/variations – get all variations of a variable product.
     * Requires product_id query param.
     */
    public function variations(WP_REST_Request $request): WP_REST_Response {
        $product_id = (int) $request->get_param('product_id');
        if (!$product_id) {
            return $this->json([]);
        }
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_type('variable')) {
            return $this->json([]);
        }
        $variation_ids = $product->get_children();
        $list = [];
        foreach ($variation_ids as $variation_id) {
            $variation = wc_get_product($variation_id);
            if (!$variation || !$variation->exists()) {
                continue;
            }
            $attributes = $variation->get_variation_attributes();
            $attr_parts = [];
            foreach ($attributes as $attr_name => $attr_value) {
                if ($attr_value !== '') {
                    $attr_parts[] = $attr_value;
                }
            }
            $label = $product->get_name();
            if (!empty($attr_parts)) {
                $label .= ' – ' . implode(', ', $attr_parts);
            }
            $list[] = [
                'id'   => $variation->get_id(),
                'name' => $label,
            ];
        }
        return $this->json($list);
    }
}
