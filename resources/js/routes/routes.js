import App from '../App.vue';

import login from '../pages/login.vue';

import dashboard from '../pages/dashboard.vue';

import users from '../pages/user/users.vue';
import user from '../pages/user/user.vue';

import movies from '../pages/movie/movies.vue';
import create_movie from '../pages/movie/create_movie.vue';
import edit_movie from '../pages/movie/edit_movie.vue';

import musics from '../pages/music/musics.vue';
import create_music from '../pages/music/create_music.vue';
import edit_music from '../pages/music/edit_music.vue';

import edit_texts from '../pages/texts/edit_texts.vue';
import join_text from '../pages/texts/join_text.vue';

import singers from '../pages/singer/singers.vue';

import albums from '../pages/album/albums.vue';
import create_album from '../pages/album/create_album.vue';
import edit_album from '../pages/album/edit_album.vue';

import categories from '../pages/categories/categories.vue';
import create_category from '../pages/categories/create_category.vue';
import edit_category from '../pages/categories/edit_category.vue';
import category_items from '../pages/categories/category_items.vue';

import comments from '../pages/comment/comments.vue';

import pays from '../pages/pay/pays.vue';

import sliders from '../pages/slider/sliders.vue';
import create_slider from '../pages/slider/create_slider.vue';
import edit_slider from '../pages/slider/edit_slider.vue';

import settings from '../pages/settings/settings.vue';

import music_orders from '../pages/music_order/music_orders.vue';

import notifications from '../pages/notifications/notifications.vue';

import words from '../pages/dictionary/words.vue';
import create_word from '../pages/dictionary/create_word.vue';
import edit_word from '../pages/dictionary/edit_word.vue';
import replace_texts from '../pages/dictionary/replace_texts.vue';
import replace_rules from '../pages/dictionary/replace_rules.vue';

import idioms from '../pages/idioms/idioms.vue';
import create_idiom from '../pages/idioms/create_idiom.vue';
import edit_idiom from '../pages/idioms/edit_idiom.vue';

import grammers from '../pages/grammers/grammers.vue';
import create_grammer from '../pages/grammers/create_grammer.vue';
import edit_grammer from '../pages/grammers/edit_grammer.vue';
import grammer_rules from "../pages/grammers/grammer_rules";

import maps from '../pages/maping/maps.vue';
import create_map from '../pages/maping/create_map.vue';
import edit_map from '../pages/maping/edit_map.vue';
import map_reasons from '../pages/maping/map_reasons.vue';

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

    { path: '/comments', name: 'comments', component: comments },

    { path: '/musics', name: 'musics', component: musics },
    { path: '/musics/create', name: 'create_music', component: create_music },
    { path: '/musics/edit/:id', name: 'edit_music', component: edit_music },

    { path: '/movies', name: 'movies', component: movies },
    { path: '/movies/create', name: 'create_movie', component: create_movie },
    { path: '/movies/edit/:id', name: 'edit_movie', component: edit_movie },

    { path: '/edit_texts/:type/:textable_id', name: 'edit_texts', component: edit_texts },
    { path: '/join_text', name: 'join_text', component: join_text },

    { path: '/singers', name: 'singers', component: singers },

    { path: '/albums', name: 'albums', component: albums },
    { path: '/albums/create', name: 'create_album', component: create_album },
    { path: '/albums/edit/:id', name: 'edit_album', component: edit_album },

    { path: '/categories', name: 'categories', component: categories },
    { path: '/categories/create', name: 'create_category', component: create_category },
    { path: '/categories/edit/:id', name: 'edit_category', component: edit_category },
    { path: '/categories/items/:id', name: 'category_items', component: category_items },

    { path: '/sliders', name: 'sliders', component: sliders },
    { path: '/sliders/create', name: 'create_slider', component: create_slider },
    { path: '/sliders/edit/:id', name: 'edit_slider', component: edit_slider },

    { path: '/pays', name: 'pays', component: pays },
    { path: '/support', name: 'support', component: dashboard },
    { path: '/settings', name: 'settings', component: settings },
    { path: '/music_orders', name: 'music_orders', component: music_orders },

    { path: '/notifications', name: 'notifications', component: notifications },

    { path: '/words', name: 'words', component: words },
    { path: '/words/create', name: 'create_word', component: create_word },
    { path: '/words/edit/:id', name: 'edit_word', component: edit_word },
    { path: '/words/replace-texts/:type/:id', name: 'replace_texts', component: replace_texts },
    { path: '/words/replace-rules', name: 'replace_rules', component: replace_rules },

    { path: '/idioms', name: 'idioms', component: idioms },
    { path: '/idioms/create', name: 'create_idiom', component: create_idiom },
    { path: '/idioms/edit/:id', name: 'edit_idiom', component: edit_idiom },

    { path: '/grammers', name: 'grammers', component: grammers },
    { path: '/grammers/create', name: 'create_grammer', component: create_grammer },
    { path: '/grammers/edit/:id', name: 'edit_grammer', component: edit_grammer },
    { path: '/grammers/rules', name: 'grammer_rules', component: grammer_rules },

    { path: '/maps', name: 'maps', component: maps },
    { path: '/maps/create', name: 'create_map', component: create_map },
    { path: '/maps/edit/:id', name: 'edit_map', component: edit_map },
    { path: '/maps/reasons', name: 'map_reasons', component: map_reasons },
];
export default allUrl;
