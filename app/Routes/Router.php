<?php

namespace SmartDynamicPricingDiscounts\Routes;

/**
 * Router class for handling REST and admin routes
 */
class Router
{
    protected $routes = [];
    protected $groups = [];
    protected $currentGroup = '';
    protected $currentMiddleware = [];

    /** ---------------------------
     *  Basic HTTP Method Registration
     *  --------------------------- */
    public function get(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function patch(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function any(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('ANY', $path, $handler, $middleware);
    }

    /** ---------------------------
     *  Add a Route
     *  --------------------------- */
    protected function addRoute(string $method, string $path, $handler, array $middleware = []): self
    {
        $fullPath = $this->currentGroup . $path;
        $fullMiddleware = array_merge($this->currentMiddleware, $middleware);

        $this->routes[] = [
            'method'     => $method,
            'path'       => $fullPath,
            'handler'    => $handler,
            'middleware' => $fullMiddleware,
            'pattern'    => $this->convertToRegex($fullPath),
        ];

        return $this;
    }

    /** ---------------------------
     *  Route Grouping
     *  --------------------------- */
    public function group(array $attributes, callable $callback): self
    {
        $prefix = $attributes['prefix'] ?? '';
        $middleware = $attributes['middleware'] ?? [];

        $previousGroup = $this->currentGroup;
        $previousMiddleware = $this->currentMiddleware;

        $this->currentGroup = $previousGroup . $prefix;
        $this->currentMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->currentGroup = $previousGroup;
        $this->currentMiddleware = $previousMiddleware;

        return $this;
    }

    /** ---------------------------
     *  Convert route {param} to regex
     *  --------------------------- */
    protected function convertToRegex(string $path): string
    {
        $pattern = str_replace('/', '\/', $path);
        // Convert {id} → (?P<id>[^/]+)
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^\/]+)', $pattern);
        return '/^' . $pattern . '$/';
    }

    /** ---------------------------
     *  Handle incoming requests
     *  --------------------------- */
    public function handle(string $method, string $path): void
    {
        $route = $this->findRoute($method, $path);

        if (!$route) {
            $this->notFound();
            return;
        }

        // Execute middleware
        foreach ($route['middleware'] as $middleware) {
            if (!$this->executeMiddleware($middleware)) {
                return;
            }
        }

        // Execute route handler
        $this->executeHandler($route['handler'], $route['params'] ?? []);
    }

    /** ---------------------------
     *  Find matching route
     *  --------------------------- */
    protected function findRoute(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if (($route['method'] === $method || $route['method'] === 'ANY') &&
                preg_match($route['pattern'], $path, $matches)) {

                // Extract named params
                $params = [];
                if (preg_match_all('/\(\?P<([^>]+)>/', $route['pattern'], $paramNames)) {
                    foreach ($paramNames[1] as $name) {
                        $params[$name] = $matches[$name] ?? null;
                    }
                }

                $route['params'] = $params;
                return $route;
            }
        }
        return null;
    }

    /** ---------------------------
     *  Execute Middleware
     *  --------------------------- */
    protected function executeMiddleware($middleware): bool
    {
        if (is_string($middleware)) {
            $middlewareClass = "SmartDynamicPricingDiscounts\\Middleware\\{$middleware}";
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                return $middlewareInstance->handle();
            }
        } elseif (is_callable($middleware)) {
            return $middleware();
        }

        return true;
    }

    /** ---------------------------
     *  Execute Controller Handler
     *  --------------------------- */
    protected function executeHandler($handler, array $params = []): void
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controller, $method] = explode('@', $handler);
            $controllerClass = "SmartDynamicPricingDiscounts\\Controllers\\{$controller}";

            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                if (method_exists($controllerInstance, $method)) {
                    call_user_func_array([$controllerInstance, $method], $params);
                    return;
                }
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        $this->error('Handler not found', 500);
    }

    /** ---------------------------
     *  Register WordPress REST Routes
     *  --------------------------- */
    public function registerRestRoutes(string $namespace = 'my-plugin/v1'): void
    {
        add_action('rest_api_init', function() use ($namespace) {
            foreach ($this->routes as $route) {
                // Determine HTTP methods
                $methods = $route['method'] === 'ANY'
                    ? ['GET', 'POST', 'PUT', 'DELETE', 'PATCH']
                    : [$route['method']];

                // Convert /rules/{id} → rules/(?P<id>[^/]+)
                $path = ltrim($route['path'], '/');
                $path = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $path);

                register_rest_route($namespace, $path, [
                    'methods'  => $methods,
                    'callback' => function($request) use ($route) {
                        $_SERVER['REQUEST_METHOD'] = $request->get_method();
                        $_POST = $request->get_params();

                        // Extract named parameters (like id)
                        $params = $request->get_url_params();
                        $this->executeHandler($route['handler'], $params);
                    },
                    'permission_callback' => function($request) use ($route) {
                        foreach ($route['middleware'] as $middleware) {
                            if (!$this->executeMiddleware($middleware)) {
                                return false;
                            }
                        }
                        return true;
                    },
                ]);
            }
        });
    }

    /** ---------------------------
     *  Register Admin Menu Routes
     *  --------------------------- */
    public function registerAdminRoutes(): void
    {
        add_action('admin_menu', function() {
            foreach ($this->routes as $route) {
                if (strpos($route['path'], '/admin/') === 0) {
                    $pageTitle = ucwords(str_replace(['/admin/', '-', '_'], ['', ' ', ' '], $route['path']));
                    $menuSlug = 'smart-pricing' . $route['path'];

                    add_menu_page(
                        $pageTitle,
                        $pageTitle,
                        'manage_options',
                        $menuSlug,
                        function() use ($route) {
                            $this->handle('GET', $route['path']);
                        }
                    );
                }
            }
        });
    }

    /** ---------------------------
     *  Error Handlers
     *  --------------------------- */
    protected function notFound(): void
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found']);
        exit;
    }

    protected function error(string $message, int $status = 500): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }

    /** ---------------------------
     *  Helper: Get All Routes
     *  --------------------------- */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
