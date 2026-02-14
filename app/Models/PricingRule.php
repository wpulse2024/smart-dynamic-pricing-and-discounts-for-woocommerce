<?php

namespace WpulsePricingRules\App\Models;

/**
 * Pricing rule model.
 */
class PricingRule extends Model {

    protected static string $table = 'wpulse_pricing_rules';

    /**
     * Ensure table exists (call on activation).
     */
    public static function createTable(): void {
        global $wpdb;
        $table = self::tableName();
        $sql   = "CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(50) NOT NULL DEFAULT 'percentage',
            value decimal(10,2) NOT NULL DEFAULT 0,
            min_quantity int unsigned DEFAULT NULL,
            max_quantity int unsigned DEFAULT NULL,
            product_ids text DEFAULT NULL,
            active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY active (active)
        ) " . $wpdb->get_charset_collate() . ';';
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
