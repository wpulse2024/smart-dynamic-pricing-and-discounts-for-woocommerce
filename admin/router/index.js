import { createRouter, createWebHashHistory } from 'vue-router';
import RulesView from '../views/RulesView.vue';
import RuleEditorView from '../views/RuleEditorView.vue';

const routes = [
  { path: '/', redirect: '/rules' },
  { path: '/rules', name: 'Rules', component: RulesView },
  { path: '/rules/edit/:id', name: 'RuleEditor', component: RuleEditorView },
];

export default createRouter({
  history: createWebHashHistory(),
  routes,
});
