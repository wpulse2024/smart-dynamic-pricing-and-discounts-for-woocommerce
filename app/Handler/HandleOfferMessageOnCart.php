<?php

namespace SmartDynamicPricing\Handler;

use SmartDynamicPricing\Models\Rule;
use SmartDynamicPricing\Helpers\ValidateApplyDiscount;

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
                       echo '<p style="color:#2e7d32; font-size:13px; margin-top:4px;" class="offer-applied">Applied - '. esc_html( $rule->name ) .'</p>';
                   } else if ($qty < $buy) {
                       echo '<p style="color:#f16334; font-size:13px; margin-top:4px;" class="offer-not-applied">Buy ' . esc_html(($buy - $qty)) . ' more to get - ' . esc_html( $rule->name ) . ' offer</p>';
                   } else if ($qty == $buy) {
                       echo '<p style="color:#0073aa; font-size:13px; margin-top:4px;" class="offer-applied">You are qualify for the get - ' . esc_html( $rule->name ) . ' offer</p>';
                   }
               }
               if (($offer['type'] ?? '') == 'quantity_discount') {
                   $tiers = $offer['tiers'] ?? [];
                   $qty = $cart_item['quantity'];
                   foreach ($tiers as $tier) {
                       $min = (int) ($tier['min'] ?? 0);
                       $max = (int) ($tier['max'] ?? 0);
                       if ($qty >= $min && $qty <= $max) {
                           $this->applied_discounts[] = $cart_item['product_id'];
                           echo '<p style="color:#2e7d32; font-size:13px; margin-top:4px;" class="offer-applied">Applied - '. esc_html( $rule->name ) .'</p>';
                       }
                   }
               }
               if (($offer['type'] ?? '') == 'global_discount') {
                   $this->applied_discounts[] = $cart_item['product_id'];
                   echo '<p style="color:#2e7d32; font-size:13px; margin-top:4px;" class="offer-applied">Applied - '. esc_html( $rule->name ) .'</p>';
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
                if (($offer['type'] ?? '') == 'special_offer') {

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
                    echo '🎁 <strong>Special Offer:</strong> Buy <strong>' . esc_html( $buy ) . '</strong> to get <strong>' . esc_html( $get ) . '</strong> free!';
                    echo '</div>';
                }
                if (($offer['type'] ?? '') == 'quantity_discount') {
                    $tiers = $offer['tiers'] ?? [];
                    if (empty($tiers)) {
                        continue;
                    }
                    $this->applied_discounts[] = $product->get_id();
                    // shows tiers table price 
                    echo '<div class="single-offer-note" style="margin: 20px 0; padding: 0; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
                        echo '<table class="offer-tiers-table" style="width: 100%; border-collapse: collapse;">';
                            echo '<thead>';
                                echo '<tr style="background: linear-gradient(to bottom, #f9fafb, #f3f4f6);">';
                                    echo '<th style="text-align: left; padding: 12px 16px; font-weight: 600; font-size: 13px; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Quantity</th>';
                                    echo '<th style="text-align: left; padding: 12px 16px; font-weight: 600; font-size: 13px; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb;">Discount</th>';
                                echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            foreach ($tiers as $index => $tier) {
                                $min = (int) ($tier['min'] ?? 0);
                                $max = (int) ($tier['max'] ?? 0);
                                $discount_type = $tier['discountType'] ?? 'percentage';
                                $discount_value = (float) ($tier['value'] ?? 0);
                                
                                $bg_color = $index % 2 === 0 ? '#ffffff' : '#f9fafb';
                                $discount_display = $discount_type === 'percentage' ? $discount_value . '%' : wc_price($discount_value);
                                
                                echo '<tr style="background: ' . esc_attr($bg_color) . '; transition: background 0.2s ease;">';
                                    echo '<td style="text-align: left; padding: 14px 16px; font-size: 14px; color: #1f2937; border-bottom: 1px solid #f3f4f6;">' . esc_html($min) . ' - ' . esc_html($max) . '</td>';
                                    echo '<td style="text-align: left; padding: 14px 16px; font-size: 14px; font-weight: 600; border-bottom: 1px solid #f3f4f6;">' . wp_kses_post($discount_display) . '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                        echo '</table>';
                    echo '</div>';
                }
            }
        }
    }

}
