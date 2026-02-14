<?php

namespace WpulsePricingRules\Includes\Admin;

/**
 * Single source of truth for rule templates.
 * Templates::all() returns template definitions (id, title, type, icon, defaults, description).
 * Scratch types are used for "start from scratch" links with minimal config.
 */
class Templates {

    /**
     * All templates for the modal grid (id, title, type, icon, defaults, description).
     *
     * @return array<int, array{id: string, title: string, type: string, icon: string, defaults: array, description?: string}>
     */
    public static function all(): array {
        return [
            [
                'id'          => '3x2',
                'title'       => __('3x2', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'quantity_discount',
                'icon'        => 'dashicons-editor-table',
                'description' => __('Buy 3 pay for 2', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'benefit' => [
                        'kind'        => 'x_for_y',
                        'buy_qty'     => 3,
                        'pay_qty'     => 2,
                        'repeat'      => true,
                        'apply_mode'  => 'cheapest_free',
                    ],
                ],
            ],
            [
                'id'          => 'bogo',
                'title'       => __('Buy 1 Get 1', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'gift',
                'icon'        => 'dashicons-cart',
                'description' => __('Buy one get one free', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'benefit' => [
                        'kind'         => 'buy_x_get_y',
                        'buy'          => 1,
                        'get'          => 1,
                        'get_discount' => 100,
                        'same_product' => true,
                        'repeat'       => true,
                    ],
                ],
            ],
            [
                'id'          => '2x1',
                'title'       => __('2x1', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'quantity_discount',
                'icon'        => 'dashicons-editor-table',
                'description' => __('Buy 2 pay for 1', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'benefit' => [
                        'kind'    => 'x_for_y',
                        'buy_qty' => 2,
                        'pay_qty' => 1,
                        'repeat'  => true,
                        'apply_mode' => 'cheapest_free',
                    ],
                ],
            ],
            [
                'id'          => '50pct-2nd',
                'title'       => __('50% on 2nd unit', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'quantity_discount',
                'icon'        => 'dashicons-percent',
                'description' => __('50% off the second unit', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'benefit' => [
                        'kind'             => 'nth_percent_off',
                        'nth'              => 2,
                        'percent'          => 50,
                        'apply_to_each_set' => true,
                    ],
                ],
            ],
            [
                'id'          => 'black-friday',
                'title'       => __('Black Friday promo', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'global_discount',
                'icon'        => 'dashicons-calendar-alt',
                'description' => __('Store-wide percentage discount', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'benefit' => [
                        'kind'   => 'percent_off',
                        'percent' => 20,
                    ],
                    'schedule' => [
                        'start' => '',
                        'end'   => '',
                    ],
                ],
            ],
            [
                'id'          => 'qty-discount',
                'title'       => __('Qty discount', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'quantity_discount',
                'icon'        => 'dashicons-chart-bar',
                'description' => __('Tiered quantity discount', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'benefit' => [
                        'kind'  => 'tiered',
                        'tiers' => [
                            ['min' => 2, 'max' => 4, 'percent_off' => 5, 'fixed_off' => 0],
                            ['min' => 5, 'max' => 9, 'percent_off' => 10, 'fixed_off' => 0],
                            ['min' => 10, 'max' => 0, 'percent_off' => 15, 'fixed_off' => 0],
                        ],
                    ],
                ],
            ],
            [
                'id'          => 'user-role-discount',
                'title'       => __('User role discount', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'user_role_discount',
                'icon'        => 'dashicons-groups',
                'description' => __('Discount by customer role', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    ['type' => 'user_role', 'operator' => 'in', 'value' => ['customer']],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [
                        'kind'   => 'percent_off',
                        'percent' => 25,
                    ],
                ],
            ],
            [
                'id'          => 'buy-x-get-y-free',
                'title'       => __('Buy X Get Y Free', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'gift',
                'icon'        => 'dashicons-tag',
                'description' => __('Buy X get Y free (product selectors)', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'benefit' => [
                        'kind'         => 'buy_x_get_y',
                        'buy_selector' => [], // products/categories
                        'get_selector' => [],
                        'get_discount' => 100,
                        'repeat'       => true,
                    ],
                ],
            ],
            [
                'id'          => 'free-gift-on-cart',
                'title'       => __('Free gift on cart', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'gift',
                'icon'        => 'dashicons-cart',
                'description' => __('Free gift when cart subtotal meets threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    ['type' => 'cart_subtotal', 'operator' => '>=', 'value' => 50],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [
                        'kind'       => 'free_gift',
                        'product_ids' => [],
                    ],
                ],
            ],
            [
                'id'          => 'cart-discount',
                'title'       => __('Cart discount', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'cart_discount',
                'icon'        => 'dashicons-cart',
                'description' => __('Discount when cart subtotal meets threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    ['type' => 'cart_subtotal', 'operator' => '>=', 'value' => 100],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [
                        'kind'   => 'cart_percent_off',
                        'percent' => 10,
                    ],
                ],
            ],
            [
                'id'          => 'free-shipping',
                'title'       => __('Free shipping', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'free_shipping',
                'icon'        => 'dashicons-location-alt',
                'description' => __('Free shipping when cart meets threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    ['type' => 'cart_subtotal', 'operator' => '>=', 'value' => 75],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => ['kind' => 'free_shipping'],
                ],
            ],
            [
                'id'          => 'checkout-last-deal',
                'title'       => __('Checkout – Last Deal', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'checkout_deal',
                'icon'        => 'dashicons-clock',
                'description' => __('Opt-in deal on checkout page', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::defaultRule() + [
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    ['type' => 'page', 'operator' => '=', 'value' => 'checkout'],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [
                        'kind'   => 'percent_off',
                        'percent' => 10,
                    ],
                    'meta' => [
                        'checkout_opt_in_message' => __('Last chance: 10% off your order!', 'wpulse-pricing-rules-for-woocommerce'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Scratch types for "start from scratch" (key => label for UI).
     *
     * @return array<string, string>
     */
    public static function scratchTypes(): array {
        return [
            'quantity_discount' => __('Set a quantity discount', 'wpulse-pricing-rules-for-woocommerce'),
            'special_offer'     => __('Create a special offer', 'wpulse-pricing-rules-for-woocommerce'),
            'gift'              => __('Gift products', 'wpulse-pricing-rules-for-woocommerce'),
            'global_discount'   => __('Set a global discount', 'wpulse-pricing-rules-for-woocommerce'),
            'category_discount' => __('Set a category discount', 'wpulse-pricing-rules-for-woocommerce'),
            'cart_discount'     => __('Set a cart discount', 'wpulse-pricing-rules-for-woocommerce'),
        ];
    }

    /**
     * Get minimal rule config for a scratch type. Returns null if invalid.
     *
     * @return array{name: string, type: string, rule: array}|null
     */
    public static function getScratchDefaults(string $scratch_type): ?array {
        $labels = self::scratchTypes();
        $label = $labels[$scratch_type] ?? null;
        if (!$label) {
            return null;
        }
        $rule = self::defaultRule();
        switch ($scratch_type) {
            case 'quantity_discount':
                $rule['benefit'] = ['kind' => 'tiered', 'tiers' => []];
                break;
            case 'special_offer':
                $rule['benefit'] = ['kind' => 'percent_off', 'percent' => 0];
                break;
            case 'gift':
                $rule['benefit'] = ['kind' => 'free_gift', 'product_ids' => []];
                break;
            case 'global_discount':
                $rule['benefit'] = ['kind' => 'percent_off', 'percent' => 0];
                $rule['type'] = 'global_discount';
                break;
            case 'category_discount':
                $rule['benefit'] = [
                    'kind'              => 'category_discounts',
                    'category_discounts' => [['apply_type' => 'percent', 'value' => 10, 'category_ids' => []]],
                ];
                $rule['exclusions'] = ['enabled' => false, 'type' => 'products', 'ids' => []];
                break;
            case 'cart_discount':
                $rule['conditions'] = ['groups' => [['logic' => 'and', 'items' => []]]];
                $rule['benefit'] = ['kind' => 'cart_percent_off', 'percent' => 0];
                $rule['type'] = 'cart_discount';
                break;
            default:
                return null;
        }
        return [
            'name' => $label,
            'type' => $rule['type'] ?? $scratch_type,
            'rule' => $rule,
        ];
    }

    private static function defaultRule(): array {
        return [
            'targets'     => ['type' => 'all', 'products' => [], 'categories' => []],
            'conditions'  => ['groups' => [['logic' => 'and', 'items' => []]]],
            'benefit'     => ['kind' => 'percent_off', 'percent' => 0],
            'exclusions'  => ['enabled' => false, 'type' => 'products', 'ids' => []],
            'limits'      => ['max_uses' => 0, 'max_uses_per_user' => 0],
            'stacking'    => ['stop_processing' => false, 'can_stack_with_other_rules' => true],
            'schedule'    => ['start' => '', 'end' => ''],
            'meta'        => [],
        ];
    }
}
