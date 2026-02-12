<?php

namespace WpulsePricingRules\Models;

/**
 * Trip model
 */
class Trip extends Model
{
    /**
     * The table name
     */
    protected $table = 'trips';

    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'title',
        'description',
        'destination',
        'start_date',
        'end_date',
        'price',
        'status',
        'user_id'
    ];

    /**
     * The attributes that should be hidden for arrays
     */
    protected $hidden = [];

    /**
     * Get trips by status
     */
    public static function getByStatus(string $status): array
    {
        return self::where('status', $status);
    }

    /**
     * Get trips by user
     */
    public static function getByUser(int $userId): array
    {
        return self::where('user_id', $userId);
    }

    /**
     * Get upcoming trips
     */
    public static function getUpcoming(): array
    {
        $instance = new static();
        global $wpdb;
        $table = $wpdb->prefix . $instance->table;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE start_date > NOW() AND status = 'active' ORDER BY start_date ASC"
        );
        
        $trips = [];
        foreach ($results as $result) {
            $trip = new static();
            $trip->fill((array) $result);
            $trip->exists = true;
            $trips[] = $trip;
        }
        
        return $trips;
    }

    /**
     * Get past trips
     */
    public static function getPast(): array
    {
        $instance = new static();
        global $wpdb;
        $table = $wpdb->prefix . $instance->table;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE end_date < NOW() ORDER BY end_date DESC"
        );
        
        $trips = [];
        foreach ($results as $result) {
            $trip = new static();
            $trip->fill((array) $result);
            $trip->exists = true;
            $trips[] = $trip;
        }
        
        return $trips;
    }

    /**
     * Get trip duration in days
     */
    public function getDuration(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        
        $start = new \DateTime($this->start_date);
        $end = new \DateTime($this->end_date);
        $diff = $start->diff($end);
        
        return $diff->days;
    }

    /**
     * Check if trip is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->start_date && new \DateTime($this->start_date) > new \DateTime();
    }

    /**
     * Check if trip is past
     */
    public function isPast(): bool
    {
        return $this->end_date && new \DateTime($this->end_date) < new \DateTime();
    }

    /**
     * Check if trip is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted date range
     */
    public function getFormattedDateRange(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return '';
        }
        
        $start = new \DateTime($this->start_date);
        $end = new \DateTime($this->end_date);
        
        return $start->format('M j, Y') . ' - ' . $end->format('M j, Y');
    }
}
