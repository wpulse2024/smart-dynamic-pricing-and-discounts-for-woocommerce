import { createRouter, createWebHashHistory } from 'vue-router';
import RulesView from '../views/RulesView.vue';

const routes = [
  { path: '/', redirect: '/rules' },
  { path: '/rules', name: 'Rules', component: RulesView },
];

export default createRouter({
  history: createWebHashHistory(),
  routes,
});
