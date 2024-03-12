<template>
    <div class="productDet" v-if="step == 1">
        <MyNav></MyNav>
        <div class="productDet_wrap">
            <div class="topbox" style="padding-bottom: 0;background:#fff;">
                <!--                <van-image :src="img_banner" class="pro_pic"></van-image>-->
                <MySwiper :kv="getCovers(info.covers)" height="20rem" style="border-radius: 5px;overflow: hidden;">
                </MySwiper>
                <div class="pro_name" style="word-break: break-all;color: #3d3d3b;">
                    <van-image :src="imgFlag(info.icon)"
                        style="width: 1.2rem;height: 1.2rem;vertical-align: middle;border-radius: 3px;overflow: hidden;"
                        fit="cover" class="pro_pic"></van-image>
                    {{ info.name }}
                </div>
                <van-grid :column-num="3" class="pro_grid" :border="false">
                    <van-grid-item>
                        <p class="txt">{{ t('价格') }} RS</p>
                        <p class="nums"><span class="gold">{{ info.price }}</span></p>
                    </van-grid-item>
                    <van-grid-item>
                        <p class="txt">{{ t('日产量') }} (%)</p>
                        <p class="nums"><span class="gold">{{ info.rate }}</span></p>
                    </van-grid-item>
                    <van-grid-item>
                        <p class="txt">{{ t('周期') }} ({{ t('天') }})</p>
                        <p class="nums"><span class="gold">{{ info.days }}</span></p>
                    </van-grid-item>
                </van-grid>
                <div class="pro_info" v-show="false">
                    <p>Dividend method：Repayment of principal and interest</p>
                    <p v-if="info.guarantors">Guarantee institution：{{ info.guarantors }}</p>
                    <p>No risk investment: principal and interest guarantee</p>
                </div>
                <div class="pro_speed" v-if="false">
                    <p class="jindu"><i :style="{ width: info.percent + '%' }"></i></p>
                    <span class="txt">{{ info.percent }}%</span>
                </div>
            </div>
            <div class="cerbox" style="background:#fff;color: #3d3d3b;">
                <van-tabs v-model:active="active" class="pro_tabs">
                    <!--                    <van-tab title="Investment details"></van-tab>-->
                    <van-tab :title="t('项目描述')"></van-tab>
                </van-tabs>
                <div class="pro_detail">
                    <!--                    <div class="tablecon">
                                        <table cellspacing="0" cellpadding="0">
                                            <tr>
                                                <th>Product name</th>
                                                <td style="word-break: break-all;">{{info.name}}</td>
                                            </tr>
                                            <tr>
                                                <th>Product price</th>
                                                <td><span class="gold">${{info.price}}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Daily dividend</th>
                                                <td><span class="gold">{{info.rate}}%</span></td>
                                            </tr>
                                            <tr>
                                                <th>Restricted quantity</th>
                                                <td>
                                                    <span class="gold" v-if="info.invest_limit>0">{{info.invest_limit}}</span>
                                                    <span class="gold" v-else>Unlimited</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Revenue cycle</th>
                                                <td><span class="gold">1day</span></td>
                                            </tr>
                                            <tr>
                                                <th>Profit calculation</th>
                                                <td><span class="gold">${{info.price}}</span>×<span>{{info.rate}}%</span>×<span class="gold">1day</span>=<span class="gold">${{info.profit_day}}</span>
                                                    + Principal <span class="gold">${{info.price}}</span>=Total <span class="gold">${{info.profit_day*1+info.price*1}}</span></td>
                                            </tr>
                                        </table>
                                    </div>-->
                    <div class="tablecon" v-html="info.content" style="padding-bottom: 1rem;"></div>
                </div>
            </div>
        </div>
        <div class="productDet_bot" style="height: 4rem;">
            <van-button v-if="info.status == 2" class="touziBtn" @click="onPresale"
                style="background: #0098a2;height: 3rem;">
                {{ t('预售') }}</van-button>
            <van-button v-else-if="info.status == 10" class="touziBtn" @click="onPresale"
                style="background: #0098a2;height: 3rem;">
                Not for sale</van-button>
            <van-button v-else class="touziBtn" @click="onInvest" style="background: #0098a2;height: 3rem;">Buy now
            </van-button>
        </div>
    </div>

    <div class="invest" v-else-if="step == 2">
        <MyNav>
            <template #left>
                <div @click="step = 1">
                    <van-icon name="arrow-left" size="1.3rem" style="vertical-align: middle;top:0px;color: white;" />
                    <span style="vertical-align: middle;position: relative;left: -2px;">Back</span>
                </div>
            </template>
            <template #title>Invest</template>
        </MyNav>
        <div class="invest_wrap">
            <div class="amount">
                <van-grid :border="false" :column-num="2">
                    <van-grid-item>
                        <p>My Balance RS</p>
                        <span>{{ wallet.balance }}</span>
                    </van-grid-item>
                    <van-grid-item>
                        <p>Price RS</p>
                        <span>{{ info.price }}</span>
                    </van-grid-item>
                </van-grid>
            </div>
            <div class="cont">
                <van-cell-group>
                    <van-cell title="Total Invested">
                        <template #value>
                            <span style="color: #0098a2;">${{ info.invested }}</span>
                        </template>
                    </van-cell>
                    <van-cell title="Restricted quantity">
                        <template #value>
                            <span v-if="info.invest_limit > 0" class="gold">{{ info.invest_limit }}</span>
                            <span v-else class="gold">Unlimited</span>
                        </template>
                    </van-cell>
                    <van-cell title="Interest time" value="Settlement at 24:00 every day"></van-cell>
                    <van-cell title="Purchase quantity">
                        <template #value>
                            <van-stepper v-model="quantity" :step="1" :min="1" :max="1000" button-size="20px"
                                input-width="40px" />
                        </template>
                    </van-cell>
                    <van-cell title="" v-if="false">
                        <template #value>
                            Minimum start-up ${{ info.invest_min }} , step ${{ info.invest_min }}
                        </template>
                    </van-cell>
                    <van-field label-width="4rem" input-align="right" label="Password" v-model="dataForm.password2"
                        type="password" placeholder="Enter payment password " />
                </van-cell-group>
            </div>
            <van-button class="touziBtn" @click="onSubmit">Confirm investment</van-button>
        </div>
    </div>

    <van-popup v-model:show="investShow" close-icon="close" position="bottom" closeable round class="goodsBuyPop"
        :style="{ height: '59%', background: '#ffffff' }">
        <div class="invest" style="background: #fff;">
            <div class="invest_wrap" style="padding-top: 3rem;">
                <div class="amount">
                    <van-grid :border="false" :column-num="2">
                        <van-grid-item>
                            <p>Balance Wallet RS</p>
                            <span>{{ wallet2.balance }}</span>
                        </van-grid-item>
                        <van-grid-item>
                            <p>Recharge Wallet RS</p>
                            <span>{{ wallet1.balance }}</span>
                        </van-grid-item>
                    </van-grid>
                </div>
                <div class="cont">
                    <van-cell-group>
                        <van-cell title="Price">
                            <template #value>
                                <span style="color: #0098a2;">{{ info.price }}RS</span>
                            </template>
                        </van-cell>
                        <van-cell title="Restricted quantity">
                            <template #value>
                                <span v-if="info.invest_limit > 0" class="gold">{{ info.invest_limit }}</span>
                                <span v-else class="gold">Unlimited</span>
                            </template>
                        </van-cell>
                        <van-cell title="Interest time" value="Settlement at 24:00 every day"></van-cell>
                        <van-cell title="Purchase quantity">
                            <template #value>
                                <van-stepper v-model="quantity" :step="1" :min="1" :max="1000" button-size="20px"
                                    input-width="40px" />
                            </template>
                        </van-cell>
                        <van-cell title="" v-if="false">
                            <template #value>
                                Minimum start-up ${{ info.invest_min }} , step ${{ info.invest_min }}
                            </template>
                        </van-cell>
                        <van-field v-show="false" label-width="4rem" input-align="right" label="Password"
                            v-model="dataForm.password2" type="password" placeholder="Enter payment password " />
                    </van-cell-group>
                </div>
                <van-button class="touziBtn" @click="onSubmit" style="height: 3rem;background: #0098a2;">Confirm buy
                </van-button>
            </div>
        </div>
    </van-popup>
    <MyLoading :show="loadingShow" title="Submit"></MyLoading>

