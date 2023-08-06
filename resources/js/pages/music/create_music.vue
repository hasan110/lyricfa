<template>
  <div>

    <div class="page-head">
      <div class="titr">ایجاد موزیک</div>
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

        <v-col cols="4" class="pb-0">
          <v-text-field
            v-model="form_data.english_title"
            outlined
            clearable
            dense
            label="عنوان انگلیسی آهنگ"
          ></v-text-field>
        </v-col>
        <v-col cols="4" class="pb-0">
          <v-text-field
            v-model="form_data.persian_title"
            outlined
            clearable
            dense
            label="عنوان فارسی آهنگ"
          ></v-text-field>
        </v-col>
        <v-col cols="3" class="pb-0">
          <v-text-field
            v-model="singers_count"
            append-outer-icon="mdi-minus"
            @click:append-outer="toggleSingers(false)"
            prepend-icon="mdi-plus"
            @click:prepend="toggleSingers(true)"
            outlined
            dense
            label="تعداد خواننده ها"
            type="number"
            min="1"
            :max="max_number_of_singers"
            readonly
          ></v-text-field>
        </v-col>

      </v-row>

      <div class="pb-5">
        <div v-for="(item , key) in singers_count" :key="key">

          <v-row>
            <v-col cols="4" class="pb-0">
              <v-text-field
                v-model="form_data.singers[key]"
                outlined
                dense
                :label="'شناسه خواننده ' + (key+1)"
              ></v-text-field>
            </v-col>
          </v-row>

        </div>
      </div>

      <hr>
      <br>
      <v-row>
        <v-col cols="4" class="pb-0">
          <v-menu
            ref="menu"
            v-model="menu"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            min-width="auto"
          >
            <template #activator="{ on, attrs }">
              <v-text-field
                v-model="form_data.date_publication"
                label="تاریخ انتشار"
                outlined
                dense
                prepend-icon="mdi-calendar"
                readonly
                v-bind="attrs"
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              v-model="form_data.date_publication"
              :max="(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10)"
              min="1950-01-01"
            ></v-date-picker>
          </v-menu>
        </v-col>
        <v-col v-if="form_data.has_album" cols="4" class="pb-2">
          <v-text-field
            v-model="form_data.album_id"
            outlined
            dense
            label="شناسه آلبوم"
          ></v-text-field>
        </v-col>
        <v-col cols="2" class="pa-0">
          <v-checkbox
            v-model="form_data.has_album"
            label="آلبوم دارد؟"
          ></v-checkbox>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="8" class="pb-0">
          درجه سختی
          <v-radio-group
            v-model="form_data.hardest_degree"
            row
          >
            <v-radio
              label="آسان"
              value="1"
            ></v-radio>
            <v-radio
              label="متوسط"
              value="2"
            ></v-radio>
            <v-radio
              label="سخت"
              value="3"
            ></v-radio>
            <v-radio
              label="خیلی سخت"
              value="4"
            ></v-radio>
          </v-radio-group>
        </v-col>
      </v-row>

      <v-row>

        <v-col cols="4" class="pb-0">
          <v-file-input
            show-size
            dense
            outlined
            label="تصویر بنر"
            v-model="form_data.image"
            accept="image/*"
          ></v-file-input>
        </v-col>
        <v-col cols="4" class="pb-0">
          <v-file-input
            show-size
            dense
            outlined
            label="فایل موزیک"
            v-model="form_data.music"
            accept="audio/*"
          ></v-file-input>
        </v-col>

      </v-row>

      <v-row>

        <img v-if="form_data.image" :src="form_data.image" alt="">

      </v-row>

      <v-row>

        <v-col cols="2" class="pb-0">
          <v-text-field
            v-model="form_data.start_demo"
            outlined
            dense
            label="دموی آهنگ از"
          ></v-text-field>
        </v-col>

        <v-col cols="2" class="pb-0">
          <v-text-field
            v-model="form_data.end_demo"
            outlined
            dense
            label="دموی آهنگ تا"
          ></v-text-field>
        </v-col>

      </v-row>

      <v-row>

        <v-col cols="12" class="pb-0 text-center">
          <v-btn
            color="accent"
            dense
            :loading="loading"
            :disabled="loading"
            @click="saveMusic()"
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
  name:'create_music',
  data: () => ({
    form_data:{
      has_album: false,
      singers: [],
    },
    singers_count: 1,
    max_number_of_singers: 5,
    menu: false,
    errors:{},
    loading: false,
    banner_image: null,
  }),
  watch:{
    singers_count(){
      this.singers_count = parseInt(this.singers_count);
    }
  },
  methods:{
    toggleSingers(state){
      if(state){
        if(this.singers_count >= 5){
          return;
        }else{
          this.singers_count++;
        }
      }else{
        if(this.singers_count <= 1){
          return;
        }else{
          this.singers_count--;
        }
      }
    },
    changeBannerImage(event){
      this.banner_image = URL.createObjectURL(this.form_data.image);
      this.errors.image = null
    },
    getList(){
      this.$http.post(`musics/list?page=${this.current_page}` , this.filter)
      .then(res => {
        this.list = res.data.data.data
        this.last_page = res.data.data.last_page;
      })
      .catch( () => {
      });
    },
    saveMusic(){
      const d = new FormData();
      const x = this.form_data;

      x.english_title ? d.append('english_title', x.english_title) : '';
      x.persian_title ? d.append('persian_title', x.persian_title) : '';
      x.date_publication ? d.append('date_publication', x.date_publication) : '';
      x.has_album ? d.append('has_album', x.has_album) : '';
      x.album_id ? d.append('album_id', x.album_id) : '';
      x.hardest_degree ? d.append('hardest_degree', x.hardest_degree) : '';
      x.image ? d.append('image', x.image) : '';
      x.music ? d.append('music', x.music) : '';
      x.start_demo ? d.append('start_demo', x.start_demo) : '';
      x.end_demo ? d.append('end_demo', x.end_demo) : '';

      if(x.singers.length){

          x.singers[0] ? d.append('id_first_singer', x.singers[0]) : '';
          x.singers[1] ? d.append('id_second_singer', x.singers[1]) : '';
          x.singers[2] ? d.append('id_third_singer', x.singers[2]) : '';
          x.singers[3] ? d.append('id_fourth_singer', x.singers[3]) : '';

      }

      this.$http.post(`musics/create` , d)
      .then(res => {
        this.form_data = {
          has_album: false,
          singers: [],
        };

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
