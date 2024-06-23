<template>
    <div>

        <div class="page-head">
            <div class="titr">
                لغات مپ شده
                <v-chip :to="{ name : 'words' }" small class="mr-2">لغات</v-chip>
                <v-chip :to="{ name : 'idioms' }" small class="mr-1">اصطلاحات</v-chip>
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
                    <v-btn color="success" outlined dense @click="advanced_search_modal = !advanced_search_modal">جست و جو</v-btn>
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

                <v-btn color="success" dense :to="{name:'create_map'}">
                    ایجاد یک مپ لغت
                </v-btn>
                <v-btn color="primary" dense :to="{name:'map_reasons'}">
                    علت مپ
                </v-btn>
                <v-btn v-if="selected_maps.length > 0" color="error" dense @click="edit_word_map_reason_modal = true">
                    ویرایش گروهی علت مپ
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
                            <th>
                                <v-checkbox dense color="primary" hide-details v-model="select_all"></v-checkbox>
                            </th>
                            <th>#</th>
                            <th>لغت</th>
                            <th>نوع لغت</th>
                            <th>لغت پایه</th>
                            <th>نوع لغت پایه</th>
                            <th>تعداد علت مپ</th>
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
                            <td><v-checkbox dense color="primary" hide-details v-model="selected_maps" :value="item.id"></v-checkbox></td>
                            <td>{{ item.id }}</td>
                            <td>{{ item.word }}</td>
                            <td>{{ item.word_types }}</td>
                            <td>{{ item.ci_base }}</td>
                            <td>{{ item.base_word_types }}</td>
                            <td>
                                <template v-if="item.map_reasons.length">
                                    <v-chip size="x-small" color="deep-purple" dark class="text--white" @click="word_map_reasons = item.map_reasons , show_word_map_reason_modal = true">
                                        {{ item.map_reasons.length }}
                                    </v-chip>
                                </template>
                            </td>
                            <td>
                                <v-btn color="primary" dark dens :to="{name:'edit_map' , params:{id : item.id}}">
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
            v-model="show_word_map_reason_modal"
            width="500"
        >
            <v-card>
                <v-simple-table
                    height="100%"
                    style="height:100%"
                >
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th>نام فارسی</th>
                            <th>نام انگلیسی</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="item in word_map_reasons"
                            :key="item.id"
                        >
                            <td>{{ item.persian_title }}</td>
                            <td>{{ item.english_title }}</td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="show_word_map_reason_modal = false">بستن</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog
            v-model="edit_word_map_reason_modal"
            width="500"
        >
            <v-card>
                <v-card-title>
                    ویرایش علت مپ لغات انتخاب شده
                </v-card-title>
                <hr>
                <v-container>
                    <v-row class="pt-3">
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-autocomplete
                                chips deletable-chips dense multiple small-chips
                                v-model="edit_map_reasons_form_data.map_reasons"
                                outlined :items="map_reasons"
                                item-value="id"
                                :item-text="getMapReasonTitle"
                                :error-messages="errors.map_reasons"
                                label="علت مپ شدن"
                            ></v-autocomplete>
                        </v-col>
                    </v-row>
                </v-container>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="edit_word_map_reason_modal = false">انصراف</v-btn>
                    <v-btn color="success" :disabled="loading" :loading="loading" @click="editWordMapReason()">ویرایش</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog
            v-model="advanced_search_modal"
            width="500"
        >
            <v-card>
                <v-card-title>
                    جست و جو
                </v-card-title>
                <hr>
                <v-container>
                    <v-row class="mb-3">
                        <v-col cols="6">
                            <v-text-field
                                hide-details
                                v-model="filter.word" outlined
                                dense label="لغت" type="text"
                                @keyup.enter="reset"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <v-select
                                hide-details
                                v-model="filter.word_search_model" outlined
                                dense label="نوع جست و جو" :items="searching_models"
                                item-text="text" item-value="value"
                            ></v-select>
                        </v-col>
                        <v-col cols="6">
                            <v-text-field
                                hide-details
                                v-model="filter.word_types" outlined
                                dense label="نوع لغت" type="text"
                                @keyup.enter="reset"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <v-select
                                hide-details
                                v-model="filter.word_types_search_model" outlined
                                dense label="نوع جست و جو" :items="searching_models"
                                item-text="text" item-value="value"
                            ></v-select>
                        </v-col>
                        <v-col cols="6">
                            <v-text-field
                                hide-details
                                v-model="filter.base_word" outlined
                                dense label="لغت پایه" type="text"
                                @keyup.enter="reset"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <v-select
                                hide-details
                                v-model="filter.base_word_search_model" outlined
                                dense label="نوع جست و جو" :items="searching_models"
                                item-text="text" item-value="value"
                            ></v-select>
                        </v-col>
                        <v-col cols="6">
                            <v-text-field
                                hide-details
                                v-model="filter.base_word_types" outlined
                                dense label="نوع لغت پایه" type="text"
                                @keyup.enter="reset"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <v-select
                                hide-details
                                v-model="filter.base_word_types_search_model" outlined
                                dense label="نوع جست و جو" :items="searching_models"
                                item-text="text" item-value="value"
                            ></v-select>
                        </v-col>
                    </v-row>

                </v-container>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="advanced_search_modal = false">انصراف</v-btn>
                    <v-btn color="primary" small fab text @click="filter = {}">
                        <v-icon >mdi-refresh</v-icon>
                    </v-btn>
                    <v-btn color="success" :disabled="loading" :loading="loading" @click="advanced_search_modal = false , reset()">اعمال</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
