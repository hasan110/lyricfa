<template>
    <div>
        <div class="page-head">
            <div class="titr">ویرایش دسته بندی</div>
            <div class="back">
                <router-link :to="{ name : 'categories' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>
        <v-container>
            <v-row class="justify-center">
                <v-col md="8" sm="12">
                    <v-row>
                        <v-col cols="12" md="5" sm="12" class="pb-0">
                            <v-text-field
                                v-model="form_data.title"
                                :error-messages="errors.title"
                                outlined clearable dense
                                label="عنوان"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="5" sm="8" class="pb-0">
                            <v-text-field
                                v-model="form_data.subtitle"
                                :error-messages="errors.subtitle"
                                outlined clearable dense
                                label="عنوان فرعی"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="2" sm="4" class="pb-0">
                            <v-select
                                label="نوع"
                                :items="[{text:'دسته بندی', value:'category'},{text:'برچسب', value:'label'}]"
                                v-model="form_data.mode"
                                :error-messages="errors.mode"
                                item-text="text"
                                item-value="value"
                                outlined dense disabled
                            ></v-select>
                        </v-col>
                    </v-row>
                    <template v-if="form_data.mode === 'label'">
                        <v-row>
                            <v-col cols="12" sm="6" class="pb-0">
                                <v-text-field
                                    v-model="form_data.color"
                                    outlined clearable dense
                                    :error-messages="errors.color"
                                    label="رنگ" type="color"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" class="pb-0">
                                <v-textarea
                                    v-model="form_data.description"
                                    :error-messages="errors.description"
                                    outlined
                                    clearable
                                    dense rows="4"
                                    label="توضیحات"
                                ></v-textarea>
                            </v-col>
                        </v-row>
                    </template>
                    <template v-if="form_data.mode === 'category'">
                        <v-row>
                            <v-col cols="6" class="pb-0">
                                <v-select
                                    label="دسته بندی برای"
                                    :items="[
                                    {text:'گرامر',value:'grammers'},
                                    {text:'موزیک',value:'musics'},
                                    {text:'فیلم',value:'films'},
                                    {text:'معنی لغت و عبارت',value:'word_definitions'},
                                ]"
                                    v-model="form_data.belongs_to"
                                    :error-messages="errors.belongs_to"
                                    item-text="text"
                                    item-value="value"
                                    outlined clearable dense disabled
                                ></v-select>
                            </v-col>
                            <v-col cols="6" class="pb-0">
                                <v-text-field
                                    v-model="form_data.priority"
                                    :error-messages="errors.priority"
                                    outlined dense type="number"
                                    label="شماره اولویت"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" class="pb-0">
                                <v-autocomplete
                                    dense v-model="form_data.parent_id"
                                    outlined :items="categories_list"
                                    item-value="id"
                                    item-text="title"
                                    label="انتخاب بالا دسته جدید"
                                    :search-input.sync="categories_list_filter.search_key"
                                    @change="getCategoryList()"
                                ></v-autocomplete>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="3" class="pb-0">
                                <v-checkbox
                                    v-model="form_data.is_parent"
                                    :value="1"
                                    label="والد"
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="3" class="pb-0">
                                <v-checkbox
                                    v-model="form_data.need_level"
                                    :value="1"
                                    label="سطح بندی"
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="3" class="pb-0">
                                <v-checkbox
                                    v-model="form_data.is_public"
                                    :value="1"
                                    label="عمومی"
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="3" class="pb-0">
                                <v-checkbox
                                    v-model="form_data.status"
                                    :value="1"
                                    label="فعال"
                                ></v-checkbox>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" sm="6" class="pb-0">
                                <v-file-input
                                    show-size dense outlined
                                    label="انتخاب پوستر جدید"
                                    v-model="form_data.poster" persistent-hint
                                    hint="فرمت تصویر باید jpg یا png و سایز آن 200*200 باشد"
                                    accept="image/*"
                                    :error-messages="errors.poster"
                                ></v-file-input>
                            </v-col>
                            <v-col cols="6" sm="3" class="pb-0">
                                <v-text-field
                                    v-model="form_data.color"
                                    outlined clearable dense
                                    :error-messages="errors.color"
                                    label="رنگ" type="color"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="3" class="pb-0">
                                <v-select
                                    v-model="form_data.permission_type"
                                    outlined dense
                                    :error-messages="errors.permission_type"
                                    label="نوع دسترسی"
                                    :items="[{text:'اشتراکی',value:'paid'},{text:'رایگان',value:'free'}]"
                                    item-value="value" item-text="text"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" class="pb-0 plain-text">
                                <v-textarea
                                    v-model="form_data.description"
                                    :error-messages="errors.description"
                                    outlined
                                    clearable
                                    dense rows="4"
                                    label="توضیحات"
                                ></v-textarea>
                            </v-col>
                        </v-row>
                    </template>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" class="pb-0 text-center">
                    <v-btn
                        color="accent"
                        dense
                        :loading="loading"
                        :disabled="loading"
                        @click="save()"
                    >
                        ویرایش
                    </v-btn>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>
<script>
export default {
    name:'create_movie',
    data: () => ({
        category_id: null,
        list: [],
        categories_list: [],
        form_data:{},
        movie_list_filter:{
            type:2
        },
        categories_list_filter:{
            mode:"category"
        },
        errors:{},
        loading: false,
    }),
    watch:{
        movie_list_filter: {
            handler(){
                this.getMovieList();
            },
            deep: true
        }
    },
    methods:{
        save(){
            this.loading = true
            this.errors = {}
            const form = new FormData();
            const data = this.form_data;

            data.id ? form.append('id', data.id) : '';
            data.title ? form.append('title', data.title) : '';
            data.subtitle ? form.append('subtitle', data.subtitle) : '';
            form.append('parent_id', data.parent_id ? data.parent_id : 0);
            data.poster ? form.append('poster', data.poster) : '';
            data.color ? form.append('color', data.color) : '';
            data.priority ? form.append('priority', data.priority) : '';
            data.description ? form.append('description', data.description) : '';
            data.permission_type ? form.append('permission_type', data.permission_type) : '';
            form.append('is_parent', data.is_parent ? 1 : 0);
            form.append('is_public', data.is_public ? 1 : 0);
            form.append('need_level', data.need_level ? 1 : 0);
            form.append('status', data.status ? 1 : 0);

            this.$http.post(`categories/update` , form)
                .then(res => {
                    this.form_data = {};

                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })

                    this.$router.push({name:'categories'})

                })
                .catch( err => {
                    this.loading = false
                    const e = err.response.data
                    if(e.errors){ this.errors = e.errors }
                    else {
                        this.$fire({
                            title: "خطا",
                            text: e.message,
                            type: "error",
                            timer: 5000
                        })
                    }
                });
        },
        getCategory(){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`categories/single` , {id:this.category_id})
                .then(res => {
                    this.form_data = res.data.data;
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                })
                .catch( err => {
                    this.loading = false
                    const e = err.response.data
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                    this.$fire({
                        title: "خطا",
                        text: e.message,
                        type: "error",
                        timer: 5000
                    })
                });
        },
        getCategoryList(){
            this.categories_list_filter.except = this.category_id;
            this.$http.post(`categories/list?page=1` , this.categories_list_filter)
                .then(res => {
                    this.categories_list = res.data.data.data
                })
                .catch( () => {});
        },
    },
    mounted(){
        this.category_id = this.$route.params.id;
        this.getCategory();
        this.getCategoryList();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ویرایش دسته بندی')
    }
}
</script>
<style>
</style>
