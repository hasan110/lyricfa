<template>
    <div>

        <div class="page-head">
            <div class="titr">فیلم ها</div>
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

                <v-btn color="success" dens @click="$router.push({name:'create_movie'})">
                    افزودن فیلم جدید
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
                            <th>فیلم</th>
                            <th>نوع</th>
                            <th>سطح</th>
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
                                    <div v-if="item.film_poster" class="d-flex ml-4 py-1">
                                        <img :src="item.film_poster" class="rounded m-1" width="40" alt="film poster">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span>{{item.english_name}}</span>
                                        <span>{{item.persian_name}}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <template v-if="item.type == 1">
                                    فیلم
                                </template>
                                <template v-if="item.type == 2">
                                    سریال
                                </template>
                            </td>
                            <td>
                                <span v-if="item.level" :style="{color:levelColor(item.level)}">
                                    {{item.level}}
                                </span>
                                <template v-else>
                                    _
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
                                    <router-link :to="{ name:'edit_movie' , params:{ id:item.id } }">
                                        ویرایش
                                    </router-link>
                                </v-btn>
                                <v-btn v-if="item.type === 1" color="primary" dens small>
                                    <router-link :to="{ name:'edit_texts' , params:{ type:'film',textable_id:item.id } }">
                                        ویرایش متن
                                    </router-link>
                                </v-btn>
                                <v-btn v-if="item.type === 2" color="primary" dens small @click="getChilds(item.id , 1)">
                                    بخش ها
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
            max-width="750"
            v-model="chlids_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >
                    <div style="display: flex;width: 100%;justify-content: space-between;align-items: center;">
                        <span>لیست بخش ها</span>
                        <span><v-btn color="danger" @click="chlids_modal = false">بستن</v-btn></span>
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
                            <th>نوع</th>
                            <th>سطح</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="item in child_list"
                            :key="item.id"
                        >
                            <td>{{ item.id }}</td>
                            <td>
                                <div class="d-flex align-center">
                                    <div v-if="item.film_poster" class="d-flex ml-4 py-1">
                                        <img :src="item.film_poster" class="rounded m-1" width="40" alt="film poster">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span>{{item.english_name}}</span>
                                        <span>{{item.persian_name}}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <template v-if="item.type == 3">
                                    فصل
                                </template>
                                <template v-if="item.type == 4">
                                    قسمت
                                </template>
                            </td>
                            <td>
                                <span v-if="item.level" :style="{color:levelColor(item.level)}">
                                    {{item.level}}
                                </span>
                                <template v-else>
                                    _
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
                                    <router-link :to="{ name:'edit_movie' , params:{ id:item.id } }">
                                        ویرایش
                                    </router-link>
                                </v-btn>
                                <v-btn v-if="parseInt(item.type) === 4" color="primary" dens small>
                                    <router-link :to="{ name:'edit_texts' , params:{ type:'film',textable_id:item.id } }">
                                        ویرایش متن
                                    </router-link>
                                </v-btn>
                                <v-btn v-if="parseInt(item.type) === 3" color="primary" dens small @click="getChilds(item.id , 2)">
                                    بخش ها
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
            max-width="750"
            v-model="sub_chlids_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >
                    <div style="display: flex;width: 100%;justify-content: space-between;align-items: center;">
                        <span>لیست بخش ها</span>
                        <span><v-btn color="danger" @click="sub_chlids_modal = false">بستن</v-btn></span>
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
                            <th>سطح</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="item in sub_child_list"
                            :key="item.id"
                        >
                            <td>{{ item.id }}</td>
                            <td>
                                <div class="d-flex align-center">
                                    <div v-if="item.film_poster" class="d-flex ml-4 py-1">
                                        <img :src="item.film_poster" class="rounded m-1" width="40" alt="film poster">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span>{{item.english_name}}</span>
                                        <span>{{item.persian_name}}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span v-if="item.level" :style="{color:levelColor(item.level)}">
                                    {{item.level}}
                                </span>
                                <template v-else>
                                    _
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
                                    <router-link :to="{ name:'edit_movie' , params:{ id:item.id } }">
                                        ویرایش
                                    </router-link>
                                </v-btn>
                                <v-btn color="primary" dens small>
                                    <router-link :to="{ name:'edit_texts' , params:{ type:'film',textable_id:item.id } }">
                                        ویرایش متن
                                    </router-link>
                                </v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>

            </v-card>
        </v-dialog>
    </div>
</template>
<script>
export default {
    name:'movies',
    data: () => ({
        list:[],
        child_list:[],
        sub_child_list:[],
        filter:{
            sort_by : 'newest'
        },
        errors:{},
        sort_by_list: [
            {text: 'جدید ترین ها',value: 'newest'},
            {text: 'قدیمی ترین ها',value: 'oldest'},
        ],
        current_page:1,
        per_page:0,
        last_page:5,
        fetch_loading:false,
        chlids_modal:false,
        sub_chlids_modal:false,
    }),
    watch:{
        current_page(){
            this.getList();
        }
    },
    methods:{
        getList(){
            this.fetch_loading = true
            this.$http.post(`movies/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.fetch_loading = false
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        getChilds(id , status){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`movies/list_parts` , {id})
                .then(res => {
                    if (status === 1){
                        this.child_list = res.data.data
                        this.chlids_modal = true
                    }else{
                        this.sub_child_list = res.data.data
                        this.sub_chlids_modal = true
                    }
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                })
                .catch( () => {
                    this.$store.commit('SHOW_APP_LOADING' , 0)
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
        this.setPageTitle('فیلم ها')
    }
}
</script>
<style>
</style>
