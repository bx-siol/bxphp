<template>
    <div class="home">
        <MyTab></MyTab>
        <div class="home_wrap">
            <div class="home_set">
                <div class="home_top">
                    <div class="home_basic_info">
                        <div class="headico" @click="onAvatarChose">
                            <van-image :src="imgFlag(user.headimgurl)" width="4.125rem" height="4.125rem"></van-image>
                        </div>
                        <div class="info" :style="{ color: '#000' }" @click="onLink({ name: 'Setting_uinfo' })">
                            <div class="vip" v-if="false"><span class="vip_level">{{ 10 }}</span></div>
                            <p class="username">{{ user.account }}</p>
                            <div class="yield">
                                <p>12%</p>
                                <p>6%</p>
                                <p>3%</p>
                            </div>
                        </div>
                    </div>
                    <div class="Service" @click="onLink({ name: 'Service' })"></div>
                    <div class="set" @click="onLink({ name: 'Setting' })"></div>
                </div>
            </div>

            <div class="home_income">
                <div class="money">
                    <div style="display: flex;justify-content: space-between;">
                        <div class="finance1">
                            {{ t('充值钱包') }}
                        </div>
                        <div class="finance1" style="border-radius: 0 8px 0 8px;">
                            points
                        </div>
                    </div>
                    <div class="finance2">
                        <p>₹{{ wallet.balance }}</p>
                        <p>{{ wallet3.balance }} </p>
                    </div>
                </div>
                <div style="display: flex;justify-content: space-between;">
                    <div class="jump" @click="onLink({ name: 'Finance_withdraw' })">{{ t('提现') }}</div>

                    <div class="jump special" @click="onLink({ name: 'Finance_recharge' })">{{ t('充值') }}</div>
                </div>
                <van-grid :border="false" :column-num="2">
                    <van-grid-item>
                        <p>{{ wallet2.balance }}</p>
                        <span>{{ t('钱包余额') }}</span>
                    </van-grid-item>
                    <van-grid-item>
                        <!-- :to="{ name: 'Product_order' }" -->
                        <p>{{ cutOutNum(t_investment) }}</p>
                        <span>{{ t('产品') }}</span>
                    </van-grid-item>
                    <van-grid-item>

                        <p>{{ cutOutNum(withdraw) }}</p>
                        <span>{{ t('总提款') }}</span>
                    </van-grid-item>
                    <van-grid-item style="margin-top: 0;">
                        <!-- :to="{ name: 'Finance_reward', params: { type: 1 } }" -->
                        <p>{{ cutOutNum(t_reward) }}</p>
                        <span>{{ t('总利润') }}</span>
                    </van-grid-item>
                    <van-grid-item style="margin-top: 0;">
                        <p>{{ cutOutNum(t_tprofit) }}</p>
                        <span>{{ t('今日收益') }}</span>
                    </van-grid-item>
                    <van-grid-item style="margin-top: 0;">
                        <!-- :to="{ name: 'Finance_reward', params: { type: 2 } }" -->
                        <p>{{ cutOutNum(t_rebate) }}</p>
                        <span>{{ t('团队收入') }}</span>
                    </van-grid-item>

                </van-grid>
            </div>

            <div class="home_list">
                <van-cell-group>
                    <van-cell :title="t('我的产品')" :icon="myproduct" :to="{ name: 'Purchase' }"></van-cell>
                    <van-cell class="financial_records" :title="t('财务记录')" :icon="financialrecords"
                        :to="{ name: 'Finance_balancelog' }"></van-cell>
                    <van-cell :title="t('券')" :to="{ name: 'coupon', params: { type: 1 } }" :icon="coupon"></van-cell>
                    <van-cell :title="t('银行账户')" :icon="bankaccount" class="bankIcoBox"
                        :to="{ name: 'Setting_bank' }"></van-cell>
                    <van-cell :title="t('邀请券')" :to="{ name: 'coupon', params: { type: 2 } }" :icon="ico_1062"
                        v-if="false"></van-cell>
                    <van-cell :title="t('我的团队')" :icon="myteam" :to="{ name: 'User_team' }"></van-cell>
                    <van-cell :title="t('联系经理')" :icon="Service" :to="{ name: 'Service' }" v-if="false"></van-cell>
                    <van-cell :title="t('红包')" :icon="bonus" :to="{ name: 'Gift_redpack' }"></van-cell>
                    <van-cell :title="t('邀请链接')" :icon="invitationlink" :to="{ name: 'Share' }"></van-cell>
                    <van-cell :title="t('App')" :icon="app" @click="appdload"></van-cell>
                </van-cell-group>
            </div>
            <van-button class="myBtns" block type="primary" @click="onLogout" v-if="false">SIGN OUT</van-button>
        </div>
    </div>

    <MyAvatar ref="avatarRef" @success="onAvatarSuccess"></MyAvatar>

    <van-action-sheet v-model:show="serviceShow" :cancel-text="t('关闭')" :description="t('客户服务')" close-on-click-action>
        <template #default>
            <div v-for="item in actions" @click="onServiceSelect(item)"
                style="overflow: hidden;padding: 1rem 5%;border-bottom: 1px solid #efefef;">
                <van-image :src="item.type == 1 ? img_telegram : img_whatsapp" style="float: left;" width="2.5rem"
                    height="2.5rem" />
                <div style="float: right;text-align: right;">
                    <div>{{ item.name }}</div>
                    <div style="font-size: 0.9rem;color: #666666;">{{ item.account }}</div>
                </div>
            </div>
        </template>
    </van-action-sheet>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted,computed } from "vue";
