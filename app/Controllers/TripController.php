<?php

namespace SmartDynamicPricing\Controllers;

use SmartDynamicPricing\Models\Trip;

/**
 * Trip Controller
 */
class TripController extends Controller
{
    /**
     * Get all trips
     */
    public function index(): void
    {
        $trips = Trip::all();
        
        $this->success([
            'trips' => array_map(function($trip) {
                return $trip->toArray();
            }, $trips)
        ]);
    }

    /**
     * Get a specific trip
     */
    public function show(int $id): void
    {
        $trip = Trip::find($id);
        
        if (!$trip) {
            $this->error('Trip not found', 404);
            return;
        }
        
        $this->success([
            'trip' => $trip->toArray()
        ]);
    }

    /**
     * Create a new trip
     */
    public function store(): void
    {
        $this->requireAuth();
        
        $data = $this->validate([
            'title' => 'required|max:255',
            'description' => 'max:1000',
            'destination' => 'required|max:255',
            'start_date' => 'required',
            'end_date' => 'required',
            'price' => 'required|numeric',
            'status' => 'required|in:active,inactive'
        ]);
        
        $data['user_id'] = $this->getCurrentUserId();
        
        $trip = Trip::create($data);
        
        $this->success([
            'trip' => $trip->toArray()
        ], 'Trip created successfully', 201);
    }

    /**
     * Update a trip
     */
    public function update(int $id): void
    {
        $this->requireAuth();
        
        $trip = Trip::find($id);
        
        if (!$trip) {
            $this->error('Trip not found', 404);
            return;
        }
        
        // Check if user owns the trip or has admin capabilities
        if ($trip->user_id !== $this->getCurrentUserId() && !$this->can('manage_options')) {
            $this->error('Unauthorized', 403);
            return;
        }
        
        $data = $this->validate([
            'title' => 'max:255',
            'description' => 'max:1000',
            'destination' => 'max:255',
            'start_date' => '',
            'end_date' => '',
            'price' => 'numeric',
            'status' => 'in:active,inactive'
        ]);
        
        // Remove empty values
        $data = array_filter($data, function($value) {
            return $value !== '';
        });
        
        $trip->fill($data);
        $trip->save();
        
        $this->success([
            'trip' => $trip->toArray()
        ], 'Trip updated successfully');
    }

    /**
     * Delete a trip
     */
    public function destroy(int $id): void
    {
        $this->requireAuth();
        
        $trip = Trip::find($id);
        
        if (!$trip) {
            $this->error('Trip not found', 404);
            return;
        }
        
        // Check if user owns the trip or has admin capabilities
        if ($trip->user_id !== $this->getCurrentUserId() && !$this->can('manage_options')) {
            $this->error('Unauthorized', 403);
            return;
        }
        
        $trip->delete();
        
        $this->success([], 'Trip deleted successfully');
    }

    /**
     * Get upcoming trips
     */
    public function upcoming(): void
    {
        $trips = Trip::getUpcoming();
        
        $this->success([
            'trips' => array_map(function($trip) {
                return $trip->toArray();
            }, $trips)
        ]);
    }

    /**
     * Get past trips
     */
    public function past(): void
    {
        $trips = Trip::getPast();
        
        $this->success([
            'trips' => array_map(function($trip) {
                return $trip->toArray();
            }, $trips)
        ]);
    }

    /**
     * Get trips by status
     */
    public function byStatus(string $status): void
    {
        $trips = Trip::getByStatus($status);
        
        $this->success([
            'trips' => array_map(function($trip) {
                return $trip->toArray();
            }, $trips)
        ]);
    }

    /**
     * Get user's trips
     */
    public function myTrips(): void
    {
        $this->requireAuth();
        
        $trips = Trip::getByUser($this->getCurrentUserId());
        
        $this->success([
            'trips' => array_map(function($trip) {
                return $trip->toArray();
            }, $trips)
        ]);
    }
}
