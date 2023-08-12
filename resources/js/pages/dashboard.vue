<template>
  <div>
      <v-container>
          <v-row dense>
              <v-col cols="12" lg="3" md="6" sm="12">
                  <v-card
                      color="#D81B60"
                      dark
                      :to="{name:'users'}"
                      class="white--text"
                  >
                      <v-card-title>
                          <h3>کل کاربران</h3>
                      </v-card-title>
                      <v-card-title>
                          <v-spacer></v-spacer>
                          <h1>{{ statistics.total_users }}</h1>
                      </v-card-title>
                      <v-card-actions>
                          <v-tooltip top>
                              <template #activator="{ on, attrs }">
                                  <div v-bind="attrs" v-on="on">کاربران جدید</div>
                              </template>
                              <span>تعداد کاربران در 7 روز گذشته</span>
                          </v-tooltip>
                          <v-divider></v-divider>
                          <div>{{ statistics.new_users }}</div>
                      </v-card-actions>
                  </v-card>
              </v-col>
              <v-col cols="12" lg="3" md="6" sm="12">
                  <v-card
                      color="#0D47A1"
                      dark
                      :to="{name:'musics'}"
                      class="white--text"
                  >
                      <v-card-title>
                          <h3>کل آهنگ ها</h3>
                      </v-card-title>
                      <v-card-title>
                          <v-spacer></v-spacer>
                          <h1>{{ statistics.total_musics }}</h1>
                      </v-card-title>
                      <v-card-actions>
                          <v-tooltip top>
                              <template #activator="{ on, attrs }">
                                  <div v-bind="attrs" v-on="on">آهنگ های جدید</div>
                              </template>
                              <span>تعداد آهنگ های 7 روز گذشته</span>
                          </v-tooltip>
                          <v-divider></v-divider>
                          <div>{{ statistics.new_musics }}</div>
                      </v-card-actions>
                  </v-card>
              </v-col>
              <v-col cols="12" lg="3" md="6" sm="12">
                  <v-card
                      color="#2E7D32"
                      dark
                      :to="{name:'singers'}"
                      class="white--text"
                  >
                      <v-card-title>
                          <h3>کل خواننده ها</h3>
                      </v-card-title>
                      <v-card-title>
                          <v-spacer></v-spacer>
                          <h1>{{ statistics.total_singers }}</h1>
                      </v-card-title>
                      <v-card-actions>
                          <v-tooltip top>
                              <template #activator="{ on, attrs }">
                                  <div v-bind="attrs" v-on="on">خواننده های جدید</div>
                              </template>
                              <span>تعداد خواننده های 7 روز گذشته</span>
                          </v-tooltip>
                          <v-divider></v-divider>
                          <div>{{ statistics.new_singers }}</div>
                      </v-card-actions>
                  </v-card>
              </v-col>
              <v-col cols="12" lg="3" md="6" sm="12">
                  <v-card
                      color="#ff9b20"
                      dark
                      :to="{name:'comments'}"
                      class="white--text"
                  >
                      <v-card-title>
                          <h3>کل نظرات</h3>
                      </v-card-title>
                      <v-card-title>
                          <v-spacer></v-spacer>
                          <h1>{{ statistics.total_comments }}</h1>
                      </v-card-title>
                      <v-card-actions>
                          <v-tooltip top>
                              <template #activator="{ on, attrs }">
                                  <div v-bind="attrs" v-on="on">تایید نشده</div>
                              </template>
                              <span>تعداد نظرات تایید نشده</span>
                          </v-tooltip>
                          <v-divider></v-divider>
                          <div>{{ statistics.pending_comments }}</div>
                      </v-card-actions>
                  </v-card>
              </v-col>

          </v-row>
      </v-container>
  </div>
</template>
<script>
export default {
  name:'dashboard',
  data: () => ({
      statistics: {
          'total_users':0,
          'new_users':0,
          'total_musics':0,
          'new_musics':0,
          'total_singers':0,
          'new_singers':0,
          'total_comments':0,
          'pending_comments':0
      },
      fetch_loading: false
  }),
  methods: {
      getStatistics(){
          this.fetch_loading = true
          this.$http.get(`index/statistics`)
          .then(res => {
              this.statistics = res.data.data
          })
          .catch( (err) => {
              console.log(err)
          }).finally( () => {
              this.fetch_loading = false
          });
      },
  },
  beforeMount(){
    this.checkAuth()
  },
  mounted(){
    this.getStatistics()
  }
}
</script>
<style>
</style>
