<template>
    <Page url="c=Ext&a=bank" ref="pageRef">
        <template #btn="myScope">
            <el-button type="success" size="small" icon="el-icon-plus" @click="add">添加银行</el-button>
        </template>
        <template #table="myScope">
            <el-table-column prop="id" label="ID"></el-table-column>
            <el-table-column prop="code" label="编号"></el-table-column>
            <el-table-column prop="name" label="名称"></el-table-column>
            <el-table-column label="操作">
                <template #default="scope">
                    <el-button size="small" @click="edit(scope.$index, scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </template>
        <template #summary="{ tdata }">
            <span>记录数：{{ tdata.count }}</span>
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" 
            :width="configForm.width" :top="configForm.top" @opened="dialogOpened" @closed="onDialogClosed">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="编号">
                        <el-input size="small" v-model="dataForm.code" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="名称">
                        <el-input size="small" v-model="dataForm.name" autocomplete="off"></el-input>
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
import Editor from '../../components/Editor.vue';

export default defineComponent({
    components: {
        Editor
    }
})
</script>
<script lang="ts" setup>
import {  ref, onMounted, reactive } from 'vue'
import http from "../../global/network/http";
import { getZero, _alert } from "../../global/common";

let isRequest = false
const pageRef = ref()

const configForm = reactive({
    title: '',
    top: '',
    width: '500px',
    labelWidth: '100px',
    visible: false,
    isEdit: false
})

const actItem = ref<any>({})
const dataForm = reactive<any>({
    id: 0,
    code: '',
    name: '',
})

//弹层打开后回调
const dialogOpened = () => {
    //
}

//弹层关闭后
const onDialogClosed = () => {
    //
}

const add = () => {
    getZero(dataForm)
    dataForm.code = '';
    dataForm.name = ''
    configForm.visible = true
    configForm.title = '添加银行'
    configForm.isEdit = false
}

const edit = (idx: number, item: any) => {
    actItem.value = item
    for (let i in dataForm) {
        dataForm[i] = item[i]
    }
    configForm.visible = true
    configForm.title = '编辑银行'
    configForm.isEdit = true
}

const save = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Ext&a=bank_update',
        data: dataForm
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
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
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {

            }
        })
    })
}

onMounted(() => {

})

</script>