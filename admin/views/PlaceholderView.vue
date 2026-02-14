<template>
  <div class="placeholder-page">
    <h1 class="placeholder-page__title">{{ title }}</h1>
    <p class="placeholder-page__text">This page is coming soon.</p>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'Page' },
});
</script>

<style scoped>
.placeholder-page {
  padding: 24px;
}
.placeholder-page__title {
  font-size: 24px;
  margin: 0 0 8px;
}
.placeholder-page__text {
  color: #64748b;
  margin: 0;
}
</style>
