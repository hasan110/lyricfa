<template>
    <div>

        <div class="page-head">
            <div class="titr">ویرایش متن
                <template v-if="type === 'film'">
                    فیلم
                </template>
                <template v-else-if="type === 'music'">
                    آهنگ
                </template>
            </div>
            <div class="back">
                <router-link :to="{ name : back_link }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <div class="edit-texts-wrapper" id="edit-texts-wrapper">
            <v-container fluid>
                <v-row>
                    <v-col cols="4">
                        <v-text-field
                            v-model="textable_id"
                            outlined
                            readonly
                            dense
                            label="شناسه"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="4">
                        <v-btn
                            color="accent"
                            dense
                            :loading="loading" :disabled="loading"
                            @click="saveData(1)"
                        >ذخیره</v-btn>
                    </v-col>
                    <v-col cols="4" class="text-right">
                        <v-btn color="deep-purple" dark dense :to="{name:'replace_texts', params:{id:textable_id, type}}">
                            پردازش متن
                        </v-btn>
                        <template v-if="type === 'film' && (parseInt(textable.persian_subtitle) === 0 || parseInt(textable.status) === 0)">
                            <v-btn color="success" dense @click="upload_modal = true">
                                آپلود متن از طریق فایل
                            </v-btn>
                        </template>
                        <template v-if="type === 'music' && parseInt(textable.status) === 0">
                            <v-btn color="success" dense @click="upload_modal = true">
                                آپلود متن از طریق فایل
                            </v-btn>
                        </template>
                        <v-tooltip top>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn color="primary" x-small fab v-bind="attrs" v-on="on" @click="download()">
                                    <v-icon>mdi-download</v-icon>
                                </v-btn>
                            </template>
                            <span>دانلود متن</span>
                        </v-tooltip>
                    </v-col>
                </v-row>

                <div class="panel-body" id="app">
                    <table>
                        <thead>
                        <tr>
                            <th style="width: 5px;">#</th>
                            <th style="width: 100px;">شناسه</th>
                            <th style="width: 220px;">متن انگلیسی</th>
                            <th style="width: 220px;">متن فارسی</th>
                            <th style="width: 120px;">شروع</th>
                            <th style="width: 120px;">پایان</th>
                            <th style="width: 10px;">افزودن</th>
                            <th style="width: 10px;">حذف</th>
                            <th style="width: 10px;">پخش</th>
                            <template v-if="type === 'film' && (parseInt(textable.persian_subtitle) === 1 && parseInt(textable.status) === 1)">
                                <th style="width: 10px;">اتصال</th>
                            </template>
                            <template v-if="type === 'music' && parseInt(textable.status) === 1">
                                <th style="width: 10px;">اتصال</th>
                            </template>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(row,index) in rows" :key="index">
                            <td>{{ index + 1 }}</td>
                            <td class="text-center">{{ row.id }}</td>
                            <td>
                                <v-textarea
                                    v-model="row.text_english"
                                    :background-color="(text_errors[index] && text_errors[index].text_english ? 'red lighten-5' : null) || (!row.text_english ? 'red lighten-5' : null)"
                                    outlined
                                    dense
                                ></v-textarea>
                            </td>
                            <td>
                                <v-textarea
                                    v-model="row.text_persian"
                                    outlined
                                    :background-color="(text_errors[index] && text_errors[index].text_persian ? 'red lighten-5' : null)"
                                    dense
                                ></v-textarea>
                            </td>
                            <td class="font-en-input">
                                <v-text-field
                                    v-model="row.start_time"
                                    :background-color="(!row.start_time ? 'red lighten-5' : null)"
                                    outlined
                                    dense
                                    type="number"
                                    step="100"
                                ></v-text-field>

                                <small class="red--text">
                                    {{ text_errors[index] && text_errors[index].start_time ? text_errors[index].start_time : null }}
                                </small>
                            </td>
                            <td class="font-en-input">
                                <v-text-field
                                    v-model="row.end_time"
                                    :background-color="(!row.end_time ? 'red lighten-5' : null)"
                                    outlined
                                    dense
                                    type="number"
                                    step="100"
                                ></v-text-field>
                            </td>
                            <td>
                                <v-btn class="mx-2" fab small dark @click="add_text.start_time = row.end_time , add_text_modal = true" color="indigo">
                                    <v-icon dark>
                                        mdi-plus
                                    </v-icon>
                                </v-btn>
                            </td>
                            <td >
                                <v-btn v-if="row.id" class="mx-2" fab small dark @click="deleteText(row)" color="red">
                                    <v-icon dark>
                                        mdi-minus
                                    </v-icon>
                                </v-btn>
                            </td>
                            <td >
                                <v-btn class="mx-2" fab small dark @click="play(row)" color="green">
                                    <v-icon dark>
                                        mdi-play
                                    </v-icon>
                                </v-btn>
                            </td>
                            <template v-if="type === 'film' && (parseInt(textable.persian_subtitle) === 1 && parseInt(textable.status) === 1)">
                                <td><v-btn class="mx-2" fab small dark @click="startJoin(row)" color="purple"><v-icon dark>mdi-arrow-collapse</v-icon></v-btn></td>
                            </template>
                            <template v-if="type === 'music' && parseInt(textable.status) === 1">
                                <td><v-btn class="mx-2" fab small dark @click="startJoin(row)" color="purple"><v-icon dark>mdi-arrow-collapse</v-icon></v-btn></td>
                            </template>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="sm-section">
                    <v-pagination
                        v-model="current_page"
                        :length="last_page"
                        :total-visible="7"
                    ></v-pagination>
                </div>
            </v-container>
        </div>

        <v-btn color="accent" fab dark bottom left small class="btn--float down" @click="scroll(0)">
            <v-icon>mdi-chevron-down</v-icon>
        </v-btn>
        <v-btn color="accent" fab dark bottom left small class="btn--float up" @click="scroll(1)">
            <v-icon>mdi-chevron-up</v-icon>
        </v-btn>
        <v-btn color="accent" fab dark bottom left small class="btn--float save" :loading="loading" @click="saveData(1)">
            <v-icon>mdi-content-save-check</v-icon>
        </v-btn>

        <v-dialog
            max-width="400"
            v-model="upload_modal"
        >
            <v-card>
                <v-toolbar
                    color="accent"
                    dark
                >آپلود متن از طریق فایل</v-toolbar>
                <v-card-text>

                    <v-container>
                        <v-row class="pt-3">
                            <v-col cols="12" class="pb-0">
                                <v-file-input
                                    label="فایل خود را انتخاب کنید"
                                    outlined
                                    dense
                                    v-model="form_data.lyrics"
                                    show-size
                                ></v-file-input>
                            </v-col>
                            <v-col v-if="type === 'film'" cols="12" class="pb-0">
                                <v-checkbox v-model="form_data.persian_subtitle" label="زیر نویس فارسی جداگانه؟"></v-checkbox>
                            </v-col>
                            <v-col v-if="form_data.persian_subtitle" cols="12" class="pb-0">
                                <v-file-input
                                    label="زیر نویس فارسی را انتخاب کنید"
                                    outlined
                                    dense
                                    v-model="form_data.persian_lyrics"
                                    show-size
                                ></v-file-input>
                            </v-col>
                        </v-row>

                    </v-container>

                </v-card-text>

                <v-card-actions class="justify-end">
                    <v-btn color="danger" dark @click="upload_modal = false">بستن</v-btn>
                    <v-btn
                        :loading="upload_loading"
                        :disabled="upload_loading"
                        color="success"
                        @click="upload()"
                    >آپلود</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog
            max-width="400"
            v-model="add_text_modal"
        >
            <v-card>
                <v-toolbar color="accent" dark>افزودن متن</v-toolbar>
                <v-card-text>
                    <v-container>
                        <v-row class="pt-3">
                            <v-col cols="12" class="pb-0">
                                <v-textarea
                                    label="متن انگلیسی"
                                    outlined
                                    dense
                                    v-model="add_text.text_english"
                                    :error-messages="errors.text_english"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" class="pb-0">
                                <v-textarea
                                    label="متن فارسی"
                                    outlined
                                    dense
                                    v-model="add_text.text_persian"
                                    :error-messages="errors.text_persian"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="6" class="pb-0">
                                <v-text-field
                                    label="شروع"
                                    v-model="add_text.start_time"
                                    :error-messages="errors.start_time"
                                    outlined
                                    dense
                                    type="number"
                                    step="100"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" class="pb-0">
                                <v-text-field
                                    label="پایان"
                                    v-model="add_text.end_time"
                                    :error-messages="errors.end_time"
                                    outlined
                                    dense
                                    type="number"
                                    step="100"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions class="justify-end">
                    <v-btn color="danger" dark @click="add_text_modal = false">انصراف</v-btn>
                    <v-btn
                        :loading="add_text_loading"
                        :disabled="add_text_loading"
                        color="success"
                        @click="addText()"
                    >افزودن</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog
            max-width="700"
            v-model="join_modal"
        >
            <v-card>
                <v-toolbar color="accent" dark>اتصال متن</v-toolbar>
                <v-card-text>
                    <v-container>
                        <div class="text-left">{{join_form_data.text_id}}</div>
                        <div class="text-left">{{join_form_data.text_english}}</div>
                        <div class="mb-8">{{join_form_data.text_persian}}</div>
                        <v-row v-if="join_form_data.text_id">
                            <v-col cols="12" sm="12" md="6">
                                <v-text-field
                                    label="تعداد متن قبل تر"
                                    v-model="join_form_data.text_before"
                                    outlined dense type="number"
                                    step="1" min="0" max="3"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="12" md="6">
                                <v-text-field
                                    label="تعداد متن بعد تر"
                                    v-model="join_form_data.text_after"
                                    outlined dense type="number"
                                    step="1" min="0" max="3"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="12" md="12">
                                <v-select
                                    label="اتصال متن به"
                                    v-model="join_form_data.join_to"
                                    outlined dense
                                    item-text="item" item-value="value"
                                    :items="[{'item':'معنی لغت','value':'word_definition'},{'item':'معنی اصطلاح','value':'idiom_definition'},{'item':'بخشی از گرامر','value':'grammer_section'}]"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-row v-if="join_form_data.join_to && join_form_data.join_to === 'word_definition'">
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
                                    no-filter
                                    @keyup.enter="searchWord()"
                                ></v-autocomplete>
                            </v-col>
                            <v-col v-if="word_id" cols="12" sm="12" md="12">
                                <v-select
                                    v-model="join_form_data.joinable_id"
                                    outlined :items="word_definitions_list"
                                    item-value="id" clearable
                                    item-text="definition"
                                    :error-messages="errors.joinable_id"
                                    label="انتخاب معنی لغت"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-row v-if="join_form_data.join_to && join_form_data.join_to === 'idiom_definition'">
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
                                    no-filter
                                    @keyup.enter="searchIdiom()"
                                ></v-autocomplete>
                            </v-col>
                            <v-col v-if="idiom_id" cols="12" sm="12" md="12">
                                <v-select
                                    v-model="join_form_data.joinable_id"
                                    outlined :items="idiom_definitions_list"
                                    item-value="id" clearable
                                    item-text="definition"
                                    :error-messages="errors.joinable_id"
                                    label="انتخاب معنی اصطلاح"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-row v-if="join_form_data.join_to && join_form_data.join_to === 'grammer_section'">
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
                                    no-filter
                                    @keyup.enter="searchGrammer()"
                                ></v-autocomplete>
                            </v-col>
                            <v-col v-if="grammer_id" cols="12" sm="12" md="12">
                                <v-select
                                    v-model="join_form_data.joinable_id"
                                    outlined :items="grammer_sections_list"
                                    item-value="id" clearable
                                    item-text="title"
                                    :error-messages="errors.joinable_id"
                                    label="انتخاب بخشی از گرامر"
                                ></v-select>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions class="justify-end">
                    <v-btn color="danger" dark @click="join_modal = false">انصراف</v-btn>
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="success"
                        @click="createJoin()"
                    >افزودن</v-btn>
                </v-card-actions>
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
    name:'edit_texts',
    data: () => ({
        rows: [
            {text_english: '', text_persian: "", start_time: '', end_time: ''},
        ],
        current_page:1,
        last_page:1,
        textable_id:null,
        type:null,
        back_link:null,
        text_errors:[],
        textable:{},
        join_form_data:{},
        errors:{},
        form_data:{
            lyrics:null,
            persian_lyrics:null,
            persian_subtitle:false,
        },
        add_text:{
            text_english:null,
            text_persian:null,
            start_time:null,
            end_time:null,
        },
        add_text_modal: false,
        upload_modal: false,
        upload_loading: false,
        add_text_loading: false,
        loading: false,
        video_wrapper: false,
        audio_wrapper: false,
        search_loading: false,
        join_modal: false,


        text_search:null,
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
    }),
    watch:{
        current_page(){
            this.getTexts();
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
        play(data) {
            if (!data.end_time || !data.start_time) {
                alert('تاریخ شروع و پایان مشخص نیست!!!');
                return;
            }

            let media = {};
            if (this.type === 'film') {
                this.video_wrapper = true;
                media = this.$refs.vid;
            } else {
                this.audio_wrapper = true;
                media = this.$refs.aud;
            }

            media.currentTime = data.start_time / 1000;
            media.play();
            const a = setInterval(function () {
                if(media.currentTime >= data.end_time / 1000) {
                    media.pause();
                    clearInterval(a)
                }
            }, 30);
        },
        startJoin(text) {
            this.join_form_data.text_id = text.id;
            this.join_form_data.text_english = text.text_english;
            this.join_form_data.text_persian = text.text_persian;
            this.join_modal = true;
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
            this.$http.post(`texts/join` , this.join_form_data).then(res => {
                this.$fire({
                    title: "موفق",
                    text: res.data.message,
                    type: "success",
                    timer: 5000
                })
                this.join_form_data = {}
                this.text_search = null;
                this.search_key = null;
                this.loading = false
                this.join_modal = false;
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
        addRow(index) {
            this.rows.splice(index + 1, 0, {});
        },
        removeRow(index) {
            if(this.rows.length === 1){ return; }
            this.rows.splice(index, 1);
        },
        getTexts(){
            this.$store.commit('SHOW_APP_LOADING' , 1)
            this.$http.post(`texts/list` , {
                textable_id:this.textable_id,
                type:this.type,
                page:this.current_page,
            })
            .then(res => {
                const texts = res.data.data.texts.data
                this.last_page = res.data.data.texts.last_page
                if(texts.length > 0){
                    this.rows = texts;
                }
                this.textable = res.data.data.textable;
                this.$store.commit('SHOW_APP_LOADING' , 0)
            })
            .catch( () => {
                this.$store.commit('SHOW_APP_LOADING' , 0)
            });
        },
        saveData(status){
            if(!this.validateInputs()){
                this.$fire({
                    title: "خطا",
                    text: 'اشکالی در وارد کردن اطلاعات وجود دارد',
                    type: "error",
                    timer: 5000
                })
                return;
            }
            this.loading = true
            this.$http.post(`texts/update` ,
                {
                    textable_id:this.textable_id,
                    type:this.type,
                    texts:this.rows,
                }
            ).then(res => {
                this.$fire({
                    title: "موفق",
                    text: res.data.message,
                    type: "success",
                    timer: 5000
                })
                if(status === 1){
                    this.loading = false
                }else {
                    if (this.type === 'film') {
                        this.$router.push({name:'movies'})
                    } else {
                        this.$router.push({name:'musics'})
                    }
                }
            })
            .catch( err => {
                this.loading = false
                const e = err.response.data
                this.$fire({
                    title: "خطا",
                    text: e.message,
                    type: "error",
                    timer: 5000
                })
            });
        },
        download(){
            this.$store.commit('SHOW_APP_LOADING' , 1);
            this.$http.post(`texts/download` , {id:this.textable_id,type:this.type})
                .then(res => {
                    this.$store.commit('SHOW_APP_LOADING' , 0);
                    const fileName = Date.now() + '-' + this.type + '-' + this.textable_id + '.srt'
                    this.generateFile(fileName , res.data);
                })
                .catch( () => {
                    this.$store.commit('SHOW_APP_LOADING' , 0);
                    this.$fire({
                        title: "خطا",
                        text: 'دانلود متن با مشکل مواجه شد.',
                        type: "error",
                        timer: 5000
                    })
                });
        },
        generateFile(filename, text) {
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
            element.setAttribute('download', filename);

            element.style.display = 'none';
            document.body.appendChild(element);

            element.click();

            document.body.removeChild(element);
        },
        upload(){
            const d = new FormData();
            d.append('id', this.textable_id);
            d.append('type', this.type);
            this.form_data.lyrics ? d.append('lyrics', this.form_data.lyrics) : '';
            this.form_data.persian_lyrics ? d.append('persian_lyrics', this.form_data.persian_lyrics) : '';
            d.append('persian_subtitle', this.form_data.persian_subtitle ? '1' : '0');
            this.upload_loading = true
            this.$http.post(`texts/upload` , d).then(res => {
                this.upload_loading = false
                this.upload_modal = false
                this.$fire({
                    title: "موفق",
                    text: 'متن با موفقیت اضافه شد.',
                    type: "success",
                    timer: 5000
                })

                this.current_page = 1;
                this.last_page = 1;
                this.getTexts();
            })
            .catch( () => {
                this.upload_loading = false
                this.$fire({
                    title: "خطا",
                    text: 'افزودن متن از طریق فایل با مشکل مواجه شد.',
                    type: "error",
                    timer: 5000
                })
            });
        },
        deleteText(text){
            if (confirm('آیا مطمئنید می خواهید این متن به طور کامل حذف شود؟')) {
                this.$store.commit('SHOW_APP_LOADING' , 1);
                this.$http.post(`texts/remove` , {id:text.id}).then( () => {
                    this.$fire({
                        title: "موفق",
                        text: 'متن با موفقیت حذف شد.',
                        type: "success",
                        timer: 5000
                    })
                    this.getTexts();
                })
                .catch( () => {
                    this.$fire({
                        title: "خطا",
                        text: 'حذف متن با مشکل مواجه شد.',
                        type: "error",
                        timer: 5000
                    })
                });
            }
        },
        addText(){
            this.add_text_loading = true;
            this.add_text.textable_id = this.textable_id;
            this.add_text.type = this.type;
            this.$http.post(`texts/add` , this.add_text).then( () => {
                this.$fire({
                    title: "موفق",
                    text: 'متن با موفقیت حذف شد.',
                    type: "success",
                    timer: 5000
                })
                this.getTexts();
                this.add_text_loading = false;
                this.add_text_modal = false;
                this.add_text = {};
            })
            .catch( err => {
                this.add_text_loading = false;
                const e = err.response.data
                if(e.errors){ this.errors = e.errors }
                else if(e.message){
                    this.$fire({
                        title: "خطا",
                        text: e.message,
                        type: "error",
                        timer: 3000
                    })
                }
            });
        },
        validateInputs(){
            this.text_errors = []
            let ok = true;
            for (let i = 0; i < this.rows.length; i++) {
                let obj = {}
                const d = this.rows
                if(!d[i].text_english) { ok = false; }
                // if(!d[i].text_persian) { ok = false; }
                if(!d[i].start_time) { ok = false; }
                if(!d[i].end_time) { ok = false; }

                this.text_errors.push(obj);
            }
            return ok;
        },
        scroll(status){
            const objDiv = document.getElementById("edit-texts-wrapper");
            if (status){
                objDiv.scrollTop = 0;
            }else {
                objDiv.scrollTop = objDiv.scrollHeight;
            }
        }
    },
    mounted(){
        this.textable_id = this.$route.params.textable_id;
        this.type = this.$route.params.type;

        this.back_link = 'musics';
        if (this.type === 'film') {
            this.back_link = 'movies';
        } else if (this.type === 'music') {
            this.back_link = 'musics';
        }
        this.getTexts();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ویرایش متن')
    }
}
</script>
<style scoped>

.inputFront {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 10px;
    margin-top: 15px;
    width: 100%;
}

.table > thead > tr > th {
    background-color: #fff;
    text-align: center;
}


.inputFront textarea {
    width: 200px;
    height: 100px;
    overflow-y: scroll;
    text-align: center;
}

.inputFront input {
    width: 90px;
    text-align: center;
}

.inputFront input, textarea {
    padding: 8px 6px;
    margin: 0 auto;
    border-radius: 5px;
    box-shadow: 2px 2px 2px #000;
    vertical-align: top;
}

.plus {
    border: 1px solid;
    padding: 8px;
    border-radius: 100%;
    cursor: pointer;
    margin: 3px;
    background-color: red;
    color: #fff;
}

.minus {
    border: 1px solid;
    padding: 8px;
    border-radius: 100%;
    cursor: pointer;
    margin: 3px;
    color: #fff;
    background-color: grey !important;
}

.inputFront tbody tr td {
    border-bottom: 1px solid #ccc;
}

.remo-add {
    text-align: center;
}

.remo-add i {
    position: relative;
    top: 35px;
}
.music_id{
    width: 150px;
    border-radius: 5px;
    border: 1px solid #ccc;
    height: 32px;
    text-align: left;
}
</style>
