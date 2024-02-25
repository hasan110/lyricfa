import VueCookies from 'vue-cookies';
import axios from 'axios';

const Axios = axios.create({
    baseURL: 'http://localhost:8000/',
    // baseURL: 'https://panel.lyricfa.app/',
    headers: {'ApiToken': VueCookies.get('admin_token')}
});

// Axios.interceptors.request.use(function (config) {
//     console.log(config);
// }, function (error) {
//     console.log(error);
// });

export default Axios;
