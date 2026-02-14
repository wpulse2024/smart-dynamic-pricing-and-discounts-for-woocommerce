<?php

namespace WpulsePricingRules\Includes\Admin;

use WpulsePricingRules\Includes\DB\RulesRepository;

/**
 * Edit Rule admin page – WP admin form (name, status, priority, schedule + tabs).
 */
class EditRulePage {

    public static function register(): void {
        add_submenu_page(
            'wpulse-pricing-rules',
            __('Edit Rule', 'wpulse-pricing-rules-for-woocommerce'),
            __('Edit Rule', 'wpulse-pricing-rules-for-woocommerce'),
            'manage_woocommerce',
            'wpulse-pricing-rules-edit',
            [__CLASS__, 'render']
        );
    }

    public static function render(): void {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=wpulse-pricing-rules'));
            exit;
        }
        // Redirect to Vue app rule editor (hash route)
        wp_safe_redirect(admin_url('admin.php?page=wpulse-pricing-rules') . '#/rules/edit/' . $id);
        exit;
    }

}
