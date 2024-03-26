<template>

    <div class="conbox">
        <div>
            <el-tree :data="tableData.list" default-expand-all node-key="id" ref="tree" highlight-current
                check-on-click-node check-strictly :expand-on-click-node="true" :props="tableData.defaultProps">
                <template #default="{ node, data }">
                    <div class="treeItemLabel" v-html="node.label"></div>
                    <div class="treeItemTool">
                        <span>{{ data.status == 1 ? '隐藏' : '显示' }}</span>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <span>排序：{{ data.sort }}</span>
                        <span style="padding-left: 20px;">ID：{{ data.id }}</span>
                        <span style="padding-left: 20px;">
                            <el-image style="height: 30px;width:30px;vertical-align: middle;" v-if="data.cover"
                                fit="cover" :src="imgFlag(data.cover)" hide-on-click-modal
                                :preview-src-list="[imgFlag(data.cover)]">
                            </el-image>
                        </span>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <el-button size="small" type="danger" @click.stop="del(node, data)">删除</el-button>
                        <el-button size="small" type="success" @click.stop="edit(node, data)">更新</el-button>
                        <el-button size="small" type="primary" @click.stop="add(data)">添加</el-button>
                    </div>
                </template>
            </el-tree>
            <div style="height: 50px;"></div>
        </div>
    </div>

    <!--弹出层-->
    <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
        :width="configForm.width" :top="configForm.top">
        <el-form>
            <el-form-item label="父级分类" :label-width="configForm.labelWidth">
                <div v-if="!dataForm.pname">/</div>
                <div v-if="dataForm.pname">{{ dataForm.pname }}</div>
            </el-form-item>
            <el-form-item label="分类名称" :label-width="configForm.labelWidth">
                <el-input size="small" v-model="dataForm.name" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="排序值" :label-width="configForm.labelWidth" style="margin-bottom: 0;">
                <el-input size="small" v-model="dataForm.sort" autocomplete="off" placeholder=""></el-input>
                <span>从大到小</span>
            </el-form-item>
            <el-form-item label="图标" :label-width="configForm.labelWidth">
                <el-upload class="img-upload-box" action="/api/?a=upload" :show-file-list="false"
                    :on-success="onSuccessCover"
                    :headers="{ 'Token': $store.state.token, 'X-Requested-With': 'XMLHttpRequest' }">
                    <img v-if="coverCpu" :src="coverCpu">
                    <i class="el-icon-plus"></i>
                </el-upload>
            </el-form-item>
            <el-form-item label="前台状态" :label-width="configForm.labelWidth" style="margin-bottom: 0;">
                <el-radio-group v-model="dataForm.status">
                    <el-radio :label="2">显示</el-radio>
                    <el-radio :label="1">隐藏</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="备注" :label-width="configForm.labelWidth">
                <el-input size="small" type="textarea" v-model="dataForm.remark" autocomplete="off" rows="3"></el-input>
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

<script lang="ts">
import { defineComponent, ref, onMounted, toRefs, reactive, watch, computed } from 'vue'
import { useStore } from "vuex";
import http from "../../global/network/http";
import { getSrcUrl, _alert, showImg, getZero } from "../../global/common";
import { checkPower } from "../../global/user";
import md5 from "md5";
import { ElMessageBox } from "element-plus";
export default defineComponent({
    emits: ['loadingFinish'],
    setup(props, { emit }) {
        let isRequest = false
        const store = useStore()

        const configForm = reactive({
            title: '',
            width: '540px',
            labelWidth: '100px',
            visible: false,
            isEdit: false
        })

        let nowItem: any = {}
        const actItem = ref<any>()
        const dataForm = reactive<any>({
            id: 0,
            pid: 0,
            pname: '',
            sort: 1000,
            status: 2,
            name: '',
            cover: '',
            remark: ''
        })

        const tableData = ref({
            count: 0,
            limit: 15,
            list: new Array<any[]>(),
            defaultProps: {
                children: 'children',
                label: (item: any, node: any) => {
                    return item.name
                }
            }
        })

        const searchForm = ref({
            s_account: '',
            s_gid: ''
        })

        //权限控制
        const power = reactive({
            update: checkPower('Product_category_update'),
            delete: checkPower('Product_category_delete')
        })

        const tree = ref()    //不需要传任何参数

        const getData = () => {
            http({
                url: 'c=Product&a=category',
                data: searchForm.value
            }).then((res: any) => {
                if (res.code != 1) {
                    _alert(res.msg)
                    return
                }
                // console.log(res.data)
                tableData.value.list = res.data.list
                setTimeout(() => { store.dispatch('loadingFinish'); }, store.state.loadingTime)
            })
        }

        const onSuccessCover = (res: any, file: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            dataForm.cover = res.data.src
        }

        const coverCpu = computed(() => {
            return getSrcUrl(dataForm.cover)
        })

        const add = (item: any) => {
            getZero(dataForm)
            dataForm.status = 2
            dataForm.sort = 1000
            if (item) {
                dataForm.pid = item.id
                dataForm.pname = item.name
            }
            configForm.visible = true
            configForm.title = '添加分类'
            configForm.isEdit = false
        }

        const edit = (node: any, item: any) => {
            nowItem = item
            for (let i in dataForm) {
                dataForm[i] = item[i]
            }
            configForm.visible = true
            configForm.title = '编辑分类'
            configForm.isEdit = true
        }

        const save = () => {
            if (!dataForm.name) {
                _alert('请填写分类名称')
                return
            }
            if (isRequest) {
                return
            } else {
                isRequest = true
            }
            http({
                url: 'c=Product&a=category_update',
                data: dataForm
            }).then((res: any) => {
                if (res.code != 1) {
                    isRequest = false
                    _alert(res.msg)
                    return
                }
                configForm.visible = false  //关闭弹层
                /*
                if(!configForm.isEdit){//添加的重新加载
                    //onSearch()
                }else{//动态更新字段
                    for(let i in dataForm){
                        nowItem[i]=dataForm[i]
                    }
                }*/
                getData()

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

        const del = (node: any, item: any) => {
            ElMessageBox.confirm('同时会删除该分类的所有子分类，您确定要执行该操作么？', '操作提示', {
                type: 'warning',
                beforeClose: (action: string, instance, done) => {
                    if (action == 'cancel') {
                        done()
                        return
                    }
                    instance.confirmButtonLoading = true
                    http({
                        url: 'c=Product&a=category_delete',
                        data: { id: item.id }
                    }).then((res: any) => {
                        _alert(res.msg)
                        setTimeout(() => {
                            instance.confirmButtonLoading = false
                        }, 2000)
                        if (res.code != 1) {
                            return
                        }
                        getData()
                        done()
                    })
                }
            }).catch(() => { })
        }

        //初始化
        const init = () => {
            getData()
        }

        const imgFlag = (src: string) => {
            return getSrcUrl(src)
        }

        onMounted(() => {
            init()
        })

        return {
            dataForm,
            tableData,
            searchForm,
            configForm,
            power,
            tree,
            onSuccessCover,
            coverCpu,
            add,
            edit,
            save,
            del,
            imgFlag
        }
    }
})
</script>

<style>
.el-tree-node__content {
    height: 32px;
}

.treeItemLabel {
    display: inline-block;
    width: 200px;
}

.treeItemTool {
    width: 600px;
    display: inline-block;
    text-align: right;
    line-height: 28px;
    border: 0px solid;
}
</style>