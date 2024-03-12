<template>
    <div class="realId">
        <Nav></Nav>
        <div class="realId_wrap">
            <div class="realId_top">
                <p class="realId_top_txt1">身份证照片</p>
                <p class="realId_top_txt2">(上传身份证正面和反面；禁止上传含有第三方水印照片)</p>
            </div>
            <div class="realId_pic">
                <ul>
                    <li>
                        <Upload v-model:file-list="frontList" width="100%" height="7rem">
                            <template #delete v-if="configForm.isView">&nbsp;</template>
                            <template #notice>
                                <span style="color: #000000;">身份证正面</span>
                            </template>
                        </Upload>
                    </li>
                    <li>
                        <Upload v-model:file-list="backList" width="100%" height="7rem">
                            <template #delete v-if="configForm.isView">&nbsp;</template>
                            <template #notice>
                                <span style="color: #000000;">身份证反面</span>
                            </template>
                        </Upload>
                    </li>
                </ul>
            </div>
            <div class="realId_input">
                <van-cell-group>
<!--                    <van-field label="手机号码" placeholder="输入您的手机号" :readonly="configForm.isView" v-model="dataForm.phone"></van-field>
                    <van-field label="短信验证" placeholder="输入短信验证码" v-if="!configForm.isView" v-model="dataForm.scode">
                        <template #button>
                            <van-button size="mini" type="warning" :loading="sendLoading" style="vertical-align: middle;width: 60px;border-color: #ff6191;color: #ff6191;left: -0.1rem;" @click="onSendCode" plain>
                                <van-count-down
                                        v-if="isTimer"
                                        :time="60000"
                                        :auto-start="true"
                                        format="sss"
                                        @finish="onTimerFinish"
                                />
                                <span v-else>点击获取</span>
                            </van-button>
                        </template>
                    </van-field>-->
                    <van-field label="真实姓名" placeholder="输入您的真实姓名" :readonly="configForm.isView" v-model="dataForm.realname"></van-field>
                    <van-field label="身份证号" placeholder="输入您的身份证号" :readonly="configForm.isView" v-model="dataForm.idcard"></van-field>
                    <van-field label="二级密码" placeholder="输入二级密码" v-if="!configForm.isView" type="password" v-model="dataForm.password2"></van-field>
                </van-cell-group>
                <div class="btns">
                    <van-button @click="onSubmit" class="myBtn" type="primary" round block :disabled="configForm.isView">{{configForm.isView?dataForm.status_flag:'提交认证'}}</van-button>
                </div>
            </div>
            <div class="realId_tips">
                <p class="tit">实名认证说明</p>
                <div class="con">
                    <p>
                        1、填写实名认证的信息后，请静待审核，勿重复提交<br>
                        2、请确保您的信息真实有效，我们会严格保护会员隐私<br>
                    </p>
                </div>
            </div>
        </div>

    </div>
</template>

<script lang="ts">
    import {defineComponent} from "vue";
    import {Button, CellGroup, Field, NavBar,Uploader,Image,CountDown} from "vant";
    import Nav from '../../components/Nav.vue';
    import Upload from '../../components/Upload.vue';

    export default defineComponent({
        components:{
            Nav,Upload,
            [Button.name]:Button,
            [NavBar.name]:NavBar,
            [Uploader.name]:Uploader,
            [Image.name]:Image,
            [Field.name]:Field,
            [CellGroup.name]:CellGroup,
            [CountDown.name]:CountDown,
        }
    })

</script>

