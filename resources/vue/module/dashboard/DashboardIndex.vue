<template>
    <div class="dashboard-wrapper">
        <DashboardSummaryCards />
        <QuickActions />
        <RecentActivity />
    </div>
</template>

<script>
import DashboardSummaryCards from './partials/DashboardSummaryCards.vue';
import QuickActions from './partials/QuickActions.vue';
import RecentActivity from './partials/RecentActivity.vue';
export default {
  name: 'DashboardIndex',
  components: {
    DashboardSummaryCards,
    QuickActions,
    RecentActivity,
  },
}
</script>