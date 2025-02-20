<template>
    <div>
        <v-btn small :loading="loading" color="purple darken-4" dark @click="getCategoriesList">
            انتخاب دسته بندی
        </v-btn>
        <v-dialog
            max-width="500"
            scrollable
            v-model="modal"
        >
            <v-card>
                <v-card-title>انتخاب دسته بندی برای
                    <template v-if="categorizeable_type === 'grammers'">گرامر</template>
                    <template v-if="categorizeable_type === 'musics'">آهنگ</template>
                    <template v-if="categorizeable_type === 'films'">فیلم</template>
                    <template v-if="categorizeable_type === 'word_definitions'">لغت</template>
                    <template v-if="categorizeable_type === 'idiom_definitions'">عبارت</template>
                </v-card-title>
                <v-card-subtitle>با شناسه {{categorizeable_id}}</v-card-subtitle>
                <v-divider></v-divider>
                <v-card-text>
                    <v-row class="pt-2">
                        <v-col cols="12">
                            <v-text-field hide-details clearable dense outlined v-model="search" placeholder="جست و جو در لیست"></v-text-field>
                        </v-col>
                        <v-col cols="12" class="pt-0" style="max-height: 500px;overflow-y: auto">
                            <v-treeview
                                :items="categories"
                                :open.sync="open" :search="search"
                                item-text="title" item-key="id"
                                open-on-click hoverable
                                transition dense
                                selectable selected-color="purple darken-4"
                                v-model="selected_categories"
                            >
                                <template v-slot:prepend="{ item, open }">
                                    <img v-if="item.category_poster" :src="item.category_poster" height="32px" alt="">
                                    <div v-else-if="item.color" :style="{backgroundColor:item.color , height:'20px' , width:'20px' , borderRadius:'50%'}"></div>
                                </template>
                            </v-treeview>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn :loading="save_loading" color="indigo darken-4" dark @click="applyChanges()">
                        اعمال
                    </v-btn>
                    <v-btn color="danger" dark @click="modal = false">
                        بستن
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    name:'SelectCategory',
    props: {
        categorizeable_id: {
            type: Number,
            default: 0,
        },
        categorizeable_type: {
            type: String,
            default: "",
        },
        categories_selected_ids: {
            type: Array,
            default: [],
        }
    },
    data: () => ({
        selected_categories:[],
        categories:[],
        open: [],
        search: null,
        loading:false,
        save_loading:false,
        modal:false
    }),
    watch:{
    },
    methods:{
        getCategoriesList(){
            if (this.categories.length > 0) {
                this.modal = true;
                return;
            }
            this.selected_categories = this.categories_selected_ids;
            this.loading = true;
            this.$http.post(`categories/list` , {get_all:true,belongs_to:this.categorizeable_type})
            .then(res => {
                this.categories = res.data.data
                this.loading = false;
                this.modal = true;
            })
            .catch( () => {
                this.loading = false;
            });
        },
        applyChanges(){
            const data = {
                selected_categories:this.selected_categories,
                categorizeable_id:this.categorizeable_id,
                categorizeable_type:this.categorizeable_type
            }
            this.save_loading = true;
            this.$http.post(`categories/sync` , data)
            .then( () => {
                this.save_loading = false;
                this.modal = false;
                this.$emit('refresh')
                this.$fire({
                    title: "موفق",
                    text: 'تغییرات با موفقیت اعمال شد',
                    type: "success",
                    timer: 3000
                })
            })
            .catch( () => {
                this.save_loading = false;
                this.$fire({
                    title: "خطا",
                    text: 'عملیات با خطا مواجه شد',
                    type: "error",
                    timer: 3000
                })
            });
        },
    },
    mounted(){
    }
}
</script>
