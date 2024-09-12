<template>
    <div class="choujiang">
        <MyNav leftText=''> </MyNav>
        <div class="topBox">
            <img :src="lotTitle" class="lotTitle">
            <div class="leftChance">LUCKY DRAW CHANCES: {{ num }}</div>
            <div class="lotbox">
                <LuckyWheel ref="myLucky"
                            width="72vw"
                            height="72vw"
                            style="padding-top: 14.5vw; margin: auto; "
                            :prizes="lotData.prizes"
                            :blocks="blocks"
                            :buttons="buttons"
                            @start="startCallback"
                            @end="endCallback" />
            </div>
        </div>
        <div class="bottomBox">
            <div style="padding-top: 14vh; margin-bottom: 4vh;">
                <img :src="winULeft" style=" width: 8vw;display:inline-block">
                <span style="font-weight: bold; padding: 0 5vw;">LIST OF WINNING USERS</span>
                <img :src="winURight" style=" width: 8vw; display: inline-block">
            </div>
            <div class="lotWinUsers">
                <div style=" height: 100%; overflow: hidden;">
                    <div class="lotWinUsersContent">
                        <div v-for="(item, index) in tdata.notice" :key="index">
                            <div style="color: #622fb5;display:inline-block;float:left">{{item.account}} ········· <span v-if="item.RS!='Thank You'">WIN</span></div><div style="color: #e20000; display: inline-block; text-align: right;">{{item.RS}}</div>
                        </div>
                    </div>
                    <div class="lotWinUsersContent2">
                        <div v-for="(item, index) in tdata.notice" :key="index">
                            <div style="color: #622fb5;display:inline-block;float:left">{{item.account}} ········· <span v-if="item.RS!='Thank You'">WIN</span></div><div style="color: #e20000; display: inline-block; text-align: right;">{{item.RS}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <img :src="winULeft" style=" width: 8vw;display:inline-block">
                <span style="font-weight: bold; padding: 0 5vw;">ACTIVITY RULES</span>
                <img :src="winURight" style=" width: 8vw; display: inline-block">
            </div>
            <div class="tipsContent">
                <div>1. Invite new users to recharge and get 1 lucky draw chance</div>
                <div>2. You can get 1 lucky draw chance when you buy aproduct</div>
                <div>3. How to use the voucher:
                When you get a cash coupon, the amount you get goes directly into your account</div>
                <div>4. How to use the coupon:
                After receiving the coupon, you can purchase thecorresponding discounted product and enjoy the discount</div>
                <div>5. How to use the invitation coupon:
                After obtaining the invitation coupon, invite new members to join and purchase equipment to get extra cash rewards</div>
            </div>
        </div>
    </div>

    <van-popup v-model:show="showLotteryPop" style="border-radius: 12px; background: #fff0;">
        <div class="LotteryPop" @click="receiveGift">
            <img :src="result" v-if="false" />
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
    import lotTitle from '../../assets/img/lottery/lotTitle.png'
    import winURight from '../../assets/img/lottery/winURight.png'
    import winULeft from '../../assets/img/lottery/winULeft.png'
    import leftChance from '../../assets/img/lottery/leftChance.png'
    var vm = null;
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
        data() {
            return {
                blocks: [{ padding: '13px', }],
                buttons: [{
                    radius: '35%',
                    pointer: true,
                    imgs: [{
                        src: lotStart,
                        width: '100%',
                        top: '-130%'
                    }],
                    fonts: [{ text: '', top: '-10px' }]
                }],
            }
        },
        created() {
            vm = this;
        }
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
    import lotCircleBG from '../../assets/img/lottery/lotCircleBG.png'
    import lotStart from '../../assets/img/lottery/lotStart.png'
    import lottery1 from '../../assets/img/lottery/lottery1.png'
    import lotItem1 from '../../assets/img/lottery/lotItem1.png'
    import xx from '../../assets/img/lottery/xx.png'
    import back from '../../assets/img/lottery/back.png'

    const lotData = ref({
        prizes: [
            //{ fonts: [{ text: '0', top: '10%' }] },
        ]
    })
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
        notice: [],
        prize_arr: [],
    })
    const order = ref(0)
    const result = ref('')
    const num = ref(0)
    const tipstr = ref('Thank you')

    const myLucky = ref()
    const endCallback = (prize: any) => {
        console.info(prize);
        if (prize != "Thank You")
            showLotteryPop.value = true;
    }
    const startCallback = (val: any) => {
        // 模拟调用接口异步抽奖
        //myLucky.value.play()
        //setTimeout(() => {
        //    // 假设后端返回的中奖索引是0
        //    let index = 0;
        //    var a = "Get 20RS Cash";
        //    index = tdata.value.prize_arr.indexOf(a);
        //    // 调用stop停止旋转并传递中奖索引
        //    myLucky.value.stop(index)
        //}, 3000)
        //showLotteryPop.value = true
        //return false;
        //const delayTime = Math.floor(Math.random() * 1000);
        //setTimeout(() => {
        http({
            url: 'c=Gift&a=turntableAct',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            myLucky.value.play()
            num.value = res.data.lottery
            val.target.parentNode.parentNode.parentNode.className += " box_rolling ";
            tipstr.value = res.data.giftprizelog.prize_name
            result.value = imgFlag(res.data.giftprizelog.prize_cover)
            let delayTime = Math.floor((Math.random() * 2 + 2) * 1000)
            setTimeout(() => {
                //flipitback();
                let index = 0;
                index = tdata.value.prize_arr.indexOf(res.data.giftprizelog.prize_name);
                // 调用stop停止旋转并传递中奖索引
                myLucky.value.stop(index)
            }, delayTime)
        })
        //}, delayTime)
    }

    const flipitback = () => {
        const rollboxes = document.querySelectorAll('.rollbox');

        rollboxes.forEach(box => {
            box.classList.remove('box_rolling');
        });
    }

    onBeforeMount(() => {
        // if (!pageuser) { 
        //     store.state.backurl = route.path
        //     router.push({ name: 'Login' })
        //     // console.log(pageuser, 'pageuser的状态');
        //     return
        // }

        http({
            url: 'a=notice',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            tdata.value.notice = res.data.notice
        })

        http({
            url: 'c=Gift&a=turntable',
            data: { page: 1 }
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            for (var i = 0; i < res.data.prize_arr.length; i++) {
                var color = "#ffdf60"
                if (i % 2 == 0)
                    color = "#fffffd"
                lotData.value.prizes.push({
                    imgs: [{ src: getSrcUrl(res.data.prize_arr[i].cover, 1), width: '2.5rem', top: '10%' }],
                    background: color
                });
                tdata.value.prize_arr.push(res.data.prize_arr[i].name)
            }
            num.value = res.data.user.lottery
            //tdata.value = res.data
        })
    })

    onMounted(() => {
        // if (!pageuser) {
        //     store.state.backurl = route.path
        //     router.push({ name: 'Login' })
        //     console.log(pageuser, 'pageuser的状态');
        //     return
        // }
    })