import { CellGroup, Cell, Image, Grid, GridItem, ActionSheet, Button } from "vant";
import MyTab from "../../components/Tab.vue";
import MyAvatar from "../../components/Avatar.vue";

export default defineComponent({
    name: "home",
    components: {
        MyTab,
        [ActionSheet.name]: ActionSheet,
        [Image.name]: Image,
        [Grid.name]: Grid,
        [GridItem.name]: GridItem,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [Button.name]: Button,

    }
})
</script>
<script lang="ts" setup>
import myproduct from '../../assets/img/user/myproduct.png';
import bankaccount from '../../assets/img/user/bankaccount.png';
import financialrecords from '../../assets/img/user/financialrecords.png';
import missioncenter from '../../assets/img/user/missioncenter.png';
import syngenta from '../../assets/img/home/syngenta.jpg'

import invitationlink from '../../assets/img/user/invitationlink.png';
import myteam from '../../assets/img/user/myteam.png';
import bonus from '../../assets/img/user/bonus.png';
import app from '../../assets/img/user/app.png';
import Setting from "../../assets/img/user/Setting.png";
import coupon from "../../assets/img/user/coupon.png";
import coupon2 from "../../assets/img/user/coupon2.png";
import Service from "../../assets/img/user/Service.png";

import pay_pwd from "../../assets/img/user/pay_pwd.png";
import { ico_106, img_telegram, img_whatsapp } from '../../global/assets';
import http from "../../global/network/http";
// import {_alert,lang} from "../../global/common";
import { _alert, lang, getSrcUrl, goRoute, cutOutNum } from "../../global/common";
import { Dialog } from "vant";
import { flushUserinfo, doLogout } from "../../global/user";
import { useStore } from "vuex";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();


const onLink = (to: any) => {
    goRoute(to)
}


const appdload = () => {
    window.location.href = '/app'
}

const imgFlag = (src: string) => {
    return getSrcUrl(src, 0)
}

const store = useStore()
const user = ref({})
const wallet = ref({})
const wallet2 = ref({})
const wallet3 = ref({})
const avatarRef = ref()

const onAvatarChose = () => {
    avatarRef.value.chooseFile()
}

const onAvatarSuccess = (src: string) => {
    Dialog.confirm({
        message: '<b style="color: #cbac8c;">Confirm to upload?</b>',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
        width: '280px',
        allowHtml: true,
    }).then(() => {
        return new Promise((resolve) => {
            const delayTime = Math.floor(Math.random() * 1000);
            setTimeout(() => {
                http({
                    url: "c=Setting&a=uinfo_update",
                    data: { headimgurl: src },
                }).then((res: any) => {
                    if (res.code != 1) {
                        _alert(res.msg);
                        return;
                    }
                    user.value.headimgurl = src;
                    flushUserinfo(store.state.token);
                    resolve(true);
                    _alert(res.message);
                });
            }, delayTime)
        });

    }).catch(() => { })
}

const t_investment = ref(0.00)
const t_recharge = ref(0.00)
const t_withdraw = ref(0.00)

const t_reward = ref(0.00)
const t_rebate = ref(0.00)
const t_tprofit = ref(0.00)
const withdraw = ref(0.00)

const serviceShow = ref(false)
const actions = ref([]) //{ name: '选项三', subname: '描述信息' }

const onService = () => {
    serviceShow.value = true
}

const onServiceSelect = (ev: any) => {
    // console.log(ev)
    let url = ''
    if (ev.type == 1) {
        url = 'https://t.me/' + ev.account
    } else if (ev.type == 2) {
        url = 'https://wa.me/' + ev.account
    } else {
        return
    }
    window.location.href = url
}
const appshow = ref(true)

const onLogout = () => {
    Dialog.confirm({
        message: t('确定要退出么'),
        confirmButtonText: t('确定'),
        cancelButtonText: t('取消'),
        width: '280px',
        allowHtml: true,
    }).then(() => {
        return new Promise((resolve) => {
            doLogout()
        })
    }).catch(() => { });
}

