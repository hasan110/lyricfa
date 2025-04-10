<template>
    <div>

        <div class="page-head">
            <div class="titr">اسلایدر ها</div>
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
                <v-btn color="success" dens @click="$router.push({name:'create_slider'})">
                    افزودن اسلایدر جدید
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
                            <th>بنر</th>
                            <th>نوع</th>
                            <th>لینک</th>
                            <th>نمایش</th>
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
                                <div v-if="item.slider_banner" class="d-flex align-center">
                                    <img :src="item.slider_banner" style="width:60px;height:auto" alt="slider banner" class="rounded-sm">
                                </div>
                            </td>
                            <td>
                                <template v-if="item.action === 'no-action'">-</template>
                                <template v-if="item.action === 'internal-link'">لینک داخلی</template>
                                <template v-if="item.action === 'external-link'">لینک خارجی</template>
                            </td>
                            <td>
                                <template v-if="item.link">{{item.link}}</template>
                                <template v-else>-</template>
                            </td>
                            <td>
                                <template v-if="parseInt(item.status) === 1">
                                    <v-btn color="green darken-3" x-small dark @click="changeSliderStatus(item.id , 0)">فعال</v-btn>
                                </template>
                                <template v-else>
                                    <v-btn color="cyan darken-1" x-small darkslider banner @click="changeSliderStatus(item.id , 1)">غیر فعال</v-btn>
                                </template>
                            </td>
                            <td>
                                <v-btn color="danger" dark dens fab small @click="deleteSlider(item.id)">
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
    name:'sliders',
    data: () => ({
        list:[],
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
        last_page:1,
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
            this.$http.post(`sliders/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                    this.fetch_loading = false
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        changeSliderStatus(slider_id, status){
            const data = {
                id:slider_id,
                status
            };
            this.$http.post(`sliders/update` , data)
            .then( () => {
                this.getList();
            });
        },
        deleteSlider(slider_id){
            if (confirm('از حذف اسلایدر اطمینان دارید؟')) {
                this.$http.post(`sliders/remove` , {id:slider_id})
                .then( () => {
                    this.getList();
                });
            }
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
        this.getList()
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('اسلایدر ها')
    }
}
</script>
