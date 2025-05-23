import Vue from 'vue';
import router from './routes/router';
import store from './store';
import http from './request';
import VueCookies from 'vue-cookies'

import Helper from './plugins/Helper'
import Auth from './plugins/Auth'
import vuetify from './plugins/vuetify'

import App from './App.vue';
import MySidebar from './components/template/MySidebar.vue';
import JoinTextToVendor from "./components/JoinTextToVendor.vue";
import SelectCategory from "./components/SelectCategory.vue";
import SelectLink from "./components/SelectLink.vue";
import VueSimpleAlert from "vue-simple-alert";
import VueApexCharts from 'vue-apexcharts'

require('./bootstrap');

window.Vue = require('vue').default;
Vue.prototype.$http = http

Vue.use(Helper);
Vue.use(Auth);
Vue.use(VueCookies)
Vue.use(VueSimpleAlert);
Vue.use(VueApexCharts)

Vue.component('app', App);
Vue.component('my-sidebar', MySidebar);
Vue.component('join-text-to-vendor', JoinTextToVendor);
Vue.component('select-category', SelectCategory);
Vue.component('select-link', SelectLink);
Vue.component('apexchart', VueApexCharts)

const app = new Vue({
    el: '#app',
    router,
    vuetify,
    store
});
