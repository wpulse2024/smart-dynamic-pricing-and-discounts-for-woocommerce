<?php

namespace SmartPricing\Controllers;

/**
 * Base Controller class
 */
abstract class Controller
{
    /**
     * The sanitized request data
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct()
    {
        $rawData = $this->getRequestData();
        $this->request = $this->sanitize($rawData);
    }

    /**
     * Get request data from $_POST, $_GET, or JSON input
     */
    protected function getRequestData(): array
    {
        $data = [];

        // Get JSON input
        $json = file_get_contents('php://input');
        if (!empty($json)) {
            $data = json_decode($json, true) ?? [];
        }

        // Merge with POST data
        if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        }

        // Merge with GET data
        if (!empty($_GET)) {
            $data = array_merge($data, $_GET);
        }

        return $data;
    }

    /**
     * 🔒 Sanitize input recursively using WordPress sanitization rules
     */
    protected function sanitize(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $this->sanitize($value); // recursive sanitization
            } elseif (is_numeric($value)) {
                $clean[$key] = sanitize_text_field($value);
            } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $clean[$key] = sanitize_email($value);
            } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                $clean[$key] = esc_url_raw($value);
            } elseif (in_array($key, ['content', 'description', 'html'], true)) {
                // allow limited HTML for content fields
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
     * Check if user is authenticated (WordPress user)
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