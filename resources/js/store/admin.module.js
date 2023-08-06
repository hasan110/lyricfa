const state = () => {
  return {
    ADMIN:{}
  }
};

const getters = {
};

const actions = {
};

const mutations = {
  SET_ADMIN_DATA(state , payload){
    state.ADMIN = payload
  }
};

export default {
  state,
  getters,
  actions,
  mutations
};