<?php

namespace SmartDynamicPricingDiscounts\Handler;

use SmartDynamicPricingDiscounts\Models\Rule;
use SmartDynamicPricingDiscounts\Helpers\ValidateApplyDiscount;

class HandleOfferMessageOnCart
{
    protected $rules = [];
    protected $applied_discounts = [];
   public function __construct()
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
        add_action('woocommerce_after_cart_item_name', [$this, 'custom_cart_item_offer_note'], 10, 2);
        add_action('woocommerce_before_add_to_cart_button', [$this, 'show_single_product_offer_note']);
   }

   public function custom_cart_item_offer_note($cart_item, $cart_item_key)
   {
       // check the cart item if it has a rule applied if not then shows like buy 1 more item to get discount and if match then shows applied bogo discount
       foreach ($this->rules as $rule) {
            if (!ValidateApplyDiscount::validate($rule, $cart_item, $cart_item_key)) {
                continue;
            }

            if (empty($rule->offers) || !is_array($rule->offers)) {
                continue;
            }
           foreach ($rule->offers as $offer) {
               // check if offer is special offer
               if(in_array($cart_item['product_id'], $this->applied_discounts)) {
                   continue;
               }
               if (($offer['type'] ?? '') == 'special_offer') {
                   $condition = $offer['condition'] ?? [];
                   $reward = $offer['reward'] ?? [];

                   $buy = (int) ($condition['purchaseQuantity'] ?? 0);
                   $repeat = (bool) ($condition['repeat'] ?? false);

                   $get = (int) ($reward['discountedItems'] ?? 0);
                   $discount_value = (float) ($reward['discountValue'] ?? 0);
                   $discount_type = $reward['discountType'] ?? 'percentage';
                   $discount_product_type = $reward['discount_product_type'] ?? 'same_product';

                   if ($buy <= 0 || $get <= 0) {
                       continue;
                   }

                   $qty = $cart_item['quantity'];
                   $group_size = $buy + $get;
                   $times = $repeat ? floor($qty / $group_size) : ($qty >= $group_size ? 1 : 0);
                   // push product id to array
                   $this->applied_discounts[] = $cart_item['product_id'];
                   if ($times > 0) {
                       echo '<p style="color:#2e7d32; font-size:13px; margin-top:4px;" class="offer-applied">Applied - '. $rule->name .'</p>';
                   } else if ($qty < $buy) {
                       echo '<p style="color:#f16334; font-size:13px; margin-top:4px;" class="offer-not-applied">Buy ' . ($buy - $qty) . ' more to get - ' . $rule->name . ' offer</p>';
                   } else if ($qty == $buy) {
                       echo '<p style="color:#0073aa; font-size:13px; margin-top:4px;" class="offer-applied">You are qualify for the get - ' . $rule->name . ' offer</p>';
                   }
               }
           }
       }
   }

    public function show_single_product_offer_note()
    {
        global $product;

        // Ensure we're on a single product page
        if (!is_product() || empty($this->rules)) {
            return;
        }

        foreach ($this->rules as $rule) {

            if(in_array($product->get_id(), $this->applied_discounts)) {
                continue;
            }

            if (!ValidateApplyDiscount::isValidProduct($rule, $product)) {
                continue;
            }

            if (empty($rule->offers) || !is_array($rule->offers)) {
                continue;
            }

            foreach ($rule->offers as $offer) {
                if (($offer['type'] ?? '') !== 'special_offer') {
                    continue;
                }

                $condition = $offer['condition'] ?? [];
                $reward = $offer['reward'] ?? [];

                $buy = (int) ($condition['purchaseQuantity'] ?? 0);
                $get = (int) ($reward['discountedItems'] ?? 0);

                if ($buy <= 0 || $get <= 0) {
                    continue;
                }

                // push product id to array
                $this->applied_discounts[] = $product->get_id();

                echo '<div class="single-offer-note" style="margin:10px 0px; padding:8px 12px; background:#f8f8f8; border-radius:6px; font-size:14px;">';
                echo '🎁 <strong>Special Offer:</strong> Buy <strong>' . $buy . '</strong> to get <strong>' . $get . '</strong> free!';
                echo '</div>';
            }
        }
    }

}
