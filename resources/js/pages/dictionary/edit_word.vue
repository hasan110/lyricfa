<template>
    <div>
        <div class="page-head">
            <div class="titr">ویرایش لغت</div>
            <div class="back">
                <router-link :to="{ name : 'words' }">بازگشت
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
                            @click="deleteWord()"
                        >حذف لغت</v-btn>
                    </v-col>
                </v-row>
                <v-row class="pt-3">
                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                        <v-text-field
                            v-model="form_data.english_word"
                            outlined clearable
                            :error-messages="errors.english_word"
                            dense label="لغت"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xs="6" sm="3" class="pb-0">
                        <v-text-field
                            v-model="form_data.us_pronunciation"
                            :error-messages="errors.us_pronunciation"
                            outlined
                            dense
                            label="تلفظ (us)"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xs="6" sm="3" class="pb-0">
                        <v-text-field
                            v-model="form_data.uk_pronunciation"
                            :error-messages="errors.uk_pronunciation"
                            outlined
                            dense
                            label="تلفظ (uk)"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                        <v-select
                            v-model="form_data.word_type"
                            outlined
                            :items="word_types"
                            dense multiple
                            label="نوع لغت"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                        <v-select
                            v-model="form_data.level"
                            outlined label="سطح"
                            :error-messages="errors.level"
                            :items="levels" dense
                        ></v-select>
                    </v-col>
                </v-row>
                <hr class="mt-3">
                <div class="d-flex align-center justify-space-between pa-2">
                    <div>معنی فارسی لغت</div>
                    <div>
                        <v-btn
                            class="mx-2" fab dark small color="primary"
                            @click="addDefinition(1)"
                        >
                            <v-icon dark>mdi-plus</v-icon>
                        </v-btn>
                    </div>
                </div>
                <v-container class="pa-0">
                    <draggable v-model="form_data.word_definitions" class="w-100">
                        <div v-for="(item , key) in form_data.word_definitions" :key="key" class="stripes-bg pa-2">
                            <v-row>
                                <v-col v-if="item.word_definition_image" cols="12" xs="12" sm="12" class="pb-0">
                                    <v-card rounded="lg" ripple max-width="200" width="100%" class="ma-auto">
                                        <v-img width="100%" :src="item.word_definition_image"></v-img>
                                    </v-card>
                                </v-col>
                                <v-col cols="12" sm="12" md="8" class="pb-0">
                                    <v-textarea
                                        v-model="item.definition" outlined rows="4"
                                        :error-messages="errors[`word_definitions.${key}.definition`] ? errors[`word_definitions.${key}.definition`] : null"
                                        dense :label="'معنی ' + (key + 1)"
                                        :prepend-icon="item.id ? 'mdi-image-area' : ''"
                                        @click:prepend="definition_upload_image = item , definition_upload_image_modal = true"
                                    ></v-textarea>
                                </v-col>
                                <v-col cols="12" sm="12" md="4" class="pb-0">
                                    <v-row>
                                        <v-col cols="12" sm="12" class="pb-0">
                                            <v-select
                                                v-model="item.level" outlined
                                                :append-outer-icon="item.id ? '' : 'mdi-delete'"
                                                :items="levels"
                                                @click:append-outer="removeDefinition(1 , key)"
                                                :error-messages="errors[`word_definitions.${key}.level`] ? errors[`word_definitions.${key}.level`] : null"
                                                dense :label="'سطح معنی ' + (key + 1)"
                                            ></v-select>
                                        </v-col>
                                        <v-col cols="12" sm="12" class="pb-0">
                                            <v-select
                                                v-model="item.type" outlined
                                                :items="word_types"
                                                :error-messages="errors[`word_definitions.${key}.type`] ? errors[`word_definitions.${key}.type`] : null"
                                                dense :label="'نوع معنی ' + (key + 1)"
                                            ></v-select>
                                        </v-col>
                                    </v-row>
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
                                        <v-chip v-for="(link_item, link_item_key) in link.list" :key="link_item_key" pill class="mx-1" close @click:close="deleteLink(link_item.link_id)">
                                            {{link_item.text}}
                                        </v-chip>
                                    </v-col>
                                </template>
                                <template v-if="item.categories">
                                    <v-col v-if="item.categories.length > 0" cols="12" sm="12" class="pt-0">
                                        <v-chip
                                            v-for="(category, key) in item.categories"
                                            :key="key"
                                            pill
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
                                <v-col cols="12" sm="12" class="py-0 mb-6">
                                    <div class="d-flex justify-space-between">
                                        <div class="d-flex">
                                            <v-btn v-if="item.id" dark color="orange" small @click="joinable_id = item.id , join_modal = true" class="ml-2">اتصال به متن</v-btn>
                                            <select-category
                                                v-if="item.id"
                                                :categorizeable_id="item.id"
                                                categorizeable_type="word_definitions"
                                                :categories_selected_ids="item.categories_ids"
                                                @refresh="refresh()"
                                            ></select-category>
                                            <select-link v-if="item.id" :link_from_id="item.id" link_from_type="word_definition" @refresh="refresh()"></select-link>
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
                            <div class="mt-4" style="color: #6200ed">
                                <small>مثال برای معنی</small>
                            </div>
                            <div v-for="(example , example_key) in item.word_definition_examples" :key="example_key">
                                <v-row>
                                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                                        <v-textarea
                                            v-model="example.phrase"
                                            outlined rows="3"
                                            dense :label="'عبارت ' + (example_key + 1)"
                                            :error-messages="errors[`word_definitions.${key}.word_definition_examples.${example_key}.phrase`] ? errors[`word_definitions.${key}.word_definition_examples.${example_key}.phrase`] : null"
                                        ></v-textarea>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                                        <v-textarea
                                            v-model="example.definition"
                                            outlined rows="3"
                                            append-outer-icon="mdi-delete"
                                            @click:append-outer="removeExample(key , example_key)"
                                            dense :label="'معنی عبارت ' + (example_key + 1)"
                                            :error-messages="errors[`word_definitions.${key}.word_definition_examples.${example_key}.definition`] ? errors[`word_definitions.${key}.word_definition_examples.${example_key}.definition`] : null"
                                        ></v-textarea>
                                    </v-col>
                                </v-row>
                            </div>
                            <div class="d-flex justify-end">
                                <v-btn x-small color="primary" dark @click="addExample(key)">افزودن مثال </v-btn>
                            </div>
                            <hr style="margin-top: 8px;margin-bottom: 48px;border-style: dashed">
                        </div>
                    </draggable>
                </v-container>
                <hr class="mt-3">
                <div class="d-flex align-center justify-space-between pa-2">
                    <div>معنی انگلیسی لغت</div>
                    <div>
                        <v-btn
                            class="mx-2" fab dark small color="primary"
                            @click="addDefinition(2)"
                        >
                            <v-icon dark>mdi-plus</v-icon>
                        </v-btn>
                    </div>
                </div>
                <v-container>
                    <div v-for="(item , en_key) in form_data.english_definitions" :key="en_key">
                        <v-row>
                            <v-col cols="12" xs="12" sm="12" class="pb-0">
                                <v-textarea
                                    v-model="item.definition"
                                    outlined rows="3"
                                    :error-messages="errors[`english_definitions.${en_key}.definition`] ? errors[`english_definitions.${en_key}.definition`] : null"
                                    dense :label="'معنی انگلیسی ' + (en_key + 1)"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                <v-text-field
                                    v-model="item.pronounciation"
                                    outlined clearable
                                    :error-messages="errors[`english_definitions.${en_key}.pronunciation`] ? errors[`english_definitions.${en_key}.pronunciation`] : null"
                                    dense label="تلفظ"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                <v-select
                                    v-model="item.word_type"
                                    :items="word_types"
                                    outlined clearable
                                    append-outer-icon="mdi-delete"
                                    @click:append-outer="removeDefinition(2 , en_key)"
                                    :error-messages="errors[`english_definitions.${en_key}.word_type`] ? errors[`english_definitions.${en_key}.word_type`] : null"
                                    dense label="نوع"
                                ></v-select>
                            </v-col>
                        </v-row>
                    </div>
                </v-container>
                <div class="text-center pt-3">
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="success"
                        @click="saveWord()"
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
                <join-text-to-vendor v-if="join_modal" :joinable_id="joinable_id" joinable_type="word_definition" :search_for="form_data.english_word" @close="join_modal = false"></join-text-to-vendor>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
