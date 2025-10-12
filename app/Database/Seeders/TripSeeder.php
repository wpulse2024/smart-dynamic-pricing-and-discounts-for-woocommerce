<?php

namespace SmartDynamicPricingDiscounts\Database\Seeders;

use SmartDynamicPricingDiscounts\Models\Trip;

/**
 * Trip seeder
 */
class TripSeeder
{
    /**
     * Run the seeder
     */
    public function run(): void
    {
        $trips = [
            [
                'title' => 'Paris Adventure',
                'description' => 'A romantic getaway to the City of Light',
                'destination' => 'Paris, France',
                'start_date' => '2024-06-01',
                'end_date' => '2024-06-07',
                'price' => 2500.00,
                'status' => 'active',
                'user_id' => 1
            ],
            [
                'title' => 'Tokyo Exploration',
                'description' => 'Discover the vibrant culture and cuisine of Tokyo',
                'destination' => 'Tokyo, Japan',
                'start_date' => '2024-07-15',
                'end_date' => '2024-07-22',
                'price' => 3200.00,
                'status' => 'active',
                'user_id' => 1
            ],
            [
                'title' => 'New York City',
                'description' => 'Experience the Big Apple',
                'destination' => 'New York, USA',
                'start_date' => '2024-05-10',
                'end_date' => '2024-05-15',
                'price' => 1800.00,
                'status' => 'inactive',
                'user_id' => 1
            ],
            [
                'title' => 'Barcelona Beach',
                'description' => 'Relax on the beautiful beaches of Barcelona',
                'destination' => 'Barcelona, Spain',
                'start_date' => '2024-08-20',
                'end_date' => '2024-08-27',
                'price' => 2200.00,
                'status' => 'active',
                'user_id' => 1
            ],
            [
                'title' => 'Sydney Opera',
                'description' => 'Visit the iconic Sydney Opera House',
                'destination' => 'Sydney, Australia',
                'start_date' => '2024-09-05',
                'end_date' => '2024-09-12',
                'price' => 2800.00,
                'status' => 'active',
                'user_id' => 1
            ]
        ];

        foreach ($trips as $tripData) {
            Trip::create($tripData);
        }
    }
}
