<template>
    <div>
        <v-card>
            <v-card-text>
                <v-row>
                    <v-col cols="12" sm="12" md="9" class="pb-0">
                        <v-autocomplete
                            v-model="form_data.text_id"
                            outlined :items="texts_list"
                            item-value="id" clearable
                            item-text="text_english"
                            :error-messages="errors.text_id"
                            label="جست و جوی متن"
                            :search-input.sync="text_search"
                            :loading="text_search_loading"
                            no-filter dense
                            @keyup.enter="searchText()"
                            :prepend-icon="search_for ? 'mdi-pencil' : null"
                            @click:prepend="text_search = search_for"
                        >
                            <template v-slot:item="{ item }">
                                <v-list-item-content style="max-width: 500px">
                                    <v-list-item-title v-text="item.text_english + '-' + item.id"></v-list-item-title>
                                    <v-list-item-subtitle v-if="item.text_persian">
                                        {{item.text_persian}}
                                    </v-list-item-subtitle>
                                    <v-list-item-subtitle v-if="item.textable_type.includes('Music')">
                                        آهنگ با شناسه {{item.textable.id}} --- {{item.textable.name}}
                                    </v-list-item-subtitle>
                                    <v-list-item-subtitle v-if="item.textable_type.includes('Film')">
                                        فیلم با شناسه {{item.textable.id}} --- {{item.textable.english_name}}
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                            </template>
                        </v-autocomplete>
                    </v-col>
                    <v-col cols="12" sm="12" md="3" class="pb-0">
                        <v-select
                            label="نوع جست و جو"
                            v-model="search_exact"
                            outlined dense
                            item-text="item" item-value="value"
                            :items="[{'item':'جدا جدا','value':0},{'item':'پشت سر هم','value':1}]"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" sm="12" md="9" class="pt-0">
                        <v-text-field
                            v-model="translate_text_search"
                            outlined dense
                            label="جست و جو در ترجمه"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="12" md="3" class="pt-0">
                        <v-select
                            label="جست و جو در"
                            v-model="text_search_type"
                            outlined dense
                            item-text="item" item-value="value"
                            :items="[{'item':'همه','value':null},{'item':'متن آهنگ','value':'music'},{'item':'متن فیلم','value':'film'}]"
                        ></v-select>
                    </v-col>
                </v-row>
                <div v-if="link_data.name" class="d-flex justify-end pb-4 mb-4">
                    <v-btn dark small color="danger" :to="link_data" target="_blank">
                        ویرایش متن انتخاب شده
                    </v-btn>
                </div>
                <v-row>
                    <v-col cols="12" sm="12" md="6">
                        <v-text-field
                            label="تعداد متن قبل تر"
                            v-model="form_data.text_before"
                            outlined dense type="number"
                            step="1" min="0" max="3"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="12" md="6">
                        <v-text-field
                            label="تعداد متن بعد تر"
                            v-model="form_data.text_after"
                            outlined dense type="number"
                            step="1" min="0" max="3"
                        ></v-text-field>
                    </v-col>
                    <template v-if="!joinable_id">
                        <v-col cols="12" sm="12" md="12">
                            <v-select
                                label="اتصال متن به"
                                v-model="form_data.join_to"
                                outlined dense
                                item-text="item" item-value="value"
                                :items="[{'item':'معنی لغت','value':'word_definition'},{'item':'معنی اصطلاح','value':'idiom_definition'},{'item':'بخشی از گرامر','value':'grammer_section'}]"
                            ></v-select>
                        </v-col>
                    </template>
                </v-row>
                <v-row v-if="!joinable_id && form_data.join_to && form_data.join_to === 'word_definition'">
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
                            :append-outer-icon="word_id ? 'mdi-pencil' : null"
                            @click:append-outer="redirectTo(word_id , 'word')"
                        ></v-autocomplete>
                    </v-col>
                    <v-col v-if="word_id" cols="12" sm="12" md="12">
                        <v-select
                            v-model="form_data.joinable_id"
                            outlined :items="word_definitions_list"
                            item-value="id" clearable
                            item-text="definition" dense
                            :error-messages="errors.joinable_id"
                            label="انتخاب معنی لغت"
                        ></v-select>
                    </v-col>
                </v-row>
                <v-row v-if="!joinable_id && form_data.join_to && form_data.join_to === 'idiom_definition'">
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
                            :append-outer-icon="idiom_id ? 'mdi-pencil' : null"
                            @click:append-outer="redirectTo(idiom_id , 'idiom')"
                        ></v-autocomplete>
                    </v-col>
                    <v-col v-if="idiom_id" cols="12" sm="12" md="12">
                        <v-select
                            v-model="form_data.joinable_id"
                            outlined :items="idiom_definitions_list"
                            item-value="id" clearable
                            item-text="definition" dense
                            :error-messages="errors.joinable_id"
                            label="انتخاب معنی اصطلاح"
                        ></v-select>
                    </v-col>
                </v-row>
                <v-row v-if="!joinable_id && form_data.join_to && form_data.join_to === 'grammer_section'">
                    <v-col cols="12" sm="12" md="12">
                        <v-autocomplete
                            v-model="grammer_id"
                            outlined :items="grammers_list"
                            item-value="id" clearable
                            item-text="english_name"
                            :error-messages="errors.grammer_id"
                            label="جست و جوی گرامر"
                            :search-input.sync="search_key"
                            :loading="search_loading"
                            no-filter dense
                            @keyup.enter="searchGrammer()"
                            :append-outer-icon="grammer_id ? 'mdi-pencil' : null"
                            @click:append-outer="redirectTo(grammer_id , 'grammer')"
                        ></v-autocomplete>
                    </v-col>
                    <v-col v-if="grammer_id" cols="12" sm="12" md="12">
                        <v-select
                            v-model="form_data.joinable_id"
                            outlined :items="grammer_sections_list"
                            item-value="id" clearable
                            item-text="title" dense
                            :error-messages="errors.joinable_id"
                            label="انتخاب بخشی از گرامر"
                        ></v-select>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-card-actions>
                <v-btn :disabled="!form_data.text_id" :loading="text_select_loading" color="success" @click="getSelectedTextInfo()">
                    پخش متن انتخاب شده
                </v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" :loading="loading" :disabled="!form_data.text_id" @click="createJoin()">
                    ثبت
                </v-btn>
            </v-card-actions>
        </v-card>

        <v-dialog
            max-width="500"
            scrollable
            v-model="selected_text_modal"
        >
            <v-card>
                <v-card-title>پخش متن ها</v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-list dense>
                        <v-list-item-group>
                            <v-list-item
                                v-for="(item , key) in selected_text_list" :key="key"
                                :style="{background: item.id === form_data.text_id ? '#a8f5ff' : 'transparent'}"
                                @click="play(item)"
                            >
                                <v-list-item-content>
                                    <v-list-item-title v-text="item.id + '-' + item.text_english"></v-list-item-title>
                                    <v-list-item-subtitle v-if="item.text_persian">
                                        {{item.text_persian}}
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list-item-group>
                    </v-list>
                </v-card-text>
            </v-card>
        </v-dialog>

        <div class="mini-player-wrapper" v-show="video_wrapper">
            <div class="close-mini-player-wrapper" @click="video_wrapper = !video_wrapper">
                <v-icon dark>
                    mdi-close
                </v-icon>
            </div>
            <video v-if="textable.id" height="100%" width="100%" ref="vid" controls>
                <source :src="textable.film_source" type="video/mp4" />
            </video>
        </div>
        <div class="mini-player-wrapper audio" v-show="audio_wrapper">
            <div class="close-mini-player-wrapper" @click="audio_wrapper = !audio_wrapper">
                <v-icon dark>
                    mdi-close
                </v-icon>
            </div>
            <audio v-if="textable.id" ref="aud" controls>
                <source :src="textable.music_source" type="audio/mp3" />
            </audio>
        </div>
    </div>
