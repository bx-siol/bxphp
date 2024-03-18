<template>
    <div class="forgetPass">
        <MyNav :title="t('找回密码')" leftText=''></MyNav>
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
                <img :src="illustration">
            </div>
            <van-cell-group>
                <van-field style="margin-top: 2rem;" class="accountItem" v-model="dataForm.account" :left-icon="lock"
                    label="+91" label-width="30" :placeholder="t('请输入手机号')"  maxlength="10"></van-field>
                <van-field v-model="dataForm.scode" :placeholder="t('短信验证码')">
                    <template #left-icon>
                        <van-image :src="lock" fit="cover" style="top:4px;width: 1.5rem;" />
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
                <van-field v-model="dataForm.password_flag" type="password" :left-icon="lock"
                    :placeholder="t('请填写新密码')"></van-field>
                <van-field v-model="dataForm.password_check" type="password" :left-icon="lock"
                    :placeholder="t('确认新密码')"></van-field>
                <van-field v-show="false" v-model="dataForm.imgcode" :placeholder="t('图形验证码')" @keyup.enter="onRetrieve"
                    style="padding-top: 0;padding-bottom: 0;padding-right: 0;">
                    <template #left-icon>
                        <van-image :src="lock" fit="cover" style="top:4px;width: 1.5rem;" />
                    </template>
                    <template #right-icon>
                        <van-image class="imgCode" style="border-radius: 5px;overflow: hidden;height: 2.2rem;width: 5rem;"
                            :src="dataForm.imgcode_url" @click="getVcode" />
                    </template>
                </van-field>
            </van-cell-group>
            <!-- <div class="otheraccount">
                <a href="javascript:;"
                    style="border:1px solid #ba192f; color: #ba192f;  border-radius:1rem ;  padding: 0.5rem;"
                    @click="onLink({ name: 'Login' })">{{
                        t('返回登录')
                    }}</a>
            </div> -->
            <van-button class="forgetPassBtn" @click="onRetrieve"
                style="background: linear-gradient(to right, #c49b6c 20%, #a77d52); color: #fff;border-radius: 6px;"> {{
                    t('找回密码') }}</van-button>
        </div>
    </div>
</template>

<script lang="ts">
import MyNav from "../../components/Nav.vue";
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
import ornament2 from '../../assets/img/login/ornament2.png';
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
    background-size: 100% 38rem;

    :deep(.van-nav-bar__left) {
        .alter {
            color: #c69c6d !important;
        }
    }

    :deep(.van-nav-bar__title) {
        .alter {
            color: #c69c6d !important;
        }
    }

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

    .formbox {
        width: 100%;
        padding: 2rem 1rem;
        box-sizing: border-box;
        padding-bottom: 1.25rem;
        border-radius: 16px;
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

        .sendCodeBtn {
            border: none;
            font-weight: bold;
            font-size: 16px;
            color: #c69c6d;

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
    }

    .avatar {
        width: 100%;
        height: 10rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: auto;
        margin-bottom: 2rem;

        img:nth-child(1) {
            width: 10rem;
            position: relative;
        }
    }

}

.van-cell-group {
    background: transparent;

}

.formbox .van-field {
    color: #3d3d3d;
    padding: 0.6rem;
    border-radius: 8px;
    background-color: transparent;
    border-bottom: 1px solid #c69c6d;
}


.accountItem .van-field__label label {
    font-weight: bold;
    color: white;
}

:deep(.van-field__control) {
    color: #fff;
}

:deep(.van-field__control::-webkit-input-placeholder) {
    color: #808080 !important; //placeholder颜色修改
}
</style>