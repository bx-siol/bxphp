<template>
    <Page :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #search="{ params, tdata, doSearch }">

            <span style="margin-left: 10px;color: #909399;">产品分类:</span>
            <el-select size="small" style="width: 160px;margin-left: 10px;" v-model="params.s_cid" @change="onChangeCid"
                placeholder="全部分类">
                <el-option key="0" label="全部分类" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.category_tree" :key="item.id" :label="item.name"
                    :value="item.id">
                </el-option>
            </el-select>

            <span style="margin-left: 10px;color: #909399;">所属产品:</span>
            <el-select size="small" style="width: 280px;margin-left: 10px;" v-model="params.s_gid" placeholder="全部产品">
                <el-option key="0" label="全部产品" value="0"></el-option>
                <el-option v-for="(item, idx) in tableData.goods_arr" :key="item.id" :label="item.name"
                    :value="item.id">
                </el-option>
            </el-select>

            <el-input size="small" style="width: 280px;margin-left: 10px;" placeholder="ID/账号/昵称" clearable
                v-model="params.s_user" @keyup.enter="doSearch">
                <template #prepend>投资用户</template>
            </el-input>

            <el-input size="small" style="width: 280px;margin-left: 10px;" placeholder="ID/账号/昵称" clearable
                v-model="params.s_puser" @keyup.enter="doSearch">
                <template #prepend>团队搜索</template>
            </el-input>

            <div style="clear: both;height: 10px;"></div>

            <span style="margin-left: 10px;color: #909399;">订单状态:</span>
            <el-select size="small" style="width: 120px;margin-left: 10px;" v-model="params.s_status"
                placeholder="全部状态">
                <el-option key="0" label="全部状态" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>

            <span style="margin-left: 10px;color: #909399;">日期:</span>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable
                v-model="params.s_start_time_flag" type="date" placeholder="开始日期">
            </el-date-picker>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable
                v-model="params.s_end_time_flag" type="date" placeholder="结束日期">
            </el-date-picker>

        </template>

        <template #table="myScope">

            <el-table-column prop="checked" :label="'选择'" width="50" fixed>
                <template #default="{ row, $index }">
                    <template v-if="((row.p1 == 0))">
                        <el-checkbox v-model="selectAllArr[$index]" size="large" @change="onSelectItem($index, $event)">
                        </el-checkbox>
                    </template>
                    <template v-else>/</template>
                </template>
            </el-table-column>

            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="osn" label="订单号" width="160"></el-table-column>
            <el-table-column prop="account" label="用户账号" width="140"></el-table-column>
            <el-table-column prop="paccount" label="上级账号" width="140"></el-table-column>
            <el-table-column prop="category_name" label="产品分类" width="120"></el-table-column>
            <el-table-column prop="goods_name" label="产品名称" min-width="140"></el-table-column>

            <el-table-column prop="price1" label="用户奖励" width="100"></el-table-column>
            <el-table-column prop="price2" label="上级奖励" width="100"></el-table-column>


            <el-table-column prop="p1" label="审核状态" width="100">
                <template #default="scope">
                    <span style="color:black" v-if="scope.row.p1 == 0">待审核</span>
                    <span style="color:#85ce61" v-if="scope.row.p1 == 1">通过</span>
                    <span style="color:red" v-if="scope.row.p1 == 2">拒绝</span>
                </template>
            </el-table-column>
            <el-table-column prop="p1" label="审核状态" width="200">
                <template #default="scope">

                    <el-button size="small" v-if="power.set && (scope.row.p1 == 0)"
                        @click="onOrderSet(scope.$index, scope.row, 1)" type="success">通过</el-button>

                    <el-button size="small" v-if="power.set && scope.row.p1 == 0"
                        @click="onOrderSet(scope.$index, scope.row, 2)" type="danger">拒绝</el-button>

                </template>
            </el-table-column>

            <!-- <el-table-column prop="p3" label="性别">
                <template slot-scope="scope">
                    <span v-if="scope.row.p3 == 0">男</span>
                    <span v-if="scope.row.p3 == 1">女</span>
                </template>
            </el-table-column> -->

            <el-table-column prop="days" label="期限(天)" width="100"></el-table-column>
            <el-table-column prop="rate" label="收益率(%)" width="100"></el-table-column>
            <el-table-column prop="price" label="产品单价" width="120"></el-table-column>
            <el-table-column prop="num" label="购买数量" width="100"></el-table-column>

            <!-- <el-table-column prop="money" label="总额度" width="120"></el-table-column>
            <el-table-column prop="total_reward" label="累计收益额度" width="130"></el-table-column>
            <el-table-column prop="total_days" label="累计收益天数" width="130"></el-table-column>
            <el-table-column prop="status_flag" label="状态" width="130"></el-table-column> -->
            <el-table-column prop="create_time" label="创建时间" width="105"></el-table-column>

        </template>

        <template #summary="{ tdata }">
            <div class="plActionBox" style="text-align: left;  ">
                <span style="display: inline-block;width: 55px;text-align: center;">
                    <el-checkbox v-model="selectAll" size="large" @change="onSelectAll"></el-checkbox>
                </span>
                <el-button size="small" type="success" @click="onPlAction(1)"> 批量通过
                </el-button>
                <el-button size="small" type="danger" @click="onPlAction(2)"> 批量驳回
                </el-button>
            </div>

            <span>订单数：{{ tdata.count }}</span>
            <span>投资总额：{{ tdata.money }}</span>
            <span>收益总额：{{ tdata.total_reward }}</span>
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
import { checkPower } from "../../global/user";
import order from "./order.vue";