</script>

<style scoped>
    .choujiang :deep(.van-popup) {
    }
</style>
<style lang="scss" scoped>
    @keyframes myMove {
        0% {
            transform: translateY(0px);
        }

        100% {
            transform: translateY(-100%);
        }
    }

    .lotbox {
        background-image: url(../../assets/img/lottery/lotCircleBG.png);
        background-repeat: no-repeat;
        background-size: 100% 100%;
        width: 100vw;
        height: 114vw;
        margin: auto;
        position: absolute;
    }

    .topBox {
        background-image: url(../../assets/img/lottery/lotBG.png);
        background-repeat: no-repeat;
        background-size: 100% 100%;
        padding-top: 2vh;
        height: 128vw;

        .lotTitle {
            width: 90%;
            margin: auto;
        }

        .leftChance {
            width: 55%;
            margin: auto;
            background-image: url(/src/assets/img/lottery/leftChance.png);
            background-repeat: no-repeat;
            background-size: 100% 100%;
            font-size: .8rem;
            height: 5vh;
            text-align: center;
            line-height: 5vh;
            margin-top: 2vh;
            font-weight: bold;
        }
    }

    .bottomBox {
        background-image: url(../../assets/img/lottery/lotBottomBG.png);
        background-repeat: no-repeat;
        background-size: 100% 100%;
        text-align: center;

        .lotWinUsers {
            background-image: url(../../assets/img/lottery/lotWinUsersBG.png);
            background-repeat: no-repeat;
            background-size: 100% 100%;
            margin: auto;
            width: 80%;
            font-weight: bold;
            height: 80vw;
            padding: 4vw 3vw;
            white-space: nowrap;
            overflow: hidden;
            margin-bottom: 4vh;
            font-size: .9rem;

            .lotWinUsersContent {
                height: 100%;
                overflow: hidden;
                padding: 0 2vw;
                animation-name: myMove;
                animation-duration: 10s;
                animation-delay: 0s;
                animation-iteration-count: infinite;
                animation-timing-function: linear;
                animation-fill-mode: both;
                animation-direction: normal;
            }
            .lotWinUsersContent2 {
                height: 100%;
                overflow: hidden;
                padding: 0 2vw;
                animation-name: myMove;
                animation-duration: 10s;
                animation-delay: 0s;
                animation-iteration-count: infinite;
                animation-timing-function: linear;
                animation-fill-mode: both;
                animation-direction: normal;
            }
        }

        .tipsContent {
            text-align: left;
            padding: 2vh 6vw;

            div {
                margin-bottom: 1vh;
                margin-bottom: 2vh;
            }
        }
    }

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
                    > img {
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
            height: 8rem;
            margin-top: 1rem;
            font-size: 0.70rem;
            color: #64523e;

            p {
                display: flex;
                align-items: flex-start;
                font: 12px/20px "微软雅黑";

                img {
                    width: 0.5rem;
                    margin-top: 0.2rem;
                    margin-right: 0.2rem;
                }
            }
        }
    }

    .LotteryPop {
        width: 20rem;
        height: 21rem;
        border-radius: 8px;
        background-image: url(../../assets/img/lottery/winNotify.png);
        background-repeat: no-repeat;
        background-size: 100% 100%;

        img {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .content {
            height: 3rem;
            width: 12rem;
            position: absolute;
            top: 11.6rem;
            left: 50%;
            transform: translateX(-54%);
            text-align: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            word-break: break-all;
            padding-top: 0.5rem;
            overflow: scroll;
            line-height: 3rem;
        }
    }
</style>
