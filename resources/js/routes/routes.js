import App from '../App.vue';

import login from '../pages/login.vue';

import dashboard from '../pages/dashboard.vue';

import users from '../pages/user/users.vue';
import user from '../pages/user/user.vue';

import musics from '../pages/music/musics.vue';
import create_music from '../pages/music/create_music.vue';
import edit_music_text from '../pages/music/edit_music_text.vue';

import singers from '../pages/singer/singers.vue';

// import albums from '../pages/albums.vue';
// import sliders from '../pages/sliders.vue';
// import pays from '../pages/pays.vue';
// import support from '../pages/support.vue';
// import settings from '../pages/settings.vue';
// import music_orders from '../pages/music_orders.vue';

const allUrl = [
    {
        path: '/',
        component: App,
        redirect: '/dashboard'
    },
    { path: '/login', name: 'login', component: login },

    { path: '/dashboard', name: 'dashboard', component: dashboard },

    { path: '/users', name: 'users', component: users },
    { path: '/user/:id', name: 'user', component: user },

    { path: '/musics', name: 'musics', component: musics },
    { path: '/musics/create', name: 'create_music', component: create_music },
    { path: '/musics/edit_text/:id', name: 'edit_music_text', component: edit_music_text },

    { path: '/singers', name: 'singers', component: singers },

    { path: '/albums', name: 'albums', component: users },
    { path: '/sliders', name: 'sliders', component: users },
    { path: '/pays', name: 'pays', component: users },
    { path: '/support', name: 'support', component: users },
    { path: '/settings', name: 'settings', component: users },
    { path: '/music_orders', name: 'music_orders', component: users },
];
export default allUrl;