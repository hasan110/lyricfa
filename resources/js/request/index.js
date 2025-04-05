import VueCookies from 'vue-cookies';
import axios from 'axios';
import config from '../config'

const Axios = axios.create({
    baseURL: config.API_BASE_URL,
    // baseURL: 'https://panel.lyricfa.app/',
    headers: {'ApiToken': VueCookies.get('admin_token')}
});

// Axios.interceptors.request.use(function (config) {
//     console.log(config);
// }, function (error) {
//     console.log(error);
// });

export default Axios;
