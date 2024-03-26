<template>
    <Page :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #search="{ params, tdata, doSearch }">

            <span style="margin-left: 10px;color: #909399;">日期:</span>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable v-model="params.s_start_time_flag"
                type="date" placeholder="开始日期">
            </el-date-picker>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable v-model="params.s_end_time_flag"
                type="date" placeholder="结束日期">
            </el-date-picker>
            <el-input size="small" style="width: 180px;margin-left: 10px;" placeholder="用户账号" v-model="params.s_user"></el-input>

        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="rsn" label="红包码"></el-table-column>
            <el-table-column prop="account" label="领取账号"></el-table-column>
            <el-table-column prop="nickname" label="用户昵称"></el-table-column>
            <el-table-column prop="money" label="红包金额"></el-table-column>
            <el-table-column prop="c_account" label="红包创建人"></el-table-column>
            <el-table-column prop="receive_time" label="领取时间"></el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>记录数：{{ tdata.count }}</span>
            <span>累计金额：{{ tdata.money }}</span>
        </template>

        <template #layer="{ tdata }">

        </template>
    </Page>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import Page from '../../components/Page.vue';

export default defineComponent({
    components: {
        Page
    }
})
</script>

<script lang="ts" setup>
import { ref, onMounted, nextTick, reactive } from 'vue';
import { useStore } from "vuex";
import { _alert, getSrcUrl, showImg } from "../../global/common";
import dayjs from "dayjs";
import { useRoute } from "vue-router";
import http from "../../global/network/http";

let isRequest = false
const store = useStore()
const route = useRoute()
const pageRef = ref()

const configForm = reactive({
    title: '',
    width: '800px',
    labelWidth: '100px',
    top: '1%',
    visible: false,
    isEdit: false
})

const actItem = ref<any>({})
const tableData = ref<any>({})

//权限控制
const power = reactive({
    //delete:checkPower('Shop_order_delete'),
})

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

const onPreviewImg = (src: string) => {
    showImg(getSrcUrl(src))
}

const pageUrl = ref('c=Gift&a=redpackLog')
const onPageSuccess = (td: any) => {
    tableData.value = td
}

onMounted(() => {

})

</script>