import draggable from "vuedraggable";
export default {
    name:'edit_word',
    components: {
        draggable
    },
    data: () => ({
        form_data:{},
        definition_upload_image:{},
        word_types:[],
        errors:{},
        definition_upload_image_modal: false,
        upload_loading: false,
        loading: false,
        delete_loading: false,
        show_joins: 0,
        join_modal: false,
        joinable_id: null,
    }),
    watch:{
        singers_count(){
            this.singers_count = parseInt(this.singers_count);
        }
    },
    methods:{
        refresh(){
            this.getWord(this.$route.params.id);
        },
        addDefinition(type){
            if(type === 1){
                this.form_data.word_definitions.push({
                    definition:'',
                    word_definition_examples: [],
                    definition_examples: []
                })
            }else if(type === 2){
                this.form_data.english_definitions.push({
                    definition:'',
                    word_type:'',
                    pronunciation:''
                })
            }
            return true;
        },
        removeDefinition(type , definition_key){
            if(type === 1){
                if(this.form_data.word_definitions.length === 1){
                    alert('حداقل یک معنی باید تعریف شود');
                    return false;
                }
                this.form_data.word_definitions.splice(definition_key , 1)
            }else if(type === 2){
                this.form_data.english_definitions.splice(definition_key , 1)
            }
            return true;
        },
        addExample(definition_key){
            this.form_data.word_definitions[definition_key].word_definition_examples.push({
                definition:'',
                phrase:''
            })
            return true;
        },
        removeExample(definition_key , example_key){
            this.form_data.word_definitions[definition_key].word_definition_examples.splice(example_key , 1)
            return true;
        },
        getWord(id) {
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`words/single` , {id})
                .then(res => {
                    this.form_data = res.data.data
                    if (this.form_data.word_types) {
                        this.form_data.word_type = this.form_data.word_types.split(',')
                    }
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                })
                .catch( () => {
                    this.$store.commit('SHOW_APP_LOADING' , 0)
                    this.$router.replace({name:'words'});
                });
        },
        saveWord() {
            this.loading = true;
            if (this.form_data.word_types) {
                this.form_data.word_types = this.form_data.word_type.join(',')
            }
            this.$http.post(`words/update` , this.form_data)
            .then(res => {
                this.form_data = {};
                this.loading = false;
                this.$fire({
                    title: "موفق",
                    text: res.data.message,
                    type: "success",
                    timer: 5000
                })
                this.errors = {};
                this.getWord(this.$route.params.id);
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
        deleteWord(){
            if (confirm('باحذف این لغت تمامی معانی و مثال های فارسی و انگلیسی حذف خواهد شد. \n \n از حذف لغت اطمینان دارید؟')){
                this.$http.post(`words/remove` , {id:this.form_data.id})
                    .then(res => {
                        this.$fire({
                            title: "موفق",
                            text: res.data.message,
                            type: "success",
                            timer: 5000
                        })
                        this.$router.push({name:'words'})
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
        getWordTypes(){
            this.$http.get(`words/types`)
                .then(res => {
                    this.word_types = res.data.data
                })
                .catch( err => {
                    console.log(err)
                });
        },
        deleteDefinition(id){
            if (confirm('از حذف معنی لغت اطمینان دارید؟')){
                this.delete_loading = true;
                this.$http.post(`words/definition/remove` , {id})
                    .then(res => {
                        this.$fire({
                            title: "موفق",
                            text: res.data.message,
                            type: "success",
                            timer: 5000
                        })
                        this.delete_loading = false;
                        this.getWord(this.$route.params.id);
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
                        this.getWord(this.$route.params.id);
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
        uploadDefinitionImage(){
            this.upload_loading = true
            const form = new FormData();

            this.definition_upload_image.id ? form.append('word_definition_id', this.definition_upload_image.id) : '';
            this.definition_upload_image.image ? form.append('image', this.definition_upload_image.image) : '';

            this.$http.post(`words/add_image` , form)
            .then(res => {
                this.definition_upload_image = {};
                this.$fire({
                    title: "موفق",
                    text: res.data.message,
                    type: "success",
                    timer: 5000
                })
                this.getWord(this.$route.params.id)
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
        this.setPageTitle('ویرایش لغت')
    },
    mounted() {
        const word_id = this.$route.params.id;
        this.getWord(word_id);
        this.getWordTypes();
    }
}
</script>