</template>

<script lang="ts">

import { defineComponent, ref, reactive, onMounted } from "vue";

import MyNav from "../../components/Nav.vue";
import MySwiper from '../../components/Swiper.vue';
import MyLoading from "../../components/Loading.vue";

import { Button, Grid, GridItem, Image, Tab, Tabs, Cell, CellGroup, Stepper, Icon, Field, Popup } from "vant";

export default defineComponent({
    name: "productDet",
    components: {
        MyNav, MySwiper,MyLoading,
        [Image.name]: Image,
        [Button.name]: Button,
        [Grid.name]: Grid,
        [GridItem.name]: GridItem,
        [Tab.name]: Tab,
        [Tabs.name]: Tabs,
        [Cell.name]: Cell,
        [Field.name]: Field,
        [Stepper.name]: Stepper,
        [CellGroup.name]: CellGroup,
        [Icon.name]: Icon,
        [Popup.name]: Popup,
    }
})
</script>
<script lang="ts" setup>
import { useRoute, useRouter } from "vue-router";
import { img_banner } from '../../global/assets';
import http from "../../global/network/http";
import { _alert, getSrcUrl, goRoute } from "../../global/common";
import md5 from "md5";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const onLink = (to: any) => {
    goRoute(to)
}

let isRequest = false
const route = useRoute()
const router = useRouter()

