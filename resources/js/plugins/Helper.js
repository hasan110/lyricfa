const Plugin = {};

Plugin.install = function(Vue) {

  Vue.mixin({
    data: function() {
      return {
          levels: ['A1','A2','B1','B2','C1','C2']
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
      },
      levelColor(level){
          if (!level) return "#000";
          const levels = {
              'A1':'#0d6013',
              'A2':'#4faf56',
              'B1':'#ffd901',
              'B2':'#ff4d00',
              'C1':'#ed2323',
              'C2':'#b10000',
          };
          if (levels[level] !== undefined) {
              return levels[level];
          }
          return "#000";
      }
    }

  });



};

export { Plugin as default };