let isRequest = false
const store = useStore()
const route = useRoute()
const pageRef = ref()
const selectAllArr = ref([])
const selectAll = ref(false)

const configForm = reactive({
    title: '订单',
    width: '800px',
    labelWidth: '100px',
    top: '1%',
    visible: false,
    isEdit: false
})

const actItem = ref<any>({})
const tableData = ref<any>({
    goods_arr: []
})

//权限控制
const power = reactive({
    //delete:checkPower('Product_order_delete'),
    set: checkPower('Product_order_set1')
})

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

const onPreviewImg = (src: string) => {
    showImg(getSrcUrl(src))
}

const pageUrl = ref('c=Product&a=order&t1=1')
const onPageSuccess = (td: any) => {
    tableData.value = td
    selectAllArr.value = []
    for (let i in td.list) {
        let row = td.list[i]
        if (row.status == 1 || (row.status == 9 && row.pay_status == 3)) {
            selectAllArr.value[i] = false
        }
    }
}

//分类切换
const onChangeCid = (ev: any) => {
    pageRef.value.searchForm.s_gid = '0'
    http({
        url: 'c=Product&a=getGoodsByCid',
        data: { cid: ev }
    }).then((res: any) => {
        tableData.value.goods_arr = res.data.list
    })
}

const setShow = ref(false)
const orderForm = reactive({
    status: 0
})

const onOrderSet = (idx: number, item: any, t: number) => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Product&a=order_set1',
        data: {
            id: item.id,
            status: t,
        }
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert(res.msg)
        pageRef.value.doSearch()
    })
}

//全选
const onSelectAll = (ev: Boolean) => {
    for (let i in selectAllArr.value) {
        selectAllArr.value[i] = ev
    }
}

//单选
const onSelectItem = (idx: number, ev: Boolean) => {
    if (!ev) {
        selectAll.value = false
    } else {
        initSelectAll()
    }
}

const initSelectAll = () => {
    let isAll = true
    console.log(selectAllArr.value)
    if (selectAllArr.value.length < 1) {
        isAll = false
    } else {
        for (let i in selectAllArr.value) {
            if (!selectAllArr.value[i]) {
                isAll = false
                break
            }
        }
    }
    selectAll.value = isAll
}

const getActionIdxs = () => {
    let idxs = []
    for (let i in selectAllArr.value) {
        if (selectAllArr.value[i]) {
            idxs.push(i)
        }
    }
    return idxs
}

const getActionIdxById = (id: number) => {
    let idx = -1
    for (let i in pageRef.value.tableData.list) {
        if (pageRef.value.tableData.list[i].id == id) {
            idx = i
        }
    }
    return idx
}

const getActionIds = () => {
    let ids = []
    for (let i in selectAllArr.value) {
        if (selectAllArr.value[i]) {
            ids.push(pageRef.value.tableData.list[i].id)
        }
    }
    return ids
}


const onPlAction = (status: number) => {
    // pageRef.value.multipleTable.toggleAllSelection()
    let idxs = getActionIdxs()
    if (idxs.length < 1) {
        _alert('至少需要选择一项')
        return
    }
    let ids = getActionIds()
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Product&a=order_check_all',
        data: {
            ids: ids,
            status: status
        }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                pageRef.value.doSearch()
            }
        })
    })
}
const onOrderSetSave = () => {
    if (orderForm.status == 0) {
        _alert('请选择订单状态')
        return
    }

}

onMounted(() => {

})

</script>
<style>
.plActionBox span {
    padding: 0;
}
</style>