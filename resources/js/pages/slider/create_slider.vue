<template>
    <div>

        <div class="page-head">
            <div class="titr">ایجاد اسلایدر</div>
            <div class="back">
                <router-link :to="{ name : 'sliders' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <v-container>

            <v-row>

                <v-col cols="4" class="pb-0">
                    <v-select
                        label="نوع اسلایدر"
                        :items="slider_types"
                        v-model="form_data.type"
                        item-text="text"
                        item-value="value"
                        outlined
                        clearable
                        dense
                    ></v-select>
                </v-col>
                <v-col cols="4" class="pb-0">
                    <v-text-field
                        v-model="form_data.id_singer_music_album"
                        outlined
                        clearable
                        dense
                        label="آیدی مرتبط"
                    ></v-text-field>
                </v-col>

            </v-row>

            <hr>
            <br>
            <v-row>
                <v-col cols="4" class="pb-2">
                    <v-text-field
                        v-model="form_data.title"
                        outlined
                        dense
                        label="عنوان"
                    ></v-text-field>
                </v-col>
                <v-col cols="4" class="pb-2">
                    <v-textarea
                        v-model="form_data.description"
                        outlined
                        dense
                        label="توضیحات"
                    ></v-textarea>
                </v-col>
                <v-col cols="4" class="pa-0">
                    <v-checkbox
                        v-model="form_data.show_it"
                        label="نمایش داده شود؟"
                    ></v-checkbox>
                </v-col>

            </v-row>

            <v-row>

                <v-col cols="4" class="pb-0">
                    <v-file-input
                        show-size
                        dense
                        outlined
                        label="تصویر بنر"
                        v-model="form_data.banner"
                        accept="image/*"
                        persistent-hint hint="فرمت تصویر باید jpg و سایز آن 575*1024 باشد"
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
                        @click="saveSlider()"
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
        slider_types: [
            {text: 'موزیک',value: 1},
            {text: 'آلبوم',value: 2},
            {text: 'خواننده',value: 3},
            {text: 'چندین آهنگ',value: 4},
        ],
        form_data:{
            show_it: false,
        },
        errors:{},
        loading: false,
    }),
    methods:{
        saveSlider(){
            this.loading = true
            const d = new FormData();
            const x = this.form_data;

            x.type ? d.append('type', x.type) : '';
            x.id_singer_music_album ? d.append('id_singer_music_album', x.id_singer_music_album) : '';
            x.title ? d.append('title', x.title) : '';
            x.description ? d.append('description', x.description) : '';
            x.show_it ? d.append('show_it', 1) : d.append('show_it', 0);
            x.banner ? d.append('banner', x.banner) : '';


            this.$http.post(`sliders/create` , d)
                .then(res => {

                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })

                    this.$router.push({name:'sliders'})

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
        }
    },
    mounted(){
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ایجاد اسلایدر')
    }
}
</script>
<style>
</style>
