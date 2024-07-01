<template>
    <div class="choujiang" style="padding: 0 1rem;">
        <MyNav>
            <template #title><div></div></template>
            <template #left>
                <div @click="callback"> 
                    <van-icon name="arrow-left" size="1.3rem" style="vertical-align: middle;top:0px;color: #84a80f;" class="alter"/>
                    <span class="alter" style="vertical-align: middle;position: relative;left: -2px;color: #84a80f;">Back</span>
                </div>
            </template>
        </MyNav>
        <div :class="['tree', { shake: Shaking }]">
            <img :src="tree">
        </div>
        <div class="number">
            <div class="lotteryclick" @click="startCallback">
                <p>lottery click</p>
                <span>
                    <img :src="number">
                    {{ num }} times left
                </span>
            </div>
            <div class="lotterypoints" @click="wobble" v-if="false">
                <p>ponints lottery </p>
                <span>
                    <img :src="number">
                    {{ 0 }} points
                </span>
            </div>
        </div>
        <div class="article">
            <div class="lotterytitle">Lottery Rules</div>
            <div class="lotteryarticle">
                <p>
                    <img :src="xx" />
                    <span>New members can get 1 chance to draw a lottery by joining and activating the product.</span>
                </p>
                <br />
                <p>
                    <img :src="xx" />
                    Invite new members to join and get 1 chance to draw a lottery.
                </p>
                <br />
                <p>
                    <img :src="xx" />
                    Get 1 chance to draw a lottery for each product purchased.
                </p>
                <br />
                <p>
                    <img :src="xx" />
                    How to use cash coupons: After obtaining cash coupons, the amount will be directly transferred to your account.
                </p>
                <br />
                <p>
                    <img :src="xx" />
                    How to use coupons: After receiving coupons, you can purchase corresponding discounted products and enjoy discounts.
                </p>
                <br />
                <p>
                    <img :src="xx" />
                    How to use invitation coupons: After obtaining invitation coupons, you can get extra cash rewards by inviting new members to join.
                </p>
                <br />
                <p>
                    <img :src="xx" />
                    Note: The number of draws per day will be reset to 0 at 23:59. If you get a chance to draw, please use it immediately.
                </p>
            </div>
        </div>
        <van-popup v-model:show="showLotteryPop">
            <div class="LotteryPop" @click="receiveGift">
                <img :src="imgLotteryPop" />
                <div class="content">{{ tipstr }}</div>
            </div>
        </van-popup>
        <van-popup v-model:show="showLotteryRule">
            <div style="background-color: #fff;color: #000;width: 13rem;height: 14rem;padding: 1rem;border-radius: 10px;">
                <div style="text-align: center;font-size: 1.2rem;font-weight: bold;padding: 0.5rem 0;">Kind tips</div>
                <p style="font-size: 0.75rem;margin: 0.5rem 0;">Rules for the lucky draw:</p>
                <p style="font-size: 0.75rem;margin: 0.5rem 0;">1: Upgrade the product to get a chance to win a lucky draw</p>
                <p style="font-size: 0.75rem;margin: 0.5rem 0;">2: Invite friends to join to get a chance to win a lucky draw</p>
                <p style="font-size: 0.75rem;margin: 0.5rem 0;">The number of lucky draws per day will be reset to 0 at 23:59 p.m.</p>
                <div style="height: 2rem;background-color: #e00302;color: #fff;text-align: center;line-height: 2rem;border-radius: 20px;margin-top: 1.2rem;" @click="receiveGiftRule">Close</div>
            </div>
        </van-popup>
    </div>
</template>
<script lang="ts">
import { defineComponent, ref, onMounted, onBeforeMount } from 'vue'
import MyNav from '../../components/Nav.vue'
import { Grid, GridItem, Tab, Icon, Button, Image, Popup } from 'vant'
import MyNoticeBar from '../../components/NoticeBar.vue'
import { Swipe, SwipeItem, NoticeBar, Tag, Col, Row } from 'vant'
import MyPop from '../../components/Pop.vue'
export default defineComponent({
    components: {
        MyPop,
        MyNoticeBar,
        [Grid.name]: Grid,
        [Tab.name]: Tab,
        [GridItem.name]: GridItem,
        [Icon.name]: Icon,
        [Button.name]: Button,
        [Image.name]: Image,
        [Popup.name]: Popup,
        [Swipe.name]: Swipe,
        [SwipeItem.name]: SwipeItem,
        [NoticeBar.name]: NoticeBar,
        [Col.name]: Col,
        [Row.name]: Row,
    },
})
</script>
<script lang="ts" setup>
import { useStore } from 'vuex'
import { checkLogin, doLogout, isLogin } from '../../global/user'
import { useRoute, useRouter } from 'vue-router'
import { Dialog } from 'vant'
import http from '../../global/network/http'
import { _alert, lang } from '../../global/common'
import xx from '../../assets/img/lottery/xx.png'
import number from '../../assets/img/lottery/number.png'
import tree from '../../assets/img/lottery/tree.png'
import Lotteryback from '../../assets/img/lottery/Lotteryback.png'

