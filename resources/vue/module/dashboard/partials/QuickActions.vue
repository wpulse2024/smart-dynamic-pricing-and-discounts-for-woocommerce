<template>
    <div class="fraise_quick-actions">
        <!-- <h3 class="fraise_quick-actions-title">Quick Actions</h3> -->

        <div class="fraise_btn-wrapper">
            <!-- Create From Template -->
            <el-button class="fraise_btn-template" @click="templateDialogVisible = true">
                <el-icon>
                    <plus />
                </el-icon>
                Create New Rule
            </el-button>
        </div>

        <!-- Template Dialog -->
        <el-dialog v-model="templateDialogVisible" title="Choose a Template" width="800px" :close-on-click-modal="false"
            class="fraise_modal template-modal">
            <div class="create_new_role_modal_body">
                <div>
                    <p class="fraise_modal-subtitle">Select a pre-built template to get started quickly</p>
                    <div class="fraise_template-grid">
                        <div :class="['fraise_template-card', { 'upcoming': template?.is_upcoming }]" v-for="(template, index) in templates" :key="index" @click="createCustomRule('template', template.key, template?.is_upcoming)">
                            <div class="fraise_template-icon" :style="{ background: template.color }">
                                <el-icon :size="24">
                                    <component :is="template.icon" />
                                </el-icon>
                            </div>
                            <div class="fraise_template-title">{{ template.title }}</div>
                            <div class="fraise_template-desc">{{ template.desc }}</div>
                        </div>
                    </div>
                </div>
                <div class="create_from_scratch_wrapper">
                    <p class="fraise_modal-subtitle">Create From Scratch Choose Type of Discount</p>
                    <ul class="create_from_scratch_list">
                        <li :class="{ 'upcoming': template?.is_upcoming }" @click="createCustomRule('custom', template.key, template?.is_upcoming)" v-for="(template, index) in discountTypes" :key="index">
                            <el-tooltip v-if="template?.is_upcoming" content="Coming soon!" placement="top">
                                {{ template.title }}
                            </el-tooltip>
                            <span v-if="!template?.is_upcoming">{{ template.title }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import {
    Plus,
    MagicStick,
    Goods,
    Present,
    Discount,
    ShoppingCart,
    Truck,
    User,
    Star,
} from '@element-plus/icons-vue'

export default {
    name: 'QuickActions',
    components: {
        Plus,
        MagicStick,
        Goods,
        Present,
        Discount,
        ShoppingCart,
        Truck,
        User,
        Star,
    },
    data() {
        return {
            templateDialogVisible: false,
            customRuleDialogVisible: false,
            activeTab: 'general',
            ruleStatus: true,
            priority: 1,
            discountTypes: [
                { title: 'Quantity Discount', key: 'quantity_discount' },
                { title: 'Special Offer', key: 'special_offer' },
                { title: 'Global Discount', key: 'global_discount' },
                { title: 'Gift with Purchase', key: 'gift_with_purchase', is_upcoming: true },
                { title: 'Cart Discount', key: 'cart_discount', is_upcoming: true },
                { title: 'Category Discount', key: 'category_discount', is_upcoming: true },
            ],
            templates: [
                { title: '3×2 Offer', desc: 'Buy 3 items, pay for 2', color: '#6366f1', icon: 'Goods', key: 'buy3pay2' },
                { title: 'BOGO', desc: 'Buy 1 Get 1 Free', color: '#8b5cf6', icon: 'Present', key: 'bogo' },
                { title: '50% Off Second Unit', desc: 'Half price on second item', color: '#10b981', icon: 'Discount', key: 'half_second' },
                { title: 'Quantity Discount', desc: 'Bulk purchase savings', color: '#f97316', icon: 'ShoppingCart', key: 'bulk_discount' },
                { title: 'User Role Discount', desc: 'Special pricing by role', color: '#ec4899', icon: 'User', key: 'role_discount' },
                { title: 'Free Gift on Cart', desc: 'Free product with purchase', color: '#f59e0b', icon: 'Star', key: 'free_gift_on_cart', is_upcoming: true },
                { title: 'Cart Discount', desc: 'Percentage off cart total', color: '#ef4444', icon: 'Discount', key: 'cart_discount', is_upcoming: true },
                { title: 'Free Shipping', desc: 'Free delivery on orders', color: '#06b6d4', icon: 'Truck', key: 'free_shipping', is_upcoming: true },
            ],
            rule: {
                // 🏷️ Basic Info
                id: '',
                name: '',
                status: true,
                priority: 1,
                ruleType: 'quantity_discount', // Options: quantity_discount, special_offer, gift_with_purchase, global_discount, cart_discount, category_discount

                // 🛒 Product Scope
                product_scope: {
                    scopeType: 'all_products', // Options: all_products, specific_products, product_categories, product_tags
                    inclusion: {
                        products: [],             // Array of product IDs
                        categories: [],           // Array of category IDs
                        tags: []                  // Array of tag IDs
                    },
                    exclusion: {
                        type: 'none',             // Options: none, specific_products, product_categories, product_tags
                        products: [],             // Array of product IDs
                        categories: [],           // Array of category IDs
                        tags: []                  // Array of tag IDs
                    }
                },

                // 👥 User Scope
                user_scope: {
                    scopeType: 'all_users',       // Options: all_users, specific_users, user_roles
                    users: [],                    // Array of user IDs
                    roles: [],                    // Array of role slugs or IDs
                    exclusion: {
                        type: 'none',             // Options: none, specific_users, user_roles
                        users: [],                // Array of user IDs
                        roles: []                 // Array of role IDs
                    },
                    apply_on_sale_items: true,    // Whether rule affects sale items
                    apply_on_discounted_products: true // Whether rule affects already discounted items
                },

                // 📅 Schedule
                schedule: {
                    start: '',                    // ISO datetime string e.g., "2025-10-15T00:00:00"
                    end: '',                      // ISO datetime string e.g., "2025-10-31T23:59:59"
                    daysOfWeek: [],               // Array of day names e.g., ['monday', 'friday']
                    specificDates: []             // Array of specific dates e.g., ['2025-10-20']
                },

                // 🎁 Offers (Normalized)
                offers: [
                    // 🧮 Quantity Discount (Tiered)
                    {
                        type: 'quantity_discount',
                        tiers: [
                            {
                                min: 1,
                                max: 10,
                                discountType: 'percentage', // Options: percentage, fixed_amount
                                value: 10
                            },
                            {
                                min: 11,
                                max: 20,
                                discountType: 'percentage',
                                value: 20
                            }
                        ]
                    },

                    // 🎟️ Special Offer (Buy X Get Y)
                    {
                        type: 'special_offer',
                        condition: {
                            purchaseQuantity: 3,
                            offerBasedOn: 'item_quantity_in_cart_line', // Options: item_quantity_in_cart_line, single_product_quantity_variations_not_counted, single_product_quantity_variations_counted, total_products_quantity_in_cart
                            repeat: false
                        },
                        reward: {
                            discountedItems: 1,
                            discountType: 'percentage', // Options: percentage, fixed_amount
                            discountValue: 100,
                            discount_product_type: 'same_product', // Options: same_product, specific_product, specific_product_category, specific_product_tag, all_products
                            discount_product_id: '',     // Product ID
                            discount_product_category: '', // Category ID
                            discount_product_tag: ''       // Tag ID
                        }
                    },

                    // 🎁 Gift with Purchase
                    {
                        type: 'gift_with_purchase',
                        condition: {
                            purchaseQuantity: 3,
                            apply_condition: 'cart_total', // Options: cart_total, cart_quantity
                            min_cart_total: 0,
                            min_cart_quantity: 0
                        },
                        reward: {
                            giftedItems: 1,
                            gift_product_ids: [] // Array of product IDs
                        }
                    },

                    // 🌐 Global Discount
                    {
                        type: 'global_discount',
                        reward: {
                            discountType: 'percentage', // Options: percentage, fixed_amount
                            value: 10
                        }
                    },

                    // 🛒 Cart Discount
                    {
                        type: 'cart_discount',
                        condition: {
                            minCartAmount: 100
                        },
                        reward: {
                            discountType: 'percentage', // Options: percentage, fixed_amount
                            value: 10
                        }
                    },

                    // 🏷️ Category Discount
                    {
                        type: 'category_discount',
                        condition: {
                            categories: [], // Array of category IDs
                            minQuantity: 1,
                            maxQuantity: 10
                        },
                        reward: {
                            discountType: 'percentage', // Options: percentage, fixed_amount
                            value: 15
                        }
                    }
                ],

                // 🧾 Meta Information
                meta: {
                    createdBy: '',                    // Admin/User ID
                    createdAt: '',                    // ISO datetime string e.g. "2025-10-10T10:00:00"
                    updatedAt: '',                    // ISO datetime string
                    usageLimit: 0,                    // Max number of uses (0 = unlimited)
                    usageCount: 0,                    // How many times used so far
                    description: '',                  // Short description of the rule
                    notes: ''                         // Developer or internal notes
                }
            }
        } 
    },
    methods: {
        saveRule() {
            this.customRuleDialogVisible = false
            this.$message.success('Rule saved successfully!')
        },
        createCustomRule(type, templateKey, is_upcoming = false) {
            if (is_upcoming) {
                this.$message.warning('Coming soon!')
                return;
            } else {
                if (type === 'template') {
                    this.$router.push({ name: 'add-new-role-template', params: { template: templateKey }})
                } else {
                    this.$router.push({ name: 'create-new-rule', params: { type: templateKey }})
                }
            }
        }
    },
}
</script>

<style lang="scss">
/* Create From Scratch Section */
.create_from_scratch_wrapper {
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.create_from_scratch_wrapper .fraise_modal-subtitle {
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    margin-top: 0px;
    margin-bottom: 16px;
}

.create_from_scratch_list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    .upcoming {
        opacity: 0.5;
        cursor: not-allowed;
    }
}

.create_from_scratch_list li {
    padding: 14px 18px;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.2s ease;
}

.create_from_scratch_list li:hover, .fraise_template-card:hover {
    background: #f3f4f6;
    border-color: #D9DADC;
    color: #253241;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
    cursor: pointer;
}

.create_from_scratch_list li:active {
    transform: translateY(0);
}

/* Modal Body Layout */
.create_new_role_modal_body {
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .create_from_scratch_list {
        grid-template-columns: 1fr;
    }
}
</style>