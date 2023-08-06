<template>
  <div>
    <div class="page-head">
      <div class="titr">اطلاعات کاربر</div>
      <div class="back">
        <router-link :to="{ name : 'users' }">بازگشت
          <v-icon>
            mdi-chevron-left
          </v-icon>
        </router-link>
      </div>
    </div>

    <v-container fluid>
      <v-row>
        <v-col xs="12" sm="6" md="4" lg="4">
          <v-card class=""
            max-width="100%"
            color="#8B50FF"
            dark
            hover
          >
            <v-avatar size="100" style="margin :12px;">
              <v-img src="https://cdn.vuetifyjs.com/images/profiles/marcus.jpg"></v-img>
            </v-avatar>
        
            <v-card-title>
              09307818757
            </v-card-title>
        
            <v-card-subtitle>
              سید حسن حسینی
            </v-card-subtitle>

            <v-card-title>
              اشتراک باقیمانده : 12 روز
            </v-card-title>
        
            <v-card-actions>
              <v-btn text>SHARE</v-btn>
        
              <v-btn
                text
              >
                Explore
              </v-btn>
        
              <v-spacer></v-spacer>
        
              <v-btn
                icon
                @click="show = !show"
              >
                <v-icon>{{ show ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
              </v-btn>
            </v-card-actions>
        
            <v-expand-transition>
              <div v-show="show">
                <v-divider></v-divider>
        
                <v-card-text>
                  Army Knowledge Online provides web-based enterprise information services to the United States Army, joint, and Department of Defense customers.
                </v-card-text>
              </div>
            </v-expand-transition>
          </v-card>
        </v-col>

        <v-col xs="12" sm="6" md="8" lg="8">

          <v-text-field 
            v-model="numb"
            label="افزودن اشتراک" 
            append-outer-icon="mdi-minus" 
            @click:append-outer="numb--" 
            prepend-icon="mdi-plus" 
            @click:prepend="numb++"
            outlined
            dense
          >
        </v-text-field>

        </v-col>
      </v-row>

      <v-row>

        <v-col cols="12">
          <h2>اشتراک های خریده شده توسط کاربر</h2>
        </v-col>

        <v-col cols="12">
          <v-simple-table
            fixed-header
            height="500px"
          >
            <template v-slot:default>
              <thead>
                <tr>
                  <th>#</th>
                  <th>شناسه پرداخت</th>
                  <th>مقدار</th>
                  <th>وضعیت</th>
                  <th>تاریخ</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>23456409</td>
                  <td>50000 تومان</td>
                  <td>موفق</td>
                  <td>12 اردیبهشت 1401</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-col>

      </v-row>
    </v-container>
    
  </div>
</template>
<script>
export default {
  name:'user',
  data: () => ({
    numb:'',
    show:false,
    user:{},
    errors:{}
  }),
  methods:{
    getUser(){
      this.$http.post(`users/user` , {user_id : this.user_id})
      .then(res => {
        this.user = res.data.data
      })
      .catch( () => {
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
