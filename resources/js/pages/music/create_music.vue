<template>
    <div>
        <div class="page-head">
            <div class="titr">ایجاد موزیک</div>
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
                        v-model="form_data.english_title"
                        :error-messages="errors.english_title"
                        outlined
                        clearable
                        dense
                        label="عنوان انگلیسی آهنگ"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" class="pb-0">
                    <v-text-field
                        v-model="form_data.persian_title"
                        :error-messages="errors.persian_title"
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
                            :error-messages="errors.singers"
                        ></v-autocomplete>
                    </v-col>
                    <v-col cols="12" sm="6" class="pb-0">
                        <v-select
                            v-model="form_data.level"
                            outlined :items="levels"
                            dense label="سطح آهنگ"
                            :error-messages="errors.level"
                        ></v-select>
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
                                v-model="form_data.date_publication"
                                label="تاریخ انتشار"
                                :error-messages="errors.date_publication"
                                outlined dense readonly
                                prepend-icon="mdi-calendar"
                                v-bind="attrs" v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="form_data.date_publication"
                            :max="(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10)"
                            min="1950-01-01"
                        ></v-date-picker>
                    </v-menu>
                </v-col>
                <v-col v-if="form_data.has_album" sm="8" lg="4" xl="4" class="pb-2">
                    <v-text-field
                        v-model="form_data.album_id"
                        outlined
                        :error-messages="errors.album_id"
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
                        v-model="form_data.hardest_degree"
                        :error-messages="errors.hardest_degree"
                        row
                    >
                        <v-radio
                            label="آسان"
                            value="1"
                        ></v-radio>
                        <v-radio
                            label="متوسط"
                            value="2"
                        ></v-radio>
                        <v-radio
                            label="سخت"
                            value="3"
                        ></v-radio>
                        <v-radio
                            label="خیلی سخت"
                            value="4"
                        ></v-radio>
                    </v-radio-group>
                </v-col>
                <v-col cols="12" sm="8" class="pb-0">
                    وضعیت
                    <v-radio-group
                        v-model="form_data.status"
                        :error-messages="errors.status" row
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
                        show-size dense outlined
                        :error-messages="errors.image"
                        label="تصویر بنر"
                        v-model="form_data.image" persistent-hint
                        hint="فرمت تصویر باید jpg و سایز آن 300*300 باشد"
                        accept="image/*"
                    ></v-file-input>
                </v-col>
                <v-col cols="12" sm="6" class="pb-0">
                    <v-file-input
                        show-size dense outlined
                        :error-messages="errors.music"
                        label="فایل موزیک"
                        v-model="form_data.music"
                        accept="audio/*"
                    ></v-file-input>
                </v-col>
            </v-row>

            <v-row>
                <img v-if="form_data.image" :src="form_data.image" alt="">
            </v-row>

            <v-row>
                <v-col cols="6" sm="3" class="pb-0">
                    <v-text-field
                        v-model="form_data.start_demo"
                        :error-messages="errors.start_demo"
                        outlined dense
                        label="دموی آهنگ از"
                    ></v-text-field>
                </v-col>
                <v-col cols="6" sm="3" class="pb-0">
                    <v-text-field
                        v-model="form_data.end_demo"
                        :error-messages="errors.end_demo"
                        outlined dense
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
    name:'create_music',
    data: () => ({
        form_data:{
            has_album: false,
            is_user_request: false,
            singers: [],
        },
        singers: [],
        menu: false,
        singers_filter:{
            no_page:true
        },
        errors:{},
        loading: false,
        banner_image: null,
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
        changeBannerImage(event){
            this.banner_image = URL.createObjectURL(this.form_data.image);
            this.errors.image = null
        },
        saveMusic(){
            this.loading = true;
            this.errors = {};
            const d = new FormData();
            const x = this.form_data;

            x.english_title ? d.append('english_title', x.english_title) : '';
            x.persian_title ? d.append('persian_title', x.persian_title) : '';
            x.date_publication ? d.append('date_publication', x.date_publication) : '';
            x.has_album ? d.append('has_album', 1) : d.append('has_album', 0);
            x.is_user_request ? d.append('is_user_request', 1) : d.append('is_user_request', 0);
            x.status ? d.append('status', 1) : d.append('status', 0);
            x.album_id ? d.append('album_id', x.album_id) : '';
            d.append('hardest_degree', parseInt(x.hardest_degree));
            x.image ? d.append('image', x.image) : '';
            x.music ? d.append('music', x.music) : '';
            x.level ? d.append('level', x.level) : '';
            d.append('start_demo', x.start_demo);
            d.append('end_demo', x.end_demo);

            if(x.singers.length){
                d.append('singers', x.singers);
            }

            this.$http.post(`musics/create` , d)
                .then(res => {
                    this.form_data = {
                        has_album: false,
                        singers: [],
                    };

                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.loading = false;
                    this.$router.push({name:'musics'})

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
        }
    },
    mounted(){
        this.getSingers()
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ایجاد موزیک')
    }
}
</script>
<style>
</style>
