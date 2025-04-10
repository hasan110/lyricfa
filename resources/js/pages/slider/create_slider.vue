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
            <v-row class="justify-center">
                <v-col lg="8" md="10" sm="12">
                    <v-row>
                        <v-col cols="12" lg="4" sm="6" class="pb-0">
                            <v-select
                                label="نوع اسلایدر"
                                :items="slider_types"
                                v-model="form_data.action"
                                item-text="text"
                                item-value="value"
                                outlined clearable dense
                                :error-messages="errors.action"
                            ></v-select>
                        </v-col>
                        <v-col v-if="form_data.action === 'external-link'" cols="12" lg="4" sm="6" class="pb-0">
                            <v-text-field
                                v-model="form_data.link"
                                outlined :error-messages="errors.link"
                                clearable
                                dense placeholder="https://example.com"
                                label="لینک را به صورت کامل وارد کنید"
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="form_data.action === 'internal-link'" cols="12" lg="4" sm="6" class="pb-0">
                            <v-select
                                label="لینک به کدام صفحه؟"
                                :items="[
                                    {text: 'خرید اشتراک',value: 'subscription'},
                                    {text: 'واژه نامه',value: 'dictionary'},
                                    {text: 'دنیای فیلم و آهنگ',value: 'media'},
                                    {text: 'دسته بندی',value: 'category'},
                                    {text: 'آهنگ',value: 'music'},
                                    {text: 'فیلم',value: 'film'},
                                    {text: 'خواننده',value: 'singer'},
                                ]"
                                v-model="form_data.link"
                                item-text="text"
                                item-value="value"
                                :error-messages="errors.link"
                                outlined clearable dense
                            ></v-select>
                        </v-col>
                        <v-col v-if="form_data.link === 'category' || form_data.link === 'film' || form_data.link === 'music' || form_data.link === 'singer'" cols="12" lg="4" sm="6" class="pb-0">
                            <v-text-field
                                label="شناسه مرتبط را وارد کنید"
                                v-model="form_data.linkable_id"
                                :error-messages="errors.linkable_id"
                                outlined clearable dense
                            ></v-text-field>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col cols="12" lg="4" sm="6" class="pb-2">
                            <v-text-field
                                v-model="form_data.title"
                                outlined
                                dense :error-messages="errors.title"
                                label="عنوان"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" lg="4" sm="6" class="pa-0">
                            <v-checkbox
                                v-model="form_data.status"
                                label="نمایش داده شود؟"
                            ></v-checkbox>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col cols="12" lg="6" class="pb-0">
                            <v-file-input
                                show-size
                                dense :error-messages="errors.banner"
                                outlined
                                label="انتخاب بنر"
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
                            >ثبت</v-btn>
                        </v-col>
                    </v-row>
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
            {text: 'بدون لینک',value: 'no-action'},
            {text: 'لینک به داخل',value: 'internal-link'},
            {text: 'لینک به خارج',value: 'external-link'},
        ],
        form_data:{
            status: false,
            link: '',
            linkable_id: '',
        },
        errors:{},
        loading: false,
    }),
    watch: {
        'form_data.action': {
            handler(after, before) {
                this.form_data.link = '';
                this.form_data.linkable_id = '';
            },
            deep: true
        },
    },
    methods:{
        saveSlider(){
            this.loading = true
            const d = new FormData();
            const x = this.form_data;

            x.action ? d.append('action', x.action) : '';
            x.title ? d.append('title', x.title) : '';
            x.link ? d.append('link', x.link) : '';
            x.linkable_id ? d.append('linkable_id', x.linkable_id) : '';
            x.status ? d.append('status', 1) : d.append('status', 0);
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
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ایجاد اسلایدر')
    }
}
</script>
<style>
</style>
