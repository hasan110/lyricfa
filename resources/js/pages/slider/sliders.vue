<template>
  <div>

    <div class="page-head">
      <div class="titr">اسلایدر ها</div>
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

        <v-btn color="success" dens @click="$router.push({name:'create_slider'})">
          افزودن اسلایدر جدید
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
                <th>بنر</th>
                <th>نوع</th>
                <th>نمایش</th>
                <th>عملیات</th>
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
                <td>
                    <div v-if="item.banner" class="d-flex align-center">
                        <img :src="Url + item.banner" style="width:60px;height:auto">
                    </div>
                </td>
                <td>
                    <template v-if="item.type === 1">آهنگ</template>
                    <template v-if="item.type === 2">آلبوم</template>
                    <template v-if="item.type === 3">خواننده</template>
                    <template v-if="item.type === 4">چند آهنگ</template>
                </td>
                  <td>
                      <template v-if="item.show_it">
                          <v-icon>
                              mdi-eye
                          </v-icon>
                      </template>
                      <template v-else>
                          <v-icon>
                              mdi-eye-off
                          </v-icon>
                      </template>
                  </td>
                <td>
                  <v-btn color="primary" dens>
                    <router-link :to="{ name:'edit_slider' , params:{ id:item.id } }">
                      ویرایش
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
  name:'sliders',
  data: () => ({
    list:[],
    filter:{
        sort_by : 'newest'
    },
    errors:{},
    sort_by_list: [
        {text: 'جدید ترین ها',value: 'newest'},
        {text: 'قدیمی ترین ها',value: 'oldest'},
    ],
    current_page:1,
    per_page:0,
    last_page:1,
    fetch_loading:false,
  }),
  watch:{
    current_page(){
      this.getList();
    }
  },
  methods:{
    getList(){
      this.fetch_loading = true
      this.$http.post(`sliders/list?page=${this.current_page}` , this.filter)
      .then(res => {
        this.list = res.data.data.data
        this.last_page = res.data.data.last_page;
        this.fetch_loading = false
      })
      .catch( () => {
        this.fetch_loading = false
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
