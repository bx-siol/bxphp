<template>
    <div class="cashlogBox" style="background-color: #84973b;height: 100%;">
        <MyNav></MyNav>
        <div class="tablebox">
            <van-tabs v-if="false" v-model:active="activeName">
                <van-tab style="text-transform: capitalize !important;" title="Points" name="all">
                    <MyListBase url="c=Finance&a=balancelog&s_type=1019" @success="onPageSuccess">
                        <!-- ref="pageRef" -->
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr style="border-bottom: 1px solid #000 !important;" v-for="item in list">
                                        <td>
                                            <img :src="pts" style="width:40px;">
                                        </td>
                                        <td>
                                            <div><span style="font-size: 1rem;">
                                                    Points
                                                </span>
                                            </div>
                                            {{ item.create_time }}
                                        </td>
                                        <td>
                                            <div><span style="font-size: 1rem;">Order number</span></div>
                                            {{ t(item.id) }}
                                        </td>
                                        <td>
                                            <span style="font-size: 1rem; "> {{
                                                item.money > 0 ? '+' + item.money : item.money
                                            }} </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>
            </van-tabs>

            <van-tabs v-model:active="activeName" line-height="0">
                <van-tab style="text-transform: capitalize !important;" :title="t('全部')" name="all">
                    <MyListBase url="c=Finance&a=balancelog" ref="pageRef" @success="onPageSuccess">
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <td class="setof_top">
                                            <div :style="{ backgroundColor: beijingColor(item.type) }">
                                                <span v-if="item.type == 10">{{ t('红包') }}</span>
                                                <span v-else-if="item.type == 1">{{ t('投资') }}</span>
                                                <span v-else-if="item.type == 6">{{ t('投资收益') }}</span>
                                                <span v-else-if="item.type == 9">{{ t('幸运抽奖') }}</span>
                                                <span v-else-if="item.type == 14">{{ t('兑换券') }}</span>
                                                <span v-else-if="item.type == 8">{{ t('佣金') }}</span>
                                                <span v-else-if="item.type == 21">{{ t('充值') }}</span>
                                                <span v-else-if="item.type == 42">Task</span>
                                                <span v-else-if="item.type == 33">Withdrawal refund</span>
                                                <span v-else-if="item.type == 31">Withdrawal</span>
                                                <span v-else-if="item.type == 11">System Recharge</span>
                                                <span v-else-if="item.type == 1019">Points</span>
                                                <span v-else>Other</span>

                                                <span v-if="item.type == 10" style="font-size: 1rem; ">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}RS</span>

                                                <span v-else-if="item.type == 1">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}RS</span>

                                                <span v-else-if="item.type == 6">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}RS</span>

                                                <span v-else-if="item.type == 9">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}RS</span>

                                                <span v-else-if="item.type == 14">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}RS</span>

                                                <span v-else-if="item.type == 8">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}RS</span>

                                                <span v-else-if="item.type == 21">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}RS</span>

                                                <span v-else-if="item.type == 1019">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}</span>

                                                <span v-else
                                                    :style="{ 'font-size': '1rem', 'color': item.money > 0 ? '' : '#ce1b22' }">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }}RS</span>
                                            </div>
                                        </td>

                                        <td class="setof_bottom">
                                            <div>
                                                <span style="font-size: 1rem;">Order number: {{ t(item.id) }}</span>
                                                <span>{{ item.create_time }}</span>
                                            </div>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>

                <van-tab :title="t('充值')" name="cz">
                    <MyListBase url="c=Finance&a=paylog" @success="onPageSuccess">
                        <!-- ref="pageRef" -->
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <!-- <td v-if="item.status == 9">
                                            <img :src="rech" style="width:40px;">
                                        </td> -->
                                        <td  class="setof_top">
                                            <div style="background-color: #ce1b22;">
                                                <span>{{ t('充值') }}</span>
                                                <span>{{ '+' + item.money }} RS</span>
                                            </div>
                                        </td>

                                        <td  class="setof_bottom">
                                            <div>
                                                <span>Order number: {{ t(item.osn) }}</span>
                                                <span>{{ item.create_time }}</span>
                                                <span>{{ t(item.status_flag) }}</span>
                                            </div>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>

                <van-tab :title="t('提现')" name="tx">
                    <MyListBase url="c=Finance&a=cashlog" @success="onPageSuccess">
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <td class="setof_top">
                                            <div style="background-color: rgb(241,101,34);">
                                                <span>{{ t('提现') }}</span>
                                                <span> {{ '-' + item.real_money }} RS</span>
                                            </div>
                                        </td>

                                        <td class="setof_bottom">
                                            <div>
                                                <span>Order number: {{ t(item.osn) }}</span>
                                                <span>{{ item.create_time }}</span>
                                                <span>{{ t(item.status_flag) }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>


                </van-tab>

                <van-tab :title="t('投资')" name="tz">
                    <MyListBase url="c=Finance&a=balancelog&s_type=1" @success="onPageSuccess">
                        <!-- ref="pageRef" -->
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <td class="setof_top">
                                            <div style="background-color: rgb(4,38,241);">
                                                <span v-if="item.type == 10">{{ t('红包') }}</span>
                                                <span v-else-if="item.type == 1">{{ t('投资') }}</span>
                                                <span v-else-if="item.type == 6">{{ t('投资收益') }}</span>
                                                <span v-else-if="item.type == 9">{{ t('抽奖') }}</span>
                                                <span v-else-if="item.type == 14">{{ t('兑换券') }}</span>

                                                <span v-if="item.type == 10"> {{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 1">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 6">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 9">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 14">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                            </div>
                                        </td>

                                        <td class="setof_bottom">
                                            <div>
                                                <span style="font-size: 1rem;">Order number:{{ t(item.id) }}</span>
                                                {{ item.create_time }}
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>

                <van-tab :title="t('投资收益')" name="tzsy">
                    <MyListBase url="c=Finance&a=balancelog&s_type=6" @success="onPageSuccess">
                        <!-- ref="pageRef" -->
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <td class="setof_top">
                                            <div style="background-color: #4bbd4b;">
                                                <span v-if="item.type == 10">{{ t('红包') }}</span>
                                                <span v-else-if="item.type == 1">{{ t('投资') }}</span>
                                                <span v-else-if="item.type == 6">{{ t('投资收益') }}</span>
                                                <span v-else-if="item.type == 9">{{ t('抽奖') }}</span>
                                                <span v-else-if="item.type == 14">{{ t('兑换券') }}</span>

                                                <span v-if="item.type == 10"> {{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 1">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 6">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 9">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 14">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                            </div>
                                        </td>

                                        <td class="setof_bottom">
                                            <div>
                                                <span style="font-size: 1rem;">Order number:{{ t(item.id) }}</span>
                                                {{ item.create_time }}
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>

                <van-tab :title="t('抽奖')" name="cj">
                    <MyListBase url="c=Finance&a=balancelog&s_type=9" @success="onPageSuccess">
                        <!-- ref="pageRef" -->
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <td class="setof_top">
                                            <div style="background-color: rgb(255,177,35);">
                                                <span v-if="item.type == 10">{{ t('红包') }}</span>
                                                <span v-else-if="item.type == 1">{{ t('投资') }}</span>
                                                <span v-else-if="item.type == 6">{{ t('投资收益') }}</span>
                                                <span v-else-if="item.type == 9">{{ t('抽奖') }}</span>
                                                <span v-else-if="item.type == 14">{{ t('兑换券') }}</span>

                                                <span v-if="item.type == 10"> {{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 1">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 6">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 9">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 14">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                            </div>
                                        </td>
                                        <td class="setof_bottom">
                                            <div>
                                                <span style="font-size: 1rem;">Order number:{{ t(item.id) }}</span>
                                                {{ item.create_time }}
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>

                <van-tab :title="t('兑换券')" name="dhj">
                    <MyListBase url="c=Finance&a=balancelog&s_type=14" @success="onPageSuccess">
                        <!-- ref="pageRef" -->
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <td class="setof_top">
                                            <div style="background-color: #4bbd4b;">
                                                <span v-if="item.type == 10">{{ t('红包') }}</span>
                                                <span v-else-if="item.type == 1">{{ t('投资') }}</span>
                                                <span v-else-if="item.type == 6">{{ t('投资收益') }}</span>
                                                <span v-else-if="item.type == 9">{{ t('抽奖') }}</span>
                                                <span v-else-if="item.type == 14">{{ t('兑换券') }}</span>

                                                <span v-if="item.type == 10"> {{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 1">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 6">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 9">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 14">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                            </div>
                                        </td>

                                        <td class="setof_bottom">
                                            <div>
                                                <span style="font-size: 1rem;">Order number:{{ t(item.id) }}</span>
                                                {{ item.create_time }}
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>

                <van-tab :title="t('红包')" name="hb">
                    <MyListBase url="c=Finance&a=balancelog&s_type=10" @success="onPageSuccess">
                        <!-- ref="pageRef" -->
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <td class="setof_top">
                                            <div style="background-color: rgb(234,19,120);">
                                                <span v-if="item.type == 10">{{ t('红包') }}</span>
                                                <span v-else-if="item.type == 1">{{ t('投资') }}</span>
                                                <span v-else-if="item.type == 6">{{ t('投资收益') }}</span>
                                                <span v-else-if="item.type == 9">{{ t('抽奖') }}</span>
                                                <span v-else-if="item.type == 14">{{ t('兑换券') }}</span>

                                                <span v-if="item.type == 10"> {{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 1">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 6">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 9">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 14">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                            </div>
                                        </td>

                                        <td class="setof_bottom">
                                            <div>
                                                <span style="font-size: 1rem;">Order number:{{ t(item.id) }}</span>
                                                {{ item.create_time }}
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>

                <van-tab :title="t('佣金')" name="yj">
                    <MyListBase url="c=Finance&a=balancelog&s_type=8" @success="onPageSuccess">
                        <!-- ref="pageRef" -->
                        <template #default="{ list }">
                            <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                                <tbody>
                                    <tr v-for="item in list" class="setof">
                                        <td class="setof_top">
                                            <div style="background-color: #4bbd4b;">
                                                <span v-if="item.type == 10">{{ t('红包') }}</span>
                                                <span v-else-if="item.type == 1">{{ t('投资') }}</span>
                                                <span v-else-if="item.type == 6">{{ t('投资收益') }}</span>
                                                <span v-else-if="item.type == 9">{{ t('抽奖') }}</span>
                                                <span v-else-if="item.type == 14">{{ t('兑换券') }}</span>
                                                <span v-else-if="item.type == 8">{{ t('佣金') }}</span>
                                                <span v-else-if="item.type == 21">{{ t('充值') }}</span>

                                                <span v-if="item.type == 10"> {{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 1">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 6">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 9">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 14">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 8">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                                <span v-else-if="item.type == 21">{{ item.money > 0 ? '+' + item.money :
                                                    item.money }}RS</span>
                                            </div>
                                        </td>
                                        <td class="setof_bottom">
                                            <div>
                                                <span style="font-size: 1rem;">Order number:{{ t(item.id) }}</span>
                                                {{ item.create_time }}
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </MyListBase>
                </van-tab>

            </van-tabs>

        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { defineComponent, onMounted, onBeforeMount, reactive, ref } from 'vue';
import { Tab, Tabs } from 'vant';
import MyNav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';

import rech from '../../assets/a/rech.png';

import pts from '../../assets/a/pts.png';
import bonus from '../../assets/a/bonus.png';

import invest from '../../assets/a/bonus.png';

import raffle from '../../assets/a/raffle.png';

import withc from '../../assets/a/with.png';

import com from '../../assets/a/com.png';

export default defineComponent({
    components: {
        MyNav, MyListBase,
        [Image.name]: Image,
        [Tab.name]: Tab,
        [Tabs.name]: Tabs
    }
})
</script>
<script lang="ts" setup>
import { getSrcUrl, lang } from "../../global/common";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const activeName = ref('a');
let isRequest = false
const router = useRouter()
const route = useRoute()
const pointflg = ref(true)
const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}
const list2 = [
    {
        status: 9,
        create_time: '创造时间',
        pay_time: '提现时间',
        osn: 15515,
        money: 200,
        status_flag: '成功',
        type: 10,
        id: 18,
    },
]
const loadingShow = ref(true)
// const pageRef = ref()
let pageUrl = ref('c=Finance&a=balancelog')
const tableData = ref<any>({})
const pdata = reactive({})

const onPageSuccess = (res: any) => {
    tableData.value = res.data
    loadingShow.value = false
}

const beijingColor = (type: any) => {
    switch (type) {
        case 21:
            return '#ce1b22';
        case 1:
            return '#0426f1';
        case 9:
            return '#ffb123';
        case 31:
            return '#f16522';
        case 8:
            return '#0098a2';
        case 10:
            return '#ea1378';
        default:
            return '#4bbd4b';
    }
};


onMounted(() => {


})

</script>
<style lang="scss" scoped>
.tablebox {
    color: #fff;
    background-color: #84973b;

    :deep(.van-tabs__nav) {
        background-color: #84973b;
    }

    :deep(.van-tab--grow) {
        color: #b3b3b3;
    }

    :deep(.van-tab--active) {
        color: #fff;
       .van-tab__text{
        border-bottom: 2px solid #fff;
       }
    }


}

.tablebox table {
    background: #bd312d00;

    .setof {
        border-bottom: 1px solid #aaa;
        display: flex;
        flex-direction: column;
        margin-bottom: 1rem;

        .setof_top {
            div {
                display: flex;
                justify-content: space-between;
                background-color: #84973b;
                border-radius: 4px;
                padding: 0.4rem;

                span {
                    font-size: 0.875rem;
                    color: #fff;
                    font-weight: bold;
                }
            }
        }

        .setof_bottom {
            div {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.2rem
            }
        }
    }
}

.tablebox td {
    padding: 0.2rem 0rem;
    text-align: center;
}

span,
.van-tab__text {
    text-transform: capitalize !important;

}

table>tbody>tr>td:nth-child(4) {
    text-align: end;
}
</style>