<template>
  <div>

    <div class="page-head">
      <div class="titr">تنظیمات</div>
      <div class="back">
        <router-link :to="{ name : 'dashboard' }">بازگشت
          <v-icon>
            mdi-chevron-left
          </v-icon>
        </router-link>
      </div>
    </div>

    <v-container>

        <v-row class="pt-3">

            <v-col cols="6" class="pb-0">
                <v-text-field
                    v-model="setting.app_version_code"
                    outlined
                    clearable
                    dense
                    label="کد ورژن اپ"
                ></v-text-field>
            </v-col>
            <v-col cols="6" class="pb-0">
                <v-text-field
                    v-model="setting.app_version_name"
                    outlined
                    clearable
                    dense
                    label="نام ورژن اپ"
                ></v-text-field>
            </v-col>
            <v-col cols="6" class="pb-0">
                <v-checkbox
                    v-model="setting.maintenance_mode"
                    outlined
                    clearable
                    dense
                    label="حالت تعمیر و نگهداری"
                ></v-checkbox>
            </v-col>

            <v-col cols="12" class="pb-0 text-center">
                <v-btn
                    color="accent"
                    dense
                    :loading="loading"
                    :disabled="loading"
                    @click="updateSetting()"
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
  name:'settings',
  data: () => ({
    setting:{},
    errors:{},
    loading : false
  }),
  methods:{
    getData(){
      this.$store.commit('SHOW_APP_LOADING' , 1)
      this.$http.post(`setting/single`)
      .then(res => {
        this.$store.commit('SHOW_APP_LOADING' , 0)
        this.setting = res.data.data
      })
      .catch( () => {
        this.$store.commit('SHOW_APP_LOADING' , 0)
      });
    },
    updateSetting(){
      this.loading = true
      this.$http.post(`setting/edit` , this.setting)
      .then(res => {
        this.getData()
        this.$fire({
          title: "موفق",
          text: res.data.message,
          type: "success",
          timer: 5000
        })
        this.loading = false
      })
      .catch( err => {
        this.loading = false
        const e = err.response.data
        if(e.errors){ this.errors = e.errors }
        this.$fire({
          title: "خطا",
          text: e.message ? e.message : 'خطا در پردازش درخواست !',
          type: "error",
          timer: 5000
        })
      });
    }
  },
  mounted(){
    this.getData();
  },
  beforeMount(){
    this.checkAuth()
  }
}
</script>
<style>
</style>
