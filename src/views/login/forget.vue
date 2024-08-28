<template>
    <div class="forgetPass">
        <div class="login_adorn">
            <img :src="ornament">
        </div>

        <div class="formbox">
            <van-cell-group>
                <van-field style="margin-top: 1rem;" class="accountItem" v-model="dataForm.account" :left-icon="lock"
                    label="+91" label-width="30" :placeholder="t('请输入手机号')"  maxlength="10"></van-field>
                <van-field v-model="dataForm.scode" :placeholder="t('短信验证码')">
                    <template #left-icon>
                        <van-image :src="lock" fit="cover" style="top:4px;width: 1.5rem;" />
                    </template>
                    <template #button>
                        <van-button size="mini" type="warning" class="sendCodeBtn" :loading="sendLoading" @click="onSendCode" plain>
                            <van-count-down v-if="isTimer" :time="60000" :auto-start="true" format="sss" @finish="onTimerFinish" />
                            <span v-else>{{ t('发送') }}</span>
                        </van-button>
                    </template>
                </van-field>
                <van-field v-model="dataForm.password_flag" type="password" :left-icon="lock" :placeholder="t('请填写新密码')"></van-field>
                <van-field v-model="dataForm.password_check" type="password" :left-icon="lock" :placeholder="t('确认新密码')"></van-field>
                <van-field v-show="false" v-model="dataForm.imgcode" :placeholder="t('图形验证码')" @keyup.enter="onRetrieve" 
                    style="padding-top: 0;padding-bottom: 0;padding-right: 0;">
                    <template #left-icon>
                        <van-image :src="lock" fit="cover" style="top:4px;width: 1.5rem;" />
                    </template>
                    <template #right-icon>
                        <van-image class="imgCode" style="border-radius: 5px;overflow: hidden;height: 2.2rem;width: 5rem;" :src="dataForm.imgcode_url" @click="getVcode" />
                    </template>
                </van-field>
            </van-cell-group>
            <div class="makeup">
                <p @click="onLink({ name: 'Login' })"> {{ t('返回登录') }} ?</p>
            </div>
            <div class="registerBtnWrapper" @click="onRetrieve">
                <div class="onLogin_btn">
                    {{ t('找回密码') }}
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Button, CellGroup, Field, Image, CountDown } from "vant";

export default defineComponent({
    name: "forgetPass",
    components: {
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Button.name]: Button,
        [Image.name]: Image,
        [CountDown.name]: CountDown,
    }
})
</script>
<script lang="ts" setup>
import {
    ico_1, ico_2, ico_3, ico_4, ico_5, ico_6, ico_103, img_yzm
} from '../../global/assets';
import { defineComponent, ref, reactive, toRefs, onMounted } from 'vue';
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import md5 from 'md5'
import http from '../../global/network/http'
import ornament from '../../assets/img/login/ornament.png';
import lock from '../../assets/img/login/lock2.png'
import { _alert, goRoute, lang } from "../../global/common";
import { isLogin } from "../../global/user";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

let isRequest = false
const store = useStore()
const router = useRouter()

const onLink = (to: any) => {
    goRoute(to)
}

const dataForm = reactive({
    sid: '',
    account: '',
    password: '',
    password_flag: '',
    password_check: '',
    ecode: '',
    scode: '',
    imgcode: '',
    imgcode_url: ''
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
        let pdata = { stype: 3, phone: dataForm.account, email: dataForm.account }
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

//找回密码
const onRetrieve = () => {
    var accountRegex = /^\d+$/;
    if (!accountRegex.test(dataForm.account)) {
        _alert('The account can only contain numbers and cannot include symbols');
        return;
    }

    if (dataForm.password_flag != dataForm.password_check) {
        _alert('The password entered twice is inconsistent')
        return
    }
    dataForm.password = md5(dataForm.password_flag)

    if (isRequest) {
        return
    }
    isRequest = true
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'a=forgetAct',
            data: dataForm
        }).then(async (res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                isRequest = false
                getVcode()  //更新图形验证码
                return
            }
            _alert({
                icon: 'success',
                message: res.msg,
                onClose: () => {
                    router.push({ name: 'Login' })
                }
            })
        })
    }, delayTime)

}

onMounted(() => {
    if (isLogin()) {
        router.push({ name: 'Default' })
        return
    }
    getVcode()
});
</script>

<style lang="scss" scoped>
.forgetPass {
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

    .formbox {
        width: 90%;
        margin-left: 5%;
        height: 22rem;
        padding: 0.5rem 1rem;
        box-sizing: border-box;
        border-radius: 15px;
        background: white;

        .sendCodeBtn {
            border: none;
            font-weight: bold;
            font-size: 16px;
            color: #cb1a00;
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

    .formbox .van-field {
        color: #3d3d3d;
        padding: 0.4rem;
        border-radius: 8px;
        background-color: transparent;
        border: 1px solid #d7d2d0;
    }

    .accountItem .van-field__label label {
        font-weight: bold;
        color: black;
    }

    :deep(.van-field__control) {
        color: black;
    }

    :deep(.van-field__control::-webkit-input-placeholder) {
        color: #808080 !important; //placeholder颜色修改
    }

    .makeup {
        color: #d7d2d0;
        display: flex;
        justify-content: flex-end;
        margin: 0rem 0 1.5rem 0;
        font-size: 1rem;
    }
}
</style>