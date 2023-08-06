const Auth = {
  data(){
    return {
      admin_token_name : 'admin_token'
    }
  },
  computed:{
    ADMIN_DATA(){
      return 0;
    }
  },
  methods: {
    checkAuth()
    {
      const adminToken = this.$cookies.get(this.admin_token_name);
      if(!adminToken)
      {
        this.$router.replace({ name: 'login'})
      }
    },
    saveToken(token)
    {
      this.$cookies.set(this.admin_token_name, token , 60*60*24*365);
      return true
    },
    checkNotAuthenticated()
    {
      const adminToken = this.$cookies.get(this.admin_token_name);
      if(adminToken){
        this.$router.replace({ name: 'dashboard'})
      }
    },
    getToken()
    {
      const adminToken = this.$cookies.get(this.admin_token_name);
      return adminToken;
    },
    checkIsAuthenticated()
    {
      const adminToken = this.$cookies.get(this.admin_token_name);
      if(adminToken)
      {
        return true
      }else
      {
        return false
      }
    },
    removeToken()
    {
      this.$cookies.remove(this.admin_token_name)

      return true;
    },
  }
}

const Plugin = {};

Plugin.install = function(Vue) {
  
  Vue.mixin(Auth);
  
}
export { Plugin as default };