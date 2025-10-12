<?php

namespace SmartDynamicPricingDiscounts\Controllers;

use SmartDynamicPricingDiscounts\Models\Rule;

/**
 * Rule Controller
 */
class RuleController extends Controller
{
    /**
     * Get all rules
     */
    public function index(): void
    {
        $rules = Rule::all();
        $this->success([
            'rules' => array_map(function($rule) {
                return $rule->toArray();
            }, $rules)
        ]);
    }

    /**
     * Create a new rule
     */
    public function store(): void
    {
        $data = $this->validate([
            'name' => 'required|max:255',
            'description' => 'max:1000',
            'status' => 'required|in:active,inactive',
            'priority' => 'required|numeric',
            'rule_type' => 'required|max:255',
            'product_scope' => 'required',
            'user_scope' => 'required',
            'schedule' => 'required',
            'offers' => 'required',
            'meta' => 'required'
        ]);

        $rule = Rule::create($data);
        
        $this->success([
            'rule' => $rule->toArray()
        ], 'Rule created successfully', 201);
    }
}
