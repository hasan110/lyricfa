<template>
  <div>

    <div class="page-head">
      <div class="titr">لیست سفارش آهنگ ها</div>
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
                <th>نام موزیک</th>
                <th>نام خواننده</th>
                <th>کاربر</th>
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
                <td>{{item.music_name}}</td>
                <td>{{item.singer_name}}</td>
                <td>
                  <router-link v-if="item.user" :to="{name : 'user' , params : { id : item.user.id}}">
                      {{item.user.phone_number}}
                  </router-link>
                  <span v-else>--</span>
                </td>
                <td>
                  <v-btn color="primary" dens @click="getItem(item.id)">
                    ویرایش
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
      v-model="edit_modal"
    >
      <v-card>
        <v-toolbar
          color="accent"
          dark
        >ویرایش درخواست موزیک</v-toolbar>
        <v-card-text>

          <v-container>

            <v-row class="pt-3">

              <v-col cols="6" class="pb-0">
                  <v-select
                      label="تغییر وضعیت"
                      :items="order_statuses"
                      v-model="form_data.condition_order"
                      item-text="text"
                      item-value="value"
                      outlined
                      clearable
                      dense
                  ></v-select>
              </v-col>

            </v-row>

          </v-container>

        </v-card-text>

        <v-card-actions class="justify-end">
          <v-btn color="danger" @click="edit_modal = false">بستن</v-btn>
          <v-btn
            :loading="edit_loading"
            :disabled="edit_loading"
            color="success"
            @click="updateMusicOrder()"
          >ویرایش</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </div>
</template>
<script>
export default {
  name:'music_orders',
  data: () => ({
    order_statuses: [
      {text: 'بررسی نشده',value: 0},
      {text: 'قبول شده',value: 1},
      {text: 'رد شده',value: 2},
      {text: 'موجود',value: 3},
    ],
    list:[],
    form_data:{},
    filter:{},
    errors:{},
    sort_by_list: [
      {text: 'جدید ترین ها',value: 'newest'},
      {text: 'قدیمی ترین ها',value: 'oldest'},
    ],
    current_page:1,
    per_page:0,
    last_page:5,
    edit_modal:false,
    edit_loading:false,
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
      this.$http.post(`orders/list?page=${this.current_page}` , this.filter)
      .then(res => {
        this.fetch_loading = false
        this.list = res.data.data.data
        this.last_page = res.data.data.last_page;
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
    getItem(id){
      this.$http.post(`orders/single` , {id})
      .then(res => {
        this.form_data = res.data.data
        this.edit_modal = true
      })
      .catch( () => {
      });
    },
    updateMusicOrder(){
      this.edit_loading = true

      this.$http.post(`orders/edit` , this.form_data)
      .then(res => {
        this.edit_loading = false
        this.edit_form_data = {};
        this.edit_modal = false
        this.reset()

        this.$fire({
          title: "موفق",
          text: res.data.message,
          type: "success",
          timer: 5000
        })
      })
      .catch( err => {
        this.edit_loading = false
        const e = err.response.data
        if(e.errors){ this.errors = e.errors }

          this.$fire({
            title: "خطا",
            text: e.message ? e.message : 'خطا در پردازش درخواست !',
            type: "error",
            timer: 5000
          })

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
