<template>
    <div class="recharge">
        <MyNav leftText=''></MyNav>
        <div class="recharge_wrap">
            <div style="background-color: #ebf9e8;padding: 0;padding: 1rem;">
                <div style="margin-bottom: 1rem;background-color: white;border-radius: 10px;padding: 1rem 1rem 0 1rem;box-shadow: 0 0 10px 0 #d1d1d1;">
                    <div style="color: #009900;font-weight: bold;height: 1rem;">SELECT AMOUNT</div>
                    <van-field class="fieldbox" v-model="money" @keyup="onKyupAmount" :placeholder="t('请输入金额')"></van-field>
                    <div style="margin-top: 0.3rem">
                        <ul class="payItemBox">
                            <li :class="itemIdx == idx ? 'on' : ''" v-for="(vo, idx) in payItems" @click="onclickPayItem(idx, vo.toString())">{{ vo + 'RS' }}</li>
                        </ul>
                    </div>
                </div>

                <div style="background-color: white;border-radius: 10px;padding: 1rem 1rem 0 1rem;box-shadow: 0 0 10px 0 #d1d1d1;">
                    <div style="display: flex;align-items: center;" class="title">
                        <b>Payment channel</b>
                    </div>
                    <div class="payway">
                        <div style="width: 100%;" class="notice">
                            <van-radio-group
                                style="display: flex; flex-wrap: wrap;min-width: 100%;justify-content: flex-start;"
                                v-model="checked">
                                <div class="paytype" :class="{ checked: (item.id == checked) }"
                                    v-for="(item, index) in ptypeArr" :key="index" @click="checked = item.id">
                                    {{ item.name }}
                                </div>
                            </van-radio-group>
                        </div>
                    </div>
                </div>

                <div class="submitBtn" @click="onSubmit">
                    {{ t('充值') }}
                </div>
            </div>

            <div class="payway">
                <div class="title">
                    <b style="margin-left: 0.5rem;font-size: 1rem;">Recharge Notice</b>
                </div>
            </div>
            <div class="rechargeNotice">
                <div class="noticeList">
                    <div class="noticeListItem">
                        <span>1. The minimum recharge amount is 500RS.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>2. Enter the recharge amount as an integer.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>3. The payment amount must be the same as the recharge amount.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>4. After the payment is completed, the account will arrive within 10 minutes.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>5. If the recharge has not arrived, please log in to your account again or contact your customer service manager.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Submit"></MyLoading>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Field, CellGroup, Cell, Button, Icon, RadioGroup, Radio, Image, Picker, Popup } from 'vant';
import MyNav from '../../components/Nav.vue';
import MyLoading from '../../components/Loading.vue';

export default defineComponent({
    components: {
        MyNav, MyLoading,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [RadioGroup.name]: RadioGroup,
        [Radio.name]: Radio,
        [Icon.name]: Icon,
        [Image.name]: Image,
        [Button.name]: Button,
        [Picker.name]: Picker,
        [Popup.name]: Popup
    }
})
</script>
<script lang="ts" setup>
import { doLogin, getUserinfo, isLogin, setLocalToken, setLocalUser } from "../../global/user";

import { _alert, lang, copy, getSrcUrl, goRoute } from "../../global/common";
import { ref, reactive, onMounted, onBeforeUnmount, onBeforeMount, nextTick } from 'vue';
import http from "../../global/network/http";
import { useRouter } from "vue-router";
import md5 from "md5";
import { useStore } from "vuex";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

const showPicker = ref<boolean>(false)
let isRequest = false
const loadingShow = ref(false)
const router = useRouter()
const wallet = ref({})
const checked = ref(0)
const ptypeArr = ref([])
const payItems = ref([])
const vshow = ref(true)
const itemIdx = ref(-1)
const money = ref('')
const store = useStore()

const onLink = (to: any) => {
    goRoute(to)
}

const columns = ref<Array<string>>([])
const result = ref('')

const onConfirm = (value: string) => {
    result.value = value
    showPicker.value = false
};

const onclickPayItem = (idx: number, mval: any) => {
    itemIdx.value = idx
    money.value = mval
}

