<template>
    <div class="choujiang">
        <MyNav leftText=''>
            <template #left>
                <div></div>
            </template>
        </MyNav>
        <div class="cj_center">
            <div class="title">
                Number of draws remaining: {{ num }}
            </div>
            <div class="cj_bg">
                <LuckyGrid ref="myLucky" width="300px" height="300px" :prizes="prizes" :blocks="blocks" :buttons="buttons" @start="startCallback" @end="endCallback" />
            </div>

            <div class="lotteryNum">Activity Rules </div>
            <div class="introduce">
                <p>New members can get 1 chance to win a lottery by joining and activating the product.</p>
                <p>Invite new members to join and get 1 chance to win a lottery. Get 1 chance to win a lottery for every
                    product
                    purchased.</p>
                <p>How to use [cash coupons]: After receiving the cash coupons, the amount will be directly transferred
                    to your
                    account.</p>
                <p>How to use [discount coupons]: After receiving the [discount coupons], you can use them when
                    purchasing
                    products to enjoy discounts.</p>
                <p>How to use [invitation coupons]: After obtaining the invitation coupons, you can get additional cash
                    rewards
                    by inviting new members to join.</p>
                <p>Note: The number of draws will be reset to 0 at 0:00 every day. If you have a chance to win a
                    lottery, please
                    use it immediately.</p>
                <div style="width: 100%;height: 5rem;"></div>
            </div>
        </div>
    </div>
    <van-popup v-model:show="showLotteryPop" style="border-radius: 10px;">
        <div class="LotteryPop" @click="receiveGift">
            <img :src="result" />
        </div>
    </van-popup>
    <MyTab></MyTab>
</template>
<script lang="ts">
import { defineComponent, ref, onMounted, onBeforeMount } from 'vue'
import { getSrcUrl } from '../../global/common'
import MyNav from '../../components/Nav.vue'
import { Tab, Image, Popup } from 'vant'
import MyTab from '../../components/Tab.vue'
import MyPop from '../../components/Pop.vue'
export default defineComponent({
    components: {
        MyPop,
        MyTab,
        [Tab.name]: Tab,
        [Image.name]: Image,
        [Popup.name]: Popup,
    },
})
</script>

<script lang="ts" setup>
import http from '../../global/network/http'
import { _alert, lang } from '../../global/common'

import cj_bg from "../../assets/img/lottery/cj_bg.png";
import draw from "../../assets/img/lottery/draw.png";

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1);
}

const showLotteryPop = ref<boolean>(false)
const prizes = ref([{ imgs: [] }])
const result = ref('')
const num = ref(0)
const myLucky = ref()
const tdata = ref([])
const LotteryResults = ref()

const blocks = ref([
    {
        borderRadius: '15px',
        padding: '2rem',
        imgs: [
            {
                src: cj_bg,   //图片url
                top: '0',     //图片距顶部距离
                width: '300px',  //图片宽
                height: '300px', //图片高
            }
        ],
    }
])

const buttons = ref([
    {
        x: 1, y: 1,
        imgs: [
            {
                src:  draw,
                width: '100%',
                height: '100%',
            }
        ]
    }
])

const prizesInitialization = () => {
    prizes.value = [
        { x: 0, y: 0, imgs: [{ src: imgFlag(tdata.value[0].cover), width: '90%', height: '85%', top: '8%' }] },
        { x: 1, y: 0, imgs: [{ src: imgFlag(tdata.value[1].cover), width: '90%', height: '85%', top: '8%' }] },
        { x: 2, y: 0, imgs: [{ src: imgFlag(tdata.value[2].cover), width: '90%', height: '85%', top: '8%' }] },
        { x: 2, y: 1, imgs: [{ src: imgFlag(tdata.value[3].cover), width: '90%', height: '85%', top: '8%' }] },
        { x: 2, y: 2, imgs: [{ src: imgFlag(tdata.value[4].cover), width: '90%', height: '85%', top: '8%' }] },
        { x: 1, y: 2, imgs: [{ src: imgFlag(tdata.value[5].cover), width: '90%', height: '85%', top: '8%' }] },
        { x: 0, y: 2, imgs: [{ src: imgFlag(tdata.value[6].cover), width: '90%', height: '85%', top: '8%' }] },
        { x: 0, y: 1, imgs: [{ src: imgFlag(tdata.value[7].cover), width: '90%', height: '85%', top: '8%' }] },
    ]
}

const startCallback = (val: any) => {
    buttons.value[0].imgs[0].width = "95%";
    buttons.value[0].imgs[0].height = "95%";
    setTimeout(() => {
        buttons.value[0].imgs[0].width = "100%";
        buttons.value[0].imgs[0].height = "100%";
    }, 300)

    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Gift&a=turntableAct',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                myLucky.value.init();
                return
            }

            myLucky.value.play()
            LotteryResults.value = res.data.giftprizelog
            myLucky.value.stop(LotteryResults.value.gift_prize_id - 1);
            num.value = res.data.lottery;
        })
    }, delayTime)
}

const endCallback = () => {
    result.value = imgFlag(LotteryResults.value.prize_cover);
    showLotteryPop.value = true;
}

const receiveGift = () => {
    showLotteryPop.value = false
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
        tdata.value = res.data.prize_arr

        prizesInitialization()
    })
})

</script>

<style lang="scss" scoped>
.choujiang {
    background: url(/src/assets/img/lottery/bg.png);
    background-repeat: no-repeat;
    background-size: 100% 100%;

    .cj_center {
        margin-top: 10rem;
        display: flex;
        justify-content: center;
        padding: 0 1rem;
        flex-direction: column;
        align-items: center;

        .title {
            width: 80%;
            height: 2.5rem;
            background-color: #fe9522;
            margin-bottom: 1rem;
            color: white;
            line-height: 2.5rem;
            text-align: center;
            font-weight: bold;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .lotteryNum {
            font-size: 1.2rem;
            font-weight: bold;
            margin: 1rem 0 0.5rem;
            color: red;
        }

        .introduce {
            font-size: 0.8rem;
            padding: 0 0.8rem;
            color: #fddd50;

            p {
                margin-bottom: 0.5rem;
            }
        }

    }
}
</style>
