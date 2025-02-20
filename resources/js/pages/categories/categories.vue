<template>
    <div>
        <div class="page-head">
            <div class="titr">دسته بندی ها</div>
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
                <v-btn color="success" dens @click="$router.push({name:'create_category'})">
                    دسته بندی جدید
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
                            <th>نوع</th>
                            <th>دسته بندی برای</th>
                            <th>وضعیت</th>
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
                                <div class="d-flex align-center">
                                    <div v-if="item.category_poster" class="d-flex ml-1 py-1">
                                        <img :src="item.category_poster" class="rounded m-1" width="40" alt="film poster">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span>{{item.title}} &nbsp; <v-badge v-if="item.permission_label" color="deep-purple darken-3" :content="item.permission_label"></v-badge></span>
                                        <span>{{item.subtitle}}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="pa-1 rounded" :style="{background: item.color ? item.color : 'transparent' , color: item.color ? getTextColor(item.color) : 'initial'}">
                                    <template v-if="item.mode === 'category'">
                                        دسته بندی
                                    </template>
                                    <template v-if="item.mode === 'label'">
                                        برچسب
                                    </template>
                                </span>
                            </td>
                            <td>
                                <template v-if="item.belongs_to === 'grammers'">
                                    گرامر
                                </template>
                                <template v-if="item.belongs_to === 'musics'">
                                    آهنگ
                                </template>
                                <template v-if="item.belongs_to === 'films'">
                                    فیلم
                                </template>
                                <template v-if="item.belongs_to === 'word_definitions'">
                                    معنی لغات
                                </template>
                                <template v-if="item.belongs_to === 'idiom_definitions'">
                                    معنی عبارات
                                </template>
                            </td>
                            <td>
                                <template v-if="parseInt(item.status) === 1">
                                    <v-badge color="success" content="فعال"></v-badge>
                                </template>
                                <template v-else>
                                    <v-badge color="warning" content="غیر فعال"></v-badge>
                                </template>
                            </td>
                            <td>
                                <v-btn color="primary" dens small>
                                    <router-link :to="{ name:'edit_category' , params:{ id:item.id } }">
                                        ویرایش
                                    </router-link>
                                </v-btn>
                                <v-btn @click="getChildren(item.id)" v-if="item.mode === 'category'" color="success" dens small>
                                    زیردسته ها
                                </v-btn>
                                <v-btn v-if="!item.is_parent" @click="group_category_id = item.id , group_category_modal = true" color="purple" dark dens small>
                                    افزودن گروهی
                                </v-btn>
                                <v-btn v-if="!item.is_parent" color="purple darken-4" dens small>
                                    <router-link :to="{ name:'category_items' , params:{ id:item.id } }">
                                        آیتم ها
                                    </router-link>
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
            max-width="1000"
            v-model="children_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >
                    <div style="display: flex;width: 100%;justify-content: space-between;align-items: center;">
                        <span>لیست زیر دسته ها</span>
                        <span><v-btn color="danger" @click="children_modal = false">بستن</v-btn></span>
                    </div>
                </v-toolbar>

                <v-simple-table
                    fixed-header
                    height="400px"
                >
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان</th>
                            <th>وضعیت</th>
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
                            v-for="item in child_list"
                            :key="item.id"
                        >
                            <td>{{ item.id }}</td>
                            <td>
                                <div class="d-flex align-center">
                                    <div v-if="item.category_poster" class="d-flex ml-1 py-1">
                                        <img :src="item.category_poster" class="rounded m-1" width="40" alt="film poster">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span>{{item.title}} &nbsp; <v-badge v-if="item.permission_label" color="deep-purple darken-3" :content="item.permission_label"></v-badge></span>
                                        <span>{{item.subtitle}}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <template v-if="parseInt(item.status) === 1">
                                    <v-badge color="success" content="فعال"></v-badge>
                                </template>
                                <template v-else>
                                    <v-badge color="warning" content="غیر فعال"></v-badge>
                                </template>
                            </td>
                            <td>
                                <v-btn color="primary" dens small>
                                    <router-link :to="{ name:'edit_category' , params:{ id:item.id } }">
                                        ویرایش
                                    </router-link>
                                </v-btn>
                                <v-btn @click="getChildren(item.id)" v-if="item.mode === 'category'" color="success" dens small>
                                    زیردسته ها
                                </v-btn>
                                <v-btn v-if="!item.is_parent" @click="group_category_id = item.id , group_category_modal = true" color="purple" dark dens small>
                                    افزودن گروهی
                                </v-btn>
                                <v-btn v-if="!item.is_parent" color="purple darken-4" dens small>
                                    <router-link :to="{ name:'category_items' , params:{ id:item.id } }">
                                        آیتم ها
                                    </router-link>
                                </v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </v-card>
        </v-dialog>

        <v-dialog
            transition="dialog-top-transition"
            max-width="1000"
            v-model="group_category_modal"
        >
            <v-card>
                <v-toolbar
                    color="purple darken-4"
                    dark
                >
                    <div style="display: flex;width: 100%;justify-content: space-between;align-items: center;">
                        <span>اضافه کردن آیتم به دسته بندی با شناسه {{group_category_id}}</span>
                    </div>
                </v-toolbar>
                <v-card-text>
                    <v-row class="mt-2">
                        <v-col cols="12">
                            <v-select
                                label="انتخاب مدل مورد نظر"
                                item-text="item" item-value="value"
                                :items="[
                                    {'item':'گرامر','value':'grammers'},
                                    {'item':'آهنگ','value':'musics'},
                                    {'item':'فیلم','value':'films'},
                                    {'item':'معنی لغت','value':'word_definitions'},
                                    {'item':'معنی عبارت','value':'idiom_definitions'}
                                ]"
                                :error-messages="errors.category_type"
                                solo dense v-model="group_category_type"
                            ></v-select>
                        </v-col>
                        <v-col cols="12">
                            <v-textarea placeholder="شناسه های مورد نظر را وارد کنید" :error-messages="errors.category_list_ids" rows="12" dense v-model="group_category_list_ids" solo hint="شناسه ها را با اینتر جدا کنید"></v-textarea>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn :loading="group_loading" color="indigo darken-4" dark @click="addItemToCategory()">
                        اعمال
                    </v-btn>
                    <v-btn color="danger" dark @click="group_category_modal = false">
                        بستن
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
export default {
    name:'categories',
    data: () => ({
        list:[],
        child_list:[],
        filter:{
            sort_by : 'newest'
        },
        errors:{},
        sort_by_list: [
            {text: 'جدید ترین ها',value: 'newest'},
            {text: 'قدیمی ترین ها',value: 'oldest'},
        ],
        current_page:1,
        last_page:0,
        group_category_type:'',
        group_category_list_ids:null,
        group_category_id:null,
        group_category_modal: false,
        parent_id:null,
        group_loading:false,
        fetch_loading:false,
        children_modal:false,
    }),
    watch:{
        current_page(){
            this.getList();
        }
    },
    methods:{
        getList(){
            this.fetch_loading = true
            this.$http.post(`categories/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.fetch_loading = false
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        getChildren(id){
            this.parent_id = id;
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`categories/list?page=1` , {parent_id:id,mode:'category'})
                .then(res => {
                    this.child_list = res.data.data.data
                    this.children_modal = true;
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                })
                .catch( () => {
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                });
        },
        addItemToCategory(){
            this.group_loading = true;
            const data = {
                category_type: this.group_category_type,
                category_list_ids: this.group_category_list_ids,
                category_id: this.group_category_id,
            };
            this.$http.post(`categories/add/group` , data)
                .then(res => {
                    this.group_category_list_ids = null;
                    this.group_loading = false;
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.group_category_modal = false;
                })
                .catch( err => {
                    this.group_loading = false;
                    const e = err.response.data
                    if(e.errors){ this.errors = e.errors }
                    else if(e.message){
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
        }
    },
    mounted(){
        this.getList()
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('دسته بندی ها')
    }
}
</script>
<style>
</style>
