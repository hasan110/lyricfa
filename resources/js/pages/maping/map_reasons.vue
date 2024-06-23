<template>
    <div>

        <div class="page-head">
            <div class="titr">علت مپ</div>
            <div class="back">
                <router-link :to="{ name : 'maps' }">بازگشت
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

                <v-btn color="success" dark dens @click="add_map_reason_modal = true">
                    افزودن علت مپ
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
                            <th>عنوان انگلیسی</th>
                            <th>عنوان فارسی</th>
                            <th>نوع دلیل</th>
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
                            <td>{{ item.english_title }}</td>
                            <td>{{ item.persian_title }}</td>
                            <td>{{ item.type }}</td>
                            <td>
                                <v-btn color="primary" dark dens @click="edit_form_data = item , edit_modal = true">
                                    ویرایش
                                </v-btn>
                                <v-btn color="danger" small fab dark @click="deleteMapReason(item.id)">
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
            v-model="add_map_reason_modal"
            width="500"
        >
            <v-card>
                <v-card-title>
                    افزودن علت مپ
                </v-card-title>
                <hr>
                <v-container>
                    <v-row class="pt-3">
                        <v-col cols="12" xs="12" sm="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.english_title"
                                outlined clearable
                                :error-messages="errors.english_title"
                                dense label="عنوان انگلیسی"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xs="12" sm="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.persian_title"
                                outlined clearable
                                :error-messages="errors.persian_title"
                                dense label="عنوان فارسی"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-text-field
                                v-model="form_data.type"
                                outlined clearable
                                :error-messages="errors.type"
                                hint="نوع دلیل باید بصورت انگلیسی و بدون فاصله وارد شود."
                                dense label="نوع دلیل"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-textarea
                                v-model="form_data.description"
                                outlined clearable
                                :error-messages="errors.description"
                                dense label="توضیحات"
                            ></v-textarea>
                        </v-col>
                    </v-row>
                </v-container>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="add_map_reason_modal = false">انصراف</v-btn>
                    <v-btn color="success" :disabled="loading" :loading="loading" @click="saveMapReason()">ثبت</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog
            v-model="edit_modal"
            width="500"
        >
            <v-card>
                <v-card-title>
                    ویرایش علت مپ
                </v-card-title>
                <hr>
                <v-container>
                    <v-row class="pt-3">
                        <v-col cols="12" xs="12" sm="6" class="pb-0">
                            <v-text-field
                                v-model="edit_form_data.english_title"
                                outlined clearable
                                :error-messages="edit_errors.english_title"
                                dense label="عنوان انگلیسی"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xs="12" sm="6" class="pb-0">
                            <v-text-field
                                v-model="edit_form_data.persian_title"
                                outlined clearable
                                :error-messages="edit_errors.persian_title"
                                dense label="عنوان فارسی"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-text-field
                                v-model="edit_form_data.type"
                                outlined clearable disabled
                                :error-messages="edit_errors.type"
                                hint="نوع دلیل باید بصورت انگلیسی و بدون فاصله وارد شود."
                                dense label="نوع دلیل"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" xs="12" sm="12" class="pb-0">
                            <v-textarea
                                v-model="edit_form_data.description"
                                outlined clearable
                                :error-messages="edit_errors.description"
                                dense label="توضیحات"
                            ></v-textarea>
                        </v-col>
                    </v-row>
                </v-container>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="danger" text @click="edit_modal = false">انصراف</v-btn>
                    <v-btn color="success" :disabled="loading" :loading="loading" @click="editMapReason()">ثبت</v-btn>
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
        filter:{},
        edit_form_data:{},
        form_data:{},
        errors:{},
        edit_errors:{},
        sort_by_list: [
            {text: 'اول به آخر',value: 'asc'},
            {text: 'آخر به اول',value: 'desc'},
        ],
        current_page:1,
        last_page:0,
        fetch_loading:false,
        loading:false,
        add_map_reason_modal:false,
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
            this.$http.post(`maps/reasons/list?page=${this.current_page}` , this.filter)
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
        saveMapReason(){
            this.loading = true;
            this.errors = {};
            this.$http.post(`maps/reasons/create` , this.form_data)
                .then(res => {
                    this.form_data = {};
                    this.loading = false;
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.add_map_reason_modal = false;
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
        editMapReason(){
            this.loading = true;
            this.edit_errors = {};
            this.$http.post(`maps/reasons/update` , this.edit_form_data)
                .then(res => {
                    this.form_data = {};
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
        deleteMapReason(id){
            if (confirm('آیا از حذف این مورد اطمینان دارید؟')) {
                this.$http.post(`maps/reasons/remove` , {id})
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
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('علت مپ')
    }
}
</script>
<style>
</style>
