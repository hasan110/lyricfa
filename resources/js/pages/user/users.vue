<template>
    <div>

        <div class="page-head">
            <div class="titr">کاربران</div>
            <div class="back">
                <router-link :to="{ name : 'dashboard' }">بازگشت
                    <v-icon>
                        mdi-chevron-left
                    </v-icon>
                </router-link>
            </div>
        </div>

        <v-container>
            <v-row class="md-section">

                <v-col cols="4" class="pb-0">

                    <v-text-field
                        v-model="filter.search_key"
                        :append-outer-icon="'mdi-magnify'"
                        outlined
                        clearable
                        dense
                        label="جست و جو"
                        type="text"
                        @click:append-outer="getList()"
                        @keyup.enter="getList()"
                    ></v-text-field>

                </v-col>
                <v-col cols="4" class="pb-0">
                </v-col>
                <v-col cols="4" class="pb-0">
                    <v-select
                        label="مرتب سازی بر اساس"
                        :items="sort_by_list"
                        v-model="filter.sort_by"
                        item-text="text"
                        item-value="value"
                        append-outer-icon="mdi-filter"
                        outlined
                        clearable
                        autocomplete
                        dense
                        @click:append-outer="getList()"
                    ></v-select>
                </v-col>

            </v-row>

            <div class="sm-section">

                <v-btn @click="sub_modal = true" color="primary" dark>افزودن اشتراک</v-btn>

            </div>

            <div class="main-section">
                <v-simple-table
                    fixed-header
                    height="100%"
                    style="height:100%"
                >
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>شماره تلفن</th>
                            <th>اشتراک</th>
                            <th>ثبت نام از / سطح</th>
                            <th>تاریخ ثبت نام</th>
                            <th>جزییات</th>
                        </tr>
                        </thead>
                        <div class="fetch-loading">
                            <v-progress-linear
                                v-if="fetch_loading"
                                indeterminate
                                color="cyan"
                            ></v-progress-linear>
                        </div>
                        <tbody>
                        <tr
                            v-for="(item , key) in list"
                            :key="key"
                            :class="[item.has_subscription ? 'bg-star' : '']"
                        >
                            <td>{{ item.id }}</td>
                            <td>
                                <template v-if="item.phone_number">
                                    {{item.phone_number}}
                                </template>
                                <template v-else>
                                    {{item.email}}
                                </template>
                            </td>
                            <td><div v-html="item.expire"></div></td>
                            <td>
                                <span style="color: blue" v-if="item.corridor === 'app'">
                                    اپلیکیشن
                                </span>
                                <span style="color: #6200ed" class="text-primary" v-else-if="item.corridor === 'web-app'">
                                    وب اپ
                                </span>
                                /
                                <template v-if="item.level">
                                    <span :style="{color:levelColor(item.level)}" class="en-font">{{item.level}}</span>
                                </template>
                                <span v-else>_</span>
                            </td>
                            <td>{{ item.persian_created_at }}</td>
                            <td>
                                <v-btn fab small color="teal" dens>
                                    <router-link :to="{ name:'user' , params:{ id:item.id } }">
                                        <v-icon>mdi-information-outline</v-icon>
                                    </router-link>
                                </v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </div>

            <div class="sm-section">
                <v-pagination
                    v-model="current_page"
                    :length="last_page"
                    :total-visible="7"
                ></v-pagination>
            </div>


            <v-dialog
                max-width="600"
                v-model="sub_modal"
            >
                <v-card>
                    <v-toolbar
                        color="accent"
                        dark
                    >تمدید اشتراک کاربران</v-toolbar>
                    <v-card-text>
                        <v-container>
                            <v-row class="pt-3">
                                <v-col cols="12" class="pb-0">
                                    <v-text-field
                                        v-model="sub_form_data.hours"
                                        outlined
                                        clearable
                                        dense
                                        label="مدت تمدید (به ساعت)"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions class="justify-end">
                        <v-btn color="danger" dark @click="sub_modal = false">بستن</v-btn>
                        <v-btn :loading="sub_loading" :disabled="sub_loading" color="success" @click="addSubscription()" >اعمال اشتراک</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

        </v-container>
    </div>
</template>
<script>
export default {
    name:'users',
    data: () => ({
        list:{},
        filter:{},
        errors:{},
        sub_form_data:{},
        sort_by_list: [
            {text: 'جدید ترین',value: 'newest'},
            {text: 'قدیمی ترین',value: 'oldest'},
            {text: 'بیشترین اشتراک',value: 'most_subscribed'},
            //{text: 'اشتراک طلایی',value: 'gold_plan'},
            //{text: 'اشتراک نقره ای',value: 'silver_plan'},
            //{text: 'اشتراک الماس',value: 'diamond_plan'},
        ],
        current_page:1,
        per_page:0,
        last_page:5,
        fetch_loading:false,
        sub_modal:false,
        sub_loading:false,
    }),
    watch:{
        current_page(){
            this.getList();
        }
    },
    methods:{
        getList(){
            this.fetch_loading = true
            this.$http.post(`users/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                    this.fetch_loading = false
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        addSubscription(){
            this.sub_loading = true
            this.$http.post(`users/add_subs/group` , this.sub_form_data)
                .then( () => {
                    this.getList()
                    this.$fire({
                        title: "موفق",
                        text: 'تمدید اشتراک کاربران باموفقیت انجام شد.',
                        type: "success",
                        timer: 5000
                    })
                    this.sub_loading = false
                    this.sub_modal = false
                })
                .catch( () => {
                    this.$fire({
                        title: "خطا",
                        text: 'تمدید اشتراک کاربران با مشکل مواجه شد.',
                        type: "error",
                        timer: 5000
                    })
                    this.sub_loading = false
                });
        },
        Search(e){
            if (e.keyCode === 13) {
                this.current_page = 1
                this.list = []
                this.getList()
            }
        },
    },
    mounted(){
        this.filter.sort_by = 'newest';
        this.getList();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('کاربران')
    }
}
</script>
<style>
.bg-star{
    background: #cdfff9 !important;
}
</style>
