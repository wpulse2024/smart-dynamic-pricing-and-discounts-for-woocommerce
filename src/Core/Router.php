<?php

namespace WpulsePricingRules\Core;

/**
 * Laravel-style router: REST API routes + optional rewrite rules.
 */
class Router {

    private const REST_NAMESPACE = 'wpulse-pricing-rules/v1';

    /** @var array<int, array{method: string, uri: string, controller: string, action: string}> */
    private array $restRoutes = [];

    /** @var array<int, array{regex: string, query: string, controller: string, action: string}> */
    private array $rewriteRoutes = [];

    /**
     * Register a GET REST route.
     */
    public function get(string $uri, string $controller, string $action = 'index'): self {
        return $this->addRestRoute('GET', $uri, $controller, $action);
    }

    /**
     * Register a POST REST route.
     */
    public function post(string $uri, string $controller, string $action = 'store'): self {
        return $this->addRestRoute('POST', $uri, $controller, $action);
    }

    /**
     * Register a PUT REST route.
     */
    public function put(string $uri, string $controller, string $action = 'update'): self {
        return $this->addRestRoute('PUT', $uri, $controller, $action);
    }

    /**
     * Register a PATCH REST route.
     */
    public function patch(string $uri, string $controller, string $action = 'update'): self {
        return $this->addRestRoute('PATCH', $uri, $controller, $action);
    }

    /**
     * Register a DELETE REST route.
     */
    public function delete(string $uri, string $controller, string $action = 'destroy'): self {
        return $this->addRestRoute('DELETE', $uri, $controller, $action);
    }

    private function addRestRoute(string $method, string $uri, string $controller, string $action): self {
        $this->restRoutes[] = [
            'method'     => $method,
            'uri'        => trim($uri, '/'),
            'controller' => $controller,
            'action'     => $action,
        ];
        return $this;
    }

    /**
     * Register all REST routes with WordPress.
     */
    public function registerRestRoutes(): void {
        foreach ($this->restRoutes as $route) {
            $controller = $route['controller'];
            $action     = $route['action'];
            register_rest_route(self::REST_NAMESPACE, '/' . $route['uri'], [
                'methods'             => $route['method'],
                'callback'            => function ($request) use ($controller, $action) {
                    return $this->callController($controller, $action, $request);
                },
                'permission_callback' => [$this, 'restPermission'],
                'args'                => [],
            ]);
        }
    }

    /**
     * Permission callback: validates wp_rest nonce and capability.
     */
    public function restPermission(\WP_REST_Request $request): bool {
        $nonce = $request->get_header('X-WP-Nonce') ?: ( $request->get_param('_wpnonce') ?? '' );
        if ( ! $nonce || wp_verify_nonce( $nonce, 'wp_rest' ) === false ) {
            return false;
        }
        return current_user_can( 'manage_woocommerce' );
    }

    /**
     * Call controller@action with request.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    private function callController(string $controller, string $action, $request) {
        if ( strpos( $controller, '\\' ) === false ) {
            $controller = 'WpulsePricingRules\\App\\Http\\Controllers\\' . $controller;
        }
        if (!class_exists($controller)) {
            return new \WP_Error('controller_missing', __('Controller not found.', 'wpulse-pricing-rules-for-woocommerce'), ['status' => 500]);
        }
        $instance = new $controller();
        if (!method_exists($instance, $action)) {
            return new \WP_Error('action_missing', __('Action not found.', 'wpulse-pricing-rules-for-woocommerce'), ['status' => 500]);
        }
        return $instance->{$action}($request);
    }

    /**
     * Add a rewrite rule (Laravel-style web routes if needed).
     */
    public function addRewrite(string $regex, string $query, string $controller, string $action): self {
        $this->rewriteRoutes[] = [
            'regex'      => $regex,
            'query'      => $query,
            'controller' => $controller,
            'action'     => $action,
        ];
        return $this;
    }

    /**
     * Register rewrite rules with WordPress.
     */
    public function dispatchRewriteRules(): void {
        foreach ($this->rewriteRoutes as $route) {
            add_rewrite_rule($route['regex'], $route['query'], 'top');
        }
    }
}
