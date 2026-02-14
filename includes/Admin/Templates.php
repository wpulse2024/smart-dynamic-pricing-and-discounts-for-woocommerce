<?php

namespace WpulsePricingRules\Includes\Admin;

/**
 * Single source of truth for rule templates.
 * Templates::all() returns template definitions (id, title, type, icon, defaults, description).
 * Scratch types are used for "start from scratch" links with minimal config.
 * All template configs are merged with defaultRule() so the editor always receives a complete rule (production-ready).
 */
class Templates {

    /**
     * Full default rule structure (matches RuleEditorView.vue defaultRule). Used as base for every template/scratch.
     *
     * @return array<string, mixed>
     */
    public static function defaultRule(): array {
        return [
            'targets'     => [ 'type' => 'all', 'products' => [], 'categories' => [] ],
            'conditions'  => [ 'groups' => [ [ 'logic' => 'and', 'items' => [] ] ] ],
            'benefit'     => [
                'kind'              => 'percent_off',
                'percent'           => 0,
                'amount'            => 0,
                'tiers'             => [],
                'buy_qty'           => 2,
                'pay_qty'           => 1,
                'nth'               => 2,
                'product_ids'       => [],
                'category_discounts' => [],
            ],
            'exclusions'  => [ 'enabled' => false, 'type' => 'products', 'ids' => [] ],
            'limits'      => [ 'max_uses' => 0, 'max_uses_per_user' => 0 ],
            'stacking'    => [ 'stop_processing' => false, 'can_stack_with_other_rules' => true ],
            'schedule'    => [ 'start' => '', 'end' => '' ],
            'meta'        => [ 'show_badge' => true, 'show_on_shop' => true, 'custom_message' => '' ],
        ];
    }

    /**
     * Deep-merge overlay into defaultRule() so templates only need to override specific keys.
     *
     * @param array<string, mixed> $overlay Template-specific overrides (e.g. benefit.kind, conditions).
     * @return array<string, mixed> Complete rule config.
     */
    public static function mergeWithDefault(array $overlay): array {
        $default = self::defaultRule();
        foreach ( $overlay as $key => $value ) {
            if ( $key === 'benefit' && is_array( $value ) && isset( $default['benefit'] ) && is_array( $default['benefit'] ) ) {
                $default['benefit'] = array_merge( $default['benefit'], $value );
            } elseif ( $key === 'conditions' && is_array( $value ) ) {
                $default['conditions'] = $value;
            } elseif ( $key === 'targets' && is_array( $value ) ) {
                $default['targets'] = array_merge( $default['targets'], $value );
            } elseif ( $key === 'exclusions' && is_array( $value ) ) {
                $default['exclusions'] = array_merge( $default['exclusions'], $value );
            } elseif ( $key === 'schedule' && is_array( $value ) ) {
                $default['schedule'] = array_merge( $default['schedule'], $value );
            } elseif ( $key === 'meta' && is_array( $value ) ) {
                $default['meta'] = array_merge( $default['meta'], $value );
            } else {
                $default[ $key ] = $value;
            }
        }
        return $default;
    }

