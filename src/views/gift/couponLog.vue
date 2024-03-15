<template>
    <Page url="c=Gift&a=couponLog" ref="pageRef">
        <template #btn="myScope">

            <el-input v-if="power.update1" style="width: 180px;margin-left: 10px;" placeholder="赠送次数"
                v-model="s_addyxyh"></el-input>

            <el-button v-if="power.update1" type="success" size="small" icon="el-icon-plus"
                @click="addyxyh">赠送有效用户</el-button>
            <el-input v-if="power.update1" style="width: 180px;margin-left: 10px;" placeholder="赠送用户"
                v-model="s_cuser"></el-input>


            <el-button v-if="power.update1" type="success" size="small" icon="el-icon-plus"
                @click="addyxyhbyuser">赠送指定用户</el-button>

            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">赠送券</el-button>
        </template>

        <template #search="{ params, tdata, doSearch }">
            <el-select style="width: 110px;margin-left: 10px;" v-model="params.s_status" placeholder="所有状态">
                <el-option key="0" label="所有状态" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
        </template>

        <template #table>
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="account" label="用户账号" min-width="160"></el-table-column>
            <el-table-column prop="coupon_name" label="券名称" min-width="200"></el-table-column>
            <el-table-column prop="discount" label="券折扣%" width="120"></el-table-column>

            <el-table-column prop="money" label="券面值" width="120"></el-table-column>
            <el-table-column prop="num" label="券数量" width="120"></el-table-column>
            <el-table-column prop="used" label="已使用数量" width="120"></el-table-column>
            <el-table-column prop="effective_time_flag" label="有效截止日期" width="140"></el-table-column>
            <el-table-column prop="remark" label="备注" width="70"></el-table-column>
            <el-table-column prop="create_time" label="时间" width="180"></el-table-column>
            <!-- <el-table-column label="操作" width="160">
                    <template #default="scope">
                        <el-popconfirm confirmButtonText='确定' cancelButtonText='取消' icon="el-icon-warning" iconColor="red"
                            title="您确定要进行删除吗？" @confirm="del(scope.$index, scope.row)" v-if="power.delete">
                            <template #reference>
                                <el-button type="danger" size="small">删除</el-button>
                            </template>
                        </el-popconfirm>
                        <el-button size="small" v-if="power.update" @click="edit(scope.$index, scope.row)">编辑</el-button>
                    </template>
                </el-table-column> -->
        </template>

        <template #summary="{ tdata }">
            <span>记录数：{{ tdata.count }}</span>
            <!-- <span>领取总数：{{ tdata.receive_quantity }}</span>
                                                                        <span>领取总额：{{ tdata.receive_money }}</span> -->
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="接收账号">
                        <el-input v-model="dataForm.account" autocomplete="off" placeholder="任意填写，便于区分红包用途"></el-input>
                    </el-form-item>
                    <el-form-item label="赠送券">


                        <el-select style="width: 310px;" v-model="dataForm.coupon_id" placeholder="请选择">
                            <el-option key="0" label="请选择" value="0"></el-option>
                            <el-option v-for="(item ) in tdata.coupon_arr" :key="item.id" :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>


                    </el-form-item>
                    <el-form-item label="赠送数量">
                        <el-input v-model="dataForm.num" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder=""></el-input>

                    </el-form-item>
                    <el-form-item label="备注">
                        <el-input v-model="dataForm.remark" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder=""></el-input>

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
    width: '600px',
    labelWidth: '100px',
    // top:'1%',
    visible: false,
    isEdit: false
})

const coverList = ref<any>([])
const iconList = ref<any>([])
const actItem = ref<any>()
const s_addyxyh = ref<any>()

const s_cuser = ref<any>()

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
    update: checkPower('Gift_couponLog_update'),
    delete: checkPower('Gift_couponLog_delete'),
    update1: checkPower('Gift_couponLog_update1'),
})



const addyxyhbyuser = () => {
    if (isRequest) {
        _alert({
            type: 'success',
            message: '请求服务器中……请勿重复点击',
            onClose: () => {
                isRequest = false
            }
        })
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Gift&a=addyxyhbyuser&user=' + s_cuser.value + '&num=' + s_addyxyh.value,
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
                isRequest = false
            }
        })
    })
}

const addyxyh = () => {

    if (isRequest) {
        _alert({
            type: 'success',
            message: '请求服务器中……请勿重复点击',
            onClose: () => {
                isRequest = false
            }
        })
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Gift&a=addyxyh&num=' + s_addyxyh.value,
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
                isRequest = false
            }
        })
    })
}
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
        url: 'c=Gift&a=couponLogAdd',
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
        url: 'c=Gift&a=couponLog_delete',
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