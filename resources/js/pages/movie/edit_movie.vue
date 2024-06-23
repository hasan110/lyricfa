<template>
    <div>

        <div class="page-head">
            <div class="titr"> ویرایش فیلم</div>
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
                                outlined
                                clearable
                                dense
                                label="عنوان انگلیسی فیلم"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.persian_name"
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
                                item-text="text"
                                item-value="value"
                                outlined
                                clearable
                                dense
                            ></v-select>
                        </v-col>
                        <v-col cols="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.parent"
                                outlined
                                clearable
                                dense
                                label="آیدی فیلم مرتبط"
                            ></v-text-field>
                        </v-col>

                    </v-row>
                    <v-row>

                        <v-col cols="6" class="pb-0">
                            <v-file-input
                                show-size
                                dense
                                outlined
                                label="پوستر فیلم"
                                v-model="form_data.new_poster" persistent-hint
                                accept="image/*"
                            ></v-file-input>
                        </v-col>
                        <v-col cols="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.extension"
                                outlined
                                clearable
                                dense
                                label="پسوند فایل فیلم"
                            ></v-text-field>
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
        movie_types: [
            {text: 'فیلم سینمایی',value: 1},
            {text: 'سریال',value: 2},
            {text: 'فصل',value: 3},
            {text: 'قسمت',value: 4},
        ],
        form_data:{},
        errors:{},
        loading: false,
        movie_id:null,
    }),
    methods:{
        getMovie(){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.get(`movies/movie/${this.movie_id}`)
                .then(res => {
                    this.form_data = res.data.data
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
            data.extension ? form.append('extension', data.extension) : '';

            this.$http.post(`movies/update` , form)
                .then(res => {
                    this.form_data = {};

                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })

                    this.$router.push({name:'movies'})

                })
                .catch( err => {
                    this.loading = false
                    const e = err.response.data

                    this.$fire({
                        title: "خطا",
                        text: e.message,
                        type: "error",
                        timer: 5000
                    })
                });
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
