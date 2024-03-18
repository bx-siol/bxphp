<template>
    <div class="login">
        <div class="formbox">
            <div class="deckout"></div>
            <div class="deckout2"></div>
            <div class="login_top">
                <img :src="illustration">
            </div>
            <van-cell-group>
                <van-field v-model="dataForm.account" :left-icon="lock1" :placeholder="t('请填写账号')" maxlength="10"></van-field>
                <van-field v-model="dataForm.password" :type="showPassword ? 'text' : 'password'" :left-icon="lock2"
                    :placeholder="t('请填写登录密码')">
                    <template #right-icon>
                        <van-icon v-if="showPassword" name="eye-o" color="#d6d6d6" @click="showPassword = false"></van-icon>
                        <van-icon v-else name="closed-eye" color="#d6d6d6" @click="showPassword = true"></van-icon>
                    </template>
                </van-field>
                <van-field v-model="dataForm.vcode" :placeholder="t('图形验证码')" @keyup.enter="onLogin" maxlength="4" style="padding: 0.4rem;">
                    <template #left-icon>
                        <van-image :src="lock2" style="width: 1.4rem;height: 1.4rem;margin-top: 0.4rem;" />
                    </template>
                    <template #right-icon>
                        <van-image class="imgCode" style="border-radius: 5px;overflow: hidden;height: 2.2rem;width: 5rem;"
                            :src="dataForm.vcode_url" @click="getVcode" />
                    </template>
                </van-field>
            </van-cell-group>
            <div class="makeup">
                <p @click="onLink({ name: 'Forget' })"> {{ t('忘记密码') }} ?</p>
            </div>

            <div class="registerBtnWrapper">
                <van-button class="loginBtn" @click="onLogin">
                    {{ t('登录') }}
                </van-button>
            </div>
            <div class="makeup area">
                <p @click="onLink({ name: 'Register' })"> {{ t('立即注册') }}</p>
            </div>
            <div v-show="appshow" @click="appdload" class="appIco">
                <span>APP</span>
            </div>
        </div>
    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from "vue";
import { Checkbox, Field, CellGroup, Button, Row, Col, Image,Icon } from "vant";
import MyLanguage from "../../components/Language.vue";
import MyLoading from "../../components/Loading.vue";
export default defineComponent({
    name: "login",
    components: {
        MyLanguage,MyLoading,
        [Image.name]: Image,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Button.name]: Button,
        [Row.name]: Row,
        [Col.name]: Col,
        [Icon.name]: Icon,

    }
})
</script>
<script lang="ts" setup>
// import {
//     ico_1, ico_2, ico_4, ico_6 
// } from '../../global/assets';
import lock1 from '../../assets/img/login/lock1.png';
import lock2 from '../../assets/img/login/lock2.png';
import ornament from '../../assets/img/login/ornament.png';
import ornament2 from '../../assets/img/login/ornament2.png';
import illustration from '../../assets/img/login/illustration.png';

import http from "../../global/network/http";
import { _alert, lang } from "../../global/common";
import { doLogin, getUserinfo, isLogin } from "../../global/user";
import { useRouter } from "vue-router";
import { goRoute } from "../../global/common";
import md5 from "md5";
import { useStore } from "vuex";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

let isRequest = false
const router = useRouter()
const store = useStore()
const loadtitle = ref("Logging in")
const loadingShow = ref(false);
const showPassword = ref(false)

const onLink = (to: any) => {
    goRoute(to)
}
const appdload = () => {
    window.location.href = '/app'
}
//获取图形验证码
const getVcode = () => {
    http({
        url: 'a=getVcode'
    }).then((res: any) => {
        if (res.code == 1) {
            dataForm.sid = res.data.session_id
            dataForm.vcode_url = res.data.url
        }
    });
}

const dataForm = reactive({
    sid: '',
    vcode: '',
    vcode_url: '',
    account: '',
    password: ''
})

const remember = ref(true)

let rememberJson = window.localStorage.getItem('remember')
if (rememberJson) {
    let member = JSON.parse(rememberJson)
    if (member.account) {
        dataForm.account = member.account
        dataForm.password = member.password
        remember.value = true
    }
}

const onRemember = (ev: any) => {
    if (!ev) {
        window.localStorage.removeItem('remember')
    } else {
        let member = { account: '', password: '' }
        if (dataForm.account) {
            member.account = dataForm.account
        }
        if (dataForm.password) {
            member.password = dataForm.password
        }
        window.localStorage.setItem('remember', JSON.stringify(member))
    }
}