<script lang="ts" setup>
    import {reactive, ref,onMounted} from "vue";
    import Compressor from "compressorjs";
    import http from "../../global/network/http";
    import {_alert, getSrcUrl} from "../../global/common";
    import md5 from "md5";

    let isRequest=false

    const configForm=reactive({
        labelAlign:'right',
        labelWidth:'3.5rem',
        isView:true
    })

    const dataForm=reactive({
        phone:'',
        scode:'',
        realname:'',
        idcard:'',
        front:'',
        back:'',
        password2:'',
        status:'',
        status_flag:''
    })

    const frontList=ref<any>([])
    const backList=ref<any>([])

    const sendLoading=ref(false)
    const isTimer=ref(false)
    const onSendCode=()=>{
        if(!dataForm.phone){
            _alert('请先输入手机号')
            return
        }
        sendLoading.value=true
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => { 
        let pdata={stype:4,phone:dataForm.phone}
        let url='a=getPhoneCode'
            http({
            url:url,
            data:pdata
            }).then((res:any)=>{
            setTimeout(()=>{
                sendLoading.value=false
            },1000)
            if(res.code!=1){
                _alert({
                    message:res.msg,
                    onClose:()=>{

                    }
                })
                return
            }
            isTimer.value=true
            })
        }, delayTime)
    }

    const onTimerFinish=()=>{
        isTimer.value=false
    }

    const onSubmit=()=>{
        if(isRequest){
            return
        }
        if(frontList.value[0]){
            dataForm.front=frontList.value[0].src
        }else{
            dataForm.front=''
        }
        if(backList.value[0]){
            dataForm.back=backList.value[0].src
        }else{
            dataForm.back=''
        }
/*        if(!dataForm.phone){
            _alert('请填写手机号')
            return
        }
        if(!dataForm.scode){
            _alert('请填写短信验证码')
            return
        }*/
        if(!dataForm.realname){
            _alert('请填写姓名')
            return
        }
        if(!dataForm.idcard){
            _alert('请填写证件号')
            return
        }
        let password2=md5(dataForm.password2)
        isRequest=true
        http({
            url:'c=Setting&a=auth_update',
            data:{
                phone:dataForm.phone,
                scode:dataForm.scode,
                realname:dataForm.realname,
                idcard:dataForm.idcard,
                front:dataForm.front,
                back:dataForm.back,
                password2:password2
            }
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                setTimeout(()=>{
                    isRequest=false
                },1000)
                return
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{
                    location.reload()
                }
            })
        })
    }

    const imgFlag=(src:string)=>{
        return getSrcUrl(src, 1)
    }

    onMounted(()=>{
        http({
            url:'c=Setting&a=auth'
        }).then((res:any)=>{
            if(res.data.status==1||res.data.status==3){
                configForm.isView=true
            }else{
                configForm.isView=false
            }
            if(res.data.status>0){
                dataForm.phone=res.data.phone
                dataForm.realname=res.data.realname
                dataForm.idcard=res.data.idcard
                dataForm.front=res.data.front
                dataForm.back=res.data.back
                dataForm.status=res.data.status
                dataForm.status_flag=res.data.status_flag
                frontList.value=[{src:res.data.front}]
                backList.value=[{src:res.data.back}]
            }
        })
    })
</script>

<style scoped>
    .van-count-down{color: #ff6191;}

    .realId{position: relative;height: 100%;width:100%;}
    .realId_wrap{position: absolute;width: 100%;top:var(--van-nav-bar-height);bottom: 0rem;overflow-y: scroll;}
    .realId_top{padding: 0.2rem 3%;padding-top: 0.8rem;}
    .realId_top_txt1{font-size: 1rem;font-weight: bold;}
    .realId_top_txt2{font-size: 0.8rem;color: #999999;}
    .realId_pic{padding: 0 3%;margin-top:0.5rem;}
    .realId_pic ul{display:flex;justify-content: space-between;}
    .realId_pic li{width: 48%;}
    .realId_pic p{color: #333333;text-align: center;padding: 0.6rem 0;}
    .realId_input .btns{padding: 2rem;padding-top: 1rem;padding-bottom: 1rem;}
    .realId_tips{padding: 1rem 3%;}
    .realId_tips .tit{font-size: 1rem;font-weight: bold;}
    .realId_tips .con{color: #999999;padding-top: 0.8rem;font-size: 0.8rem;}
    .realId_tips .con p{line-height: 1.6rem;padding-bottom: 0.4rem;}
</style>