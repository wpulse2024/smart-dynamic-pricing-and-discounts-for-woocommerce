<?php

namespace WpulsePricingRules\Includes\Admin;

use WpulsePricingRules\Includes\DB\RulesRepository;

/**
 * WP AJAX endpoints (nonce + manage_woocommerce).
 * Alternative to REST for templates and create-from-template.
 */
class Ajax {

    const NONCE_ACTION = 'wpulse_templates';

    public static function register(): void {
        add_action('wp_ajax_wpulse_get_templates', [__CLASS__, 'getTemplates']);
        add_action('wp_ajax_wpulse_create_rule_from_template', [__CLASS__, 'createRuleFromTemplate']);
    }

    public static function getTemplates(): void {
        self::checkRequest();
        $list = [];
        foreach (Templates::all() as $t) {
            $list[] = [
                'id'          => $t['id'],
                'title'       => $t['title'],
                'icon'        => $t['icon'],
                'description' => $t['description'] ?? '',
            ];
        }
        $scratch = [];
        foreach (Templates::scratchTypes() as $key => $label) {
            $scratch[] = ['id' => $key, 'label' => $label];
        }
        wp_send_json_success([
            'templates' => $list,
            'scratch'   => $scratch,
        ]);
    }

    public static function createRuleFromTemplate(): void {
        self::checkRequest();
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';
        $scratch_type = isset($_POST['scratch_type']) ? sanitize_key($_POST['scratch_type']) : '';

        if ($template_id !== '') {
            $result = RulesRepository::createFromTemplate($template_id);
        } elseif ($scratch_type !== '') {
            $result = RulesRepository::createFromScratch($scratch_type);
        } else {
            wp_send_json_error(['message' => __('Provide template_id or scratch_type.', 'wpulse-pricing-rules-for-woocommerce')]);
        }

        if (!$result) {
            wp_send_json_error(['message' => __('Could not create rule from template.', 'wpulse-pricing-rules-for-woocommerce')]);
        }
        wp_send_json_success($result);
    }

    private static function checkRequest(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Permission denied.', 'wpulse-pricing-rules-for-woocommerce')], 403);
        }
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_send_json_error(['message' => __('Security check failed.', 'wpulse-pricing-rules-for-woocommerce')], 403);
        }
    }
}
