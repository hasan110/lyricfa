<template>
    <div>

        <div class="page-head">
            <div class="titr">
                گرامر
                <v-chip :to="{ name : 'words' }" small class="mr-2">لغات</v-chip>
                <v-chip :to="{ name : 'idioms' }" small class="mr-1">اصطلاحات</v-chip>
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

                <v-btn color="success" dens :to="{name:'create_grammer'}">
                    افزودن گرامر
                </v-btn>
                <v-btn color="primary" dens :to="{name:'grammer_rules'}">
                    قانون گرامر
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
                            <th>عنوان فارسی</th>
                            <th>عنوان انگلیسی</th>
                            <th>سطح</th>
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
                            <td>{{ item.english_name }}</td>
                            <td>{{ item.persian_name }}</td>
                            <td :style="{color:levelColor(item.level)}">{{ item.level }}</td>
                            <td>
                                <v-btn color="primary" dark dens :to="{name:'edit_grammer' , params:{id : item.id}}">
                                    ویرایش
                                </v-btn>
                                <v-btn color="danger" small fab dark @click="deleteGrammer(item.id)">
                                    <v-icon>mdi-delete</v-icon>
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

    </div>
</template>
<script>
export default {
    name:'words',
    data: () => ({
        list:[],
        map_reasons_filter:{},
        form_data:{},
        filter:{},
        errors:{},
        map_reasons:[],
        word_types:[],
        sort_by_list: [
            {text: 'اول به آخر',value: 'asc'},
            {text: 'آخر به اول',value: 'desc'},
        ],
        current_page:1,
        last_page:0,
        fetch_loading:false,
        loading:false,
        add_grammer_rule_modal:false,
    }),
    watch:{
        current_page(){
            this.getList();
        }
    },
    methods:{
        getList(){
            this.fetch_loading = true
            this.$http.post(`grammers/list?page=${this.current_page}` , this.filter)
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
        deleteGrammer(id){
            if (confirm('آیا از حذف این مورد اطمینان دارید؟')) {
                this.$http.post(`grammers/remove` , {id})
                    .then(res => {
                        this.$fire({
                            title: "موفق",
                            text: res.data.message,
                            type: "success",
                            timer: 5000
                        })
                        this.reset()
                    })
                    .catch( err => {
                        const e = err.response.data
                        if(e.errors){ this.edit_errors = e.errors }
                        else if(e.message){
                            this.$fire({
                                title: "خطا",
                                text: e.message,
                                type: "error",
                                timer: 5000
                            })
                        }
                    });
            }
        },
    },
    mounted(){
        this.filter.sort_by = 'asc';
        this.getList();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('گرامر ها')
    }
}
</script>
<style>
</style>
