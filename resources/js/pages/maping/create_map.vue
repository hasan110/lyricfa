<template>
    <div>

        <div class="page-head">
            <div class="titr">ایجاد مپ</div>
            <div class="back">
                <router-link :to="{ name : 'maps' }">بازگشت
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
                            v-model="form_data.word"
                            outlined clearable
                            :error-messages="errors.word"
                            dense label="لغت"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                        <v-select
                            :items="word_types"
                            v-model="form_data.word_type"
                            outlined clearable multiple
                            :error-messages="errors.word_types"
                            dense label="نوع لغت"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                        <v-text-field
                            v-model="form_data.base_word"
                            outlined clearable
                            :error-messages="errors.base_word"
                            dense label="لغت پایه"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" xs="12" sm="6" class="pb-0">
                        <v-select
                            :items="word_types"
                            v-model="form_data.base_word_type"
                            outlined clearable multiple
                            :error-messages="errors.base_word_types"
                            dense label="نوع لغت پایه"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" xs="12" sm="12" class="pb-0">
                        <v-autocomplete
                            chips deletable-chips dense multiple small-chips
                            v-model="form_data.map_reasons"
                            outlined :items="map_reasons"
                            item-value="id"
                            :item-text="getMapReasonTitle"
                            :error-messages="errors.map_reasons"
                            label="علت مپ شدن"
                        ></v-autocomplete>
                    </v-col>
                </v-row>

                <div class="text-center pt-3">
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="success"
                        @click="saveMap()"
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
        map_reasons_filter:{},
        form_data:{
            word: '',
        },
        word_types: [],
        errors:{},
        map_reasons:[],
        loading: false,
    }),
    methods:{
        getMapReasonTitle(item){
            return `${item.persian_title} - ${item.english_title}`;
        },
        saveMap(){
            this.loading = true;
            if (this.form_data.word_type) {
                this.form_data.word_types = this.form_data.word_type.join(',')
            }
            if (this.form_data.base_word_types) {
                this.form_data.base_word_types = this.form_data.base_word_type.join(',')
            }

            this.$http.post(`maps/create` , this.form_data)
                .then(res => {
                    this.form_data = {};
                    this.loading = false;
                    this.$fire({
                        title: "موفق",
                        text: res.data.message,
                        type: "success",
                        timer: 5000
                    })
                    this.$router.push({name:'maps'})
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
        getMapReasonsList(){
            this.$http.post(`maps/reasons/list?page=1` , this.map_reasons_filter)
                .then(res => {
                    this.map_reasons = res.data.data.data
                })
                .catch( err => {
                    console.log(err)
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
        },
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('ایجاد مپ لغت')
    },
    mounted() {
        const word = this.$route.query.word;
        if (word) {
            this.form_data.word = word;
        }
        this.getMapReasonsList();
        this.getWordTypes();
    }
}
</script>