const wallet1 = ref({})
const wallet2 = ref({})
const info = ref({
    invest_min: 0,
    covers: []
})
const step = ref(1)
const active = ref(0)
const money = ref(info.invest_min)
const quantity = ref(1)

const getCovers = (covers: any) => {
    let kv = []
    for (let i in covers) {
        kv.push({ cover: covers[i] })
    }
    return kv
}

const dataForm = reactive({
    password2: ''
})

const investShow = ref(false)
const loadingShow = ref(false);

const onPresale = () => {
    _alert('Unable to activate during pre-sale')
}

const onInvest = () => {
    // step.value=2
    investShow.value = true
}

const onSubmit = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    loadingShow.value = true;
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Product&a=invest',
            data: {
                gsn: info.value.gsn,
                money: money.value,
                quantity: quantity.value,
                password2: md5(dataForm.password2)
            }
        }).then((res: any) => {
            loadingShow.value = false;
            if (res.code != 1) {
                isRequest = false
                _alert(res.msg)
                return
            }
            dataForm.password2 = ''
            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => {
                    init()
                    isRequest = false
                    step.value = 1
                    investShow.value = false
                }
            })
        })
    }, delayTime)
}

const init = () => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Product&a=goods',
            data: { gsn: route.params.gsn }
        }).then((res: any) => {
            if (res.code != 1) {
                _alert({
                    type: 'error',
                    message: res.msg,
                    onClose: () => {
                        router.go(-1)
                    }
                })
                return
            }
            info.value = res.data.info
            wallet1.value = res.data.wallet1
            wallet2.value = res.data.wallet2
        })
    }, delayTime)
}

onMounted(() => {
    init()
})

</script>

<style>
.van-stepper__input,
.invest_wrap .cont .van-stepper button {
    color: #3d3d3d !important;
}

.invest_wrap .cont .van-cell__title,
.invest_wrap .cont .van-cell__value {
    color: #3d3d3d;
}

.invest .van-field__control {
    color: white;
}

.invest_wrap .cont .van-cell::after {
    border-color: #544c4c;
}

.goodsBuyPop {
    right: 0;
}
</style>