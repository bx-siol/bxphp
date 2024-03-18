<template>
    <div class="register">
        <div class="login_adorn">
            <div class="deckout">
                <img :src="ornament">
                <p>Welcome to join Nestle</p>
            </div>
            <div class="deckout2">
                <img :src="ornament2">
            </div>
        </div>
        <div class="formbox">
            <div class="avatar">
                <p @click="onLink({ name: 'Login' })"> {{ t('登录') }}</p>
                <p @click="onLink({ name: 'Register' })"> {{ t('注册') }}</p>
            </div>
            <van-cell-group>
                <van-field v-model="dataForm.account" class="accountItem" :left-icon="phone" label="+91" label-width="30" maxlength="10"
                    :placeholder="t('请输入手机号')"></van-field>
                <van-field v-model="dataForm.scode" :placeholder="t('短信验证码')" maxlength="6">
                    <template #left-icon>
                        <van-image :src="lock" style="width: 1.5rem;height: 1.5rem;" />
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
    var accountRegex = /^\d+$/;
    if (!accountRegex.test(dataForm.account)) {
        _alert('The account can only contain numbers and cannot include symbols');
        return;
    }
    
    if (isRequest) {
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
            router.push({ name: 'Login' })
            _alert({
                icon: 'success',
                message: res.msg,
                onClose: () => {
                    
                }
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
    border-radius: 8px;
    background-color: transparent;
    border-bottom: 1px solid #c69c6d;
}


.sendCodeBtn {
    border: none;
    font-weight: bold;
    font-size: 16px;
    color: #c69c6d;
}

.register {
    .login_adorn {
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding-top: 1rem;

        .deckout {
            display: flex;
            flex-direction: column;
            align-items: center;

            img {
                width: 8rem;
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

    :deep(.van-nav-bar) {
        background-color: transparent;
    }

    .van-nav-bar {
        background-color: #1e1e2a;
    }

    .avatar {
        width: 100%;
        height: 6.8rem;
        display: flex;
        justify-content: space-around;
        align-items: center;


        p:nth-child(1) {
            font-weight: bold;
            color: #808080;
        }

        p:nth-child(2) {
            font-weight: bold;
            color: #c69c6d;
            border-bottom: 2px solid #c69c6d;
        }
    }

    .formbox {
        width: 100%;
        height: 38rem;
        padding: 0rem 1rem 1.25rem;
        box-sizing: border-box;

        border-radius: 30px 30px 0 0;
        background: #64523e;


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
                    margin-left: 1rem;
                }
            }
        }

        .registerBtnWrapper {
            width: 100%;
            box-sizing: border-box;
            display: flex;

            .registerBtn {
                background: linear-gradient(to right, #c49b6c 20%, #a77d52);
                color: #fff;
                border-radius: 6px;
                margin: 1rem auto 0;
                width: 80%;
                font-size: 1rem;
                border-width: 0;
                font-weight: bold;
            }
        }
    }

    :deep(.van-field__control) {
        color: #fff;
    }

    :deep(.van-field__control::-webkit-input-placeholder) {
        color: #808080 !important; //placeholder颜色修改
    }
}
</style>