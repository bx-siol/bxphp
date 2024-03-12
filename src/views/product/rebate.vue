<template>
    <Page :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #search="{params,tdata,doSearch}">

            <el-input style="width: 280px;margin-left: 10px;" placeholder="ID/账号/昵称" clearable v-model="params.s_user"
                @keyup.enter="doSearch">
                <template #prepend>所属用户</template>
            </el-input>

            <el-input style="width: 280px;margin-left: 10px;" placeholder="ID/账号/昵称" clearable v-model="params.s_user1"
                @keyup.enter="doSearch">
                <template #prepend>来源用户</template>
            </el-input>

            <el-input style="width: 280px;margin-left: 10px;" placeholder="ID/账号/昵称" clearable v-model="params.s_puser"
                @keyup.enter="doSearch">
                <template #prepend>团队搜索</template>
            </el-input>

            <div style="clear: both;height: 10px;"></div>

            <el-input style="width: 300px;margin-left: 10px;" placeholder="请输入" clearable v-model="params.s_osn"
                @keyup.enter="doSearch">
                <template #prepend>订单号</template>
            </el-input>

            <span style="margin-left: 10px;color: #909399;">日期:</span>
            <el-date-picker :style="{marginLeft:'10px',width:'150px'}" clearable v-model="params.s_start_time_flag"
                type="date" placeholder="开始日期">
            </el-date-picker>
            <el-date-picker :style="{marginLeft:'10px',width:'150px'}" clearable v-model="params.s_end_time_flag"
                type="date" placeholder="结束日期">
            </el-date-picker>

        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="account" label="用户账号" width="140"></el-table-column>
            <el-table-column prop="nickname" label="用户昵称" width="130"></el-table-column>

            <el-table-column prop="u2account" label="来源账号" width="140"></el-table-column>
            <el-table-column prop="u2nickname" label="来源昵称" width="130"></el-table-column>

            <el-table-column prop="category_name" label="产品分类" min-width="130"></el-table-column>
            <el-table-column prop="goods_name" label="产品名称" min-width="320"></el-table-column>
            <el-table-column prop="base_money" label="投资额度" width="140"></el-table-column>
            <el-table-column prop="type_flag" label="收益类型" width="120"></el-table-column>
            <el-table-column prop="level" label="层级" width="90"></el-table-column>
            <el-table-column prop="rate" label="收益率(%)" width="100"></el-table-column>
            <el-table-column prop="money" label="收益额度" width="140"></el-table-column>
            <el-table-column prop="create_time" label="时间" width="170"></el-table-column>
        </template>

        <template #summary="{tdata}">
            <span>记录数：{{tdata.count}}</span>
            <span>累计收益：{{tdata.money}}</span>
        </template>

        <template #layer="{tdata}">

        </template>
    </Page>
</template>

<script lang="ts">
    import {defineComponent} from 'vue'
    import Page from '../../components/Page.vue';

    export default defineComponent({
        components:{
            Page
        }
    })
</script>

<script lang="ts" setup>
    import {ref,onMounted, nextTick, reactive} from 'vue';
    import {useStore} from "vuex";
    import {_alert, getSrcUrl, showImg} from "../../global/common";
    import dayjs from "dayjs";
    import {useRoute} from "vue-router";
    import http from "../../global/network/http";

    let isRequest=false
    const store=useStore()
    const route=useRoute()
    const pageRef=ref()

    const configForm=reactive({
        title:'收益记录',
        width:'800px',
        labelWidth:'100px',
        top:'1%',
        visible:false,
        isEdit:false
    })

    const actItem=ref<any>({})
    const tableData=ref<any>({
        goods_arr:[]
    })

    //权限控制
    const power=reactive({
        //delete:checkPower('Shop_order_delete'),
    })

    const imgFlag=(src:string)=>{
        return getSrcUrl(src)
    }

    const onPreviewImg=(src:string)=>{
        showImg(getSrcUrl(src))
    }

    const pageUrl=ref('c=Product&a=rebate')
    const onPageSuccess=(td:any)=>{
        tableData.value=td
    }

    //分类切换
    const onChangeCid=(ev:any)=>{
        pageRef.value.searchForm.s_gid='0'
        http({
            url: 'c=Product&a=getGoodsByCid',
            data: {cid: ev}
        }).then((res:any)=>{
            tableData.value.goods_arr=res.data.list
        })
    }

    onMounted(()=>{

    })

</script>