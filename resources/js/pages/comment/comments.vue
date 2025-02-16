<template>
    <div>

        <div class="page-head">
            <div class="titr">نظرات</div>
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
                        @click:append-outer="reset()"
                        @keyup.enter="reset()"
                    ></v-text-field>

                </v-col>
                <v-col cols="4" class="pb-0">
                </v-col>
                <v-col cols="4" class="pb-0">
                    <v-select
                        label="فیلتر"
                        :items="[{text:'تایید نشده ها',value:'pending'},{text:'تایید شده ها',value:'confirmed'}]"
                        v-model="filter.sort_by"
                        item-text="text"
                        item-value="value"
                        append-outer-icon="mdi-filter"
                        outlined
                        clearable
                        autocomplete
                        dense
                        @click:append-outer="reset()"
                    ></v-select>
                </v-col>

            </v-row>

            <div class="sm-section">

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
                            <th>کاربر</th>
                            <th>متن نظر</th>
                            <th>پاسخ</th>
                            <th>نظر برای</th>
                            <th>اطلاعات</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
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
                            v-for="item in list"
                            :key="item.id"
                        >
                            <td>{{ item.id }}</td>
                            <td>
                                <template v-if="item.user">
                                    <router-link :to="{ name:'user' , params:{ id:item.user.id } }">
                                        <template v-if="item.user.phone_number">
                                            {{item.user.phone_number}}
                                        </template>
                                        <template v-else>
                                            {{item.user.email}}
                                        </template>
                                    </router-link>
                                </template>
                                <template v-else>
                                    ---
                                </template>
                            </td>
                            <td :title="item.comment">
                                <span class="two-line-box">{{item.comment}}</span>
                            </td>
                            <td :title="item.reply">
                                <span class="two-line-box">{{item.reply}}</span>
                            </td>
                            <td>
                                <template v-if="item.commentable">
                                    <template v-if="item.commentable_type === 'App\\Models\\Singer'">
                                        خواننده
                                    </template>
                                    <template v-else-if="item.commentable_type === 'App\\Models\\Music'">
                                        آهنگ
                                    </template>
                                    <template v-else-if="item.commentable_type === 'App\\Models\\Film'">
                                        فیلم
                                    </template>
                                </template>
                                <template v-else>
                                    ---
                                </template>
                            </td>
                            <td>
                                <template v-if="item.commentable">
                                    <template v-if="item.commentable_type === 'App\\Models\\Singer'">
                                        <router-link :to="{name:'singers' , query:{english_name:item.commentable.english_name}}">{{item.commentable.english_name}}</router-link>
                                    </template>
                                    <template v-else-if="item.commentable_type === 'App\\Models\\Music'">
                                        <router-link :to="{name:'edit_music' , params:{id:item.commentable.id}}">{{item.commentable.name}}</router-link>
                                    </template>
                                    <template v-else-if="item.commentable_type === 'App\\Models\\Film'">
                                        <router-link :to="{name:'edit_movie' , params:{id:item.commentable.id}}">{{item.commentable.english_name}}</router-link>
                                    </template>
                                </template>
                                <template v-else>
                                    ---
                                </template>
                            </td>
                            <td>{{ item.persian_created_at }}</td>
                            <td>
                                <v-btn
                                    class="mx-2"
                                    fab small
                                    dark
                                    @click="toggleStatusModal(item.id , 1)"
                                    color="indigo"
                                >
                                    <v-icon dark>
                                        mdi-check-bold
                                    </v-icon>
                                </v-btn>

                                <v-btn
                                    class="mx-2"
                                    fab small
                                    dark
                                    @click="toggleStatusModal(item.id , 0)"
                                    color="red"
                                >
                                    <v-icon dark>
                                        mdi-close-thick
                                    </v-icon>
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

        </v-container>

        <v-dialog
            v-model="change_status_modal"
            persistent
            max-width="600px"
        >
            <v-card>
                <v-card-title>
                    <h4>
                        <template v-if="comment_status === 1">
                            تایید نظر
                        </template>
                        <template v-else>
                            رد کردن نظر
                        </template>
                    </h4>
                </v-card-title>
                <v-card-text>

                    <template v-if="comment_status === 1">
                        آیا مطمئنید می خواهید این نظر را تایید کنید؟
                    </template>
                    <template v-else>
                        آیا مطمئنید می خواهید این نظر را رد کنید؟
                        <p>(با رد کردن , این نظر حذف خواهد شد.)</p>
                    </template>

                </v-card-text>
                <div class="pa-4">
                    <v-textarea v-if="comment_status === 1" rows="3" v-model="reply" outlined dense placeholder="در صورت نیاز برای کامنت پاسخ بنویسید"></v-textarea>
                </div>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="red"
                        dark
                        @click="change_status_modal = false"
                    >
                        بستن
                    </v-btn>
                    <v-btn
                        :loading="loading"
                        :disabled="loading"
                        color="blue"
                        @click="changeStatus()"
                    >
                        ادامه
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>
<script>
export default {
    name:'comments',
    data: () => ({
        list:[],
        filter:{
            sort_by:'pending'
        },
        errors:{},
        current_page:1,
        per_page:0,
        last_page:1,
        comment_id:null,
        change_status_modal:false,
        reply:'',
        fetch_loading:false,
        loading:false,
        comment_status : 0
    }),
    watch:{
        current_page(){
            this.getList();
        }
    },
    methods:{
        getList(){
            this.fetch_loading = true
            this.$http.post(`user_comments/list?page=${this.current_page}` , this.filter)
                .then(res => {
                    this.list = res.data.data.data
                    this.last_page = res.data.data.last_page;
                    this.fetch_loading = false
                })
                .catch( () => {
                    this.fetch_loading = false
                });
        },
        Search(e){
            if (e.keyCode === 13) {
                this.current_page = 1
                this.list = []
                this.getList()
            }
        },
        reset(){
            this.current_page = 1
            this.list = []
            this.getList()
        },
        toggleStatusModal(id , status){
            this.comment_id = id
            this.comment_status = status
            this.change_status_modal = true
        },
        changeStatus(){
            this.loading = true
            this.$http.post(`user_comments/change_status` , {id:this.comment_id , status:this.comment_status , reply:this.reply})
                .then( () => {
                    this.reset()
                    this.loading = false
                    this.change_status_modal = false
                })
                .catch( err => {
                    this.loading = false
                    const e = err.response.data
                    if(e.errors){ this.errors = e.errors }

                    this.$fire({
                        title: "خطا",
                        text: e.message ? e.message : 'خطا در پردازش درخواست !',
                        type: "error",
                        timer: 5000
                    })

                });
        }
    },
    mounted(){
        this.getList();
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('نظرات')
    }
}
</script>
<style>
</style>
