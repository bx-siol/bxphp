<template>
<Page :url="'c=Finance&a=wallet&uid='+route.query.uid">
    <template #title="{tdata,title}">
        <el-breadcrumb-item v-if="tdata.user" @click="router.go(-1)" style="cursor: pointer;"><i class="el-icon-arrow-left"></i>返回</el-breadcrumb-item>
        <el-breadcrumb-item v-else>{{title}}</el-breadcrumb-item>
        <el-breadcrumb-item v-if="tdata.user">
            <el-image :src="getSrcUrl(tdata.user.headimgurl)" style="width: 30px;height: 30px;vertical-align: middle;"/>
            <span> &nbsp;{{tdata.user.account}}</span>
            <span>（{{tdata.user.nickname}}）</span>
        </el-breadcrumb-item>
    </template>
    <template #search="{params,tdata}">
        <el-select style="width: 120px;" v-model="params.s_cid" placeholder="全部钱包">
            <el-option key="0" label="全部钱包" value="0"></el-option>
            <el-option
                    v-for="(item,idx) in tdata.currency_arr"
                    :key="idx"
                    :label="item.name"
                    :value="item.id">
            </el-option>
        </el-select>
    </template>

    <template #table>
        <el-table-column prop="uid" label="UID" width="80"></el-table-column>
        <el-table-column prop="account" label="账号" width="140"></el-table-column>
        <el-table-column prop="nickname" label="昵称" width="180"></el-table-column>
        <el-table-column prop="currency" label="钱包类型" width="200">
            <template #default="scope">
                <div style="text-align: left;padding: 0 30px;">
                    <el-image
                            style="width: 30px;height: 30px;vertical-align: middle;"
                            fit="cover"
                            :src="getSrcUrl(scope.row.icon+'?v=0.1')"
                            hide-on-click-modal
                            :preview-src-list="[getSrcUrl(scope.row.icon)]">
                    </el-image>&nbsp;
                    <span>{{scope.row.currency}}</span>
                </div>
            </template>
        </el-table-column>
        <el-table-column prop="balance" label="余额" width="140"></el-table-column>
        <el-table-column prop="fz_balance" label="冻结中" width="140"></el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="170"></el-table-column>
        <el-table-column label="操作" width="200">
            <template #default="scope">
                <template v-if="power.pay||power.walletLog">
                    <el-button size="small" v-if="power.pay" @click="onPay(scope.$index,scope.row)" type="success">充值</el-button>
                    <el-button size="small" v-if="power.walletLog" @click="goWalletLog(scope.row)" type="primary">账变记录</el-button>
                </template>
                <span v-else>/</span>
            </template>
        </el-table-column>
    </template>

    <template #summary="{tdata}">
        <span>记录：{{tdata.count}}</span>
        <span>余额：{{tdata.balance}}</span>
        <span>冻结中：{{tdata.fz_balance}}</span>
    </template>

    <template #layer="{tdata}">
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="账号" style="margin-bottom: 0;">
                    <div>{{actItem.account}}（{{actItem.nickname}}）</div>
                </el-form-item>
                <el-form-item label="钱包类型">
                    <div>
                        <el-image :src="getSrcUrl(actItem.icon)" style="width: 30px;height: 30px;vertical-align: middle;"/>
                        {{actItem.currency}}
                    </div>
                </el-form-item>
                <el-form-item label="余额">
                    <div>{{actItem.balance}}</div>
                </el-form-item>
                <el-form-item label="冻结中">
                    <div>{{actItem.fz_balance}}</div>
                </el-form-item>
                <el-form-item label="充值类型" style="margin-bottom: 0;">
                    <el-radio-group v-model="dataForm.type">
                        <el-radio :label="1">余额</el-radio>
                        <el-radio :label="2">冻结</el-radio>
                        <el-radio :label="3">解冻</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="充值额度">
                    <el-input type="text" v-model="dataForm.money" autocomplete="off"></el-input>
                    <div style="color: #ff6600;" v-if="dataForm.type==1">正数是增加，负数是减少</div>
                    <div style="color: #ff6600;" v-else>填写大于0的数</div>
                </el-form-item>
                <el-form-item label="备注">
                    <el-input type="textarea" v-model="dataForm.remark" autocomplete="off" rows="3"></el-input>
                </el-form-item>
                <el-form-item label="二级密码">
                    <el-input type="password" v-model="dataForm.password2_flag" autocomplete="off" placeholder="您自己的二级密码"></el-input>
                </el-form-item>
            </el-form>
            <template #footer>
            <span class="dialog-footer">
                <el-button @click="configForm.visible = false">取消</el-button>
                <el-button type="primary" @click="onPaySave">提交</el-button>
            </span>
            </template>
        </el-dialog>
    </template>

</Page>
</template>

<script lang="ts" setup>
    import {ref,onMounted, reactive} from 'vue'
    import http from "../../global/network/http"
    import {_alert, getSrcUrl} from "../../global/common"
    import { checkPower } from '../../global/user'
    import {useStore} from "vuex"
    import Page from '../../components/Page.vue'
    import {useRoute, useRouter} from "vue-router";
    import md5 from "md5";

    let isRequest=false
    const store=useStore()
    const route=useRoute()
    const router=useRouter()


    //权限控制
    const power=reactive({
        pay:checkPower('Finance_wallet_pay'),
        walletLog:checkPower('Finance_walletLog')
    })

    const goWalletLog=(item:any)=>{
        // router.push({name:'Finance_walletLog',params:{wid:item.id}})
        router.push({name:'Finance_walletLog',query:{wid:item.id}})
    }

    const configForm=reactive({
        title:'充值',
        width:'540px',
        labelWidth:'100px',
        visible:false,
        isEdit:false
    })

    const actItem=ref<any>({})
    const dataForm=reactive<any>({
        id:0,
        type:1,
        money:'',
        remark:'',
        password2:'',
        password2_flag:''
    })

    const onPay=(idx:number,item:any)=>{
        actItem.value=item
        dataForm.id=item.id
        dataForm.money=''
        dataForm.remark=''
        dataForm.password2=''
        dataForm.password2_flag=''
        configForm.visible=true
    }

    const onPaySave=()=>{
        dataForm.password2=md5(dataForm.password2_flag)
        http({
            url: 'c=Finance&a=wallet_pay',
            data: dataForm
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            actItem.value.balance=res.data.balance
            actItem.value.fz_balance=res.data.fz_balance
            configForm.visible=false
            _alert(res.msg)
        })
    }

    onMounted(()=>{

    })
</script>