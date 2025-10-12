<?php

namespace SmartDynamicPricingDiscounts\Services;

use SmartDynamicPricingDiscounts\Models\Trip;

/**
 * Trip Service - Business logic for trips
 */
class TripService
{
    /**
     * Get trips with pagination
     */
    public function getPaginatedTrips(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $trips = Trip::all();
        
        return [
            'data' => array_slice($trips, $offset, $perPage),
            'total' => count($trips),
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil(count($trips) / $perPage)
        ];
    }

    /**
     * Get trip statistics
     */
    public function getTripStats(): array
    {
        $allTrips = Trip::all();
        $activeTrips = Trip::getByStatus('active');
        $upcomingTrips = Trip::getUpcoming();
        $pastTrips = Trip::getPast();
        
        $totalValue = array_sum(array_map(function($trip) {
            return $trip->price;
        }, $allTrips));
        
        return [
            'total_trips' => count($allTrips),
            'active_trips' => count($activeTrips),
            'upcoming_trips' => count($upcomingTrips),
            'past_trips' => count($pastTrips),
            'total_value' => $totalValue,
            'average_price' => count($allTrips) > 0 ? $totalValue / count($allTrips) : 0
        ];
    }

    /**
     * Search trips
     */
    public function searchTrips(string $query): array
    {
        $allTrips = Trip::all();
        
        return array_filter($allTrips, function($trip) use ($query) {
            return stripos($trip->title, $query) !== false ||
                   stripos($trip->destination, $query) !== false ||
                   stripos($trip->description, $query) !== false;
        });
    }

    /**
     * Get trips by date range
     */
    public function getTripsByDateRange(string $startDate, string $endDate): array
    {
        $allTrips = Trip::all();
        
        return array_filter($allTrips, function($trip) use ($startDate, $endDate) {
            return $trip->start_date >= $startDate && $trip->end_date <= $endDate;
        });
    }
}
