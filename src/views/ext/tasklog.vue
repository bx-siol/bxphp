<template>
    <Page :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #search="{ params, tdata, doSearch }">

            <!--            <el-input size="small"
                    style="width: 280px;margin-left: 10px;"
                    placeholder="ID/账号/昵称"
                    clearable
                    v-model="params.s_user"
                    @keyup.enter="doSearch">
                <template #prepend>用户搜索</template>
            </el-input>-->

            <el-input size="small" style="width: 280px;margin-left: 10px;" placeholder="ID/账号/昵称" clearable v-model="params.s_puser"
                @keyup.enter="doSearch">
                <template #prepend>团队搜索</template>
            </el-input>

            <span style="margin-left: 10px;color: #909399;">任务状态:</span>
            <el-select size="small" style="width: 120px;margin-left: 10px;" v-model="params.s_status" placeholder="全部状态">
                <el-option key="0" label="全部状态" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>

        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="tsn" label="任务序列号" width="160"></el-table-column>
            <el-table-column prop="task_name" label="任务名称"></el-table-column>
            <el-table-column prop="account" label="领取账号" width="140"></el-table-column>
            <el-table-column prop="zt_account" :label="'推荐人'" width="120"></el-table-column>
            <el-table-column prop="agent1_account" :label="'一级代理'" width="120">
            </el-table-column>
            <el-table-column prop="agent2_account" :label="'二级代理'" width="120">
            </el-table-column>
            <el-table-column prop="award" label="任务奖励" width="100"></el-table-column>
            <el-table-column prop="voucher" label="任务凭证" width="220">
                <template #default="{ row }">
                    <div style="line-height: 16px;text-align: left;">
                        <div v-if="row.remark">{{ row.remark }}</div>
                        <div style="padding-top: 5px;">
                            <el-image v-for="(item, idx) in row.voucher" :src="imgFlag(item)"
                                @click="onPreviewImg(item)"
                                style="height: 40px;width: 40px;margin-right: 6px;display: inline-block;" />
                        </div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="status_flag" label="状态" width="130"></el-table-column>
            <el-table-column prop="create_time" label="创建时间" width="135"></el-table-column>
            <!--            <el-table-column prop="submit_time" label="提交时间" width="135"></el-table-column>-->
            <el-table-column prop="check_time" label="审核时间" width="135"></el-table-column>
            <el-table-column prop="check_remark" label="审核备注"></el-table-column>
            <el-table-column label="操作" width="120">
                <template #default="scope">
                    <!--                    <el-popconfirm
                            confirmButtonText='确定'
                            cancelButtonText='取消'
                            icon="el-icon-warning"
                            iconColor="red"
                            title="您确定要进行删除吗？"
                            @confirm="del(scope.$index,scope.row)" v-if="power.delete&&scope.row.status==7">
                        <template #reference>
                            <el-button type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>-->
                    <el-button size="small" v-if="power.check && scope.row.status < 9"
                        @click="onCheck(scope.$index, scope.row)" type="success">审核</el-button>
                    <span v-else>/</span>
                </template>
            </el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>记录数：{{ tdata.count }}</span>
        </template>

        <template #layer="{ tdata }">
            <el-dialog title="任务审核" v-model="configForm.checkVisible" :close-on-click-modal="false" :width="500">
                <el-form :label-width="100">
                    <el-form-item label="任务序列号">
                        <el-input size="small" v-model="actItem.tsn" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="任务状态" style="margin-bottom: 0;">
                        <el-radio-group v-model="checkForm.status">
                            <el-radio :label="3">未通过</el-radio>
                            <el-radio :label="9">已完成</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="备注">
                        <el-input size="small" type="textarea" v-model="checkForm.check_remark" autocomplete="off"
                            rows="3"></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="configForm.checkVisible = false">取消</el-button>
                        <el-button type="primary" @click="onCheckSave">提交</el-button>
                    </span>
                </template>
            </el-dialog>
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

const configForm = reactive({
    title: '订单',
    width: '800px',
    labelWidth: '100px',
    top: '1%',
    visible: false,
    isEdit: false,
    checkVisible: false
})

const checkForm = reactive({
    id: 0,
    status: '',
    check_remark: ''
})

const actItem = ref<any>({})
const tableData = ref<any>({

})

//权限控制
const power = reactive({
    check: checkPower('Ext_tasklog_check')
})

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

const onPreviewImg = (src: string) => {
    showImg(getSrcUrl(src))
}

const pageUrl = ref('c=Ext&a=tasklog')
const onPageSuccess = (td: any) => {
    tableData.value = td
}

const onCheck = (idx: number, item: any) => {
    actItem.value = item
    checkForm.id = item.id
    checkForm.check_remark = item.check_remark
    configForm.checkVisible = true
}

const onCheckSave = () => {
    if (!checkForm.status) {
        _alert('请选择审核状态')
        return
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Ext&a=tasklog_check',
        data: checkForm
    }).then((res: any) => {
        _alert(res.msg)
        isRequest = false
        if (res.code != 1) {
            return
        }
        configForm.checkVisible = false
        actItem.value.status_flag = res.data.status_flag
        actItem.value.check_time = res.data.check_time
        actItem.value.check_remark = checkForm.check_remark
        actItem.value.status = checkForm.status
        checkForm.check_remark = ''
    })
}

onMounted(() => {

})

</script>