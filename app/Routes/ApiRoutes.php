<?php

namespace SmartDynamicPricing\Routes;

use SmartDynamicPricing\Core\Plugin;

/**
 * API Routes
 */
class ApiRoutes
{
    /**
     * Register API routes
     */
    public static function register(): void
    {
        $plugin = Plugin::getInstance();
        $router = $plugin->getRouter();
        
        // Trip routes
        $router->group(['prefix' => '/api'], function($router) {
            // Public trip routes
            $router->get('/rules', 'RuleController@index');
            $router->post('/rules', 'RuleController@store');
            $router->delete('/rules/{id}', 'RuleController@destroy');
            $router->get('/rules/{id}', 'RuleController@show');
            
            // $router->get('/trips', 'TripController@index');
            // $router->get('/trips/upcoming', 'TripController@upcoming');
            // $router->get('/trips/past', 'TripController@past');
            // $router->get('/trips/status/{status}', 'TripController@byStatus');
            // $router->get('/trips/{id}', 'TripController@show');
            
            // // Protected trip routes
            // $router->post('/trips', 'TripController@store', ['auth']);
            // $router->put('/trips/{id}', 'TripController@update', ['auth']);
            // $router->delete('/trips/{id}', 'TripController@destroy', ['auth']);
            // $router->get('/my-trips', 'TripController@myTrips', ['auth']);
        });
    }
}
