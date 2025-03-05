<template>
    <div>
        <div class="page-head">
            <div class="titr">اطلاعات کاربر</div>
            <div class="back">
                <router-link :to="{ name : 'users' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <v-container fluid>
            <v-row>
                <v-col cols="12" sm="12" md="6" lg="4">
                    <v-card max-width="100%" color="#8B50FF" dark>
                        <v-card-title v-if="user.phone_number" class="ltr-dir justify-center">
                            +{{user.prefix_code}}{{user.phone_number}}
                        </v-card-title>
                        <v-card-title v-if="user.email" class="ltr-dir justify-center">
                            {{user.email}}
                        </v-card-title>
                        <v-card-subtitle>
                            {{user.fullname}}
                        </v-card-subtitle>
                        <v-card-title>
                            تاریخ ثبت نام:
                            {{ user.persian_created_at }}
                        </v-card-title>
                        <v-card-subtitle class="pt-0 mt-4">
                            سطح:
                            <span v-if="user.level" :style="{color: levelColor(user.level)}">
                                    {{user.level}}
                                </span>
                            <span v-else>نامشخص</span>
                        </v-card-subtitle>
                        <v-card-subtitle class="pt-0">
                             ثبت نام از:
                            {{ user.corridor }}
                        </v-card-subtitle>
                        <v-card-subtitle class="pt-0">
                            کد معرفی به دیگران: {{user.code_introduce}}
                        </v-card-subtitle>
                        <v-card-subtitle v-if="user.referral_code" class="pt-0">
                            کد معرف: {{user.referral_code}}
                        </v-card-subtitle>
                        <v-card-title>
                            وضعیت اشتراک :
                            <template v-if="user.days_remain">
                                {{user.days_remain}} روز باقی مانده
                            </template>
                            <template v-else>
                                بدون اشتراک
                            </template>
                        </v-card-title>
                        <v-card-title>
                            <v-btn color="success" dens @click="create_notif_modal = true">
                                ارسال نوتیفیکیشن
                            </v-btn>
                        </v-card-title>
                    </v-card>
                </v-col>
                <v-col cols="12" sm="12" md="6" lg="4">
                    <v-card>
                        <v-card-title>تمدید اشتراک</v-card-title>
                        <v-card-text>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        v-model="subscribe.title"
                                        label="چند روز؟"
                                        append-outer-icon="mdi-minus"
                                        @click:append-outer="subscribe.title--"
                                        prepend-icon="mdi-plus"
                                        @click:prepend="subscribe.title++"
                                        outlined dense hide-details
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-select
                                        :items="[{value:0 , title:'پولی'},{value:1 , title:'رایگان'}]"
                                        v-model="subscribe.type"
                                        item-text="title"
                                        item-value="value"
                                        label="نوع اعمال"
                                        outlined dense hide-details
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" v-if="subscribe.type === 0">
                                    <v-text-field
                                        v-model="subscribe.val_money"
                                        label="مقدار پرداختی (تومان)"
                                        outlined dense hide-details
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" v-if="subscribe.type === 0">
                                    <v-text-field
                                        v-model="subscribe.ref_id"
                                        label="کد درگاه / پیگیری"
                                        outlined dense hide-details
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea
                                        v-model="subscribe.description"
                                        label="توضیحات"
                                        outlined
                                        dense hide-details
                                    >
                                    </v-textarea>
                                </v-col>
                                <v-col cols="12">
                                    <v-btn block :loading="subscribe_loading" :disabled="subscribe_loading" color="success" dens @click="increaseSubscription()">
                                        ثبت
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12" class="pb-0">
                    <h3>📌اشتراک های خریده شده ({{user.subscription.length}})</h3>
                </v-col>
                <v-col cols="12">
                    <v-simple-table
                        fixed-header
                        :height="user.subscription.length === 0 ? '0px' : '300px'"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>شناسه پرداخت</th>
                                <th>مقدار</th>
                                <th>نوع</th>
                                <th>توضیحات</th>
                                <th>تاریخ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item , key) in user.subscription" :key="key">
                                <td>{{item.id}}</td>
                                <td>
                                    <template v-if="item.ref_id">
                                        {{item.ref_id}}
                                    </template>
                                    <template v-else>
                                        ---
                                    </template>
                                </td>
                                <td>
                                    <template v-if="item.val_money">
                                    {{item.val_money}} تومان
                                    </template>
                                    <template v-else>
                                        ---
                                    </template>
                                </td>
                                <td>
                                    <template v-if="parseInt(item.type) === 1">
                                        رایگان
                                    </template>
                                    <template v-else>
                                        خرید
                                    </template>
                                </td>
                                <td>{{item.description}}</td>
                                <td>{{item.persian_created_at}}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <div v-if="user.subscription.length === 0" class="py-4 text-center grey--text">
                        لیست خالی است
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" sm="12" md="6">
                    <v-col cols="12" class="pb-0">
                        <h3>📌لاگ ورود</h3>
                    </v-col>
                    <v-simple-table
                        fixed-header
                        height="200px"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>نوع</th>
                                <th>تاریخ آخرین استفاده</th>
                                <th>تاریخ ایجاد</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in user.access_tokens"
                                :key="item.id"
                            >
                                <td>{{ item.id }}</td>
                                <td>{{ item.name }}</td>
                                <td>{{ item.persian_last_used_at }}</td>
                                <td>{{ item.persian_created_at }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-col>
                <v-col cols="12" sm="12" md="6">
                    <v-col cols="12" class="pb-0">
                        <h3>📌جعبه لایتنر</h3>
                    </v-col>
                    <v-simple-table
                        fixed-header
                        height="200px"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>شماره جعبه</th>
                                <th>تعداد کل</th>
                                <th>تعداد لغات/عبارات/گرامر</th>
                                <th>آماده مرور</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in user.lightener_box_data"
                                :key="item.status"
                            >
                                <td>{{ item.status + 1 }}</td>
                                <td>{{ item.total_count }}</td>
                                <td>{{ item.words_count }} لغت / {{ item.idioms_count }} عبارت / {{ item.grammars_count }} گرامر </td>
                                <td>
                                    <template v-if="item.status === 5">
                                        یادگرفته شده
                                    </template>
                                    <template v-else>
                                        {{ item.reviews_count }}
                                    </template>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12" class="pb-0">
                    <h3>📌بازدید های اخیر</h3>
                </v-col>
                <v-col cols="12">
                    <v-simple-table
                        fixed-header
                        :height="user.views.length === 0 ? '0px' : '300px'"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>بازدید از</th>
                                <th>اطلاعات</th>
                                <th>پیشرفت</th>
                                <th>تاریخ</th>
                                <th>تاریخ بروزرسانی</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item , key) in user.views" :key="key">
                                <td>{{item.id}}</td>
                                <td>
                                    <template v-if="item.viewable">
                                        <template v-if="item.viewable_type === 'App\\Models\\Music'">
                                            آهنگ
                                        </template>
                                        <template v-else-if="item.viewable_type === 'App\\Models\\Film'">
                                            فیلم
                                        </template>
                                    </template>
                                    <template v-else>
                                        ---
                                    </template>
                                </td>
                                <td>
                                    <template v-if="item.viewable">
                                        <template v-if="item.viewable_type === 'App\\Models\\Music'">
                                            <router-link :to="{name:'edit_music' , params:{id:item.viewable.id}}">{{item.viewable.name}}</router-link>
                                        </template>
                                        <template v-else-if="item.viewable_type === 'App\\Models\\Film'">
                                            <router-link :to="{name:'edit_movie' , params:{id:item.viewable.id}}">{{item.viewable.english_name}}</router-link>
                                        </template>
                                    </template>
                                    <template v-else>
                                        ---
                                    </template>
                                </td>
                                <td>
                                    <v-progress-circular
                                        :rotate="-90"
                                        :size="40"
                                        :width="4"
                                        :value="item.percentage"
                                        color="purple darken-4"
                                    >
                                        {{ item.percentage }}%
                                    </v-progress-circular>
                                </td>
                                <td>{{item.persian_created_at}}</td>
                                <td>{{item.persian_updated_at}}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <div v-if="user.subscription.length === 0" class="py-4 text-center grey--text">
                        لیست خالی است
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" sm="12" md="6">
                    <v-col cols="12" class="pb-0">
                        <h3>📌سفارشات آهنگ ({{user.music_orders.length}})</h3>
                    </v-col>
                    <v-simple-table
                        fixed-header
                        :height="user.music_orders.length === 0 ? '0px' : '300px'"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>نام آهنگ</th>
                                <th>نام خواننده</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in user.music_orders"
                                :key="item.id"
                            >
                                <td>{{ item.id }}</td>
                                <td>{{ item.music_name }}</td>
                                <td>{{ item.singer_name }}</td>
                                <td>
                                    <template v-if="parseInt(item.condition_order) === 0">
                                        بررسی نشده
                                    </template>
                                    <template v-if="parseInt(item.condition_order) === 1">
                                        قبول شده
                                    </template>
                                    <template v-if="parseInt(item.condition_order) === 2">
                                        رد شده
                                    </template>
                                    <template v-if="parseInt(item.condition_order) === 3">
                                        موجود
                                    </template>
                                </td>
                                <td>{{ item.persian_created_at }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <div v-if="user.music_orders.length === 0" class="py-4 text-center grey--text">
                        لیست خالی است
                    </div>
                </v-col>
                <v-col cols="12" sm="12" md="6">
                    <v-col cols="12" class="pb-0">
                        <h3>📌لیست پخش ها ({{user.playlists.length}})</h3>
                    </v-col>
                    <v-simple-table
                        fixed-header
                        :height="user.playlists.length === 0 ? '0px' : '300px'"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>نام لیست پخش</th>
                                <th>تاریخ</th>
                                <th>آهنگ ها</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in user.playlists"
                                :key="item.id"
                            >
                                <td>{{ item.id }}</td>
                                <td>{{ item.name }}</td>
                                <td>{{ item.persian_created_at }}</td>
                                <td>
                                    <v-btn fab small dark color="purple" dens @click="user_playlist_musics = item.musics , playlist_musics_modal = true">
                                        <v-icon>mdi-information-outline</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <div v-if="user.likes.length === 0" class="py-4 text-center grey--text">
                        لیست خالی است
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" sm="12" md="6">
                    <v-col cols="12" class="pb-0">
                        <h3>📌نظرات ثبت شده ({{user.comments.length}})</h3>
                    </v-col>
                    <v-simple-table
                        fixed-header
                        :height="user.comments.length === 0 ? '0px' : '300px'"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>متن نظر</th>
                                <th>پاسخ</th>
                                <th>نظر برای</th>
                                <th>اطلاعات</th>
                                <th>تاریخ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in user.comments"
                                :key="item.id"
                            >
                                <td>{{ item.id }}</td>
                                <td :title="item.comment">
                                    <span class="two-line-box">{{item.comment}}</span>
                                </td>
                                <td :title="item.reply">
                                    <span class="two-line-box">{{item.reply}}</span>
                                </td>
                                <td>
                                    <template v-if="item.commentable">
                                        <template v-if="item.commentable_type === 'App\\Models\\Singer'">
                                            خواننده
                                        </template>
                                        <template v-else-if="item.commentable_type === 'App\\Models\\Music'">
                                            آهنگ
                                        </template>
                                        <template v-else-if="item.commentable_type === 'App\\Models\\Film'">
                                            فیلم
                                        </template>
                                    </template>
                                    <template v-else>
                                        ---
                                    </template>
                                </td>
                                <td>
                                    <template v-if="item.commentable">
                                        <template v-if="item.commentable_type === 'App\\Models\\Singer'">
                                            <router-link :to="{name:'singers' , query:{english_name:item.commentable.english_name}}">{{item.commentable.english_name}}</router-link>
                                        </template>
                                        <template v-else-if="item.commentable_type === 'App\\Models\\Music'">
                                            <router-link :to="{name:'edit_music' , params:{id:item.commentable.id}}">{{item.commentable.name}}</router-link>
                                        </template>
                                        <template v-else-if="item.commentable_type === 'App\\Models\\Film'">
                                            <router-link :to="{name:'edit_movie' , params:{id:item.commentable.id}}">{{item.commentable.english_name}}</router-link>
                                        </template>
                                    </template>
                                    <template v-else>
                                        ---
                                    </template>
                                </td>
                                <td>{{ item.persian_created_at }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <div v-if="user.comments.length === 0" class="py-4 text-center grey--text">
                        لیست خالی است
                    </div>
                </v-col>
                <v-col cols="12" sm="12" md="6">
                    <v-col cols="12" class="pb-0">
                        <h3>📌لایک ها ({{user.likes.length}})</h3>
                    </v-col>
                    <v-simple-table
                        fixed-header
                        :height="user.likes.length === 0 ? '0px' : '300px'"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>لایک برای</th>
                                <th>اطلاعات</th>
                                <th>تاریخ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in user.likes"
                                :key="item.id"
                            >
                                <td>{{ item.id }}</td>
                                <td>
                                    <template v-if="item.likeable">
                                        <template v-if="item.likeable_type === 'App\\Models\\Singer'">
                                            خواننده
                                        </template>
                                        <template v-else-if="item.likeable_type === 'App\\Models\\Music'">
                                            آهنگ
                                        </template>
                                        <template v-else-if="item.likeable_type === 'App\\Models\\Film'">
                                            فیلم
                                        </template>
                                    </template>
                                    <template v-else>
                                        ---
                                    </template>
                                </td>
                                <td>
                                    <template v-if="item.likeable">
                                        <template v-if="item.likeable_type === 'App\\Models\\Singer'">
                                            <router-link :to="{name:'singers' , query:{english_name:item.likeable.english_name}}">{{item.likeable.english_name}}</router-link>
                                        </template>
                                        <template v-else-if="item.likeable_type === 'App\\Models\\Music'">
                                            <router-link :to="{name:'edit_music' , params:{id:item.likeable.id}}">{{item.likeable.name}}</router-link>
                                        </template>
                                        <template v-else-if="item.likeable_type === 'App\\Models\\Film'">
                                            <router-link :to="{name:'edit_movie' , params:{id:item.likeable.id}}">{{item.likeable.english_name}}</router-link>
                                        </template>
                                    </template>
                                    <template v-else>
                                        ---
                                    </template>
                                </td>
                                <td>{{ item.persian_created_at }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <div v-if="user.likes.length === 0" class="py-4 text-center grey--text">
                        لیست خالی است
                    </div>
                </v-col>
            </v-row>

            <br>
            <br>
        </v-container>

        <v-dialog
            max-width="600"
            v-model="playlist_musics_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >آهنگ های پلی لیست</v-toolbar>
                <v-card-text class="pa-2">
                    <v-simple-table
                        fixed-header
                        :height="user_playlist_musics.length === 0 ? '0px' : '500px'"
                        class="tbl-nowrap"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>آهنگ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in user_playlist_musics"
                                :key="item.id"
                            >
                                <td>{{ item.id }}</td>
                                <td>
                                    <div class="d-flex align-center">

                                        <div v-if="item.music_poster" class="d-flex ml-4">
                                            <img :src="item.music_poster" class="item-profile m-1" alt="music poster">
                                        </div>
                                        <div v-else class="d-flex ml-4">
                                            <img src="/assets/img/user.jpg" class="item-profile rounded-circle m-1" alt="music poster alt">
                                        </div>

                                        <div class="d-flex flex-column">
                                            <span>
                                                <router-link :to="{name:'edit_music' , params:{id:item.id}}">{{item.name}}</router-link>
                                            </span>
                                            <span>{{item.persian_name}}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <div v-if="user_playlist_musics.length === 0" class="py-4 text-center grey--text">
                        لیست خالی است
                    </div>
                </v-card-text>

                <v-card-actions class="justify-end">
                    <v-btn color="danger" dark @click="playlist_musics_modal = false">بستن</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog
            transition="dialog-top-transition"
            max-width="600"
            v-model="create_notif_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >ارسال نوتیفیکیشن</v-toolbar>
                <v-card-text class="pa-2">
                    <v-container>
                        <v-row class="pt-3">
                            <v-col cols="12" sm="12" md="6" class="pb-0">
                                <v-text-field
                                    v-model="form_data_notif.title"
                                    outlined
                                    clearable
                                    dense
                                    label="عنوان"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="12" md="6" class="pb-0">
                                <v-file-input
                                    v-model="form_data_notif.image"
                                    outlined
                                    show-size
                                    dense
                                    label="آپلود تصویر"
                                    accept="image/*"
                                    persistent-hint hint="فرمت تصویر باید jpg و سایز آن باید (200 تا 500) در (200 تا 500) باشد"
                                ></v-file-input>
                            </v-col>
                        </v-row>
                        <v-row class="pt-3">
                            <v-col cols="12" class="pb-0">
                                <v-textarea
                                    v-model="form_data_notif.body"
                                    outlined
                                    clearable
                                    dense
                                    label="متن"
                                ></v-textarea>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions class="justify-end">
                    <v-btn color="danger" @click="create_notif_modal = false">بستن</v-btn>
                    <v-btn v-if="!notif_id"
                           :loading="create_notif_loading"
                           :disabled="create_notif_loading"
                           color="success"
                           @click="saveNotification()"
                    >ایجاد</v-btn>
                    <v-btn v-if="notif_id"
                           color="blue"
                           @click="sendNotification()"
                    >ارسال</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
export default {
    name:'user',
    data: () => ({
        user_id:null,
        show:false,
        user:{},
        subscribe:{
            title:''
        },
        errors:{},
        playlist_musics_modal:false,
        user_playlist_musics:[],
        form_data_notif:{},
        notif_id:null,
        create_notif_modal:false,
        create_notif_loading:false,
        subscribe_loading:false
    }),
    methods:{
        getUser(){
            this.$store.commit('SHOW_APP_LOADING' , 1);
            this.$http.post(`users/single` , {id : this.user_id})
                .then(res => {
                    this.user = res.data.data
                    this.$store.commit('SHOW_APP_LOADING' , 0);
                })
                .catch( err => {
                    this.$store.commit('SHOW_APP_LOADING' , 0);
                    const e = err.response.data
                    if(e.errors){ this.errors = e.errors }

                    this.$fire({
                        title: "خطا",
                        text: e.message ? e.message : 'خطا در پردازش درخواست !',
                        type: "error",
                        timer: 5000
                    })

                });
        },
        increaseSubscription(){
            this.subscribe_loading = true
            this.subscribe.user_id = this.user_id
            this.$http.post(`users/add_subs` , this.subscribe)
                .then( res => {
                    this.subscribe = {
                        title:''
                    }
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.subscribe_loading = false
                    this.getUser()
                })
                .catch( err => {
                    this.subscribe_loading = false
                    const e = err.response.data
                    if(e.errors){ this.errors = e.errors }

                    this.$fire({
                        title: "خطا",
                        text: e.message ? e.message : 'خطا در پردازش درخواست !',
                        type: "error",
                        timer: 5000
                    })

                });

        },

        sendNotification(){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`notifications/send` , {id:this.notif_id , user_id:this.user.id})
                .then(res => {
                    this.create_notif_modal = false
                    this.notif_id = null
                    this.$fire({
                        title: "موفق",
                        text: res.message,
                        type: "success",
                        timer: 5000
                    })
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                })
                .catch( () => {
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                });
        },
        saveNotification(){
            this.create_notif_loading = true
            const d = new FormData();
            const x = this.form_data_notif;

            x.title ? d.append('title', x.title) : '';
            x.body ? d.append('body', x.body) : '';
            x.image ? d.append('image', x.image) : '';
            d.append('type', 'one');

            this.$http.post(`notifications/create` , d)
                .then(res => {
                    this.create_notif_loading = false

                    this.notif_id = res.data.id

                    this.$fire({
                        title: "موفق",
                        text: res.message,
                        type: "success",
                        timer: 5000
                    })
                })
                .catch( err => {
                    this.create_notif_loading = false
                    const e = err.response.data
                    if(e.errors){ this.errors = e.errors }

                    this.$fire({
                        title: "خطا",
                        text: e.message,
                        type: "error",
                        timer: 5000
                    })
                });
        },
    },
    mounted(){
        this.user_id = this.$route.params.id
        this.getUser()
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('اطلاعات کاربر')
    }
}
</script>
<style>
</style>
