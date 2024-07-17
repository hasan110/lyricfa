<template>
    <div>

        <div class="page-head">
            <div class="titr">ویرایش آلبوم</div>
            <div class="back">
                <router-link :to="{ name : 'albums' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <v-container>

            <v-row>

                <v-col cols="4" class="pb-0">
                    <v-text-field
                        v-model="form_data.english_name"
                        outlined
                        clearable
                        dense
                        label="عنوان انگلیسی آلبوم"
                    ></v-text-field>
                </v-col>
                <v-col cols="4" class="pb-0">
                    <v-text-field
                        v-model="form_data.persian_name"
                        outlined
                        clearable
                        dense
                        label="عنوان فارسی آلبوم"
                    ></v-text-field>
                </v-col>

            </v-row>

            <div class="pb-5">
                <v-row>
                    <v-col cols="6" class="pb-0">
                        <v-autocomplete
                            chips deletable-chips multiple small-chips
                            v-model="form_data.singers"
                            outlined :items="singers"
                            item-value="id" dense
                            item-text="english_name"
                            label="انتخاب خواننده (ها)"
                            :search-input.sync="singers_filter.search_key"
                        ></v-autocomplete>
                    </v-col>
                </v-row>
            </div>

            <hr>
            <br>

            <v-row>
                <v-col cols="4" class="pb-0">
                    <v-file-input
                        show-size
                        dense
                        outlined
                        label="انتخاب تصویر جدید برای آلبوم" persistent-hint
                        hint="فرمت تصویر باید jpg و سایز آن 300*300 باشد"
                        v-model="form_data.image"
                        accept="image/*"
                    ></v-file-input>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12" class="pb-0 text-center">
                    <v-btn
                        color="accent"
                        dense
                        :loading="loading"
                        :disabled="loading"
                        @click="saveAlbum()"
                    >
                        ذخیره
                    </v-btn>
                </v-col>
            </v-row>

        </v-container>
    </div>
</template>
<script>
export default {
    name:'edit_album',
    data: () => ({
        form_data:{
            singers: [],
        },
        singers: [],
        singers_filter:{
            no_page:true
        },
        errors:{},
        loading: false,
        banner_image: null,
        album_id:null,
    }),
    watch:{
        singers_filter: {
            handler(){
                this.getSingers();
            },
            deep: true
        }
    },
    methods:{
        getSingers(){
            this.singers_filter.singer_ids = this.form_data.singers;
            this.$http.post(`singers/list?page=1` , this.singers_filter)
                .then(res => {
                    this.singers = res.data.data
                })
                .catch( () => {
                });
        },
        getAlbum(){
            this.$store.commit('SHOW_APP_LOADING' , 1);
            this.$http.post(`albums/single` , {id : this.album_id})
                .then(res => {
                    this.form_data = res.data.data
                    this.getSingers()
                    this.$store.commit('SHOW_APP_LOADING' , 0);
                })
                .catch( err => {
                    const e = err.response.data
                    this.$fire({
                        title: "خطا",
                        text: e.message,
                        type: "error",
                        timer: 5000
                    })
                    this.$store.commit('SHOW_APP_LOADING' , 0);
                });
        },
        saveAlbum(){
            this.loading = true
            const d = new FormData();
            const x = this.form_data;

            d.append('id', this.album_id)
            x.english_name ? d.append('album_name_english', x.english_name) : '';
            x.persian_name ? d.append('album_name_persian', x.persian_name) : '';
            x.image ? d.append('image_url', x.image) : '';

            if(x.singers.length){
                d.append('singers', x.singers);
            }

            this.$http.post(`albums/update` , d)
                .then(res => {

                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })

                    this.$router.push({name:'albums'})

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
        },
    },
    mounted(){
        this.album_id = this.$route.params.id;
        this.getAlbum();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ویرایش آلبوم')
    }
}
</script>
<style>
</style>
