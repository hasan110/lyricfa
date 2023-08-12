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

            <v-card-title>
              {{user.phone_number}}
            </v-card-title>

            <v-card-subtitle>
              {{user.fullname}}
            </v-card-subtitle>

            <v-card-title>
                وضعیت اشتراک :
                <template v-if="user.days_remain">
                    {{user.days_remain}} روز باقی مانده
                </template>
                <template v-else>
                    منقضی شده
                </template>

            </v-card-title>
              <v-card-title>

                  <v-btn color="success" dens @click="create_notif_modal = true">
                      ارسال نوتیفیکیشن
                  </v-btn>

              </v-card-title>

            <!-- <v-card-actions>
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
            </v-expand-transition> -->
          </v-card>
        </v-col>

        <v-col xs="12" sm="6" md="8" lg="8">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="subscribe.title"
                label="افزودن اشتراک"
                append-outer-icon="mdi-minus"
                @click:append-outer="subscribe.title--"
                prepend-icon="mdi-plus"
                @click:prepend="subscribe.title++"
                outlined
                dense
              >
              </v-text-field>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="subscribe.description"
                label="علت افزایش اشتراک"
                outlined
                dense
              >
              </v-textarea>
            </v-col>
            <v-col cols="12">
              <v-btn :loading="subscribe_loading" :disabled="subscribe_loading" color="success" dens @click="increaseSubscription()">
                ثبت
              </v-btn>
            </v-col>
          </v-row>
          <div>


          </div>
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
                  <th>توسط</th>
                  <th>روزهای اضافه شده</th>
                  <th>توضیحات</th>
                  <th>تاریخ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item , key) in user.subscription" :key="key">
                  <td>{{item.id}}</td>
                  <td>{{item.ref_id}}</td>
                  <td>{{item.val_money}} تومان</td>
                  <td>
                    <template v-if="item.type == 1">
                      ادمین
                    </template>
                    <template v-else>
                      کاربر
                    </template>
                  </td>
                  <td>{{item.title}}</td>
                  <td>{{item.description}}</td>
                  <td>{{item.created_at}}</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-col>

      </v-row>
    </v-container>



      <v-dialog
          transition="dialog-top-transition"
          max-width="600"
          v-model="create_notif_modal"
      >
          <v-card>
              <v-toolbar
                  color="accent"
                  dark
              >ارسال نوتیفیکیشن</v-toolbar>
              <v-card-text>

                  <v-container>

                      <v-row class="pt-3">

                          <v-col cols="6" class="pb-0">
                              <v-text-field
                                  v-model="form_data_notif.title"
                                  outlined
                                  clearable
                                  dense
                                  label="عنوان"
                              ></v-text-field>
                          </v-col>

                          <v-col cols="6" class="pb-0">
                              <v-file-input
                                  v-model="form_data_notif.image"
                                  outlined
                                  show-size
                                  dense
                                  label="آپلود تصویر"
                                  accept="image/*"
                              ></v-file-input>
                          </v-col>


                      </v-row>

                      <v-row class="pt-3">


                          <v-col cols="12" class="pb-0">
                              <v-textarea
                                  v-model="form_data_notif.body"
                                  outlined
                                  clearable
                                  dense
                                  label="متن"
                              ></v-textarea>
                          </v-col>
                      </v-row>

                  </v-container>

              </v-card-text>

              <v-card-actions class="justify-end">
                  <v-btn color="danger" @click="create_notif_modal = false">بستن</v-btn>
                  <v-btn v-if="!notif_id"
                      :loading="create_notif_loading"
                      :disabled="create_notif_loading"
                      color="success"
                      @click="saveNotification()"
                  >ایجاد</v-btn>
                  <v-btn v-if="notif_id"
                      color="blue"
                      @click="sendNotification()"
                  >ارسال</v-btn>
              </v-card-actions>
          </v-card>
      </v-dialog>

  </div>
</template>
<script>
export default {
  name:'user',
  data: () => ({
    user_id:null,
    show:false,
    user:{},
    subscribe:{
        title:''
    },
    errors:{},
    form_data_notif:{},
    notif_id:null,
    create_notif_modal:false,
    create_notif_loading:false,
    subscribe_loading:false
  }),
  methods:{
    getUser(){
      this.$store.commit('SHOW_APP_LOADING' , 1);
      this.$http.post(`users/single` , {id : this.user_id})
      .then(res => {
        this.user = res.data.data
          this.$store.commit('SHOW_APP_LOADING' , 0);
      })
      .catch( err => {
          this.$store.commit('SHOW_APP_LOADING' , 0);
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
    increaseSubscription(){
      this.subscribe_loading = true
      this.subscribe.user_id = this.user_id
      this.$http.post(`users/add_subs` , this.subscribe)
      .then( res => {
          this.subscribe = {
              title:''
          }
          this.$fire({
              title: "موفق",
              text: res.data.message,
              type: "success",
              timer: 5000
          })
          this.subscribe_loading = false
          this.getUser()
      })
      .catch( err => {
        this.subscribe_loading = false
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

    sendNotification(){
        this.$store.commit('SHOW_APP_LOADING' , 1)
        this.$http.post(`notifications/send` , {id:this.notif_id , api_token:this.user.api_token})
            .then(res => {
                this.create_notif_modal = false
                this.notif_id = null
                this.$fire({
                    title: "موفق",
                    text: res.message,
                    type: "success",
                    timer: 5000
                })
                this.$store.commit('SHOW_APP_LOADING' , 0)
            })
            .catch( () => {
                this.$store.commit('SHOW_APP_LOADING' , 0)
            });
    },
    saveNotification(){
        this.create_notif_loading = true
        const d = new FormData();
        const x = this.form_data_notif;

        x.title ? d.append('title', x.title) : '';
        x.body ? d.append('body', x.body) : '';
        x.image ? d.append('image', x.image) : '';
        d.append('type', 'one');

        this.$http.post(`notifications/create` , d)
            .then(res => {
                this.create_notif_loading = false

                this.notif_id = res.data.id

                this.$fire({
                    title: "موفق",
                    text: res.message,
                    type: "success",
                    timer: 5000
                })
            })
            .catch( err => {
                this.create_notif_loading = false
                const e = err.response.data
                if(e.errors){ this.errors = e.errors }

                this.$fire({
                    title: "خطا",
                    text: e.message,
                    type: "error",
                    timer: 5000
                })
            });
    },
  },
  mounted(){
    this.user_id = this.$route.params.id
    this.getUser()
  },
  beforeMount(){
    this.checkAuth()
  }
}
</script>
<style>
</style>
