<template>
    <div>

        <div class="page-head">
            <div class="titr">پردازش متن</div>
            <div class="back">
            </div>
        </div>

        <v-container>
            <v-row class="md-section">

                <v-col cols="6" class="pb-0">
                    <v-pagination
                        v-model="current_page"
                        :length="last_page"
                        :total-visible="7"
                    ></v-pagination>
                </v-col>
                <v-col cols="4" class="pb-0">

                </v-col>

            </v-row>

            <v-tabs v-model="tab">
                <v-tabs-slider></v-tabs-slider>
                <v-tab href="#english">متن انگلیسی</v-tab>
                <v-tab href="#persian">متن فارسی</v-tab>
                <v-tab href="#word">لغات</v-tab>
            </v-tabs>
            <v-tabs-items v-model="tab">
                <v-tab-item value="english">
                    <v-row>
                        <v-col cols="12" sm="12" class="text-left">
                            <v-btn color="primary" dark @click="applyChanges('english')">
                                اعمال تغییرات
                            </v-btn>
                        </v-col>
                    </v-row>
                    <v-simple-table
                        fixed-header
                        height="100%"
                        style="height:100%;direction: ltr;"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>عبارت اصلی</th>
                                <th>عبارت تغییر کرده</th>
                                <th>ویرایش</th>
                            </tr>
                            </thead>
                            <div class="fetch-loading">
                                <v-progress-linear
                                    v-if="fetch_loading"
                                    indeterminate
                                    color="cyan"
                                ></v-progress-linear>
                            </div>
                            <tbody>
                            <tr
                                v-for="item in list"
                                :key="item.id"
                            >
                                <td>{{ item.id }}</td>
                                <td>{{ item.text_english }}</td>
                                <td>
                                    <template v-if="item.enable_edit">
                                        <textarea class="inline-text-area" rows="8" v-model="item.changed_text_english_raw"></textarea>
                                    </template>
                                    <template v-else>
                                        <span v-html="item.changed_text_english"></span>
                                    </template>
                                </td>
                                <td>
                                    <template v-if="item.enable_edit">
                                        <v-btn color="success" fab dark small dens @click="item.changed_text_english = item.changed_text_english_raw, item.enable_edit = false">
                                            <v-icon>mdi-check</v-icon>
                                        </v-btn>
                                        <v-btn color="error" fab dark small dens @click="item.changed_text_english_raw = item.changed_text_english_raw_safe, item.enable_edit = false">
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </template>
                                    <template v-else>
                                        <v-btn v-if="item.english_changed" color="teal" fab dark small dens @click="item.enable_edit = true">
                                            <v-icon>mdi-pencil</v-icon>
                                        </v-btn>
                                    </template>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-tab-item>
                <v-tab-item value="persian">
                    <v-row>
                        <v-col cols="12" sm="12" class="text-left">
                            <v-btn color="primary" dark @click="applyChanges('persian')">
                                اعمال تغییرات
                            </v-btn>
                        </v-col>
                    </v-row>
                    <v-simple-table
                        fixed-header
                        height="100%"
                        style="height:100%"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>عبارت اصلی</th>
                                <th>عبارت تغییر کرده</th>
                                <th>ویرایش</th>
                            </tr>
                            </thead>
                            <div class="fetch-loading">
                                <v-progress-linear
                                    v-if="fetch_loading"
                                    indeterminate
                                    color="cyan"
                                ></v-progress-linear>
                            </div>
                            <tbody>
                            <tr
                                v-for="item in list"
                                :key="item.id"
                            >
                                <td>{{ item.id }}</td>
                                <td>{{ item.text_persian }}</td>
                                <td>
                                    <template v-if="item.enable_edit">
                                        <textarea class="inline-text-area" rows="8" v-model="item.changed_text_persian_raw"></textarea>
                                    </template>
                                    <template v-else>
                                        <span v-html="item.changed_text_persian"></span>
                                    </template>
                                </td>
                                <td>
                                    <template v-if="item.enable_edit">
                                        <v-btn color="success" fab dark small dens @click="item.changed_text_persian = item.changed_text_persian_raw, item.enable_edit = false">
                                            <v-icon>mdi-check</v-icon>
                                        </v-btn>
                                        <v-btn color="error" fab dark small dens @click="item.changed_text_persian_raw = item.changed_text_persian_raw_safe, item.enable_edit = false">
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </template>
                                    <template v-else>
                                        <v-btn v-if="item.persian_changed" color="teal" fab dark small dens @click="item.enable_edit = true">
                                            <v-icon>mdi-pencil</v-icon>
                                        </v-btn>
                                    </template>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-tab-item>
                <v-tab-item value="word">
                    <v-row>
                        <v-col cols="12" sm="12" class="text-left">
                            <v-btn color="primary" dark @click="applyChanges('english' , true)">
                                اعمال تغییرات
                            </v-btn>
                        </v-col>
                    </v-row>
                    <v-simple-table
                        fixed-header
                        height="100%"
                        style="height:100%;direction: ltr;"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>عبارت </th>
                                <th>تغییر </th>
                            </tr>
                            </thead>
                            <div class="fetch-loading">
                                <v-progress-linear
                                    v-if="fetch_loading"
                                    indeterminate
                                    color="cyan"
                                ></v-progress-linear>
                            </div>
                            <tbody>
                            <tr
                                v-for="item in list"
                                :key="item.id"
                            >
                                <td style="width: 40px;">{{ item.id }}</td>
                                <td style="width: 300px;">
                                    <span style="unicode-bidi: plaintext;" v-html="item.untranslated_words_text"></span>
                                </td>
                                <td style="width: 300px;">
                                    <template v-if="item.untranslated_words_changed">
                                        <textarea class="inline-text-area" rows="2" v-model="item.raw_untranslated_words_text"></textarea>
                                    </template>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-tab-item>
            </v-tabs-items>

            <div class="text-center">
                <v-pagination
                    v-model="current_page"
                    :length="last_page"
                    :total-visible="7"
                ></v-pagination>
            </div>

        </v-container>

    </div>

