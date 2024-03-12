<template>
<Page url="c=User&a=group">
    <template #btn="myScope">
        <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add(myScope)">添加分组</el-button>
    </template>

    <template #table="myScope">
        <el-table-column prop="id" label="ID" width="80"></el-table-column>
        <el-table-column prop="name" label="名称"></el-table-column>
        <el-table-column prop="cover" label="图标" width="140" class-name="imgCellBox">
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
        </el-table-column>
        <el-table-column prop="remark" label="备注"></el-table-column>
        <el-table-column prop="sort" label="排序大->小"></el-table-column>
        <el-table-column prop="create_time" label="时间" width="130"></el-table-column>
        <el-table-column label="操作" min-width="160">
            <template #default="scope">
                <el-popconfirm
                        confirmButtonText='确定'
                        cancelButtonText='取消'
                        icon="el-icon-warning"
                        iconColor="red"
                        title="您确定要进行删除吗？"
                        @confirm="del(scope.$index,scope.row,myScope)" v-if="power.delete&&scope.row.id>1">
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

    <template #layer>
        <!--弹出层-->
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width" :top="configForm.top" @opened="dialogOpened" @closed="onDialogClosed">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="ID">
                    <el-input v-model="dataForm.nid" :disabled="dataForm.nid==1" placeholder="添加时留空自动分配" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input v-model="dataForm.name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="排序">
                    <el-input v-model="dataForm.sort" autocomplete="off"></el-input>
                    <span>从大到小</span>
                </el-form-item>
                <el-form-item label="图标">
                    <Upload v-model:file-list="coverList" width="50px" height="50px"></Upload>
                </el-form-item>
                <el-form-item label="描述">
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
    let pageScope:any
    let isRequest=false

    //权限控制
    const power=reactive({
        update:checkPower('User_group_update'),
        delete:checkPower('User_group_delete'),
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
        nid:'',
        name:'',
        sort:'',
        remark:'',
        cover:''
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

    const add=(myScope:any)=>{
        pageScope=myScope
        coverList.value=[]
        getZero(dataForm)
        configForm.visible=true
        configForm.title='添加分组'
        configForm.isEdit=false
    }

    const edit=(idx:number,item:any)=>{
        actItem.value=item
        coverList.value=[{src:item.cover}]
        for(let i in dataForm){
            dataForm[i]=item[i]
        }
        dataForm.nid=item.id.toString()
        configForm.visible=true
        configForm.title='编辑分组'
        configForm.isEdit=true
    }

    const save=()=>{
        if(!dataForm.name){
            _alert('请填写分组名称')
            return
        }
        if(coverList.value[0]){
            dataForm.cover=coverList.value[0].src
        }else{
            dataForm.cover=''
        }
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url: 'c=User&a=group_update',
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
                actItem.value.id=dataForm.nid
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
            url: 'c=User&a=group_delete',
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
        return getSrcUrl(src)
    }

    onMounted(()=>{

    })

</script>