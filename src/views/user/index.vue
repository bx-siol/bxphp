<template>
    <div class="home">
        <MyTab></MyTab>
        <div class="home_wrap">
            <div class="home_set">
                <div class="home_top">
                    <div class="home_basic_info">                        
                        <div class="headico" @click="onAvatarChose">
                            <van-image :src="imgFlag(user.headimgurl)" @error="onError"  width="4.125rem" height="4.125rem"></van-image>
                        </div>                  
                        <div class="info" :style="{ color: '#fff' }" @click="onLink({ name: 'Setting_uinfo' })">
                            <p class="username">{{ user.account }}</p>
                            <p class="username">ID:{{ user.id }}</p>
                        </div>
                    </div>
                </div>
                <div class="money">
                    <div class="money_body">
                        <div class="flex">
                            <p>₹{{ wallet.balance }}</p>
                            <p>{{ t('充值钱包') }}</p>
                        </div>
                    </div>
                    <div class="money_body">
                        <div class="flex">                            
                            <p>₹{{ wallet2.balance }}</p>
                            <p>{{ t('余额钱包') }}</p>
                        </div>
                    </div>
                    <div class="money_body">
                        <div class="flex">                            
                            <p>₹{{ cutOutNum(t_tprofit) }}</p>
                            <p>{{ t('今日收益') }}</p>
                        </div>
                    </div>
                    <div class="money_body">
                        <div class="flex">                            
                            <p>₹{{ cutOutNum(t_rebate) }}</p>
                            <p>{{ t('团队收入') }}</p>
                        </div>
                    </div>
                    <div class="money_body">
                        <div class="flex">                            
                            <p>₹{{ cutOutNum(t_investment) }}</p>
                            <p>{{ t('产品') }}</p>
                        </div>
                    </div>
                    <div class="money_body">
                        <div class="flex">                            
                            <p>₹{{ cutOutNum(t_reward) }}</p>
                            <p>{{ t('总利润') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="home_list">
                <van-cell-group>
                    <van-cell :title="t('我的产品')" :icon="myproduct" :to="{ name: 'Purchase' }" v-if="false"></van-cell>
                    <van-cell :title="t('券')" :to="{ name: 'coupon', params: { type: 1 } }" :icon="coupon"></van-cell>
                    <van-cell :title="t('邀请券')" :to="{ name: 'coupon', params: { type: 2 } }" :icon="coupon2" v-if="false"></van-cell>
                    <van-cell :title="t('银行账户')" :icon="bankaccount" class="bankIcoBox" :to="{ name: 'Setting_bank' }"></van-cell>
                    <van-cell  :title="t('财务记录')" :icon="financialrecords" :to="{ name: 'Finance_balancelog' }"></van-cell>
                    <van-cell :title="t('邀请链接')" :icon="invitationlink" :to="{ name: 'Share' }"></van-cell>
                    <van-cell :title="t('我的团队')" :icon="myteam" :to="{ name: 'User_team' }"></van-cell>
                    <van-cell :title="t('红包')" :icon="bonus" :to="{ name: 'Gift_redpack' }"></van-cell>
                    <van-cell :title="t('联系经理')" :icon="Service" :to="{ name: 'Service' }"></van-cell>
                    <van-cell :title="t('修改密码')" :icon="pay_pwd" :to="{ name: 'Setting_password' }"></van-cell>
                    <van-cell :title="t('App')" :icon="app" @click="appdload" class="last-child"></van-cell>
                </van-cell-group>
            </div>
            <div class="myBtns" @click="onLogout">
                {{ t('登出') }}
            </div>
        </div>
    </div>

    <MyAvatar ref="avatarRef" @success="onAvatarSuccess"></MyAvatar>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
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
import invitationlink from '../../assets/img/user/invitationlink.png';
import myteam from '../../assets/img/user/myteam.png';
import bonus from '../../assets/img/user/bonus.png';
import app from '../../assets/img/user/app.png';
import coupon from "../../assets/img/user/coupon.png";
import coupon2 from "../../assets/img/user/coupon2.png";
import Service from "../../assets/img/user/Service.png";
import Life_Fitness from '../../assets/img/home/Life-Fitness.png'

import pay_pwd from "../../assets/img/user/pay_pwd.png";
import { img_telegram, img_whatsapp } from '../../global/assets';
import http from "../../global/network/http";
import { _alert, lang, getSrcUrl, goRoute, cutOutNum } from "../../global/common";
import { Dialog } from "vant";
import { flushUserinfo, doLogout } from "../../global/user";
import { useStore } from "vuex";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

const store = useStore()
const user = ref({})
const wallet = ref({})
const wallet2 = ref({})
const wallet3 = ref({})
const avatarRef = ref()
const t_investment = ref(0.00)
const t_recharge = ref(0.00)
const t_withdraw = ref(0.00)
const t_reward = ref(0.00)
const t_rebate = ref(0.00)
const t_tprofit = ref(0.00)
const actions = ref([])
const appshow = ref(true)

const onLink = (to: any) => {
    goRoute(to)
}

const appdload = () => {
    window.location.href = '/app'
}

const imgFlag = (src: string) => {
    return getSrcUrl(src, 0)
}
const onError = () => {
  user.value.headimgurl = Life_Fitness; 
};

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
    http({
        url: 'c=User&a=index'
    }).then((res: any) => {
        user.value = res.data.user
        wallet.value = res.data.wallet
        wallet2.value = res.data.wallet2
        wallet3.value = res.data.wallet3
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
    line-height: 12px;
    text-align: center;
    display: flex;
    margin: 0.3rem 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 0.4rem;
}
</style>
<style lang="scss" scoped>
.home {

    .home_wrap {
        box-sizing: border-box;
        padding: 0 1rem;
        background-image: url(../../assets/img//user/user_bg.png);
        background-repeat: no-repeat;
        background-size: 100% 100%;

        .home_set {
            margin-top: 2rem;
        }

        .home_top {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #000;

            .home_basic_info {
                display: flex;
                width: 100%;
                text-align: left;
                flex-direction: column;
                align-items: center;
                justify-content: space-between;
                
            }

            .headico {
                margin-top: 0.25rem;

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
                line-height: 22px;
                text-align: center;
                margin-top: 1rem;
            }
        }

        .money {
            display: flex;
            justify-content: space-around;
            background-color: #fff;
            border-radius: 8px;
            margin-top: 0.5rem;
            padding: 1rem;
            color: black;
            flex-wrap: wrap;
            margin-bottom: 1rem;

            .money_body {
                height: 3.8rem;
                width: 40%;
                display: flex;
                justify-content: space-around;
                align-items: center;

                .flex {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: space-between;

                    p {
                        font-weight: bold;
                    }
                    p:nth-child(1) {
                        font-weight: bold;
                    }
                    p:nth-child(2) {
                        margin-top: 0.2rem;
                        font-size: 0.75rem;
                        font-weight: 100;
                    }
                }
            }
        }

        .myBtns {
            height: 2.5rem;
            text-align: center;
            line-height: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            background-color: black;
            border-radius: 10px;
            font-weight: bold;
        }
    }
}
</style>