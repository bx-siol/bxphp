<template>
<Page url="c=Sys&a=bset">
    <template #btn="myScope">
        <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add(myScope)">添加配置</el-button>
    </template>

    <template #search="{params,tdata}">
        <el-select style="width: 100px;" v-model="params.s_single" placeholder="单KEY">
            <el-option
                    key="all"
                    label="单KEY"
                    value="all">
            </el-option>
            <el-option
                    v-for="(item,idx) in store.state.config.yes_or_no"
                    :key="idx"
                    :label="item"
                    :value="idx">
            </el-option>
        </el-select>
    </template>

    <template #table="myScope">
        <el-table-column prop="id" label="ID" width="80"></el-table-column>
        <el-table-column prop="skey" label="SKEY" width="220"></el-table-column>
        <el-table-column prop="name" label="配置名称" width="360"></el-table-column>
        <el-table-column prop="config_flag" label="配置内容" min-width="400">
            <template #default="scope">
                <div v-html="scope.row.config_flag"></div>
            </template>
        </el-table-column>
        <el-table-column prop="single_flag" label="单KEY" width="80"></el-table-column>
        <el-table-column label="操作" width="180">
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
                <el-button size="small" @click="edit(scope.$index,scope.row)">编辑</el-button>
            </template>
        </el-table-column>
    </template>

    <!--弹出层-->
    <template #layer="{tdata}">
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="单KEY">
                    <el-radio-group v-model="dataForm.single" :disabled="configForm.isEdit">
                        <el-radio :label="0">否</el-radio>
                        <el-radio :label="1">是</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="SKEY">
                    <el-input v-model="dataForm.skey" autocomplete="off" :disabled="configForm.isEdit"></el-input>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input v-model="dataForm.name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="配置内容">
                    <el-input type="textarea" v-model="dataForm.config" autocomplete="off" rows="5"></el-input>
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
    import {ref, onMounted, reactive} from 'vue'
    import {useStore} from "vuex"
    import http from "../../global/network/http"
    import {getZero, _alert} from "../../global/common"
    import Page from '../../components/Page.vue'
    import {checkPower} from "../../global/user";

    let isRequest=false
    const store=useStore()
    let pageScope:any

    //权限控制
    const power=reactive({
        update:checkPower('Sys_bset_update'),
        delete:checkPower('Sys_bset_delete')
    })

    const configForm=ref({
        title:'',
        width:'540px',
        labelWidth:'100px',
        visible:false,
        isEdit:false
    })

    const actItem=ref<any>({})
    const dataForm=ref<any>({
        id:0,
        single:0,
        skey:'',
        name:'',
        config:''
    })

    const add=(myScope:any)=>{
        pageScope=myScope
        getZero(dataForm.value)
        configForm.value.visible=true
        configForm.value.title='添加配置'
        configForm.value.isEdit=false
    }

    const edit=(idx:number,item:any)=>{
        actItem.value=item
        for(let i in dataForm.value){
            dataForm.value[i]=item[i]
        }
        configForm.value.visible=true
        configForm.value.title='编辑配置'
        configForm.value.isEdit=true
    }

    const save=()=>{
        if(!dataForm.value.skey){
            _alert('请填写skey')
            return
        }
        if(!dataForm.value.name){
            _alert('请填写名称')
            return
        }
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url: 'c=Sys&a=bset_update',
            data: dataForm.value
        }).then((res:any)=>{
            if(res.code!=1){
                isRequest=false
                _alert(res.msg)
                return
            }
            configForm.value.visible=false  //关闭弹层
            if(!configForm.value.isEdit){//添加的重新加载
                pageScope.doSearch()
            }else{//动态更新字段
                for(let i in dataForm.value){
                    actItem.value[i]=dataForm.value[i]
                }
                actItem.value.single_flag=res.data.single_flag
                actItem.value.config_flag=res.data.config_flag
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{
                    setTimeout(()=>{
                        isRequest=false
                    },2000)
                }
            })
        })
    }

    const del=(idx:number,item:any,myScope:any)=>{
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url: 'c=Sys&a=bset_delete',
            data: {id: item.id}
        }).then((res:any)=>{
            if(res.code!=1){
                isRequest=false
                _alert(res.msg)
                return
            }
            //更新数据集
            myScope.delItem(idx)

            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{
                    setTimeout(()=>{
                        isRequest=false
                    },2000)
                }
            })
        })
    }


    onMounted(()=>{

    })


</script>