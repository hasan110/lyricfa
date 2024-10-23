<template>
    <div>

        <div class="page-head">
            <div class="titr">
                خواننده ها
                <v-chip :to="{ name : 'musics' }" small class="mr-2">موزیک ها</v-chip>
                <v-chip :to="{ name : 'albums' }" small class="mr-1">آلبوم ها</v-chip>
            </div>
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
                        @keyup.enter="reset()"
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
                    افزودن خواننده
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
                            <th>عکس</th>
                            <th>نام</th>
                            <th>آمار</th>
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
                            <td>
                                <div v-if="item.singer_poster" class="d-flex">
                                    <img :src="item.singer_poster" class="item-profile rounded-circle" alt="singer poster">
                                </div>
                                <span v-else>ندارد</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{item.english_name}}</span>
                                    <span>{{item.persian_name}}</span>
                                </div>
                            </td>
                            <td>
                                <span class="pa-2">لایک : {{ item.num_like }}</span>
                                <span class="pa-2">آهنگ ها : {{ item.num_musics }}</span>
                            </td>
                            <td>
                                <v-btn color="primary" dens @click="getSinger(item.id)">
                                    ویرایش
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
                >افزودن خواننده</v-toolbar>
                <v-card-text>

                    <v-container>

                        <v-row class="pt-3">

                            <v-col cols="6" class="pb-0">
                                <v-text-field
                                    v-model="form_data.english_name"
                                    outlined
                                    clearable
                                    dense
                                    label="نام گروه یا خواننده به انگلیسی"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" class="pb-0">
                                <v-text-field
                                    v-model="form_data.persian_name"
                                    outlined
                                    clearable
                                    dense
                                    label="نام گروه یا خواننده به فارسی"
                                ></v-text-field>
                            </v-col>

                        </v-row>

                        <v-row class="pt-3">

                            <v-col cols="12" class="pb-0">
                                <v-file-input
                                    v-model="form_data.singer_picture"
                                    outlined
                                    show-size
                                    dense persistent-hint
                                    hint="فرمت تصویر باید jpg و سایز آن 300*300 باشد"
                                    label="آپلود تصویر خواننده"
                                    accept="image/*"
                                ></v-file-input>
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
                        @click="saveSinger()"
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
                                    v-model="edit_form_data.english_name"
                                    outlined
                                    clearable
                                    dense
                                    label="نام گروه یا خواننده به انگلیسی"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" class="pb-0">
                                <v-text-field
                                    v-model="edit_form_data.persian_name"
                                    outlined
                                    clearable
                                    dense
                                    label="نام گروه یا خواننده به فارسی"
                                ></v-text-field>
                            </v-col>

                        </v-row>

                        <v-row class="pt-3">

                            <v-col cols="12" class="pb-0">
                                <v-file-input
                                    v-model="edit_form_data.singer_picture"
                                    outlined
                                    show-size
                                    dense persistent-hint
                                    hint="فرمت تصویر باید jpg و سایز آن 300*300 باشد"
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
    name:'singers',
    data: () => ({
        list:[],
        form_data:{},
        edit_form_data:{},
        filter:{},
        errors:{},
        sort_by_list: [
            {text: 'جدید ترین ها',value: 'newest'},
            {text: 'قدیمی ترین ها',value: 'oldest'},
            {text: 'نام فارسی',value: 'persian_name'},
            {text: 'نام انگلیسی',value: 'english_name'},
        ],
        current_page:1,
        per_page:0,
        last_page:5,
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
            this.$http.post(`singers/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                    this.fetch_loading = false
                })
                .catch( () => {
                    this.fetch_loading = false
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
        saveSinger(){
            this.create_loading = true
            const d = new FormData();
            const x = this.form_data;

            x.english_name ? d.append('english_name', x.english_name) : '';
            x.persian_name ? d.append('persian_name', x.persian_name) : '';
            x.singer_picture ? d.append('image', x.singer_picture) : '';

            this.$http.post(`singers/create` , d)
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
        getSinger(singer_id){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`singers/single` , {id:singer_id})
                .then(res => {
                    this.edit_form_data = res.data.data
                    this.edit_modal = true
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                })
                .catch( () => {
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                });
        },
        updateSinger(){
            this.edit_loading = true
            const d = new FormData();
            const x = this.edit_form_data;

            d.append('id', x.id);
            x.english_name ? d.append('english_name', x.english_name) : '';
            x.persian_name ? d.append('persian_name', x.persian_name) : '';
            x.singer_picture ? d.append('image', x.singer_picture) : '';

            this.$http.post(`singers/update` , d)
                .then(res => {
                    this.edit_loading = false
                    this.edit_form_data = {};
                    this.edit_modal = false
                    this.reset()

                    this.$fire({
                        title: "موفق",
                        text: res.message,
                        type: "success",
                        timer: 5000
                    })
                })
                .catch( err => {
                    this.edit_loading = false
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
    },
    mounted(){
        this.filter.sort_by = 'newest';

        const english_name = this.$route.query.english_name;
        if (english_name) {
            this.filter.search_key = english_name;
        }

        this.getList();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('خواننده ها')
    }
}
</script>
<style>
</style>