    /**
     * All templates for the modal grid (id, title, type, icon, defaults, description).
     * Title is used as the rule name (production-style label so users don't need to change it).
     *
     * @return array<int, array{id: string, title: string, type: string, icon: string, defaults: array, description?: string}>
     */
    public static function all(): array {
        return [
            [
                'id'          => '3x2',
                'title'       => __('3 for 2 – Buy 3 pay for 2', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'quantity_discount',
                'icon'        => 'dashicons-editor-table',
                'description' => __('Buy 3 pay for 2', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'benefit' => [
                        'kind'        => 'x_for_y',
                        'buy_qty'     => 3,
                        'pay_qty'     => 2,
                        'repeat'      => true,
                        'apply_mode'  => 'cheapest_free',
                    ],
                ]),
            ],
            [
                'id'          => 'bogo',
                'title'       => __('Buy 1 Get 1 Free – BOGO', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'gift',
                'icon'        => 'dashicons-cart',
                'description' => __('Buy one get one free', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'benefit' => [
                        'kind'         => 'x_for_y',
                        'buy'          => 1,
                        'get'          => 1,
                        'get_discount' => 100,
                        'same_product' => true,
                        'repeat'       => true,
                    ],
                ]),
            ],
            [
                'id'          => '2x1',
                'title'       => __('2 for 1 – Buy 2 pay for 1', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'quantity_discount',
                'icon'        => 'dashicons-editor-table',
                'description' => __('Buy 2 pay for 1', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'benefit' => [
                        'kind'       => 'x_for_y',
                        'buy_qty'    => 2,
                        'pay_qty'    => 1,
                        'repeat'     => true,
                        'apply_mode' => 'cheapest_free',
                    ],
                ]),
            ],
            [
                'id'          => '50pct-2nd',
                'title'       => __('50% off 2nd unit – Nth unit discount', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'quantity_discount',
                'icon'        => 'dashicons-percent',
                'description' => __('50% off the second unit', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'benefit' => [
                        'kind'              => 'nth_percent_off',
                        'nth'               => 2,
                        'percent'           => 50,
                        'apply_to_each_set' => true,
                    ],
                ]),
            ],
            [
                'id'          => 'black-friday',
                'title'       => __('Black Friday – Store-wide % discount', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'global_discount',
                'icon'        => 'dashicons-calendar-alt',
                'description' => __('Store-wide percentage discount', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'benefit' => [
                        'kind'    => 'percent_off',
                        'percent' => 20,
                    ],
                    'schedule' => [ 'start' => '', 'end' => '' ],
                ]),
            ],
            [
                'id'          => 'qty-discount',
                'title'       => __('Tiered quantity discount', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'quantity_discount',
                'icon'        => 'dashicons-chart-bar',
                'description' => __('Tiered quantity discount', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'benefit' => [
                        'kind'  => 'tiered',
                        'tiers' => [
                            [ 'min' => 2, 'max' => 4, 'percent_off' => 5, 'fixed_off' => 0 ],
                            [ 'min' => 5, 'max' => 9, 'percent_off' => 10, 'fixed_off' => 0 ],
                            [ 'min' => 10, 'max' => 0, 'percent_off' => 15, 'fixed_off' => 0 ],
                        ],
                    ],
                ]),
            ],
            [
                'id'          => 'user-role-discount',
                'title'       => __('User role discount – e.g. Customer 25% off', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'user_role_discount',
                'icon'        => 'dashicons-groups',
                'description' => __('Discount by customer role', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items'  => [
                                    [ 'type' => 'user_role', 'operator' => 'in', 'value' => [ 'customer' ] ],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [ 'kind' => 'percent_off', 'percent' => 25 ],
                ]),
            ],
            [
                'id'          => 'buy-x-get-y-free',
                'title'       => __('Buy X Get Y Free – Product selectors', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'gift',
                'icon'        => 'dashicons-tag',
                'description' => __('Buy X get Y free (product selectors)', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'benefit' => [
                        'kind'         => 'x_for_y',
                        'buy_selector' => [],
                        'get_selector' => [],
                        'get_discount' => 100,
                        'repeat'       => true,
                    ],
                ]),
            ],
            [
                'id'          => 'free-gift-on-cart',
                'title'       => __('Free gift when cart subtotal ≥ threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'gift',
                'icon'        => 'dashicons-cart',
                'description' => __('Free gift when cart subtotal meets threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    [ 'type' => 'cart_subtotal', 'operator' => '>=', 'value' => 50 ],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [ 'kind' => 'free_gift', 'product_ids' => [] ],
                ]),
            ],
            [
                'id'          => 'cart-discount',
                'title'       => __('Cart % off when subtotal ≥ threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'cart_discount',
                'icon'        => 'dashicons-cart',
                'description' => __('Discount when cart subtotal meets threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    [ 'type' => 'cart_subtotal', 'operator' => '>=', 'value' => 100 ],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [ 'kind' => 'cart_percent_off', 'percent' => 10 ],
                ]),
            ],
            [
                'id'          => 'free-shipping',
                'title'       => __('Free shipping when cart meets threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'free_shipping',
                'icon'        => 'dashicons-location-alt',
                'description' => __('Free shipping when cart meets threshold', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    [ 'type' => 'cart_subtotal', 'operator' => '>=', 'value' => 75 ],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [ 'kind' => 'free_shipping' ],
                ]),
            ],
            [
                'id'          => 'checkout-last-deal',
                'title'       => __('Checkout – Last chance deal (opt-in)', 'wpulse-pricing-rules-for-woocommerce'),
                'type'        => 'checkout_deal',
                'icon'        => 'dashicons-clock',
                'description' => __('Opt-in deal on checkout page', 'wpulse-pricing-rules-for-woocommerce'),
                'defaults'    => self::mergeWithDefault([
                    'conditions' => [
                        'groups' => [
                            [
                                'logic' => 'and',
                                'items' => [
                                    [ 'type' => 'page', 'operator' => '=', 'value' => 'checkout' ],
                                ],
                            ],
                        ],
                    ],
                    'benefit' => [ 'kind' => 'percent_off', 'percent' => 10 ],
                    'meta'    => [
                        'checkout_opt_in_message' => __('Last chance: 10% off your order!', 'wpulse-pricing-rules-for-woocommerce'),
                    ],
                ]),
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
     * Get rule config for a scratch type. Always returns a full rule merged with defaultRule (production-ready).
     *
     * @return array{name: string, type: string, rule: array}|null
     */
    public static function getScratchDefaults(string $scratch_type): ?array {
        $labels = self::scratchTypes();
        $label = $labels[ $scratch_type ] ?? null;
        if ( ! $label ) {
            return null;
        }
        $overlay = [];
        switch ( $scratch_type ) {
            case 'quantity_discount':
                $overlay = [ 'benefit' => [ 'kind' => 'tiered', 'tiers' => [] ] ];
                break;
            case 'special_offer':
                $overlay = [ 'benefit' => [ 'kind' => 'percent_off', 'percent' => 0 ] ];
                break;
            case 'gift':
                $overlay = [ 'benefit' => [ 'kind' => 'free_gift', 'product_ids' => [] ] ];
                break;
            case 'global_discount':
                $overlay = [ 'benefit' => [ 'kind' => 'percent_off', 'percent' => 0 ] ];
                break;
            case 'category_discount':
                $overlay = [
                    'benefit'    => [
                        'kind'               => 'category_discounts',
                        'category_discounts' => [ [ 'apply_type' => 'percent', 'value' => 10, 'category_ids' => [] ] ],
                    ],
                ];
                break;
            case 'cart_discount':
                $overlay = [
                    'conditions' => [ 'groups' => [ [ 'logic' => 'and', 'items' => [] ] ] ],
                    'benefit'    => [ 'kind' => 'cart_percent_off', 'percent' => 0 ],
                ];
                break;
            default:
                return null;
        }
        $rule = self::mergeWithDefault( $overlay );
        return [
            'name' => $label,
            'type' => $scratch_type,
            'rule' => $rule,
        ];
    }
}
