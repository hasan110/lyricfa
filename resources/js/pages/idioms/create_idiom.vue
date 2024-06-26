<template>
    <div>

        <div class="page-head">
            <div class="titr">ایجاد اصطلاح</div>
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
                    <v-col cols="12" xs="12" sm="12" class="pb-0">
                        <v-text-field
                            v-model="form_data.base"
                            outlined clearable
                            :error-messages="errors.base"
                            dense label="لغت پایه"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xs="12" sm="12" class="pb-0">
                        <v-text-field
                            v-model="form_data.phrase"
                            :error-messages="errors.phrase"
                            outlined
                            clearable
                            dense
                            label="متن اصطلاح"
                        ></v-text-field>
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
                <v-container>
                    <div v-for="(item , key) in form_data.idiom_definitions" :key="key">
                        <v-row>
                            <v-col cols="12" xs="12" sm="12" class="pb-0">
                                <v-text-field
                                    v-model="item.definition"
                                    outlined clearable
                                    append-outer-icon="mdi-delete"
                                    @click:append-outer="removeDefinition(key)"
                                    :error-messages="errors[`idiom_definitions.${key}.definition`] ? errors[`idiom_definitions.${key}.definition`] : null"
                                    dense :label="'معنی ' + (key + 1)"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <div>
                            <small>مثال برای معنی</small>
                        </div>
                        <div v-for="(example , example_key) in item.idiom_definition_examples">
                            <v-row>
                                <v-col cols="12" xs="12" sm="6" class="pb-0">
                                    <v-text-field
                                        v-model="example.phrase"
                                        outlined clearable
                                        dense :label="'عبارت ' + (example_key + 1)"
                                        :error-messages="errors[`idiom_definitions.${key}.idiom_definition_examples.${example_key}.phrase`] ? errors[`idiom_definitions.${key}.idiom_definition_examples.${example_key}.phrase`] : null"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" xs="12" sm="6" class="pb-0">
                                    <v-text-field
                                        v-model="example.definition"
                                        outlined clearable
                                        append-outer-icon="mdi-delete"
                                        @click:append-outer="removeExample(key , example_key)"
                                        dense :label="'معنی عبارت ' + (example_key + 1)"
                                        :error-messages="errors[`idiom_definitions.${key}.idiom_definition_examples.${example_key}.definition`] ? errors[`idiom_definitions.${key}.idiom_definition_examples.${example_key}.definition`] : null"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </div>
                        <div class="d-flex justify-end">
                            <v-btn x-small color="primary" dark @click="addExample(key)">افزودن مثال </v-btn>
                        </div>
                        <hr style="margin-block: 8px;">
                    </div>
                </v-container>

                <div class="text-center pt-3">
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="success"
                        @click="saveIdiom()"
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
        form_data:{
            base: '',
            idiom_definitions: [
                {
                    definition:'',
                    idiom_definition_examples: []
                }
            ]
        },
        errors:{},
        loading: false,
    }),
    methods:{
        addDefinition(){
            this.form_data.idiom_definitions.push({
                definition:'',
                idiom_definition_examples: []
            })
            return true;
        },
        removeDefinition(definition_key){
            if(this.form_data.idiom_definitions.length == 1){
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
        saveIdiom(){
            this.loading = true;
            this.$http.post(`idioms/create` , this.form_data)
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
    mounted() {
        const word = this.$route.query.word;
        if (word) {
            this.form_data.base = word;
        }
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ایجاد اصطلاح')
    }
}
</script>
