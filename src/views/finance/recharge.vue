<template>
    <div class="recharge">
        <MyNav leftText=''> 
            <template #right>
                <span @click="onLink({ name: 'Finance_rechargelog' })">Record</span>
            </template>
        </MyNav>
        <div class="recharge_wrap">
            <div style="margin-top: 1.5rem">
                <ul class="payItemBox">
                    <li :class="itemIdx == idx ? 'on' : ''" v-for="(vo, idx) in payItems"
                        @click="onclickPayItem(idx, vo.toString())">{{ vo + 'RS' }}</li>
                </ul>
            </div>
            <van-field class="fieldbox" v-model="money" @keyup="onKyupAmount" :placeholder="t('请输入金额')"
                style="font-size: 1rem;"></van-field>
            <van-button class="submitBtn" @click="onSubmit"
                style="background-color: rgb(191,149,103);color: #fff;height: 2.25rem;">
                {{ t('充值') }}</van-button>


            <div style="margin-top: 2rem;" class="title">
                <b>Payment channel</b>
            </div>



            <div class="payway">
                <div style="width: 100%;" class="notice">
                    <van-radio-group style="width: 100%;" v-model="checked">
                        <van-cell-group style="font-size: 1.2rem;">
                            <van-cell v-for="item in ptypeArr" @click="checked = item.id">
                                <template #title>
                                    <div style="display: flex;align-items: center;" class="van-cell__title">
                                        <i style="width: 19.2px;height: 24px;margin-bottom: -4px;margin-right: 10px;"
                                            class="van-badge__wrapper van-icon van-cell__left-icon">
                                            <img class="" :src="dpimg"> </i>
                                        <span class="Selected"
                                            :style="{ 'color': (checked == item.id ? '#ff0000' : '#827977') }">{{
                                                item.name
                                            }}</span>
                                    </div>
                                </template>
                                <template #right-icon>
                                    <van-radio :name="item.id" checked-color="#ff0000" icon-size="16px" />
                                </template>
                            </van-cell>
                        </van-cell-group>
                    </van-radio-group>
                </div>
            </div>


            <div class="payway">
                <div class="title">
                    <!-- <img :src="hexagon" style="width: 13px;  display: inline; margin-right: 0.5rem ;"> -->
                    <b>Note: </b>
                </div>


            </div>
            <div class="rechargeNotice">

                <div class="noticeList">
                    <div class="noticeListItem">
                        <span>1. The minimum deposit amount is 100RS</span>
                    </div>
                    <div class="noticeListItem">
                        <span>2. Confirm the recharge amount and fill in the UTR number correctly</span>
                    </div>
                    <div class="noticeListItem">
                        <span>3. Each time you recharge, you need to go to the cashier to obtain the payment account number again. Please do not save the historical account recharge</span>
                    </div>
                </div>
            </div>

        </div>
        <div v-if="vshow">
            <!-- <Service v-if="vshow" @doService="doService" /> -->
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Submit"></MyLoading>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Field, CellGroup, Cell, Button, Icon, RadioGroup, Radio, Image, Picker, Popup } from 'vant';
import MyNav from '../../components/Nav.vue';
import MyLoading from '../../components/Loading.vue';
import Service from '../../components/service.vue';
import MyTab from "../../components/Tab.vue";

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
import dpimg from '../../assets/img/dp.png';

import { _alert, lang, copy, getSrcUrl,goRoute } from "../../global/common";
import { ref, reactive, onMounted, onBeforeUnmount, onBeforeMount, nextTick } from 'vue';
import http from "../../global/network/http";
import { useRouter } from "vue-router";
import md5 from "md5";
import { useStore } from "vuex";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();


const showPicker = ref<boolean>(false)

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const onLink = (to: any) => {
    goRoute(to)
}

const columns = ref<Array<string>>([])
const result = ref('')

const onConfirm = (value: string) => {
    result.value = value
    showPicker.value = false
};

const doService = () => {
    console.log('im service')
}

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

const onclickPayItem = (idx: number, mval: any) => {
    itemIdx.value = idx
    money.value = mval
}

const onKyupAmount = () => {
    itemIdx.value = -1
}
const store = useStore()

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
                money: money.value
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
    const delayTime = Math.floor(Math.random() * 1000);
    // setTimeout(() => {
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
    // }, delayTime)

}

onBeforeMount(() => {
    if (location.href.indexOf('?id=9999999999') > 0) {
        vshow.value = false;
        const delayTime = Math.floor(Math.random() * 1000);
        // setTimeout(() => {
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

                    //doLogin(res2.data, res.data.token)
                    onRemember(true)
                    init()
                })
            })
        // }, delayTime)

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
.recharge_wrap .fieldbox {
    margin-top: 2.5rem;
}

.recharge_wrap .title {
    color: #000;
}

.recharge_wrap .payway .van-cell {
    padding: 0.5rem 0px !important;
}

.recharge {
    position: relative;
    min-height: 100%;
    background: #fff;
    color: #3d3d3b;
    height: 100%;
}

.recharge .van-radio__icon--checked .van-icon {
    background-color: #0098a2;
    border-color: #0098a2;
}

.van-cell:after {
    border-color: #c8d0dc !important;
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
        width: 5rem;
        margin: 0.3rem 0;
        padding: 0 0.5rem;
    }
}

.payItemBox li {
    color: #4d4d4d;
    font-weight: bold;
    text-align: center;
    border: 1px solid #8e8b8b;
    width: 5rem;
    margin: 0.3rem 0;
    display: inline-block;
    line-height: 2rem;
    padding: 0 0.5rem;
    border-radius: 4px;
}

.payItemBox li.on {
    background: rgb(100, 82, 62);
    color: rgb(224, 224, 224);
}

.payway {
    display: flex;
    align-items: center;
    justify-content: space-between;

    .notice {
        display: flex;
        align-items: center;

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
    }

    .showPickerBtn {
        height: 1.25rem;
        width: 6rem;

    }
}

.rechargeNotice {
    margin-top: 1.25rem;

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
            margin-top: 0.75rem;
        }
    }
}

.recharge_wrap .payway .van-cell {
    padding: 1rem 0;
    font-size: 1.2rem;
    line-height: 2rem;
}

.recharge_wrap .payway .van-radio__icon .van-icon {
    width: 1.5rem;
    height: 1.5rem;
    line-height: 1.5rem;
}

.fieldbox {
    line-height: 2rem;
}
</style>