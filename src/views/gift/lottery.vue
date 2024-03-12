<template>
    <div class="choujiang" style="padding: 0 1rem;">
        <MyNav>
            <template #left>
                <div></div>
            </template>
        </MyNav>
        <LuckyGrid ref="myLucky" width="18.5rem" height="19.5rem" :prizes="prizes" :buttons="buttons" :blocks="blocks"
            :default-config="defaultConfig" class="myLucky" />

        <div class="number">
            <div class="lotteryclick" @click="startCallback">
                <p>lottery click</p>
                <span>
                    <img :src="number">
                    {{ num }} times left
                </span>
            </div>
            <div class="lotterypoints">
                <p>ponints lottery </p>
                <span>
                    <img :src="number">
                    {{ 0 }} points
                </span>
            </div>
        </div>
        <div class="article">
            <div class="lotterytitle">Activity Rules</div>
            <div class="lotteryarticle">
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
                    After obtaining the invitation coupon,invite new members to join and purchase to get extra cash rewards
                </p>
            </div>


        </div>
        <van-popup v-model:show="showLotteryPop">
            <div class="LotteryPop" @click="receiveGift">
                <img :src="imgLotteryPop"
                    style="width: 55%; position: absolute; top: 10%; left: 50%; transform: translateX(-50%);" />
            </div>
        </van-popup>
    </div>
    <MyTab></MyTab>
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
import test from '../../assets/img/lottery/test.gif'
import number from '../../assets/img/lottery/number.png'

const store = useStore()
const route = useRoute()
const router = useRouter()
const pageuser = isLogin()
const imgFlag = (src: string) => {
    return getSrcUrl(src, 1);
}
const showLotteryPop = ref<boolean>(false)
const tdata = ref({
    lottery: 0,
    notice: {},
    prize_arr: [],
})
const imgLotteryPop = ref(number)
const receiveGift = () => {
    showLotteryPop.value = false
    myLucky.value.init()
}
const myLucky = ref()
const imgArr = ref();
const num = ref(0)
const redpackArr = ref([])
const actItem = ref({})
const showAgain = ref(false)
const buttons = ref<Array<any>>()
const prizes = ref<Array<any>>([])
const defaultConfig = ref({
    accelerationTime: 2000,
    decelerationTime: 1500,
})
const blocks = ref<Array<any>>([
    {
        imgs: [
            {
                src: '00',
                width: '100%',
                height: '100%',
                rotate: true
            },
        ],
    },
    { padding: '1px' },
    { padding: '10px' },
])

const tipstr = ref('Thank you')
const startCallback = () => {
    myLucky.value.play()
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Gift&a=turntableAct',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                myLucky.value.init()
                return
            }
            num.value = res.data.lottery
            setTimeout(() => {
                for (let index = 0; index < tdata.value.prize_arr.length; index++) {
                    const pzitem = tdata.value.prize_arr[index];
                    if (pzitem.id == res.data.id) {
                        myLucky.value.stop(index)
                        console.log(index);

                    }
                }
                setTimeout(() => {
                    buttons.value = [
                        {
                            x: 1, y: 1,
                            background: "transparent",
                            fonts: [{ text: "Remaining \n" + num.value + " times", top: "20%", fontColor: '#fcfcfccf', fontSize: '14px', lineHeight: '26px', wordWrap: false, }],
                        },
                    ]
                    for (let index = 0; index < tdata.value.prize_arr.length; index++) {
                        const pzitem = tdata.value.prize_arr[index];
                        if (pzitem.id == res.data.id) {
                            tipstr.value = pzitem.name
                            imgLotteryPop.value = imgFlag(pzitem.cover)
                        }
                    }
                    showLotteryPop.value = true

                }, 2000)
            }, 1200)
        })
    }, delayTime)
}
const endCallback = () => {
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
        buttons.value = [
            {
                // x: 1, y: 1,
                // background: "transparent",
                // fonts: [{ text: "Remaining \n" + num.value + " times", top: "20%", fontColor: '#fcfcfccf', fontSize: '14px', lineHeight: '26px', wordWrap: false, }],
            },
        ]
        prizes.value =
            [
                // {
                //     x: 0, y: 0,
                //     imgs: [
                //         {
                //             src: xx,//左上
                //             width: "90%",
                //             top: "1%",
                //             left: '0%'
                //         }
                //     ]
                // },
                // {
                //     x: 1, y: 0,
                //     imgs: [
                //         {
                //             src: imgFlag(tdata.value.prize_arr[2].cover),//中上
                //             width: "90%",
                //             top: "1%",
                //             left: '0%'
                //         }
                //     ]
                // },
                // {
                //     x: 2, y: 0,
                //     imgs: [
                //         {
                //             src: imgFlag(tdata.value.prize_arr[3].cover),//右上
                //             width: "90%",
                //             top: "1%",
                //             left: '0%'
                //         }
                //     ]
                // },
                // {
                //     x: 2, y: 1,
                //     imgs: [
                //         {
                //             src: imgFlag(tdata.value.prize_arr[4].cover),//右中
                //             width: "90%",
                //             top: "1%",
                //             left: '0%'
                //         }
                //     ]
                // },
                // {//5
                //     x: 2, y: 2,
                //     imgs: [
                //         {
                //             src: imgFlag(tdata.value.prize_arr[5].cover), //右下
                //             width: "90%",
                //             top: "1%",
                //             left: '0%'
                //         }
                //     ]
                // },
                // {
                //     x: 1, y: 2,
                //     imgs: [
                //         {
                //             src: imgFlag(tdata.value.prize_arr[6].cover),//中下
                //             width: "90%",
                //             top: "1%",
                //             left: '0%'
                //         }
                //     ]
                // },
                // {
                //     x: 0, y: 2,
                //     imgs: [
                //         {
                //             src: imgFlag(tdata.value.prize_arr[7].cover),//中下
                //             width: "90%",
                //             top: "1%",
                //             left: '0%'
                //         }
                //     ]
                // },
                // {
                //     x: 0, y: 1,
                //     imgs: [
                //         {
                //             src: imgFlag(tdata.value.prize_arr[8].cover),//中下
                //             width: "90%",
                //             top: "1%",
                //             left: '0%'
                //         }
                //     ]
                // },
            ]
        myLucky.value.init()
        setTimeout(() => {
            myLucky.value.init()
        }, 2000)
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
    background: #84a80f url(../../assets/img/lottery/back.png)0 0rem;
    background-size: 100% 48rem;
    background-repeat: no-repeat;
    min-height: 875px;

    :deep(.van-nav-bar) {
        background-color: transparent;
    }

    :deep(.van-nav-bar__title) {
        .alter {
            color: #84a80f !important;
        }
    }

    .number {
        position: absolute;
        top: 23rem;
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
        width: 90%;

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
</style>
