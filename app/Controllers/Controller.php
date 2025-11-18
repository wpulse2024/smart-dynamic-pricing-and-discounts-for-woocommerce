<?php

namespace SmartPricing\Controllers;

use ReflectionClass;

/**
 * Base Controller class
 */
abstract class Controller
{
    /**
     * The sanitized request data
     */
    protected $request = [];

    /**
     * Auto-detected model instance
     */
    protected $model = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resolveModel();
        $rawData       = $this->getRequestData();
        $this->request = $this->sanitize($rawData);
    }

    /**
     * Automatically resolve the model based on controller name
     */
    protected function resolveModel(): void
    {
        // Example: RuleController → Rule
        $class      = (new ReflectionClass($this))->getShortName();
        $modelName  = str_replace('Controller', '', $class);
        $modelClass = "SmartPricing\\Models\\{$modelName}";

        if (class_exists($modelClass)) {
            $this->model = new $modelClass();
        }
    }

    /**
     * Get allowed fields only from model $fillable
     */
    protected function getAllowedFields(): array
    {
        if ($this->model && method_exists($this->model, 'getFillable')) {
            return $this->model->getFillable();
        }
        return [];
    }

    /**
     * Get request data ONLY for allowed fields
     */
    protected function getRequestData(): array
    {
        if (!current_user_can('manage_options')) {
            $this->error('Unauthorized', 401);
        }

        $allowed = $this->getAllowedFields();
        $data    = [];

        // Collect raw inputs
        $json  = json_decode(file_get_contents('php://input'), true) ?? [];
        $input = array_merge($_GET, $_POST, $json);

        // Only capture allowed keys (WordPress Review Team requirement)
        foreach ($allowed as $key) {
            if (array_key_exists($key, $input)) {
                $data[$key] = $input[$key];
            }
        }

        return $data;
    }

    /**
     * Sanitize input recursively using WordPress sanitization rules
     */
    protected function sanitize(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $this->sanitize($value);
            } elseif (is_numeric($value)) {
                $clean[$key] = sanitize_text_field($value);
            } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $clean[$key] = sanitize_email($value);
            } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                $clean[$key] = esc_url_raw($value);
            } elseif (in_array($key, ['content', 'description', 'html'], true)) {
                $clean[$key] = wp_kses_post($value);
            } else {
                $clean[$key] = sanitize_text_field($value);
            }
        }

        return $clean;
    }

    /**
     * Get a specific request parameter
     */
    protected function get(string $key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }

    /**
     * Check if request has a specific parameter
     */
    protected function has(string $key): bool
    {
        return isset($this->request[$key]);
    }

    /**
     * Return a JSON response
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo wp_json_encode($data);
        exit;
    }

    /**
     * Return a success response
     */
    protected function success(array $data = [], string $message = 'Success'): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    /**
     * Return an error response
     */
    protected function error(string $message = 'Error', int $status = 400, array $data = []): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Validate request data
     */
    protected function validate(array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $this->get($field);

            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
            }

            if (strpos($rule, 'email') !== false && !empty($value) && !is_email($value)) {
                $errors[$field] = ucfirst($field) . ' must be a valid email';
            }

            if (preg_match('/min:(\d+)/', $rule, $matches) && !empty($value) && strlen($value) < $matches[1]) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $matches[1] . ' characters';
            }

            if (preg_match('/max:(\d+)/', $rule, $matches) && !empty($value) && strlen($value) > $matches[1]) {
                $errors[$field] = ucfirst($field) . ' must not exceed ' . $matches[1] . ' characters';
            }
        }

        if (!empty($errors)) {
            $this->error('Validation failed', 422, ['errors' => $errors]);
        }

        return $this->request;
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return is_user_logged_in();
    }

    /**
     * Get current user ID
     */
    protected function getCurrentUserId(): ?int
    {
        return get_current_user_id() ?: null;
    }

    /**
     * Check if user has capability
     */
    protected function can(string $capability): bool
    {
        return current_user_can($capability);
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->error('Authentication required', 401);
        }
    }

    /**
     * Require specific capability
     */
    protected function requireCapability(string $capability): void
    {
        if (!$this->can($capability)) {
            $this->error('Insufficient permissions', 403);
        }
    }
}
