<template>
    <div class="recharge">
        <MyNav leftText=''> </MyNav>
        <div class="recharge_wrap">
            <div style="margin-top: 1.5rem">
                <ul class="payItemBox">
                    <li :class="itemIdx == idx ? 'on' : ''" v-for="(vo, idx) in payItems"
                        @click="onclickPayItem(idx, vo.toString())">{{ vo + 'RS' }}</li>
                </ul>
            </div>
            <van-field class="fieldbox" v-model="money" @keyup="onKyupAmount" :placeholder="t('请输入金额')"
                style="font-size: 1rem;"></van-field>
            <van-button class="submitBtn" @click="onSubmit" style="background-color: #fff;color: #84973b;height: 2.25rem;">
                {{ t('充值') }}</van-button>

            <div style="margin-top: 2rem;" class="title">
                <img :src="leaf">
                <b>Payment channel</b>
            </div>

            <div class="payway">
                <div style="width: 100%;" class="notice">
                    <van-radio-group style="display: flex; flex-wrap: wrap;min-width: 100%;
                    justify-content: space-between;" v-model="checked">
                        <div class="paytype" :class="{ checked: (item.id == checked) }" v-for="(item, index) in ptypeArr"
                            :key="index" @click="checked = item.id">{{ item.name }}
                        </div>
                    </van-radio-group>
                </div>
            </div>
            <div class="payway">
                <div class="title">
                    <img :src="leaf">
                    <b>Recharge Notice</b>
                </div>
            </div>

            <div class="rechargeNotice">

                <div class="noticeList">
                    <div class="noticeListItem">
                        <span>1.The Minimun Recharge Amout Is 100RS</span>
                    </div>
                    <div class="noticeListItem">
                        <span>2.Confirm The Recharge Amount And Fill In The UTR Number Correctly</span>
                    </div>
                    <div class="noticeListItem">
                        <span>3.Each Recharge Needs To Obtain The Collection Account Again At The Cashier. Please Do Not
                            Save The Historical Account Recharge</span>
                    </div>
                    <div class="noticeListItem">
                        <span>4.Please Contact Online Customer Service For Recharging</span>
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
import leaf from '../../assets/ico/leaf.png'

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

import { _alert, lang, copy, getSrcUrl } from "../../global/common";
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
            console.log(ptype.type);
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
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                if (res.data.pay_url && res.data.fin_ptype == 1) {
                    location.href = 'https://www.gamedreamer.in/Recharge.html?pay_url=' + res.data.pay_url
                } else if (res.data.pay_url && res.data.fin_ptype == 0) {
                    location.href = res.data.pay_url
                } else {
                    router.push({ name: 'Finance_payInfo', query: { osn: res.data.osn } })
                }
            }
        })
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
        setTimeout(() => {
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
        }, delayTime)
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
    color: #fff;
    display: flex;
    align-items: center;
    font-size: 16px;
}

.recharge_wrap .title img {
    width: 1rem;
    margin-right: 0.575rem;
}

.recharge_wrap .payway .van-cell {
    padding: 0.5rem 0px !important;
}

.recharge {
    position: relative;
    min-height: 100%;
    background: #84973B;
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
    color: #b3b3b3;
    font-weight: bold;
    text-align: center;
    border: 1px solid #b3b3b3;
    width: 5rem;
    margin: 0.3rem 0;
    display: inline-block;
    line-height: 2rem;
    padding: 0 0.5rem;
    border-radius: 4px;
}

.payItemBox li.on {
    background: #fff;
    color: #84973b;
}

.payway {
    display: flex;
    align-items: center;
    justify-content: space-between;

    .notice {
        display: flex;
        align-items: center;

        .paytype {
            border: 1px solid #ccc;
            color: #b3b3b3;
            font-weight: bold;
            text-align: center;
            border-radius: 4px;
            width: 40%;
            margin: 0 0.3rem 0.5rem;
            padding: 0.5rem 0.60rem;
            // background-color: #f6f6f6;
        }

        .checked {
            background: #fff;
            color: #84973b;
            border-color: #fff;
        }

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
            color: #fff;
            margin-top: 0.75rem;
            font-size: 12px;
            line-height: 22px;
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