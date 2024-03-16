<template>
    <div class="conBox">
        <Nav></Nav>
        <van-form @submit="onSubmit" :label-width="configForm.labelWidth" :label-align="configForm.labelAlign">
            <van-cell-group>
                <van-field :formatter="formatter" :label="t('真实姓名')" v-model="dataForm.realname"
                    :placeholder="t('请输入真实姓名')" />
                <van-field :label="t('银行名称')" is-link readonly v-model="dataForm.bank_name"
                    :placeholder="t('请选择您的银行名称')" @click="popShowBank = true" />
                <van-field type="number" :label="t('账号')" v-model="dataForm.account" :placeholder="t('请输入您的账号')" />
                <van-field :label="t('ifsc')" show-word-limit maxlength="11" v-model="dataForm.ifsc"
                    :placeholder="t('请填写IFSC代码')" />
                <van-field readonly v-model="dataForm.phone" :label="t('手机号码')" />
                <van-field :rules="[{ pattern: /^[0-9]+$/, message: 'Only numbers can be entered', trigger: 'onBlur' }]"
                    label="OTP" v-model="dataForm.scode" :placeholder="t('请输入OTP')">
                    <template #button>
                        <van-button size="mini" class="sendCodeBtn" :loading="sendLoading" @click="onSendCode" plain>
                            <van-count-down v-if="isTimer" :time="60000" :auto-start="true" format="sss"
                                @finish="onTimerFinish" />
                            <span v-else style="color:#c69c6d ;">{{ t('发送') }}</span>
                        </van-button>
                    </template>
                </van-field>
            </van-cell-group>
            <div class="streamer"></div>

            <div class="article" style="display: block; padding: 0.8rem 0; font-size: 12px;">
                <p>kind tids:</p>
                <p>1. Please fill in your real name</p>
                <p>2. The bank card number cannot contain letters</p>
                <p>3. Please fill in your payment account information correctly, among which IFSC must
                    be 11 digits, and the fifth digit must be 0</p>
            </div>
            <div style="">
                <van-button class="myBtn" round block type="primary" native-type="submit">{{ t('提交') }}</van-button>
            </div>
        </van-form>
    </div>

    <van-popup v-model:show="popShowBank" position="bottom">
        <van-picker :title="t('银行名称')" :default-index="bankIdx" :cancel-button-text="t('取消')"
            :confirm-button-text="t('确定')" @cancel="popShowBank = false" @confirm="onBankConfirm" :columns="banks"
            :columns-field-names="{ text: 'name' }" />
    </van-popup>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>

<script lang="ts">

import { defineComponent } from 'vue';
import { Button, Form, Field, CellGroup, Image, Icon, Overlay, Popup, Picker,CountDown } from 'vant';
import Nav from "../../components/Nav.vue";
import MyLoading from "../../components/Loading.vue";

export default defineComponent({
    components: {
        Nav, MyLoading,
        [Button.name]: Button,
        [Form.name]: Form,
        [CellGroup.name]: CellGroup,
        [Field.name]: Field,
        [Image.name]: Image,
        [Icon.name]: Icon,
        [Overlay.name]: Overlay,
        [Popup.name]: Popup,
        [Picker.name]: Picker,
        [CountDown.name]: CountDown
    }
})
</script>

<script lang="ts" setup>
import { _alert, lang, getSrcUrl, imgPreview } from "../../global/common";
import { ref, reactive, onMounted } from 'vue';
import { useStore } from "vuex";
import { http } from "../../global/network/http";
import md5 from 'md5';
import { flushUserinfo } from "../../global/user";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
let isRequest = false
const router = useRouter()
const store = useStore()
const loadtitle = ref("Loading...")
const loadingShow = ref(false);

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const configForm = reactive({
    labelAlign: 'right',
    labelWidth: '4.8rem'
})

const dataForm = reactive({
    bank_id: 0,
    bank_name: '',
    account: '',
    realname: '',
    ifsc: '',
    password2: '',
    phone: '',
    scode: ''
})

const popShowBank = ref(false)
const bankArr = ref([])
const banks = ref([])
const bankIdx = ref(0)
const cbank = ref(0)

const sendLoading = ref(false)
const isTimer = ref(false)
const onTimerFinish = () => {
    isTimer.value = false
}

const onSendCode = () => {
    if (isTimer.value) {
        return
    }
    if (!dataForm.phone) {
        _alert(t('请输入手机号'))
        return
    }
    sendLoading.value = true
    var delayTime = Math.floor(Math.random() * 1000);
    setTimeout((() => {
        let pdata = { stype: 9, phone: dataForm.phone, email: dataForm.account }
        let url = 'a=getPhoneCode'
        http({
            url: url,
            data: pdata
        }).then((res: any) => {
            setTimeout(() => {
                sendLoading.value = false
            }, 1000)
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            isTimer.value = true
        })
    }), delayTime)
}

