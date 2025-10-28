<?php

namespace SmartDynamicPricingDiscounts\Models;

use SmartDynamicPricingDiscounts\Core\Database;

/**
 * Base Model class - Lightweight ORM using $wpdb
 */
abstract class Model
{
    /**
     * The table name
     */
    protected $table;

    /**
     * The primary key
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays
     */
    protected $hidden = [];

    /**
     * The model's attributes
     */
    protected $attributes = [];

    /**
     * Whether the model exists in the database
     */
    protected $exists = false;

    /**
     * Global $wpdb instance
     */
    protected $wpdb;

    /**
     * Constructor
     */
    public function __construct(array $attributes = [])
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        if (empty($this->table)) {
            $this->table = $this->getTableName();
        }

        $this->fill($attributes);
    }

    /**
     * Get the table name for the model
     */
    protected function getTableName(): string
    {
        $class = get_class($this);
        $class = str_replace('SmartDynamicPricingDiscounts\\Models\\', '', $class);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
    }

    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Get an attribute from the model
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute on the model
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get all attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the model's attributes as an array
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;
        
        // Remove hidden attributes
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }
        
        return $attributes;
    }

    /**
     * Save the model to the database
     */
    public function save(): bool
    {
        $data = $this->attributes;
        
        if ($this->exists) {
            // Update existing record
            $where = [$this->primaryKey => $this->getAttribute($this->primaryKey)];
            unset($data[$this->primaryKey]);
            
            $result = $this->wpdb->update(
                $this->wpdb->prefix . $this->table,
                $data,
                $where
            );
        } else {
            // Insert new record
            $result = $this->wpdb->insert(
                $this->wpdb->prefix . $this->table,
                $data
            );
            
            if ($result) {
                $this->setAttribute($this->primaryKey, $this->wpdb->insert_id);
                $this->exists = true;
            }
        }
        
        return $result !== false;
    }

    /**
     * Delete the model from the database
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $result = $this->wpdb->delete(
            $this->wpdb->prefix . $this->table,
            [$this->primaryKey => $this->getAttribute($this->primaryKey)]
        );
        
        if ($result) {
            $this->exists = false;
        }
        
        return $result !== false;
    }

    /**
     * Find a model by its primary key
     */
    public static function find($id): ?self
    {
        $instance = new static();
        $table = $instance->wpdb->prefix . $instance->table;
        
        $result = $instance->wpdb->get_row(
             // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $instance->wpdb->prepare("SELECT * FROM {$table} WHERE {$instance->primaryKey} = %s", $id)
        );
        
        if ($result) {
            $instance->fill((array) $result);
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }

    /**
     * Get all records
     */
    public static function all(): array
    {
        $instance = new static();
        $table = $instance->wpdb->prefix . $instance->table;
        
        $results = $instance->wpdb->get_results("SELECT * FROM {$table}");
        
        $models = [];
        foreach ($results as $result) {
            $model = new static();
            $model->fill((array) $result);
            $model->exists = true;
            $models[] = $model;
        }
        
        return $models;
    }

    /**
     * Create a new model instance
     */
    public static function create(array $attributes): self
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Get records with a where clause
     */
    public static function where(string $column, $operator, $value = null): array
    {
        $instance = new static();
        $table = $instance->wpdb->prefix . $instance->table;
        
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $sql = "SELECT * FROM {$table} WHERE {$column} {$operator} %s";
        $results = $instance->wpdb->get_results(
             // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $instance->wpdb->prepare($sql, $value)
        );
        
        $models = [];
        foreach ($results as $result) {
            $model = new static();
            $model->fill((array) $result);
            $model->exists = true;
            $models[] = $model;
        }
        
        return $models;
    }

    /**
     * Magic method to get attributes
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic method to set attributes
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Magic method to check if attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
}
