<template>
    <Page :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #search="{ params, tdata, doSearch }">

            <span style="margin-left: 10px;color: #909399;">{{ isTrans ? 'Date' : '日期' }}:</span>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable v-model="params.s_start_time_flag"
                type="date" :placeholder="isTrans ? 'Date' : '开始日期'">
            </el-date-picker>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable v-model="params.s_end_time_flag"
                type="date" :placeholder="isTrans ? 'Date' : '结束日期'">
            </el-date-picker>
            <span style="font-size: 14px;margin-left: 10px;">{{ isTrans ? 'Translate' : '翻译' }}：</span>
            <el-select size="small" style="width: 70px;" v-model="params.s_trans" placeholder="否" @change="onTrans">
                <el-option key="0" :label="isTrans ? 'No' : '否'" value="0"></el-option>
                <el-option key="1" :label="isTrans ? 'Yes' : '是'" value="1"></el-option>
            </el-select>
        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="account" :label="isTrans ? 'Account' : '用户账号'"></el-table-column>
            <el-table-column prop="nickname" :label="isTrans ? 'User nickname' : '用户昵称'"></el-table-column>
            <el-table-column prop="type_flag" :label="isTrans ? 'Award Type' : '奖品类型'"></el-table-column>
            <el-table-column prop="prize_name" :label="isTrans ? 'Prize Name' : '奖品名称'"></el-table-column>
            <el-table-column prop="money" :label="isTrans ? 'Winning Amount' : '中奖金额'"></el-table-column>
            <el-table-column prop="goods_name" :label="isTrans ? 'Products' : '系统产品'"></el-table-column>
            <el-table-column prop="prize_name" :label="isTrans ? 'Ticket' : '奖券'"></el-table-column>
            <el-table-column prop="remark" :label="isTrans ? 'Description' : '中奖描述'"></el-table-column>
            <el-table-column prop="create_time" :label="isTrans ? 'Date' : '时间'"></el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>{{ isTrans ? 'Records' : '记录数' }}：{{ tdata.count }}</span>
            <span>{{ isTrans ? 'Total money' : '累计金额：' }}{{ tdata.money }}</span>
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
//翻译相关
const isTrans = ref(false)
const onTrans = (ev: any) => {
    if (ev == 1) {
        isTrans.value = true
    } else {
        isTrans.value = false
    }
    pageRef.value.doSearch()
}
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

const pageUrl = ref('c=Gift&a=prizeLog')
const onPageSuccess = (td: any) => {
    tableData.value = td
}

onMounted(() => {

})

</script>