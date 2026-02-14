<?php

namespace WpulsePricingRules\App\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use WpulsePricingRules\Includes\Admin\Templates;

/**
 * REST controller for templates and scratch types (for modal UI).
 */
class TemplatesController extends Controller {

    /**
     * GET templates – returns list for modal grid (id, title, icon, description).
     */
    public function index(WP_REST_Request $request): WP_REST_Response {
        $all = Templates::all();
        $list = [];
        foreach ($all as $t) {
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
        return $this->json([
            'templates' => $list,
            'scratch'   => $scratch,
        ]);
    }
}
