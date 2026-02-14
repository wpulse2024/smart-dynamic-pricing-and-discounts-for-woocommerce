<?php

namespace WpulsePricingRules\App\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use WpulsePricingRules\App\Models\PricingRule;

/**
 * REST controller for pricing rules – Laravel-style resource.
 */
class RulesController extends Controller {

    public function index(WP_REST_Request $request): WP_REST_Response {
        $rules = PricingRule::all();
        return $this->json($rules);
    }

    public function show(WP_REST_Request $request): WP_REST_Response {
        $id   = (int) $request['id'];
        $rule = PricingRule::find($id);
        if (!$rule) {
            return $this->error(__('Rule not found.', 'wpulse-pricing-rules-for-woocommerce'), 404);
        }
        return $this->json($rule);
    }

    public function store(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params() ?: $request->get_body_params();
        $rule   = PricingRule::create($params);
        if (!$rule) {
            return $this->error(__('Could not create rule.', 'wpulse-pricing-rules-for-woocommerce'), 422);
        }
        return $this->success('', $rule, 201);
    }

    public function update(WP_REST_Request $request): WP_REST_Response {
        $id     = (int) $request['id'];
        $rule   = PricingRule::find($id);
        if (!$rule) {
            return $this->error(__('Rule not found.', 'wpulse-pricing-rules-for-woocommerce'), 404);
        }
        $params = $request->get_json_params() ?: $request->get_body_params();
        $rule   = PricingRule::update($id, $params);
        return $this->json($rule);
    }

    public function destroy(WP_REST_Request $request): WP_REST_Response {
        $id = (int) $request['id'];
        if (!PricingRule::delete($id)) {
            return $this->error(__('Rule not found or could not be deleted.', 'wpulse-pricing-rules-for-woocommerce'), 404);
        }
        return $this->success(__('Rule deleted.', 'wpulse-pricing-rules-for-woocommerce'), null, 200);
    }
}
