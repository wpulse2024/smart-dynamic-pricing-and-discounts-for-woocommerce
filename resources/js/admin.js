import { createApp } from 'vue';
import App from '../vue/App.vue';
import ElementPlus from 'element-plus';
import { ElNotification } from 'element-plus';
import 'element-plus/dist/index.css'
import router from '../vue/routes.js';

window.$ = window.jQuery = jQuery;
function $t (string) {
    return window?.WpulsePricingRulesDiscount?.i18n[string] || string;
}

// ad active class to the current menu item

// Create and mount Vue app
const app = createApp(App);
const globals = app.config.globalProperties;

globals.$notify = (message, type = 'info') => ElNotification({
	offset: 20,
	type: type,
    title: type === 'info' ? 'Notification': capitalize(type),
	message: message
});


app.use(ElementPlus);
app.config.globalProperties.$t = $t;
app.use(router);
app.mount('#my-plugin-admin')
