<template>
    <Page url="c=Finance&a=dtype" ref="pageRef">
        <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">添加通道</el-button>
        </template>

        <template #search="{params,tdata,doSearch}">
            <el-select size="small" style="width: 110px;margin-left: 10px;" v-model="params.s_status" placeholder="所有状态">
                <el-option key="0" label="所有状态" value="0"></el-option>
                <el-option v-for="(item,idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
        </template>

        <template #table>
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="name" label="通道名称" min-width="160"></el-table-column>
            <el-table-column prop="type" label="通道编码" min-width="280"></el-table-column>
            <!--        <el-table-column prop="icon" label="图标" width="160">
            <template #default="{row}">
                <img v-if="row.icon" :src="imgFlag(row.icon)" @click="onPreviewImg(row.icon)" style="height: 40px;vertical-align: middle;">
                <span v-else>/</span>
            </template>
        </el-table-column>-->
            <el-table-column prop="sort" label="排序(从大到小)"></el-table-column>
            <el-table-column prop="status_flag" label="状态">
                <template #default="{row}">
                    <el-switch v-model="row.status_switch" inactive-text="下线" active-text="上线"
                        @change="onSwitch($event,row,'status')" />
                </template>
            </el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-button size="small" v-if="power.test" @click="test(scope.$index,scope.row)">测试</el-button>
                    <el-popconfirm confirmButtonText='确定' cancelButtonText='取消' icon="el-icon-warning" iconColor="red"
                        title="您确定要进行删除吗？" @confirm="del(scope.$index,scope.row)" v-if="power.delete">
                        <template #reference>
                            <el-button type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>
                    <el-button size="small" v-if="power.update" @click="edit(scope.$index,scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </template>

        <template #summary="{tdata}">
            <span>记录数：{{tdata.count}}</span>
        </template>

        <template #layer="{tdata}">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="通道名称">
                        <el-input size="small" v-model="dataForm.name" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="通道编码">
                        <el-input size="small" v-model="dataForm.type" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="排序">
                        <el-input size="small" v-model="dataForm.sort" autocomplete="off" placeholder=""></el-input>
                        <span> 从大到小</span>
                    </el-form-item>
                    <!--                <el-form-item label="图标">-->
                    <!--                    <MyUpload2 v-model:file-list="iconList" width="80px" height="80px" style="line-height: initial;"></MyUpload2>-->
                    <!--                </el-form-item>-->
                    <el-form-item label="状态" style="margin-bottom: 0;">
                        <el-radio-group v-model="dataForm.status">
                            <el-radio :label="idx" v-for="(item,idx) in tdata.status_arr">{{item}}</el-radio>
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

const iconList = ref<any>([])
const actItem = ref<any>()

const dataForm = reactive<any>({
    id: 0,
    name: '',
    type: '',
    sort: 100,
    status: '1'
})

//权限控制
const power = reactive({
    update: checkPower('Finance_dtype_update'),
    delete: checkPower('Finance_dtype_delete'),
    test: checkPower('Finance_ptype_test'),
})

const add = () => {
    dataForm.id = 0
    dataForm.name = ''
    dataForm.type = ''
    dataForm.sort = '100'
    dataForm.status = '1'
    configForm.visible = true
    configForm.title = '添加通道'
    configForm.isEdit = false
}

const edit = (idx: number, item: any) => {
    actItem.value = item
    dataForm.id = item.id
    dataForm.name = item.name
    dataForm.type = item.type
    dataForm.sort = item.sort
    dataForm.status = item.status.toString()
    configForm.visible = true
    configForm.title = '编辑通道'
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

const test = (idx: number, item: any) => {
    http({
        url: 'c=Finance&a=rechargeAct',
        data: {
            pay_type: item.type,
            money: 100
        }
    }).then((res: any) => {
        //loadingShow.value = false
        isRequest = false
        if (res.code != 1) {

            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                if (res.data.pay_url) {
                    location.href = res.data.pay_url
                }
            }
        })
    })
}

const save = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Finance&a=dtype_update',
        data: dataForm
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
            if (dataForm.status == 3) {
                actItem.value.status_switch = true
            } else {
                actItem.value.status_switch = false
            }
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
        url: 'c=Finance&a=dtype_delete',
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

const onSwitch = (ev: any, item: any, fieldName: string) => {
    let value = ev ? 3 : 1
    http({
        url: 'a=changeTableVal',
        data: {
            table: 'fin_dtype',
            id_name: 'id',
            id_value: item.id,
            field: fieldName,
            value: value
        }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        item[fieldName] = value
        item[fieldName + '_switch'] = value == 3 ? true : false
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