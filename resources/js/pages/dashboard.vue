<template>
    <div>
        <v-container>
            <v-row dense>
                <v-col cols="12" lg="3" md="6" sm="12">
                    <v-card
                        color="#D81B60"
                        dark
                        :to="{name:'users'}"
                        class="white--text"
                    >
                        <v-card-title>
                            <h3>کل کاربران</h3>
                        </v-card-title>
                        <v-card-title>
                            <v-spacer></v-spacer>
                            <h1>{{ statistics.total_users }}</h1>
                        </v-card-title>
                        <v-card-actions>
                            <v-tooltip top>
                                <template #activator="{ on, attrs }">
                                    <div v-bind="attrs" v-on="on">کاربران جدید</div>
                                </template>
                                <span>تعداد کاربران در 7 روز گذشته</span>
                            </v-tooltip>
                            <v-divider></v-divider>
                            <div>{{ statistics.new_users }}</div>
                        </v-card-actions>
                    </v-card>
                </v-col>
                <v-col cols="12" lg="3" md="6" sm="12">
                    <v-card
                        color="#0D47A1"
                        dark
                        :to="{name:'musics'}"
                        class="white--text"
                    >
                        <v-card-title>
                            <h3>کل آهنگ ها</h3>
                        </v-card-title>
                        <v-card-title>
                            <v-spacer></v-spacer>
                            <h1>{{ statistics.total_musics }}</h1>
                        </v-card-title>
                        <v-card-actions>
                            <v-tooltip top>
                                <template #activator="{ on, attrs }">
                                    <div v-bind="attrs" v-on="on">آهنگ های جدید</div>
                                </template>
                                <span>تعداد آهنگ های 7 روز گذشته</span>
                            </v-tooltip>
                            <v-divider></v-divider>
                            <div>{{ statistics.new_musics }}</div>
                        </v-card-actions>
                    </v-card>
                </v-col>
                <v-col cols="12" lg="3" md="6" sm="12">
                    <v-card
                        color="#ff9b20"
                        dark
                        :to="{name:'comments'}"
                        class="white--text"
                    >
                        <v-card-title>
                            <h3>کل نظرات</h3>
                        </v-card-title>
                        <v-card-title>
                            <v-spacer></v-spacer>
                            <h1>{{ statistics.total_comments }}</h1>
                        </v-card-title>
                        <v-card-actions>
                            <v-tooltip top>
                                <template #activator="{ on, attrs }">
                                    <div v-bind="attrs" v-on="on">تایید نشده</div>
                                </template>
                                <span>تعداد نظرات تایید نشده</span>
                            </v-tooltip>
                            <v-divider></v-divider>
                            <div>{{ statistics.pending_comments }}</div>
                        </v-card-actions>
                    </v-card>
                </v-col>
                <v-col cols="12" lg="3" md="6" sm="12">
                    <v-card
                        color="#6b32a8"
                        dark
                        :to="{name:'pays'}"
                        class="white--text"
                    >
                        <v-card-title>
                            <h3>پرداختی امروز</h3>
                            <v-spacer></v-spacer>
                            <h3>{{ statistics.today_total_pays }}</h3>
                        </v-card-title>
                        <v-card-title>
                            <h3>پرداختی دیروز</h3>
                            <v-spacer></v-spacer>
                            <h3>{{ statistics.yesterday_total_pays }}</h3>
                        </v-card-title>
                        <v-card-actions>
                            <v-tooltip top>
                                <template #activator="{ on, attrs }">
                                    <div v-bind="attrs" v-on="on">پرداختی هفته اخیر</div>
                                </template>
                                <span>جمع پرداختی هفت روز اخیر</span>
                            </v-tooltip>
                            <v-divider></v-divider>
                            <div>{{ statistics.week_total_pays }}</div>
                        </v-card-actions>
                    </v-card>
                </v-col>

            </v-row>

            <v-row dense class="mt-4 pt-4">
                <v-col cols="12" lg="6" md="12" sm="12">
                    <h3>نمودار ثبت نام کاربران</h3>
                    <div class="pa-4">
                        <v-select
                            :items="[{value:'this_month' , title:'ماه جاری'},{value:'last_month' , title:'سی روز اخیر'}
                                    ,{value:'this_year' , title:'سال جاری'},{value:'last_year' , title:'12 ماه اخیر'}]"
                            v-model="user_chart_period"
                            item-text="title"
                            item-value="value"
                            label="دوره زمانی"
                            outlined dense hide-details
                            @change="getStatistics()"
                            :disabled="fetch_loading"
                        ></v-select>
                    </div>
                    <apexchart type="line" height="400" :options="UsersChartOptions" :series="users_series"></apexchart>
                </v-col>
                <v-col cols="12" lg="6" md="12" sm="12">
                    <h3>نمودار پرداخت ها</h3>
                    <div class="pa-4">
                        <v-select
                            :items="[{value:'this_month' , title:'ماه جاری'},{value:'last_month' , title:'سی روز اخیر'}
                                    ,{value:'this_year' , title:'سال جاری'},{value:'last_year' , title:'12 ماه اخیر'}]"
                            v-model="income_chart_period"
                            item-text="title"
                            item-value="value"
                            label="دوره زمانی"
                            outlined dense hide-details
                            @change="getStatistics()"
                            :disabled="fetch_loading"
                        ></v-select>
                    </div>
                    <apexchart type="line" height="400" :options="IncomeChartOptions" :series="income_series"></apexchart>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>
