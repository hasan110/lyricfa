<template>
    <div>

        <div class="page-head">
            <div class="titr">قوانین جایگزینی عبارات</div>
            <div class="back">
                <router-link :to="{ name : 'words' }">بازگشت
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

                <v-btn color="success" dark dens @click="create_modal = true">
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
                            <th>عبارت مورد جست و جو</th>
                            <th>عبارت جایگزین</th>
                            <th>اعمال روی</th>
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
                                {{ item.find_phrase }}
                                <template v-if="parseInt(item.last_character)">
                                    ⛔
                                </template>
                                <template v-if="parseInt(item.similar)">
                                    ≊
                                </template>
                            </td>
                            <td>{{ item.replace_phrase }}</td>
                            <td>
                                <template v-if="item.apply_on === 'persian_text'">متون فارسی</template>
                                <template v-else-if="item.apply_on === 'english_text'">متون انگلیسی</template>
                                <template v-else>همه</template>
                            </td>
                            <td>
                                <v-btn color="danger" dark dens @click="deleteRule(item.id)">
                                    حذف
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

        <v-dialog v-model="create_modal" width="500">
            <v-card>
                <v-card-title>
                    افزودن قانون جایگذاری
                </v-card-title>
                <hr>
                <v-container>
                    <v-row class="pt-3">
                        <v-col cols="12" xs="12" sm="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.find_phrase"
                                outlined :error-messages="errors.find_phrase"
                                dense label="عبارت مورد جست و جو"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xs="12" sm="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.replace_phrase"
                                outlined :error-messages="errors.replace_phrase"
                                dense label="عبارت جایگزین"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-select
                                :items="apply_on_list"
                                v-model="form_data.apply_on"
                                item-text="text" item-value="value"
                                outlined dense label="اعمال روی"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" xs="12" sm="6" class="pb-0">
                            <v-checkbox
                                v-model="form_data.last_character"
                                dense
                                label="آخر جمله؟"
                            ></v-checkbox>
                        </v-col>
                        <v-col cols="12" xs="12" sm="6" class="pb-0">
                            <v-checkbox
                                v-model="form_data.similar"
                                dense
                                label="کاملا مشابه؟"
                            ></v-checkbox>
                        </v-col>
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-autocomplete
                                chips deletable-chips dense multiple small-chips
                                v-model="form_data.rules"
                                outlined :items="replace_rules"
                                item-value="id"
                                :item-text="getRuleTitle"
                                :error-messages="errors.rules"
                                label="اعمال بعد از" persistent-hint
                                hint="لیست مواردی که میخواهید این قانون بعد از آن ها اعمال شود را انتخاب نمایید"
                                :search-input.sync="rules_filter.search_key"
                            ></v-autocomplete>
                        </v-col>
                    </v-row>
                </v-container>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="create_modal = false">انصراف</v-btn>
                    <v-btn color="success" :disabled="create_loading" :loading="create_loading" @click="saveReplaceRule()">ثبت</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>

</template>
<script>
export default {
    name:'replace_rules',
    data: () => ({
        list:[],
        replace_rules:[],
        form_data:{},
        filter:{},
        rules_filter:{},
        errors:{},
        sort_by_list: [
            {text: 'اول به آخر',value: 'asc'},
            {text: 'آخر به اول',value: 'desc'},
        ],
        apply_on_list: [
            {text: 'متون فارسی',value: 'persian_text'},
            {text: 'متون انگلیسی',value: 'english_text'},
            {text: 'همه',value: 'all'},
        ],
        current_page:1,
        last_page:0,
        create_modal:false,
        create_loading:false,
        edit_modal:false,
        edit_loading:false,
        fetch_loading:false,
    }),
    watch:{
        current_page(){
            this.getList();
        },
        rules_filter: {
            handler(){
                this.getReplaceRulesList();
            },
            deep: true
        }
    },
    methods:{
        getRuleTitle(item){
            return `${item.find_phrase} -> ${item.replace_phrase}`;
        },
        getReplaceRulesList(){
            this.$http.post(`replace_rule/list?page=1` , this.rules_filter)
                .then(res => {
                    this.replace_rules = res.data.data.data;
                })
                .catch( () => {
                });
        },
        getList(add_to_list = false){
            this.fetch_loading = true
            this.$http.post(`replace_rule/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    const response = res.data.data;
                    this.list = response.data;
                    this.last_page = response.last_page;
                    this.fetch_loading = false

                    if (add_to_list) {
                        this.replace_rules = response.data;
                    }
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        deleteRule(rule_id){
            if (confirm('آیا از حذف این مورد اطمینان دارید؟')) {
                this.$http.post(`replace_rule/remove` , {id:rule_id})
                    .then( () => {
                        this.reset(true)
                    })
                    .catch( err => {
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
            }
        },
        saveReplaceRule(){
            this.create_loading = true;
            this.$http.post(`replace_rule/create` , this.form_data)
                .then(res => {
                    this.form_data = {};
                    this.create_loading = false;
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.create_modal = false;
                    this.reset(true);
                })
                .catch( err => {
                    this.create_loading = false;
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
        reset(add_to_list = false){
            this.current_page = 1
            this.list = []
            this.getList(add_to_list)
        }
    },
    mounted(){
        this.filter.sort_by = 'word_asc';
        this.getList(true);
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('قوانین جایگذاری عبارات')
    }
}
</script>
<style>
</style>
