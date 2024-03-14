<template>
    <div class="choujiang" style="padding: 0 1rem;">
        <MyNav leftText=''> </MyNav>
        <div class="top"> LUCKY DRAW</div>
        <div class="lotteryNum">There are {{ num }} draws left</div>
        <div
            style="height: 22rem; margin-top: 1rem; display: flex; flex-direction: row; flex-wrap: wrap; justify-content: space-around; align-content: space-around;">

            <div class="rollbox" @click="FlippingOver($event)">

                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>

                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="result" />
                    </div>
                </div>
            </div>

            <div class="rollbox" @click="FlippingOver($event)">

                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>

                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="result" />
                    </div>
                </div>
            </div>

            <div class="rollbox" @click="FlippingOver($event)">

                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>

                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="result" />
                    </div>
                </div>
            </div>

            <div class="rollbox" @click="FlippingOver($event)">

                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>

                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="result" />
                    </div>
                </div>
            </div>

            <div class="rollbox" @click="FlippingOver($event)">

                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>

                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="result" />
                    </div>
                </div>
            </div>

            <div class="rollbox" @click="FlippingOver($event)">

                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>

                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="result" />
                    </div>
                </div>
            </div>
        </div>
        <div class="lotteryNum" style=" width: 43%; margin-left: 28%; margin-top: 1rem;">Activity Rules</div>
        <div class="introduce">
            <p>
                <img :src="xx" />
                <span>Invite new users to recharge and get 1 lucky fraw chance</span>
            </p>
            <br />
            <p>
                <img :src="xx" />
                You can get 1 lucky draw chance when you buy a product
            </p>
            <br />
            <p>
                <img :src="xx" />
                How to use the voucher:<br />

                When you get a cash coupon,the amount you get gose directly into your accout

            </p>
            <br />
            <p>
                <img :src="xx" />
                How to use the coupon:<br />

                After receiving the coupon,you can purchase the corresponding discounted product and enjoy the discount
            </p>
            <br />
            <p>
                <img :src="xx" />
                How to use the invitation coupon:<br />

                After obtaining the invitation coupon,invite new members to join and purchase
                equipment to get extra cash rewards
            </p>
        </div>
        <div style="height:15rem;">
            <img :src="back" />
        </div>
    </div>
    <van-popup v-model:show="showLotteryPop" style="border-radius: 12px;">
        <div class="LotteryPop" @click="receiveGift">
            <img :src="result" />
            <div class="content">{{ tipstr }}</div>
        </div>
    </van-popup>
</template>
<script lang="ts">
import { defineComponent, ref, onMounted, onBeforeMount } from 'vue'
import { getSrcUrl } from '../../global/common'
import MyNav from '../../components/Nav.vue'
import { Grid, GridItem, Tab, Icon, Button, Image, Popup } from 'vant'
import MyTab from '../../components/Tab.vue'
import MyNoticeBar from '../../components/NoticeBar.vue'
import { Swipe, SwipeItem, NoticeBar, Tag, Col, Row } from 'vant'
import MyPop from '../../components/Pop.vue'
export default defineComponent({
    components: {
        MyPop,
        MyTab,
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

import open from '../../assets/img/lottery/open.png'
import lottery1 from '../../assets/img/lottery/lottery1.png'
import xx from '../../assets/img/lottery/xx.png'
import back from '../../assets/img/lottery/back.png'

const store = useStore()
const route = useRoute()
const router = useRouter()
const pageuser = isLogin()
const imgFlag = (src: string) => {
    return getSrcUrl(src, 1);
}

const FlippingOver = (val: any) => {

    if (val.target.parentNode.parentNode.parentNode.classList.contains('box_rolling')) {
        return;
    }
    startCallback(val)
}

const showLotteryPop = ref<boolean>(false)

const receiveGift = () => {
    flipitback();
    showLotteryPop.value = false

}

const tdata = ref({
    lottery: 0,
    notice: {},
    prize_arr: [],
})
const order = ref(0)
const result = ref('')
const num = ref(0)
const tipstr = ref('Thank you')

const startCallback = (val: any) => {

    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Gift&a=turntableAct',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                return
            }

            num.value = res.data.lottery
            val.target.parentNode.parentNode.parentNode.className += " box_rolling ";
            tipstr.value = res.data.giftprizelog.prize_name
            result.value = imgFlag(res.data.giftprizelog.prize_cover)
            console.log(result.value, '中奖图片');

            showLotteryPop.value = true

            setTimeout(() => {
                flipitback();
            }, 1000)
        })
    }, delayTime)
}

const flipitback = () => {
    const rollboxes = document.querySelectorAll('.rollbox');

    rollboxes.forEach(box => {
        box.classList.remove('box_rolling');
    });
}

