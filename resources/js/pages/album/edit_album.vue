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
                        v-model="form_data.album_name_english"
                        outlined
                        clearable
                        dense
                        label="عنوان انگلیسی آلبوم"
                    ></v-text-field>
                </v-col>
                <v-col cols="4" class="pb-0">
                    <v-text-field
                        v-model="form_data.album_name_persian"
                        outlined
                        clearable
                        dense
                        label="عنوان فارسی آلبوم"
                    ></v-text-field>
                </v-col>
                <v-col cols="3" class="pb-0">
                    <v-text-field
                        v-model="singers_count"
                        append-outer-icon="mdi-minus"
                        @click:append-outer="toggleSingers(false)"
                        prepend-icon="mdi-plus"
                        @click:prepend="toggleSingers(true)"
                        outlined
                        dense
                        label="تعداد خواننده ها"
                        type="number"
                        min="1"
                        :max="max_number_of_singers"
                        readonly
                    ></v-text-field>
                </v-col>

            </v-row>

            <div class="pb-5">
                <div v-for="(item , key) in singers_count" :key="key">

                    <v-row>
                        <v-col cols="4" class="pb-0">
                            <v-text-field
                                v-model="form_data.singers[key]"
                                outlined
                                dense
                                :label="'شناسه خواننده ' + (key+1)"
                            ></v-text-field>
                        </v-col>
                    </v-row>

                </div>
            </div>

            <hr>
            <br>

            <v-row>

                <v-col cols="4" class="pb-0">
                    <v-file-input
                        show-size
                        dense
                        outlined
                        label="تصویر" persistent-hint
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
    name:'create_album',
    data: () => ({
        form_data:{
            singers: [],
        },
        singers_count: 1,
        max_number_of_singers: 5,
        errors:{},
        loading: false,
        banner_image: null,
        album_id:null,
    }),
    watch:{
        singers_count(){
            this.singers_count = parseInt(this.singers_count);
        }
    },
    methods:{
        toggleSingers(state){
            if(state){
                if(this.singers_count >= 5){
                    return;
                }else{
                    this.singers_count++;
                }
            }else{
                if(this.singers_count <= 1){
                    return;
                }else{
                    this.singers_count--;
                }
            }
        },
        getAlbum(){
            this.$store.commit('SHOW_APP_LOADING' , 1);
            this.$http.post(`albums/single` , {id : this.album_id})
                .then(res => {
                    this.form_data = res.data.data
                    this.singers_count = this.form_data.singers.length
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
            x.album_name_english ? d.append('album_name_english', x.album_name_english) : '';
            x.album_name_persian ? d.append('album_name_persian', x.album_name_persian) : '';
            x.image ? d.append('image_url', x.image) : '';

            if(x.singers.length){

                x.singers[0] ? d.append('id_first_singer', x.singers[0]) : '';
                x.singers[1] ? d.append('id_second_singer', x.singers[1]) : '';
                x.singers[2] ? d.append('id_third_singer', x.singers[2]) : '';
                x.singers[3] ? d.append('id_fourth_singer', x.singers[3]) : '';

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
                    // if(e.errors){ this.errors = e.errors }
                    // else if(e.message){

                    this.$fire({
                        title: "خطا",
                        text: e.message,
                        type: "error",
                        timer: 5000
                    })

                    // }
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
