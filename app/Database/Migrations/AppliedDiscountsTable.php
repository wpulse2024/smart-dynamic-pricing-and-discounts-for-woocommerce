<?php

namespace SmartPricing\Database\Migrations;

use SmartPricing\Database\Database;

class AppliedDiscountsTable
{
    protected $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    public function up(): void
    {
        $columns = [
            'id' => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
            'rule_id' => 'bigint(20) UNSIGNED NOT NULL',
            'user_id' => 'bigint(20) UNSIGNED DEFAULT NULL',
            'order_id' => 'bigint(20) UNSIGNED DEFAULT NULL',
            'product_id' => 'bigint(20) UNSIGNED DEFAULT NULL',
            'meta' => 'text DEFAULT NULL',
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ];

        $options = [
            'primary_key' => 'id',
            'indexes' => [
                'KEY idx_rule_id (rule_id)',
                'KEY idx_product_id (product_id)',
                'KEY idx_user_id (user_id)',
                'KEY idx_order_id (order_id)'
            ]
        ];

        $this->database->createTable('smart_pricing_discounts_applied', $columns, $options);
    }

    public function down(): void
    {
        $this->database->dropTable('smart_pricing_discounts_applied');
    }
}
