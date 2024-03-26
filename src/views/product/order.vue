<template>
    <Page :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #search="{params,tdata,doSearch}">

            <span style="margin-left: 10px;color: #909399;">产品分类:</span>
            <el-select size="small" style="width: 160px;margin-left: 10px;" v-model="params.s_cid" @change="onChangeCid" placeholder="全部分类">
                <el-option key="0" label="全部分类" value="0"></el-option>
                <el-option
                        v-for="(item,idx) in tdata.category_tree"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id">
                </el-option>
            </el-select>

            <span style="margin-left: 10px;color: #909399;">所属产品:</span>
            <el-select size="small" style="width: 280px;margin-left: 10px;" v-model="params.s_gid" placeholder="全部产品">
                <el-option key="0" label="全部产品" value="0"></el-option>
                <el-option
                        v-for="(item,idx) in tableData.goods_arr"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id">
                </el-option>
            </el-select>

            <el-input size="small"
                    style="width: 280px;margin-left: 10px;"
                    placeholder="ID/账号/昵称"
                    clearable
                    v-model="params.s_user"
                    @keyup.enter="doSearch">
                <template #prepend>投资用户</template>
            </el-input>

            <el-input size="small"
                    style="width: 280px;margin-left: 10px;"
                    placeholder="ID/账号/昵称"
                    clearable
                    v-model="params.s_puser"
                    @keyup.enter="doSearch">
                <template #prepend>团队搜索</template>
            </el-input>

            <div style="clear: both;height: 10px;"></div>

            <span style="margin-left: 10px;color: #909399;">订单状态:</span>
            <el-select size="small" style="width: 120px;margin-left: 10px;" v-model="params.s_status" placeholder="全部状态">
                <el-option key="0" label="全部状态" value="0"></el-option>
                <el-option
                        v-for="(item,idx) in tdata.status_arr"
                        :key="idx"
                        :label="item"
                        :value="idx">
                </el-option>
            </el-select>

            <span style="margin-left: 10px;color: #909399;">日期:</span>
            <el-date-picker size="small"
                    :style="{marginLeft:'10px',width:'150px'}"
                    clearable
                    v-model="params.s_start_time_flag"
                    type="date"
                    placeholder="开始日期">
            </el-date-picker>
            <el-date-picker size="small"
                    :style="{marginLeft:'10px',width:'150px'}"
                    clearable
                    v-model="params.s_end_time_flag"
                    type="date"
                    placeholder="结束日期">
            </el-date-picker>

        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="osn" label="订单号" width="160"></el-table-column>
            <el-table-column prop="account" label="用户账号" width="140"></el-table-column>
<!--            <el-table-column prop="nickname" label="用户昵称" width="130"></el-table-column>-->
            <el-table-column prop="category_name" label="产品分类" width="120"></el-table-column>
            <el-table-column prop="goods_name" label="产品名称" min-width="320"></el-table-column>
            <el-table-column prop="days" label="期限(天)" width="100"></el-table-column>
            <el-table-column prop="rate" label="收益率(%)" width="100"></el-table-column>
            <el-table-column prop="price" label="产品单价" width="120"></el-table-column>
            <el-table-column prop="num" label="购买数量" width="100"></el-table-column>
            <el-table-column prop="money" label="总额度" width="120"></el-table-column>
            <el-table-column prop="total_reward" label="累计收益额度" width="130"></el-table-column>
            <el-table-column prop="total_days" label="累计收益天数" width="130"></el-table-column>
            <el-table-column prop="status_flag" label="状态" width="130"></el-table-column>
            <el-table-column prop="create_time" label="创建时间" width="105"></el-table-column>
            <el-table-column label="操作" min-width="120">
                <template #default="scope">
<!--                    <el-popconfirm
                            confirmButtonText='确定'
                            cancelButtonText='取消'
                            icon="el-icon-warning"
                            iconColor="red"
                            title="您确定要进行删除吗？"
                            @confirm="del(scope.$index,scope.row)" v-if="power.delete&&scope.row.status==7">
                        <template #reference>
                            <el-button type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>-->
                    <el-button size="small" v-if="power.set&&scope.row.status<9"  @click="onOrderSet(scope.$index,scope.row)" type="success">设置</el-button>
                    <span v-else>/</span>
                </template>
            </el-table-column>
        </template>

        <template #summary="{tdata}">
            <span>订单数：{{tdata.count}}</span>
            <span>投资总额：{{tdata.money}}</span>
            <span>收益总额：{{tdata.total_reward}}</span>
        </template>

        <template #layer="{tdata}">
            <el-dialog title="订单设置" v-model="setShow" :close-on-click-modal="false" :width="500">
                <el-form :label-width="100">
                    <el-form-item label="订单号">
                        <el-input size="small" v-model="actItem.osn" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="订单状态" style="margin-bottom: 0;">
                        <el-radio-group v-model="orderForm.status">
                            <el-radio :label="1">正常</el-radio>
                            <el-radio :label="3">暂停收益</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="setShow = false">取消</el-button>
                        <el-button type="primary" @click="onOrderSetSave">提交</el-button>
                    </span>
                </template>
            </el-dialog>
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
    import {checkPower} from "../../global/user";
    import order from "./order.vue";

    let isRequest=false
    const store=useStore()
    const route=useRoute()
    const pageRef=ref()

    const configForm=reactive({
        title:'订单',
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
        //delete:checkPower('Product_order_delete'),
        set:checkPower('Product_order_set')
    })

    const imgFlag=(src:string)=>{
        return getSrcUrl(src)
    }

    const onPreviewImg=(src:string)=>{
        showImg(getSrcUrl(src))
    }

    const pageUrl=ref('c=Product&a=order')
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

    const setShow=ref(false)
    const orderForm=reactive({
        status:0
    })

    const onOrderSet=(idx:number,item:any)=>{
        actItem.value=item
        orderForm.status=item.status
        setShow.value=true
    }

    const onOrderSetSave=()=>{
        if(orderForm.status==0){
            _alert('请选择订单状态')
            return
        }
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url:'c=Product&a=order_set',
            data:{
                id:actItem.value.id,
                status:orderForm.status
            }
        }).then((res:any)=>{
            isRequest=false
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            actItem.value.status=res.data.status
            actItem.value.status_flag=res.data.status_flag
            _alert(res.msg)
            setShow.value=false
        })
    }

    onMounted(()=>{

    })

</script>