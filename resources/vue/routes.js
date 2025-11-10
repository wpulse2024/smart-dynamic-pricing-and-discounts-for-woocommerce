import { createRouter, createWebHashHistory } from 'vue-router';
import TripsView from './module/rules/RolesTable.vue';
import DashboardIndex from './module/dashboard/DashboardIndex.vue';
import Documentation from './module/documentation/Documentation.vue';
import AddNewRole from './module/rules/AddNewRole.vue';
// Create router
const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        { 
            path: '/',
            component: TripsView,
            name: 'roles',
            meta: {
                active_menu: 0
            }
        },
        { 
            path: '/documentation', 
            name: 'documentation',
            component: Documentation,
            meta: {
                active_menu: 1
            }
        },
        // { 
        //     path: '/', 
        //     name: 'dashboard',
        //     component: DashboardIndex,
        //     meta: {
        //         active_menu: 1
        //     }
        // },
        { 
            path: '/create-new-rule/:type', 
            name: 'create-new-rule',
            component: AddNewRole,
            meta: {
                active_menu: 1
            }
        },
        { 
            path: '/edit-rule/:id', 
            name: 'edit-rule',
            component: AddNewRole,
            meta: {
                active_menu: 1
            }
        },
        { 
            path: '/add-new-role-template/:template', 
            name: 'add-new-role-template',
            component: AddNewRole,
            meta: {
                active_menu: 1
            }
        },
    ]
});

export default router;