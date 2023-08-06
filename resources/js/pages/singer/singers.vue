<template>
  <div>

    <div class="page-head">
      <div class="titr">خواننده ها</div>
      <div class="back">
        <router-link :to="{ name : 'dashboard' }">بازگشت
          <v-icon>
            mdi-chevron-left
          </v-icon>
        </router-link>
      </div>
    </div>

    <v-container>
      <v-row class="md-section">

        <v-col cols="4" class="pb-0">

          <v-text-field
            v-model="filter.search_key"
            :append-outer-icon="'mdi-magnify'"
            outlined
            clearable
            dense
            label="جست و جو"
            type="text"
            @click:append-outer="reset()"
          ></v-text-field>

        </v-col>
        <v-col cols="4" class="pb-0">
        </v-col>
        <v-col cols="4" class="pb-0">
          <v-select
            label="مرتب سازی بر اساس"
            :items="sort_by_list"
            v-model="filter.sort_by"
            item-text="text"
            item-value="value"
            append-outer-icon="mdi-filter"
            outlined
            clearable
            autocomplete
            dense
            @click:append-outer="reset()"
          ></v-select>
        </v-col>

      </v-row>

      <div class="sm-section">

        <v-btn color="success" dens @click="create_modal = true">
          افزودن خواننده
        </v-btn>

      </div>

      <div class="main-section">
        <v-simple-table
          fixed-header
          height="100%"
          style="height:100%"
        >
          <template v-slot:default>
            <thead>
              <tr>
                <th>#</th>
                <th>عکس</th>
                <th>نام</th>
                <th>آمار</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in list"
                :key="item.id"
              >
                <td>{{ item.id }}</td>
                <td>
                    <img :src="Url + 'singers/'+item.id+'.jpg'" class="item-profile">
                <td>
                  <div class="d-flex flex-column">
                    <span>{{item.english_name}}</span>
                    <span>{{item.persian_name}}</span>
                  </div>
                </td>
                <td>
                  <span class="pa-2">لایک : {{ item.num_like }}</span>
                  <span class="pa-2">آهنگ ها : {{ item.num_musics }}</span>
                </td>
                <td>
                  <v-btn color="primary" dens>
                    <!-- <router-link :to="{ name:'edit_music_text' , params:{ id:item.id } }"> -->
                    ویرایش
                    <!-- </router-link> -->
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </div>

      <div class="sm-section">
        <v-pagination
          v-model="current_page"
          :length="last_page"
          :total-visible="7"
        ></v-pagination>
      </div>

    </v-container>

    <v-dialog
      transition="dialog-top-transition"
      max-width="600"
      v-model="create_modal"
    >
      <v-card>
        <v-toolbar
          color="accent"
          dark
        >افزودن خواننده</v-toolbar>
        <v-card-text>

          <v-container>

            <v-row class="pt-3">

              <v-col cols="6" class="pb-0">
                <v-text-field
                  v-model="form_data.persian_name"
                  outlined
                  clearable
                  dense
                  label="نام گروه یا خواننده به فارسی"
                ></v-text-field>
              </v-col>
              <v-col cols="6" class="pb-0">
                <v-text-field
                  v-model="form_data.english_name"
                  outlined
                  clearable
                  dense
                  label="نام گروه یا خواننده به انگلیسی"
                ></v-text-field>
              </v-col>

            </v-row>

            <v-row class="pt-3">

              <v-col cols="12" class="pb-0">
                <v-file-input
                  v-model="form_data.singer_picture"
                  outlined
                  show-size
                  dense
                  label="آپلود تصویر خواننده"
                  accept="image/*"
                ></v-file-input>
              </v-col>

            </v-row>

          </v-container>

        </v-card-text>

        <v-card-actions class="justify-end">
          <v-btn color="danger" @click="create_modal = false">بستن</v-btn>
          <v-btn color="success" @click="saveSinger()">ایجاد</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </div>
</template>
<script>
export default {
  name:'singers',
  data: () => ({
    list:[],
    form_data:{},
    filter:{},
    errors:{},
    sort_by_list: [
      {text: 'جدید ترین ها',value: 'newest'},
      {text: 'قدیمی ترین ها',value: 'oldest'},
      {text: 'نام فارسی',value: 'persian_name'},
      {text: 'نام انگلیسی',value: 'english_name'},
    ],
    current_page:1,
    per_page:0,
    last_page:5,
    create_modal:false,
  }),
  watch:{
    current_page(){
      this.getList();
    }
  },
  methods:{
    getList(){
      this.$http.post(`singers/list?page=${this.current_page}` , this.filter)
      .then(res => {
        this.list = res.data.data.data
        this.last_page = res.data.data.last_page;
      })
      .catch( () => {
      });
    },
    Search(e){
      if (e.keyCode === 13) {
        this.current_page = 1
        this.list = []
        this.getList()
      }
    },
    reset(){
        this.current_page = 1
        this.list = []
        this.getList()
    },
    saveSinger(){
      const d = new FormData();
      const x = this.form_data;

      x.english_name ? d.append('english_name', x.english_name) : '';
      x.persian_name ? d.append('persian_name', x.persian_name) : '';
      x.singer_picture ? d.append('singer_picture', x.singer_picture) : '';

      this.$http.post(`singers/create` , d)
      .then(res => {
        this.form_data = {};

        this.$fire({
          title: "موفق",
          text: res.message,
          type: "success",
          timer: 5000
        })
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
  },
  mounted(){
    this.getList();
  },
  beforeMount(){
    this.checkAuth()
  }
}
</script>
<style>
</style>
