<template>
<Page url="c=News&a=notice">
    <template #btn="myScope">
        <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add(myScope)">添加公告</el-button>
    </template>
    <template #table="myScope">
        <el-table-column prop="id" label="ID" width="80"></el-table-column>
        <el-table-column prop="title" label="标题" min-width="200"></el-table-column>
        <el-table-column prop="text" label="内容" min-width="300"></el-table-column>
        <el-table-column prop="sort" label="排序大->小" width="200"></el-table-column>
        <el-table-column prop="create_time" label="时间" width="140"></el-table-column>
        <el-table-column label="操作" min-width="140">
            <template #default="scope">
                <el-popconfirm
                        confirmButtonText='确定'
                        cancelButtonText='取消'
                        icon="el-icon-warning"
                        iconColor="red"
                        title="您确定要进行删除吗？"
                        @confirm="del(scope.$index,scope.row,myScope)" v-if="power.delete">
                    <template #reference>
                        <el-button type="danger" size="small">删除</el-button>
                    </template>
                </el-popconfirm>
                <el-button size="small" v-if="power.update" @click="edit(scope.$index,scope.row)">编辑</el-button>
            </template>
        </el-table-column>
    </template>

    <template #layer>
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="标题">
                    <el-input type="text" v-model="dataForm.title" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="内容">
                    <el-input type="textarea" v-model="dataForm.text" autocomplete="off" rows="8"></el-input>
                </el-form-item>
                <el-form-item label="排序">
                    <el-input v-model="dataForm.sort" autocomplete="off"></el-input>
                    <span>从大到小</span>
                </el-form-item>
            </el-form>
            <template #footer>
            <span class="dialog-footer">
                <input type="hidden" v-model="dataForm.id"/>
                <el-button @click="configForm.visible = false">取消</el-button>
                <el-button type="primary" @click="save">保存</el-button>
            </span>
            </template>
        </el-dialog>
    </template>

</Page>
</template>

<script lang="ts" setup>
    import {ref,onMounted, reactive} from 'vue'
    import http from "../../global/network/http"
    import {getZero, _alert, getSrcUrl} from "../../global/common"
    import { checkPower } from '../../global/user'
    import Page from '../../components/Page.vue'

    let isRequest=false
    let pageScope:any

    const configForm=reactive({
        title:'',
        width:'640px',
        labelWidth:'80px',
        visible:false,
        isEdit:false
    })

    const actItem=ref<any>({})
    const dataForm=reactive<any>({
        id:0,
        title:'',
        text:'',
        sort:'',
    })

    //权限控制
    const power=reactive({
        update:checkPower('News_notice_update'),
        delete:checkPower('News_notice_delete'),
    })

    const add=(myScope:any)=>{
        pageScope=myScope
        getZero(dataForm)
        configForm.visible=true
        configForm.title='添加公告'
        configForm.isEdit=false
    }

    const edit=(idx:number,item:any)=>{
        actItem.value=item
        for(let i in dataForm){
            dataForm[i]=item[i]
        }
        configForm.visible=true
        configForm.title='编辑公告'
        configForm.isEdit=true
    }

    const save=()=>{
        if(!dataForm.text){
            _alert('请填写内容')
            return
        }
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url: 'c=News&a=notice_update',
            data: dataForm
        }).then((res:any)=>{
            isRequest=false
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            configForm.visible=false  //关闭弹层
            if(!configForm.isEdit){//添加的重新加载
                pageScope.doSearch()
            }else{//动态更新字段
                for(let i in dataForm){
                    actItem.value[i]=dataForm[i]
                }
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{

                }
            })
        })
    }

    const del=(idx:number,item:any,myScope:any)=> {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        http({
            url: 'c=News&a=notice_delete',
            data: {id: item.id}
        }).then((res: any) => {
            isRequest = false
            if (res.code != 1) {
                _alert(res.msg)
                return
            }

            //更新数据集
            myScope.delItem(idx)

            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => {

                }
            })
        })
    }

    const imgFlag=(src:string)=>{
        if(!src){
            return false
        }
        return getSrcUrl(src)
    }

    onMounted(()=>{

    })

</script>