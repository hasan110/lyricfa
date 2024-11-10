<template>
    <div>

        <div class="page-head">
            <div class="titr">ایجاد گرامر</div>
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
                                <v-expansion-panel v-for="(section_item, section_key) in form_data.grammer_sections" :key="section_key">
                                    <v-expansion-panel-header>
                                        <template v-if="section_item.title">
                                            {{section_item.title}}
                                        </template>
                                        <template v-else>بخش {{ section_key+1 }}</template>
                                    </v-expansion-panel-header>
                                    <v-expansion-panel-content>
                                        <v-row>
                                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                                <v-text-field
                                                    v-model="section_item.title" outlined
                                                    :error-messages="errors[`grammer_sections.${section_key}.title`] ? errors[`grammer_sections.${section_key}.title`] : null"
                                                    dense :label="'عنوان بخش ' + (section_key + 1)" hide-details
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                                <v-text-field
                                                    v-model="section_item.priority" outlined
                                                    :error-messages="errors[`grammer_sections.${section_key}.priority`] ? errors[`grammer_sections.${section_key}.priority`] : null"
                                                    dense :label="'اولویت بخش ' + (section_key + 1)" hide-details
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                                <v-select
                                                    v-model="section_item.level" outlined :items="levels"
                                                    :error-messages="errors[`grammer_sections.${section_key}.level`] ? errors[`grammer_sections.${section_key}.level`] : null"
                                                    dense :label="'سطح بخش ' + (section_key + 1)" hide-details
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12" xs="12" sm="6" class="pb-0 d-flex justify-end">
                                                <v-btn
                                                    dark small color="error"
                                                    @click="removeSection(section_key)"
                                                >
                                                    حذف بخش
                                                </v-btn>
                                            </v-col>
                                        </v-row>
                                        <div class="d-flex align-center justify-space-between mt-3 pa-2">
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
                                            <div v-for="(item , key) in section_item.grammer_explanations" :key="key">
                                                <v-row>
                                                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                                                        <v-text-field
                                                            v-model="item.title"
                                                            outlined clearable
                                                            :error-messages="errors[`grammer_sections.${section_key}.grammer_explanations.${key}.title`] ? errors[`grammer_sections.${section_key}.grammer_explanations.${key}.title`] : null"
                                                            dense :label="'عنوان ' + (key + 1)"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                                                        <v-select
                                                            v-model="item.type"
                                                            :items="grammer_explanation_types"
                                                            item-text="title" item-value="value"
                                                            outlined clearable
                                                            append-outer-icon="mdi-delete"
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
                                                <div>
                                                    <small>مثال برای توضیح</small>
                                                </div>
                                                <div v-for="(example , example_key) in item.grammer_examples">
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
                                                <div class="d-flex justify-end">
                                                    <v-btn x-small color="primary" dark @click="addExample(key, section_key)">افزودن مثال </v-btn>
                                                </div>
                                                <hr style="margin-block: 8px;">
                                            </div>
                                        </v-container>
                                    </v-expansion-panel-content>
                                </v-expansion-panel>
                            </v-expansion-panels>
                        </div>
                    </v-tab-item>
                </v-tabs-items>

                <div class="text-center pt-3 mb-3">
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="success"
                        @click="saveGrammer()"
                    >ایجاد</v-btn>
                </div>

            </v-container>
        </div>

    </div>
</template>
<script>
export default {
    name:'create_idiom',
    data: () => ({
        tab:'main',
        rules_filter:{
            search_key:''
        },
        prerequisites_filter:{
            search_key:''
        },
        form_data:{
            rules_level:[],
            grammer_sections: [
                {
                    title:'',
                    grammer_explanations: [
                        {
                            type:'',
                            title:'',
                            content:'',
                            grammer_examples: []
                        }
                    ]
                }
            ]
        },
        grammer_explanation_types: [
            {title:'توضیح', value:'explain'},
            {title:'نکته', value:'tip'},
            {title:'هشدار', value:'attention'},
        ],
        errors:{},
        grammers_list:[],
        rules_list:[],
        loading: false,
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
        saveGrammer(){
            this.loading = true;
            this.$http.post(`grammers/create` , this.form_data)
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
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ایجاد گرامر')
    },
    mounted() {
        this.getGrammersList();
        this.getGrammerRulesList();
    }
}
</script>
