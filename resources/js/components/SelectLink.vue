<template>
    <div>
        <v-btn small color="green darken-4" class="mx-2" dark @click="modal = true">
            لینک
        </v-btn>
        <v-dialog
            max-width="500"
            scrollable
            v-model="modal"
        >
            <v-card>
                <v-card-title>لینک
                    <template v-if="link_from_type === 'word_definition'">لغت</template>
                    <template v-if="link_from_type === 'idiom_definition'">عبارت</template>
                </v-card-title>
                <v-card-subtitle>با شناسه {{link_from_id}}</v-card-subtitle>
                <v-divider></v-divider>
                <v-card-text>
                    <v-row class="pt-2">
                        <v-col cols="12">
                            <v-select
                                label="نوع لینک"
                                hide-details clearable dense outlined
                                v-model="form_data.type"
                                :error-messages="errors.type"
                                :items="[{title:'مترادف',value:'synonym'},{title:'متضاد',value:'opposite'}]"
                                item-text="title" item-key="value"
                            ></v-select>
                        </v-col>
                    </v-row>
                    <v-row class="pt-2">
                        <v-col cols="12">
                            <v-select
                                label="لینک به"
                                hide-details clearable dense outlined
                                v-model="form_data.link_to_type"
                                :error-messages="errors.type"
                                :items="[{title:'معنی لغت',value:'word_definition'},{title:'معنی عبارت',value:'idiom_definition'}]"
                                item-text="title" item-key="value"
                            ></v-select>
                        </v-col>
                    </v-row>
                    <v-row v-if="form_data.link_to_type && form_data.link_to_type === 'word_definition'">
                        <v-col cols="12" sm="12" md="12">
                            <v-autocomplete
                                v-model="word_id"
                                outlined :items="words_list"
                                item-value="id" clearable
                                item-text="english_word"
                                :error-messages="errors.word_id"
                                label="جست و جوی لغت"
                                :search-input.sync="search_key"
                                :loading="search_loading"
                                no-filter dense
                                @keyup.enter="searchWord()"
                            ></v-autocomplete>
                        </v-col>
                        <v-col v-if="word_id" cols="12" sm="12" md="12">
                            <v-select
                                v-model="form_data.link_to_id"
                                outlined :items="word_definitions_list"
                                item-value="id" clearable
                                item-text="definition" dense
                                :error-messages="errors.link_to_id"
                                label="انتخاب معنی لغت"
                            ></v-select>
                        </v-col>
                    </v-row>
                    <v-row v-if="form_data.link_to_type && form_data.link_to_type === 'idiom_definition'">
                        <v-col cols="12" sm="12" md="12">
                            <v-autocomplete
                                v-model="idiom_id"
                                outlined :items="idioms_list"
                                item-value="id" clearable
                                item-text="phrase"
                                :error-messages="errors.idiom_id"
                                label="جست و جوی اصطلاح"
                                :search-input.sync="search_key"
                                :loading="search_loading"
                                no-filter dense
                                @keyup.enter="searchIdiom()"
                            ></v-autocomplete>
                        </v-col>
                        <v-col v-if="idiom_id" cols="12" sm="12" md="12">
                            <v-select
                                v-model="form_data.link_to_id"
                                outlined :items="idiom_definitions_list"
                                item-value="id" clearable
                                item-text="definition" dense
                                :error-messages="errors.link_to_id"
                                label="انتخاب معنی اصطلاح"
                            ></v-select>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn :loading="save_loading" color="indigo darken-4" dark @click="applyChanges()">
                        اعمال
                    </v-btn>
                    <v-btn color="danger" dark @click="modal = false">
                        بستن
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    name:'SelectLink',
    props: {
        link_from_id: {
            type: Number,
            default: 0,
        },
        link_from_type: {
            type: String,
            default: "",
        }
    },
    data: () => ({
        form_data:{},
        errors:{},
        search_key:null,
        word_id:null,
        idiom_id:null,
        words_list:[],
        word_definitions_list:[],
        idioms_list:[],
        idiom_definitions_list:[],
        search: null,
        loading:false,
        save_loading:false,
        search_loading: false,
        modal:false
    }),
    watch:{
        word_id() {
            if (!this.word_id) {
                this.word_definitions_list = [];
                return;
            }
            let word = this.words_list.findIndex(
                (temp) => temp['id'] === this.word_id
            );
            this.word_definitions_list = this.words_list[word].word_definitions;
        },
        idiom_id() {
            if (!this.idiom_id) {
                this.idiom_definitions_list = [];
                return;
            }
            let idiom = this.idioms_list.findIndex(
                (temp) => temp['id'] === this.idiom_id
            );
            this.idiom_definitions_list = this.idioms_list[idiom].idiom_definitions;
        },
    },
    methods:{
        searchWord(){
            if (!this.search_key) return;
            this.search_loading = true;
            this.$http.post(`words/list?page=1` , {
                search_key:this.search_key,
                equals:true,
            })
                .then(res => {
                    this.words_list = res.data.data.data
                    this.search_loading = false;
                })
                .catch( () => {
                    this.search_loading = false;
                });
        },
        searchIdiom(){
            if (!this.search_key) return;
            this.search_loading = true;
            this.$http.post(`idioms/list?page=1` , {
                search_key:this.search_key,
                equals:true,
            })
                .then(res => {
                    this.idioms_list = res.data.data.data
                    this.search_loading = false;
                })
                .catch( () => {
                    this.search_loading = false;
                });
        },
        applyChanges(){
            this.form_data.link_from_id = this.link_from_id;
            this.form_data.link_from_type = this.link_from_type;
            this.save_loading = true;
            this.$http.post(`links/add` , this.form_data)
            .then( () => {
                this.save_loading = false;
                this.modal = false;
                this.$emit('refresh')
                this.$fire({
                    title: "موفق",
                    text: 'تغییرات با موفقیت اعمال شد',
                    type: "success",
                    timer: 3000
                })
            })
            .catch( err => {
                this.save_loading = false;
                this.$fire({
                    title: "خطا",
                    text: err.response.data.message,
                    type: "error",
                    timer: 3000
                })
            });
        },
    },
    mounted(){
    }
}
</script>