<script>
export default {
    name:'dashboard',
    data: () => ({
        statistics: {
            'total_users':0,
            'new_users':0,
            'total_musics':0,
            'new_musics':0,
            'total_comments':0,
            'pending_comments':0,
            'today_total_pays':0,
            'yesterday_total_pays':0,
            'week_total_pays':0
        },
        fetch_loading: false,
        user_chart_period: 'this_month',
        income_chart_period: 'this_month',
        users_series: [
            {
                data: []
            },
            {
                data: []
            }
        ],
        income_series: [
            {
                data: []
            },
            {
                data: []
            }
        ],
        UsersChartOptions: {
            chart: {
                id:'user',
                // dropShadow: {
                //     enabled: true,
                //     color: '#000',
                //     top: 18,
                //     left: 7,
                //     blur: 10,
                //     opacity: 0.5
                // },
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            colors: ['#4d0293', 'rgba(191,191,191,0.45)'],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth'
            },
            // title: {
            //     text: 'ثبت نام کاربران',
            //     align: 'left'
            // },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
            },
            markers: {
                size: 3
            },
            xaxis: {
                // categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                // title: {
                //     text: 'Month'
                // }
            },
            yaxis: {
                // title: {
                //     text: 'Temperature'
                // },
                // min: 5,
                // max: 40
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                floating: true,
                offsetY: -25,
                offsetX: -5
            }
        },
        IncomeChartOptions: {
            chart: {
                id:'income',
                // dropShadow: {
                //     enabled: true,
                //     color: '#000',
                //     top: 18,
                //     left: 7,
                //     blur: 10,
                //     opacity: 0.5
                // },
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            colors: ['#4d0293', 'rgba(191,191,191,0.45)'],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth'
            },
            // title: {
            //     text: 'ثبت نام کاربران',
            //     align: 'left'
            // },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
            },
            markers: {
                size: 3
            },
            xaxis: {
                // categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                // title: {
                //     text: 'Month'
                // }
            },
            yaxis: {
                // title: {
                //     text: 'Temperature'
                // },
                // min: 5,
                // max: 40
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                floating: true,
                offsetY: -25,
                offsetX: -5
            }
        },
    }),
    methods: {
        getStatistics(){
            this.fetch_loading = true
            this.$http.get(`index/statistics?user_chart_period=`+this.user_chart_period+`&income_chart_period=`+this.income_chart_period)
                .then(res => {
                    const data = res.data.data;
                    this.statistics = data.statistics;
                    this.users_series = data.user_chart_data;
                    this.income_series = data.income_chart_data;
                })
                .catch( (err) => {
                    console.log(err)
                }).finally( () => {
                this.fetch_loading = false
            });
        },
    },
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('داشبورد')
    },
    mounted(){
        this.getStatistics()
    }
}
</script>
<style>
</style>
