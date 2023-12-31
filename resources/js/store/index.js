import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

import admin from "./admin.module";
import loadings from "./loadings.module";

export default new Vuex.Store({
  modules: {
    admin ,
    loadings
  }
});
