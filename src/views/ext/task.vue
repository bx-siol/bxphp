<template>
    <Page url="c=Ext&a=task" ref="pageRef">
        <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">添加任务</el-button>
        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="name" label="名称"></el-table-column>
            <el-table-column prop="u_account" label="创建人"></el-table-column>
            <el-table-column prop="icon" label="图标" width="70">
                <template #default="{ row }">
                    <img :src="imgFlag(row.img)" @click="onPreviewImg(row.img)"
                        style="height: 40px;vertical-align: middle;">
                </template>
            </el-table-column>
            <el-table-column prop="award" label="完成任务奖励"></el-table-column>
            <el-table-column prop="day_limit" label="用户每日限量">
                <template #default="{ row }">
                    <template v-if="row.day_limit > 0">{{ row.day_limit }}</template>
                    <template v-else>不限</template>
                </template>
            </el-table-column>
            <!--        <el-table-column prop="cover" label="图标" width="140" class-name="imgCellBox">
            <template #default="scope">
                <el-image
                        style="height: 26px;vertical-align: middle;"
                        v-if="scope.row.cover"
                        fit="cover"
                        :src="imgFlag(scope.row.cover)"
                        hide-on-click-modal
                        :preview-src-list="[imgFlag(scope.row.cover)]">
                </el-image>
            </template>
        </el-table-column>-->
            <el-table-column prop="sort" label="排序大->小"></el-table-column>
            <el-table-column prop="create_time" label="时间"></el-table-column>
            <el-table-column label="操作">
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
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened" @closed="onDialogClosed">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="名称">
                        <el-input v-model="dataForm.name" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="图标">
                        <MyUpload v-model:file-list="iconList" width="80px" height="80px" style="line-height: initial;">
                        </MyUpload>
                    </el-form-item>
                    <el-form-item label="用户每日限量">
                        <el-input v-model="dataForm.day_limit" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="任务限量">
                        <el-input v-model="dataForm.all_limit" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="领取类型">

                        <el-radio-group v-model="dataForm.type">
                            <el-radio label="0"> 一次</el-radio>
                            <el-radio label="1"> 多次</el-radio>
                        </el-radio-group>

                    </el-form-item>

                    <el-form-item label="显示">

                        <el-radio-group v-model="dataForm.ishow">
                            <el-radio label="0"> 不显示</el-radio>
                            <el-radio label="1"> 显示</el-radio>
                        </el-radio-group>

                    </el-form-item>

                    <el-form-item label="截止日期">
                        <el-date-picker :style="{ width: '250px' }" clearable v-model="dataForm.end_time" type="datetime"
                            placeholder="截止日期">
                        </el-date-picker>
                    </el-form-item>




                    <el-form-item label="奖励额度">
                        <el-input v-model="dataForm.award" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="排序">
                        <el-input v-model="dataForm.sort" autocomplete="off"></el-input>
                        <span> 从大到小</span>
                    </el-form-item>
                    <!--               <el-form-item label="图标">
                    <Upload v-model:file-list="coverList" width="50px" height="50px"></Upload>
                </el-form-item>-->
                    <el-form-item label="任务要求">
                        <Editor2 :height="360" ref="editor"></Editor2>
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
import MyUpload from '../../components/Upload.vue';
import Editor from '../../components/Editor.vue';

export default defineComponent({
    components: {
        Editor, MyUpload
    }
})
</script>
<script lang="ts" setup>
import { defineEmits, ref, onMounted, reactive, nextTick } from 'vue'
import http from "../../global/network/http";
import { getZero, _alert, getSrcUrl, showImg } from "../../global/common";
import { checkPower } from '../../global/user';

//   import Page from "../../components/Page.vue"
import Editor2 from "../../components/Editor2.vue"
// import Upload from '../../components/Upload.vue'
import { useStore } from "vuex";
const iconList = ref<any>([])
const store = useStore()
let isRequest = false

const pageRef = ref()
const editor = ref()
const end_time = ref()

//权限控制
const power = reactive({
    update: checkPower('Ext_task_update'),
    delete: checkPower('Ext_task_delete'),
})

const configForm = reactive({
    title: '',
    top: '3%',
    width: '940px',
    labelWidth: '100px',
    visible: false,
    isEdit: false
})

const actItem = ref<any>({})
const dataForm = reactive<any>({
    id: 0,
    name: '',
    sort: '',
    award: '',
    day_limit: '',
    content: '',
    qrcode: '',
    img: '',
    end_time: '',
    all_limit: '',
    type: '',
    ishow: '',
})
const coverList = ref<any>([])

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
    if (dataForm.sort <= 0) {
        dataForm.sort = ''
    }
    dataForm.sort = 1000
    dataForm.img = ''
    dataForm.all_limit = '1';
    dataForm.day_limit = '1';
    dataForm.type = '0';
    dataForm.ishow = '0'
    dataForm.end_time = '';
    configForm.visible = true
    configForm.title = '添加任务'
    configForm.isEdit = false
    iconList.value = []
}

const edit = (idx: number, item: any) => {
    actItem.value = item
    for (let i in dataForm) {
        dataForm[i] = item[i]
    }
    dataForm.ishow = item.ishow.toString();
    dataForm.type = item.type.toString();
    configForm.visible = true
    configForm.title = '编辑任务'
    configForm.isEdit = true
    nextTick(() => {
        editor.value.clear()
        editor.value.setHtml(item.content)
    })
    iconList.value = []
    iconList.value.push({ src: item.img })
}

const save = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    if (iconList.value[0]) {
        dataForm.img = iconList.value[0].src
    }
    dataForm.content = editor.value.getHtml()
    http({
        url: 'c=Ext&a=task_update',
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

const del = (idx: number, item: any) => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Ext&a=task_delete',
        data: { id: item.id }
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        //更新数据集
        pageRef.value.delItem(idx)
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {

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