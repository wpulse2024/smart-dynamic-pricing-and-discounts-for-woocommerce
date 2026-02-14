<?php

namespace WpulsePricingRules\Routes;

use WpulsePricingRules\Core\Plugin;

/**
 * Laravel-style Route facade – use in routes/api.php and routes/web.php.
 *
 * Usage:
 *   Route::get('rules', [RulesController::class, 'index']);
 *   Route::post('rules', [RulesController::class, 'store']);
 *   Route::get('rules/{id}', [RulesController::class, 'show']);
 *   Route::resource('rules', RulesController::class);
 */
class Route {

    private static function router(): \WpulsePricingRules\Core\Router {
        return Plugin::getInstance()->getRouter();
    }

    public static function get(string $uri, $action, string $actionMethod = 'index'): void {
        self::mapAction('GET', $uri, $action, $actionMethod);
    }

    public static function post(string $uri, $action, string $actionMethod = 'store'): void {
        self::mapAction('POST', $uri, $action, $actionMethod);
    }

    public static function put(string $uri, $action, string $actionMethod = 'update'): void {
        self::mapAction('PUT', $uri, $action, $actionMethod);
    }

    public static function patch(string $uri, $action, string $actionMethod = 'update'): void {
        self::mapAction('PATCH', $uri, $action, $actionMethod);
    }

    public static function delete(string $uri, $action, string $actionMethod = 'destroy'): void {
        self::mapAction('DELETE', $uri, $action, $actionMethod);
    }

    /**
     * @param string $method
     * @param string $uri Laravel-style URI, e.g. 'rules' or 'rules/{id}' (converted to WP regex)
     * @param array|string $action [Controller::class, 'method'] or Controller::class for resource
     * @param string $actionMethod Used when $action is controller class string
     */
    private static function mapAction(string $method, string $uri, $action, string $actionMethod): void {
        $controller = is_array($action) ? $action[0] : $action;
        $methodName = is_array($action) ? $action[1] : $actionMethod;
        if (is_object($controller)) {
            $controller = get_class($controller);
        }
        $wpUri = self::laravelUriToWpRegex($uri);
        $router = self::router();
        switch (strtoupper($method)) {
            case 'GET':
                $router->get($wpUri, $controller, $methodName);
                break;
            case 'POST':
                $router->post($wpUri, $controller, $methodName);
                break;
            case 'PUT':
                $router->put($wpUri, $controller, $methodName);
                break;
            case 'PATCH':
                $router->patch($wpUri, $controller, $methodName);
                break;
            case 'DELETE':
                $router->delete($wpUri, $controller, $methodName);
                break;
        }
    }

    /**
     * Convert Laravel-style 'rules/{id}' to WordPress REST regex 'rules/(?P<id>\d+)'
     */
    private static function laravelUriToWpRegex(string $uri): string {
        $uri = trim($uri, '/');
        return preg_replace_callback(
            '#\{(\w+)\}#',
            static function ($m) {
                return '(?P<' . $m[1] . '>\d+)';
            },
            $uri
        );
    }

    /**
     * Register REST resource routes (index, store, show, update, destroy).
     */
    public static function resource(string $uri, string $controller): void {
        $base = trim($uri, '/');
        self::get($base, $controller, 'index');
        self::post($base, $controller, 'store');
        self::get($base . '/{id}', $controller, 'show');
        self::put($base . '/{id}', $controller, 'update');
        self::delete($base . '/{id}', $controller, 'destroy');
    }
}
