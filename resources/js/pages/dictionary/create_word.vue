<template>
    <div>

        <div class="page-head">
            <div class="titr">
                ایجاد لغت
                <v-chip :to="{ name : 'create_idiom', query:{'word': form_data.english_word} }" small class="mr-2">ایجاد اصطلاح</v-chip>
                <v-chip :to="{ name : 'create_map', query:{'word': form_data.english_word} }" small class="mr-1">ایجاد مپ</v-chip>
            </div>
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
                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                        <v-text-field
                            v-model="form_data.english_word"
                            outlined clearable
                            :error-messages="errors.english_word"
                            dense label="لغت"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                        <v-text-field
                            v-model="form_data.pronunciation"
                            outlined
                            clearable
                            dense
                            label="تلفظ"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" class="pb-0">
                        <v-select
                            v-model="form_data.word_type"
                            outlined
                            :items="word_types"
                            dense multiple
                            label="نوع لغت"
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
                <v-container>
                    <div v-for="(item , key) in form_data.definitions" :key="key">
                        <v-row>
                            <v-col cols="12" xs="12" sm="12" class="pb-0">
                                <v-text-field
                                    v-model="item.definition"
                                    outlined clearable
                                    append-outer-icon="mdi-delete"
                                    @click:append-outer="removeDefinition(1 , key)"
                                    :error-messages="errors[`definitions.${key}.definition`] ? errors[`definitions.${key}.definition`] : null"
                                    dense :label="'معنی ' + (key + 1)"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <div>
                            <small>مثال برای معنی</small>
                        </div>
                        <div v-for="(example , example_key) in item.definition_examples">
                            <v-row>
                                <v-col cols="12" xs="12" sm="6" class="pb-0">
                                    <v-textarea
                                        v-model="example.phrase"
                                        outlined clearable rows="3"
                                        dense :label="'عبارت ' + (example_key + 1)"
                                        :error-messages="errors[`definitions.${key}.definition_examples.${example_key}.phrase`] ? errors[`definitions.${key}.definition_examples.${example_key}.phrase`] : null"
                                    ></v-textarea>
                                </v-col>
                                <v-col cols="12" xs="12" sm="6" class="pb-0">
                                    <v-textarea
                                        v-model="example.definition"
                                        outlined clearable rows="3"
                                        append-outer-icon="mdi-delete"
                                        @click:append-outer="removeExample(key , example_key)"
                                        dense :label="'معنی عبارت ' + (example_key + 1)"
                                        :error-messages="errors[`definitions.${key}.definition_examples.${example_key}.definition`] ? errors[`definitions.${key}.definition_examples.${example_key}.definition`] : null"
                                    ></v-textarea>
                                </v-col>
                            </v-row>
                        </div>
                        <div class="d-flex justify-end">
                            <v-btn x-small color="primary" dark @click="addExample(key)">افزودن مثال </v-btn>
                        </div>
                        <hr style="margin-block: 8px;">
                    </div>
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
                                    outlined clearable rows="3"
                                    :error-messages="errors[`english_definitions.${en_key}.definition`] ? errors[`english_definitions.${en_key}.definition`] : null"
                                    dense :label="'معنی انگلیسی ' + (en_key + 1)"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" xs="12" sm="6" class="pb-0">
                                <v-text-field
                                    v-model="item.pronunciation"
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
                    >ایجاد</v-btn>
                </div>

            </v-container>
        </div>

    </div>
</template>
<script>
export default {
    name:'create_word',
    data: () => ({
        form_data:{
            english_word:'',
            definitions: [
                {
                    definition:'',
                    definition_examples: []
                }
            ],
            english_definitions: [],
        },
        word_types: [],
        english_words_type:[
            {text:"article" , value:"article"},
            {text:"prefix" , value:"prefix"},
            {text:"adverb" , value:"adverb"},
            {text:"adjective" , value:"adjective"},
            {text:"interjection" , value:"interjection"},
            {text:"preposition" , value:"preposition"},
            {text:"verb" , value:"verb"},
            {text:"conjunction" , value:"conjunction"},
            {text:"pronoun" , value:"pronoun"},
            {text:"abbreviation" , value:"abbreviation"},
            {text:"suffix" , value:"suffix"}
        ],
        errors:{},
        loading: false,
    }),
    watch:{
        singers_count(){
            this.singers_count = parseInt(this.singers_count);
        }
    },
    methods:{
        addDefinition(type){
            if(type === 1){
                this.form_data.definitions.push({
                    definition:'',
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
                if(this.form_data.definitions.length == 1){
                    alert('حداقل یک معنی باید تعریف شود');
                    return false;
                }
                this.form_data.definitions.splice(definition_key , 1)
            }else if(type === 2){
                this.form_data.english_definitions.splice(definition_key , 1)
            }
            return true;
        },
        addExample(definition_key){
            this.form_data.definitions[definition_key].definition_examples.push({
                definition:'',
                phrase:''
            })
            return true;
        },
        removeExample(definition_key , example_key){
            this.form_data.definitions[definition_key].definition_examples.splice(example_key , 1)
            return true;
        },
        saveWord(){
            this.loading = true;
            if (this.form_data.word_types) {
                this.form_data.word_types = this.form_data.word_type.join(',')
            }
            this.$http.post(`words/create` , this.form_data)
                .then(res => {
                    this.form_data = {
                        definitions: [],
                        definition_examples: []
                    };
                    this.loading = false;
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
        },
        getWordTypes(){
            this.$http.get(`words/types`)
                .then(res => {
                    this.word_types = res.data.data
                })
                .catch( err => {
                    console.log(err)
                });
        }
    },
    mounted() {
        const word = this.$route.query.word;
        if (word) {
            this.form_data.english_word = word;
        }
        this.getWordTypes();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ایجاد لغت')
    }
}
</script>
