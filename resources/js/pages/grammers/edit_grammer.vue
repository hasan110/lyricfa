<template>
    <div>

        <div class="page-head">
            <div class="titr">ویرایش گرامر</div>
            <div class="back">
                <router-link :to="{ name : 'grammers' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <div class="ma-auto" style="max-width: 720px;">
            <v-container class="mb-5">
                <v-tabs v-model="tab">
                    <v-tabs-slider></v-tabs-slider>
                    <v-tab href="#main">اطلاعات کلی</v-tab>
                    <v-tab href="#details">توضیحات و مثال ها</v-tab>
                </v-tabs>

                <v-tabs-items v-model="tab">
                    <v-tab-item value="main">
                        <v-row class="pt-3">
                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                <v-text-field
                                    v-model="form_data.english_name"
                                    outlined clearable
                                    :error-messages="errors.english_name"
                                    dense label="عنوان انگلیسی"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                <v-text-field
                                    v-model="form_data.persian_name"
                                    outlined clearable
                                    :error-messages="errors.persian_name"
                                    dense label="عنوان فارسی"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                <v-select
                                    v-model="form_data.level"
                                    outlined
                                    :error-messages="errors.level"
                                    :items="levels"
                                    dense label="سطح"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                <v-text-field
                                    v-model="form_data.priority"
                                    :error-messages="errors.priority"
                                    dense label="اولویت" outlined
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" class="py-0">
                                <div class="d-flex justify-space-between align-center my-4">
                                    <div>
                                        <v-chip
                                            v-for="(category, key) in form_data.categories"
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
                                    </div>
                                    <select-category
                                        v-if="form_data.id"
                                        :categorizeable_id="form_data.id"
                                        categorizeable_type="grammers"
                                        :categories_selected_ids="form_data.categories_ids"
                                        @refresh="refresh()"
                                    ></select-category>
                                </div>
                            </v-col>
                            <v-col cols="12" xs="12" sm="12" class="pb-0">
                                <v-textarea
                                    v-model="form_data.description"
                                    outlined
                                    :error-messages="errors.description"
                                    dense label="توضیحات"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" xs="12" sm="12" class="pb-0">
                                <v-autocomplete
                                    chips deletable-chips dense multiple small-chips
                                    v-model="form_data.prerequisite"
                                    outlined :items="grammers_list"
                                    item-value="id"
                                    item-text="persian_name"
                                    :error-messages="errors.prerequisite"
                                    label="گرامرهای پیش نیاز"
                                    :search-input.sync="prerequisites_filter.search_key"
                                ></v-autocomplete>
                            </v-col>
                            <v-col cols="12" xs="12" sm="12" class="pb-0">
                                <v-autocomplete
                                    chips deletable-chips dense multiple small-chips
                                    v-model="form_data.rules"
                                    outlined :items="rules_list"
                                    item-value="id" return-object
                                    :item-text="getRuleTitle"
                                    :error-messages="errors.rules"
                                    label="انتخاب قوانین"
                                    :search-input.sync="rules_filter.search_key"
                                ></v-autocomplete>
                            </v-col>
                            <v-col cols="12" xs="12" sm="12" class="pb-0">
                                <div v-for="(item , key) in form_data.rules">
                                    <v-text-field
                                        v-model="form_data.rules[key]['level']"
                                        outlined clearable
                                        dense :label="' سطح قانون شماره ' + getRuleTitle(item)"
                                    ></v-text-field>
                                </div>
                            </v-col>
                        </v-row>
                    </v-tab-item>
                    <v-tab-item value="details">
                        <div class="d-flex align-center justify-space-between pa-2">
                            <div>افزودن بخش</div>
                            <div>
                                <v-btn
                                    class="mx-2" fab dark small color="success"
                                    @click="addSection()"
                                >
                                    <v-icon dark>mdi-plus</v-icon>
                                </v-btn>
                            </div>
                        </div>
                        <div class="pa-2">
                            <v-expansion-panels accordion multiple>
                                <draggable v-model="form_data.grammer_sections" class="w-100">
                                    <v-expansion-panel v-for="(section_item, section_key) in form_data.grammer_sections" :key="section_key">
                                        <v-expansion-panel-header>
                                            <template v-if="section_item.title">
                                                {{section_item.title}}
                                            </template>
                                            <template v-else>بخش {{ section_key+1 }}</template>
                                        </v-expansion-panel-header>
                                        <v-expansion-panel-content>
                                            <v-row style="background: #f2f2f2;padding-bottom: .5rem; border-radius: .5rem;">
                                                <v-col cols="12" xs="12" sm="6" class="pb-0">
                                                    <v-text-field
                                                        v-model="section_item.title" outlined
                                                        :error-messages="errors[`grammer_sections.${section_key}.title`] ? errors[`grammer_sections.${section_key}.title`] : null"
                                                        dense :label="'عنوان بخش ' + (section_key + 1)" hide-details
                                                    ></v-text-field>
                                                </v-col>
                                                <v-col cols="12" xs="12" sm="3" class="pb-0">
                                                    <v-select
                                                        v-model="section_item.level" outlined :items="levels"
                                                        :error-messages="errors[`grammer_sections.${section_key}.level`] ? errors[`grammer_sections.${section_key}.level`] : null"
                                                        dense :label="'سطح بخش ' + (section_key + 1)" hide-details
                                                    ></v-select>
                                                </v-col>
                                                <v-col cols="12" xs="12" sm="3" class="pb-0 d-flex justify-end">
                                                    <v-btn
                                                        v-if="!section_item.id"
                                                        dark small color="error"
                                                        @click="removeSection(section_key)"
                                                    >
                                                        حذف بخش
                                                    </v-btn>
                                                </v-col>

                                                <v-col v-if="section_item.id" cols="12" sm="12" class="py-0 mt-4 pb-2">
                                                    <div class="d-flex justify-space-between">
                                                        <v-btn dark color="orange" small @click="joinable_id = section_item.id , join_modal = true">اتصال به متن</v-btn>
                                                    </div>
                                                </v-col>
                                            </v-row>

                                            <template v-if="section_item.joins_count > 0">
                                                <div class="d-flex justify-space-between mt-4">
                                                    <v-btn
                                                        color="purple" text
                                                        @click="show_joins = parseInt(section_item.id)"
                                                    >{{section_item.joins_count}} مثال در آهنگ ها و فیلم ها</v-btn>

                                                    <v-btn icon @click="show_joins = 0">
                                                        <v-icon>{{ show_joins === parseInt(section_item.id) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                                                    </v-btn>
                                                </div>
                                                <v-expand-transition>
                                                    <div v-show="show_joins === parseInt(section_item.id)">
                                                        <v-divider></v-divider>
                                                        <v-list-item-group>
                                                            <v-list-item v-for="(join, i) in section_item.text_joins" :key="i" dense>
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
                                            </template>

                                            <div class="d-flex align-center justify-space-between pa-4">
                                                <div>توضیح</div>
                                                <div>
                                                    <v-btn
                                                        class="mx-2" fab dark small color="primary"
                                                        @click="addExplanation(section_key)"
                                                    >
                                                        <v-icon dark>mdi-plus</v-icon>
                                                    </v-btn>
                                                </div>
                                            </div>
                                            <v-container>
                                                <draggable v-model="section_item.grammer_explanations" class="w-100">
                                                    <div v-for="(item , key) in section_item.grammer_explanations" :key="key">
                                                        <v-row>
                                                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                                                <v-text-field
                                                                    v-model="item.title"
                                                                    outlined clearable
                                                                    :error-messages="errors[`grammer_sections.${section_key}.grammer_explanations.${key}.title`] ? errors[`grammer_sections.${section_key}.grammer_explanations.${key}.title`] : null"
                                                                    dense :label="'عنوان توضیح ' + (key + 1)"
                                                                ></v-text-field>
                                                            </v-col>
                                                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                                                <v-select
                                                                    v-model="item.type"
                                                                    :items="grammer_explanation_types"
                                                                    item-text="title" item-value="value"
                                                                    outlined clearable
                                                                    :append-outer-icon="item.id ? '' : 'mdi-delete'"
                                                                    @click:append-outer="removeExplanation(key, section_key)"
                                                                    :error-messages="errors[`grammer_sections.${section_key}.grammer_explanations.${key}.type`] ? errors[`grammer_sections.${section_key}.grammer_explanations.${key}.type`] : null"
                                                                    dense :label="'نوع توضیح ' + (key + 1)"
                                                                ></v-select>
                                                            </v-col>
                                                            <v-col cols="12" xs="12" sm="12" class="pb-0">
                                                                <v-textarea
                                                                    v-model="item.content"
                                                                    outlined clearable
                                                                    :error-messages="errors[`grammer_sections.${section_key}.grammer_explanations.${key}.content`] ? errors[`grammer_sections.${section_key}.grammer_explanations.${key}.content`] : null"
                                                                    dense :label="'متن توضیح ' + (key + 1)"
                                                                ></v-textarea>
                                                            </v-col>
                                                        </v-row>

                                                        <div class="py-3 text-primary">
                                                            <small>مثال برای توضیح</small>
                                                        </div>
                                                        <div v-for="(example , example_key) in item.grammer_examples" style="padding-inline: .5rem; border-radius: .5rem; background: #8647ff29; margin-bottom: 1.5rem;">
                                                            <v-row>
                                                                <v-col cols="12" xs="12" sm="12" class="pb-0">
                                                                    <v-text-field
                                                                        v-model="example.english_content"
                                                                        outlined clearable
                                                                        dense :label="'متن انگلیسی مثال ' + (example_key + 1)"
                                                                        :error-messages="errors[`grammer_sections.${section_key}.grammer_explanations.${key}.grammer_examples.${example_key}.english_content`] ? errors[`grammer_sections.${section_key}.grammer_explanations.${key}.grammer_examples.${example_key}.english_content`] : null"
                                                                    ></v-text-field>
                                                                </v-col>
                                                                <v-col cols="12" xs="12" sm="12" class="pb-0">
                                                                    <v-text-field
                                                                        v-model="example.persian_content"
                                                                        outlined clearable
                                                                        append-outer-icon="mdi-delete"
                                                                        @click:append-outer="removeExample(key , example_key, section_key)"
                                                                        dense :label="'متن فارسی ' + (example_key + 1)"
                                                                        :error-messages="errors[`grammer_sections.${section_key}.grammer_explanations.${key}.grammer_examples.${example_key}.persian_content`] ? errors[`grammer_sections.${section_key}.grammer_explanations.${key}.grammer_examples.${example_key}.persian_content`] : null"
                                                                    ></v-text-field>
                                                                </v-col>
                                                            </v-row>
                                                        </div>
                                                        <div class="d-flex justify-end mb-3">
                                                            <v-btn x-small color="primary" dark @click="addExample(key, section_key)">افزودن مثال </v-btn>
                                                        </div>
                                                        <hr style="margin-block: 8px; border-style: dashed;">
                                                    </div>
                                                </draggable>
                                            </v-container>
                                        </v-expansion-panel-content>
                                    </v-expansion-panel>
                                </draggable>
                            </v-expansion-panels>
                        </div>
                    </v-tab-item>
                </v-tabs-items>

                <div class="text-center pt-3 mb-3">
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="success"
                        @click="updateGrammer()"
                    >ویرایش</v-btn>
                </div>

            </v-container>
        </div>

        <v-dialog
            max-width="600"
            v-model="join_modal"
        >
            <v-card>
                <v-toolbar color="accent" dark class="d-flex justify-space-between">
                    اتصال بخش با شناسه {{joinable_id}}
                </v-toolbar>
                <join-text-to-vendor v-if="join_modal" :joinable_id="joinable_id" joinable_type="grammer_section" @close="join_modal = false"></join-text-to-vendor>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
import draggable from 'vuedraggable'

export default {
    name:'edit_grammer',
    components: {
        draggable,
    },
    data: () => ({
        tab:'main',
        rules_filter:{},
        prerequisites_filter:{
            search_key:''
        },
        form_data:{
            grammer_explanations: [
                {
                    type:'',
                    title:'',
                    content:'',
                    grammer_examples: []
                }
            ]
        },
        errors:{},
        grammers_list:[],
        grammer_explanation_types: [
            {title:'توضیح', value:'explain'},
            {title:'نکته', value:'tip'},
            {title:'هشدار', value:'attention'},
        ],
        rules_list:[],
        loading: false,
        show_joins: 0,
        join_modal: false,
        joinable_id: null,
    }),
    watch:{
        rules_filter: {
            handler(){
                this.getGrammerRulesList();
            },
            deep: true
        },
        prerequisites_filter: {
            handler(){
                this.getGrammersList();
            },
            deep: true
        }
    },
    methods:{
        refresh(){
            this.getGrammer(this.$route.params.id);
        },
        addSection(){
            this.form_data.grammer_sections.push({
                title:'',
                grammer_explanations: [
                    {
                        type:'',
                        title:'',
                        content:'',
                        grammer_examples: []
                    }
                ]
            })
            return true;
        },
        removeSection(key){
            if(this.form_data.grammer_sections.length === 1){
                alert('حداقل یک بخش باید تعریف شود');
                return false;
            }
            this.form_data.grammer_sections.splice(key , 1)
            return true;
        },
        addExplanation(key){
            this.form_data.grammer_sections[key].grammer_explanations.push({
                type:'',
                title:'',
                content:'',
                grammer_examples: []
            })
            return true;
        },
        removeExplanation(key , section_key){
            if(this.form_data.grammer_sections[section_key].grammer_explanations.length === 1){
                alert('حداقل یک توضیح باید تعریف شود');
                return false;
            }
            this.form_data.grammer_sections[section_key].grammer_explanations.splice(key , 1)
            return true;
        },
        addExample(key , section_key){
            this.form_data.grammer_sections[section_key].grammer_explanations[key].grammer_examples.push({
                english_content:'',
                persian_content:''
            })
            return true;
        },
        removeExample(key , example_key , section_key){
            this.form_data.grammer_sections[section_key].grammer_explanations[key].grammer_examples.splice(example_key , 1)
            return true;
        },
        getRuleTitle(item){
            if (parseInt(item.apply_method) === 1 && item.proccess_method === 1  && !item.map_reason ) {
                console.log(item)
            }
            if (parseInt(item.apply_method) === 1) {
                if (item.proccess_method === 1) {
                    return item.id + " - جستجو در مپ - علت مپ: " + item.map_reason.english_title;
                } else if (item.proccess_method === 2) {
                    return item.id + " - جستجو در متن - " + (item.words && item.words.length > 20 ? item.words.slice(0,20) + ' ...' : item.words) + ' - ' + item.type;
                } else if (item.proccess_method === 3) {
                    return item.id + " - جستجو در نوع لغت - " + item.type;
                }
            } else if (parseInt(item.apply_method) === 2) {
                return item.id + " - اعمال گروهی - " + item.type;
            }
        },
        getGrammer(id){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`grammers/single` , {id})
                .then(res => {
                    this.form_data = res.data.data;
                    this.getGrammerRulesList();
                })
                .catch( () => {
                    this.$router.push({name:'grammers'})
                }).finally(()=>{
                this.$store.commit('SHOW_APP_LOADING' , 0)
            });
        },
        updateGrammer(){
            this.loading = true;
            this.$http.post(`grammers/update` , this.form_data)
                .then(res => {
                    this.form_data = {};
                    this.loading = false;
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.$router.push({name:'grammers'})
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
        getGrammersList(){
            this.$http.post(`grammers/list?page=1` , this.prerequisites_filter)
                .then(res => {
                    this.grammers_list = res.data.data.data
                })
                .catch( err => {
                    console.log(err)
                });
        },
        getGrammerRulesList(){
            if (this.form_data.rules) {
                const rules = [];
                this.form_data.rules.forEach((val) => rules.push(val.id));
                this.rules_filter.rule_ids = rules;
            }
            this.rules_filter.no_page = true;
            this.$http.post(`grammers/rules/list?page=1` , this.rules_filter)
                .then(res => {
                    this.rules_list = res.data.data
                })
                .catch( err => {
                    console.log(err)
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
        this.setPageTitle('ویرایش گرامر')
    },
    mounted() {
        const id = this.$route.params.id;
        this.getGrammer(id);
        this.getGrammersList();
    }
}
</script>
