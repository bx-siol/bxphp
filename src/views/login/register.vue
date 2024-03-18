<template>
    <div class="register">
        <MyNav :title="t('注册')" leftText=''></MyNav>

        <div class="formbox">
            <div class="deckout"></div>
            <div class="deckout2"></div>
            <div class="avatar">
                <img :src="illustration">
            </div>
            <van-cell-group>
                <van-field v-model="dataForm.account" class="accountItem" :left-icon="phone" label="+91" label-width="30"
                    maxlength="10" :placeholder="t('请输入手机号')"></van-field>

                <van-field v-model="dataForm.scode" :placeholder="t('短信验证码')" maxlength="6" style="padding: 0.3rem 0.6rem;">
                    <template #left-icon>
                        <van-image :src="lock" style="width: 1.5rem;height: 1.5rem;margin-top: 0.4rem;" />
                    </template>
                    <template #button>
                        <van-button size="mini" type="warning" class="sendCodeBtn" :loading="sendLoading"
                            @click="onSendCode" plain>
                            <van-count-down v-if="isTimer" :time="60000" :auto-start="true" format="sss"
                                @finish="onTimerFinish" />
                            <span v-else>{{ t('发送') }}</span>
                        </van-button>
                    </template>
                </van-field>

                <van-field v-model="dataForm.nickname" :left-icon="useractive" :placeholder="t('请填写昵称')" v-if="false"></van-field>

                <van-field v-model="dataForm.password_flag" type="password" :left-icon="key"
                    :placeholder="t('请填写登录密码')"></van-field>

                <van-field v-model="dataForm.icode"
                    :disabled="(route.query.Icode ? route.query.Icode : (route.query.icode ? route.query.icode : '')) > 0"
                    :left-icon="verify" :placeholder="t('请填写邀请码')"></van-field>
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
            <!-- <div class="otheraccount">{{ t('已有账号') }}？<a href="javascript:;" @click="onLink({ name: 'Login' })">{{
                t('立即登录')
            }}</a> </div> -->
            <div class="registerBtnWrapper">
                <van-button class="registerBtn" @click="onRegister">{{
                    t('注册')
                }}</van-button>
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
import MyNav from "../../components/Nav.vue";
import {
    ico_1, ico_2, ico_3, ico_4, ico_5, ico_6, ico_103, img_yzm
} from '../../global/assets';
import useractive from '../../assets/img/login/name3.png';
import ornament from '../../assets/img/login/ornament.png';
import ornament2 from '../../assets/img/login/ornament2.png';
import avatar from '../../assets/img/login/avatar.png';
import illustration from '../../assets/img/login/illustration.png';

import lock from '../../assets/img/login/lock3.png';
import phone from '../../assets/img/login/lock1.png';
import verify from '../../assets/img/login/lock4.png';
import key from '../../assets/img/login/lock2.png';
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
    // nickname: ''
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

<style>
.accountItem .van-field__label label {
    font-weight: bold;
    color: white;
}

/* .formbox .van-field {
    background: #0098a2;
} */
</style>
<style scoped lang="scss">
.formbox .van-field {
    color: #3d3d3d;
    padding: 0.6rem;
    border-radius: 30px;
    background-color: #e8f1ee;
    // :deep(.van-field__left-icon){
    //     margin: 0.2rem 0.4rem 0 0;
    // }
}

.sendCodeBtn {
    border: none;
    font-weight: bold;
    font-size: 16px;
    color: #84973b;
}

.register {
    padding: 0 1rem;
    background: url("../../assets//img//login/back.png") no-repeat 0 0;
    background-size: 100% 8rem;
    background-color: #84973b;

    :deep(.van-nav-bar) {
        background-color: transparent;

    }

    :deep(.van-nav-bar__left) {
        .alter {
            color: #ffffff !important;
        }
    }

    :deep(.van-nav-bar__title) {
        .alter {
            color: #ffffff !important;
        }
    }

    .avatar {
        width: 100%;
        height: 10rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;

        img:nth-child(1) {
            width: 10rem;
            position: relative;
            top: 0;
        }

        .name {
            margin-top: 0.625rem;
            width: 9.625rem;
            height: 1.2625rem;
            border: 1px solid #ddd;
            border-radius: 0.3125rem;
            font-size: 0.75rem;
            color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    }

    .formbox {
        width: 100%;
        padding: 0rem 1rem 1.25rem;
        box-sizing: border-box;
        position: relative;
        top: 5rem;
        border-radius: 8px;
        background-color: #fff;

        .deckout {
            position: absolute;
            top: -2.4rem;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgb(255, 255, 255, 0.15);
            height: 2.4rem;
            width: 80%;
            border-radius: 8px 8px 0 0;
        }

        .deckout2 {
            position: absolute;
            top: -1.2rem;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgb(255, 255, 255, 0.2);
            height: 1.2rem;
            width: 90%;
            border-radius: 8px 8px 0 0;
            mix-blend-mode: screen;
        }

        .van-cell-group {
            background: transparent;

        }

        .van-field {

            &.accountItem {
                :deep(.van-cell__value) {
                    margin-left: 0;
                }

                :deep(.van-field__label) {
                    margin-left: 0.6rem;
                    margin-right: 0;
                }
            }
        }

        .registerBtnWrapper {
            width: 100%;
            box-sizing: border-box;

            .registerBtn {
                background-color: #84973b;
                color: #fff;
                border-radius: 30px;
                margin-top: 1rem;
                width: 100%;
                font-size: 1rem;
                border-width: 0;
                font-weight: bold;
            }
        }
    }

    :deep(.van-field__control) {
        color: #000;
    }

    :deep(.van-field__control::-webkit-input-placeholder) {
        color: #9cb7ae !important; //placeholder颜色修改
    }
}
</style>