const onLogin = () => {
    var accountRegex = /^\d+$/;
    if (!accountRegex.test(dataForm.account)) {
        _alert('The account can only contain numbers and cannot include symbols');
        return;
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    loadingShow.value = true;
    const delayTime = Math.floor(Math.random() * 1000);

    setTimeout(() => {
        http({
        url: 'a=login',
        data: {
            account: dataForm.account,
            password: md5(dataForm.password),
            sid: dataForm.sid,
            vcode: dataForm.vcode
        }
        }).then((res: any) => {
        loadingShow.value = false;
        if (res.code != 1) {
            isRequest = false
            getVcode()
            _alert(res.msg)
            return
        }
        getUserinfo({ token: res.data.token }).then((res2: any) => {//因为设置了拦截器，回调内必然是调用成功的
            doLogin(res2.data, res.data.token)
            onRemember(true)
            if (store.state.backurl) {
                router.push({ path: store.state.backurl })
                store.state.backurl = ''
            } else {
                router.push({ name: 'Default' })
            }
        })
        })
    }, delayTime)
}
const appshow = ref(true)



onMounted(() => {
    if (window.location.href.indexOf('csisolar.in') > 0 || window.location.href.indexOf('csisolar.life ') > 0) {
        appshow.value = false;
    }
    if (isLogin()) {
        router.push({ name: 'Default' })
        return
    }
    getVcode()
})
</script>
<style scoped lang="scss">
.formbox .van-field {
    color: #3d3d3d;
    padding: 0.6rem;
    border-radius: 30px;
    background-color: #e8f1ee;
}

.login {
    padding: 0 1rem;
    background: url("../../assets//img//login/back.png") no-repeat 0 0;
    background-size: 100% 8rem;
    background-color: #84973b;
    .deckout {
        position: absolute;
        top: -2.4rem;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgb(255, 255, 255,0.15);
        height: 2.4rem;
        width: 80%;
        border-radius: 8px 8px 0 0;
    }
    .deckout2 {
        position: absolute;
        top: -1.2rem;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgb(255, 255, 255,0.2);
        height: 1.2rem;
        width: 90%;
        border-radius: 8px 8px 0 0;
        mix-blend-mode: screen;
    }

    .login_top {
        width: 100%;
        height: 10rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;

        img:nth-child(1) {
            width: 10rem;
            position: relative;
            top: 0rem;
        }

        img:nth-child(2) {
            width: 8rem;
            position: relative;
            top: 2.8rem;
        }

        .avatar {
            width: 4.125rem;
            height: 4.125rem;

            img {
                border-radius: 8rem;
                width: 4.125rem;
                height: 4.125rem;
            }
        }
    }

    .formbox {
        width: 100%;
        padding: 0 1rem;
        box-sizing: border-box;
        position: relative;
        padding-bottom: 1.25rem;
        top: 8rem;
        border-radius: 8px;
        background-color: #fff;

        .van-cell-group {
            width: 100%;
            box-sizing: border-box;
            background: none;
        }

        .appIco {
            width: 100%;
            height: 2.5rem;
            font-size: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2.375rem;

            span {
                width: 2.8rem;
                height: 2.8rem;
                display: inline-block;
                border-radius: 50%;
                color: #fff;
                background: #84973b;
                display: flex;
                justify-content: center;
                align-items: center;
                font-weight: bold;
            }
        }

        .registerBtnWrapper {
            width: 100%;
            box-sizing: border-box;

            .loginBtn {
                width: 100%;
                box-sizing: border-box;
                background: #84973b !important;
                font-weight: bold;
                display: block;
                padding: 0;
                border: 0;
                color: #fff;
                height: 2.8rem;
                font-size: 1rem;
                margin: 1.875rem auto 0;
                border-radius: 16rem;
            }
        }

        .registerBtn {
            background: #dfe0e0;
            color: #807977;
            width: 9.5625rem;
            height: 1.5625rem;
            font-size: 0.8125rem;
            border-radius: 1.25rem;
            display: block;
            padding: 0;
            border: 0;
            margin: 4.375rem auto 0;
        }
    }

    .makeup {
        color: #84973b;
        display: flex;
        margin-top: 1rem;
    }

    .area {
        justify-content: center;
        margin-top: 1.5rem;

        p {
            border-bottom: 1px solid;
        }
    }

    :deep(.van-field__control) {
        color: #000;
    }

    :deep(.van-field__control::-webkit-input-placeholder) {
        color: #9cb7ae;

    }
}
</style>