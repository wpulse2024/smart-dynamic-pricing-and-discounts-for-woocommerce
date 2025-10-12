<template>
    <header class="fraise_navbar">
        <!-- Logo & Brand -->
        <div class="fraise_navbar-left">
            <img src="" alt="My Plugin Logo" class="fraise_navbar-logo" />
            <span class="fraise_navbar-brand">
                My<span class="brand-bold">Plugin</span>
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="fraise_navbar-menu">
            <ul class="fraise_navbar-links">
                <div v-for="item in navItems" :key="item.label" class="fraise_navbar-link">
                    <router-link :to="item.to">
                        <li :to="item.to">
                            {{ item.label }}
                        </li>
                    </router-link>
                </div>
            </ul>
        </nav>

        <!-- Actions Right -->
        <div class="fraise_navbar-actions">
            <div class="fraise_navbar-shortcut">
                <el-icon>
                    <Search />
                </el-icon><span class="shortcut-text">⌘K</span>
            </div>
            <el-icon class="fraise_navbar-icon">
                <Sunny />
            </el-icon>
            <el-icon class="fraise_navbar-icon">
                <Setting />
            </el-icon>
        </div>
    </header>
</template>

<script>
import { defineComponent } from 'vue';
import { Search, Sunny, Setting } from '@element-plus/icons-vue';

export default defineComponent({
    name: 'FluentTopNav',
    components: {
        Search,
        Sunny,
        Setting,
    },
    data() {
        return {
            navItems: [
                {
                    label: 'Dashboard',
                    to: '/',
                },
                {
                    label: 'Rules',
                    to: '/roles',
                },
            ],
        };
    },
});
</script>