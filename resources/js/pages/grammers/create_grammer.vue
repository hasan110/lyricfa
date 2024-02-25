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
      <v-container>

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
              <v-col cols="12" xs="12" sm="12" class="pb-0">
                  <v-select
                      v-model="form_data.level"
                      outlined clearable
                      :error-messages="errors.level"
                      :items="['beginner', 'medium', 'advanced']"
                      dense label="سطح"
                  ></v-select>
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

          <div class="text-center pt-3">
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
    grammers_filter:{},
    rules_filter:{
        search_key:''
    },
    form_data:{
        rules_level:[]
    },
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
    }
  },
  methods:{
    getRuleTitle(item){
        if (item.proccess_method === 1) {
            return item.id + " - جستجو در مپ - علت مپ: " + item.map_reason.english_title;
        } else if (item.proccess_method === 2) {
            return item.id + " - جستجو در متن - " + (item.words && item.words.length > 20 ? item.words.slice(0,20) + ' ...' : item.words) + ' - ' + item.type;
        } else if (item.proccess_method === 3) {
            return item.id + " - جستجو در نوع لغت - " + item.type;
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
      this.$http.post(`grammers/list?page=1` , this.grammers_filter)
        .then(res => {
          this.grammers_list = res.data.data.data
        })
        .catch( err => {
          console.log(err)
        });
    },
    getGrammerRulesList(){
      this.$http.post(`grammers/rules/list?page=1` , this.rules_filter)
        .then(res => {
          this.rules_list = res.data.data.data
        })
        .catch( err => {
          console.log(err)
        });
    },
  },
  beforeMount(){
    this.checkAuth()
  },
  mounted() {
    this.getGrammersList();
    this.getGrammerRulesList();
  }
}
</script>
