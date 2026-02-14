<template>
  <el-config-provider :locale="locale">
    <div class="wpulse-pricing-rules">
      <el-container>
        <el-header class="app-header">
          <h1 class="title">
            <el-icon><PriceTag /></el-icon>
            Pricing Rules
          </h1>
        </el-header>
        <el-main>
          <router-view />
        </el-main>
      </el-container>
    </div>
  </el-config-provider>
</template>

<script setup>
import { computed } from 'vue';
import { PriceTag } from '@element-plus/icons-vue';
import enLocale from 'element-plus/dist/locale/en.mjs';
import zhLocale from 'element-plus/dist/locale/zh-cn.mjs';

const locale = computed(() => {
  const wp = window.wpulsePricingRules || {};
  return (wp.lang === 'zh' || wp.lang === 'zh_CN') ? zhLocale : enLocale;
});
</script>

<style lang="scss" scoped>
.wpulse-pricing-rules {
  min-height: 100vh;
  background: var(--wpulse-bg-page);
}

.app-header {
  display: flex;
  align-items: center;
  background: var(--wpulse-header-bg);
  border-bottom: 1px solid var(--el-border-color-lighter);
  .title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
}

.el-main {
  padding: 1.5rem;
}
</style>
