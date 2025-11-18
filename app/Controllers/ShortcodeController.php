<?php

namespace SmartDynamicPricing\Controllers;

use SmartDynamicPricing\Models\Trip;

/**
 * Shortcode Controller
 */
class ShortcodeController
{
    /**
     * Register shortcodes
     */
    public static function register(): void
    {
        add_shortcode('smart-pricing_trips', [self::class, 'displayTrips']);
        add_shortcode('smart-pricing_upcoming_trips', [self::class, 'displayUpcomingTrips']);
    }

    /**
     * Display all trips
     */
    public static function displayTrips($atts): string
    {
        $atts = shortcode_atts([
            'limit' => 5,
            'status' => 'active'
        ], $atts);

        $trips = Trip::getByStatus($atts['status']);
        $trips = array_slice($trips, 0, (int) $atts['limit']);

        if (empty($trips)) {
            return '<p>No trips found.</p>';
        }

        $output = '<div class="my-plugin-trips">';
        foreach ($trips as $trip) {
            $output .= '<div class="trip-item">';
            $output .= '<h3>' . esc_html($trip->title) . '</h3>';
            $output .= '<p><strong>Destination:</strong> ' . esc_html($trip->destination) . '</p>';
            $output .= '<p><strong>Price:</strong> ' . esc_html($trip->getFormattedPrice()) . '</p>';
            $output .= '<p><strong>Dates:</strong> ' . esc_html($trip->getFormattedDateRange()) . '</p>';
            if ($trip->description) {
                $output .= '<p>' . esc_html($trip->description) . '</p>';
            }
            $output .= '</div>';
        }
        $output .= '</div>';

        return $output;
    }

    /**
     * Display upcoming trips
     */
    public static function displayUpcomingTrips($atts): string
    {
        $atts = shortcode_atts([
            'limit' => 3
        ], $atts);

        $trips = Trip::getUpcoming();
        $trips = array_slice($trips, 0, (int) $atts['limit']);

        if (empty($trips)) {
            return '<p>No upcoming trips.</p>';
        }

        $output = '<div class="my-plugin-upcoming-trips">';
        $output .= '<h3>Upcoming Trips</h3>';
        foreach ($trips as $trip) {
            $output .= '<div class="trip-item">';
            $output .= '<h4>' . esc_html($trip->title) . '</h4>';
            $output .= '<p>' . esc_html($trip->destination) . ' - ' . esc_html($trip->getFormattedDateRange()) . '</p>';
            $output .= '</div>';
        }
        $output .= '</div>';

        return $output;
    }
}
