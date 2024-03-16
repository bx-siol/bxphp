<template>
    <div class="conBox">
        <Nav></Nav>
        <div class="streamer">
            <div :class="{ activeTab: currentTab === 'LOGIN' }" @click="handleTab('LOGIN')">LOGIN PASSWORD</div>
            <div :class="{ activeTab: currentTab === 'PAYMENT' }" @click="handleTab('PAYMENT')">PAYMENT PASSWORD</div>
        </div>
        <van-form @submit="onSubmit">
            <van-cell-group>
                <van-field v-model="dataForm.account" readonly>
                    <template #left-icon>
                        <van-icon name="user-o" :size="configForm.iconSize" :style="configForm.iconStyle" />
                    </template>
                </van-field>
                <van-field v-model="dataForm.phone_flag" readonly>
                    <template #left-icon>
                        <van-icon name="phone-o" :size="configForm.iconSize" :style="configForm.iconStyle" />
                    </template>
                    <template #right-icon>{{ t('发送手机号') }}</template>
                </van-field>
                <van-field v-model="dataForm.scode" :placeholder="t('短信验证码')" class="dilate">
                    <template #left-icon>
                        <van-icon name="envelop-o" :size="configForm.iconSize" :style="configForm.iconStyle" />
                    </template>
                    <template #button>
                        <van-button size="mini" type="warning" :loading="sendLoading" class="sendCodeBtn"
                            @click="onSendCode(1)" plain>
                            <van-count-down v-if="isTimer" :time="60000" :auto-start="true" format="sss"
                                @finish="onTimerFinish" />
                            <span v-else>{{ t('点击获取') }}</span>
                        </van-button>
                    </template>
                </van-field>
                <van-field v-model="dataForm.password_flag" type="password" :placeholder="t('新密码')">
                    <template #left-icon>
                        <van-image :src="ico_2" width="1.8rem" fit="cover"
                            style="vertical-align: middle;margin-left: -0.25rem;position: relative;top:-3px;" />
                    </template>
                </van-field>
                <van-field v-model="dataForm.password_flag2" type="password" :placeholder="t('确认密码')">
                    <template #left-icon>
                        <van-icon name="passed" :size="configForm.iconSize" :style="configForm.iconStyle" />
                    </template>
                </van-field>
            </van-cell-group>
            <div :style="{ textAlign: 'center', color: '#bd312d', padding: '0 2rem' }" v-if="isPassword2">{{
                t('初始支付密码与登录密码相同') }}
            </div>
            <div style="padding:0 2rem 1rem;">
                <van-button class="myBtn" round block type="primary" native-type="submit">{{ t('提交') }}</van-button>
            </div>
        </van-form>
    </div>
</template>

<script lang="ts">
import { defineComponent, onBeforeUnmount } from 'vue';
import { Form, Field, CellGroup, Cell, Button, Icon, Image, CountDown } from 'vant';
import Nav from '../../components/Nav.vue';

export default defineComponent({
    components: {
        Nav,
        [Form.name]: Form,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [Icon.name]: Icon,
        [Image.name]: Image,
        [CountDown.name]: CountDown,
        [Button.name]: Button
    }
})
</script>

<script lang="ts" setup>
import { ico_2 } from '../../global/assets';
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import { Dialog } from 'vant';
import { http } from "../../global/network/http";
import { _alert, isEmail, lang } from "../../global/common";
import md5 from "md5";
import { doLogout } from "../../global/user";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const route = useRoute()
const store = useStore()

const configForm = reactive({
    iconSize: '1.3rem',
    iconStyle: {
        marginRight: '0.2rem'
    }
})

const currentTab = ref('LOGIN');

const handleTab = (tab: string) => {
    if (tab !== currentTab.value) {
        currentTab.value = tab;
        if (tab === 'PAYMENT') {
            isPassword2.value = true;
        } else {
            isPassword2.value = false;
        }
    }
};


const isPassword2 = ref(false)
const dataForm = reactive({
    account: store.state.user.account,
    phone_flag: store.state.user.phone_flag,
    scode: '',
    ecode: '',
    password: '',
    password_flag: '',
    password_flag2: '',
    type: isPassword2.value ? 2 : 1
})

