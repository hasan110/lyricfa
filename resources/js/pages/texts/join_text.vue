<template>
    <div>
        <div class="page-head">
            <div class="titr">اتصال متن</div>
            <div class="back">
                <router-link :to="{ name : 'dashboard' }">بازگشت
                    <v-icon>mdi-chevron-left</v-icon>
                </router-link>
            </div>
        </div>

        <v-container fluid>
            <v-row justify="center">
                <v-col cols="12" sm="10" md="8" lg="6">
                    <join-text-to-vendor></join-text-to-vendor>
                </v-col>
            </v-row>
        </v-container>

    </div>
</template>
<script>
export default {
    name:'join_text',
    beforeMount(){
        this.checkAuth()
        this.setPageTitle('اتصال متن')
    }
}
</script>