const store = useStore()
const route = useRoute()
const router = useRouter()
const pageuser = isLogin()
const num = ref(0)
const tipstr = ref('Thank you')
const Shaking = ref(false);
const imgLotteryPop = ref(Lotteryback)
let limitation = false;

const showLotteryPop = ref<boolean>(false)
const showLotteryRule = ref<boolean>(true)
const tdata = ref({
    lottery: 0,
    notice: {},
    prize_arr: [],
})

const receiveGift = () => {
    showLotteryPop.value = false
}

const receiveGiftRule = () => {
    showLotteryRule.value = false
}

const callback = ()=>{
    router.push({ path: '/' });
}

const startCallback = () => {
    if (limitation) {
        return;
    }
    limitation = true;
    Shaking.value = true;
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Gift&a=turntableAct',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                limitation = false;
                Shaking.value = false;
                return
            }

            num.value = res.data.lottery
            tipstr.value = res.data.giftprizelog.prize_name
            
            setTimeout(() => {
                Shaking.value = false;
                showLotteryPop.value = true
                limitation = false;
            }, 1000)

        })
    }, delayTime)
}

const wobble = () => {
    if (limitation) {
        return;
    }
    limitation = true;

    Shaking.value = true;
    setTimeout(() => {
        Shaking.value = false;
        showLotteryPop.value = true
        limitation = false;
    }, 2000);
}

onBeforeMount(() => {   
    http({
        url: 'c=Gift&a=turntable',
        data: { page: 1 }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        num.value = res.data.user.lottery
        tdata.value = res.data
    })
})

onMounted(() => {

})
</script>

<style scoped>
.choujiang :deep(.van-popup) {
    background: none;
    top: 38%;
}
</style>
<style lang="scss" scoped>
.choujiang {
    background: #84a80f url(../../assets/img/lottery/back3.png)0 0rem;
    background-size: 100% 21.1rem;
    background-repeat: no-repeat;
    // min-height: 875px;

    :deep(.van-nav-bar) {
        background-color: transparent;
    }

    :deep(.van-nav-bar__title) {
        .alter {
            color: #84a80f !important;
        }
    }

    .tree {
        display: flex;
        justify-content: center;
        height: 40rem;
        margin-top: 1.8rem;
        margin-right: 0.5rem;

        img {
            width: 17rem;
            height: 16rem;
        }

    }

    .number {
        position: absolute;
        top: 22.5rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: space-around;
        text-align: center;
        width: 100%;

        div {
            background: no-repeat url(../../assets/img/lottery/back2.png)0 0rem;
            background-size: 100% 100%;
            padding: 0.4rem 1rem;

            p {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 0.4rem;
            }

            span {
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;

                img {
                    width: 0.8rem;
                    margin-right: 0.4rem;

                }
            }
        }
    }

    .article {
        position: absolute;
        top: 27rem;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;

        .lotterytitle {
            height: 1.8rem;
            text-align: center;
            color: #84a80f;
            background-color: #fff;
            width: 50%;
            border-radius: 18px;
            border: 1px solid #84a80f;
            font-size: 0.85rem;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0.8rem auto 1rem;
        }

        .lotteryarticle {
            padding-bottom: 2rem;
            background-color: #84a80f;
            padding: 0 5%;

            p {
                display: flex;
                align-items: flex-start;
                font: 10px/18px '微软雅黑';

                img {
                    width: 0.6rem;
                    margin: 0.25rem 0.25rem 0 0;
                }
            }
        }
    }

}

.LotteryPop {
    width: 18rem;
    height: 18rem;

    img {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
    }

    .content {
        height: 3rem;
        width: 10rem;
        position: absolute;
        top: 6rem;
        left: 50%;
        transform: translateX(-54%);
        text-align: center;
        color: red;
        font-weight: bold;
        font-size: 1.2rem;
        word-break: break-all;
        padding-top: 0.5rem;
    }
}

.shake {
    animation: shake-animation 0.15s infinite alternate;
}

@keyframes shake-animation {
    0% {
        transform: rotate(0.5deg);
    }

    20% {
        transform: rotate(-0.5deg);
    }

    40% {
        transform: rotate(0.5deg);
    }

    60% {
        transform: rotate(-0.5deg);
    }
    80% {
        transform: rotate(0.5deg);
    }
    100% {
        transform: rotate(-0.5deg);
    }
}
</style>
