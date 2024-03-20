<template>
    <div class="conbar">
        <el-breadcrumb separator="/"
            style="padding-left:12px;padding-top: 2px;line-height: 40px;display: inline-block;">
            <slot name="title" :tdata="tableData" :title="compTitle">
                <el-breadcrumb-item>{{ compTitle }}</el-breadcrumb-item>
            </slot>
        </el-breadcrumb>
        <div style="float: right;padding-top: 7px;padding-right: 12px;">
            <slot name="btn" :doSearch="onSearch" :tdata="tableData"></slot>
        </div>
    </div>
    <div class="conbox">
        <!--搜素-->
        <div class="consearch">
            <template v-if="slots.search2">
                <slot name="search2" :params="searchForm" :tdata="tableData" :doSearch="onSearch"></slot>
            </template>
            <template v-else>
                <slot name="search" :params="searchForm" :tdata="tableData" :doSearch="onSearch"></slot>

                <template v-if="searchTimeOpt.need">
                    <el-date-picker
                        :style="{ marginLeft: '10px', width: (searchTimeOpt.type == 'date' ? '150px' : '200px') }"
                        clearable v-model="sStartTime" :type="searchTimeOpt.type"
                        :default-value="searchTimeOpt.start_default_time" :placeholder="startPlaceholder">
                    </el-date-picker>
                    <el-date-picker
                        :style="{ marginLeft: '10px', width: (searchTimeOpt.type == 'date' ? '150px' : '200px') }"
                        clearable v-model="sEndTime" :type="searchTimeOpt.type"
                        :default-value="searchTimeOpt.end_default_time" :placeholder="endPlaceholder">
                    </el-date-picker>
                </template>

                <el-input style="width: 320px;margin-left: 10px;" placeholder="请输入" clearable
                    v-model="searchForm.s_keyword" @keyup.enter="onSearch">
                    <template #prepend>关键词<i class="el-icon-search"></i></template>
                </el-input>
                <el-button @click="exportToExcel">导出</el-button>
                <el-button type="primary" style="margin-left: 10px;" @click="onSearch">查询</el-button>
            </template>
        </div>

        <!--数据表-->
        <div class="user_skills">
            <el-table :header-cell-class-name="cellfun" :data="tableData.list"
                :header-cell-style="{ textAlign: 'center' }" :cell-style="{ textAlign: 'center' }"
                :row-class-name="rowClass" border ref="multipleTable">
                <slot name="table" :tdata="tableData" :delItem="delItem" :params="searchForm" :doSearch="onSearch">
                </slot>
            </el-table>
        </div>


        <div class="consummary" v-if="slots.summary">
            <slot name="summary" :tdata="tableData"></slot>
        </div>

        <!--分页-->
        <div class="conpage">
            <el-pagination background @current-change="handlePage" v-model:currentPage="searchForm.page"
                v-model:page-size="searchForm.s_sizes"
                :page-sizes="[10, 20, 50, 70, 100, 200, 300, 400, 500, 1000, 2000]" @size-change="handleSizeChange"
                :total="tableData.count" :layout="pageLayout">
            </el-pagination>
        </div>
    </div>

    <slot name="layer" :tdata="tableData"></slot>
    <slot name="balance" :tdata="tableData"></slot>
</template>

<script lang="ts" setup>
import { ref, onMounted, useSlots } from 'vue';
import { ElLoading, ILoadingInstance } from 'element-plus'
import { useStore } from "vuex";
import http from "../global/network/http";
import { _alert } from "../global/common";

import * as XLSX from "xlsx";
// import * as XLSXStyle from "xlsx-style";

import dayjs from 'dayjs';

interface DataList {
    count: number,
    limit: number,
    list: any[],
    [propName: string]: any
}
interface kv {
    key: string,
    value: string,
}

interface SearchParams {
    page: number,
    s_keyword: string,
    s_sizes: number,
    [propName: string]: any
}

const emit = defineEmits(['success'])

