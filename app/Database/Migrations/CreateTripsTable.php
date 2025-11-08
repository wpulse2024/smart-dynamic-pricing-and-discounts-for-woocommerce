<?php

namespace SmartPricing\Database\Migrations;

use SmartPricing\Database\Database;

/**
 * Migration to create trips table
 */
class CreateTripsTable
{
    /**
     * Database instance
     */
    protected $database;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->database = new Database();
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $columns = [
            'id' => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
            'name' => 'varchar(255) NOT NULL',
            'priority' => 'int(11) NOT NULL DEFAULT 1',
            'rule_type' => 'varchar(255) NOT NULL',
            'product_scope' => 'text DEFAULT NULL',
            'user_scope' => 'text DEFAULT NULL',
            'schedule' => 'text DEFAULT NULL',
            'offers' => 'text DEFAULT NULL',
            'meta' => 'text DEFAULT NULL',
            'status' => "enum('active','inactive') NOT NULL DEFAULT 'active'",
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ];

        $options = [
            'primary_key' => 'id',
            'indexes' => [
                'KEY idx_name (name)',
                'KEY idx_rule_type (rule_type)',
                'KEY idx_status (status)'
            ]
        ];

        $this->database->createTable('smart_pricing_discounts_roles', $columns, $options);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->database->dropTable('smart_pricing_discounts_roles');
    }
}
