<template>
  <div id="app">
    <TopNav />
    <router-view />
  </div>
</template>

<script>
import TopNav from './components/Layout/TopNav.vue';

export default {
  name: 'App',
  components: {
    TopNav,
  },
}
</script>