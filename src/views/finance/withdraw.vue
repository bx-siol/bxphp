<template>
    <div class="recharge">
        <MyNav leftText=''>
            <template #right>
                <span @click="onLink({ name: 'Finance_withdrawlog' })">Record</span>
            </template>
        </MyNav>
        <div class="recharge_wrap">
            <div class="balances" v-if="false">
                <div style="display: flex;align-items: center;">
                    <img :src="qb">
                    <p class="balance">{{ t('余额') }}</p>
                </div>
                <span>₹ {{ wallet.balance }}</span>
            </div>

            <div>

                <div class="payinfo" style="margin-top: 1rem;">
                    <p class="tit">{{ t('银行账户') }}</p>
                    <ul>
                        <!-- <li><span>{{ dataForm.money * tar / 100 }}RS</span>{{ t('税收') }}</li>
                        <li><span>{{ tar }}%</span>{{ t('费用比率') }}</li>
                        <li><span>{{ min }} RS</span>{{ t('最小提现金额') }}</li>
                        <li><span>{{ max }} RS</span>{{ t('最大提现金额') }}</li> -->
                        <li><span>{{ t('银行名称') }}</span> <span>{{ banklog.bank_name }}</span> </li>
                        <li><span>{{ t('真实姓名') }}</span> <span>{{ banklog.realname }}</span> </li>
                        <li><span>{{ t('银行账号') }}</span> <span>{{ banklog.account }}</span></li>
                        <li><span>{{ t('IFSC ') }} </span> <span>{{ banklog.ifsc }}</span> </li>
                        <li>
                            <span style="color:#6e523e;">{{ t('账户余额') }} </span>
                            <span style="color:#6e523e;">{{ wallet.balance }}</span>
                        </li>
                        <li><span style="color:#6e523e;">{{ t('Quantity') }}: </span>
                            <span style="font-size: 12px;">Withdrawal Handling Fee({{ tar }}%):
                                {{ dataForm.money * tar / 100 }} RS</span>
                        </li>
                    </ul>
                </div>
                <div style="display: flex;align-items: center;">
                    <van-field class="fieldbox" v-model="dataForm.money" :placeholder="t('请输入金额')"
                        style="background-color:#fff ;">
                        <template #left-icon>
                            <van-image :src="jb" style="width: 1.4rem;height: 1.4rem;margin-left: 0.5rem;" />
                        </template>
                        <template #button>
                            <van-button size="small" type="primary" color="#bf9567" style="border-radius:4px;"
                                @click="onClickAll">ALL</van-button>
                        </template>
                    </van-field>
                </div>
                <div class="withdrawInfo">
                    <div class="notice">
                        <span class="noticeText">PaymentPassword:</span>
                    </div>
                </div>
                <van-field class="fieldbox" v-model="password2" :placeholder="t('请输入提现密码')"
                    :type="showPassword ? 'text' : 'password'"
                    style="height: 1.75rem;font-size: 0.75rem;margin-top: 0.875rem;">
                    <template #right-icon>
                        <van-icon v-if="showPassword" name="eye-o" color="#d6d6d6" @click="showPassword = false"></van-icon>
                        <van-icon v-else name="closed-eye" color="#d6d6d6" @click="showPassword = true"></van-icon>
                    </template>
                </van-field>
                <van-button class="submitBtn" @click="onSubmit">{{ t('提现') }}</van-button>
            </div>

            <div class="withdrawInfo" v-if="false">
                <div style="width: 100%;" class="notice">
                    <van-radio-group style="width: 100%;" v-model="checked">
                        <van-cell-group style="font-size: 1.2rem;">
                            <van-cell v-for="item in ptypeArr" @click="checked = item.id">
                                <template #title>
                                    <div style="text-align: left;" class="van-cell__title">
                                        <i style="width: 19.2px;height: 24px;margin-bottom: -4px;margin-right: 10px;"
                                            class="van-badge__wrapper van-icon van-cell__left-icon">
                                            <img class="" :src="dpimg"> </i>
                                        <span :style="{ 'color': (checked == item.id ? '#ec8655' : '#000') }">{{
                                            item.name
                                        }}</span>
                                    </div>
                                </template>
                                <template #right-icon>
                                    <van-radio :name="item.id" icon-size="16px" />
                                </template>
                            </van-cell>
                        </van-cell-group>
                    </van-radio-group>
                </div>
            </div>

            <div class="withdrawalNotes" style="margin-top: 2rem;" v-if="false">
                <div class="notice">
                    <!-- <img :src="hexagon"> -->
                    <span class="noticeText">Withdrawal Notes</span>
                </div>
                <div class="noticeList">
                    <div class="noticeListItem">
                        <span>You invest a minimum of Rs. 500 to initiate withdrawals.</span>
                    </div>
                </div>
            </div>
            <div class="withdrawalNotes">
                <div class="notice">
                    <!-- <img :src="hexagon"> -->
                    <span class="noticeText">Kind tips:</span>
                </div>
                <div class="noticeList">

                    <div class="noticeListItem">
                        <span>1: Valid members can apply to withdraw money. There is no limit on the number of withdrawals. The minimum withdrawal amount is Rs {{ min }}. </span>
                    </div>
                    <div class="noticeListItem">
                        <span>2: IFSC must be 11 characters, the 5th character is 0. If the bank information is filled in incorrectly, the withdrawal will fail.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>3: Withdrawal fee:{{ tar }} %</span>
                    </div>
                    <div class="noticeListItem">
                        <span>4: Withdrawal time: 1-2 days </span>
                    </div>
                </div>
            </div>
            <!--            <div class="Instructions">
                <p class="tit">Instructions：</p>
                <div class="txtbox">
                    <p>1、充值12356</p>
                    <p>2、充值12356</p>
                </div>
            </div>-->
        </div>
    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from "vue";
