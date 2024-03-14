<template>
    <Page :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="typename" label="类型"></el-table-column>
            <el-table-column prop="name" label="名称"></el-table-column>
            <el-table-column prop="cover" label="图标">
                <template #default="scope">
                    <el-image style="width: 100px; height: 100px" :src="imgFlag(scope.row.cover)" :fit="(scope.row.cover)"></el-image>
                </template>
            </el-table-column>
            <el-table-column prop="probability" label="中奖概率（%）"></el-table-column>
            <el-table-column prop="from_money" label="最小金额"></el-table-column>
            <el-table-column prop="to_money" label="最大金额"></el-table-column>
            <el-table-column prop="goodname" label="产品名称"></el-table-column>
            <el-table-column prop="couponname" label="奖券名称"></el-table-column>
            <el-table-column prop="buyAmountStart" label="必中奖起始购买金额"></el-table-column>
            <el-table-column prop="buyAmountEnd" label="必中奖结束购买金额"></el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-button size="mini"  @click="edit(scope.$index, scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width" :top="configForm.top" @opened="dialogOpened">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="奖品名称">
                        <el-input v-model="dataForm.name" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="图标">
                        <MyUpload v-model:file-list="iconList" width="80px" height="80px" style="line-height: initial;"></MyUpload>
                    </el-form-item>
                    <el-form-item label="购买必中奖">
                        <el-input v-model="dataForm.buyAmountStart" autocomplete="off" placeholder="" style="width: 310px;"></el-input>
                        &nbsp;&nbsp;至&nbsp;&nbsp;
                        <el-input v-model="dataForm.buyAmountEnd" autocomplete="off" placeholder="" style="width: 310px;"></el-input>
                    </el-form-item>
                    <el-form-item label="中奖概率">
                        <el-input v-model="dataForm.probability" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="奖品类型">
                        <el-radio-group v-model="dataForm.type">
                            <el-radio :label="1">余额</el-radio>
                            <el-radio :label="2">产品</el-radio>
                            <el-radio :label="3">实物</el-radio>
                            <el-radio :label="4">空</el-radio>
                            <el-radio :label="5">奖券</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="中奖余额" v-if="dataForm.type==1">
                        <el-input v-model="dataForm.from_money" autocomplete="off" placeholder="" style="width: 310px;"></el-input>
                        &nbsp;&nbsp;至&nbsp;&nbsp;
                        <el-input v-model="dataForm.to_money" autocomplete="off" placeholder="" style="width: 310px;"></el-input>
                    </el-form-item>
                    <el-form-item label="产品" v-if="dataForm.type==2">
                        <el-select style="width: 100%;" v-model="dataForm.gid" placeholder="选择产品">
                            <el-option v-for="(item, idx) in tdata.goods" :key="item.id" :label="item.name":value="item.id"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="中奖描述" v-if="dataForm.type==3">
                        <el-input v-model="dataForm.remark" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="奖券" v-if="dataForm.type==5">
                        <el-select style="width: 100%;" v-model="dataForm.coupon_id" placeholder="选择奖券">
                            <el-option v-for="(item, idx) in tdata.coupons" :key="item.id" :label="item.name" :value="item.id"></el-option>
                        </el-select>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <input type="hidden" v-model="dataForm.id" />
                        <el-button @click="onDialogClosed">取消</el-button>
                        <el-button type="primary" @click="save">保存</el-button>
                    </span>
                </template>
            </el-dialog>
        </template>

    </Page>
</template>

<script lang="ts">
    import {defineComponent} from 'vue'
    import Page from '../../components/Page.vue';
    import MyUpload from '../../components/Upload.vue';

    export default defineComponent({
        components:{
            Page
        }
    })
</script>

<script lang="ts" setup>
    import {ref, onMounted, reactive, getCurrentInstance } from 'vue';
    import {useStore} from "vuex";
    import {_alert, getSrcUrl} from "../../global/common";
    import http from "../../global/network/http";

    let isRequest=false
    const store=useStore()
    const pageRef=ref()   
    const insObj = getCurrentInstance() 
    const editor = ref()
    const actItem = ref<any>()
    const iconList = ref<any>([])

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

    const pageUrl=ref('c=Gift&a=lottery')
    const onPageSuccess=(td:any)=>{
        tableData.value = td
    }

    const configForm=reactive({
        title:'',
        width:'800px',
        labelWidth:'100px',
        top:'1%',
        visible:false,
        isEdit:false
    })

    //弹层打开后回调
    const dialogOpened = () => {
        if (insObj) {
            editor.value = insObj.refs['editor']
        }
        if (configForm.isEdit) {
           
        } else {
            editor.value.clear()
        }
    }
    //弹层关闭后
    const onDialogClosed = () => {
        iconList.value = [];
        configForm.visible = false
    }
    const dataForm = reactive<any>({      
        id:0, 
        type:0,
        name:'',
        cover:[],
        probability:0,
        from_money:0,
        to_money:0,
        gid:0,
        coupon_id:0,
        remark:'',
        buyAmountStart:0,
        buyAmountEnd:0,
    })

    const edit = (idx: number, item: any) => {
        actItem.value = item
        dataForm.id = item.id
        dataForm.type = item.type
        dataForm.name = item.name
        dataForm.cover = item.cover
        dataForm.probability = item.probability
        dataForm.from_money = item.from_money
        dataForm.to_money = item.to_money
        dataForm.gid = item.gid
        dataForm.coupon_id = item.coupon_id
        dataForm.remark = item.remark,
        dataForm.buyAmountStart = item.buyAmountStart
        dataForm.buyAmountEnd = item.buyAmountEnd
        configForm.visible = true
        configForm.title = '编辑奖品'
        configForm.isEdit = true,
        iconList.value.push({ src: item.cover })
    }

    const save = () => {
    if (iconList.value[0]) {
        dataForm.icon = iconList.value[0].src
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    const pdata = {}
    for (let i in dataForm) {
        pdata[i] = dataForm[i]
    }
    http({
        url: 'c=Gift&a=lottery_save',
        data: pdata
    }).then((res: any) => {
            isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        iconList.value = [];
        configForm.visible = false  //关闭弹层
        pageRef.value.doSearch()
    })
}

    onMounted(()=>{

    })

</script>