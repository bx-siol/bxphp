<template>
    <div class="choujiang">
        <MyNav leftText=''></MyNav>
        <div class="cj_center">
            <div class="cj_bg">
                <LuckyWheel ref="myLucky" width="380px" height="450px" :prizes="prizes" :blocks="blocks"
                    :buttons="buttons" @start="startCallback" @end="endCallback" />
            </div>

            <div class="cs">{{ num }} DRAWS REMAINING</div>
            <div class="lotteryNum">ACTIVITY RULES</div>
            <div class="introduce">
                <p>Invite new users to recharge and get 1 lucky draw chance.</p>
                <p style="padding: 1rem 0;">You can get 1 lucky draw chance when you buy a product.</p>
                <p style="color: #fdea44;">How to use the voucher: </p>
                <p>When you get a cash coupon, the amount you get goes directly into your account.</p>
                <p style="color: #fdea44;margin-top: 1rem;">How to use the coupon: </p>
                <p>After receiving the coupon, you can purchase the corresponding discounted product and enjoy the
                    discount.</p>
                <p style="color: #fdea44;margin-top: 1rem;">How to use invitation coupons: </p>
                <p>After obtaining the invitation coupons, invite new members to join and purchase equipment to get
                    extra cash rewards.</p>
                <div style="width: 100%;height: 2rem;"></div>
            </div>
        </div>

        <van-popup v-model:show="showLotteryPop" style="border-radius: 10px;">
            <div class="LotteryPop" @click="receiveGift">
                <img :src="result" />
                <p style="color: #f84604;">{{ title }}</p>
            </div>
        </van-popup>
    </div>
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
const prizes = ref([])
const result = ref('')
const title = ref('')
const num = ref(0)
const myLucky = ref()
const tdata = ref([])
const LotteryResults = ref()

const blocks = ref([
    {
        padding: '58px',
        imgs: [
            {
                src: cj_bg,   //图片url
                top: '-10px',     //图片距顶部距离
                width: '380px',  //图片宽
                height: '450px', //图片高
            }
        ],
    }
])

const buttons = ref([
    { radius: '21%', background: '#617df2' },
    { radius: '21%', background: '#afc8ff' },
    {
        imgs: [
            {
                src: draw,
                top: '-40px',
                width: '70px',
                height: '80px',
            }
        ]
    }
])

const prizesInitialization = () => {
    tdata.value.forEach((item, index) => {
        prizes.value.push({
            background: index % 2 == 0 ? "#fefefe" : "#fee2c4",
            fonts: [{ text: item.name, fontColor: '#f84604', fontSize: 11, top: "10px" }],
            imgs: [{ src: imgFlag(item.cover), width: '35px', height: '35px', top: '40px' }]
        });
    });
}

const startCallback = (val: any) => {
    myLucky.value.play()
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

            LotteryResults.value = res.data.giftprizelog
            myLucky.value.stop(LotteryResults.value.gift_prize_id - 1);
            num.value = res.data.lottery;
        })
    }, delayTime)
}

const endCallback = () => {
    result.value = imgFlag(LotteryResults.value.prize_cover);
    title.value = LotteryResults.value.prize_name
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

        .cs {
            margin-top: 1rem;
            color: #fdea44;
        }

        .lotteryNum {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 1rem 0 1rem;
            color: #9c3b0e;
            background-color: #fcb856;
            padding: 0.7rem 2rem;
            border-radius: 30px;
        }

        .introduce {
            font-size: 0.7rem;
            padding: 0 0.8rem;
            color: white;

            p {
                margin-bottom: 0.5rem;
            }
        }

    }

    .LotteryPop {
        width: 11rem;
        height: 10rem;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        img {
            width: 6rem;
            height: 6rem;
        }

        p {
            font-size: 1rem;
        }
    }
}
</style>
