<template>
  <div class="lyricfa-loading">
      <div class="lyricfa-loading-content">
          <div class="lds-dual-ring"></div>
          <div class="text">
              <template v-if="type === 1">
                  درحال دریافت اطلاعات...
              </template>
              <template v-if="type === 2">
                  درحال ارسال اطلاعات...
              </template>
          </div>
      </div>
  </div>
</template>
<script>
export default {
  name:'Loadings',
  data: () => ({
  }),
  props: {
    type: {
      type: Number,
      default: 1,
    },
  },
  methods: {
  },
  beforeMount(){
  }
}
</script>
<style>
.lyricfa-loading{
    width: 100vw;
    height: 100vh;
    z-index: 999;
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    background: #fcfefc;
}
.lyricfa-loading .lyricfa-loading-content{
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}
.lds-dual-ring {
    display: inline-block;
    width: 80px;
    height: 80px;
}
.lds-dual-ring:after {
    content: " ";
    display: block;
    width: 64px;
    height: 64px;
    margin: 8px;
    border-radius: 50%;
    border: 6px solid #3F51B5;
    border-color: #3F51B5 transparent #3F51B5 transparent;
    animation: lds-dual-ring .5s linear infinite;
}
@keyframes lds-dual-ring {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

</style>
