<?php

/**
 * REST API routes – Laravel-style with Route facade.
 * All routes require valid wp_rest nonce (X-WP-Nonce header) and manage_woocommerce capability.
 */

use WpulsePricingRules\Routes\Route;
use WpulsePricingRules\App\Http\Controllers\RulesController;

// Resource: pricing rules (index, store, show, update, destroy)
Route::resource('rules', RulesController::class);

// Optional: explicit routes if you prefer over resource
// Route::get('rules', [RulesController::class, 'index']);
// Route::post('rules', [RulesController::class, 'store']);
// Route::get('rules/{id}', [RulesController::class, 'show']);
// Route::put('rules/{id}', [RulesController::class, 'update']);
// Route::delete('rules/{id}', [RulesController::class, 'destroy']);
