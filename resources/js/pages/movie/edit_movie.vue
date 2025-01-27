<template>
    <div>

        <div class="page-head">
            <div class="titr"> ویرایش فیلم
                <v-btn small color="primary" dark :to="{name:'edit_texts' , params:{type:'film' , textable_id:movie_id}}" text>ویرایش متن</v-btn>
            </div>
            <div class="back">
                <router-link :to="{ name : 'movies' }">بازگشت
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

                        <v-col cols="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.english_name"
                                :error-messages="errors.english_name"
                                outlined
                                clearable
                                dense
                                label="عنوان انگلیسی فیلم"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.persian_name"
                                :error-messages="errors.persian_name"
                                outlined
                                clearable
                                dense
                                label="عنوان فارسی فیلم"
                            ></v-text-field>
                        </v-col>

                    </v-row>
                    <v-row>

                        <v-col cols="6" class="pb-0">
                            <v-select
                                label="نوع فیلم"
                                :items="movie_types"
                                v-model="form_data.type"
                                :error-messages="errors.type"
                                item-text="text"
                                item-value="value"
                                outlined readonly
                                clearable
                                dense
                            ></v-select>
                        </v-col>
                        <v-col v-if="form_data.type === 3 || form_data.type === 4 || form_data.type === 5" cols="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.priority"
                                :error-messages="errors.priority"
                                outlined dense type="number"
                                :label="form_data.type === 3 ? 'شماره فصل' : 'شماره قسمت'"
                            ></v-text-field>
                        </v-col>

                    </v-row>
                    <v-row>

                        <v-col v-if="form_data.type === 5" cols="6" class="pb-0">
                            <v-autocomplete
                                dense v-model="movie_list_filter.series_id"
                                outlined :items="series_list"
                                item-value="id"
                                item-text="english_name"
                                label="انتخاب سریال"
                                :search-input.sync="series_list_filter.search_key"
                                @change="getMovieList()"
                            ></v-autocomplete>
                        </v-col>
                        <v-col v-if="form_data.type === 3 || form_data.type === 4 || form_data.type === 5" cols="6" class="pb-0">
                            <v-autocomplete
                                dense v-model="form_data.parent"
                                :error-messages="errors.parent"
                                outlined :items="list"
                                item-value="id"
                                item-text="english_name"
                                :label="form_data.type === 5 ? 'انتخاب فصل' : 'انتخاب سریال'"
                                :search-input.sync="movie_list_filter.search_key"
                            ></v-autocomplete>
                        </v-col>

                    </v-row>
                    <v-row>

                        <v-col cols="12" sm="6" class="pb-0">
                            <v-file-input
                                show-size dense outlined
                                label="پوستر جدید"
                                v-model="form_data.new_poster" persistent-hint
                                :error-messages="errors.poster"
                                hint="فرمت تصویر باید jpg و سایز آن 750*1000 باشد"
                                accept="image/*"
                            ></v-file-input>
                        </v-col>
                        <v-col cols="6" sm="3" class="pb-0">
                            <v-text-field
                                v-model="form_data.extension"
                                :error-messages="errors.extension"
                                outlined
                                clearable
                                dense
                                label="پسوند فایل فیلم"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6" sm="3" class="pb-0">
                            <v-select
                                v-model="form_data.permission_type"
                                outlined dense
                                :error-messages="errors.permission_type"
                                label="نوع دسترسی"
                                :items="[{text:'اشتراکی',value:'paid'},{text:'رایگان',value:'free'},{text:'فصل اول رایگان',value:'first_season_free'},{text:'قسمت اول رایگان',value:'first_episode_free'}]"
                                item-value="value" item-text="text"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" class="pb-0 plain-text">
                            <v-textarea
                                v-model="form_data.description"
                                :error-messages="errors.description"
                                outlined
                                clearable
                                dense rows="8"
                                label="توضیحات فیلم"
                            ></v-textarea>
                        </v-col>

                    </v-row>

                    <v-row>
                        <v-col cols="12" sm="12" md="4" class="pb-0">
                            <v-select
                                label="سطح"
                                :items="levels"
                                v-model="form_data.level"
                                :error-messages="errors.level"
                                outlined dense
                            ></v-select>
                        </v-col>
                        <v-col cols="12" sm="12" md="4" class="pb-0">
                            <v-select
                                label="زیرنویس فارسی ؟"
                                :items="[{text: 'دارد',value: 1},{text: 'ندارد',value: 0}]"
                                v-model="form_data.persian_subtitle"
                                :error-messages="errors.persian_subtitle"
                                item-text="text"
                                item-value="value"
                                outlined clearable dense
                            ></v-select>
                        </v-col>
                        <v-col cols="12" sm="12" md="4" class="pb-0">
                            <v-select
                                label="وضعیت"
                                :items="[{text: 'فعال',value: 1},{text: 'غیر فعال',value: 0}]"
                                v-model="form_data.status"
                                :error-messages="errors.status"
                                item-text="text"
                                item-value="value"
                                outlined clearable dense
                            ></v-select>
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>

            <v-row>

                <v-col cols="12" class="pb-0 text-center">
                    <v-btn
                        color="accent"
                        dense
                        :loading="loading"
                        :disabled="loading"
                        @click="saveMovie()"
                    >
                        ثبت
                    </v-btn>
                </v-col>

            </v-row>


        </v-container>
    </div>
