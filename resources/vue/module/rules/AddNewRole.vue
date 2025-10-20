<template>
    <div class="fraise_add-new-role">
        <div class="fraise_page-header">
            <Icon icon="back" @click="$router.go(-1)" />
            <h2>{{ templateData ? templateData.name : 'Create New Rule' }}</h2>
        </div>

        <el-form :model="ruleForm" label-position="top" class="fraise_rule-form">
            <!-- Basic Information -->
            <el-card class="fraise_form-section">
                <template #header>
                    <div class="fraise_section-header">
                        <el-icon>
                            <Document />
                        </el-icon>
                        <span>Basic Information</span>
                    </div>
                </template>

                <el-form-item label="Rule Name" required>
                    <el-input v-model="ruleForm.name" placeholder="Enter rule name" />
                </el-form-item>

                <!-- <el-form-item label="Description">
                    <el-input v-model="ruleForm.meta.description" type="textarea" :rows="3"
                        placeholder="Brief description of this rule" />
                </el-form-item> -->

                <el-form-item label="Priority" style="max-width: 300px;">
                    <el-input-number v-model="ruleForm.priority" :min="1" :max="100" />
                </el-form-item>

                <el-row :gutter="20">
                    <el-col :span="24">
                        <el-form-item label="Status">
                            <el-switch active-value="active" inactive-value="inactive" v-model="ruleForm.status" active-text="Active"  />
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-card>

            <!-- Product Scope -->
            <el-card class="fraise_form-section">
                <template #header>
                    <div class="fraise_section-header">
                        <el-icon>
                            <ShoppingBag />
                        </el-icon>
                        <span>Product Scope</span>
                    </div>
                </template>

                <el-form-item label="Apply to">
                    <el-radio-group v-model="ruleForm.product_scope.scopeType">
                        <el-radio label="all_products">All Products</el-radio>
                        <el-radio label="specific_products">Specific Products</el-radio>
                        <el-radio label="product_categories">Product Categories</el-radio>
                        <el-radio label="product_tags">Product Tags</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-if="ruleForm.product_scope.scopeType === 'specific_products'" label="Select Products">
                    <el-select v-model="ruleForm.product_scope.inclusion.products" multiple
                        placeholder="Choose products" style="width: 100%">
                        <el-option v-for="product in products" :key="product.ID" :label="product.post_title"
                            :value="product.ID" />
                    </el-select>
                </el-form-item>

                <el-form-item v-if="ruleForm.product_scope.scopeType === 'product_categories'"
                    label="Select Categories">
                    <el-select v-model="ruleForm.product_scope.inclusion.categories" multiple
                        placeholder="Choose categories" style="width: 100%">
                        <el-option v-for="category in categories" :key="category.term_id" :label="category.name"
                            :value="category.term_id" />
                    </el-select>
                </el-form-item>

                <el-form-item v-if="ruleForm.product_scope.scopeType === 'product_tags'" label="Select Tags">
                    <el-select v-model="ruleForm.product_scope.inclusion.tags" multiple placeholder="Choose tags"
                        style="width: 100%">
                        <el-option v-for="tag in tags" :key="tag.id" :label="tag.name" :value="tag.id" />
                    </el-select>
                </el-form-item>

                <!-- Exclusions -->
                <el-divider />
                <el-form-item label="Exclude" v-if="ruleForm.product_scope.scopeType == 'all_products'">
                    <el-radio-group v-model="ruleForm.product_scope.exclusion.type">
                        <el-radio label="none">None</el-radio>
                        <el-radio label="specific_products">Specific Products</el-radio>
                        <el-radio label="product_categories">Categories</el-radio>
                        <el-radio label="product_tags">Tags</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item v-if="ruleForm.product_scope.exclusion.type === 'specific_products'"
                    label="Select Products">
                    <el-select v-model="ruleForm.product_scope.exclusion.products" multiple
                        placeholder="Choose products" style="width: 100%">
                        <el-option v-for="product in products" :key="product.ID" :label="product.post_title"
                            :value="product.ID" />
                    </el-select>
                </el-form-item>
                <el-form-item v-if="ruleForm.product_scope.exclusion.type === 'product_categories'"
                    label="Select Categories">
                    <el-select v-model="ruleForm.product_scope.exclusion.categories" multiple
                        placeholder="Choose categories" style="width: 100%">
                        <el-option v-for="category in categories" :key="category.term_id" :label="category.name"
                            :value="category.term_id" />
                    </el-select>
                </el-form-item>
                <el-form-item v-if="ruleForm.product_scope.exclusion.type === 'product_tags'" label="Select Tags">
                    <el-select v-model="ruleForm.product_scope.exclusion.tags" multiple placeholder="Choose tags"
                        style="width: 100%">
                        <el-option v-for="tag in tags" :key="tag.id" :label="tag.name" :value="tag.id" />
                    </el-select>
                </el-form-item>
            </el-card>

            <!-- User Scope -->
            <el-card class="fraise_form-section">
                <template #header>
                    <div class="fraise_section-header">
                        <el-icon>
                            <User />
                        </el-icon>
                        <span>User Scope</span>
                    </div>
                </template>

                <el-form-item label="Apply to">
                    <el-radio-group v-model="ruleForm.user_scope.scopeType">
                        <el-radio label="all_users">All Users</el-radio>
                        <el-radio label="specific_users">Specific Users</el-radio>
                        <el-radio label="user_roles">User Roles</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-if="ruleForm.user_scope.scopeType === 'specific_users'" label="Select Users">
                    <el-select v-model="ruleForm.user_scope.users" multiple placeholder="Choose users"
                        style="width: 100%">
                        <el-option v-for="user in users" :key="user.id" :label="user.user_login" :value="user.id" />
                    </el-select>
                </el-form-item>

                <el-form-item v-if="ruleForm.user_scope.scopeType === 'user_roles'" label="Select Roles">
                    <el-select v-model="ruleForm.user_scope.roles" multiple placeholder="Choose roles"
                        style="width: 100%">
                        <el-option v-for="role in roles" :key="role.id" :label="role.name" :value="role.id" />
                    </el-select>
                </el-form-item>

                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item>
                            <el-checkbox v-model="ruleForm.user_scope.apply_on_sale_items">
                                Apply on Sale Items
                            </el-checkbox>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item>
                            <el-checkbox v-model="ruleForm.user_scope.apply_on_discounted_products">
                                Apply on Discounted Products
                            </el-checkbox>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-card>

            <!-- Schedule -->
            <el-card class="fraise_form-section">
                <template #header>
                    <div class="fraise_section-header">
                        <el-icon>
                            <Calendar />
                        </el-icon>
                        <span>Schedule</span>
                    </div>
                </template>

                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="Start Date">
                            <el-date-picker v-model="ruleForm.schedule.start" type="datetime"
                                placeholder="Select start date" style="width: 100%" />
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="End Date">
                            <el-date-picker v-model="ruleForm.schedule.end" type="datetime"
                                placeholder="Select end date" style="width: 100%" />
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-form-item label="Days of Week (Optional)">
                    <el-checkbox-group v-model="ruleForm.schedule.daysOfWeek">
                        <el-checkbox label="monday">Monday</el-checkbox>
                        <el-checkbox label="tuesday">Tuesday</el-checkbox>
                        <el-checkbox label="wednesday">Wednesday</el-checkbox>
                        <el-checkbox label="thursday">Thursday</el-checkbox>
                        <el-checkbox label="friday">Friday</el-checkbox>
                        <el-checkbox label="saturday">Saturday</el-checkbox>
                        <el-checkbox label="sunday">Sunday</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
            </el-card>

            <!-- Dynamic Offer Configuration -->
            <el-card class="fraise_form-section">
                <template #header>
                    <div class="fraise_section-header">
                        <el-icon>
                            <Discount />
                        </el-icon>
                        <span>Offer Configuration</span>
                    </div>
                </template>

                <!-- Quantity Discount -->
                <div v-if="ruleForm.rule_type === 'quantity_discount'">
                    <h4>Quantity Tiers</h4>
                    <div v-for="(tier, index) in currentOffer.tiers" :key="index" class="fraise_tier-row">
                        <el-row :gutter="10">
                            <el-col :span="5">
                                <el-form-item label="Min Qty">
                                    <el-input-number v-model="tier.min" :min="1" />
                                </el-form-item>
                            </el-col>
                            <el-col :span="5">
                                <el-form-item label="Max Qty">
                                    <el-input-number v-model="tier.max" :min="tier.min" />
                                </el-form-item>
                            </el-col>
                            <el-col :span="6">
                                <el-form-item label="Discount Type">
                                    <el-select v-model="tier.discountType">
                                        <el-option label="Percentage" value="percentage" />
                                        <el-option label="Fixed Amount" value="fixed_amount" />
                                    </el-select>
                                </el-form-item>
                            </el-col>
                            <el-col :span="6">
                                <el-form-item label="Value">
                                    <el-input-number v-model="tier.value" :min="0" />
                                </el-form-item>
                            </el-col>
                            <el-col :span="2">
                                <el-button type="danger" :icon="Delete" circle @click="removeTier(index)"
                                    style="margin-top: 28px" />
                            </el-col>
                        </el-row>
                    </div>
                    <el-button @click="addTier" :icon="Plus">Add Tier</el-button>
                </div>

                <!-- Special Offer (Buy X Get Y) -->
                <div v-if="ruleForm.rule_type === 'special_offer'">
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Purchase Quantity">
                                <el-input-number v-model="currentOffer.condition.purchaseQuantity" :min="1" />
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Discounted Items">
                                <el-input-number v-model="currentOffer.reward.discountedItems" :min="1" />
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Discount Type">
                                <el-select v-model="currentOffer.reward.discountType" style="width: 100%">
                                    <el-option label="Percentage" value="percentage" />
                                    <el-option label="Fixed Amount" value="fixed_amount" />
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Discount Value">
                                <el-input-number v-model="currentOffer.reward.discountValue" :min="0" :max="100" />
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <!-- <el-form-item label="Discount Product Type">
                        <el-radio-group v-model="currentOffer.reward.discount_product_type">
                            <el-radio label="same_product">Same Product</el-radio>
                            <el-radio label="specific_product">Specific Product</el-radio>
                            <el-radio label="specific_product_category">Category</el-radio>
                            <el-radio label="all_products">All Products</el-radio>
                        </el-radio-group>
                    </el-form-item> -->

                    <el-form-item v-if="currentOffer.reward.discount_product_type === 'specific_product'"
                        label="Select Product">
                        <el-select v-model="currentOffer.reward.specific_products" placeholder="Choose product"
                            style="width: 100%" multiple>
                            <el-option v-for="product in products" :key="product.ID" :label="product.post_title"
                                :value="product.ID" />
                        </el-select>
                    </el-form-item>

                    <el-form-item v-if="currentOffer.reward.discount_product_type === 'specific_product_category'"
                        label="Select Category">
                        <el-select v-model="currentOffer.reward.specific_categories" placeholder="Choose category"
                            style="width: 100%">
                            <el-option v-for="category in categories" :key="category.term_id" :label="category.name"
                                :value="category.term_id" />
                        </el-select>
                    </el-form-item>

                    <el-form-item>
                        <el-checkbox v-model="currentOffer.condition.repeat">Repeat Offer</el-checkbox>
                    </el-form-item>
                </div>

                <!-- Gift with Purchase -->
                <div v-if="ruleForm.rule_type === 'gift_with_purchase'">
                    <el-form-item label="Apply Condition Based On">
                        <el-radio-group v-model="currentOffer.condition.apply_condition">
                            <el-radio label="cart_total">Cart Total</el-radio>
                            <el-radio label="cart_quantity">Cart Quantity</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item v-if="currentOffer.condition.apply_condition === 'cart_total'"
                                label="Min Cart Total">
                                <el-input-number v-model="currentOffer.condition.min_cart_total" :min="0" />
                            </el-form-item>
                            <el-form-item v-else label="Min Cart Quantity">
                                <el-input-number v-model="currentOffer.condition.min_cart_quantity" :min="1" />
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Gifted Items">
                                <el-input-number v-model="currentOffer.reward.giftedItems" :min="1" />
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-form-item label="Select Gift Products">
                        <el-select v-model="currentOffer.reward.gift_product_ids" multiple
                            placeholder="Choose gift products" style="width: 100%">
                            <el-option v-for="product in products" :key="product.ID" :label="product.post_title"
                                :value="product.ID" />
                        </el-select>
                    </el-form-item>
                </div>

                <!-- Cart Discount -->
                <div v-if="ruleForm.rule_type === 'cart_discount'">
                    <el-row :gutter="20">
                        <el-col :span="8">
                            <el-form-item label="Min Cart Amount">
                                <el-input-number v-model="currentOffer.condition.minCartAmount" :min="0" />
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Discount Type">
                                <el-select v-model="currentOffer.reward.discountType" style="width: 100%">
                                    <el-option label="Percentage" value="percentage" />
                                    <el-option label="Fixed Amount" value="fixed_amount" />
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Discount Value">
                                <el-input-number v-model="currentOffer.reward.value" :min="0" />
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>

                <!-- Global Discount -->
                <div v-if="ruleForm.rule_type === 'global_discount' || ruleForm.rule_type === 'role_discount'">
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Discount Type">
                                <el-select v-model="currentOffer.reward.discountType" style="width: 100%">
                                    <el-option label="Percentage" value="percentage" />
                                    <el-option label="Fixed Amount" value="fixed_amount" />
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Discount Value">
                                <el-input-number v-model="currentOffer.reward.value" :min="0" />
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>

                <!-- Category Discount -->
                <div v-if="ruleForm.rule_type === 'category_discount'">
                    <el-form-item label="Select Categories">
                        <el-select v-model="currentOffer.condition.categories" multiple placeholder="Choose categories"
                            style="width: 100%">
                            <el-option v-for="category in categories" :key="category.term_id" :label="category.name"
                                :value="category.term_id" />
                        </el-select>
                    </el-form-item>

                    <el-row :gutter="20">
                        <el-col :span="6">
                            <el-form-item label="Min Quantity">
                                <el-input-number v-model="currentOffer.condition.minQuantity" :min="1" />
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label="Max Quantity">
                                <el-input-number v-model="currentOffer.condition.maxQuantity" :min="1" />
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label="Discount Type">
                                <el-select v-model="currentOffer.reward.discountType" style="width: 100%">
                                    <el-option label="Percentage" value="percentage" />
                                    <el-option label="Fixed Amount" value="fixed_amount" />
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label="Discount Value">
                                <el-input-number v-model="currentOffer.reward.value" :min="0" />
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>

                <!-- Free Shipping -->
                <div v-if="ruleForm.rule_type === 'free_shipping'">
                    <el-form-item label="Min Cart Amount for Free Shipping">
                        <el-input-number v-model="currentOffer.condition.minCartAmount" :min="0" />
                    </el-form-item>
                </div>
            </el-card>

            <!-- Usage Limits -->
            <el-card class="fraise_form-section">
                <template #header>
                    <div class="fraise_section-header">
                        <el-icon>
                            <DataLine />
                        </el-icon>
                        <span>Usage Limits</span>
                    </div>
                </template>

                <el-form-item label="Usage Limit (0 = Unlimited)">
                    <el-input-number v-model="ruleForm.meta.usageLimit" :min="0" />
                </el-form-item>

                <el-form-item label="Internal Notes">
                    <el-input v-model="ruleForm.meta.notes" type="textarea" :rows="3"
                        placeholder="Internal notes for developers or admins" />
                </el-form-item>
            </el-card>

            <!-- Action Buttons -->
            <div class="fraise_form-actions">
                <el-button size="medium" @click="$router.go(-1)">Cancel</el-button>
                <el-button size="medium" type="primary" @click="saveRule" :icon="Check">Save Rule</el-button>
            </div>
        </el-form>
    </div>
