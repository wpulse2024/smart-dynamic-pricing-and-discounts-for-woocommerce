<?php

namespace SmartPricing\Database;

/**
 * Database migration helper class
 */
class Database
{
    /**
     * Global $wpdb instance
     */
    protected $wpdb;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Create a table
     */
    public function createTable(string $tableName, array $columns, array $options = []): bool
    {
        global $wpdb;
    
        $charsetCollate = $wpdb->get_charset_collate();
        $fullTableName = $wpdb->prefix . $tableName;
    
        $sql = "CREATE TABLE {$fullTableName} (\n";
    
        // Add columns
        $columnDefinitions = [];
        foreach ($columns as $name => $definition) {
            $columnDefinitions[] = "  {$name} {$definition}";
        }
    
        $sql .= implode(",\n", $columnDefinitions);
    
        // Add primary key if specified
        if (isset($options['primary_key'])) {
            // dbDelta requires 2 spaces after PRIMARY KEY
            $sql .= ",\n  PRIMARY KEY  ({$options['primary_key']})";
        }
    
        // Add indexes if specified
        if (!empty($options['indexes'])) {
            foreach ($options['indexes'] as $index) {
                // Example: 'KEY idx_user_id (user_id)' or 'UNIQUE KEY idx_slug (slug)'
                $sql .= ",\n  {$index}";
            }
        }
    
        $sql .= "\n) {$charsetCollate};";
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        // Debugging help
        // var_dump($sql, $result);
    
        // dbDelta returns an array — we return true if table was created or updated
        return !empty($result);
    }    

    /**
     * Drop a table
     */
    public function dropTable(string $tableName): bool
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        $sql = $this->wpdb->prepare(
            "DROP TABLE IF EXISTS `%s`",
            $fullTableName
        );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->query($sql) !== false;
    }

    /**
     * Add a column to a table
     */
    public function addColumn(string $tableName, string $columnName, string $definition): bool
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        $sql = "ALTER TABLE {$fullTableName} ADD COLUMN {$columnName} {$definition}";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->query($sql) !== false;
    }

    /**
     * Drop a column from a table
     */
    public function dropColumn(string $tableName, string $columnName): bool
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        $sql = "ALTER TABLE {$fullTableName} DROP COLUMN {$columnName}";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->query($sql) !== false;
    }

    /**
     * Add an index to a table
     */
    public function addIndex(string $tableName, string $indexName, array $columns, string $type = 'INDEX'): bool
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        $columnsList = implode(', ', $columns);
        $sql = "ALTER TABLE {$fullTableName} ADD {$type} {$indexName} ({$columnsList})";
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->query($sql) !== false;
    }

    /**
     * Drop an index from a table
     */
    public function dropIndex(string $tableName, string $indexName): bool
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        $sql = "ALTER TABLE {$fullTableName} DROP INDEX {$indexName}";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->query($sql) !== false;
    }

    /**
     * Check if a table exists
     */
    public function tableExists(string $tableName): bool
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $wpdb->prepare() is correctly used below.
        $result = $this->wpdb->get_var(
             // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $this->wpdb->prepare(
                "SHOW TABLES LIKE %s",
                 // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $fullTableName
            )
        );
        
        return $result === $fullTableName;
    }

    /**
     * Check if a column exists in a table
     */
    public function columnExists(string $tableName, string $columnName): bool
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        $result = $this->wpdb->get_var(
             // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $this->wpdb->prepare(
                 // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                "SHOW COLUMNS FROM {$fullTableName} LIKE %s",
                 // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $columnName
            )
        );
        
        return !empty($result);
    }

    /**
     * Get table structure
     */
    public function getTableStructure(string $tableName): array
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
         // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_results("DESCRIBE {$fullTableName}", ARRAY_A);
    }

    /**
     * Run a custom SQL query
     */
    public function query(string $sql): mixed
    {
         // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->query($sql);
    }

    /**
     * Get a single result
     */
    public function getVar(string $sql): mixed
    {
         // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_var($sql);
    }

    /**
     * Get a single row
     */
    public function getRow(string $sql): ?object
    {
         // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_row($sql);
    }

    /**
     * Get multiple rows
     */
    public function getResults(string $sql): array
    {
         // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_results($sql);
    }

    /**
     * Insert data into a table
     */
    public function insert(string $tableName, array $data): int|false
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        $result = $this->wpdb->insert($fullTableName, $data);
        
        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update data in a table
     */
    public function update(string $tableName, array $data, array $where): int|false
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        return $this->wpdb->update($fullTableName, $data, $where);
    }

    /**
     * Delete data from a table
     */
    public function delete(string $tableName, array $where): int|false
    {
        $fullTableName = $this->wpdb->prefix . $tableName;
        return $this->wpdb->delete($fullTableName, $where);
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool
    {
        return $this->wpdb->query('START TRANSACTION') !== false;
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool
    {
        return $this->wpdb->query('COMMIT') !== false;
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): bool
    {
        return $this->wpdb->query('ROLLBACK') !== false;
    }

    /**
     * Get the last error
     */
    public function getLastError(): string
    {
        return $this->wpdb->last_error;
    }

    /**
     * Get the last query
     */
    public function getLastQuery(): string
    {
        return $this->wpdb->last_query;
    }

    /**
     * Get the number of affected rows
     */
    public function getAffectedRows(): int
    {
        return $this->wpdb->rows_affected;
    }

    /**
     * Prepare a SQL statement
     */
    public function prepare(string $query, ...$args): string
    {
         // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->prepare($query, ...$args);
    }
}
