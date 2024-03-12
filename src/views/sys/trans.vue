<template>
<Page url="c=Sys&a=trans">
    <template #btn="myScope">
        <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add(myScope)">添加内容</el-button>
        <el-button type="primary" size="small" @click="renew">更新</el-button>
    </template>
    <template #table="myScope">
        <el-table-column prop="id" label="ID" width="80"></el-table-column>
        <el-table-column prop="cn" label="简体中文"></el-table-column>
        <el-table-column prop="tw" label="繁体中文"></el-table-column>
        <el-table-column prop="es" label="西班牙语"></el-table-column>
        <el-table-column prop="en" label="英文" min-width="200">
            <template #default="scope">
                <div v-html="scope.row.en"></div>
            </template>
        </el-table-column>
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
                <el-button v-if="power.update" size="small" @click="edit(scope.$index,scope.row)">编辑</el-button>
            </template>
        </el-table-column>
    </template>

    <template #layer>
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="简体中文">
                    <el-input v-model="dataForm.cn" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="繁体中文">
                    <el-input v-model="dataForm.tw" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="西班牙语">
                    <el-input v-model="dataForm.es" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="英文">
                    <el-input type="textarea" v-model="dataForm.en" autocomplete="off" rows="3"></el-input>
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
    import {checkPower} from "../../global/user"
    import Page from '../../components/Page.vue'

    let isRequest=false
    let pageScope:any
    const store=useStore()
    const configForm=ref({
        title:'',
        width:'640px',
        labelWidth:'100px',
        visible:false,
        isEdit:false
    })

    let actItem=ref<any>({})
    const dataForm=ref<any>({
        id:0,
        cn:'',
        tw:'',
        es:'',
        en:''
    })

    //权限控制
    const power=reactive({
        update:checkPower('Sys_trans_update'),
        delete:checkPower('Sys_trans_delete')
    })

    const add=(myScope:any)=>{
        pageScope=myScope
        getZero(dataForm.value)
        configForm.value.visible=true
        configForm.value.title='添加内容'
        configForm.value.isEdit=false
    }

    const edit=(idx:number,item:any)=>{
        actItem.value=item
        for(let i in dataForm.value){
            dataForm.value[i]=item[i]
        }
        configForm.value.visible=true
        configForm.value.title='编辑内容'
        configForm.value.isEdit=true
    }

    const save=()=>{
        if(!dataForm.value.cn){
            _alert('请填写简体中文内容')
            return
        }
/*        if(!dataForm.value.tw){
            _alert('请填写繁体中文内容')
            return
        }
        if(!dataForm.value.en){
            _alert('请填写英文内容')
            return
        }*/
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url: 'c=Sys&a=trans_update',
            data: dataForm.value
        }).then((res:any)=>{
            isRequest=false
            if(res.code!=1){
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
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{}
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
            url: 'c=Sys&a=trans_delete',
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
    // 更新
    const renew=()=>{
        http({
            url: 'c=Trans&a=trans_update',
            data: dataForm.value
        }).then((res:any)=>{
            isRequest=false
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            //动态更新字段
            for(let i in dataForm.value){
                actItem.value[i]=dataForm.value[i]
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



        onMounted(()=>{

    })

</script>