export default {
    name:'maps',
    data: () => ({
        list:[],
        selected_maps:[],
        word_map_reasons:[],
        map_reasons:[],
        form_data:{},
        edit_map_reasons_form_data:{},
        filter:{},
        rules_filter:{},
        errors:{},
        sort_by_list: [
            {text: 'اول به آخر',value: 'asc'},
            {text: 'آخر به اول',value: 'desc'},
            {text: 'آخرین بروزرسانی',value: 'update'},
        ],
        searching_models: [
            {text: '%....',value: 'first_like'},
            {text: '....%',value: 'last_like'},
            {text: '%...%',value: 'like'},
        ],
        current_page:1,
        last_page:0,
        fetch_loading:false,
        loading:false,
        advanced_search_modal:false,
        edit_word_map_reason_modal:false,
        show_word_map_reason_modal:false,
        select_all:false,
    }),
    watch:{
        current_page(){
            this.getList();
        },
        select_all:function (newStatus , oldStatus) {
            let vm = this;
            vm.selected_maps = [];
            if (newStatus) {
                vm.list.forEach(function (item){
                    vm.selected_maps.push(item.id);
                })
            }
        }
    },
    methods:{
        getMapReasonTitle(item){
            return `${item.persian_title} - ${item.english_title}`;
        },
        getList(){
            this.select_all = false
            this.fetch_loading = true
            this.$http.post(`maps/list?page=${this.current_page}` , this.filter)
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
        editWordMapReason(){
            this.loading = true;
            this.errors = {};
            this.edit_map_reasons_form_data.maps = this.selected_maps;
            this.$http.post(`maps/reasons/group-edit` , this.edit_map_reasons_form_data)
                .then(res => {
                    this.edit_map_reasons_form_data = {};
                    this.loading = false;
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.edit_word_map_reason_modal = false;
                    this.edit_map_reasons_form_data = {};
                    this.selected_maps = [];
                    this.getList();
                })
                .catch( err => {
                    this.loading = false;
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
        getMapReasonsList(){
            this.errors = {};
            this.$http.post(`maps/reasons/list?page=1` , this.rules_filter)
                .then(res => {
                    this.map_reasons = res.data.data.data
                })
                .catch( err => {
                    console.log(err)
                });
        },
    },
    mounted(){
        this.filter.sort_by = 'asc';
        this.getList();
        this.getMapReasonsList();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('مپ لغات')
    }
}
</script>
<style>
</style>
