<template>
    <div class="cashlogBox" style="background-color: #fff; min-height: 100%;">
        <MyNav leftText=""></MyNav>
        <div class="tablebox">
            <div class="tableRight">
                <MyListBase :key="listkey" :url="listurl" ref="pageRef" @success="onPageSuccess">
                    <template #default="{ list }">
                        <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                            <tbody>
                                <tr v-for="item in list">
                                    <td style="border-bottom: 1px solid #bbb;padding-bottom: 0.6rem;" v-if="false">
                                        <div><span>Order number</span></div>
                                        {{ t(item.id) }}
                                    </td>
                                    <div style="display: flex;justify-content: space-between;align-items: center;">
                                        <div style="display: flex;flex-direction: row;width: 100%;">

                                            <td v-if="item.type == 1" style="background-color: #00a99d;"></td>
                                            <td v-else-if="item.type == 6" style="background-color: #f15a24;"></td>
                                            <td v-else-if="item.type == 8" style="background-color: #cbccfe;"></td>
                                            <td v-else-if="item.type == 9" style="background-color: #f5665b;"></td>
                                            <td v-else-if="item.type == 10" style="background-color: #aed0ed;"></td>
                                            <td v-else-if="item.type == 11" style="background-color: black;"></td>
                                            <td v-else-if="item.type == 21" style="background-color: #cc1700;"></td>
                                            <td v-else-if="item.type == 31" style="background-color: #f7931e;"></td>
                                            <td v-else-if="item.type == 33" style="background-color: #c3c3c3;"></td>
                                            <td v-else style="background-color: #376ab3;"></td>

                                            <td class="variation" style="flex-direction: column;width: 64%;">
                                                <div v-if="item.type == 1">
                                                    <span style="color: #00a99d;">{{ t('投资') }}</span>
                                                </div>
                                                <div v-else-if="item.type == 6">
                                                    <span style="color: #f15a24;">{{ t('投资收益') }}</span>
                                                </div>
                                                <div v-else-if="item.type == 8">
                                                    <span style="color: #cbccfe;">{{ t('佣金') }}</span>
                                                </div>
                                                <div v-else-if="item.type == 9">
                                                    <span style="color: #f5665b;">{{ t('幸运抽奖') }}</span>
                                                </div>
                                                <div v-else-if="item.type == 10">
                                                    <span style="color: #aed0ed;">{{ t('红包') }}</span>
                                                </div>
                                                <div v-else-if="item.type == 11">
                                                    <span style="color: black;">System Recharge</span>
                                                </div>
                                                <div v-else-if="item.type == 21">
                                                    <span style="color: #cc1700;">{{ t('充值') }}</span>
                                                </div>
                                                <div v-else-if="item.type == 31">
                                                    <span style="color: #f7931e;">Withdrawal</span>
                                                </div>
                                                <div v-else-if="item.type == 33">
                                                    <span style="color: #c3c3c3;">Withdrawal refund</span>
                                                </div>
                                                <div v-else>
                                                    <span style="color: #376ab3;">Other</span>
                                                </div>
                                                
                                                <div v-if="item.type == 1" style="color: #00a99d;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else-if="item.type == 6" style="color: #f15a24;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else-if="item.type == 8" style="color: #cbccfe;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else-if="item.type == 9" style="color: #f5665b;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else-if="item.type == 10" style="color: #aed0ed;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else-if="item.type == 11" style="color: black;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else-if="item.type == 21" style="color: #cc1700;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else-if="item.type == 31" style="color: #f7931e;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else-if="item.type == 33" style="color: #c3c3c3;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                                <div v-else style="color: #376ab3;">
                                                    {{ item.money > 0 ? '+' + item.money : item.money }} RS
                                                </div>
                                            </td>
                                            <td style="display: flex;flex-direction: column;width: 35%;">
                                                <span style="color: black;">Time</span>
                                                <span style="color: #d1d1d1;">{{ item.create_time }}</span>
                                            </td>
                                        </div>
                                    </div>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                </MyListBase>
            </div>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive, ref, computed } from "vue";
import { Tab, Tabs, Sticky } from "vant";
import MyNav from "../../components/Nav.vue";
import MyListBase from "../../components/ListBase.vue";
import MyLoading from "../../components/Loading.vue";

export default defineComponent({
    components: {
        MyNav,
        MyListBase,
        [Image.name]: Image,
        [Sticky.name]: Sticky,
        [Tab.name]: Tab,
        [Tabs.name]: Tabs,
    },
});
</script>
<script lang="ts" setup>
import { getSrcUrl, lang,cutOutNum } from "../../global/common";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

const isActive = (name: string) => {
    return {
        background: selected.value === name ? "#fff" : "#f5f6fa",
        color: selected.value === name ? "#64523e" : "#002544",
    }
};

const listurl = ref("c=Finance&a=balancelog")
const listkey = ref(0)
const selected = ref("0");

const loadingShow = ref(true);
let pageUrl = ref("fin_balancelog");
const tableData = ref<any>({});

const onPageSuccess = (res: any) => {
    tableData.value = res.data;
    for(let item of tableData.value.list){
        item.money = cutOutNum(item.money);
    }
    loadingShow.value = false;
};

onMounted(() => { });

</script>
<style lang="scss" scoped>
.cashlogBox {
    .tablebox {
        color: #6f6c6c;
        padding: 0;
        min-height: 100%;

        table {
            border: none;

            tr {
                display: flex;
                flex-direction: column;
                font-size: 12px;
                margin: 0.5rem 0;
                background-color: #fff;
                border-bottom: 1px solid #ddd;
                padding-bottom: 1rem;

                td {
                    display: flex;
                    align-items: center;
                    justify-content: space-around;
                    border: none;
                    padding: 0.4rem 0rem 0;

                    div{
                        margin-bottom: 0.1rem;
                    }
                }

                td:first-child{
                    width: 0.3rem;
                    height: 2.5rem;
                }
            }
        }

        .tableRight {
            padding: 0 1rem 1rem;
            height: 100%;
            overflow-x: auto;
            font-size: 0.8rem;

            .variation {
                margin-left: 0.5rem;

                div {
                    width: 100%;
                    text-align: left;
                    font-weight: bold;
                }
            }
        }
    }

    .van-tab__text {
        text-transform: capitalize !important;
    }

    table>tbody>tr>td:nth-child(4) {
        text-align: end;
    }
}
</style>