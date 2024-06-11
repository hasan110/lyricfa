<template>
    <div>

        <div class="page-head">
            <div class="titr">نوتیفیکیشن ها</div>
            <div class="back">
                <router-link :to="{ name : 'dashboard' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <v-container>
            <v-row class="md-section">

                <v-col cols="4" class="pb-0">

                    <v-text-field
                        v-model="filter.search_key"
                        :append-outer-icon="'mdi-magnify'"
                        outlined
                        clearable
                        dense
                        label="جست و جو"
                        type="text"
                        @click:append-outer="reset()"
                    ></v-text-field>

                </v-col>
                <v-col cols="4" class="pb-0">
                </v-col>
                <v-col cols="4" class="pb-0">
                    <v-select
                        label="مرتب سازی بر اساس"
                        :items="sort_by_list"
                        v-model="filter.sort_by"
                        item-text="text"
                        item-value="value"
                        append-outer-icon="mdi-filter"
                        outlined
                        clearable
                        autocomplete
                        dense
                        @click:append-outer="reset()"
                    ></v-select>
                </v-col>

            </v-row>

            <div class="sm-section">

                <v-btn color="success" dens @click="create_modal = true">
                    افزودن نوتیفیکیشن
                </v-btn>

            </div>

            <div class="main-section">
                <v-simple-table
                    fixed-header
                    height="100%"
                    style="height:100%"
                >
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان</th>
                            <th style="width: 400px;">متن</th>
                            <th>نوع</th>
                            <th>وضعیت ارسال</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <div class="fetch-loading">
                            <v-progress-linear
                                v-if="fetch_loading"
                                indeterminate
                                color="cyan"
                            ></v-progress-linear>
                        </div>
                        <tbody>
                        <tr
                            v-for="item in list"
                            :key="item.id"
                        >
                            <td>{{ item.id }}</td>
                            <td>{{item.title}}</td>
                            <td class="two-line-box">{{item.body}}</td>
                            <td>
                                <template v-if="item.type === 'all'">گروهی</template>
                                <template v-else>تکی</template>
                            </td>
                            <td>
                                <template v-if="item.status_send">ارسال شده</template>
                                <template v-else>در انتظار ارسال</template>
                            </td>
                            <td>
                                <v-btn v-if="!item.status_send && item.type === 'all'" color="primary" dens @click="sendNotification(item.id)">
                                    ارسال
                                </v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </div>

            <div class="sm-section">
                <v-pagination
                    v-model="current_page"
                    :length="last_page"
                    :total-visible="7"
                ></v-pagination>
            </div>

        </v-container>

        <v-dialog
            transition="dialog-top-transition"
            max-width="600"
            v-model="create_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >افزودن نوتیفیکیشن</v-toolbar>
                <v-card-text>

                    <v-container>

                        <v-row class="pt-3">

                            <v-col cols="12" class="pb-0">
                                <v-text-field
                                    v-model="form_data.title"
                                    outlined
                                    clearable
                                    dense
                                    label="عنوان"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" class="pb-0">
                                <v-file-input
                                    v-model="form_data.image"
                                    outlined
                                    show-size
                                    dense
                                    label="آپلود تصویر"
                                    accept="image/*"
                                    persistent-hint hint="فرمت تصویر باید jpg و سایز آن باید (200 تا 500) در (200 تا 500) باشد"
                                ></v-file-input>
                            </v-col>

                            <v-col cols="12" class="pb-0">
                                <v-text-field
                                    v-model="music_ids"
                                    outlined
                                    dense
                                    label="شناسه موزیک را وارد کرده و Enter بزنید"
                                    hint="برای وارد کردن بازه بین دو شناسه - قرار دهید (تعداد موزیک ها بیشتر از 50 عدد نمیتواند باشد)"
                                    @keyup.enter="getMusicData"
                                ></v-text-field>
                            </v-col>

                        </v-row>

                        <v-row class="pt-3">


                            <v-col cols="12" class="pb-0">
                                <v-textarea
                                    v-model="form_data.body"
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
                    <v-btn color="danger" @click="create_modal = false">بستن</v-btn>
                    <v-btn
                        :loading="create_loading"
                        :disabled="create_loading"
                        color="success"
                        @click="saveNotification()"
                    >ایجاد</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog
            transition="dialog-top-transition"
            max-width="600"
            v-model="edit_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >ویرایش خواننده</v-toolbar>
                <v-card-text>

                    <v-container>

                        <v-row class="pt-3">

                            <v-col cols="6" class="pb-0">
                                <v-text-field
                                    v-model="edit_form_data.persian_name"
                                    outlined
                                    clearable
                                    dense
                                    label="نام گروه یا خواننده به فارسی"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" class="pb-0">
                                <v-text-field
                                    v-model="edit_form_data.english_name"
                                    outlined
                                    clearable
                                    dense
                                    label="نام گروه یا خواننده به انگلیسی"
                                ></v-text-field>
                            </v-col>

                        </v-row>

                        <v-row class="pt-3">

                            <v-col cols="12" class="pb-0">
                                <v-file-input
                                    v-model="edit_form_data.singer_picture"
                                    outlined
                                    show-size
                                    dense
                                    label="آپلود تصویر جدید خواننده"
                                    accept="image/*"
                                ></v-file-input>
                            </v-col>

                        </v-row>

                    </v-container>

                </v-card-text>

                <v-card-actions class="justify-end">
                    <v-btn color="danger" @click="edit_modal = false">بستن</v-btn>
                    <v-btn
                        :loading="edit_loading"
                        :disabled="edit_loading"
                        color="success"
                        @click="updateSinger()"
                    >ویرایش</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