import MyNav from "../../components/Nav.vue";
import Service from '../../components/service.vue';
import MyTab from "../../components/Tab.vue";
import MyLoading from "../../components/Loading.vue";
import { goRoute } from "../../global/common";
import { Button, Field, CellGroup, Cell, Checkbox, RadioGroup, Radio, Tag, Picker, Popup, Icon, Image } from "vant";
import dpimg from '../../assets/img/dp.png';
export default defineComponent({
    name: "withdrawal",
    components: {
        MyNav, MyLoading,
        [Button.name]: Button,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [Checkbox.name]: Checkbox,
        [RadioGroup.name]: RadioGroup,
        [Radio.name]: Radio,
        [Tag.name]: Tag,
        [Picker.name]: Picker,
        [Popup.name]: Popup,
        [Icon.name]: Icon,
        [Image.name]: Image,
    }
})
</script>
<script lang="ts" setup>

import { img_banner } from '../../global/assets';
import jb from '../../assets/ico/114.png'
import bird from '../../assets/ico/bird.png'
import qb from '../../assets/ico/113.png'

import { http } from "../../global/network/http";
import { _alert, lang } from "../../global/common";
import { useRouter } from "vue-router";
import md5 from "md5";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

let isRequest = false
const router = useRouter()
const loadtitle = ref("Loading...")
const loadingShow = ref(false);
const showPassword = ref(false)
const showPayOnlinePicker = ref<boolean>(false)
const payOnlineColumns = ref<Array<string>>(['Pay Online D', 'Pay Online D1'])
const payOnlineResult = ref('Pay Online D')

const payOnlineOnConfirm = (value: string) => {
    payOnlineResult.value = value
    showPayOnlinePicker.value = false
};

const showOnlineBankingPicker = ref<boolean>(false)
const onlineBankingColumns = ref<Array<string>>(['Online banking 1', 'Online banking 3'])
const onlineBankingResult = ref('Online banking 3')

const onlineBankingConfirm = (value: string) => {
    onlineBankingResult.value = value
    showOnlineBankingPicker.value = false
};

const withdrawerName = ref<string>('')
const accountNumber = ref<string>('')
const password2 = ref<string>('')

const dataForm = reactive({
    password2: '',
    money: '0'
})

const sys_pset = reactive({
    cash: {

    }
})

const onLink = (to: any) => {
    goRoute(to)
}

// { "code": 1, "msg": "ok", "data": { "wallet": { "id": 6, "waddr": "76871059f3e257d4", "uid": 107902, "cid": 2, "balance": "601880.84", "fz_balance": "0.00", "create_time": 1671869738, "lasttime": "0" }, "banklog": { "type": 1, "uid": 107902, "ifsc": "87898", "upi": null, "province_id": 0, "city_id": 0, "bank_id": "IDPT0001", "bank_name": "Canara Bank", "account": "00009989887867878", "realname": "aaa", "routing": null, "phone": "", "idcard": "", "email": null, "branch_name": null, "create_time": 1672489005, "create_id": 107902, "sort": 1000, "status": 1, "currency_id": null, "protocal": 0, "address": null, "qrcode": null, "remark": null }, "sys_pset": { "pay": { "min": "100", "max": "100000", "kmin": "1000", "kmax": "100000" }, "cash": { "min": "120", "max": "50000", "fee": { "percent": "5", "money": "0", "mode": "1" }, "time": { "from": "00:00:00", "to": "23:59:59", "weekend": "1" } } } } }

