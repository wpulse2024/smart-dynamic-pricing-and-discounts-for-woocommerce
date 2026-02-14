<?php

namespace WpulsePricingRules\App\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Base controller – Laravel-style REST responses.
 */
abstract class Controller {

    /**
     * Return JSON success response.
     *
     * @param mixed $data
     * @param int   $status
     * @return WP_REST_Response
     */
    protected function json($data, int $status = 200): WP_REST_Response {
        return new WP_REST_Response($data, $status);
    }

    /**
     * Return success message.
     *
     * @param string $message
     * @param mixed  $data
     * @param int    $status
     * @return WP_REST_Response
     */
    protected function success(string $message = '', $data = null, int $status = 200): WP_REST_Response {
        $body = ['success' => true];
        if ($message !== '') {
            $body['message'] = $message;
        }
        if ($data !== null) {
            $body['data'] = $data;
        }
        return new WP_REST_Response($body, $status);
    }

    /**
     * Return error response.
     *
     * @param string $message
     * @param int    $status
     * @return WP_REST_Response
     */
    protected function error(string $message, int $status = 400): WP_REST_Response {
        return new WP_REST_Response([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
