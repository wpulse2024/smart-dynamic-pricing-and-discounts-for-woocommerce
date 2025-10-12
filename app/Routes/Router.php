<?php

namespace SmartDynamicPricingDiscounts\Routes;

/**
 * Simple Router class for handling REST and admin routes
 */
class Router
{
    /**
     * Registered routes
     */
    protected $routes = [];

    /**
     * Route groups
     */
    protected $groups = [];

    /**
     * Current group prefix
     */
    protected $currentGroup = '';

    /**
     * Current group middleware
     */
    protected $currentMiddleware = [];

    /**
     * Register a GET route
     */
    public function get(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Register a PATCH route
     */
    public function patch(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    /**
     * Register a route for any HTTP method
     */
    public function any(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('ANY', $path, $handler, $middleware);
    }

    /**
     * Add a route
     */
    protected function addRoute(string $method, string $path, $handler, array $middleware = []): self
    {
        $fullPath = $this->currentGroup . $path;
        $fullMiddleware = array_merge($this->currentMiddleware, $middleware);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $fullMiddleware,
            'pattern' => $this->convertToRegex($fullPath)
        ];
        
        return $this;
    }

    /**
     * Create a route group
     */
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

    /**
     * Convert route path to regex pattern
     */
    protected function convertToRegex(string $path): string
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $path);
        
        // Convert route parameters to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * Handle the request
     */
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

    /**
     * Find matching route
     */
    protected function findRoute(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if (($route['method'] === $method || $route['method'] === 'ANY') && 
                preg_match($route['pattern'], $path, $matches)) {
                
                // Extract parameters
                $params = [];
                if (preg_match_all('/\{([^}]+)\}/', $route['path'], $paramNames)) {
                    for ($i = 1; $i < count($matches); $i++) {
                        $params[$paramNames[1][$i - 1]] = $matches[$i];
                    }
                }
                
                $route['params'] = $params;
                return $route;
            }
        }
        
        return null;
    }

    /**
     * Execute middleware
     */
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

    /**
     * Execute route handler
     */
    protected function executeHandler($handler, array $params = []): void
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            // Controller@method format
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

    /**
     * Register WordPress REST API routes
     */
    public function registerRestRoutes(string $namespace = 'my-plugin/v1'): void
    {
        add_action('rest_api_init', function() use ($namespace) {
            foreach ($this->routes as $route) {
                if ($route['method'] === 'ANY') {
                    $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
                } else {
                    $methods = [$route['method']];
                }
                
                register_rest_route($namespace, $route['path'], [
                    'methods' => $methods,
                    'callback' => function($request) use ($route) {
                        // Set request data for controllers
                        $_SERVER['REQUEST_METHOD'] = $request->get_method();
                        $_POST = $request->get_params();
                        
                        $this->handle($request->get_method(), $route['path']);
                    },
                    'permission_callback' => function($request) use ($route) {
                        // Execute middleware for permission check
                        foreach ($route['middleware'] as $middleware) {
                            if (!$this->executeMiddleware($middleware)) {
                                return false;
                            }
                        }
                        return true;
                    }
                ]);
            }
        });
    }

    /**
     * Register admin menu routes
     */
    public function registerAdminRoutes(): void
    {
        add_action('admin_menu', function() {
            foreach ($this->routes as $route) {
                if (strpos($route['path'], '/admin/') === 0) {
                    $pageTitle = ucwords(str_replace(['/admin/', '-', '_'], ['', ' ', ' '], $route['path']));
                    $menuSlug = 'smart-dynamic-pricing-and-discounts-for-woocommerce' . $route['path'];
                    
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

    /**
     * Handle 404 Not Found
     */
    protected function notFound(): void
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found']);
        exit;
    }

    /**
     * Handle errors
     */
    protected function error(string $message, int $status = 500): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
