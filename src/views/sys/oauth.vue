<template>
    <div class="conbar">
        <el-breadcrumb separator="/" style="padding-left:12px;padding-top: 2px;line-height: 40px;display: inline-block;">
            <el-breadcrumb-item>{{store.state.config.active.name}}</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <div class="conbox">
        <div>
            <el-tabs v-model="activeTabName" type="card" @tab-click="handleTabClick">
                <el-tab-pane label="授权分组" name="first">
                    <el-form :label-width="configForm.labelWidth">
                        <el-form-item label="分组">
                            <el-select v-model="searchForm.s_gid" placeholder="请选择" style="width: 200px;" @change="searchGroup">
                                <el-option label="请选择" value="0"></el-option>
                                <el-option :label="vo" :value="idx" v-for="(vo,idx) in dataForm.sys_group"></el-option>
                            </el-select>&nbsp;
                            <el-button size="large" type="success" @click="searchGroup">查询</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane label="授权个人" name="second">
                    <el-form :label-width="configForm.labelWidth">
                        <el-form-item label="账号">
                            <el-input v-model="searchForm.s_account" autocomplete="off" style="width: 200px;"></el-input>&nbsp;
                            <el-button size="large" type="success" @click="searchAccount">查询</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
            </el-tabs>
            <el-tree
                    :data="dataForm.list"
                    show-checkbox
                    default-expand-all
                    node-key="id"
                    ref="tree"
                    highlight-current
                    check-on-click-node
                    check-strictly
                    :expand-on-click-node="false"
                    :default-checked-keys="dataForm.checked"
                    :props="dataForm.defaultProps">
            </el-tree>
            <div style="padding: 15px 43px;">
                <el-button type="primary" @click="onSubmit">提交授权</el-button>
            </div>
            <div style="height: 50px;"></div>
        </div>
    </div>

</template>

<script lang="ts" setup>
    import {ref,onMounted, toRefs, reactive,watch} from 'vue'
    import {useStore} from "vuex";
    import http from "../../global/network/http";
    import {_alert, showImg} from "../../global/common";

    let isRequest=false
    const store=useStore()

    const configForm=ref({
        title:'',
        width:'540px',
        labelWidth:'50px',
        visible:false,
        isEdit:false
    })

    const dataForm=reactive({
        list:new Array<any>(),
        checked:[],
        sys_group:[],
        defaultProps:{
            children: 'children',
            label: 'label'
        }
    })

    const searchForm=ref({
        s_account:'',
        s_gid:''
    })

    const tree=ref()    //不需要传任何参数

    const getData=()=>{
        http({
            url: 'c=Sys&a=oauth',
            data: searchForm.value
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            // console.log(res.data)
            dataForm.list=res.data.list
            dataForm.checked=res.data.checked
            dataForm.sys_group=res.data.sys_group
            setTimeout(()=>{store.dispatch('loadingFinish');},store.state.loadingTime)
        })
    }

    const searchGroup=()=>{
        http({
            url: 'c=Sys&a=oauth',
            data: {s_gid: searchForm.value.s_gid}
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            dataForm.checked=res.data.checked
            tree.value.setCheckedKeys(dataForm.checked)
        })
    }

    const searchAccount=()=>{
        http({
            url: 'c=Sys&a=oauth',
            data: {s_account: searchForm.value.s_account}
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            dataForm.checked=res.data.checked
            tree.value.setCheckedKeys(dataForm.checked)
        })
    }

    //tab切换回调
    const handleTabClick=(tab:any)=>{
        store.state.config.tab=tab.props.name
        if(tab.props.name=='first'){
            searchGroup()
        }else{
            searchAccount()
        }
    }

    const activeTabName=ref(store.state.config.tab)

    //提交保存
    const onSubmit=()=>{
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        let checked=tree.value.getCheckedKeys()
        let pdata={
            oauth:checked,
            gid:0,
            account:''
        }
        if(activeTabName.value=='first'){
            pdata.gid=parseInt(searchForm.value.s_gid)
        }else{
            pdata.account=searchForm.value.s_account
        }
        http({
            url: 'c=Sys&a=oauth_update',
            data: pdata
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
                    setTimeout(()=>{
                        isRequest=false
                    },2000)
                }
            })
        }).catch(()=>{
            //
        })
    }

    //初始化
    const init=()=>{
        getData()
    }

    onMounted(()=>{
        init()
    })

</script>