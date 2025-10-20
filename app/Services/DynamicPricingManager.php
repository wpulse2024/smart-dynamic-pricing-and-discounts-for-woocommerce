<?php

namespace SmartDynamicPricingDiscounts\Services;

use SmartDynamicPricingDiscounts\Models\Rule;
use SmartDynamicPricingDiscounts\Helpers\ValidateApplyDiscount;

class DynamicPricingManager {
    protected $rules = [];
    protected $wpdb;
    public function __construct($wpdb) {
        $this->wpdb = $wpdb;
        $this->loadRules();
        add_filter('woocommerce_cart_item_price', array($this, 'cart_item_price_html'), 10, 3);
    }
    public function loadRules() {
        $rules = Rule::all();
        $rules = array_map(function($rule) {
            $rule->product_scope = maybe_unserialize( $rule->product_scope );
            $rule->user_scope = maybe_unserialize($rule->user_scope);
            $rule->schedule = maybe_unserialize($rule->schedule);
            $rule->offers = maybe_unserialize($rule->offers);
            $rule->meta = maybe_unserialize($rule->meta);
            return $rule;
        }, $rules);

        $rules = array_filter($rules, function($rule) {
            return $rule->status === 'active';
        });
        
        $this->rules = $rules;
    }

    public function cart_item_price_html($price_html, $cart_item, $cart_item_key) {
        $product = $cart_item['data'];
        $product_id = $product->get_id();

        foreach ($this->rules as $rule) {
            if(!ValidateApplyDiscount::validate($rule, $cart_item, $cart_item_key)) {
                continue;
            }
            
        }
        
        return $price_html;
    }
}