export default {
    name:'notifications',
    data: () => ({
        list:[],
        form_data:{},
        edit_form_data:{},
        filter:{},
        errors:{},
        sort_by_list: [
            {text: 'جدید ترین ها',value: 'newest'},
            {text: 'قدیمی ترین ها',value: 'oldest'},
        ],
        current_page:1,
        per_page:0,
        last_page:5,
        music_ids:'',
        create_modal:false,
        create_loading:false,
        edit_modal:false,
        edit_loading:false,
        fetch_loading:false,
    }),
    watch:{
        current_page(){
            this.getList();
        }
    },
    methods:{
        getList(){
            this.fetch_loading = true
            this.$http.post(`notifications/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                    this.fetch_loading = false
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        sendNotification(notif_id){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`notifications/send` , {id:notif_id})
                .then(res => {
                    this.reset()
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                })
                .catch( () => {
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                });
        },
        saveNotification(){
            this.create_loading = true
            const d = new FormData();
            const x = this.form_data;

            x.title ? d.append('title', x.title) : '';
            x.body ? d.append('body', x.body) : '';
            x.image ? d.append('image', x.image) : '';
            d.append('type', 'all');

            this.$http.post(`notifications/create` , d)
                .then(res => {
                    this.create_loading = false
                    this.form_data = {};
                    this.create_modal = false

                    this.reset()

                    this.$fire({
                        title: "موفق",
                        text: res.message,
                        type: "success",
                        timer: 5000
                    })
                })
                .catch( err => {
                    this.create_loading = false
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
        getMusicData(){
            this.create_loading = true

            this.$http.post(`notifications/get-music-data` , {music_ids : this.music_ids})
                .then(res => {
                    this.create_loading = false
                    let text = this.form_data.body ?? '';
                    this.form_data.body = text + res.data.data + `\n`;
                    this.music_ids = '';
                })
                .catch( err => {
                    this.create_loading = false
                    const e = err.response.data
                    if(e.errors){ this.errors = e.errors }
                    else {
                        this.$fire({
                            title: "خطا",
                            text: e.message,
                            type: "error",
                            timer: 5000
                        })
                    }
                });
        },
        Search(e){
            if (e.keyCode === 13) {
                this.current_page = 1
                this.list = []
                this.getList()
            }
        },
        reset(){
            this.current_page = 1
            this.list = []
            this.getList()
        },
    },
    mounted(){
        this.getList();
    },
    beforeMount(){
        this.checkAuth()
    }
}
</script>
<style>
</style>
