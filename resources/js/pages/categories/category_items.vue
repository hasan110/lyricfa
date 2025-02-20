<template>
    <div>
        <div class="page-head">
            <div class="titr">آیتم های دسته بندی با شناسه {{category_id}}</div>
            <div class="back">
                <router-link :to="{ name : 'categories' }">بازگشت
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
                        dense disabled
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
                        :items="[{text:'نمایش همه',value:'all'},{text:'سطح بندی شده ها',value:'has_level'},{text:'سطح بندی نشده ها',value:'not_has_level'}]"
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
                            <th>تصویر</th>
                            <th>عنوان</th>
                            <th>نوع</th>
                            <th>سطح</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <div class="fetch-loading">
                            <v-progress-linear
                                v-if="loading"
                                indeterminate
                                color="cyan"
                            ></v-progress-linear>
                        </div>
                        <tbody>
                        <tr
                            v-for="(item, key) in list"
                            :key="key"
                        >
                            <td>
                                <div v-if="item.category_item_poster" class="d-flex ml-1 py-1">
                                    <img :src="item.category_item_poster" class="rounded m-1" height="40" alt="category item poster">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{item.title}}</span>
                                    <span>{{item.subtitle}}</span>
                                </div>
                            </td>
                            <td>{{item.type}}</td>
                            <td>{{item.level}}</td>
                            <td>
                                <v-btn color="primary" dens small @click="goTo(item.related_id, item.type)">
                                    جزئیات
                                </v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </div>
        </v-container>
    </div>
</template>
<script>
export default {
    name:'create_movie',
    data: () => ({
        category_id: null,
        list: [],
        filter:{
            sort_by:'all'
        },
        errors:{},
        current_page:1,
        last_page:0,
        loading: false,
    }),
    watch:{
        current_page(){
            this.getCategoryItems();
        }
    },
    methods:{
        getCategoryItems(){
            this.loading = true;
            this.filter.category_id = this.category_id;
            this.$http.post(`categories/items?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.list = res.data.data
                    this.loading = false
                })
                .catch( () => {
                    this.loading = false
                });
        },
        Search(e){
            if (e.keyCode === 13) {
                this.current_page = 1
                this.list = []
                this.getCategoryItems()
            }
        },
        reset(){
            this.current_page = 1
            this.list = []
            this.getCategoryItems()
        },
        goTo(id, type){
            let route = null;
            if (type === 'grammer') {
                route = this.$router.resolve({'name':'edit_grammer' , params:{id}})
            }
            if (type === 'music') {
                route = this.$router.resolve({'name':'edit_music' , params:{id}})
            }
            if (type === 'film') {
                route = this.$router.resolve({'name':'edit_movie' , params:{id}})
            }
            if (type === 'word_definition') {
                route = this.$router.resolve({'name':'edit_word' , params:{id}})
            }
            if (type === 'idiom_definition') {
                route = this.$router.resolve({'name':'edit_idiom' , params:{id}})
            }
            if (route) {
                window.open(route.href, '_blank');
            }
        }
    },
    mounted(){
        this.category_id = this.$route.params.id;
        this.getCategoryItems();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('آیتم های دسته بندی')
    }
}
</script>
<style>
</style>
