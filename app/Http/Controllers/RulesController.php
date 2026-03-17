<?php

namespace WpulsePricingRules\App\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WpulsePricingRules\Includes\DB\RulesRepository;

/**
 * REST controller for pricing rules – Laravel-style resource.
 */
class RulesController extends Controller {

    public function index(WP_REST_Request $request): WP_REST_Response {
        $rules = RulesRepository::all('priority', 'DESC');
        return $this->json($rules);
    }

    public function show(WP_REST_Request $request): WP_REST_Response {
        $id   = (int) $request['id'];
        $row  = RulesRepository::findWithDecodedRule($id);
        if (!$row) {
            return $this->error(__('Rule not found.', 'wpulse-pricing-rules-for-woocommerce'), 404);
        }
        return $this->json($row);
    }

    public function store(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params() ?: $request->get_body_params();
        $name   = isset($params['name']) ? sanitize_text_field($params['name']) : '';
        $type   = isset($params['type']) ? sanitize_key($params['type']) : 'quantity_discount';
        $rule   = isset($params['rule']) && is_array($params['rule']) ? $params['rule'] : [];
        $id     = RulesRepository::insert([
            'name'   => $name,
            'type'   => $type,
            'status' => 'draft',
            'priority' => isset($params['priority']) ? (int) $params['priority'] : 10,
            'rule'   => $rule,
        ]);
        if (!$id) {
            return $this->error(__('Could not create rule.', 'wpulse-pricing-rules-for-woocommerce'), 422);
        }
        $row = RulesRepository::findWithDecodedRule($id);
        return $this->success('', $row, 201);
    }

    /**
     * POST rules/from-template – create draft from template_id or scratch_type.
     */
    public function createFromTemplate(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $params = $request->get_json_params() ?: $request->get_body_params();
        $template_id = isset($params['template_id']) ? sanitize_text_field($params['template_id']) : '';
        $scratch_type = isset($params['scratch_type']) ? sanitize_key($params['scratch_type']) : '';

        if ($template_id !== '') {
            $result = RulesRepository::createFromTemplate($template_id);
        } elseif ($scratch_type !== '') {
            $result = RulesRepository::createFromScratch($scratch_type);
        } else {
            return new WP_Error('invalid_params', __('Provide template_id or scratch_type.', 'wpulse-pricing-rules-for-woocommerce'), ['status' => 400]);
        }

        if (!$result) {
            return new WP_Error('create_failed', __('Could not create rule from template.', 'wpulse-pricing-rules-for-woocommerce'), ['status' => 422]);
        }
        return $this->json($result);
    }

    public function update(WP_REST_Request $request): WP_REST_Response {
        $id     = (int) $request['id'];
        $row    = RulesRepository::find($id);
        if (!$row) {
            return $this->error(__('Rule not found.', 'wpulse-pricing-rules-for-woocommerce'), 404);
        }
        $params = $request->get_json_params() ?: $request->get_body_params();
        $update = [];
        if (array_key_exists('name', $params)) {
            $update['name'] = sanitize_text_field($params['name']);
        }
        if (array_key_exists('type', $params)) {
            $update['type'] = sanitize_key($params['type']);
        }
        if (array_key_exists('status', $params)) {
            $update['status'] = in_array($params['status'], ['draft', 'active', 'disabled'], true) ? $params['status'] : $row['status'];
        }
        if (array_key_exists('priority', $params)) {
            $update['priority'] = (int) $params['priority'];
        }
        if (isset($params['rule']) && is_array($params['rule'])) {
            $update['rule'] = $params['rule'];
        }
        RulesRepository::update($id, $update);
        $updated = RulesRepository::findWithDecodedRule($id);
        return $this->json($updated);
    }

    public function destroy(WP_REST_Request $request): WP_REST_Response {
        $id = (int) $request['id'];
        if (!RulesRepository::delete($id)) {
            return $this->error(__('Rule not found or could not be deleted.', 'wpulse-pricing-rules-for-woocommerce'), 404);
        }
        return $this->success(__('Rule deleted.', 'wpulse-pricing-rules-for-woocommerce'), null, 200);
    }
}