const getdate = () => {
    const date = new Date()
    return `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}:${date.getSeconds().toString().padStart(2, '0')}`;
}
const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    XLSXname: {
        type: String,
        default: ''
    },
    url: {
        type: String,
        default: ''
    },
    needTime: Boolean,
    timeOpt: Object,
    startPlaceholder: {
        type: String,
        default: '开始时间'
    },
    endPlaceholder: {
        type: String,
        default: '结束时间'
    },
    rowClass: {
        type: Function
    },
    pageLayout: {
        type: String,
        default: 'total, sizes, prev, pager, next, jumper'
    }
})

let hasInit = false
let loadingObj: ILoadingInstance
const loading = () => {
    loadingObj = ElLoading.service({
        lock: true,
        text: '查询中，请稍后...',
        spinner: 'el-icon-loading',
        background: 'rgba(249, 249, 249, 0.5)'
    });
}


const multipleTable = ref()

const sStartTime = ref('')
const sEndTime = ref('')
let date = new Date(new Date().getTime() - 7 * 86400 * 1000)
let year = date.getFullYear()
let month = date.getMonth()
let day = date.getDate()

let date2 = new Date()
let year2 = date2.getFullYear()
let month2 = date2.getMonth()
let day2 = date2.getDate()
const searchTimeOpt = ref({
    need: props.needTime || props.timeOpt ? true : false,
    type: 'date',//datetime date
    start_default_time: new Date(year, month, day, 0, 0, 0),
    end_default_time: new Date(year2, month2, day2, 23, 59, 59),
})
if (props.timeOpt) {
    Object.assign(searchTimeOpt.value, props.timeOpt)
}

const store = useStore()
const slots = useSlots()

const compTitle = ref('')
if (props.title.length > 0) {
    compTitle.value = props.title
} else {
    if (store.state.config.active.name) {
        compTitle.value = store.state.config.active.name
    }
}

const tableData = ref<DataList>({
    count: 0,
    limit: 15,
    list: []
})
const searchForm = ref<SearchParams>({
    page: 1,
    s_keyword: '',
    s_sizes: 30
})
const downHead = ref<any>([])
const cellfun = (item: any) => {
    if (downHead.value[0] == undefined) {
        var txflg = false;
        for (let index = 0; index < item.row.length; index++) {
            const element = item.row[index];
            downHead.value[index] = {
                key: element.property,
                value: element.label,
            }

            if (element.property == 'receive_type') {
                console.log(element.property, index, item.row.length)
                txflg = true
            }
        }


        if (txflg) {
            downHead.value.push({
                key: "receive_realname",
                value: "提现人",
            });
            downHead.value.push({
                key: "receive_account",
                value: "提现账号",
            });
            downHead.value.push({
                key: "receive_bank_name",
                value: "银行名字",
            });
            downHead.value.push({
                key: "receive_ifsc",
                value: "IFSC",
            });


        }


    }
}

const exportToExcel = () => {
    //表头数据切换
    let list = [];
    for (let index = 0; index < tableData.value.list.length; index++) {
        const element = tableData.value.list[index];
        const obj = {}
        for (let indexc = 0; indexc < downHead.value.length; indexc++) {
            const elementc = downHead.value[indexc];
            if (elementc) {
                // console.log(elementc.key)
                if (elementc.key != undefined && elementc.key != 'undefined' && elementc.key != 'checked') {
                    // console.log(elementc.value + ':' + element[elementc.key])
                    // console.log(elementc.key)//收款信息
                    if (elementc.key == 'receive_type') {

                        var txstr = element['receive_type_flag'] == undefined ? '' : element['receive_type_flag'];

                        if (element['bank_name']) { txstr += " BankName：" + element['bank_name']; }
                        if (element['receive_bank_name']) { txstr += " BankNameR：" + element['receive_bank_name']; }
                        if (element['receive_ifsc']) { txstr += " IFSC：" + element['receive_ifsc']; }
                        if (element['receive_realname']) { txstr += " Realname：" + element['receive_realname']; }
                        if (element['receive_account']) { txstr += " Account：" + element['receive_account']; }
                        if (element['receive_address_type'] > 0) { txstr += " " + element['receive_address_type_flag']; }
                        if (element['receive_address_protocol'] > 0) { txstr += " " + element['receive_address_protocol_flag']; }
                        if (element['receive_address_channel'] > 0) { txstr += " " + element['receive_address_channel_flag']; }
                        if (element['receive_address']) { txstr += " Address：" + element['receive_address']; }
                        if (element['receive_phone']) { txstr += " Phone：" + element['receive_phone']; }
                        if (element['receive_email']) { txstr += " Email：" + element['receive_email']; }

                        obj[elementc.value] = txstr
                    } else
                        obj[elementc.value] = element[elementc.key]
                }
            }
        }
        list.push(obj);
    }
    // 创建工作表
    const data = XLSX.utils.json_to_sheet(list)
    // 创建工作簿
    const wb = XLSX.utils.book_new()
    // 将工作表放入工作簿中
    XLSX.utils.book_append_sheet(wb, data, 'data')

    // 生成文件并下载 
    XLSX.writeFile(wb, props.XLSXname + getdate() + '.xlsx')
}