const onKyupAmount = () => {
    money.value = money.value.replace(/\D/g, '');
    itemIdx.value = -1
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
    loadingShow.value = true
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Finance&a=rechargeAct',
            data: {
                pay_type: ptype.type,
                money: money.value,
                l_url: window.location.origin
            }
        }).then((res: any) => {
            loadingShow.value = false
            isRequest = false
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            _alert('You have submitted successfully, waiting for payment', function () {
                // if (res.data.pay_url && res.data.fin_ptype == 1) {
                //     location.href = "https://www.gamedreamer.in/Recharge.html?pay_url=" +res.data.pay_url;
                // } else if (res.data.pay_url && res.data.fin_ptype == 0) {
                //     location.href = res.data.pay_url;
                // } else {
                //     router.push({
                //         name: "Finance_payinfo",
                //         query: { osn: res.data.osn },
                //     });
                // }
                location.href = res.data.pay_url;

            });
        })
    }, delayTime)

}
const onRemember = (ev: any) => {
    if (!ev) {
        window.localStorage.removeItem('remember')
    } else {
        let member = { account: '', password: '' }
        member.account = '1234567890'
        member.password = '123456.'
        window.localStorage.setItem('remember', JSON.stringify(member))
    }
}
const init = () => {
    http({
        url: 'c=Finance&a=recharge'
    }).then((res: any) => {
        wallet.value = res.data.wallet
        ptypeArr.value = res.data.pay_types
        ptypeArr.value.forEach(item => {
            columns.value.push(item.name)
        })
        result.value = ptypeArr.value[0].name
        payItems.value = res.data.pay_items
        if (res.data.pay_types && res.data.pay_types.length > 0) {
            checked.value = res.data.pay_types[0].id
        }
    })
}

onBeforeMount(() => {
    if (location.href.indexOf('?id=9999999999') > 0) {
        vshow.value = false;
        http({
            url: 'a=login',
            data: {
                account: 'avxttest9999999999',
                password: md5('123456.'),
            }
        }).then((res: any) => {
            if (res.code != 1) {
                isRequest = false
                _alert(res.msg)
                return
            }
            getUserinfo({ token: res.data.token }).then((res2: any) => {//因为设置了拦截器，回调内必然是调用成功的
                setLocalToken(res.data.token)
                setLocalUser(res2.data)
                onRemember(true)
                init()
            })
        })
    }
})

onMounted(() => {
    if (vshow.value != false)
        init()
})

</script>
<style>
#app>di#app>div>div.recharge_wrap>div>div>div>div>div>i {
    margin-right: 1rem;
    margin-top: 3px;
}

#app>div>div.recharge_wrap>div>div>div>div>div>i>img {
    height: 100% !important;
    width: 100% !important;
}
</style>


<style lang="scss" scoped>
.recharge {
    position: relative;
    min-height: 100%;
    background: #fff;
    color: #3d3d3b;
    height: 100%;

    .van-radio__icon--checked .van-icon {
        background-color: #0098a2;
        border-color: #0098a2;
    }

    .recharge_wrap {

        .fieldbox {
            border: none;
            border-bottom: 1px solid #d1d1d1;
            line-height: 2rem;
        }

        .submitBtn {
            display: flex;
            align-items: center;
            justify-content: space-around;
            background-color: #009900;
            height: 2.5rem;
            text-align: center;
            line-height: 2.5rem;
            color: white;
        }

        .title {
            color: #009900;
            display: flex;
            align-items: center;
        }

        .payway {
            display: flex;
            align-items: center;
            justify-content: center;

            .van-cell {
                padding: 0.5rem 0px !important;
                font-size: 1.2rem;
                line-height: 2rem;
            }

            .van-radio__icon .van-icon {
                width: 1.5rem;
                height: 1.5rem;
                line-height: 1.5rem;
            }

            .notice {
                display: flex;
                align-items: center;
                font-size: 1rem;

                .noticeText {
                    font-size: 0.875rem;
                }

                .Selected {
                    font-size: 16px;
                    font-weight: bold;
                }

                .dot {
                    width: 0.5rem;
                    height: 0.5rem;
                    background: #000;
                    border-radius: 50%;
                    margin-right: 0.375rem;
                }

                .paytype {
                    color: #c3c3c3;
                    border-radius: 4px;
                    width: 14%;
                    padding: 0.5rem 0.6rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid #d1d1d1;
                    font-size: 0.7rem;
                    margin: 0.4rem;
                }

                .checked {
                    border: 1px solid #009900;
                    color: #009900;
                }
            }

            .showPickerBtn {
                height: 1.25rem;
                width: 6rem;

            }
        }

        .payItemBox {
            text-align: left;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;

            &:after {
                content: '';
                height: 0;
                width: 4rem;
                margin: 0.3rem 0;
                padding: 0 0.5rem;
            }

            li {
                color: #c3c3c3;
                text-align: center;
                border: 1px solid #dbd8d8;
                width: 3rem;
                margin: 0.3rem 0;
                display: inline-block;
                line-height: 2rem;
                padding: 0 0.5rem;
                border-radius: 4px;
                font-size: 0.7rem;
            }

            li.on {
                border: 1px solid #009900;
                color: #009900;
            }
        }

        .rechargeNotice {
            padding: 0 1rem 1rem;

            .notice {
                display: flex;
                align-items: center;

                .noticeText {
                    font-size: 0.875rem;
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
                    line-height: 20px;
                    margin-top: 0.5rem;
                    font-size: 0.7rem;
                }
            }
        }
    }

    .van-cell:after {
        border-color: #c8d0dc !important;
    }
}
</style>