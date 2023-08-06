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

        <v-btn color="success" dens>
          ایجاد
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
                <th>نام</th>
                <th>شماره تلفن</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in desserts"
                :key="item.name"
              >
                <td>{{ item.name }}</td>
                <td>{{ item.calories }}</td>
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
    desserts: [
      {
        name: 'Frozen Yogurt',
        calories: 159,
      },
      {
        name: 'Ice cream sandwich',
        calories: 237,
      },
      {
        name: 'Eclair',
        calories: 262,
      },
      {
        name: 'Cupcake',
        calories: 305,
      },
      {
        name: 'Gingerwrtybread',
        calories: 356,
      },
      {
        name: 'Jellty bean',
        calories: 375,
      },
      {
        name: 'Lolliertttpop',
        calories: 392,
      },
      {
        name: 'Honeytyucomb',
        calories: 408,
      },
      {
        name: 'Donuoput',
        calories: 452,
      },
      {
        name: 'KitKsdfkuat',
        calories: 518,
      },
      {
        name: 'Jelly beaddn',
        calories: 375,
      },
      {
        name: 'Lollisdfgspop',
        calories: 392,
      },
      {
        name: 'Honeycsdfgomb',
        calories: 408,
      },
      {
        name: 'Donutyy',
        calories: 452,
      },
      {
        name: 'KiggtKat',
        calories: 518,
      },
      {
        name: 'Jeslly bean',
        calories: 375,
      },
      {
        name: 'Lollasdipop',
        calories: 392,
      },
      {
        name: 'Honeggfycomb',
        calories: 408,
      },
      {
        name: 'Dossnut',
        calories: 452,
      },
      {
        name: 'KitKdfat',
        calories: 518,
      },
    ],
    filter:{},
    errors:{},
    sort_by_list: [
      {text: 'جدید ترین ها',value: 'newest'},
      {text: 'آخرین بازدید',value: 'last_seen'},
      {text: 'اشتراک طلایی',value: 'gold_plan'},
      {text: 'اشتراک نقره ای',value: 'silver_plan'},
      {text: 'اشتراک الماس',value: 'diamond_plan'},
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
      this.$http.post(`users/list?page=${this.current_page}` , this.filter)
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
