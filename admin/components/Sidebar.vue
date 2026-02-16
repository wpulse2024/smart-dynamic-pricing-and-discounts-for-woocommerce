<template>
    <aside :class="['sidebar', { collapsed: isCollapsed }]">
        <!-- Logo -->
        <div class="sidebar__header">
            <span v-if="!isCollapsed" class="sidebar__logo">
                Pricing Rules
            </span>
        </div>

        <!-- Menu Header -->
        <div v-if="!isCollapsed" class="sidebar__menu-header">
            <p class="sidebar__menu-title">Dynamic Pricing & Discounts</p>
        </div>

        <!-- Navigation -->
        <nav class="sidebar__nav">
            <router-link
                to="/rules"
                custom
                v-slot="{ navigate, isActive }"
            >
                <button
                    type="button"
                    class="sidebar__nav-item"
                    :class="{ 'sidebar__nav-item--active': isActive || isRulesActive }"
                    @click="navigate"
                >
                    <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    <span v-if="!isCollapsed">Dynamic Rules</span>
                </button>
            </router-link>

            <!-- <router-link to="/settings" custom v-slot="{ navigate, isActive }">
                <button
                    type="button"
                    class="sidebar__nav-item"
                    :class="{ 'sidebar__nav-item--active': isActive }"
                    @click="navigate"
                >
                    <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z">
                        </path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span v-if="!isCollapsed">Settings</span>
                </button>
            </router-link> -->

            <router-link to="/exclusion-list" custom v-slot="{ navigate, isActive }">
                <button
                    type="button"
                    class="sidebar__nav-item"
                    :class="{ 'sidebar__nav-item--active': isActive }"
                    @click="navigate"
                >
                    <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z">
                        </path>
                    </svg>
                    <span v-if="!isCollapsed">Exclusion List</span>
                </button>
            </router-link>

            <!-- <router-link to="/tools" custom v-slot="{ navigate, isActive }">
                <button
                    type="button"
                    class="sidebar__nav-item"
                    :class="{ 'sidebar__nav-item--active': isActive }"
                    @click="navigate"
                >
                    <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z">
                        </path>
                    </svg>
                    <span v-if="!isCollapsed">Your Store Tools</span>
                </button>
            </router-link> -->

            <router-link to="/help" custom v-slot="{ navigate, isActive }">
                <button
                    type="button"
                    class="sidebar__nav-item"
                    :class="{ 'sidebar__nav-item--active': isActive }"
                    @click="navigate"
                >
                    <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span v-if="!isCollapsed">Help</span>
                </button>
            </router-link>
        </nav>

        <!-- Collapse Button -->
        <!-- <button class="sidebar__collapse-btn" @click="$emit('toggle')">
            <svg class="sidebar__icon" :class="{ 'sidebar__icon--rotated': isCollapsed }" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button> -->
    </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';

defineProps<{
    isCollapsed: boolean;
}>();

defineEmits<{
    toggle: [];
}>();

const route = useRoute();
const isRulesActive = computed(() => route.path.startsWith('/rules'));
</script>

<style scoped lang="scss">
    @import '../styles/Sidebar.scss';
</style>