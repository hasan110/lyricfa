<template>
    <div>

        <div class="page-head">
            <div class="titr">قوانین گرامر</div>
            <div class="back">
                <router-link :to="{ name : 'grammers' }">بازگشت
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

                <v-btn color="success" dark dens @click="add_grammer_rule_modal = true">
                    افزودن قانون
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
                            <th>نوع اعمال</th>
                            <th>نوع جست و جو</th>
                            <th>اطلاعات</th>
                            <th>لغات</th>
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
                                <template v-if="item.apply_method === 1">
                                    اعمال تکی
                                </template>
                                <template v-else-if="item.apply_method === 2">
                                    اعمال گروهی
                                </template>
                            </td>
                            <td>
                                <template v-if="parseInt(item.apply_method) === 1">
                                    <template v-if="item.proccess_method === 1">
                                        جستجو در مپ ها
                                    </template>
                                    <template v-else-if="item.proccess_method === 2">
                                        جستجو در متن
                                    </template>
                                    <template v-else-if="item.proccess_method === 3">
                                        جستجو در نوع لغت
                                    </template>
                                </template>
                                <template v-else>---</template>
                            </td>
                            <td>
                                <template v-if="parseInt(item.apply_method) === 1">
                                    <template v-if="item.proccess_method === 1">
                                        علت مپ:
                                        {{item.map_reason.english_title}} - {{item.map_reason.persian_title}}
                                    </template>
                                    <template v-else>
                                        {{ item.type }}
                                    </template>
                                </template>
                                <template v-else>{{ item.type }}</template>
                            </td>
                            <td>
                                <template v-if="item.proccess_method === 2">
                                    {{ item.words && item.words.length > 20 ? item.words.slice(0,20) + ' ...' : item.words }}
                                </template>
                            </td>
                            <td>
                                <v-btn color="primary" dark dens @click="edit_form_data = item , edit_modal = true">
                                    ویرایش
                                </v-btn>
                                <v-btn color="danger" small fab dark @click="deleteGrammerRule(item.id)">
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

        <v-dialog
            v-model="add_grammer_rule_modal"
            width="500"
        >
            <v-card>
                <v-card-title>
                    افزودن قانون گرامر
                </v-card-title>
                <hr>
                <v-container>
                    <v-row class="pt-3">
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-select
                                v-model="form_data.apply_method"
                                outlined clearable :items="rule_types_items"
                                item-value="value" item-text="title"
                                :error-messages="errors.apply_method"
                                dense label="نوع اعمال قانون"
                            ></v-select>
                        </v-col>
                    </v-row>
                    <v-row v-if="form_data.apply_method === 1">
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <div>ابتدا نوع جستجو را برای قانون گرامر تنظیم کنید:</div>
                            <v-radio-group v-model="form_data.proccess_method" row>
                                <v-radio label="جستجو در مپ ها" :value="1"></v-radio>
                                <v-radio label="جستجو در متن" :value="2"></v-radio>
                                <v-radio label="جستجو در نوع لغت" :value="3"></v-radio>
                            </v-radio-group>
                        </v-col>
                        <v-col v-if="form_data.proccess_method === 1" cols="12" xs="12" sm="12" class="pb-0">
                            <v-autocomplete
                                dense
                                v-model="form_data.map_reason_id"
                                outlined :items="map_reasons"
                                item-value="id"
                                item-text="english_title"
                                :error-messages="errors.map_reason_id"
                                label="انتخاب علت مپ"
                            ></v-autocomplete>
                        </v-col>
                        <v-col v-if="form_data.proccess_method === 2" cols="12" xs="12" sm="12" class="pb-0">
                            <v-select
                                v-model="form_data.type"
                                outlined clearable :items="search_in_text_items"
                                :error-messages="errors.type"
                                dense label="نوع قانون"
                            ></v-select>
                        </v-col>
                        <v-col v-if="form_data.proccess_method === 2" cols="12" xs="12" sm="12" class="pb-0">
                            <v-textarea
                                v-model="form_data.words"
                                outlined
                                :error-messages="errors.words"
                                dense label="لغات"
                            ></v-textarea>
                        </v-col>
                        <v-col v-if="form_data.proccess_method === 3" cols="12" xs="12" sm="12" class="pb-0">
                            <v-autocomplete
                                dense
                                v-model="form_data.word_type"
                                outlined :items="word_types"
                                :error-messages="errors.word_type"
                                label="انتخاب نوع لغت"
                            ></v-autocomplete>
                        </v-col>
                    </v-row>
                    <v-row v-if="form_data.apply_method === 2">
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-autocomplete
                                dense multiple chips
                                v-model="form_data.sub_rules"
                                outlined :items="rules_list"
                                item-value="id"
                                :item-text="getRuleTitle"
                                :error-messages="errors.sub_rules"
                                label="انتخاب زیرمجموعه ها"
                            ></v-autocomplete>
                        </v-col>
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-text-field
                                v-model="form_data.type"
                                outlined clearable
                                :error-messages="errors.type"
                                dense label="عنوان"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-container>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="add_grammer_rule_modal = false">
                        انصراف
                    </v-btn>
                    <v-btn color="success" :disabled="loading" :loading="loading" @click="saveGrammerRule()">
                        ثبت
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog
            v-model="edit_modal"
            width="500"
        >
            <v-card>
                <v-card-title>
                    ویرایش قانون گرامر
                </v-card-title>
                <hr>
                <v-container>
                    <v-row class="pt-3">
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-select
                                v-model="edit_form_data.apply_method"
                                outlined clearable :items="rule_types_items"
                                item-value="value" item-text="title"
                                :error-messages="edit_errors.apply_method"
                                dense label="نوع اعمال قانون" disabled
                            ></v-select>
                        </v-col>
                    </v-row>
                    <v-row v-if="parseInt(edit_form_data.apply_method) === 1">
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <div> نوع جستجو:</div>
                            <v-radio-group disabled v-model="edit_form_data.proccess_method" row>
                                <v-radio label="جستجو در مپ ها" :value="1"></v-radio>
                                <v-radio label="جستجو در متن" :value="2"></v-radio>
                                <v-radio label="جستجو در نوع لغت" :value="3"></v-radio>
                            </v-radio-group>
                        </v-col>
                        <v-col v-if="edit_form_data.proccess_method === 1" cols="12" xs="12" sm="12" class="pb-0">
                            <v-autocomplete
                                dense
                                v-model="edit_form_data.map_reason_id"
                                outlined :items="map_reasons"
                                item-value="id"
                                item-text="english_title"
                                :error-messages="edit_errors.map_reason_id"
                                label="ویرایش علت مپ"
                            ></v-autocomplete>
                        </v-col>
                        <v-col v-if="edit_form_data.proccess_method === 2" cols="12" xs="12" sm="12" class="pb-0">
                            <v-select
                                v-model="edit_form_data.type"
                                outlined clearable :items="search_in_text_items"
                                :error-messages="edit_errors.type"
                                dense label="نوع قانون"
                            ></v-select>
                        </v-col>
                        <v-col v-if="edit_form_data.proccess_method === 2" cols="12" xs="12" sm="12" class="pb-0">
                            <v-textarea
                                v-model="edit_form_data.words"
                                outlined
                                :error-messages="edit_errors.words"
                                dense label="لغات"
                            ></v-textarea>
                        </v-col>
                        <v-col v-if="edit_form_data.proccess_method === 3" cols="12" xs="12" sm="12" class="pb-0">
                            <v-autocomplete
                                dense
                                v-model="edit_form_data.type"
                                outlined :items="word_types"
                                :error-messages="edit_errors.type"
                                label="ویرایش نوع لغت"
                            ></v-autocomplete>
                        </v-col>
                    </v-row>
                    <v-row v-if="parseInt(edit_form_data.apply_method) === 2">
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-autocomplete
                                dense multiple chips
                                v-model="edit_form_data.sub_rules"
                                outlined :items="rules_list"
                                item-value="id"
                                :item-text="getRuleTitle"
                                :error-messages="edit_errors.sub_rules"
                                label="انتخاب زیرمجموعه ها"
                            ></v-autocomplete>
                        </v-col>
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-text-field
                                v-model="edit_form_data.type"
                                outlined clearable
                                :error-messages="edit_errors.type"
                                dense label="عنوان"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-container>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="edit_modal = false">
                        انصراف
                    </v-btn>
                    <v-btn color="success" :disabled="loading" :loading="loading" @click="editGrammerRule()">
                        ویرایش
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
export default {
    name:'words',
    data: () => ({
        list:[],
        rules_list:[],
        filter:{},
        get_rules_filter:{},
        edit_form_data:{},
        form_data:{
            proccess_method:1
        },
        errors:{},
        edit_errors:{},
        sort_by_list: [
            {text: 'اول به آخر',value: 'asc'},
            {text: 'آخر به اول',value: 'desc'},
        ],
        search_in_text_items: [
            'search_exact',
            'search_order',
            'search_disorder',
            'search_part_of_word',
            'search_first_of_word',
            'search_end_of_word',
            'search_multiple'
        ],
        rule_types_items: [
            {
                value:1,
                title:'تکی',
            },
            {
                value:2,
                title:'گروهی',
            }
        ],
        current_page:1,
        last_page:0,
        fetch_loading:false,
        loading:false,
        add_grammer_rule_modal:false,
        edit_modal:false,
    }),
    watch:{
        current_page(){
            this.getList();
        }
    },
    methods:{
        getList(){
            this.fetch_loading = true
            this.$http.post(`grammers/rules/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                    this.fetch_loading = false
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        getRules(){
            this.$http.post(`grammers/rules/list?page=1&limit=1000&apply_method=1` , this.get_rules_filter)
                .then(res => {
                    this.rules_list = res.data.data
                })
                .catch( () => {});
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
        getRuleTitle(item){
            if (item.proccess_method === 1) {
                return item.id + " - جستجو در مپ - علت مپ: " + item.map_reason.english_title;
            } else if (item.proccess_method === 2) {
                return item.id + " - جستجو در متن - " + (item.words && item.words.length > 20 ? item.words.slice(0,20) + ' ...' : item.words) + ' - ' + item.type;
            } else if (item.proccess_method === 3) {
                return item.id + " - جستجو در نوع لغت - " + item.type;
            }
        },
        saveGrammerRule(){
            this.loading = true;
            this.$http.post(`grammers/rules/create` , this.form_data)
                .then(res => {
                    this.form_data = {};
                    this.loading = false;
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.add_grammer_rule_modal = false;
                    this.reset()
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
        editGrammerRule(){
            this.loading = true;
            this.$http.post(`grammers/rules/update` , this.edit_form_data)
                .then(res => {
                    this.edit_form_data = {};
                    this.loading = false;
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.edit_modal = false;
                    this.reset()
                })
                .catch( err => {
                    this.loading = false;
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
        },
        getMapReasonsList(){
            this.$http.post(`maps/reasons/list?page=1` , this.map_reasons_filter)
                .then(res => {
                    this.map_reasons = res.data.data.data
                })
                .catch( err => {
                    console.log(err)
                });
        },
        getWordTypes(){
            this.$http.get(`words/types`)
                .then(res => {
                    this.word_types = res.data.data
                })
                .catch( err => {
                    console.log(err)
                });
        },
        deleteGrammerRule(id){
            if (confirm('آیا از حذف این مورد اطمینان دارید؟')) {
                this.$http.post(`grammers/rules/remove` , {id})
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
        this.filter.sort_by = 'desc';
        this.getList();
        this.getRules();
        this.getMapReasonsList();
        this.getWordTypes();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('قوانین گرامر')
    }
}
</script>
<style>
</style>