</template>
<script>
export default {
    name:'edit_movie',
    data: () => ({
        list: [],
        series_list: [],
        movie_types: [
            {text: 'فیلم سینمایی',value: 1},
            {text: 'سریال',value: 2},
            {text: 'فصل',value: 3},
            {text: 'قسمتی از سریال',value: 4},
            {text: 'قسمتی از فصل یک سریال',value: 5},
        ],
        form_data:{},
        movie_list_filter:{
            type:2
        },
        series_list_filter:{
            type:2
        },
        errors:{},
        loading: false,
        movie_id:null,
    }),
    watch:{
        movie_list_filter: {
            handler(){
                this.getMovieList();
            },
            deep: true
        },
        'form_data.type': {
            handler(){
                if (this.form_data.type === 5) {
                    this.getSeries()
                    this.movie_list_filter.type = 3;
                }
                if (this.form_data.type === 3 || this.form_data.type === 4) {
                    this.movie_list_filter.type = 2;
                    this.movie_list_filter.series_id = null;
                    this.getMovieList()
                }
            }
        }
    },
    methods:{
        getMovie(){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.get(`movies/movie/${this.movie_id}`)
                .then(res => {
                    this.form_data = res.data.data;
                    const type = parseInt(this.form_data.type);
                    if (type === 5) {
                        this.movie_list_filter.series_id = this.form_data.parent_id;
                        this.getSeries();
                    }
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
        saveMovie(){
            this.loading = true
            const form = new FormData();
            const data = this.form_data;

            form.append('id', data.id);
            data.english_name ? form.append('english_name', data.english_name) : '';
            data.persian_name ? form.append('persian_name', data.persian_name) : '';
            data.type ? form.append('type', data.type) : '';
            form.append('parent', data.parent ? data.parent : 0);
            data.new_poster ? form.append('poster', data.new_poster) : '';
            data.priority ? form.append('priority', data.priority) : '';
            data.extension ? form.append('extension', data.extension) : '';
            data.description ? form.append('description', data.description) : '';
            data.level ? form.append('level', data.level) : '';
            data.permission_type ? form.append('permission_type', data.permission_type) : '';
            form.append('persian_subtitle', data.persian_subtitle ? 1 : 0);
            form.append('status', data.status ? 1 : 0);

            this.$http.post(`movies/update` , form)
                .then(res => {
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.loading = false
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
        getMovieList(){
            this.$http.post(`movies/list?page=1` , this.movie_list_filter)
            .then(res => {
                this.list = res.data.data.data
            })
            .catch( () => {});
        },
        getSeries(){
            this.$http.post(`movies/list?page=1` , this.series_list_filter)
            .then(res => {
                this.series_list = res.data.data.data
            })
            .catch( () => {});
        }
    },
    mounted(){
        this.movie_id = this.$route.params.id;
        this.getMovie();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ویرایش فیلم')
    }
}
</script>
