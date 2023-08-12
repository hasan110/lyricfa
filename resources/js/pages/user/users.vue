<template>
  <div>

    <div class="page-head">
      <div class="titr">کاربران</div>
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
            @click:append-outer="getList()"
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
            @click:append-outer="getList()"
          ></v-select>
        </v-col>

      </v-row>

      <div class="sm-section">


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
                <th>نام</th>
                <th>شماره تلفن</th>
                <th>اشتراک</th>
                <th>جزییات</th>
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
                v-for="(item , key) in list"
                :key="key"
              >
                <td>{{ item.id }}</td>
                <td>{{ item.full_name }}</td>
                <td>{{ item.phone_number }}</td>
                <td><div v-html="item.expire"></div></td>
                <td>
                  <v-btn color="primary" dens>
                    <router-link :to="{ name:'user' , params:{ id:item.id } }">
                      <v-icon>mdi-information-outline</v-icon>
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
  name:'users',
  data: () => ({
    list:{},
    filter:{},
    errors:{},
    sort_by_list: [
      {text: 'جدید ترین',value: 'newest'},
      {text: 'قدیمی ترین',value: 'oldest'},
      {text: 'بیشترین اشتراک',value: 'most_subscribed'},
      //{text: 'اشتراک طلایی',value: 'gold_plan'},
      //{text: 'اشتراک نقره ای',value: 'silver_plan'},
      //{text: 'اشتراک الماس',value: 'diamond_plan'},
    ],
    current_page:1,
    per_page:0,
    last_page:5,
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
      this.$http.post(`users/list?page=${this.current_page}` , this.filter)
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
  },
  mounted(){
      this.filter.sort_by = 'newest';
      this.getList();
  },
  beforeMount(){
    this.checkAuth()
  }
}
</script>
<style>
</style>