</template>

<script>
import Icon from '../../icons/Icon.vue';
import { getTemplateByKey } from '../../utility/Helper';
import {
    ArrowLeft,
    Document,
    ShoppingBag,
    User,
    Calendar,
    Discount,
    Plus,
    Delete,
    Check,
    DataLine,
} from '@element-plus/icons-vue';

export default {
    name: 'AddNewRole',
    components: {
        ArrowLeft,
        Document,
        ShoppingBag,
        User,
        Calendar,
        Discount,
        Plus,
        Delete,
        Check,
        DataLine,
        Icon
    },
    data() {
        return {
            products: SmartDynamicPricingDiscount.products,
            categories: SmartDynamicPricingDiscount.categories,
            tags: SmartDynamicPricingDiscount.tags,
            template: this.$route.params.template || this.$route.params.type,
            users: SmartDynamicPricingDiscount.users,
            roles: SmartDynamicPricingDiscount.roles,
            templateData: null,
            nonce: SmartDynamicPricingDiscount?.restNonce,
            restUrl: SmartDynamicPricingDiscount?.restUrl,
            ruleForm: {
                id: '',
                name: '',
                status: true,
                priority: 1,
                rule_type: '',
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
                schedule: {
                    start: '',
                    end: '',
                    daysOfWeek: [],
                    specificDates: []
                },
                offers: [],
                meta: {
                    createdBy: '',
                    createdAt: '',
                    updatedAt: '',
                    usageLimit: 0,
                    usageCount: 0,
                    description: '',
                    notes: ''
                }
            }
        };
    },
    computed: {
        currentOffer() {
            return this.ruleForm.offers[0] || {};
        }
    },
    mounted() {
        this.initializeRule();
    },
    methods: {
        initializeRule() {
            // Check if it's a template or custom type
            console.log('Route name:', this.$route.name);
            if (this.$route.name === 'add-new-role-template') {
                this.templateData = getTemplateByKey(this.template);
                if (this.templateData) {
                    this.ruleForm = JSON.parse(JSON.stringify(this.templateData));
                    delete this.ruleForm.template_key;
                }
            } else if (this.$route.name === 'edit-rule') {
                this.fetchRule();
            }
            else {
                // Custom rule from scratch
                this.ruleForm.rule_type = this.template;
                this.initializeOfferByType(this.template);
            }
        },
        initializeOfferByType(type) {
            const offerTemplates = {
                quantity_discount: {
                    type: 'quantity_discount',
                    tiers: [
                        { min: 1, max: 10, discountType: 'percentage', value: 10 }
                    ]
                },
                special_offer: {
                    type: 'special_offer',
                    condition: { purchaseQuantity: 2, repeat: false },
                    reward: {
                        discountedItems: 1,
                        discountType: 'percentage',
                        discountValue: 50,
                        discount_product_type: 'same_product'
                    }
                },
                gift_with_purchase: {
                    type: 'gift_with_purchase',
                    condition: {
                        purchaseQuantity: 3,
                        apply_condition: 'cart_total',
                        min_cart_total: 100,
                        min_cart_quantity: 0
                    },
                    reward: {
                        giftedItems: 1,
                        gift_product_ids: []
                    }
                },
                global_discount: {
                    type: 'global_discount',
                    reward: {
                        discountType: 'percentage',
                        value: 10
                    }
                },
                cart_discount: {
                    type: 'cart_discount',
                    condition: { minCartAmount: 100 },
                    reward: { discountType: 'percentage', value: 10 }
                },
                category_discount: {
                    type: 'category_discount',
                    condition: {
                        categories: [],
                        minQuantity: 1,
                        maxQuantity: 10
                    },
                    reward: {
                        discountType: 'percentage',
                        value: 15
                    }
                }
            };

            this.ruleForm.offers = [offerTemplates[type]];
        },
        fetchRule() {
            fetch(this.restUrl + 'api/rules/' + this.$route.params.id, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                }
            })
                .then(res => res.json())
                .then(data => {
                    this.ruleForm = data?.data?.rule;
                })
                .catch(err => console.error('Error:', err));
        },
        addTier() {
            if (this.currentOffer.tiers) {
                const lastTier = this.currentOffer.tiers[this.currentOffer.tiers.length - 1];
                this.currentOffer.tiers.push({
                    min: lastTier.max + 1,
                    max: lastTier.max + 10,
                    discountType: 'percentage',
                    value: 10
                });
            }
        },
        removeTier(index) {
            if (this.currentOffer.tiers && this.currentOffer.tiers.length > 1) {
                this.currentOffer.tiers.splice(index, 1);
            }
        },
        async saveRule() {
            // Validate form
            if (!this.ruleForm.name) {
                this.$message.error('Please enter a rule name');
                return;
            }

            // Set metadata
            const now = new Date().toISOString();
            this.ruleForm.meta.createdAt = now;
            this.ruleForm.meta.updatedAt = now;

            // Convert dates to ISO strings
            if (this.ruleForm.schedule.start) {
                this.ruleForm.schedule.start = new Date(this.ruleForm.schedule.start).toISOString();
            }
            if (this.ruleForm.schedule.end) {
                this.ruleForm.schedule.end = new Date(this.ruleForm.schedule.end).toISOString();
            }

            console.log('Saving rule:', this.ruleForm);

            if (this.$route.name === 'edit-rule') {
                this.ruleForm.id = this.$route.params.id;
            }

            fetch(this.restUrl + 'api/rules', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                },
                body: JSON.stringify(this.ruleForm)
            })
                .then(res => res.json())
                .then(data => {
                    this.$message.success('Rule saved successfully', 'success');
                    this.$router.push({ name: 'roles' });
                })
                .catch(err => console.error('Error:', err));

        }
    }
};
</script>