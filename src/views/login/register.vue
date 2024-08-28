<template>
    <div class="register">
        <div class="login_adorn">
            <img :src="ornament">
        </div>

        <div class="formbox">
            <div class="avatar">
                <p @click="onLink({ name: 'Login' })"> {{ t('登录') }}</p>
                <p @click="onLink({ name: 'Register' })"> {{ t('注册') }}</p>
            </div>
            <van-cell-group>
                <van-field v-model="dataForm.account" class="accountItem" :left-icon="phone" label="+91" label-width="30"
                    maxlength="10" :placeholder="t('请输入手机号')"></van-field>

                <van-field v-model="dataForm.scode" :placeholder="t('短信验证码')" maxlength="6">
                    <template #left-icon>
                        <van-image :src="lock" style="width: 1.5rem;height: 1.5rem;" />
                    </template>
                    <template #button>
                        <van-button size="mini" type="warning" class="sendCodeBtn" :loading="sendLoading" @click="onSendCode" plain>
                            <van-count-down v-if="isTimer" :time="60000" :auto-start="true" format="sss" @finish="onTimerFinish" />
                            <span v-else>{{ t('发送') }}</span>
                        </van-button>
                    </template>
                </van-field>

                <van-field v-model="dataForm.nickname" :left-icon="nickname" :placeholder="t('请填写昵称')"></van-field>
                
                <van-field v-model="dataForm.password_flag" type="password" :left-icon="key" :placeholder="t('请填写登录密码')"></van-field>

                <van-field v-model="dataForm.icode"
                    :disabled="(route.query.Icode ? route.query.Icode : (route.query.icode ? route.query.icode : '')) > 0"
                    :left-icon="Inviter" :placeholder="t('请填写邀请码')"></van-field>
                <van-field v-show="false" v-model="dataForm.imgcode" :placeholder="t('图形验证码')" @keyup.enter="onRegister"
                    style="padding-top: 0;padding-bottom: 0;padding-right: 0;">
                    <template #left-icon>
                        <van-image :src="ico_4" fit="cover" style="top:4px;width: 1.2rem;" />
                    </template>
                    <template #right-icon>
                        <van-image class="imgCode" style="border-radius: 5px;overflow: hidden;height: 2.2rem;width: 5rem;"
                            :src="dataForm.imgcode_url" @click="getVcode" />
                    </template>
                </van-field>
            </van-cell-group>
            <div class="registerBtnWrapper" @click="onRegister">
                 <div class="onLogin_btn">
                    {{ t('注册') }}
                </div>
            </div>

        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { Button, CellGroup, Col, Field, Row, Image, CountDown, NavBar } from "vant";

export default defineComponent({
    components: {
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Button.name]: Button,
        [Row.name]: Row,
        [Col.name]: Col,
        [Image.name]: Image,
        [CountDown.name]: CountDown,
        [NavBar.name]: NavBar
    }
})

</script>
<script lang="ts" setup>
import { ico_1, ico_2, ico_3, ico_4, ico_5, ico_6, ico_103, img_yzm } from '../../global/assets';
import ornament from '../../assets/img/login/ornament.png';
import phone from '../../assets/img/login/lock1.png';
import key from '../../assets/img/login/lock2.png';
import lock from '../../assets/img/login/lock3.png';
import nickname from '../../assets/img/login/lock4.png';
import Inviter from '../../assets/img/login/lock5.png';
import { ref, reactive, toRefs, onMounted } from 'vue';
import { useStore } from "vuex";
import { useRoute, useRouter } from 'vue-router';
import md5 from 'md5';
import http from '../../global/network/http';
import { _alert, goRoute, lang } from '../../global/common';
import { isLogin } from "../../global/user";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

let isRequest = false
const store = useStore()
const router = useRouter()
const route = useRoute()

const onClickLeft = () => {
    history.back()
}

const onLink = (to: any) => {
    goRoute(to)
}

const dataForm = reactive({
    sid: '',
    account: '',
    password: '',
    password_flag: '',
    icode: route.query.Icode ? route.query.Icode : (route.query.icode ? route.query.icode : ''),
    ecode: '',
    scode: '',
    imgcode: '',
    imgcode_url: '',
    nickname: ''
})

