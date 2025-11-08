<?php

namespace SmartPricing\Services;

use SmartPricing\Models\Rule;
use SmartPricing\Helpers\ValidateApplyDiscount;
use SmartPricing\Handler\SpecialDiscountHandler;
use SmartPricing\Handler\QuantityDiscountHandler;
use WC_Cart;

class DynamicPricingManager
{
    protected $rules = [];
    protected $wpdb;
    protected $applied_discounts = [];
    protected $processed_products = []; // 🆕 Track which products already have a rule applied

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
        $this->loadRules();
        add_action('woocommerce_before_calculate_totals', [$this, 'apply_cart_discounts'], 10);
        add_action('woocommerce_before_cart', [$this, 'show_discount_notices']); 
    }

    public function loadRules()
    {
        $rules = Rule::all();
        $rules = array_map(function ($rule) {
            $rule->product_scope = maybe_unserialize($rule->product_scope);
            $rule->user_scope = maybe_unserialize($rule->user_scope);
            $rule->schedule = maybe_unserialize($rule->schedule);
            $rule->offers = maybe_unserialize($rule->offers);
            $rule->meta = maybe_unserialize($rule->meta);
            return $rule;
        }, $rules);

        // ✅ Only active rules
        $active_rules = array_filter($rules, fn($r) => $r->status === 'active');
        // ✅ Sort by priority (lower number = higher priority)
        usort($active_rules, function ($a, $b) {
            $a_priority = (int) ($a->priority ?? 999);
            $b_priority = (int) ($b->priority ?? 999);
            return $a_priority <=> $b_priority;
        });

        $this->rules = $active_rules;
    }

    /**
     * Apply all dynamic pricing discounts before WooCommerce totals are calculated.
     */
    public function apply_cart_discounts(WC_Cart $cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        $this->processed_products = []; // reset on each calculation
        foreach ($this->rules as $rule) {
            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                $product_id = $cart_item['product_id'];

                // ✅ Skip if this product already got a discount from a higher-priority rule
                if (isset($this->processed_products[$product_id])) {
                    continue;
                }
 
                if (!ValidateApplyDiscount::validate($rule, $cart_item, $cart_item_key)) {
                    continue;
                }

                if (empty($rule->offers) || !is_array($rule->offers)) {
                    continue;
                }
                foreach ($rule->offers as $offer) {
                    if (($offer['type'] ?? '') == 'special_offer') {
                        $handler = new SpecialDiscountHandler();
                        $applied = $handler->handle($cart, $cart_item, $offer, $rule->name);

                        if ($applied['success']) {
                            $this->processed_products[$product_id] = true;
                            // $this->showsApplyDiscountMessage($applied['totalDiscount'], $applied['rule_name'], $applied['base_product']);
                            break;
                        }
                    }
                    if (($offer['type'] ?? '') == 'quantity_discount') {
                        $handler = new QuantityDiscountHandler();
                        $applied = $handler->handle($cart, $cart_item, $offer, $rule->name);

                        if ($applied['success']) {
                            $this->processed_products[$product_id] = true;
                            break;
                        }
                    }
                }
            }
        }
    }

    protected function showsApplyDiscountMessage($totalDiscount, $rule_name, $base_product)
    {
        $product_name = $base_product->get_name();
        $message_key = md5($rule_name . $product_name);

        if (!isset($this->applied_discounts[$message_key])) {
            $this->applied_discounts[$message_key] = sprintf(
                'Discount applied: %s — on %s — You saved %s',
                $rule_name,
                $product_name,
                wc_price($totalDiscount)
            );
        }
    }

    public function show_discount_notices()
    {
        if (!is_cart()) {
            return;
        }
        foreach ($this->applied_discounts as $message) {
            wc_add_notice($message, 'notice');
        }
        $this->applied_discounts = [];
    }
}
