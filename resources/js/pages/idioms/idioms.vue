<template>
    <div>

        <div class="page-head">
            <div class="titr">
                اصطلاحات
                <v-chip :to="{ name : 'words' }" small class="mr-2">لغات</v-chip>
                <v-chip :to="{ name : 'grammers' }" small class="mr-1">گرامرها</v-chip>
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
                    <v-checkbox v-model="filter.equals" class="mt-1" label="برابر باشد"></v-checkbox>
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

                <v-btn color="success" dark dens :to="{name:'create_idiom'}">
                    افزودن اصطلاح
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
                            <th>لغت پایه</th>
                            <th>اصطلاح</th>
                            <th>سطح</th>
                            <th>نوع</th>
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
                            <td>{{ item.base }}</td>
                            <td>{{ item.phrase }}</td>
                            <td style="width: 60px;" class="en-font">
                                <v-select
                                    v-model="item.level" outlined
                                    :items="levels" dense hide-details
                                    @change="updateLevel(item.id, item.level)"
                                ></v-select>
                            </td>
                            <td>
                                <template v-if="parseInt(item.type) === 1">عبارت دو بخشی</template>
                                <template v-else-if="parseInt(item.type) === 2">کالوکیشن</template>
                                <template v-else-if="parseInt(item.type) === 3">افعال عبارتی</template>
                                <template v-else-if="parseInt(item.type) === 4">اصطلاحات</template>
                                <template v-else-if="parseInt(item.type) === 5">ضرب المثل</template>
                                <template v-else-if="parseInt(item.type) === 6">اسلنگ</template>
                                <template v-else-if="parseInt(item.type) === 7">عبارت</template>
                                <template v-else>
                                    انتخاب نشده
                                </template>
                            </td>
                            <td>
                                <v-btn color="primary" dark dens :to="{name:'edit_idiom' , params:{id : item.id}}">
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

    </div>
</template>
<script>
export default {
    name:'words',
    data: () => ({
        list:[],
        filter:{
            equals:true
        },
        errors:{},
        sort_by_list: [
            {text: 'اول به آخر',value: 'asc'},
            {text: 'آخر به اول',value: 'desc'},
        ],
        current_page:1,
        last_page:0,
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
            this.$http.post(`idioms/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                    this.fetch_loading = false
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        updateLevel(id, level){
            this.$http.post(`idioms/update_level?` , {id,level})
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
        this.filter.sort_by = 'desc';
        this.getList();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('اصطلاحات')
    }
}
</script>
<style>
</style>
