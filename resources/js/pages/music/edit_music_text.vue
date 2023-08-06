<template>
  <div>

    <div class="page-head">
      <div class="titr">ویرایش متن موزیک</div>
      <div class="back">
        <router-link :to="{ name : 'musics' }">بازگشت
          <v-icon>
            mdi-chevron-left
          </v-icon>
        </router-link>
      </div>
    </div>

    <v-container>
      <v-row>
        <v-col cols="4">

          <v-text-field
            v-model="music_id"
            outlined
            readonly
            dense
            label="شناسه آهنگ"
          ></v-text-field>

        </v-col>
        <v-col cols="4">

          <v-btn
            color="accent"
            dense
            :loading="loading" :disabled="loading"
            @click="saveData"
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
                <th style="width: 100px;">شروع</th>
                <th style="width: 100px;">پایان</th>
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
                    outlined
                    dense
                  ></v-textarea>
                </td>
                <td>
                  <v-textarea
                    v-model="row.text_persian"
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
                <td>
                  <v-text-field
                    v-model="row.start_time"
                    outlined
                    dense
                  ></v-text-field>
                </td>
                <td>
                  <v-text-field
                    v-model="row.end_time"
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
</template>
<script>
export default {
  name:'edit_music_text',
  data: () => ({
    rows: [
      {text_english: '', text_persian: "", description: "", start_time: '', end_time: ''},
    ],
    music_id:null,
    music:{},
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
    getMusicData(){
      this.$http.post(`texts/list` , { id_music:this.music_id })
      .then(res => {
          const txt = res.data.data
          if(txt.length > 0){
              this.rows = res.data.data
          }
      })
      .catch( () => {
      });
    },
    saveData(){
        this.loading = true
      this.$http.post(`texts/update` ,
      {
        music_id:this.music_id,
        texts:this.rows,
      }
      ).then(res => {

          this.$fire({
              title: "موفق",
              text: res.data.message,
              type: "success",
              timer: 5000
          })

          this.$router.push({name:'musics'})

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
  },
  mounted(){
    this.music_id = this.$route.params.id;
    this.getMusicData();
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
