<template>
  <div>

    <div class="page-head">
      <div class="titr">موزیک ها</div>
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

        <v-btn color="success" dens @click="$router.push({name:'create_music'})">
          افزودن آهنگ جدید
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
                <th>موزیک</th>
                <th>آمار</th>
                <th>امتیاز از 5</th>
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
                    <div class="d-flex align-center">

                        <div class="d-flex">
                            <template v-if="checkIfImageExists(Url + 'musics_banner/'+item.id+'.jpg')">
                                <img :src="Url + 'musics_banner/'+item.id+'.jpg'" class="item-profile m-1">
                            </template>
                        </div>

                        <div class="d-flex flex-column">
                            <span>{{item.name}}</span>
                            <span>{{item.persian_name}}</span>
                        </div>
                    </div>
                </td>
                <td>
                  <span class="pa-2">لایک : {{ item.num_like }}</span>
                  <span class="pa-2">کامنت : {{ item.num_comment }}</span>
                  <span class="pa-2">بازدید : {{ item.views }}</span>
                </td>
                <td>-</td>
                <td>
                  <v-btn color="primary" dens>
                    ویرایش آهنگ
                  </v-btn>
                  <v-btn color="primary" dens>
                    <router-link :to="{ name:'edit_music_text' , params:{ id:item.id } }">
                      ویرایش متن آهنگ
                    </router-link>
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
  </div>
</template>
<script>
export default {
  name:'musics',
  data: () => ({
    list:[],
    filter:{
        sort_by : 'newest'
    },
    errors:{},
    sort_by_list: [
        {text: 'جدید ترین ها',value: 'newest'},
        {text: 'قدیمی ترین ها',value: 'oldest'},
        {text: 'تاریخ انتشار',value: 'publish'},
        {text: 'آسان',value: 'easy'},
        {text: 'متوسط',value: 'normal'},
        {text: 'سخت',value: 'hard'},
        {text: 'خیلی سخت',value: 'expert'},
        {text: 'بیشترین بازدید',value: 'seen'},
        {text: 'آلبوم دار ها',value: 'has_album'},
    ],
    current_page:1,
    per_page:0,
    last_page:5,
  }),
  watch:{
    current_page(){
      this.getList();
    }
  },
  methods:{
    getList(){
      this.$http.post(`musics/list?page=${this.current_page}` , this.filter)
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
    checkIfImageExists(url) {
        const img = new Image();
        img.src = url;

        if (img.complete) {
            return true

        } else {
            img.onload = () => {
                return true

            };
            img.onerror = () => {
                return false

            };
        }
    }
  },
  mounted(){
      this.getList()
  },
  beforeMount(){
    this.checkAuth()
  }
}
</script>
<style>
</style>