//获取验证码
const sendLoading = ref(false)
const isTimer = ref(false)
const onSendCode = (ctype: number) => {
    if (isTimer.value) {
        return
    }
    sendLoading.value = true
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        let pdata = { stype: 3 }
        let url = 'a=getPhoneCode'
        if (ctype != 1) {
            url = 'a=getEmailCode'
        }
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
                    onClose: () => { }
                })
                return
            }
            isTimer.value = true
        })
    }, delayTime)
}

const onTimerFinish = () => {
    isTimer.value = false
}

const onSubmit = () => {
    if (dataForm.phone_flag) {
        if (!dataForm.scode) {
            _alert('Please fill in the verification code')
            return
        }
    } else {
        if (!dataForm.ecode) {
            _alert('Please fill in the verification code')
            return
        }
    }
    if (!dataForm.password_flag) {
        _alert('Please fill in the new password')
        return
    } else {
        if (dataForm.password_flag != dataForm.password_flag2) {
            _alert('The password entered twice is inconsistent')
            return
        }
    }
    Dialog.confirm({
        message: t('Are you sure you want to modify it') + '?',
        confirmButtonText: t('Confirm'),
        cancelButtonText: t('Cancel'),
        width: '280px',
        allowHtml: true,
        beforeClose: (action: string): Promise<boolean> | boolean => {
            if (action != 'confirm') {
                return true
            }
            return new Promise((resolve) => {
                const delayTime = Math.floor(Math.random() * 1000);
                setTimeout(() => {
                    http({
                        url: 'c=Setting&a=password_update',
                        data: {
                            type: dataForm.type,
                            scode: dataForm.scode,
                            ecode: dataForm.ecode,
                            password: md5(dataForm.password_flag)
                        }
                    }).then((res: any) => {
                        resolve(true)
                        if (res.code != 1) {
                            _alert(res.msg)
                            return
                        }
                        _alert({
                            type: 'success',
                            message: res.msg,
                            onClose: () => {
                                dataForm.scode = ''
                                dataForm.ecode = ''
                                dataForm.password_flag = ''
                                dataForm.password_flag2 = ''
                            }
                        })
                    })
                }, delayTime)
            })
        }
    }).catch(() => { })
}

onBeforeUnmount(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'a=userinfo'
        }).then((res: any) => {
            if (res.code != 1) {
                doLogout()
            }
        })
    }, delayTime)

})

const getUserInfo = () => {
    const delayTime = Math.floor(Math.random() * 1000);
    // setTimeout(() => {
        http({
            url: 'a=userinfo'
        }).then((res: any) => {
            dataForm.account = res.data.account;
            dataForm.phone_flag = res.data.phone;
            // pad_arr.password = res.data.password;
            // pad_arr.password2 = res.data.password2;
        })
    // }, delayTime)
}

onMounted(() => {
    getUserInfo()
})
</script>

<style scoped>
.conBox .streamer {
    display: flex;
    justify-content: space-between;
    padding: 1rem;
}

.conBox .streamer .activeTab {
    color: #fff;
    background: #64523e;
}

.conBox .streamer div {
    padding: 0.8rem 1rem;
    text-align: center;
    border: 1px solid #64523e;
    border-radius: 2rem;
    font-size: 12px;
    font-weight: bold;
}

.conBox .streamer .nth-child1 {
    background-color: #64523e;
}

.conBox .streamer .nth-child2 {
    background-color: #fff;
}

.conBox .dilate {
    padding: 0 0 0 0.6rem;
}

.conBox .myBtn {
    width: 80%;
    margin: 2rem auto 0;
}

/deep/.van-form {
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: #fff;

}

/deep/.van-cell-group {
    position: inherit;
}

/deep/.van-cell {
    padding: 0.6rem;
    border: 1px solid #c69c6d;
    margin-bottom: 1rem;
    border-radius: 6px;
}

/deep/.van-field__right-icon {
    color: #c69c6d;
}

/deep/.van-button--plain.van-button--warning {
    color: #fff !important;
    padding: 1.38rem 0.8rem;
    background: linear-gradient(to right, #c49b6c 20%, #a77d52);
    ;
}

/deep/.van-field__control {
    color: #000000;
}

/deep/.van-field__control::-webkit-input-placeholder {
    color: #999 !important;
}
</style>