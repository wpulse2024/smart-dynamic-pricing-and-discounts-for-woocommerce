// couponRuleTemplates.js
export const couponRuleTemplates = [
    {
        template_key: 'buy3pay2',
        name: '3×2 Offer — Buy 3, Pay for 2',
        status: true,
        priority: 1,
        rule_type: 'special_offer',
        product_scope: {
            scopeType: 'all_products',
            inclusion: { products: [], categories: [], tags: [] },
            exclusion: { type: 'none', products: [], categories: [], tags: [] }
        },
        user_scope: {
            scopeType: 'all_users',
            users: [],
            roles: [],
            exclusion: { type: 'none', users: [], roles: [] },
            apply_on_sale_items: true,
            apply_on_discounted_products: true
        },
        schedule: { start: '', end: '', daysOfWeek: [], specificDates: [] },
        offers: [
            {
                type: 'special_offer',
                condition: { purchaseQuantity: 3, repeat: false },
                reward: {
                    discountedItems: 1,
                    discountType: 'percentage',
                    discountValue: 100,
                    discount_product_type: 'same_product', // can be 'same_product' or 'custom'
                    reward_scope: {
                        type: 'custom_selection', // 'custom_selection' | 'same_product'
                        products: [], // user can choose different product(s)
                        categories: [] // or entire category
                    }
                }
            }
        ],
        meta: {
            description: 'Buy 3 items, get another product or category item free.',
        }
    },

    {
        template_key: 'bogo',
        name: 'Buy 1 Get 1 Free',
        status: true,
        priority: 1,
        rule_type: 'special_offer',
        product_scope: {
            scopeType: 'all_products',
            inclusion: { products: [], categories: [], tags: [] },
            exclusion: { type: 'none', products: [], categories: [], tags: [] }
        },
        user_scope: {
            scopeType: 'all_users',
            users: [],
            roles: [],
            exclusion: { type: 'none', users: [], roles: [] },
            apply_on_sale_items: true,
            apply_on_discounted_products: true
        },
        schedule: { start: '', end: '', daysOfWeek: [], specificDates: [] },
        offers: [
            {
                type: 'special_offer',
                condition: { purchaseQuantity: 1, repeat: false },
                reward: {
                    discountedItems: 1,
                    discountType: 'percentage',
                    discountValue: 100,
                    discount_product_type: 'same_product',
                    specific_products: [], // user can choose different product(s)
                    specific_categories: [] // or entire category
                }
            }
        ],
        meta: { description: 'Buy one, get one free.' }
    },

    {
        template_key: 'half_second',
        name: '50% Off Second Unit',
        status: true,
        priority: 1,
        rule_type: 'special_offer',
        product_scope: {
            scopeType: 'all_products',
            inclusion: { products: [], categories: [], tags: [] },
            exclusion: { type: 'none', products: [], categories: [], tags: [] }
        },
        user_scope: {
            scopeType: 'all_users',
            users: [],
            roles: [],
            exclusion: { type: 'none', users: [], roles: [] },
            apply_on_sale_items: true,
            apply_on_discounted_products: true
        },
        schedule: { start: '', end: '', daysOfWeek: [], specificDates: [] },
        offers: [
            {
                type: 'special_offer',
                condition: { purchaseQuantity: 2, repeat: false },
                reward: {
                    discountedItems: 1,
                    discountType: 'percentage',
                    discountValue: 50,
                    discount_product_type: 'same_product'
                }
            }
        ],
        meta: { description: 'Get 50% off the second product.' }
    },

    {
        template_key: 'bulk_discount',
        name: 'Quantity Discount — Bulk Purchase Savings',
        status: true,
        priority: 1,
        rule_type: 'quantity_discount',
        product_scope: {
            scopeType: 'all_products',
            inclusion: { products: [], categories: [], tags: [] },
            exclusion: { type: 'none', products: [], categories: [], tags: [] }
        },
        user_scope: {
            scopeType: 'all_users',
            users: [],
            roles: [],
            exclusion: { type: 'none', users: [], roles: [] },
            apply_on_sale_items: true,
            apply_on_discounted_products: true
        },
        schedule: { start: '', end: '', daysOfWeek: [], specificDates: [] },
        offers: [
            {
                type: 'quantity_discount',
                tiers: [
                    { min: 1, max: 10, discountType: 'percentage', value: 5 },
                    { min: 11, max: 20, discountType: 'percentage', value: 10 },
                    { min: 21, max: 50, discountType: 'percentage', value: 15 }
                ]
            }
        ],
        meta: { description: 'Bulk purchase discounts by quantity range.' }
    },

    {
        template_key: 'role_discount',
        name: 'User Role Discount — Special Pricing',
        status: true,
        priority: 1,
        rule_type: 'role_discount',
        product_scope: {
            scopeType: 'all_products',
            inclusion: { products: [], categories: [], tags: [] },
            exclusion: { type: 'none', products: [], categories: [], tags: [] }
        },
        user_scope: {
            scopeType: 'user_roles',
            users: [],
            roles: ['wholesale_customer', 'subscriber'],
            exclusion: { type: 'none', users: [], roles: [] },
            apply_on_sale_items: true,
            apply_on_discounted_products: false
        },
        schedule: { start: '', end: '', daysOfWeek: [], specificDates: [] },
        offers: [
            {
                type: 'global_discount',
                reward: {
                    discountType: 'percentage',
                    value: 20
                }
            }
        ],
        meta: { description: 'Discounts based on user roles.' }
    },

    {
        template_key: 'free_gift_on_cart',
        name: 'Free Gift on Cart',
        status: true,
        priority: 1,
        rule_type: 'gift_with_purchase',
        product_scope: {
            scopeType: 'all_products',
            inclusion: { products: [], categories: [], tags: [] },
            exclusion: { type: 'none', products: [], categories: [], tags: [] }
        },
        user_scope: {
            scopeType: 'all_users',
            users: [],
            roles: [],
            exclusion: { type: 'none', users: [], roles: [] },
            apply_on_sale_items: true,
            apply_on_discounted_products: true
        },
        schedule: { start: '', end: '', daysOfWeek: [], specificDates: [] },
        offers: [
            {
                type: 'gift_with_purchase',
                condition: {
                    purchaseQuantity: 3,
                    apply_condition: 'cart_total',
                    min_cart_total: 200,
                    min_cart_quantity: 0
                },
                reward: {
                    giftedItems: 1,
                    gift_product_ids: [] // user selects free gift product
                }
            }
        ],
        meta: { description: 'Get a free gift when you reach cart conditions.' }
    },

    {
        template_key: 'cart_discount',
        name: 'Cart Discount — Percentage Off',
        status: true,
        priority: 1,
        rule_type: 'cart_discount',
        product_scope: {
            scopeType: 'all_products',
            inclusion: { products: [], categories: [], tags: [] },
            exclusion: { type: 'none', products: [], categories: [], tags: [] }
        },
        user_scope: {
            scopeType: 'all_users',
            users: [],
            roles: [],
            exclusion: { type: 'none', users: [], roles: [] },
            apply_on_sale_items: true,
            apply_on_discounted_products: true
        },
        schedule: { start: '', end: '', daysOfWeek: [], specificDates: [] },
        offers: [
            {
                type: 'cart_discount',
                condition: { minCartAmount: 100 },
                reward: { discountType: 'percentage', value: 10 }
            }
        ],
        meta: { description: '10% off entire cart when threshold is met.' }
    },

    {
        template_key: 'free_shipping',
        name: 'Free Shipping — Orders Above Amount',
        status: true,
        priority: 1,
        rule_type: 'free_shipping',
        product_scope: {
            scopeType: 'all_products',
            inclusion: { products: [], categories: [], tags: [] },
            exclusion: { type: 'none', products: [], categories: [], tags: [] }
        },
        user_scope: {
            scopeType: 'all_users',
            users: [],
            roles: [],
            exclusion: { type: 'none', users: [], roles: [] },
            apply_on_sale_items: true,
            apply_on_discounted_products: true
        },
        schedule: { start: '', end: '', daysOfWeek: [], specificDates: [] },
        offers: [
            {
                type: 'free_shipping',
                condition: { minCartAmount: 150 },
                reward: { shippingCost: 0 }
            }
        ],
        meta: { description: 'Free shipping on eligible orders.' }
    }
];


// 🔍 Utility function
export function getTemplateByKey(key) {
    return couponRuleTemplates.find(t => t.template_key === key) || null;
}
