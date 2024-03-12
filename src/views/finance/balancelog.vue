<template>
    <div class="cashlogBox" style="background-color: #fff; min-height: 100%;">
        <MyNav leftText=""></MyNav>
        <div class="over" style="overflow: hidden;" v-if="false">
            <div class="dropdown">
                <div class="dropdown-toggle" @click="toggleDropdown">
                    {{ selectedText }}
                    <span class="caret"> <img :src="dropdown"></span>
                </div>
                <div class="menu" v-show="isDropdownOpen">
                    <div @click="selectlist('0')" :style="isActive('0')">All</div>
                    <div @click="selectlist('Recharge')" :style="isActive('Recharge')">Recharge</div>
                    <div @click="selectlist('Withdraw')" :style="isActive('Withdraw')">Withdraw</div>
                    <div @click="selectlist('1')" :style="isActive('1')">Invest</div>
                    <div @click="selectlist('6')" :style="isActive('6')">Profit</div>
                    <div @click="selectlist('9')" :style="isActive('9')">Lucky Draw</div>
                    <div @click="selectlist('14')" :style="isActive('14')">Cash Coupons</div>
                    <div @click="selectlist('10')" :style="isActive('10')">Bonus</div>
                    <div @click="selectlist('8')" :style="isActive('8')">Commision</div>
                </div>
            </div>
        </div>

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
                                        <td v-if="false">
                                            <img v-if="item.type == 10" :src="bonus" style="width:42px;">
                                            <img v-else-if="item.type == 1" :src="invest" style="width:42px;">
                                            <img v-else-if="item.type == 6" :src="com" style="width:42px;">
                                            <img v-else-if="item.type == 9" :src="raffle" style="width:42px;">
                                            <img v-else-if="item.type == 14" :src="com" style="width:42px;">
                                            <img v-else-if="item.type == 8" :src="com" style="width:42px;">
                                            <img v-else-if="item.type == 21" :src="com" style="width:42px;">

                                            <img v-else-if="item.type == 11" :src="com" style="width:42px;">
                                            <img v-else-if="item.type == 42" :src="com" style="width:42px;">
                                            <img v-else-if="item.type == 33" :src="com" style="width:42px;">
                                            <img v-else-if="item.type == 31" :src="com" style="width:42px;">
                                            <img v-else-if="item.type == 1019" :src="com" style="width:42px;">
                                            <img v-else :src="withc" style="width:42px;">
                                        </td>
                                        <div style="display: flex;flex-direction: column;width: 100%;">
                                            <td class="variation">
                                                <div v-if="item.type == 10"><span>{{
                                                    t('红包')
                                                }}</span></div>
                                                <div v-else-if="item.type == 1"><span>{{
                                                    t('投资')
                                                }}</span></div>
                                                <div v-else-if="item.type == 6"><span>{{
                                                    t('投资收益')
                                                }}</span></div>
                                                <div v-else-if="item.type == 9"><span>{{
                                                    t('抽奖')
                                                }}</span></div>
                                                <div v-else-if="item.type == 14"><span>{{
                                                    t('兑换券')
                                                }}</span></div>
                                                <div v-else-if="item.type == 2"><span>{{
                                                    t('佣金')
                                                }}</span></div>
                                                <div v-else-if="item.type == 21"><span>{{
                                                    t('充值')
                                                }}</span></div>

                                                <div v-else-if="item.type == 42"><span>Task</span>
                                                </div>
                                                <div v-else-if="item.type == 33"><span>Withdrawal
                                                        refund</span>
                                                </div>
                                                <div v-else-if="item.type == 31"><span>Withdrawal</span>
                                                </div>
                                                <div v-else-if="item.type == 11"><span>System
                                                        Recharge</span>
                                                </div>
                                                <div v-else-if="item.type == 1019"><span>
                                                        Points
                                                    </span>
                                                </div>
                                                <div v-else><span>Other</span></div>


                                                <span v-if="item.type == 10" style="font-size: 0.9rem;"> {{
                                                    item.money > 0 ? '+' + item.money : item.money
                                                }}RS</span>

                                                <span v-else-if="item.type == 1"
                                                    style="font-size: 0.9rem;color: #ce1b22;font-weight: bold;"> {{
                                                        item.money > 0 ? '+' + item.money : item.money
                                                    }}RS</span>
                                                <span v-else-if="item.type == 6"
                                                    style="font-size: 0.9rem;color: #0426f1;font-weight: bold;"> {{
                                                        item.money > 0 ? '+' + item.money : item.money
                                                    }}RS</span>
                                                <span v-else-if="item.type == 9"
                                                    style="font-size: 0.9rem;color: #ffb123;font-weight: bold;"> {{
                                                        item.money > 0 ? '+' + item.money : item.money
                                                    }}RS</span>
                                                <span v-else-if="item.type == 14"
                                                    style="font-size: 0.9rem;color: #0098a2;font-weight: bold;">
                                                    {{
                                                        item.money > 0 ? '+' + item.money : item.money
                                                    }}RS</span>

                                                <span v-else-if="item.type == 2"
                                                    style="font-size: 0.9rem;color: #0098a2;font-weight: bold;"> {{
                                                        item.money > 0 ? '+' + item.money : item.money
                                                    }}RS</span>
                                                <span v-else-if="item.type == 21"
                                                    style="font-size: 0.9rem;color: #0098a2;font-weight: bold;">
                                                    {{
                                                        item.money > 0 ? '+' + item.money : item.money
                                                    }}RS</span>
                                                <span v-else-if="item.type == 1019"
                                                    style="font-size: 0.9rem;color: #0098a2;font-weight: bold;">
                                                    {{
                                                        item.money > 0 ? '+' + item.money : item.money
                                                    }}</span>
                                                <span v-else
                                                    :style="{ 'font-size': '0.9rem', 'font-weight': 'bold', 'color': item.money > 0 ? '' : '#ce1b22' }">
                                                    {{
                                                        item.money > 0 ? '+' + item.money : item.money
                                                    }}RS</span>
                                            </td>
                                            <td>
                                                <span>{{ item.create_time }}</span>
                                                <span>{{ item.fund_changes }}</span>
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
import { defineComponent, onMounted, onBeforeMount, reactive, ref, computed } from "vue";
import { Tab, Tabs, Sticky } from "vant";
import MyNav from "../../components/Nav.vue";
import MyListBase from "../../components/ListBase.vue";
import MyLoading from "../../components/Loading.vue";
import dropdown from "../../assets/a/dropdown.png";
import rech from "../../assets/a/rech.png";