const sendLoading = ref(false)
const isTimer = ref(false)
const onSendCode = () => {
    if (isTimer.value) {
        return
    }
    if (!dataForm.account) {
        _alert(t('请输入手机号'))
        return
    }
    sendLoading.value = true
    var delayTime = Math.floor(Math.random() * 1000);
    setTimeout((() => {
        let pdata = { stype: 1, phone: dataForm.account, email: dataForm.account }
        let url = 'a=getPhoneCode'
        http({
            url: url,
            data: pdata
        }).then((res: any) => {
            setTimeout(() => {
                sendLoading.value = false
            }, 1000)
            if (res.code != 1) {
                _alert({
                    message: res.msg,
                    onClose: () => {

                    }
                })
                return
            }
            isTimer.value = true
        })
    }), delayTime)
}

const onTimerFinish = () => {
    isTimer.value = false
}

//图形验证码
const vcodeUrl = ref('')

//获取图形验证码
const getVcode = () => {
    http({
        url: 'a=getVcode'
    }).then((res: any) => {
        if (res.code == 1) {
            dataForm.sid = res.data.session_id
            dataForm.imgcode_url = res.data.url
        }
    });
}

//注册
const onRegister = () => {
    if (isRequest) {
        return
    }
    if (!dataForm.account) {
        _alert(t('请输入手机号'))
        return
    }
    var accountRegex = /^\d+$/;
    if (!accountRegex.test(dataForm.account)) {
        _alert('The account can only contain numbers and cannot include symbols');
        return;
    }
    if (!dataForm.scode) {
        _alert(t('请填写验证码'))
        return
    }
    if (!dataForm.password_flag) {
        _alert(t('请填写登录密码'))
        return
    }
    if (!dataForm.icode) {
        _alert(t('请填写邀请码'))
        return
    }
    isRequest = true
    dataForm.password = md5(dataForm.password_flag)
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'a=registerAct',
            data: dataForm
        }).then(async (res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                isRequest = false
                getVcode()  //更新图形验证码
                return
            }
            _alert(res.msg, function () {
                router.push({ name: 'Login' })
            })
        })
    }, delayTime)
}

//挂载后执行
onMounted(() => {
    if (isLogin()) {
        router.push({ name: 'Default' })
        return
    }
    getVcode()
});
</script>
<style scoped lang="scss">
.formbox .van-field {
    color: #3d3d3d;
    padding: 0.4rem;
    border-radius: 8px;
    background-color: transparent;
    border: 1px solid #d7d2d0;
}

.sendCodeBtn {
    border: none;
    font-weight: bold;
    font-size: 16px;
    color: #cb1a00;
}

.register {
    .login_adorn {
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding-top: 1rem;

        img {
            width: 16rem;
            height: 3rem;
            margin: 16vh 0 5vh 0;
        }
    }

    .avatar {
        width: 100%;
        height: 3rem;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 1rem;


        p:nth-child(1) {
            font-weight: bold;            
            color: #d7d2d0;
            border-bottom: 1px solid #d7d2d0;
            width: 6rem;
            text-align: center;
            padding-bottom: 0.5rem;
        }

        p:nth-child(2) {
            font-weight: bold;
            color: #cd1f14;
            border-bottom: 1px solid #cd1f14;
            width: 6rem;
            text-align: center;
            padding-bottom: 0.5rem;
        }
    }

    .formbox {
        width: 90%;
        margin-left: 5%;
        height: 25rem;
        padding: 0 1rem;
        box-sizing: border-box;
        padding-bottom: 1.25rem;
        border-radius: 15px;
        background: white;

        .van-field {
            &.accountItem {
                :deep(.van-cell__value) {
                    margin-left: 0;
                }

                :deep(.van-field__label) {
                    margin-left: 1rem;
                }
            }
        }

        .registerBtnWrapper {
            display: flex;
            align-items: center;
            justify-content: space-around;
            margin-top: 1.5rem;

            .onLogin_btn{
                background: url(/src/assets/img/login/login_btn.png);
                background-repeat: no-repeat;
                background-size: 100% 100%;
                height: 2.5rem;
                width: 16rem;
                text-align: center;
                line-height: 2.5rem;
                color: white;
            }
        }
    }

    :deep(.van-field__control) {
        color: black;
    }

    :deep(.van-field__control::-webkit-input-placeholder) {
        color: #808080 !important; //placeholder颜色修改
    }
}
</style>