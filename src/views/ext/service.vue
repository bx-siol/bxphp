<template>
<Page url="c=Ext&a=service" ref="pageRef">
    <template #btn="myScope">
        <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">添加客服</el-button>
    </template>

    <template #table="myScope">
        <el-table-column prop="id" label="ID" width="80"></el-table-column>
        <el-table-column prop="name" label="名称"></el-table-column>
        <el-table-column prop="account" label="账号"></el-table-column>
        <el-table-column prop="u_account" label="创建人"></el-table-column>
        <el-table-column prop="type_flag" label="类型"></el-table-column>
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
        <el-table-column prop="remark" label="备注"></el-table-column>
        <el-table-column prop="create_time" label="时间"></el-table-column>
        <el-table-column label="操作">
            <template #default="scope">
                <el-popconfirm
                        confirmButtonText='确定'
                        cancelButtonText='取消'
                        icon="el-icon-warning"
                        iconColor="red"
                        title="您确定要进行删除吗？"
                        @confirm="del(scope.$index,scope.row)" v-if="power.delete">
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
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width" :top="configForm.top" @opened="dialogOpened" @closed="onDialogClosed">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="类型" style="margin-bottom: 0;">
                    <el-radio-group v-model="dataForm.type">
                        <el-radio :label="idx" v-for="(item,idx) in tdata.type_arr">{{item}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input v-model="dataForm.name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="账号">
                    <el-input v-model="dataForm.account" autocomplete="off"></el-input>
                </el-form-item>
 <!--               <el-form-item label="图标">
                    <Upload v-model:file-list="coverList" width="50px" height="50px"></Upload>
                </el-form-item>-->
                <el-form-item label="备注">
                    <el-input type="textarea" v-model="dataForm.remark" autocomplete="off" rows="3"></el-input>
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
    import {defineEmits,ref,onMounted, reactive} from 'vue'
    import http from "../../global/network/http";
    import {getZero, _alert, getSrcUrl, showImg} from "../../global/common";
    import { checkPower } from '../../global/user';
    import Page from "../../components/Page.vue"
    import Upload from '../../components/Upload.vue'
    import {useStore} from "vuex";

    const store=useStore()
    let isRequest=false

    const pageRef=ref()

    //权限控制
    const power=reactive({
        update:checkPower('Ext_service_update'),
        delete:checkPower('Ext_service_delete'),
    })

    const configForm=reactive({
        title:'',
        width:'540px',
        labelWidth:'100px',
        visible:false,
        isEdit:false
    })

    const actItem=ref<any>({})
    const dataForm=reactive<any>({
        id:0,
        type:0,
        name:'',
        account:'',
        remark:'',
        qrcode:''
    })
    const coverList=ref<any>([])

    //弹层打开后回调
    const dialogOpened=()=>{
        //
    }

    //弹层关闭后
    const onDialogClosed=()=>{
        //
    }

    const add=()=>{
        coverList.value=[]
        getZero(dataForm)
        dataForm.type='1'
        configForm.visible=true
        configForm.title='添加客服'
        configForm.isEdit=false
    }

    const edit=(idx:number,item:any)=>{
        actItem.value=item
        coverList.value=[{src:item.cover}]
        for(let i in dataForm){
            dataForm[i]=item[i]
        }
        dataForm.type=dataForm.type.toString()
        configForm.visible=true
        configForm.title='编辑客服'
        configForm.isEdit=true
    }

    const save=()=>{
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url: 'c=Ext&a=service_update',
            data: dataForm
        }).then((res:any)=>{
            isRequest=false
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            configForm.visible=false  //关闭弹层
            if(!configForm.isEdit){//添加的重新加载
                pageRef.value.doSearch()
            }else{//动态更新字段
                for(let i in dataForm){
                    actItem.value[i]=dataForm[i]
                }
                actItem.value.type_flag=res.data.type_flag
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{

                }
            })
        })
    }

    const del=(idx:number,item:any)=> {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        http({
            url: 'c=Ext&a=service_delete',
            data: {id: item.id}
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

    const imgFlag=(src:string)=>{
        return getSrcUrl(src)
    }

    onMounted(()=>{

    })

</script>