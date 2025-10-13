<template>
    <div class="fraise_cards-wrapper">
        <div class="fraise_card stat-card">
            <div class="fraise_card-icon purple">
                <el-icon><magic-stick /></el-icon>
            </div>
            <div class="fraise_card-content">
                <div class="label">Total Active Rules</div>
                <div class="value">24</div>
            </div>
            <div class="fraise_card-indicator green">
            +4</div>
        </div>

        <div class="fraise_card stat-card">
            <div class="fraise_card-icon green">
                <el-icon><shopping-cart /></el-icon>
            </div>
            <div class="fraise_card-content">
                <div class="label">Total Discounts Applied</div>
                <div class="value">1,247</div>
            </div>
            <div class="fraise_card-indicator green">18%</div>
        </div>

        <div class="fraise_card featured-card">
            <div class="fraise_card-badge">
                <el-icon><magic-stick /></el-icon> Top
            </div>
            <div class="fraise_card-content">
                <div class="label light">Top Performing Rule</div>
                <div class="value-lg">Summer Sale 50%</div>
                <div class="applications">325 applications</div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { MagicStick, ShoppingCart } from '@element-plus/icons-vue';
import Icon from '../../../icons/Icon.vue';

export default defineComponent({
    name: 'DashboardSummaryCards',
    components: {
        MagicStick,
        ShoppingCart,
        Icon,
    },
});
</script>