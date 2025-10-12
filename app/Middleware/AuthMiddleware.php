<?php

namespace SmartDynamicPricingDiscounts\Middleware;

/**
 * Authentication middleware
 */
class AuthMiddleware
{
    /**
     * Handle the request
     */
    public function handle(): bool
    {
        if (!is_user_logged_in()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
        
        return true;
    }
}
