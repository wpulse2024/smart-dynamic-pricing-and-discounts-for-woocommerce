<template>
  <el-config-provider :locale="locale">
    <div class="wpulse-pricing-rules">
      <el-container>
        <el-main class="main-container">
          <div class="sidebar-container">
            <Sidebar />
          </div>
          <div class="content-container">
            <router-view />
          </div>
        </el-main>
      </el-container>
    </div>
  </el-config-provider>
</template>

<script>
import Sidebar from './components/Sidebar.vue';

export default {
  components: {
    Sidebar,
  },
};
</script>