const handleSizeChange = (val: number) => {
    searchForm.value.s_sizes = val
    getPage()
}
const getPage = () => {
    if (sStartTime.value) {
        let s_start_time = dayjs(new Date(sStartTime.value)).format('YYYY-MM-DD')
        if (searchTimeOpt.value.type == 'datetime') {
            s_start_time = dayjs(new Date(sStartTime.value)).format('YYYY-MM-DD HH:mm:ss')
        }
        searchForm.value.s_start_time = s_start_time
    } else {
        // searchForm.value.s_start_time=''
    }
    if (sEndTime.value) {
        let s_end_time = dayjs(new Date(sEndTime.value)).format('YYYY-MM-DD')
        if (searchTimeOpt.value.type == 'datetime') {
            s_end_time = dayjs(new Date(sEndTime.value)).format('YYYY-MM-DD HH:mm:ss')
        }
        searchForm.value.s_end_time = s_end_time
    } else {
        // searchForm.value.s_end_time=''
    }

    if (searchForm.value.s_start_time_flag) {
        let s_start_time = dayjs(new Date(searchForm.value.s_start_time_flag)).format('YYYY-MM-DD')
        searchForm.value.s_start_time = s_start_time
    }

    if (searchForm.value.s_end_time_flag) {
        let s_end_time = dayjs(new Date(searchForm.value.s_end_time_flag)).format('YYYY-MM-DD')
        searchForm.value.s_end_time = s_end_time
    }

    if (searchForm.value.s_start_time2_flag) {
        let s_start_time2 = dayjs(new Date(searchForm.value.s_start_time2_flag)).format('YYYY-MM-DD')
        searchForm.value.s_start_time2 = s_start_time2
    }

    if (searchForm.value.s_end_time2_flag) {
        let s_end_time2 = dayjs(new Date(searchForm.value.s_end_time2_flag)).format('YYYY-MM-DD')
        searchForm.value.s_end_time2 = s_end_time2
    }

    if (hasInit) {
        loading()
    }

    http({
        url: props.url,
        data: searchForm.value
    }).then((res: any) => {
        if (loadingObj) {
            loadingObj.close()
        }

        if (res.code != 1) {
            _alert(res.msg)
            return
        }

        for (let i in res.data) {
            tableData.value[i] = res.data[i]
        }
        emit('success', res.data)
        setTimeout(() => { store.dispatch('loadingFinish'); }, store.state.loadingTime)
    })
}

const handlePage = () => {
    getPage()
}

const onSearch = () => {
    searchForm.value.page = 1
    getPage()
}

//初始化
const init = () => {
    onSearch()
}

const doSearch = () => {
    onSearch()
}

const doSearch2 = (pdata: any) => {
    searchForm.value = pdata
    onSearch()
}

const delItem = (idx: number) => {
    tableData.value.list.splice(idx, 1)
    tableData.value.count -= 1
}

const getTdata = () => {
    return tableData.value
}

defineExpose({
    tableData,
    searchForm,
    getTdata,
    delItem,
    doSearch,
    doSearch2,
    multipleTable
})

onMounted(() => {
    init()
    hasInit = true
})

</script>

<style>
.el-table td.imgCellBox {
    padding: 0px;
}
</style>