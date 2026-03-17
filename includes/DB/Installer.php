<?php

namespace WpulsePricingRules\Includes\DB;

/**
 * DB table installer – creates/upgrades wpulse_pricing_rules table.
 * Single unified rule schema: id, name, type, status, priority, rule_json, created_at, updated_at.
 */
class Installer {

    private const OPTION_VERSION = 'wpulse_pricing_rules_db_version';
    private const SCHEMA_VERSION = 2;

    public static function getTableName(): string {
        global $wpdb;
        return $wpdb->prefix . 'wpulse_pricing_rules';
    }

    /**
     * Run install or upgrade (call on plugin activation).
     */
    public static function install(): void {
        $current = (int) get_option(self::OPTION_VERSION, 0);
        if ($current >= self::SCHEMA_VERSION) {
            return;
        }
        if ($current < 1) {
            self::createInitialTable();
        }
        if ($current < 2) {
            self::upgradeToV2();
        }
        update_option(self::OPTION_VERSION, self::SCHEMA_VERSION);
    }

    private static function createInitialTable(): void {
        global $wpdb;
        $table = self::getTableName();
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
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
        ) {$charset};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Upgrade to unified schema (rule_json).
     */
    private static function upgradeToV2(): void {
        global $wpdb;
        $table = self::getTableName();
        $charset = $wpdb->get_charset_collate();

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table
        ));

        if ($exists) {
            $has_rule_json = $wpdb->get_var("SHOW COLUMNS FROM " . esc_sql($table) . " LIKE 'rule_json'");
            if ($has_rule_json) {
                return;
            }
            $wpdb->query("DROP TABLE IF EXISTS " . esc_sql($table));
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL DEFAULT '',
            type varchar(50) NOT NULL DEFAULT 'quantity_discount',
            status varchar(20) NOT NULL DEFAULT 'draft',
            priority int NOT NULL DEFAULT 10,
            rule_json longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type_status (type, status),
            KEY status (status),
            KEY priority (priority),
            KEY updated_at (updated_at)
        ) {$charset};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
