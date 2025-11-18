<?php

namespace SmartPricing\Models;

/**
 * Rule model
 */
class Rule extends Model
{
    /**
     * The table name
     */
    protected $table = 'smart_pricing_discounts_roles';

    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'id',
        'name',
        'description',
        'status',
        'priority',
        'rule_type',
        'product_scope',
        'user_scope',
        'schedule',
        'offers',
        'meta',
        'created_at',
        'updated_at'
    ];
}
