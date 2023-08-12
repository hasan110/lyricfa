<template>
  <div>

    <div class="page-head">
      <div class="titr">ویرایش متن فیلم</div>
      <div class="back">
        <router-link :to="{ name : 'musics' }">بازگشت
          <v-icon>
            mdi-chevron-left
          </v-icon>
        </router-link>
      </div>
    </div>

    <div class="edit-texts-wrapper" id="edit-texts-wrapper">
        <v-container>
      <v-row>
        <v-col cols="4">

          <v-text-field
            v-model="movie_id"
            outlined
            readonly
            dense
            label="شناسه فیلم"
          ></v-text-field>

        </v-col>
        <v-col cols="4">

          <v-btn
            color="accent"
            dense
            :loading="loading" :disabled="loading"
            @click="saveData(0)"
          >
          ذخیره</v-btn>

        </v-col>
      </v-row>

      <div class="panel-body" id="app">
        <table>
            <thead>
              <tr>
                <th style="width: 5px;">#</th>
                <th style="width: 220px;">متن انگلیسی</th>
                <th style="width: 220px;">متن فارسی</th>
                <th style="width: 220px;">توضیحات</th>
                <th style="width: 300px;">شروع/پایان</th>
                <th style="width: 10px;">افزودن</th>
                <th style="width: 10px;">حذف</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row,index) in rows" :key="index">
                <td>
                    {{ index + 1 }}
                </td>
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
                    :background-color="(text_errors[index] && text_errors[index].text_persian ? 'red lighten-5' : null) || (!row.text_persian ? 'red lighten-5' : null)"
                    outlined
                    dense
                  ></v-textarea>
                </td>
                <td>
                  <v-textarea
                    v-model="row.comments"
                    outlined
                    dense
                  ></v-textarea>
                </td>
                <td class="font-en-input">
                  <v-text-field
                    v-model="row.start_end_time"
                    :background-color="(!row.start_end_time ? 'red lighten-5' : null)"
                    outlined
                    dense
                  ></v-text-field>
                </td>
                <td>
                  <v-btn
                    class="mx-2"
                    fab small
                    dark
                    @click="addRow(index)"
                    color="indigo"
                  >
                    <v-icon dark>
                      mdi-plus
                    </v-icon>
                  </v-btn>
                </td>
                <td >
                  <v-btn
                    class="mx-2"
                    fab small
                    dark
                    @click="removeRow(index)"
                    color="red"
                  >
                    <v-icon dark>
                      mdi-minus
                    </v-icon>
                  </v-btn>
                </td>
            </tr>
            </tbody>
        </table>
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

  </div>
</template>
<script>
export default {
  name:'edit_music_text',
  data: () => ({
    rows: [
      {text_english: '', text_persian: "", comments: "", start_end_time: ''},
    ],
    movie_id:null,
    text_errors:[],
    movie:{},
    errors:{},
    loading: false,
  }),
  methods:{
    addRow(index) {
      this.rows.splice(index + 1, 0, {});
    },
    removeRow(index) {
      if(this.rows.length === 1){ return; }
      this.rows.splice(index, 1);
    },
    getMovieData(){
      this.$store.commit('SHOW_APP_LOADING' , 1)
      this.$http.post(`film_texts/list` , { id_film:this.movie_id })
      .then(res => {
          const txt = res.data.data
          if(txt.length > 0){
              this.rows = res.data.data
          }
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
      this.$http.post(`film_texts/update` ,
      {
        film_id:this.movie_id,
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
              this.$router.push({name:'movies'})
          }

      })
      .catch( err => {
          this.loading = false
          const e = err.response.data
          // if(e.errors){ this.errors = e.errors }
          // else if(e.message){

              this.$fire({
                  title: "خطا",
                  text: e.message,
                  type: "error",
                  timer: 5000
              })

          // }
      });
    },
    validateInputs(){
          this.text_errors = []
          let ok = true;
          for (let i = 0; i < this.rows.length; i++) {
              let obj = {}
              const d = this.rows
              if(!d[i].text_english) { ok = false; }
              if(!d[i].text_persian) { ok = false; }
              if(!d[i].start_end_time) { ok = false; }

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
    this.movie_id = this.$route.params.id;
    this.getMovieData();
  },
  beforeMount(){
    this.checkAuth()
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
