<template>
    <Page url="c=Gift&a=redpack" ref="pageRef">
        <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">添加红包</el-button>
        </template>

        <template #search="{ params, tdata, doSearch }">
            <el-select size="small" style="width: 110px;margin-left: 10px;" v-model="params.s_status" placeholder="所有状态">
                <el-option key="0" label="所有状态" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
        </template>

        <template #table>
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="rsn" label="红包码" min-width="160"></el-table-column>
            <el-table-column prop="name" label="红包名称" min-width="280"></el-table-column>
            <el-table-column prop="icon" label="图标" width="160">
                <template #default="{ row }">
                    <img v-if="row.icon" :src="imgFlag(row.icon)" @click="onPreviewImg(row.icon)"
                        style="height: 40px;vertical-align: middle;">
                    <span v-else>/</span>
                </template>
            </el-table-column>
            <el-table-column prop="total_money" label="红包总额" width="120"></el-table-column>
            <el-table-column prop="quantity" label="红包数量" width="120"></el-table-column>
            <el-table-column prop="receive_money" label="已领取总额" width="120"></el-table-column>
            <el-table-column prop="receive_quantity" label="已领取数量" width="120"></el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-button size="small" v-if="power.update" @click="edict(scope.$index, scope.row)">查看</el-button>
                </template>
            </el-table-column>
            <el-table-column prop="account" label="创建人" width="140"></el-table-column>
            <el-table-column prop="create_time" label="创建时间" width="140"></el-table-column>
            <el-table-column prop="status_flag" label="状态" width="70"></el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-popconfirm confirmButtonText='确定' cancelButtonText='取消' icon="el-icon-warning" iconColor="red"
                        title="您确定要进行删除吗？" @confirm="del(scope.$index, scope.row)" v-if="power.delete">
                        <template #reference>
                            <el-button type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>
                    <el-button size="small" v-if="power.update" @click="edit(scope.$index, scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>记录数：{{ tdata.count }}</span>
            <span>领取总数：{{ tdata.receive_quantity }}</span>
            <span>领取总额：{{ tdata.receive_money }}</span>
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="红包名称">
                        <el-input size="small" v-model="dataForm.name" autocomplete="off" placeholder="任意填写，便于区分红包用途"></el-input>
                    </el-form-item>
                    <el-form-item label="红包总额">
                        <el-input size="small" v-model="dataForm.total_money" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder="" style="width: 180px;"></el-input>
                    </el-form-item>
                    <el-form-item label="红包数量">
                        <el-input size="small" v-model="dataForm.quantity" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder="" style="width: 180px;"></el-input>
                        <span> 大于等于1且需要保障每包最小额度0.01</span>
                    </el-form-item>
                    <el-form-item label="图标">
                        <MyUpload2 v-model:file-list="iconList" width="80px" height="80px" style="line-height: initial;">
                        </MyUpload2>
                    </el-form-item>
                    <!--                <el-form-item label="相册">
                    <MyUpload v-model:file-list="coverList" :limit="5" width="180px" height="100px" style="line-height: initial;"></MyUpload>
                </el-form-item>-->
                    <el-form-item label="状态" style="margin-bottom: 0;">
                        <el-radio-group v-model="dataForm.status">
                            <el-radio :label="idx" v-for="(item, idx) in tdata.status_arr">{{ item }}</el-radio>
                        </el-radio-group>
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

            <el-dialog :title="configForm.title" v-model="configForm.visible1" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened">

                <Page title="领取记录" :key="pageRefkey" :url="pageUrllqr" ref="pageReflqr">
                    <template #table="myScope">
                        <el-table-column prop="id" label="ID" width="80"></el-table-column>
                        <el-table-column prop="rsn" label="红包码"></el-table-column>
                        <el-table-column prop="account" label="领取账号"></el-table-column>
                        <el-table-column prop="nickname" label="用户昵称"></el-table-column>
                        <el-table-column prop="money" label="红包金额"></el-table-column>
                        <el-table-column prop="c_account" label="红包创建人"></el-table-column>
                        <el-table-column prop="receive_time" label="领取时间"></el-table-column>
                    </template>
                </Page>


                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="configForm.visible1 = false">关闭</el-button>
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
const pageReflqr = ref()

const configForm = reactive({
    title: '',
    width: '600px',
    labelWidth: '100px',
    // top:'1%',
    visible: false,
    isEdit: false,
    visible1: false,
})
const pageRefkey = ref(0);
const pageUrllqr = ref('c=Gift&a=redpackLog')
const coverList = ref<any>([])
const iconList = ref<any>([])
const actItem = ref<any>()

const dataForm = reactive<any>({
    id: 0,
    name: '',
    icon: '',
    total_money: '',
    quantity: '',
    status: '1',
    covers: []
})

//权限控制
const power = reactive({
    update: checkPower('Gift_redpack_update'),
    delete: checkPower('Gift_redpack_delete')
})

const add = () => {
    dataForm.id = 0
    dataForm.name = ''
    dataForm.total_money = ''
    dataForm.quantity = ''
    dataForm.icon = ''
    dataForm.status = '3'
    dataForm.covers = []
    coverList.value = []
    iconList.value = []
    configForm.visible = true
    configForm.title = '添加红包'
    configForm.isEdit = false
}

const edit = (idx: number, item: any) => {
    actItem.value = item
    dataForm.id = item.id
    dataForm.icon = item.icon
    iconList.value = []
    coverList.value = []
    for (let i in item.covers) {
        coverList.value.push({ src: item.covers[i] })
    }
    iconList.value.push({ src: item.icon })
    dataForm.name = item.name
    dataForm.total_money = item.total_money
    dataForm.quantity = item.quantity
    dataForm.status = item.status.toString()
    dataForm.covers = item.covers
    configForm.visible = true
    configForm.title = '编辑红包'
    configForm.isEdit = true
}
const edict = (idx: number, item: any) => {
    configForm.width = '800px'
    configForm.visible1 = true
    setTimeout(() => {
        pageReflqr.value.doSearch2({
            's_keyword': item.rsn
        })
    }, 300);
}



//弹层打开后回调
const dialogOpened = () => {
    /*        if(insObj){
                editor.value=insObj.refs['editor']
            }
            if(configForm.isEdit) {
                editor.value.setHtml(actItem.value.content)
            }else{
                editor.value.clear()
            }*/
}

//弹层关闭后
const onDialogClosed = () => {
    // editor.value.clear()
}

const save = () => {
    // dataForm.content=editor.value.getHtml()
    if (iconList.value[0]) {
        dataForm.icon = iconList.value[0].src
    }
    dataForm.covers = []
    for (let i in coverList.value) {
        dataForm.covers.push(coverList.value[i].src)
    }
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
        url: 'c=Gift&a=redpack_update',
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
            actItem.value.status_flag = res.data.status_flag
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
        url: 'c=Gift&a=redpack_delete',
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