const onBankConfirm = (name: any, idx: number) => {
    popShowBank.value = true
    dataForm.bank_name = name
    for (let i in bankArr.value) {
        if (i == idx) {
            dataForm.bank_id = bankArr.value[i].id
        }
    }
    popShowBank.value = false
}
const formatter = (value: any) => {
    if (value != ' ') {
        var reg = /\\+|\~+|\!+|\@+|\#+|¥+|\￥+|\%+|\^+|\&+|\*+|\(+|\)+|\'+|(\")+|\$+|`+|\“+|\”+|\‘+|\’+|\<+|\>+|\?+|\/+|\,+|\.+|\|+|\_+|\++|\—+|\？+|\》+|\《+|\…+|\！+|\（+|\）+|\-+|\=+|\[+|\]+|\;+|\:+|\{+|\}+|\：+|\；+|\【+|\】/g;
        return value.replace(reg, "");
    } else {
        return value;
    }
}
const onSubmit = () => {
    if (dataForm.ifsc.length != 11 || dataForm.ifsc.charAt(4) != "0") {
        _alert('The length of IFSC is 11 bits, the fifth bit is 0')
        return;
    }
    var str = new RegExp("[`_-~!@#$^&*()=|{}':;',\\[\\].<>《》/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？+ ]");
    if (str.test(dataForm.ifsc)) {
        _alert('Symbols cannot appear in the ifsc')
        return;
    }
    if (dataForm.ifsc.indexOf('\\') >= 0) {
        _alert('Symbols cannot appear in the ifsc')
        return;
    }

    if (dataForm.realname.indexOf('/') >= 0) {
        _alert('Symbols cannot appear in the name')
        return;
    }

    if (dataForm.realname.indexOf('\\') >= 0) {
        _alert('Symbols cannot appear in the name')
        return;
    }

    // if (cbank.value != 1) {
    //     _alert('You are not authorized to perform this operation, please contact your superior manager')
    //     return;
    // }

    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    loadingShow.value = true;
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Setting&a=bank_update',
            data: {
                bank_name: dataForm.bank_name,
                bank_id: dataForm.bank_id,
                account: dataForm.account,
                realname: dataForm.realname,
                code: dataForm.scode,
                ifsc: dataForm.ifsc,
                password2: md5(dataForm.password2)
            }
        }).then((res: any) => {
            loadingShow.value = false;
            if (res.code != 1) {
                isRequest = false
                _alert(res.msg)
                return
            }
            dataForm.password2 = ''
            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => {
                    router.go(-1)
                }
            })
        })
    }, delayTime)
}

onMounted(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    // setTimeout(() => {
        http({
            url: 'c=Setting&a=bank'
        }).then((res: any) => {
            if (res.data.bank && res.data.bank.bank_name) {
                dataForm.bank_id = res.data.bank.bank_id
                dataForm.bank_name = res.data.bank.bank_name
                dataForm.account = res.data.bank.account
                dataForm.realname = res.data.bank.realname
                dataForm.ifsc = res.data.bank.ifsc
                for (let i in res.data.bank_arr) {
                    if (res.data.bank_arr[i].id == res.data.bank.bank_id) {
                        bankIdx.value = i * 1
                    }
                }
            }
            dataForm.phone = res.data.user.account
            cbank.value = res.data.user.cbank
            bankArr.value = res.data.bank_arr
            for (let i in res.data.bank_arr) {
                banks.value.push(res.data.bank_arr[i].name)
            }
        })
    // }, delayTime)
})

</script>
<style lang="scss" scoped>
.conBox {
    background: #fff;
    color: #b2b2b2;
    min-height: 100%;
    position: sticky;

    .streamer {
        padding: 0.3rem 0;
        background-color: #f6f6f6;
        width: 100%;
        position: absolute;
        right: 0;
    }

    .van-form {
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #fff;
        border-radius: 8px;
    }

    :deep .van-cell-group {
        position: initial;
    }

    :deep .van-cell {
        margin-bottom: 1rem;
        padding: 0;
        color: #333;
        text-align: left;
        position: initial;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #ccc;

        .label {
            color: #c69c6d;
            font-weight: bold;
            text-align: left;
            border-right: 1px solid #ccc;

        }

        .van-cell__value {
            padding: 0.6rem;
            background-color: #fff;
            display: flex;
            justify-content: space-between;

            .van-field__control {
                color: #000;
            }
        }

        .van-badge__wrapper {
            top: 0.6rem;
            display: none;
        }
    }

    .myBtn {
        position: absolute;
        bottom: 8rem;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
    }

    .article {
        line-height: 24px;
        color: #666;

        P:nth-child(1) {
            font: bold 18px/48px "Rotobo";
            color: #000;
        }



    }
}
</style>