onMounted(() => {
    if (window.location.href.indexOf('csisolar.in') > 0 || window.location.href.indexOf('csisolar.life ') > 0) {
        appshow.value = false;
    }
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=User&a=index'
        }).then((res: any) => {
            user.value = res.data.user
            wallet.value = res.data.wallet
            wallet2.value = res.data.wallet2
            wallet3.value = res.data.wallet3
            withdraw.value =res.data.withdraw
            t_investment.value = res.data.investment
            t_recharge.value = res.data.recharge
            t_withdraw.value = res.data.withdraw

            t_reward.value = res.data.reward
            t_rebate.value = res.data.rebate
            t_tprofit.value = res.data.tprofit
            if (res.data.service_arr && res.data.service_arr.length > 0) {
                for (let i in res.data.service_arr) {
                    let item = res.data.service_arr[i]
                    actions.value.push({
                        name: item.name,
                        subname: item.type_flag + ': ' + item.account,
                        account: item.account,
                        type: item.type
                    })
                }
            }
        })
    }, delayTime)
})

</script>

<style scoped>
.van-cell__right-icon {
    display: none;
    color: #fff;
    padding-right: 0.5rem;
}

.van-cell__title {
    padding-top: 0.5rem !important;
}

.van-cell {
    line-height: 14px;
    text-align: center;
    display: flex;
    /* width: 50%; */
    margin: 0.2rem 0;
}
</style>
<style lang="scss" scoped>
.home {
    background-color: #f6f6f6 !important;

    .home_wrap {
        box-sizing: border-box;
        background: #84973b;
        padding: 0 1rem;

        .home_set {
            margin-top: 2rem;
        }

        .home_top {
            // box-sizing: border-box;
            // background-image: url(../../assets/img//user/user-bg2.png);
            // background-repeat: no-repeat;
            // background-size: 100% 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #000;
            padding-bottom: 1rem;

            .home_basic_info {
                // padding-left: 3rem;
                display: flex;
                width: 100%;
                text-align: left;
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
            }

            .headico {
                margin-top: 0.25rem;
                margin-right: 1rem;

                .van-image {
                    border-radius: 50%;

                    :deep(.van-image__img) {
                        border-radius: 50%;
                    }
                }
            }

            .info {
                display: flex;
                flex-direction: column;
                line-height: 26px;

                .vip {
                    width: 3rem;
                    height: 1.5rem;
                    background-image: url("../../assets/img/user/vip.png");
                    background-size: 100% 100%;
                    background-repeat: no-repeat;
                    color: #000;
                    position: relative;
                    margin-bottom: 0.235rem;

                    .vip_level {
                        font-size: 0.3rem;
                        position: absolute;
                        right: 0.3rem;
                        bottom: 0.3rem;
                        font-weight: bold;
                    }
                }

                .yield {
                    font-size: 14px;
                    color: #fff;
                    margin-bottom: -1.2rem;
                    display: flex;
                    justify-content: space-between;
                    width: 105%;

                    p {
                        line-height: 20px;
                        background: #d3b826;
                        padding: 0 0.2rem;
                        border-radius: 0.2rem;
                    }
                }
            }

            .Service {
                background: url(../../assets/img/user/Service.png) no-repeat center center/2rem auto;
                width: 2rem;
                height: 2rem;
                position: absolute;

                top: -0.375rem;
                right: 12%;
            }

            .set {
                background: url(../../assets/img/user/Setting.png) no-repeat center center/2rem auto;
                width: 2rem;
                height: 2rem;
                position: absolute;

                top: -0.375rem;
                right: 1%;
            }
        }

        .money {
            background-color: #fff;
            border-radius: 8px;
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;

            .finance1 {
                padding: 0.2rem;
                background: #d3b826;
                color: #fff;
                border-radius: 8px 0 8px 0;
                width: 46%;
                text-align: center;
            }

            .finance2 {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 0;

                p {
                    font-weight: bold;
                    color: #84973b;
                    width: 50%;
                    text-align: center;
                }
            }
        }

        .home_income {
            margin-bottom: 1rem;
            border-radius: 8px;

            .van-grid {
                justify-content: space-between;
                background: #fff;
                border-radius: 8px;
                margin-top: 1rem;

                .van-grid-item {
                    border-radius: 8px;
                    flex-basis: 30% !important;
                    margin: 0.5rem 0;
                }
            }

            :deep .van-grid-item__content p {
                color: #84973b !important;
            }

            .jump {
                padding: 0.4rem;
                background: #d3b826;
                color: #fff;
                text-align: center;
                border-radius: 4px;
                font-size: 14px;
                width: 44%;
            }

            .special {
                margin-left: 0.2rem;
            }
        }

        .myBtns {
            border: 0;
            background: linear-gradient(to right, #c49b6c 20%, #a77d52);
            font-size: 16px;
            font-weight: bold;
            border-radius: 3rem;
            margin-bottom: 1.2rem;
            width: 100%;
        }
    }
}
</style>