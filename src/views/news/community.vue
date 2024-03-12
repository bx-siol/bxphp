<template>
    <Page url="c=News&a=community">
        <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus"
                @click="add(myScope)">添加文章</el-button>
        </template>

        <template #search="{ params, tdata, doSearch }">

        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="nikename" label="名字" min-width="200"></el-table-column>
            <el-table-column prop="commendatory" label="点赞数" min-width="120"></el-table-column>
            <el-table-column prop="headimg" label="头像" width="80" class-name="imgCellBox">
                <template #default="scope">
                    <el-image style="width: 55px;height: 40px;vertical-align: middle;" fit="headimg"
                        :src="imgFlag(scope.row.headimg)" hide-on-click-modal
                        :preview-src-list="[imgFlag(scope.row.headimg)]">
                    </el-image>
                </template>
            </el-table-column>
            <el-table-column prop="releasetime" label="时间" width="120"></el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-popconfirm confirmButtonText='确定' cancelButtonText='取消' icon="el-icon-warning" iconColor="red"
                        title="您确定要进行删除吗？" @confirm="del(scope.$index, scope.row, myScope)" v-if="power.delete">
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

        <!--弹出层-->
        <template #layer="{ tdata }">
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened" @closed="onDialogClosed">
                <el-form :label-width="configForm.labelWidth">


                    <el-form-item label="用户昵称">
                        <el-input v-model="dataForm.nikename" autocomplete="off" placeholder=""></el-input>

                    </el-form-item>
                    <el-form-item label="点赞数">
                        <el-input type="number" v-model="dataForm.commendatory" autocomplete="off" rows="3"></el-input>
                    </el-form-item>

                    <el-form-item label="排序">
                        <el-input type="number" v-model="dataForm.sort" autocomplete="off" rows="3"></el-input>
                    </el-form-item>

                    <!--  <el-form-item label="置顶评论" style="margin-bottom: 0;">
                        <el-input type="textarea" v-model="dataForm.comments" autocomplete="off" placeholder=""></el-input>
                        <span>多个之间使用逗号分隔</span> 
                    </el-form-item>-->


                    <el-form-item label="发布时间" style="margin-bottom: 0;">
                        <el-date-picker v-model="dataForm.releasetime" type="datetime" placeholder="请选择">
                        </el-date-picker>
                    </el-form-item>

                    <el-form-item label="状态" style="margin-bottom: 0;">
                        <el-radio-group v-model="dataForm.status">
                            <el-radio :label="1">待发布</el-radio>
                            <el-radio :label="2">已发布</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="头像">
                        <Upload v-model:file-list="coverList" width="105px" height="80px"></Upload>
                    </el-form-item>

                    <el-form-item label="封面图">
                        <Upload v-model:file-list="coverList1" :limit="9" width="105px" height="80px"></Upload>
                    </el-form-item>

                    <el-form-item label="文章内容">
                        <!-- <el-input type="textarea" v-model="dataForm.content" autocomplete="off" placeholder=""></el-input> -->
                        <Editor :height="400" ref="editor"></Editor>
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

<script lang="ts" setup>
import { ref, onMounted, reactive, getCurrentInstance, nextTick } from 'vue';
import http from "../../global/network/http";
import { getZero, _alert, getSrcUrl, showImg } from "../../global/common";
import { checkPower } from '../../global/user';
import Page from "../../components/Page.vue";
import Editor from "../../components/Editor.vue";
import Editor2 from "../../components/Editor2.vue";
import Upload from '../../components/Upload.vue';

const insObj = getCurrentInstance()
const editor = ref()
let pageScope: any

let isRequest = false

//权限控制
const power = reactive({
    update: checkPower('News_community_update'),
    delete: checkPower('News_community_delete')
})

const configForm = reactive({
    title: '',
    width: '1024px',
    labelWidth: '100px',
    top: '2%',
    visible: false,
    isEdit: false
})

const actItem = ref<any>()
const dataForm = reactive<any>({
    id: 0,

    headimg: '',
    nikename: '',
    releasetime: '',
    commendatory: '',
    sort: '0',
    comments: '',
    status: 2,
    imgs: '',

    content: ''
})
const coverList = ref<any>([])
const coverList1 = ref<any>([])
const add = (myScope: any) => {
    pageScope = myScope
    coverList.value = []
    coverList1.value = []
    getZero(dataForm)
    dataForm.status = 2
    configForm.visible = true
    configForm.title = '添加文章'
    configForm.isEdit = false
}

const edit = (idx: number, item: any) => {
    coverList.value = [{ src: item.headimg }]
    coverList1.value = [];
    var arr = item.imgs.toString().split(',');
    var imgs = [];
    for (let index = 0; index < arr.length; index++) {
        const element = arr[index];
        coverList1.value.push({ src: element })
    }

    actItem.value = item
    for (let i in dataForm) {
        dataForm[i] = item[i]
    }
    configForm.visible = true
    configForm.title = '编辑文章'
    configForm.isEdit = true
}

//弹层打开后回调
const dialogOpened = () => {
    //重要：打开后重新更新引用
    if (insObj) {
        editor.value = insObj.refs['editor']
    }
    nextTick(() => {
        if (configForm.isEdit) {
            console.log(actItem.value)
            editor.value.setHtml(actItem.value.content)
        } else {
            editor.value.clear()
        }
    })
}

//弹层关闭后
const onDialogClosed = () => {
    editor.value.clear()
}

const save = () => {
    console.log(coverList)
    if (!dataForm.nikename) {
        _alert('请填用户昵称')
        return
    }
    if (!coverList.value) {
        _alert('请上头像')
        return
    }
    if (!coverList1.value) {
        _alert('请上传封面图')
        return
    }

    if (coverList.value[0]) {
        dataForm.headimg = coverList.value[0].src
    }
    if (coverList1.value[0]) {
        var tstr = '';
        for (let index = 0; index < coverList1.value.length; index++) {
            const element = coverList1.value[index];
            if (index != (coverList1.value.length - 1))
                tstr += element.src + ','
            else
                tstr += element.src
        }
        dataForm.imgs = tstr
    }

    dataForm.content = editor.value.getHtml()
    if (!dataForm.content) {
        _alert('请填写文章内容')
        return
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=News&a=community_update',
        data: dataForm
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        configForm.visible = false  //关闭弹层
        if (!configForm.isEdit) {//添加的重新加载
            pageScope.doSearch()
        } else {//动态更新字段
            for (let i in dataForm) {
                actItem.value[i] = dataForm[i]
            }
            actItem.value.cname = res.data.cname
            actItem.value.publish_time = res.data.publish_time
            actItem.value.status_flag = res.data.status_flag
            actItem.value.is_recommend_flag = res.data.is_recommend_flag
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                setTimeout(() => {
                    isRequest = false
                }, 2000)
            }
        })
    })
}

const del = (idx: number, item: any, myScope: any) => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=News&a=community_delete',
        data: { id: item.id }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        //更新数据集
        myScope.delItem(idx)

        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                setTimeout(() => {
                    isRequest = false
                }, 2000)
            }
        })
    })
}

const onClickUrl = (row: any) => {
    window.open(row.url, '_blank')
}

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

onMounted(() => {

})

</script>