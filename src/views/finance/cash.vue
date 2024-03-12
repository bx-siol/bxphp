<template>
    <div class="cashBox">
        <MyNav>
            <template #right>
                <router-link to="/finance/cashlog"><span style="color: #ffffff;">提币记录</span></router-link>
            </template>
        </MyNav>

        <van-form @submit="onSubmit" :label-align="configForm.labelAlign" :label-width="configForm.labelWidth" >
            <van-cell-group>
                <van-field label="币种">
                    <template #input>
                        USDT
                    </template>
                </van-field>
                <van-field label="协议">
                    <template #input>
                        <van-radio-group v-model="dataForm.protocol" direction="horizontal" @change="onProtocalChange">
                            <template  v-for="(item,idx) in tableData.protocal_arr">
                                <van-radio :name="idx" v-if="idx==3">{{item}}</van-radio>
                            </template>
                        </van-radio-group>
                    </template>
                </van-field>
                <van-field
                        label="可提余额">
                    <template #input>
                        <div>{{tableData.wallet.withdrawable_balance}} <van-button type="success" size="mini" @click="onAllCash" style="vertical-align: middle;background-color: #ff4c43;border-color: #ff4c43;">全部</van-button></div>
                    </template>
                </van-field>
                <van-field
                        label="提币额度"
                        v-model="dataForm.money"
                        placeholder="请输入"/>
                <van-field
                        label="钱包地址"
                        type="textarea"
                        v-model="dataForm.address"
                        placeholder="请输入"/>
                <van-field
                        label="二级密码"
                        v-model="dataForm.password"
                        type="password"
                        placeholder="请输入"/>
            </van-cell-group>
            <div style="margin: 2rem;padding-top: 0;">
                <van-button round block type="primary" class="myBtn" native-type="submit">提币</van-button>
                <div style="font-size: 0.8rem;color: #ed6a0c;padding-top: 1rem;text-align: center;">
                    手续费=单笔×{{feeRule.percent}}%
                    <template v-if="feeRule.money>0">+{{feeRule.money}}</template>
                </div>
            </div>
        </van-form>
    </div>
</template>

<script lang="ts">
import { defineComponent} from 'vue';
import {Form, Field, CellGroup, Cell, Button, Icon,RadioGroup,Radio,Image,Loading,Uploader,Dialog,Popup,Picker,Checkbox,Tag} from 'vant';
import MyNav from '../../components/Nav.vue';

export default defineComponent({
    components:{
        MyNav,
        [Form.name]:Form,
        [Field.name]:Field,
        [CellGroup.name]:CellGroup,
        [Cell.name]:Cell,
        [RadioGroup.name]:RadioGroup,
        [Radio.name]:Radio,
        [Checkbox.name]:Checkbox,
        [Icon.name]:Icon,
        [Image.name]:Image,
        [Tag.name]:Tag,
        [Uploader.name]:Uploader,
        [Popup.name]:Popup,
        [Picker.name]:Picker,
        [Loading.name]:Loading,
        [Dialog.Component.name]: Dialog.Component,
        [Button.name]:Button
    }
})
</script>

<script lang="ts" setup>
    import {onMounted, reactive, ref} from "vue";
    import {http} from "../../global/network/http";
    import ClipboardJS from "clipboard";
    import {_alert, getSrcUrl, showImg} from "../../global/common";
    import md5 from "md5";
    import {useRouter} from "vue-router";

    let isRequest=false
    const router=useRouter()

    const configForm=reactive({
        labelAlign:'right',
        labelWidth:'4rem'
    })

    const feeRule=ref<any>({})
    const tableData=ref<any>({
        wallet:{},
        protocal_arr:[]
    })
    const dataForm=reactive({
        type:0,
        type_flag:'',
        channel:0,
        channel_flag:'',
        protocol:'3',
        address:'',
        money:'',
        password:''
    })

    const onAllCash=()=>{
        dataForm.money=tableData.value.wallet.withdrawable_balance
    }

    const onProtocalChange=(ev:any)=>{
        console.log(ev)
    }

    const onSubmit=()=>{
        if(isRequest){
            return
        }
        isRequest=true
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => {
            http({
            url:'c=Finance&a=cashAct',
            data:{
                protocol:dataForm.protocol,
                address:dataForm.address,
                money:dataForm.money,
                password2:md5(dataForm.password)
            }
            }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                setTimeout(()=>{
                    isRequest=false
                },2000)
                return
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{
                    router.push({path:'/finance/cashInfo',query:{osn:res.data.osn}})
                }
            })
            })
        },delayTime)

    }

    const imgFlag=(src:string)=>{
        return getSrcUrl(src, 1)
    }

    const imgShow=(src:string)=>{
        showImg(getSrcUrl(src))
    }

    onMounted(()=>{
        http({
            url:'c=Finance&a=cash'
        }).then((res:any)=>{
            tableData.value.wallet=res.data.wallet
            tableData.value.protocal_arr=res.data.protocal_arr
            feeRule.value=res.data.fee_rule
        })

        const clipboard = new ClipboardJS('.copyBtn');
        clipboard.on('success', function (e) {
            _alert('复制成功')
            e.clearSelection();
        });

    })
</script>

<style>
    .cashBox .van-radio__label{color: white;}
    .cashBox .van-radio__icon--checked .van-icon{background-color:#ff4c43;border-color: #ff4c43;}
</style>

