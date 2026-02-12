<template>
    <div class="fraise_pricing-rules-wrapper">
        <div class="fraise_pricing-rules-header">
            <div class="fraise_pricing-rules-header-left">
                <h2 class="fraise_title">Pricing Rules</h2>
                <p class="fraise_subtitle">
                    Manage your dynamic pricing rules with ease.
                </p>
            </div>
            <div class="fraise_pricing-rules-header-right">
                <QuickActions />
            </div>
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
                <el-table-column label="ID" width="90" >
                    <template #default="{ row }">
                        #{{ row.id }}
                    </template>
                </el-table-column>
                <el-table-column label="Rule Name" width="280">
                    <template #default="{ row }">
                        <router-link :to="{ name: 'edit-rule', params: { id: row.id }}" class="rule_name">
                            {{ row.name }}
                        </router-link>
                    </template>
                </el-table-column>
                <el-table-column label="Status">
                    <template #default="{ row }">
                        <el-tag :type="row.status == 'active' ? 'success' : 'danger'" effect="light" disable-transitions>
                            <span class="status-dot" :class="row.status == 'active' ? 'on' : 'off'"></span>
                            {{ row.status == 'active' ? "Active" : "Inactive" }}
                        </el-tag>
                    </template>
                </el-table-column>

                <el-table-column label="Priority">
                    <template #default="{ row }">
                        {{ getPriorityLabel(row.priority) }}
                    </template>
                </el-table-column>

                <el-table-column label="Offer Type">
                    <template #default="{ row }">
                        {{ formatOfferType(row.offers?.[0]?.type) }}
                    </template>
                </el-table-column>

                <el-table-column label="Actions" align="center" width="100">
                    <template #default="{ row }">
                        <div class="actions-btn" style="display: flex; gap: 10px; align-items: center;">
                            <router-link :to="{ name: 'edit-rule', params: { id: row.id }}" style="text-decoration: none;">
                                <Icon style="margin-top: 4px;" icon="editor" />
                            </router-link>
                            <Icon @click="confirmDeleteRule(row.id)" style="cursor: pointer;" icon="delete" />
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <div class="role_table_footer">
                <!-- <el-pagination background layout="prev, pager, next" :total="1000" /> -->
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import Icon from '../../icons/Icon.vue';
import QuickActions from "../dashboard/partials/QuickActions.vue";

export default defineComponent({
    name: "PricingRulesTable",
    components: {
        Icon,
        QuickActions,
    },
    data() {
        return {
            searchQuery: "",
            loading: false,
            restUrl: WpulsePricingRulesDiscount.restUrl,
            nonce: WpulsePricingRulesDiscount.restNonce,
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
        confirmDeleteRule(id) {
            this.$confirm('This will delete the rule. Continue?', 'Warning', {
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel',
                type: 'warning'
            }).then(() => {
                this.deleteRule(id);
            });
        },
        deleteRule(id) {
            fetch(this.restUrl + 'api/rules/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                }
            })
                .then(res => res.json())
                .then(data => {
                    this.$message.success('Rule deleted successfully');
                    this.getRules();
                })
                .catch(err => console.error('Error:', err));
        },
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
            return map[scopeType] || "All Users";
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
                free_shipping: "Free Shipping",
                quantity_discount: "Quantity Discount",
                category_discount: "Category Discount",
                cart_discount: "Cart Discount",
                role_discount: "Role Discount",
            };
            return map[type] || "N/A";
        },
    },
    mounted() {
        this.getRules();
    },
});
</script>

<style scoped lang="scss">
.fraise_pricing-rules-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.rule_name {
    color: var(--fraise-primary-color);
    text-decoration: none;
    &:hover {
        text-decoration: underline;
        border: none;
        box-shadow: none;
    }
}
</style>