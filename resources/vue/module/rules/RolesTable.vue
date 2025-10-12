<template>
    <div class="fraise_pricing-rules-wrapper">
        <div class="fraise_pricing-rules-header">
            <h2 class="fraise_title">Pricing Rules</h2>
            <p class="fraise_subtitle">
                Manage your dynamic pricing rules with ease.
            </p>
        </div>

        <!-- Data Table -->
        <div class="rule_table_wrapper">
            <el-input v-model="searchQuery" placeholder="Search rules..." class="fraise_search" clearable
            >
                <template #prefix>
                    <div class="fraise_search-icon">
                        <Icon style="height: 20px;" icon="search" />
                    </div>
                </template>
            </el-input>
            <el-table :data="filteredRules" border style="width: 100%" class="fraise_rules-table">
                <el-table-column label="Rule Name" prop="name" width="220" />
                <el-table-column label="Status">
                    <template #default="{ row }">
                        <el-tag :type="row.status ? 'success' : 'danger'" effect="light" disable-transitions>
                            <span class="status-dot" :class="row.status ? 'on' : 'off'"></span>
                            {{ row.status ? "Active" : "Inactive" }}
                        </el-tag>
                    </template>
                </el-table-column>

                <el-table-column label="Priority">
                    <template #default="{ row }">
                        {{ getPriorityLabel(row.priority) }}
                    </template>
                </el-table-column>

                <el-table-column label="Product Scope">
                    <template #default="{ row }">
                        {{ getProductScope(row.product_scope.scopeType) }}
                    </template>
                </el-table-column>

                <el-table-column label="User Scope">
                    <template #default="{ row }">
                        {{ getUserScope(row.user_scope.scopeType) }}
                    </template>
                </el-table-column>

                <el-table-column label="Schedule" width="140">
                    <template #default="{ row }">
                        {{ formatSchedule(row.schedule) }}
                    </template>
                </el-table-column>

                <el-table-column label="Offer Type">
                    <template #default="{ row }">
                        {{ formatOfferType(row.offers?.[0]?.type) }}
                    </template>
                </el-table-column>

                <el-table-column label="Actions" align="center" width="100">
                    <template>
                        <el-button type="text" icon="el-icon-edit" />
                        <el-button type="text" icon="el-icon-delete" />
                    </template>
                </el-table-column>
            </el-table>
            <div class="role_table_footer">
                <el-pagination background layout="prev, pager, next" :total="1000" />
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import Icon from '../../icons/Icon.vue';

export default defineComponent({
    name: "PricingRulesTable",
    components: {
        Icon
    },
    data() {
        return {
            searchQuery: "",
            loading: false,
            restUrl: SmartDynamicPricingDiscount.restUrl,
            nonce: SmartDynamicPricingDiscount.restNonce,
            rules: [
            ],
        };
    },
    computed: {
        filteredRules() {
            return this.rules.filter((rule) =>
                rule.name.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },
    },
    methods: {
        getRules() {
            this.loading = true;
            fetch(this.restUrl + 'api/rules', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                }
            })
                .then(res => res.json())
                .then(data => {
                    console.log('Rules:', data?.data?.rules);
                    this.rules = data?.data?.rules;
                    this.loading = false;
                })
                .catch(err => {
                    console.error('Error:', err);
                    this.loading = false;
                });
        },
        getPriorityLabel(priority) {
            if (priority <= 1) return "High";
            if (priority === 2) return "Medium";
            return "Low";
        },
        getProductScope(scopeType) {
            const map = {
                all_products: "All Products",
                selected_products: "Selected Products",
                specific_products: "Specific Products",
            };
            return map[scopeType] || "Mixed";
        },
        getUserScope(scopeType) {
            const map = {
                all_users: "All Users",
                specific_users: "Specific Users",
                user_roles: "Specific Roles",
            };
            return map[scopeType] || "Custom";
        },
        formatSchedule(schedule) {
            if (schedule.start && schedule.end) {
                return this.formatDate(schedule.start) + " - " + this.formatDate(schedule.end);
            }
            return "Ongoing";
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
            });
        },
        formatOfferType(type) {
            const map = {
                special_offer: "Discount",
                bogo: "BOGO",
                markdown: "Markdown",
                gift: "Free Gift",
            };
            return map[type] || "N/A";
        },
    },
    mounted() {
        this.getRules();
    },
});
</script>