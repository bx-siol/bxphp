<template>
<Page url="c=Sys&a=node">
    <template #btn="myScope">
        <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add(myScope)">添加节点</el-button>
    </template>

    <template #search="{params,tdata}">
        <el-select size="small" style="width: 100px;" v-model="params.s_public" placeholder="公共">
            <el-option
                    key="all"
                    label="公共"
                    value="all">
            </el-option>
            <el-option
                    v-for="(item,idx) in store.state.config.yes_or_no"
                    :key="idx"
                    :label="item"
                    :value="idx">
            </el-option>
        </el-select>
        <el-select size="small" style="width: 100px;margin-left: 10px;" v-model="params.s_type" placeholder="菜单">
            <el-option
                    key="all"
                    label="菜单"
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
        <el-table-column prop="name" label="节点名称" min-width="320">
            <template #default="scope">
                <div style="text-align: left;padding-left: 15px;">{{nameFlag(scope.row)}}</div>
            </template>
        </el-table-column>
        <el-table-column prop="nkey" label="NKEY" min-width="320">
            <template #default="scope">
                <div style="text-align: left;padding-left: 15px;">{{scope.row.nkey}}</div>
            </template>
        </el-table-column>
        <el-table-column prop="public_flag" label="公共" width="100"></el-table-column>
        <el-table-column prop="type_flag" label="菜单" width="100"></el-table-column>
        <el-table-column prop="ico" label="图标" width="200">
            <template #default="scope">
                <div>
                    <i :class="scope.row.ico"></i> {{scope.row.ico}}
                </div>
            </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" width="120"></el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="140"></el-table-column>
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

    <template #layer="{tdata}">
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="公共">
                    <el-radio-group v-model="dataForm.public">
                        <el-radio :label="0">否</el-radio>
                        <el-radio :label="1">是</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="菜单">
                    <el-radio-group v-model="dataForm.type">
                        <el-radio :label="0">否</el-radio>
                        <el-radio :label="1">是</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="父级节点">
                    <el-select size="small" v-model="dataForm.pid" placeholder="请选择" style="width: 160px;">
                        <el-option label="请选择" :value="0"></el-option>
                        <el-option :label="vo.name" :value="vo.id" v-for="vo in tdata.top"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="Nkey">
                    <el-input size="small" v-model="dataForm.nkey" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input size="small" v-model="dataForm.name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="图标">
                    <el-input size="small" v-model="dataForm.ico" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="排序">
                    <el-input size="small" v-model="dataForm.sort" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="链接">
                    <el-input size="small" v-model="dataForm.url" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="备注">
                    <el-input size="small" type="textarea" v-model="dataForm.remark" autocomplete="off" rows="3"></el-input>
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

    const store=useStore()
    let isRequest=false
    let pageScope:any

    //权限控制
    const power=reactive({
        update:checkPower('Sys_node_update'),
        delete:checkPower('Sys_node_delete')
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
        pid:0,
        public:0,
        type:0,
        name:'',
        nkey:'',
        ico:'',
        sort:100,
        url:''
    })

    const add=(myScope:any)=>{
        pageScope=myScope
        getZero(dataForm.value)
        dataForm.value.sort=100
        configForm.value.visible=true
        configForm.value.title='添加节点'
        configForm.value.isEdit=false
    }

    const edit=(idx:number,item:any)=>{
        actItem.value=item
        for(let i in dataForm.value){
            dataForm.value[i]=item[i]
        }
        configForm.value.visible=true
        configForm.value.title='编辑节点'
        configForm.value.isEdit=true
    }

    const save=()=>{
        if(!dataForm.value.nkey){
            _alert('请填写nkey')
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
            url: 'c=Sys&a=node_update',
            data: dataForm.value
        }).then((res:any)=>{
            if(res.code!=1){
                isRequest=false
                _alert(res.msg)
                return
            }
            configForm.value.visible=false  //隐藏弹层
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{
                    setTimeout(()=>{
                        isRequest=false
                    },2000)

                    if(!configForm.value.isEdit){//添加的重新加载
                        pageScope.doSearch()
                    }else{//动态更新字段
                        for(let i in dataForm.value){
                            actItem.value[i]=dataForm.value[i]
                        }
                        actItem.value.public_flag=res.data.public_flag
                        actItem.value.type_flag=res.data.type_flag
                    }
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
            url: 'c=Sys&a=node_delete',
            data: {id: item.id}
        }).then((res:any)=>{
            if(res.code!=1){
                isRequest=false
                _alert(res.msg)
                return
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{
                    if(item.pid==0){//删除顶级节点时重新加载
                        myScope.doSearch()
                        isRequest=false
                    }else{
                        setTimeout(()=>{
                            isRequest=false
                        },2000)
                        //更新数据集
                        myScope.delItem(idx)
                    }
                }
            })
        })
    }

    const nameFlag=(row:any)=>{
        let name=row.name
        if(row.pid>0){
            name='------'+row.name
        }
        return name
    }

    onMounted(()=>{

    })


</script>