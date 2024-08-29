<template>
    <div class="recharge">
        <MyNav leftText=''>
            <!-- <template #right>
                <span @click="onLink({ name: 'Finance_withdrawlog' })">Record</span>
            </template> -->
        </MyNav>
        <div class="recharge_wrap">
            <div style="height: 2rem;background-color: #ca0e00;border-radius: 20px;display: flex;color: white;align-items: center;justify-content: space-between;padding: 0 1rem;font-weight: bold;margin-top: 1rem;">
                <div>Current Balance</div>
                <div>₹ {{wallet.balance}}</div>
            </div>
            <van-field class="fieldbox_money" v-model="dataForm.money" :placeholder="t('请输入金额')" style="font-size: 1rem;"></van-field>
            <div class="payinfo" style="margin-top: 1rem;">
                <ul>
                    <li>
                        <div style="width: 5rem;">{{ t('银行名称') }}</div> 
                        <div style="width: 100%;text-align: right;">{{ banklog.bank_name }}</div>
                     </li>
                    <li>
                        <div style="width: 5.5rem;">{{ t('真实姓名') }}</div>
                        <div style="width: 100%;text-align: right;">{{ banklog.realname }}</div>
                    </li>
                    <li>
                        <div style="width: 6rem;">{{ t('银行账号') }}</div>
                        <div style="width: 100%;text-align: right;">{{ banklog.account }}</div>
                    </li>
                    <li>
                        <div style="width: 2.2rem;">{{ t('IFSC ') }}</div>
                        <div style="width: 100%;text-align: right;">{{ banklog.ifsc }}</div>
                    </li>
                    <!-- <li>
                        <div style="color:#ca0e00;">{{ t('Quantity') }}:</div>
                        <div style="font-size: 12px;">Withdrawal Handling Fee({{ tar }}%): {{ (dataForm.money * tar / 100).toFixed(2) }} RS</div>
                    </li> -->
                </ul>
            </div>
            <van-field class="fieldbox" v-model="password2" :placeholder="t('请输入提现密码')" :type="showPassword ? 'text' : 'password'"
                style="height: 1.75rem;font-size: 0.75rem;margin-top: 0.875rem;">
                <template #right-icon>
                    <van-icon v-if="showPassword" name="eye-o" color="#d6d6d6" @click="showPassword = false"></van-icon>
                    <van-icon v-else name="closed-eye" color="#d6d6d6" @click="showPassword = true"></van-icon>
                </template>
            </van-field>

            <div class="submitBtn" @click="onSubmit">
                <div class="onbtn">
                    {{ t('提现') }}
                </div>
            </div>

            <div class="withdrawalNotes">
                <div class="notice">
                    <span class="noticeText">Kind tips:</span>
                </div>
                <div class="noticeList">
                    <div class="noticeListItem">
                        <span>1: Minimum withdrawal amount is Rs {{ min }}. </span>
                    </div>
                    <div class="noticeListItem">
                        <span>2: You can withdraw once a day.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>3: Withdrawal will reach your account within 24-72.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>4: Withdrawal tax {{ tar }}%.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>5: If the withdrawal fails, please reapply or check whether your bank account information is correct.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>5: IFSC should be 11 characters, and the 5th character should be "0", not "O". If you fill in incorrect bank information,your withdrawal will fail.</span>
                    </div>
                </div>
            </div>
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

    if (dataForm.money-0 < min.value-0) {
        isRequest = false
        _alert(' Minimum withdrawal amount is ' + min.value)
        return
    }
    
    if (dataForm.money-0 > max.value-0) {
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
            _alert(res.msg, function () {
                location.reload()
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
            _alert(res.msg, function () {
                router.go(-1)
            })
            return
        }
        console.log(res.data.sys_pset.cash.max);
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

        .fieldbox_money{
            margin-top: 1rem;
            border: none;
            border-bottom: 1px solid #ca0e00;
            background-color: #d9d9d9;
            border-radius: 10px 10px 0 0 ;
            line-height: 1rem;

            ::-webkit-input-placeholder { /* WebKit, Blink, Edge */
                color: white;
                text-align: center;
                font-size: 0.9rem;
            }
            :-moz-placeholder { /* Mozilla Firefox 4 to 18 */
                color: white;
                opacity:  1;
                text-align: center;
                font-size: 0.9rem;
            }
            ::-moz-placeholder { /* Mozilla Firefox 19+ */
                color: white;
                opacity:  1;
                text-align: center;
                font-size: 0.9rem;
            }
            :-ms-input-placeholder { /* Internet Explorer 10-11 */
                color: white;
                text-align: center;
                font-size: 0.9rem;
            }
            ::-ms-input-placeholder { /* Microsoft Edge */
                color: white;
                text-align: center;
                font-size: 0.9rem;
            }
            ::placeholder { /* Most modern browsers support this now. */
                color: white;
                text-align: center;
                font-size: 0.9rem;
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

        .payinfo li div:nth-child(2){
            border-bottom: 1px solid #c3c3c3;
        }

        .submitBtn {
            display: flex;
            align-items: center;
            justify-content: space-around;

            .onbtn{
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
                    margin-top: 0.5rem;
                    line-height: 20px;
                    font-size: 0.75rem;
                }
            }
        }
    }
}
</style>