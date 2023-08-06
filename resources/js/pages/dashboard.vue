<template>
  <div>
    <v-container>
      <v-row>
        <v-col cols="3">
        </v-col>
        <v-col cols="6" class="p-5 text-center">
          <h1>به پنل ادمین لیریکفا خوش آمدید.</h1>
        </v-col>
        <v-col cols="3">
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>
<script>
export default {
  name:'dashboard',
  data: () => ({
  }),
  methods: {
  },
  beforeMount(){
    this.checkAuth()
  }
}
</script>
<style>
</style>
