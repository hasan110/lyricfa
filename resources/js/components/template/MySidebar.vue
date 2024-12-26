<template>
  <v-navigation-drawer
    fixed
    app
    :value="value"
    right
    width="250"
    @input="val => $emit('input', val)"
  >
    <v-app-bar
      dense
      dark
    >
      <v-toolbar-title class="text-center">
        <span>lyricfa Admin</span>
      </v-toolbar-title>
    </v-app-bar>
    <v-list>
      <v-list-item link>
        <v-list-item-content>
          <v-list-item-title class="text-h6">
              {{ $store.state.admin.ADMIN.full_name }}
          </v-list-item-title>
          <v-list-item-subtitle>{{ $store.state.admin.ADMIN.username }}</v-list-item-subtitle>
        </v-list-item-content>
        <v-list-item-action @click="logout()">
          <v-icon>mdi-logout</v-icon>
        </v-list-item-action>
      </v-list-item>
    </v-list>
    <v-divider></v-divider>
    <v-list dense>
      <v-list-item-group
        v-model="selectedItem"
        color="primary"
        dense
      >
        <div v-for="(item , key) in items" :key="key">
          <template v-if="item.type === 'header'">
            <v-list-item disabled>
              <v-subheader>{{item.title}}</v-subheader>
            </v-list-item>
          </template>
          <template v-else-if="item.type === 'group'">
            <v-list-group>
              <template v-slot:activator>
                <v-list-item-content>
                  <v-list-item-title>
                    {{item.title}}
                  </v-list-item-title>
                </v-list-item-content>
              </template>
              <div v-for="(sub , subkey) in items" :key="subkey">
                <template v-if="sub.parent_id === item.id">
                  <router-link :to="{ name:sub.link }">
                    <v-list-item ripple="ripple">
                      <v-list-item-icon>
                        <v-icon v-text="sub.icon"></v-icon>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title v-text="sub.title"></v-list-item-title>
                      </v-list-item-content>
                    </v-list-item>
                  </router-link>
                </template>
              </div>
            </v-list-group>
          </template>
          <template v-else-if="item.type === 'item'">
            <router-link :to="{ name:item.link }">
              <v-list-item>
                <v-list-item-icon>
                  <v-icon v-text="item.icon"></v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title v-text="item.title"></v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </router-link>
          </template>
        </div>
      </v-list-item-group>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
export default {
    name:'MySidebar',
    data: () => ({
        selectedItem: null,
        items: [
            { id:1,  parent_id:0,  title: 'صفحه اصلی', type:'item', link:'dashboard', icon: 'mdi-view-dashboard-outline' },
            { id:20, parent_id:0,  title: 'اکانتینگ', type:'header', link:'', icon: '' },
            { id:2,  parent_id:0,  title: 'کاربران', type:'item', link:'users', icon: 'mdi-account-group-outline' },
            { id:7,  parent_id:0,  title: 'پرداخت ها', type:'item', link:'pays', icon: 'mdi-cash-plus' },
            { id:11, parent_id:0,  title: 'نظرات', type:'item', link:'comments', icon: 'mdi-comment-outline' },
            { id:18, parent_id:0,  title: 'چند رسانه ای', type:'header', link:'', icon: '' },
            { id:3,  parent_id:0,  title: 'آهنگ ها', type:'item', link:'musics', icon: 'mdi-music' },
            { id:12, parent_id:0,  title: 'فیلم ها', type:'item', link:'movies', icon: 'mdi-movie-settings-outline' },
            { id:4,  parent_id:0,  title: 'خواننده ها', type:'item', link:'singers', icon: 'mdi-account-voice' },
            { id:5,  parent_id:0,  title: 'آلبوم ها', type:'item', link:'albums', icon: 'mdi-album' },
            { id:19, parent_id:0,  title: 'تنظیمات', type:'header', link:'', icon: '' },
            { id:9,  parent_id:0,  title: 'تنظیمات', type:'item', link:'settings', icon: 'mdi-cog-outline' },
            { id:6,  parent_id:0,  title: 'اسلایدر ها', type:'item', link:'sliders', icon: 'mdi-panorama-variant-outline' },
            { id:10, parent_id:0,  title: 'سفارش آهنگ', type:'item', link:'music_orders', icon: 'mdi-account-cash-outline' },
            { id:13, parent_id:0,  title: 'نوتیفیکیشن ها', type:'item', link:'notifications', icon: 'mdi-bell-outline' },
            { id:21, parent_id:0,  title: 'دیکشنری', type:'header', link:'', icon: '' },
            { id:14, parent_id:0,  title: 'لغات', type:'item', link:'words', icon: 'mdi-format-color-text' },
            { id:15, parent_id:0,  title: 'اصطلاحات', type:'item', link:'idioms', icon: 'mdi-alphabetical' },
            { id:16, parent_id:0,  title: 'گرامر', type:'item', link:'grammers', icon: 'mdi-book-open-page-variant' },
            { id:17, parent_id:0,  title: 'مپ لغات', type:'item', link:'maps', icon: 'mdi-connection' },
            { id:22, parent_id:0,  title: 'اتصال متن', type:'item', link:'join_text', icon: 'mdi-arrow-collapse' },
        ],
    }),
    watch: {
        '$route.name': {
            handler: function() {
                this.checkActiveLink();
            },
            deep: true,
            immediate: true
        }
    },
    props: {
        value: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        close() {
            this.$emit("input", !this.value);
        },
        logout(){
            this.removeToken()
            this.$router.replace({ name:'login' });
        },
        checkActiveLink() {
            const link = this.$route.name;
            for(let i = 0; i < this.items.length; i++)
            {
                if(link === this.items[i].link)
                {
                    this.selectedItem = this.items.indexOf(this.items[i]);
                }
            }
        }
    },
    beforeMount(){
        this.checkActiveLink();
    }
}
</script>