const doService = () => {
    console.log('im service')
}

const checked = ref(1)
const wallet = ref({})
const banklog = ref({})
const min = ref(0)
const max = ref(0)
const tar = ref(0)
const ptypeArr = ref([])
const onClickAll = () => {
    dataForm.money = wallet.value.balance * 1
}

const onSubmit = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }

    let ptype: any = {}
    for (let i in ptypeArr.value) {
        if (ptypeArr.value[i].id == checked.value) {
            ptype = ptypeArr.value[i];
            break
        }
    }

    if (dataForm.money < min.value) {
        isRequest = false
        _alert(' Minimum withdrawal amount is ' + min.value)
        return
    }
    if (dataForm.money > max.value) {
        isRequest = false
        _alert(' Maximum withdrawal amount is ' + max.value)
        return
    }

    loadingShow.value = true;
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Finance&a=withdrawAct',
            data: {
                //banklog_id:banklog.value.id,
                money: dataForm.money,
                password2: md5(password2.value)
            }
        }).then((res: any) => {
            loadingShow.value = false;
            if (res.code != 1) {
                isRequest = false
                _alert(res.msg)
                return
            }
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
    var delayTime = Math.floor(Math.random() * 1000);
    // setTimeout((() => {
        http({
            url: 'c=Finance&a=withdraw'
        }).then((res: any) => {
            if (res.code != 1) {
                _alert({
                    type: 'error',
                    message: res.msg,
                    onClose: () => {
                        // router.go(-1)
                    }
                })
                return
            }
            ptypeArr.value = res.data.ptms
            wallet.value = res.data.wallet
            banklog.value = res.data.banklog
            min.value = res.data.sys_pset.cash.min
            max.value = res.data.sys_pset.cash.max
            tar.value = res.data.sys_pset.cash.fee.percent
        })
    // }), delayTime)

})

</script>
<style lang="scss" scoped>
.myNavBar {
    height: 45px !important;
}

.recharge {
    height: 100vh;
    overflow-y: scroll;
    background: #fff;
    color: #000;
    height: 100%;
    position: relative;

    input {
        color: #000;
    }



    .recharge_wrap {
        .balances {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding: 0.6rem 1rem;
            border-radius: 4px;
            background: #bf9567;
            color: #fff;

            img {
                width: 2rem;
                margin-right: 1rem;
            }

            span {
                font-weight: bold;
            }
        }

        .birds {
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            margin: 1.4rem auto 0.2rem;
            width: 80%;
            color: #6e523e;
            font-weight: bold;

            img {
                width: 2rem;
            }
        }

        .fieldbox {
            :deep(.van-field__left-icon) {
                display: flex;
                position: relative;
                right: 1rem;
                margin-right: -0.8rem;
            }
        }
    }

    .allbtn {
        background: transparent;
        border: none;
        color: #4eb848;
        font-weight: bold;
        font-size: 14px;
    }

}

.withdrawInfo {

    .notice {
        display: flex;
        align-items: center;
        justify-content: flex-start;

        .noticeText {
            font-size: 0.875rem;
            color: #6e523e;
            font-weight: bold;
        }

        .dot {
            width: 0.5rem;
            height: 0.5rem;
            background: #000;
            border-radius: 50%;
            margin-right: 0.375rem;
        }

    }

    .payOnline,
    .onlineBanking {
        margin-top: 0.625rem;

        .showPayOnlineBtn,
        .showOnlineBankingBtn {
            height: 1.625rem;
            width: 100%;
            border-radius: 0.3125rem;

            :deep(.van-button__content) {
                justify-content: space-between;

                &::before {
                    display: none;
                }
            }
        }
    }

    .onlineBanking {
        margin-top: 0.875rem;
    }
}

.withdrawalNotes {
    margin-top: 2rem;
    padding-bottom: 1.25rem;

    .notice {
        display: flex;
        align-items: center;

        img {
            width: 0.8rem;
            margin-right: 0.5rem;
        }

        .noticeText {
            font-size: 0.875rem;
            font-weight: bold;
        }

        .dot {
            width: 0.5rem;
            height: 0.5rem;
            background: #000;
            border-radius: 50%;
            margin-right: 0.375rem;
        }
    }

    .noticeList {
        .noticeListItem {
            margin-top: 0.75rem;
            line-height: 20px;
        }
    }
}
</style>