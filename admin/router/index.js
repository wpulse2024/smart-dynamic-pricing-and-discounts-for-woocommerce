import { createRouter, createWebHashHistory } from 'vue-router';
import RulesView from '../views/RulesView.vue';
import RuleEditorView from '../views/RuleEditorView.vue';
import ExclusionListView from '../views/ExclusionListView.vue';
import PlaceholderView from '../views/PlaceholderView.vue';

const routes = [
  { path: '/', redirect: '/rules' },
  { path: '/rules', name: 'Rules', component: RulesView },
  { path: '/rules/edit/:id', name: 'RuleEditor', component: RuleEditorView },
  { path: '/exclusion-list', name: 'ExclusionList', component: ExclusionListView },
  { path: '/settings', name: 'Settings', component: PlaceholderView, props: { title: 'Settings' } },
  { path: '/tools', name: 'Tools', component: PlaceholderView, props: { title: 'Your Store Tools' } },
  { path: '/help', name: 'Help', component: PlaceholderView, props: { title: 'Help' } },
];

export default createRouter({
  history: createWebHashHistory(),
  routes,
});
