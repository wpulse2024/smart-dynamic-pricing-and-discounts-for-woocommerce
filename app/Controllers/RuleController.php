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
        $rules = array_map(function($rule) {
            $rule->product_scope = maybe_unserialize($rule->product_scope);
            $rule->user_scope = maybe_unserialize($rule->user_scope);
            $rule->schedule = maybe_unserialize($rule->schedule);
            $rule->offers = maybe_unserialize($rule->offers);
            $rule->meta = maybe_unserialize($rule->meta);
            return $rule;
        }, $rules);

        $this->success([
            'rules' => array_map(function($rule) {
                return $rule->toArray();
            }, $rules)
        ]);
    }

    /**
     * Get a specific rule
     */
    public function show(int $id): void
    {
        $rule = Rule::find($id);
        
        if (!$rule) {
            $this->error('Rule not found', 404);
            return;
        }

        $rule->product_scope = maybe_unserialize($rule->product_scope);
        $rule->user_scope = maybe_unserialize($rule->user_scope);
        $rule->schedule = maybe_unserialize($rule->schedule);
        $rule->offers = maybe_unserialize($rule->offers);   
        $rule->meta = maybe_unserialize($rule->meta);
        
        $this->success([
            'rule' => $rule->toArray()
        ]);
    }

    /**
     * Create a new rule
     */
    public function store(): void
    {
        $data = $this->validate([
            'name' => 'required|max:255',
            'status' => 'required|in:active,inactive',
            'priority' => 'required|numeric',
            'rule_type' => 'required|max:255',
            'product_scope' => 'required',
            'user_scope' => 'required',
            'schedule' => 'required',
            'offers' => 'required',
            'meta' => 'required',
        ]);

        $formatData = [
            'name' => $data['name'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'rule_type' => $data['rule_type'],
            'product_scope' => maybe_serialize($data['product_scope']),
            'user_scope' => maybe_serialize($data['user_scope']),
            'schedule' => maybe_serialize($data['schedule']),
            'offers' => maybe_serialize($data['offers']),
            'meta' => maybe_serialize($data['meta'])
        ];

        if (isset($data['id'])) {
            $rule = Rule::find($data['id']);
            if (!$rule) {
                $this->error('Rule not found', 404);
                return;
            }
            $rule->fill($formatData);
            $rule->save();
            $this->success([
                'rule' => $rule->toArray()
            ], 'Rule updated successfully');
            return;
        }

        $rule = Rule::create($formatData);
        
        $this->success([
            'rule' => $rule->toArray()
        ], 'Rule created successfully', 201);
    }

    /**
     * Delete a rule
     */
    public function destroy(int $id): void
    {
        $rule = Rule::find($id);

        if (!$rule) {
            $this->error('Rule not found', 404);
            return;
        }
        
        $rule->delete();
        
        $this->success([], 'Rule deleted successfully');
    }
}
