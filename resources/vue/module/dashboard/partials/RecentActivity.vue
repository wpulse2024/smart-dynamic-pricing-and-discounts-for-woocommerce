<template>
    <div class="fraise_activity-card">
        <h3 class="fraise_activity-title">Recent Activity</h3>

        <ul class="fraise_activity-list">
            <li v-for="(item, index) in activities" :key="index" class="fraise_activity-item">
                <div class="fraise_activity-left">
                    <span class="fraise_activity-dot" :style="{ backgroundColor: item.color }" />
                    <div class="fraise_activity-info">
                        <div class="fraise_activity-label">{{ item.label }}</div>
                        <div class="fraise_activity-sub">{{ item.sub }}</div>
                    </div>
                </div>
                <div class="fraise_activity-time">
                    <el-icon>
                        <Clock />
                    </el-icon>
                    <span>{{ item.time }}</span>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import { Clock } from "@element-plus/icons-vue";

export default defineComponent({
    name: "RecentActivity",
    components: {
        Clock,
    },
    data() {
        return {
            activities: [
                {
                    label: "Summer Sale 50% Off",
                    sub: "Applied 45 times",
                    time: "2 hours ago",
                    color: "#10b981", // Green
                },
                {
                    label: "BOGO - Electronics",
                    sub: "Created",
                    time: "5 hours ago",
                    color: "#8b5cf6", // Purple
                },
                {
                    label: "VIP Member Discount",
                    sub: "Applied 12 times",
                    time: "1 day ago",
                    color: "#10b981", // Green
                },
                {
                    label: "Cart Total > $100",
                    sub: "Modified",
                    time: "2 days ago",
                    color: "#f59e0b", // Orange
                },
            ],
        };
    },
});
</script>