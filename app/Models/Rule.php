<?php

namespace WpulsePricingRules\Models;

/**
 * Rule model
 */
class Rule extends Model
{
    /**
     * The table name
     */
    protected $table = 'wpulse_pricing_rules_discounts';

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
