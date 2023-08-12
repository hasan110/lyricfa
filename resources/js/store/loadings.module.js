const state = () => {
  return {
    APP_LOADING:0
  }
};

const getters = {
};

const actions = {
};

const mutations = {
  SHOW_APP_LOADING(state , payload){
    state.APP_LOADING = payload
  }
};

export default {
  state,
  getters,
  actions,
  mutations
};
