<template>
    <div>

        <div class="page-head">
            <div class="titr">ویرایش موزیک</div>
            <div class="back">
                <router-link :to="{ name : 'musics' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <v-container>

            <v-row>
                <v-col cols="12" sm="6" class="pb-0">
                    <v-text-field
                        v-model="form_data.name"
                        outlined
                        clearable
                        dense
                        label="عنوان انگلیسی آهنگ"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" class="pb-0">
                    <v-text-field
                        v-model="form_data.persian_name"
                        outlined
                        clearable
                        dense
                        label="عنوان فارسی آهنگ"
                    ></v-text-field>
                </v-col>
            </v-row>

            <div class="pb-5">
                <v-row>
                    <v-col cols="12" sm="6" class="pb-0">
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
                <v-col cols="12" sm="6" class="pb-0">
                    <v-menu
                        ref="menu"
                        v-model="menu"
                        :close-on-content-click="false"
                        transition="scale-transition"
                        offset-y
                        min-width="auto"
                    >
                        <template #activator="{ on, attrs }">
                            <v-text-field
                                v-model="form_data.published_at"
                                label="تاریخ انتشار"
                                outlined
                                dense
                                prepend-icon="mdi-calendar"
                                readonly
                                v-bind="attrs"
                                v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="form_data.publicated_at"
                            :max="(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10)"
                            min="1950-01-01"
                        ></v-date-picker>
                    </v-menu>
                </v-col>
                <v-col v-if="form_data.has_album" sm="8" lg="4" xl="4" class="pb-2">
                    <v-text-field
                        v-model="form_data.album_id"
                        outlined
                        dense
                        label="شناسه آلبوم"
                    ></v-text-field>
                </v-col>
                <v-col sm="4" lg="2" xl="2" class="pa-0">
                    <v-checkbox
                        v-model="form_data.has_album"
                        label="آلبوم دارد؟"
                    ></v-checkbox>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12" sm="12" class="pb-0">
                    درجه سختی
                    <v-radio-group
                        v-model="form_data.degree"
                        row
                    >
                        <v-radio
                            label="آسان"
                            :value="1"
                        ></v-radio>
                        <v-radio
                            label="متوسط"
                            :value="2"
                        ></v-radio>
                        <v-radio
                            label="سخت"
                            :value="3"
                        ></v-radio>
                        <v-radio
                            label="خیلی سخت"
                            :value="4"
                        ></v-radio>
                    </v-radio-group>
                </v-col>
                <v-col cols="12" sm="8" class="pb-0">
                    وضعیت
                    <v-radio-group
                        v-model="form_data.status"
                        row
                    >
                        <v-radio
                            label="فعال"
                            :value="1"
                        ></v-radio>
                        <v-radio
                            label="غیر فعال"
                            :value="0"
                        ></v-radio>
                    </v-radio-group>
                </v-col>
                <v-col cols="12" sm="4" class="pa-0">
                    <v-checkbox
                        v-model="form_data.is_user_request"
                        label="درخواستی کاربر"
                    ></v-checkbox>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12" sm="6" class="pb-0">
                    <v-file-input
                        show-size
                        dense
                        outlined
                        label="تصویر بنر"
                        v-model="form_data.image" persistent-hint
                        hint="فرمت تصویر باید jpg و سایز آن 300*300 باشد"
                        accept="image/*"
                    ></v-file-input>
                </v-col>
                <v-col cols="12" sm="6" class="pb-0">
                    <v-file-input
                        show-size
                        dense
                        outlined
                        label="فایل موزیک"
                        v-model="form_data.music"
                        accept="audio/*"
                    ></v-file-input>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="6" sm="3" class="pb-0">
                    <v-text-field
                        v-model="form_data.start_demo"
                        outlined
                        dense
                        label="دموی آهنگ از"
                    ></v-text-field>
                </v-col>
                <v-col cols="6" sm="3" class="pb-0">
                    <v-text-field
                        v-model="form_data.end_demo"
                        outlined
                        dense
                        label="دموی آهنگ تا"
                    ></v-text-field>
                </v-col>
            </v-row>

            <v-row class="mb-4">
                <v-col cols="12" class="pb-0 text-center">
                    <v-btn
                        color="accent"
                        dense
                        :loading="loading"
                        :disabled="loading"
                        @click="saveMusic()"
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
    name:'edit_music',
    data: () => ({
        form_data:{
            has_album: false,
            is_user_request: false,
            singers: [],
        },
        singers: [],
        menu: false,
        errors:{},
        singers_filter:{
            no_page:true
        },
        loading: false,
        music_id:null,
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
        getMusic(){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.get(`musics/music/${this.music_id}`)
                .then(res => {
                    this.form_data = res.data.data.music
                    this.form_data.singers = res.data.data.singers
                    this.form_data.album_id ? this.form_data.has_album = true : this.form_data.has_album = false
                    this.getSingers();
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
        saveMusic(){
            this.$store.commit('SHOW_APP_LOADING' , 2)
            const d = new FormData();
            const x = this.form_data;

            d.append('id', this.music_id)
            x.name ? d.append('english_title', x.name) : '';
            x.persian_name ? d.append('persian_title', x.persian_name) : '';
            x.published_at ? d.append('date_publication', x.published_at) : '';
            x.has_album ? d.append('has_album', 1) : d.append('has_album', 0);
            x.is_user_request ? d.append('is_user_request', 1) : d.append('is_user_request', 0);
            x.status ? d.append('status', 1) : d.append('status', 0);
            x.album_id ? d.append('album', x.album_id) : '';
            x.degree ? d.append('hardest_degree', parseInt(x.degree)) : '';
            x.image ? d.append('image', x.image) : '';
            x.music ? d.append('music', x.music) : '';
            d.append('start_demo', x.start_demo);
            d.append('end_demo', x.end_demo);

            if(x.singers.length){
                d.append('singers', x.singers);
            }

            this.$http.post(`musics/update` , d)
                .then(res => {

                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                    this.$router.push({name:'musics'})

                })
                .catch( err => {
                    this.loading = false
                    const e = err.response.data
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                    if(e.errors){ this.errors = e.errors }
                    this.$fire({
                        title: "خطا",
                        text: e.message ? e.message : 'خطا در پردازش درخواست !',
                        type: "error",
                        timer: 5000
                    })
                });
        }
    },
    mounted(){
        this.music_id = this.$route.params.id;
        this.getMusic();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ویرایش موزیک')
    }
}
</script>
<style>
</style>
