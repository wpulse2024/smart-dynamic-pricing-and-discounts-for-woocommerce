<?php

namespace WpulsePricingRules\Controllers;

use WpulsePricingRules\Models\Rule;

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
        $id = intval($id);
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
            'name'           => 'required|max:255',
            'status'         => 'required', // normalized below (accepts active/inactive/true/false)
            'priority'       => 'required|numeric',
            'rule_type'      => 'required|max:255',
            'product_scope'  => 'required',
            'user_scope'     => 'required',
            'schedule'       => 'required',
            'offers'         => 'required',
            'meta'           => 'required',
        ]);

        // Normalize status (Vue may send boolean true/false; API expects 'active'/'inactive')
        $status = $data['status'] ?? 'active';
        if ($status === true || $status === 'true' || $status === 1) {
            $status = 'active';
        } elseif ($status === false || $status === 'false' || $status === 0) {
            $status = 'inactive';
        }
        $status = in_array($status, ['active', 'inactive'], true) ? $status : 'active';

        // Sanitize scalar fields
        $sanitized = [
            'name'      => sanitize_text_field($data['name']),
            'status'    => $status,
            'priority'  => intval($data['priority']),
            'rule_type' => sanitize_text_field($data['rule_type']),
        ];

        // Sanitize array-based fields recursively
        $sanitize_array = function ($value) use (&$sanitize_array) {
            if (is_array($value)) {
                return array_map($sanitize_array, $value);
            }
            return is_scalar($value) ? sanitize_text_field($value) : '';
        };

        $sanitized['product_scope'] = maybe_serialize($sanitize_array($data['product_scope']));
        $sanitized['user_scope']    = maybe_serialize($sanitize_array($data['user_scope']));
        $sanitized['schedule']      = maybe_serialize($sanitize_array($data['schedule']));
        $sanitized['offers']        = maybe_serialize($sanitize_array($data['offers']));
        $sanitized['meta']          = maybe_serialize($sanitize_array($data['meta']));

        // Update existing rule
        if (isset($data['id'])) {
            $rule = Rule::find(intval($data['id']));
            if (!$rule) {
                $this->error('Rule not found', 404);
                return;
            }

            $rule->fill($sanitized);
            $rule->save();

            $this->success([
                'rule' => $rule->toArray()
            ], 'Rule updated successfully');

            return;
        }

        // Create new rule
        $rule = Rule::create($sanitized);

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