</template>
<script>
export default {
    name:'replace_rules',
    data: () => ({
        id:0,
        type:'music',
        list:[],
        replace_rules:[],
        form_data:{},
        filter:{},
        rules_filter:{},
        errors:{},
        tab:'english',
        current_page:1,
        last_page:0,
        edit_loading:false,
        fetch_loading:false,
    }),
    watch:{
        current_page(){
            this.getList();
        }
    },
    methods:{
        getList(){
            this.fetch_loading = true;
            this.$http.post(`replace/process-text` , {
                id:this.id,
                type:this.type,
                page:this.current_page,
            })
                .then(res => {
                    const response = res.data.data;
                    this.list = response.data;
                    this.last_page = response.last_page;
                    this.fetch_loading = false
                })
                .catch( err => {
                    this.fetch_loading = false;
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
        applyChanges(text_type , untranslated = false){
            let list = [];
            if(text_type === 'english') {
                for (let i = 0; i < this.list.length; i++) {
                    if (untranslated) {
                        if (this.list[i].untranslated_words_changed) {
                            list.push({
                                id: this.list[i].id,
                                text: this.list[i].raw_untranslated_words_text
                            });
                        }
                    } else {
                        if (this.list[i].english_changed) {
                            list.push({
                                id: this.list[i].id,
                                text: this.list[i].changed_text_english_raw
                            });
                        }
                    }
                }
            } else if (text_type === 'persian') {
                for (let i = 0; i < this.list.length; i++) {
                    if (this.list[i].persian_changed) {
                        list.push({
                            id: this.list[i].id,
                            text: this.list[i].changed_text_persian_raw
                        });
                    }
                }
            } else {
                alert('خطا هنگام اعمال تغییرات');
            }

            this.edit_loading = true;
            this.$http.post(`replace/apply` , {
                id:this.id,
                type:this.type,
                apply_on:text_type,
                texts:list,
            })
                .then( () => {
                    let route_name;
                    if (this.type === 'music') {
                        route_name = 'edit_music_text';
                    } else {
                        route_name = 'edit_movie_text';
                    }
                    this.$router.push({
                        name:route_name,
                        params:{
                            id:this.id
                        }
                    });
                })
                .catch( err => {
                    this.edit_loading = false;
                    const e = err.response.data
                    if(e.errors){ this.errors = e.errors }
                    if(e.message){
                        this.$fire({
                            title: "خطا",
                            text: e.message,
                            type: "error",
                            timer: 5000
                        })
                    }
                });
        },
        reset(){
            this.current_page = 1
            this.list = []
            this.getList()
        }
    },
    mounted(){
        this.id = this.$route.params.id;
        this.type = this.$route.params.type;
        this.getList();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('پردازش متن')
    }
}
</script>
<style>
</style>
