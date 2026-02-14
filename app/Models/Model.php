<?php

namespace WpulsePricingRules\App\Models;

/**
 * Base model – Laravel-style static API, WordPress-backed.
 */
abstract class Model {

    /** @var string Table name without prefix */
    protected static string $table = '';

    /** @var string Primary key */
    protected static string $primaryKey = 'id';

    /**
     * Get full table name with WP prefix.
     */
    public static function tableName(): string {
        global $wpdb;
        return $wpdb->prefix . static::$table;
    }

    /**
     * Find by primary key.
     *
     * @param int $id
     * @return array|null
     */
    public static function find(int $id): ?array {
        global $wpdb;
        $table = self::tableName();
        $row   = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE " . static::$primaryKey . " = %d",
                $id
            ),
            ARRAY_A
        );
        return $row ?: null;
    }

    /**
     * Get all rows.
     *
     * @return array
     */
    public static function all(): array {
        global $wpdb;
        $table = self::tableName();
        $rows  = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
        return $rows ?: [];
    }

    /**
     * Create a new row.
     *
     * @param array $data
     * @return array|null Created row or null on failure.
     */
    public static function create(array $data): ?array {
        global $wpdb;
        $wpdb->insert(self::tableName(), $data);
        if ($wpdb->insert_id) {
            return self::find((int) $wpdb->insert_id);
        }
        return null;
    }

    /**
     * Update by primary key.
     *
     * @param int   $id
     * @param array $data
     * @return array|null Updated row or null.
     */
    public static function update(int $id, array $data): ?array {
        global $wpdb;
        $updated = $wpdb->update(
            self::tableName(),
            $data,
            [static::$primaryKey => $id]
        );
        return $updated !== false ? self::find($id) : null;
    }

    /**
     * Delete by primary key.
     *
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool {
        global $wpdb;
        $result = $wpdb->delete(
            self::tableName(),
            [static::$primaryKey => $id]
        );
        return $result !== false;
    }
}
