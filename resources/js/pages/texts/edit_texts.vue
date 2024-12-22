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
    }),
    watch:{
        current_page(){
            this.getTexts();
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
