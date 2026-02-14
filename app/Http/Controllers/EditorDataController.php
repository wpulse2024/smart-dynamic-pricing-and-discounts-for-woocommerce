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
        $search = is_string($search) ? trim($search) : '';
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
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
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
     * GET editor/tags – WooCommerce product tags.
     */
    public function tags(WP_REST_Request $request): WP_REST_Response {
        $terms = get_terms([
            'taxonomy'   => 'product_tag',
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
        $search = is_string($search) ? trim($search) : '';
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
                'price' => $product->get_price(),
            ];
        }
        return $this->json($list);
    }
}
