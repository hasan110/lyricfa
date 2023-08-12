<template>
  <div>

    <div class="page-head">
      <div class="titr">ایجاد فیلم</div>
      <div class="back">
        <router-link :to="{ name : 'movies' }">بازگشت
          <v-icon>
            mdi-chevron-left
          </v-icon>
        </router-link>
      </div>
    </div>

    <v-container>
        <v-row class="justify-center">
            <v-col md="8" sm="12">
                <v-row>

                    <v-col cols="6" class="pb-0">
                        <v-text-field
                            v-model="form_data.english_name"
                            outlined
                            clearable
                            dense
                            label="عنوان انگلیسی فیلم"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="6" class="pb-0">
                        <v-text-field
                            v-model="form_data.persian_name"
                            outlined
                            clearable
                            dense
                            label="عنوان فارسی فیلم"
                        ></v-text-field>
                    </v-col>

                </v-row>
                <v-row>

                    <v-col cols="6" class="pb-0">
                        <v-select
                            label="نوع فیلم"
                            :items="movie_types"
                            v-model="form_data.type"
                            item-text="text"
                            item-value="value"
                            outlined
                            clearable
                            dense
                        ></v-select>
                    </v-col>
                    <v-col cols="6" class="pb-0">
                        <v-text-field
                            v-model="form_data.parent"
                            outlined
                            clearable
                            dense
                            label="آیدی فیلم مرتبط"
                        ></v-text-field>
                    </v-col>

                </v-row>
            </v-col>
        </v-row>

      <v-row>
        <v-col v-if="form_data.has_album" cols="8" class="pb-2">
          <v-textarea
            v-model="form_data.album_id"
            outlined
            dense
            label="توضیحات"
          ></v-textarea>
        </v-col>
      </v-row>

      <v-row>

        <v-col cols="12" class="pb-0 text-center">
          <v-btn
            color="accent"
            dense
            :loading="loading"
            :disabled="loading"
            @click="saveMovie()"
          >
            ثبت
          </v-btn>
        </v-col>

      </v-row>


    </v-container>
  </div>
</template>
<script>
export default {
  name:'create_movie',
  data: () => ({
    movie_types: [
        {text: 'فیلم سینمایی',value: 1},
        {text: 'سریال',value: 2},
        {text: 'فصل',value: 3},
        {text: 'قسمت',value: 4},
    ],
    form_data:{},
    errors:{},
    loading: false,
  }),
  methods:{
    saveMovie(){
      this.loading = true
      this.$http.post(`movies/create` , this.form_data)
      .then(res => {
        this.form_data = {};

        this.$fire({
          title: "موفق",
          text: res.data.message,
          type: "success",
          timer: 5000
        })

          this.$router.push({name:'movies'})

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
    }
  },
  mounted(){
  },
  beforeMount(){
    this.checkAuth()
  }
}
</script>
<style>
</style>