onBeforeMount(() => {
    if (!pageuser) {
        store.state.backurl = route.path
        router.push({ name: 'Login' })
        return
    }
    setTimeout(() => {
        http({
            url: 'a=notice',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            tdata.value.notice = res.data.notice
        })
    }, 1000)

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
        // buttons.value = [
        //     {
        //         // x: 1, y: 1,
        //         // background: "transparent",
        //         // fonts: [{ text: "Remaining \n" + num.value + " times", top: "20%", fontColor: '#fcfcfccf', fontSize: '14px', lineHeight: '26px', wordWrap: false, }],
        //     },
        // ]
        // prizes.value =
        //     [
        //         // {
        //         //     x: 0, y: 0,
        //         //     imgs: [
        //         //         {
        //         //             src: xx,//左上
        //         //             width: "90%",
        //         //             top: "1%",
        //         //             left: '0%'
        //         //         }
        //         //     ]
        //         // },
        //         // {
        //         //     x: 1, y: 0,
        //         //     imgs: [
        //         //         {
        //         //             src: imgFlag(tdata.value.prize_arr[2].cover),//中上
        //         //             width: "90%",
        //         //             top: "1%",
        //         //             left: '0%'
        //         //         }
        //         //     ]
        //         // },
        //         // {
        //         //     x: 2, y: 0,
        //         //     imgs: [
        //         //         {
        //         //             src: imgFlag(tdata.value.prize_arr[3].cover),//右上
        //         //             width: "90%",
        //         //             top: "1%",
        //         //             left: '0%'
        //         //         }
        //         //     ]
        //         // },
        //         // {
        //         //     x: 2, y: 1,
        //         //     imgs: [
        //         //         {
        //         //             src: imgFlag(tdata.value.prize_arr[4].cover),//右中
        //         //             width: "90%",
        //         //             top: "1%",
        //         //             left: '0%'
        //         //         }
        //         //     ]
        //         // },
        //         // {//5
        //         //     x: 2, y: 2,
        //         //     imgs: [
        //         //         {
        //         //             src: imgFlag(tdata.value.prize_arr[5].cover), //右下
        //         //             width: "90%",
        //         //             top: "1%",
        //         //             left: '0%'
        //         //         }
        //         //     ]
        //         // },
        //         // {
        //         //     x: 1, y: 2,
        //         //     imgs: [
        //         //         {
        //         //             src: imgFlag(tdata.value.prize_arr[6].cover),//中下
        //         //             width: "90%",
        //         //             top: "1%",
        //         //             left: '0%'
        //         //         }
        //         //     ]
        //         // },
        //         // {
        //         //     x: 0, y: 2,
        //         //     imgs: [
        //         //         {
        //         //             src: imgFlag(tdata.value.prize_arr[7].cover),//中下
        //         //             width: "90%",
        //         //             top: "1%",
        //         //             left: '0%'
        //         //         }
        //         //     ]
        //         // },
        //         // {
        //         //     x: 0, y: 1,
        //         //     imgs: [
        //         //         {
        //         //             src: imgFlag(tdata.value.prize_arr[8].cover),//中下
        //         //             width: "90%",
        //         //             top: "1%",
        //         //             left: '0%'
        //         //         }
        //         //     ]
        //         // },
        //     ]
        // myLucky.value.init()
        // setTimeout(() => {
        //     myLucky.value.init()
        // }, 2000)
    })
})

onMounted(() => {
    if (!pageuser) {
        store.state.backurl = route.path
        router.push({ name: 'Login' })
        return
    }
})
</script>

<style scoped>
.choujiang :deep(.van-popup) {
    background: none;
}
</style>
<style lang="scss" scoped>
.choujiang {
    .top {
        height: 3rem;
        text-align: center;
        font-size: 2.2rem;
        line-height: 3rem;
        font-style: italic;
        color: #64523e;
        font-weight: bold;
        margin: 1rem 0 0.5rem 0;
    }

    .lotteryNum {
        height: 1.5rem;
        text-align: center;
        color: #fff;
        background-color: #64523e;
        line-height: 1.5rem;
        width: 68%;
        margin-left: 16%;
        border-radius: 10px;
        font-size: 0.85rem;
    }

    .rollbox {
        position: relative;
        perspective: 1000px;
        width: 31%;
        height: 10rem;

        &_front,
        &_behind {
            transform-style: preserve-3d;
            backface-visibility: hidden;
            transition-duration: .5s;
            transition-timing-function: 'ease-in';
            background: #008080;

            .contentbox {
                >img {
                    height: 10rem;
                }
            }
        }

        &_behind {
            transform: rotateY(180deg);
            visibility: hidden;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            left: 0;
        }
    }

    .box_rolling {
        .rollbox_front {
            transform: rotateY(180deg);
            visibility: hidden;
        }

        .rollbox_behind {
            transform: rotateY(360deg);
            visibility: visible;
        }
    }

    .introduce {
        height: 18rem;
        margin-top: 0.5rem;
        font-size: 0.70rem;
        color: #64523e;

        p {
            display: flex;
            align-items: flex-start;
            font: 12px/14px "微软雅黑";

            img {
                width: 0.5rem;
                margin-top: 0.2rem;
                margin-right: 0.2rem;
            }
        }
    }
}

.LotteryPop {
    width: 18rem;
    height: 27rem;
    border-radius: 8px;

    img {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
    }

    .content {
        height: 3rem;
        width: 15rem;
        position: absolute;
        top: 6rem;
        left: 50%;
        transform: translateX(-54%);
        text-align: center;
        color: #64523e;
        font-weight: bold;
        font-size: 1.2rem;
        word-break: break-all;
        padding-top: 0.5rem;
    }
}
</style>
