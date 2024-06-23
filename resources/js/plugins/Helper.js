const Plugin = {};

Plugin.install = function(Vue) {

  Vue.mixin({
    data: function() {
      return {
        // Url:'http://localhost:8000/uploads/',
        // StaticUrl:'http://localhost:8000/',
        Url:'https://dl.lyricfa.app/uploads/',
        StaticUrl:'https://panel.lyricfa.app/',
      }
    },
    computed: {
    },
    methods:{
      just_number(evt){
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
          evt.preventDefault();;
        } else {
          return true;
        }
      },
      formatPrice(value) {
        if(value){
          const number = parseInt(value)
          const val = Number((number).toFixed(1)).toLocaleString()
          return val
        }else{
          return value
        }
      },
      just_latin(evt){
        var p = /^[\u0600-\u06FF\s]+$/;
        if (p.test(evt.key)) {
          evt.preventDefault();
        }
      },
      dont_allow_number(evt){
        var p = /[^0-9]/g;
        if (!p.test(evt.key)) {
          evt.preventDefault();
        }
      },
      allow_phone_number(evt){
        var p = /[^0-9]/g;
        if (p.test(evt.key)) {
          evt.preventDefault();
        }
        if (evt.target.value.length >= 11) {
          evt.preventDefault();
        }
      },
      setPageTitle(title){
        document.title = `لیریکفا | ${title}`;
      }
    }

  });



};

export { Plugin as default };
