<template>
  <div>
    <v-container fluid fill-height>
      <v-layout align-center justify-center>
          <v-flex xs12 sm8 md4>
            <v-card class="elevation-12">
                <v-toolbar dark color="accent">
                  <v-toolbar-title>ورود به پنل مدیریت</v-toolbar-title>
                </v-toolbar>
                <v-card-text>
                  <v-form>
                      <v-text-field
                      v-model="form_data.username"
                        prepend-icon="mdi-account"
                        label="نام کاربری"
                      ></v-text-field>
                      <div v-if="errors.username">
                        <small class="red--text">{{errors.username[0]}}</small>
                      </div>
                      <v-text-field
                      v-model="form_data.password"
                        prepend-icon="mdi-lock"
                        label="رمز عبور"
                        type="password"
                      ></v-text-field>
                      <div v-if="errors.password">
                        <small class="red--text">{{errors.password[0]}}</small>
                      </div>
                  </v-form>
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn :loading="loading" :disabled="loading" color="accent" @click="login()">ورود</v-btn>
                </v-card-actions>
            </v-card>
          </v-flex>
      </v-layout>
    </v-container>
  </div>
</template>
<script>
export default {
  name:'login',
  data: () => ({
    form_data:{},
    errors:{},
    loading:false
  }),
  methods:{
    login(){
      this.loading = true
      this.$http.post(`login_admin` , this.form_data)
      .then(res => {
        this.loading = false
        this.saveToken(res.data.data.api_token);
        location.reload()
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
            timer: 3000
          })

        }
      });
    }
  },
  beforeMount(){
    this.checkNotAuthenticated()
  }
}
</script>
<style>
</style>
