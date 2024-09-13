<template>
    <div class="login">
        <div class="login_adorn">
            <div class="deckout">
                <img style="width:80%" :src="ornament">
                <!-- <p>Welcome to join Nestle</p> -->
            </div>
            <!-- <div class="deckout2">
                <img :src="ornament2">
            </div> -->
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
                        <van-icon v-if="showPassword" name="eye-o" color="#d6d6d6"
                                  @click="showPassword = false"></van-icon>
                        <van-icon v-else name="closed-eye" color="#d6d6d6" @click="showPassword = true"></van-icon>
                    </template>
                </van-field>
                <van-field v-model="dataForm.vcode" :placeholder="t('图形验证码')" @keyup.enter="onLogin" maxlength="4">
                    <template #left-icon>
                        <van-image :src="lock2" style="width: 1.5rem;height: 1.5rem;" />
                    </template>
                    <template #right-icon>
                        <van-image class="imgCode"
                                   style="border-radius: 5px;overflow: hidden;height: 2.2rem;width: 5rem;"
                                   :src="dataForm.vcode_url" @click="getVcode" />
                    </template>
                </van-field>
            </van-cell-group>
            <div class="makeup">
                <p style="color:white" @click="onLink({ name: 'Forget' })"> {{ t('忘记密码') }} ?</p>
            </div>

            <div class="registerBtnWrapper">
                <van-button class="loginBtn" @click="onLogin">
                    {{ t('登录') }}
                </van-button>
            </div>

            <div style="text-align: center; margin-top: 2vh; color: white;">Don't Have An Account? <span style="color: #f6aa15" @click="onLink({ name: 'Register' })"> {{ t('注册') }}</span></div>

            <div v-show="appshow" @click="appdload" class="appIco">
                <span>APP</span>
            </div>
            <!-- <div class="makeup area">
        <p>Dont Have An Account? <span @click="onLink({ name: 'Register' })">{{ t('注册') }}</span></p>
    </div> -->

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
// import {
//     ico_1, ico_2, ico_4, ico_6 
// } from '../../global/assets';
import lock1 from '../../assets/img/login/lock1.png';
import lock2 from '../../assets/img/login/lock2.png';
import ornament from '../../assets/img/login/ornament.png';
// import ornament2 from '../../assets/img/login/ornament2.png';
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
        color: white;
        padding: 0.6rem;
        border-radius: 8px;
        background-color: transparent;
        border-bottom: 1px solid #fde9e7;
    }

    .login {
        .login_adorn {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding-top: 6rem;
            padding-bottom: 4rem;

            .deckout {
                display: flex;
                flex-direction: column;
                align-items: center;

                img {
                    width: 13rem;
                }

                p {
                    color: #64523e;
                    font-size: 14px;
                    margin-top: 1rem;
                }
            }

            .deckout2 {
                img {
                    width: 10rem;
                    position: relative;
                    top: 1rem;
                }
            }
        }
        // padding: 0 1rem;
        .login_top {
            width: 100%;
            height: 4rem;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;

            p:nth-child(1) {
                width: 94px;
                font-weight: bold;
                color: white;
                border-bottom: 4px solid white;
            }

            p:nth-child(2) {
                width: 94px;
                font-weight: bold;
                color: white;
                /*border-bottom: 2px solid white;*/
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
            margin-bottom: 3vh;

            span {
                width: 5rem;
                height: 5rem;
                display: inline-block;
                border-radius: 50%;
                font-size: 26px;
                color: #d61300;
                background: #fff;
                display: flex;
                justify-content: center;
                align-items: center;
                font-weight: bold;
            }
        }

        .formbox {
            padding: 0 1rem;
            box-sizing: border-box;
            padding-bottom: 1.25rem;
            border-radius: 15px;
            background-color: rgb(235 23 0);
            margin: 0px auto;
            margin-top: 3rem;

            .van-cell-group {
                width: 100%;
                box-sizing: border-box;
                background: none;
            }



            .registerBtnWrapper {
                width: 100%;
                box-sizing: border-box;
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
            color: #d61300;
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .area {
            justify-content: center;

            span {
                color: #c69c6d;
            }
        }

        :deep(.van-field__control) {
            color: white;
        }

        :deep(.van-field__control::-webkit-input-placeholder) {
            color: #b6acab;
        }
    }
</style>