import pts from "../../assets/a/pts.png";
import bonus from "../../assets/a/bonus.png";

import invest from "../../assets/a/invest.png";

import raffle from "../../assets/a/raffle.png";

import withc from "../../assets/a/with.png";

import com from "../../assets/a/com.png";

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
import { getSrcUrl, lang } from "../../global/common";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();
const activeName = ref("a");
let isRequest = false;
const router = useRouter();
const route = useRoute();
const pointflg = ref(true);
const active = ref("0");
const imgFlag = (src: string) => {
    return getSrcUrl(src, 1);
};

const isActive = (name: string) => {
    return {
        background: selected.value === name ? "#fff" : "#f5f6fa",
        color: selected.value === name ? "#64523e" : "#002544",
        // borderBottom: selected.value === name ? "5px solid #008260" : "0px solid #e22e2f",
    }
};
const isDropdownOpen = ref(false);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
}
const toggle = () => {
    isDropdownOpen.value = false;
}
const listurl = ref("c=Finance&a=balancelog")
const listkey = ref(0)
const selected = ref("0");


const selectlist = (name: any) => {
    console.log(name)
    if (name == "Recharge")
        listurl.value = "c=Finance&a=paylog"
    else if (name == "Withdraw")
        listurl.value = "c=Finance&a=cashlog"
    else
        listurl.value = "c=Finance&a=balancelog&s_type=" + name

    listkey.value = listkey.value + 1
    selected.value = name;
    isDropdownOpen.value = false;
}
const loadingShow = ref(true);
// const pageRef = ref()
let pageUrl = ref("fin_balancelog");
const tableData = ref<any>({});
const pdata = reactive({});

const onPageSuccess = (res: any) => {
    tableData.value = res.data;
    loadingShow.value = false;
};

const selectedText = computed(() => {
    switch (selected.value) {
        case '0': return "All";
        case 'Recharge': return "Recharge";
        case 'Withdraw': return "Withdraw";
        case '1': return "Invest";
        case '6': return "Profit";
        case '9': return "Lucky Draw";
        case '14': return "Cash Coupons";
        case '10': return "Bonus";
        case '8': return "Commision";
        default: return "All";
    }
});
onMounted(() => { });

</script>
<style lang="scss" scoped>
.cashlogBox {

    .over {
        text-align: right;

        .dropdown {
            display: flex;
            flex-direction: column;
            align-items: flex-end;

            .dropdown-toggle {
                padding: 0.4rem 0.2rem;
                margin: 1rem 1rem 0;
                color: #64523e;
                border: 1px solid #64523e;
                border-radius: 30px;
                // font-weight: bold;
                text-align: center;
                width: 100px;
                display: flex;
                align-items: center;
                justify-content: space-evenly;
                font-size: 12px;

                img {
                    width: 1rem;
                }
            }

            .menu {
                position: absolute;
                top: 7rem;
                right: 1rem;
                text-align: center;
                border: 1px solid #64523e;
                background-color: #fff;
                border-radius: 8px;
                // font-weight: bold;

                >div {
                    padding: 0.5rem 0.5rem;
                    font-size: 12px;
                }

                div:first-child {
                    border-radius: 8px 8px 0 0;
                }

                div:last-child {
                    border-radius: 0 0 8px 8px;
                }
            }

        }

    }

}


.tablebox {
    color: #6f6c6c;
    padding: 0;
    min-height: 100%;
}

.tablebox table {
    border: none;


    tr {
        display: flex;
        flex-direction: column;
        font-size: 12px;
        margin: 1rem 0;
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        padding-bottom: 0.5rem;

        td {
            display: flex;
            align-items: center;
            justify-content: space-between;

            img {
                width: 50px;
                margin-right: 0.4rem;
            }
        }
    }
}



.tablebox {
    .tableRight {
        padding: 0 1rem 1rem;
        height: 100%;
        overflow-x: auto;
        font-size: 0.8rem;

        .variation {
            div {
                span {
                    color: #002544;
                    font-weight: bold;
                }
            }
        }
    }
}

.tablebox td {
    border: none;
    padding: 0.5rem 0rem 0;
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