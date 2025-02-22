<template>
    <div>

        <div class="page-head">
            <div class="titr">ویرایش اصطلاح</div>
            <div class="back">
                <router-link :to="{ name : 'idioms' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <div class="ma-auto" style="max-width: 720px;">
            <v-container>

                <v-row class="pt-3">
                    <v-col cols="12" xs="12" sm="12" class="pb-0 text-left">
                        <v-btn
                            color="red" dark
                            @click="deleteIdiom()"
                        >حذف اصطلاح</v-btn>
                    </v-col>
                </v-row>

                <v-row class="pt-3">
                    <v-col cols="12" xs="12" sm="12" class="pb-0">
                        <v-text-field
                            v-model="form_data.base"
                            outlined clearable
                            :error-messages="errors.base"
                            dense label="لغت پایه"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xs="12" sm="12" class="pb-0">
                        <v-textarea
                            v-model="form_data.phrase"
                            :error-messages="errors.phrase"
                            outlined
                            dense rows="3"
                            label="متن اصطلاح"
                        ></v-textarea>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12" sm="12" md="6" class="pb-0">
                        <v-select
                            :items="[
                                {
                                    value:0,
                                    title:'انتخاب نشده',
                                },
                                {
                                    value:1,
                                    title:'عبارت دو بخشی',
                                },
                                {
                                    value:2,
                                    title:'کالوکیشن',
                                },
                                {
                                    value:3,
                                    title:'افعال عبارتی',
                                },
                                {
                                    value:4,
                                    title:'اصطلاحات',
                                },
                                {
                                    value:5,
                                    title:'ضرب المثل',
                                },
                                {
                                    value:6,
                                    title:'اسلنگ',
                                },
                                {
                                    value:7,
                                    title:'عبارت',
                                },
                            ]"
                            item-value="value"
                            item-text="title"
                            v-model="form_data.type"
                            outlined clearable
                            :error-messages="errors.type"
                            dense label="نوع"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" sm="12" md="6" class="pb-0">
                        <v-select
                            :items="levels"
                            v-model="form_data.level"
                            outlined
                            :error-messages="errors.level"
                            dense label="سطح"
                        ></v-select>
                    </v-col>
                </v-row>
                <hr class="mt-3">
                <div class="d-flex align-center justify-space-between pa-2">
                    <div>معنی اصطلاح</div>
                    <div>
                        <v-btn
                            class="mx-2" fab dark small color="primary"
                            @click="addDefinition()"
                        >
                            <v-icon dark>mdi-plus</v-icon>
                        </v-btn>
                    </div>
                </div>
                <v-container class="pa-0">
                    <draggable v-model="form_data.idiom_definitions" class="w-100">
                        <div v-for="(item , key) in form_data.idiom_definitions" :key="key" class="pa-2 stripes-bg">
                            <v-row>
                                <v-col v-if="item.idiom_definition_image" cols="12" xs="12" sm="12" class="pb-0">
                                    <v-card rounded="lg" ripple max-width="200" width="100%" class="ma-auto">
                                        <v-img width="100%" :src="item.idiom_definition_image"></v-img>
                                    </v-card>
                                </v-col>
                                <v-col cols="12" sm="12" md="8" class="pb-0">
                                    <v-textarea
                                        v-model="item.definition"
                                        outlined rows="3"
                                        :error-messages="errors[`idiom_definitions.${key}.definition`] ? errors[`idiom_definitions.${key}.definition`] : null"
                                        dense :label="'معنی ' + (key + 1)"
                                        :prepend-icon="item.id ? 'mdi-image-area' : ''"
                                        @click:prepend="definition_upload_image = item , definition_upload_image_modal = true"
                                    ></v-textarea>
                                </v-col>
                                <v-col cols="12" sm="12" md="4" class="pb-0">
                                    <v-select
                                        :items="levels"
                                        v-model="item.level" outlined
                                        :append-outer-icon="item.id ? '' : 'mdi-delete'"
                                        @click:append-outer="removeDefinition(key)"
                                        :error-messages="errors[`idiom_definitions.${key}.level`] ? errors[`idiom_definitions.${key}.level`] : null"
                                        dense :label="'سطح معنی ' + (key + 1)"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" sm="12" class="pb-0">
                                    <v-textarea
                                        v-model="item.description"
                                        outlined rows="3"
                                        dense :label="'توضیحات برای معنی ' + (key + 1)"
                                    ></v-textarea>
                                </v-col>
                                <template v-if="item.links">
                                    <v-col v-for="(link, key) in item.links" :key="key" cols="12" sm="12" class="pt-0">
                                        <span class="pa-2">{{link.title}}:</span>
                                        <v-chip v-for="(link_item, link_item_key) in link.list" :key="link_item_key" pill close @click:close="deleteLink(link_item.link_id)" class="mx-1">
                                            {{link_item.text}}
                                        </v-chip>
                                    </v-col>
                                </template>
                                <template v-if="item.categories">
                                    <v-col v-if="item.categories.length > 0" cols="12" sm="12" class="pt-0">
                                        <v-chip
                                            v-for="(category, key) in item.categories"
                                            :key="key" pill
                                            :outlined="category.mode ==='category'"
                                            :color="category.color"
                                            :text-color="category.mode ==='category' ? category.color : getTextColor(category.color)"
                                            class="mx-1"
                                        >
                                            <v-avatar v-if="category.category_poster" left>
                                                <v-img :src="category.category_poster"></v-img>
                                            </v-avatar>
                                            {{category.title}}
                                        </v-chip>
                                    </v-col>
                                </template>
                                <v-col cols="12" sm="12" class="py-0 mb-4 pb-2">
                                    <div class="d-flex justify-space-between">
                                        <div class="d-flex">
                                            <v-btn v-if="item.id" dark color="orange" small @click="joinable_id = item.id , join_modal = true" class="ml-2">اتصال به متن</v-btn>
                                            <select-category
                                                v-if="item.id"
                                                :categorizeable_id="item.id"
                                                categorizeable_type="idiom_definitions"
                                                :categories_selected_ids="item.categories_ids"
                                                @refresh="refresh()"
                                            ></select-category>
                                            <select-link v-if="item.id" :link_from_id="item.id" link_from_type="idiom_definition" @refresh="refresh()"></select-link>
                                        </div>
                                        <v-btn v-if="item.id && parseInt(item.joins_count) === 0" dark :loading="delete_loading" color="danger" small @click="deleteDefinition(item.id)">حذف این معنی</v-btn>
                                    </div>
                                </v-col>
                            </v-row>
                            <div v-if="item.joins_count > 0">
                                <div class="d-flex justify-space-between">
                                    <v-btn
                                        color="purple" text
                                        @click="show_joins = parseInt(item.id)"
                                    >{{item.joins_count}} مثال در آهنگ ها و فیلم ها</v-btn>

                                    <v-btn icon @click="show_joins = 0">
                                        <v-icon>{{ show_joins === parseInt(item.id) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                                    </v-btn>
                                </div>
                                <v-expand-transition>
                                    <div v-show="show_joins === parseInt(item.id)">
                                        <v-divider></v-divider>
                                        <v-list-item-group>
                                            <v-list-item v-for="(join, i) in item.text_joins" :key="i" dense>
                                                <v-list-item-content>
                                                    <v-list-item-title @click="goToTextsPage(join)">
                                                        <div>{{join.text.text_english}}</div>
                                                        <div>{{join.text.text_persian}}</div>
                                                    </v-list-item-title>
                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-list-item-group>
                                    </div>
                                </v-expand-transition>
                            </div>
                            <div class="my-4">
                                <small>مثال برای معنی</small>
                            </div>
                            <div v-for="(example , example_key) in item.idiom_definition_examples" :key="example_key">
                                <v-row>
                                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                                        <v-textarea
                                            v-model="example.phrase"
                                            outlined rows="3"
                                            dense :label="'عبارت ' + (example_key + 1)"
                                            :error-messages="errors[`idiom_definitions.${key}.idiom_definition_examples.${example_key}.phrase`] ? errors[`idiom_definitions.${key}.idiom_definition_examples.${example_key}.phrase`] : null"
                                        ></v-textarea>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                                        <v-textarea
                                            v-model="example.definition"
                                            outlined rows="3"
                                            append-outer-icon="mdi-delete"
                                            @click:append-outer="removeExample(key , example_key)"
                                            dense :label="'معنی عبارت ' + (example_key + 1)"
                                            :error-messages="errors[`idiom_definitions.${key}.idiom_definition_examples.${example_key}.definition`] ? errors[`idiom_definitions.${key}.idiom_definition_examples.${example_key}.definition`] : null"
                                        ></v-textarea>
                                    </v-col>
                                </v-row>
                            </div>
                            <div class="d-flex justify-end">
                                <v-btn x-small color="primary" dark @click="addExample(key)">افزودن مثال </v-btn>
                            </div>
                            <hr style="margin-block: 1rem;border-style: dashed;">
                        </div>
                    </draggable>
                </v-container>

                <div class="text-center pt-3">
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="success"
                        @click="editIdiom()"
                    >ویرایش</v-btn>
                </div>

            </v-container>
        </div>

        <v-dialog
            max-width="600"
            v-model="definition_upload_image_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >انتخاب تصویر جدید برای معنی با شناسه {{definition_upload_image.id}}</v-toolbar>
                <v-card-text>
                    <v-container>
                        <v-row class="pt-3">
                            <v-col cols="12" class="pb-0">
                                <v-file-input
                                    label="تصویر خود را انتخاب کنید"
                                    outlined dense
                                    v-model="definition_upload_image.image"
                                    hint="فرمت تصویر باید jpg و سایز آن 450*300 و حجمش حداکثر 50kb باشد"
                                    show-size accept="image/*" persistent-hint
                                    :error-messages="errors.image"
                                ></v-file-input>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions class="justify-end">
                    <v-btn color="danger" dark @click="definition_upload_image_modal = false">بستن</v-btn>
                    <v-btn
                        :loading="upload_loading"
                        :disabled="upload_loading"
                        color="success"
                        @click="uploadDefinitionImage()"
                    >آپلود</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog
            max-width="600"
            v-model="join_modal"
        >
            <v-card>
                <v-toolbar color="accent" dark class="d-flex justify-space-between">
                    اتصال معنی با شناسه {{joinable_id}}
                </v-toolbar>
                <join-text-to-vendor v-if="join_modal" :joinable_id="joinable_id" joinable_type="idiom_definition" :search_for="form_data.phrase" @close="join_modal = false"></join-text-to-vendor>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
import draggable from 'vuedraggable'
export default {
    name:'create_idiom',
    components:{
        draggable
    },
    data: () => ({
        form_data:{
            idiom_definitions: [
                {
                    definition:'',
                    level:'',
                    idiom_definition_examples: []
                }
            ]
        },
        definition_upload_image:{},
        errors:{},
        definition_upload_image_modal: false,
        upload_loading: false,
        loading: false,
        delete_loading: false,
        show_joins: 0,
        join_modal: false,
        joinable_id: null,
    }),
    methods:{
        refresh(){
            this.getIdiom(this.$route.params.id);
        },
        addDefinition(){
            this.form_data.idiom_definitions.push({
                definition:'',
                idiom_definition_examples: []
            })
            return true;
        },
        removeDefinition(definition_key){
            if(this.form_data.idiom_definitions.length === 1){
                alert('حداقل یک معنی باید تعریف شود');
                return false;
            }
            this.form_data.idiom_definitions.splice(definition_key , 1)
            return true;
        },
        addExample(definition_key){
            this.form_data.idiom_definitions[definition_key].idiom_definition_examples.push({
                definition:'',
                phrase:''
            })
            return true;
        },
        removeExample(definition_key , example_key){
            this.form_data.idiom_definitions[definition_key].idiom_definition_examples.splice(example_key , 1)
            return true;
        },
        getIdiom(id){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`idioms/single` , {id:id})
            .then(res => {
                this.form_data = res.data.data
                this.$store.commit('SHOW_APP_LOADING' , 0)
            })
            .catch( () => {
                this.$store.commit('SHOW_APP_LOADING' , 0)
                this.$router.replace({name:'idioms'});
            });
        },
        editIdiom(){
            this.loading = true;
            this.$http.post(`idioms/update` , this.form_data)
            .then(res => {
                this.form_data = {
                    idiom_definitions: [],
                    idiom_definition_examples: []
                };
                this.loading = false;
                this.$fire({
                    title: "موفق",
                    text: res.data.message,
                    type: "success",
                    timer: 5000
                })
                this.errors = {};
                this.getIdiom(this.$route.params.id);
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
        deleteIdiom(){
            if (confirm('باحذف این اصطلاح تمامی معانی و مثال های مرتبط باآن حذف خواهد شد. \n \n از حذف اصطلاح اطمینان دارید؟')){
                this.$http.post(`idioms/remove` , {id:this.form_data.id})
                .then(res => {
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.$router.push({name:'idioms'})
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
        deleteDefinition(id){
            if (confirm('از حذف معنی اصطلاح اطمینان دارید؟')){
                this.delete_loading = true;
                this.$http.post(`idioms/definition/remove` , {id})
                .then(res => {
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.delete_loading = false;
                    this.getIdiom(this.$route.params.id);
                })
                .catch( err => {
                    this.delete_loading = false;
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
        uploadDefinitionImage(){
            this.upload_loading = true
            const form = new FormData();

            this.definition_upload_image.id ? form.append('idiom_definition_id', this.definition_upload_image.id) : '';
            this.definition_upload_image.image ? form.append('image', this.definition_upload_image.image) : '';

            this.$http.post(`idioms/add_image` , form)
                .then(res => {
                    this.definition_upload_image = {};
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.getIdiom(this.$route.params.id)
                    this.definition_upload_image_modal = false;
                })
                .catch( err => {
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
                }).finally(() => {
                this.upload_loading = false;
            });
        },
        deleteLink(id){
            if (confirm('از حذف لینک اطمینان دارید؟')){
                this.$http.post(`links/delete` , {id})
                    .then(res => {
                        this.$fire({
                            title: "موفق",
                            text: res.data.message,
                            type: "success",
                            timer: 5000
                        })
                        this.getIdiom(this.$route.params.id);
                    })
                    .catch( err => {
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
        goToTextsPage(join){
            let type = 'music';
            if (join.text.textable_type === 'App\\Models\\Film') {
                type = 'film';
            }
            const link = {
                name : 'edit_texts',
                params:{
                    textable_id:join.text.textable_id,
                    type
                },
                query:{
                    'text_id': join.text_id
                }
            };
            const link_data = this.$router.resolve(link);
            if (link_data) {
                window.open(link_data.href, '_blank');
            }
        },
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ویرایش اصطلاح')
    },
    mounted() {
        const idiom_id = this.$route.params.id;
        this.getIdiom(idiom_id);
    }
}
</script>
