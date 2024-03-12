<template>
    <Page url="c=Product&a=order&s_is_give=1&s_cid=1001" ref="pageRef">
        <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">添加赠送</el-button>
        </template>

        <template #search="{ params, tdata, doSearch }">

        </template>

        <template #table>
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="osn" label="订单号" width="160"></el-table-column>
            <el-table-column prop="account" label="用户账号" width="140"></el-table-column>
            <!--        <el-table-column prop="nickname" label="用户昵称" width="130"></el-table-column>-->
            <el-table-column prop="category_name" label="产品分类" width="120"></el-table-column>
            <el-table-column prop="goods_name" label="产品名称" min-width="320"></el-table-column>
            <el-table-column prop="days" label="期限(天)" width="100"></el-table-column>
            <el-table-column prop="rate" label="收益率(%)" width="100"></el-table-column>
            <el-table-column prop="price" label="产品单价" width="120"></el-table-column>
            <el-table-column prop="num" label="数量" width="100"></el-table-column>
            <el-table-column prop="money" label="总额度" width="120"></el-table-column>
            <el-table-column prop="total_reward" label="累计收益额度" width="130"></el-table-column>
            <el-table-column prop="total_days" label="累计收益天数" width="130"></el-table-column>
            <el-table-column prop="status_flag" label="状态" width="130"></el-table-column>
            <el-table-column prop="create_time" label="创建时间" width="105"></el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-popconfirm confirmButtonText='确定' cancelButtonText='取消' icon="el-icon-warning" iconColor="red"
                        title="您确定要进行删除吗？" @confirm="del(scope.$index, scope.row)" v-if="power.delete">
                        <template #reference>
                            <el-button type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>
                    <el-button size="small" v-if="power.update && scope.row.status == 1"
                        @click="edit(scope.$index, scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>记录数：{{ tdata.count }}</span>
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="赠送账号">
                        <el-input v-model="dataForm.account" autocomplete="off" placeholder=""
                            :disabled="configForm.isEdit"></el-input>
                    </el-form-item>
                    <el-form-item label="赠送设备">
                        <el-select v-model="dataForm.gid" placeholder="请选择设备" style="width: 100%;"
                            :disabled="configForm.isEdit">
                            <el-option key="0" label="请选择设备" value="0"></el-option>
                            <el-option v-for="(item, idx) in tdata.goods_arr" :key="item.id"
                                :label="item.price + ' ' + item.name" :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="赠送数量">
                        <el-input v-model="dataForm.num" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="收益天数">
                        <el-input v-model="dataForm.days" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <input type="hidden" v-model="dataForm.id" />
                        <el-button @click="configForm.visible = false">取消</el-button>
                        <el-button type="primary" @click="save">保存</el-button>
                    </span>
                </template>
            </el-dialog>
        </template>

    </Page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Page from '../../components/Page.vue';
import MyUpload2 from '../../components/Upload.vue';

export default defineComponent({
    components: {
        Page, MyUpload2
    }
})
</script>

<script lang="ts" setup>
import { ref, reactive, onMounted, getCurrentInstance, nextTick } from 'vue';
import { useStore } from "vuex";
import http from "../../global/network/http";
import { getZero, _alert, getSrcUrl, showImg } from "../../global/common";
import { checkPower } from '../../global/user';
import md5 from 'md5';
import { ElMessageBox } from "element-plus";


let isRequest = false
const store = useStore()
const insObj = getCurrentInstance()
const pageRef = ref()
const editor = ref()

const configForm = reactive({
    title: '',
    width: '500px',
    labelWidth: '100px',
    // top:'1%',
    visible: false,
    isEdit: false
})

const coverList = ref<any>([])
const iconList = ref<any>([])
const actItem = ref<any>()

const dataForm = reactive<any>({
    id: 0,
    account: '',
    days: '',
    num: '',
    gid: ''
})

//权限控制
const power = reactive({
    update: checkPower('Product_order_update'),
    delete: checkPower('Product_order_delete')
})

const add = () => {
    dataForm.id = 0
    dataForm.account = ''
    dataForm.num = ''
    dataForm.days = ''
    dataForm.gid = '0'
    configForm.visible = true
    configForm.title = '添加赠送'
    configForm.isEdit = false
}

const edit = (idx: number, item: any) => {
    actItem.value = item
    dataForm.id = item.id
    dataForm.account = item.account
    dataForm.num = item.num
    dataForm.days = item.days
    dataForm.gid = item.gid
    configForm.visible = true
    configForm.title = '编辑赠送'
    configForm.isEdit = true
}

//弹层打开后回调
const dialogOpened = () => {

}

//弹层关闭后
const onDialogClosed = () => {

}

const save = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    let pdata = {}
    for (let i in dataForm) {
        pdata[i] = dataForm[i]
    }
    http({
        url: 'c=Product&a=order_update',
        data: pdata
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        configForm.visible = false  //关闭弹层
        if (!configForm.isEdit) {//添加的重新加载
            pageRef.value.doSearch()
        } else {//动态更新字段
            for (let i in dataForm) {
                actItem.value[i] = dataForm[i]
            }
            actItem.value.money = res.data.money
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                isRequest = false
            }
        })
    })
}

const del = (idx: number, item: any) => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Product&a=order_delete',
        data: { id: item.id }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        //更新数据集
        pageRef.value.delItem(idx)

        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                isRequest = false
            }
        })
    })
}

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

const onPreviewImg = (src: string) => {
    showImg(getSrcUrl(src))
}

onMounted(() => {

})
</script>