</template>

<script>
export default {
    name:'JoinTextToVendor',
    props: {
        joinable_id: {
            type: Number,
            default: 0,
        },
        joinable_type: {
            type: String,
            default: "",
        },
        search_for: {
            type: String,
            default: "",
        },
    },
    data: () => ({
        text_search:null,
        translate_text_search:null,
        search_exact:0,
        text_search_type:"film",
        search_key:null,
        word_id:null,
        idiom_id:null,
        grammer_id:null,
        texts_list:[],
        words_list:[],
        word_definitions_list:[],
        idioms_list:[],
        idiom_definitions_list:[],
        grammers_list:[],
        grammer_sections_list:[],
        selected_text_list:[],
        errors:{},
        link_data:{},
        textable:{},
        form_data:{
            text_id:null,
            join_to:null,
            joinable_id:null,
            text_before:0,
            text_after:0,
        },
        selected_text_modal: false,
        text_search_loading: false,
        text_select_loading: false,
        search_loading: false,
        loading: false,
        video_wrapper: false,
        audio_wrapper: false,
    }),
    watch:{
        text_search() {
            if (!this.form_data.text_id) {
                this.texts_list = [];
            }
        },
        'form_data.text_id': {
            handler (after, before) {
                if (this.form_data.text_id) {
                    let text = this.texts_list.findIndex(
                        (temp) => temp['id'] === this.form_data.text_id
                    );

                    let selected_text = this.texts_list[text];
                    let type = 'music';
                    if (selected_text.textable_type === 'App\\Models\\Film') {
                        type = 'film';
                    }
                    this.link_data = {
                        name : 'edit_texts',
                        params:{
                            textable_id:selected_text.textable_id,
                            type
                        },
                        query:{
                            'text_id': selected_text.id
                        }
                    }
                } else {
                    this.link_data = {};
                }
            },
            deep: true
        },
        selected_text_modal() {
            if (!this.selected_text_modal) {
                this.audio_wrapper = false;
                this.video_wrapper = false;
                this.textable = {};
            }
        },
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
        grammer_id() {
            if (!this.grammer_id) {
                this.grammer_sections_list = [];
                return;
            }
            let grammer = this.grammers_list.findIndex(
                (temp) => temp['id'] === this.grammer_id
            );
            this.grammer_sections_list = this.grammers_list[grammer].grammer_sections;
        }
    },
    methods:{
        redirectTo(id, type) {
            let route = null;
            if (type === 'word') {
                route = this.$router.resolve({'name':'edit_word' , params:{id}})
            }
            if (type === 'idiom') {
                route = this.$router.resolve({'name':'edit_idiom' , params:{id}})
            }
            if (type === 'grammer') {
                route = this.$router.resolve({'name':'edit_grammer' , params:{id}})
            }
            if (route) {
                window.open(route.href, '_blank');
            }
        },
        play(data) {
            if (!data.end_time || !data.start_time) {
                alert('تاریخ شروع و پایان مشخص نیست!!!');
                return;
            }
            this.textable = data.textable;

            const vm = this;
            setTimeout(function (){
                let media = {};
                if (data.textable_type === 'App\\Models\\Film') {
                    vm.video_wrapper = true;
                    media = vm.$refs.vid;
                } else {
                    vm.audio_wrapper = true;
                    media = vm.$refs.aud;
                }

                media.currentTime = data.start_time / 1000;
                media.play();
                const a = setInterval(function () {
                    if(media.currentTime >= data.end_time / 1000) {
                        media.pause();
                        clearInterval(a)
                    }
                }, 30);
            } , 100);
        },
        searchText(){
            this.text_search_loading = true;
            this.$http.post(`texts/search` , {
                search_word:this.text_search,
                translate_text_search:this.translate_text_search,
                search_type:this.text_search_type,
                search_exact:this.search_exact,
            })
                .then(res => {
                    this.texts_list = res.data.data
                    this.text_search_loading = false;
                })
                .catch( () => {
                    this.text_search_loading = false;
                });
        },
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
        searchGrammer(){
            if (!this.search_key) return;
            this.search_loading = true;
            this.$http.post(`grammers/list?page=1` , {
                search_key:this.search_key,
            })
                .then(res => {
                    this.grammers_list = res.data.data.data
                    this.search_loading = false;
                })
                .catch( () => {
                    this.search_loading = false;
                });
        },
        createJoin(){
            this.loading = true
            if (this.joinable_id) {
                this.form_data.joinable_id = this.joinable_id;
                this.form_data.join_to = this.joinable_type;
            }
            this.$http.post(`texts/join` , this.form_data).then(res => {
                this.$fire({
                    title: "موفق",
                    text: res.data.message,
                    type: "success",
                    timer: 5000
                })
                this.loading = false;

                // if (this.joinable_id) {
                //     this.$emit('close')
                // } else {
                //     setTimeout(function () {
                //         location.reload()
                //     } , 2000);
                // }
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
        },
        getSelectedTextInfo(){
            if(!this.form_data.text_id){
                this.$fire({
                    title: "خطا",
                    text: 'ابتدا متن را انتخاب کنید',
                    type: "error",
                    timer: 5000
                })
                return;
            }
            this.text_select_loading = true
            this.$http.post(`texts/load` ,
                {
                    text_id:this.form_data.text_id,
                }
            ).then(res => {
                this.selected_text_list = res.data.data;
                this.selected_text_modal = true;
                this.text_select_loading = false
            })
                .catch( err => {
                    this.text_select_loading = false
                    const e = err.response.data
                    this.$fire({
                        title: "خطا",
                        text: e.message,
                        type: "error",
                        timer: 5000
                    })
                });
        },
    },
    mounted(){
        if (this.search_for) {
            this.text_search = this.search_for;
        }
    }
}
</script>
