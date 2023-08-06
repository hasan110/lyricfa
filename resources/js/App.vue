<template>

  <v-app>
    <my-sidebar v-if="$route.name !== 'login'" v-model="isDrawerOpen"></my-sidebar>

    <v-app-bar
      v-if="$route.name !== 'login'"
      app
      flat
      absolute
      dense
    >
      <div class="boxed-container w-full">
        <div class="d-flex align-center">

          <v-btn
            color="primary"
            icon
            @click="isDrawerOpen = !isDrawerOpen"
          >
            <v-icon>mdi-format-list-bulleted</v-icon>
          </v-btn>


        </div>
      </div>
    </v-app-bar>

    <v-main>
      <router-view></router-view>
    </v-main>

  </v-app>

</template>
<script>
import removeToken from "./plugins/Auth";

export default {
  name:'App',
  data: () => ({
    isDrawerOpen:true,
    loading:true
  }),
  methods: {
    get_admin_data(){
      this.loading = true
      this.$http.get(`get_admin_data`)
      .then(res => {
        this.loading = false
        this.$store.commit('SET_ADMIN_DATA' , res.data.data);
      })
      .catch( () => {
          this.removeToken();
          this.$router.replace({name:'login'})
        this.loading = false
      });
    }
  },
  beforeMount(){
    this.checkAuth()

    this.get_admin_data();
  }
}
</script>
<style>
</style>
