<?php

namespace WpulsePricingRules\Includes\Exclusions;

/**
 * Repository for global exclusion list (products, categories, tags excluded from ALL rules).
 * Table: {$wpdb->prefix}wpulse_exclusions
 */
class ExclusionRepository {

    private const TABLE = 'wpulse_exclusions';
    private const OPTION_VERSION = 'wpulse_exclusions_db_version';
    private const SCHEMA_VERSION = 1;

    public static function getTableName(): string {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    /**
     * Create or upgrade exclusions table. Call on plugin activation.
     */
    public static function installTable(): void {
        $current = (int) get_option(self::OPTION_VERSION, 0);
        if ($current >= self::SCHEMA_VERSION) {
            return;
        }
        global $wpdb;
        $table   = self::getTableName();
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            exclusion_type varchar(20) NOT NULL,
            object_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY exclusion_type (exclusion_type),
            KEY object_id (object_id),
            UNIQUE KEY type_object (exclusion_type, object_id)
        ) {$charset};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        update_option(self::OPTION_VERSION, self::SCHEMA_VERSION);
    }

    /**
     * @return array<int, array{id: int, exclusion_type: string, object_id: int, created_at: string}>
     */
    public static function getAll(): array {
        global $wpdb;
        $table = self::getTableName();
        $rows  = $wpdb->get_results("SELECT id, exclusion_type, object_id, created_at FROM {$table} ORDER BY exclusion_type, object_id", ARRAY_A);
        return $rows ? array_map(function ($row) {
            return [
                'id'             => (int) $row['id'],
                'exclusion_type' => $row['exclusion_type'],
                'object_id'      => (int) $row['object_id'],
                'created_at'     => $row['created_at'],
            ];
        }, $rows) : [];
    }

    /**
     * @return array<int, array{id: int, exclusion_type: string, object_id: int, created_at: string}>
     */
    public static function getByType(string $type): array {
        $type = self::sanitizeType($type);
        $all  = self::getAll();
        return array_values(array_filter($all, function ($row) use ($type) {
            return $row['exclusion_type'] === $type;
        }));
    }

    /**
     * Add one exclusion. Returns inserted id or null on failure/duplicate.
     */
    public static function add(string $type, int $objectId): ?int {
        $type = self::sanitizeType($type);
        if ($objectId <= 0) {
            return null;
        }
        if (self::exists($type, $objectId)) {
            return null;
        }
        global $wpdb;
        $table = self::getTableName();
        $wpdb->insert($table, [
            'exclusion_type' => $type,
            'object_id'      => $objectId,
        ], ['%s', '%d']);
        return $wpdb->insert_id ? (int) $wpdb->insert_id : null;
    }

    public static function delete(int $id): bool {
        global $wpdb;
        $table  = self::getTableName();
        $result = $wpdb->delete($table, ['id' => $id], ['%d']);
        return $result !== false;
    }

    public static function exists(string $type, int $objectId): bool {
        global $wpdb;
        $table = self::getTableName();
        $type  = self::sanitizeType($type);
        $id    = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE exclusion_type = %s AND object_id = %d",
            $type,
            $objectId
        ));
        return $id !== null;
    }

    private static function sanitizeType(string $type): string {
        $allowed = ['product', 'category', 'tag'];
        $type    = strtolower($type);
        return in_array($type, $allowed, true) ? $type : 'product';
    }
}
