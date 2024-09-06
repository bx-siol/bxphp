<template>
    <div class="login">
        <div class="login_adorn">            
            <img :src="ornament">
        </div>

        <div class="formbox">
            <div class="login_top">
                <p @click="onLink({ name: 'Login' })"> {{ t('登录') }}</p>
                <p @click="onLink({ name: 'Register' })"> {{ t('注册') }}</p>
            </div>
            <van-cell-group>
                <van-field v-model="dataForm.account" :left-icon="lock1" :placeholder="t('请填写账号')"
                    maxlength="10"></van-field>
                <van-field v-model="dataForm.password" :type="showPassword ? 'text' : 'password'" :left-icon="lock2"
                    :placeholder="t('请填写登录密码')">
                    <template #right-icon>
                        <van-icon v-if="showPassword" name="eye-o" color="#d6d6d6" @click="showPassword = false"></van-icon>
                        <van-icon v-else name="closed-eye" color="#d6d6d6" @click="showPassword = true"></van-icon>
                    </template>
                </van-field>
                <van-field v-model="dataForm.vcode" :placeholder="t('图形验证码')" @keyup.enter="onLogin" maxlength="4">
                    <template #left-icon>
                        <van-image :src="lock3" style="width: 1.5rem;height: 1.5rem;" />
                    </template>
                    <template #right-icon>
                        <van-image class="imgCode" style="border-radius: 5px;overflow: hidden;height: 2.2rem;width: 5rem;" :src="dataForm.vcode_url" @click="getVcode" />
                    </template>
                </van-field>
            </van-cell-group>
            <div class="makeup">
                <p @click="onLink({ name: 'Forget' })"> {{ t('忘记密码') }} ?</p>
            </div>

            <div class="registerBtnWrapper" @click="onLogin">
                <div class="onLogin_btn">
                    {{ t('登录') }}
                </div>
            </div>
        </div>
        
        <div v-show="appshow" @click="appdload" class="appIco">
            <span>APP</span>
        </div>
    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from "vue";
import { Checkbox, Field, CellGroup, Button, Row, Col, Image, Icon } from "vant";
import MyLanguage from "../../components/Language.vue";
import MyLoading from "../../components/Loading.vue";

export default defineComponent({
    name: "login",
    components: {
        MyLanguage, MyLoading,
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
import lock1 from '../../assets/img/login/lock1.png';
import lock2 from '../../assets/img/login/lock2.png';
import lock3 from '../../assets/img/login/lock3.png';
import ornament from '../../assets/img/login/ornament.png';
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
    padding: 0.4rem;
    border-radius: 8px;
    background-color: transparent;
    border: 1px solid #d7d2d0;
}

.login {
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

    .login_top {
        width: 100%;
        height: 3rem;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 1rem;

        p:nth-child(1) {
            font-weight: bold;
            color: #cd1f14;
            border-bottom: 1px solid #cd1f14;
            width: 6rem;
            text-align: center;
            padding-bottom: 0.5rem;
        }

        p:nth-child(2) {
            font-weight: bold;
            color: #d7d2d0;
            width: 6rem;
            text-align: center;
            border-bottom: 1px solid #d7d2d0;
            padding-bottom: 0.5rem;
        }

    }

    .formbox {
        width: 90%;
        margin-left: 5%;
        height: 21rem;
        padding: 0 1rem;
        box-sizing: border-box;
        padding-bottom: 1.25rem;
        border-radius: 15px;
        background: white;

        .van-cell-group {
            width: 100%;
            box-sizing: border-box;
            background: none;
        }

        .registerBtnWrapper {
            display: flex;
            align-items: center;
            justify-content: space-around;

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
    
    .appIco {
            width: 100%;
            height: 2.5rem;
            font-size: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2.375rem;

            span {
                width: 3.5rem;
                height: 3.5rem;
                display: inline-block;
                border-radius: 50%;
                color: #cb1200;
                background: #fff;
                display: flex;
                justify-content: center;
                align-items: center;
                font-weight: bold;
                font-size: 1.2rem;
                box-shadow: 1px 1px 12px #837a7a;
            }
        }

    .makeup {
        color: #d7d2d0;
        display: flex;
        justify-content: flex-end;
        margin: 0rem 0 1.5rem 0;
        font-size: 0.8rem;
    }

    :deep(.van-field__control) {
        color: #000;
    }

    :deep(.van-field__control::-webkit-input-placeholder) {
        color: #808080;

    }
}
</style>