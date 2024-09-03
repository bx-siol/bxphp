<template>
    <div class="paylogBox" style="height: 100%;overflow-y: auto;">
        <Nav leftText=''>
            <template #title>
                {{ title }}
            </template>
        </Nav>

        <div class="paylogBoxWrapper">
            <div class="list-box">
                <MyListBase :url="requesturl" ref="pageRef" @success="onPageSuccess">
                    <template #default="{ list }">
                        <table>
                            <thead>
                                <tr class="listHead">
                                    <th>{{ t('用户名') }}</th>
                                    <th>{{ t('推荐人') }}</th>
                                    <th>{{ t('团队规模') }}</th>
                                    <th>{{ t('资产') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="listitem" v-for="(item, index) in list" :key="index">
                                    <td>{{ item.account }}</td>
                                    <td>{{ item.referrer }}</td>
                                    <td>{{ item.teamSize }}</td>
                                    <td>{{ item.assets }} RS
                                        <span class="plus">
                                            <van-icon @click="onLink({ name: 'User_teamlist', params: { type:route.params.type, id: item.id } })" name="arrow"></van-icon>
                                        </span>
                                    </td>
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
import { _alert, goRoute } from "../../global/common";
import { defineComponent, ref } from "vue";
import Nav from "../../components/Nav.vue";
import MyListBase from "../../components/ListBase.vue";
import MyLoading from "../../components/Loading.vue";
import { Icon } from "vant";
import {  useRoute } from "vue-router";

export default defineComponent({
    components: {
        Nav,
        MyListBase,
        MyLoading,
        [Icon.name]: Icon,
    },
});
</script>
<script lang="ts" setup>
import http from "../../global/network/http";
import { useI18n } from "vue-i18n"; const { t } = useI18n();

const route = useRoute()
const title = 'Team ' + route.params.type

const loadingShow = ref(true);
const pageRef = ref();

const tableData = ref<any>({});
const requesturl = ref("");

if (route.params.type == 'B') {
    requesturl.value = "c=User&a=team&lv=1&type=pay";
} else if (route.params.type == 'C') {
    requesturl.value = "c=User&a=team&lv=2&type=pay";
} else if (route.params.type == 'D') {
    requesturl.value = "c=User&a=team&lv=3&type=pay";
}

const onLink = (to: any) => {
    goRoute(to);
};

const onPageSuccess = (res: any) => {
    tableData.value = res.all;
    loadingShow.value = false;
};

</script>

<style>
.van-hairline--top:after {
    border-top-width: 0px !important;
}

.van-grid-item__content:after {
    border-width: 0px !important;
}
</style>

<style lang="scss" scoped>
.paylogBox {
    background: #fff;
    color: #000;
    padding: 1rem;

    .share {
        display: flex;
        justify-content: space-around;
        flex-direction: column;

        .sendCodeBtn {
            background-color: #cc1700;
            color: white;
            font-weight: 100;
            border-radius: 8px;
            padding: 0.2rem 0.5rem;
        }

        :deep(.van-field) {
            font-size: 0.7rem;
            border: 1px solid #d1d1d1;
            border-radius: 5px;
            margin-top: 1rem;
            padding-right: 0.3rem;
        }

        :deep(.van-field__button) {
            padding-left: 0.2rem;
        }

        .teamtotal {
            height: 3rem;
            border: 1px solid #f1f1f1;
            margin-top: 1rem;
            box-shadow: 0 0 8px 0 #d1d1d1;
            display: flex;
            border-radius: 10px;

            div {
                display: flex;
                width: 49%;
                justify-content: space-around;
                height: 3rem;
                align-items: center;
            }
        }
    }

    .team {

        .team_b,
        .team_c,
        .team_d {
            box-shadow: 0 0 10px 0 #d1d1d1;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 1rem;

            div {
                height: 2rem;
                line-height: 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 1rem 0 0.5rem;
                font-size: 0.8rem;
                border-bottom: 1px solid #d1d1d1;
                color: #555555;
            }

            div:first-child {
                background-color: #cc1700;
                color: white;
                font-weight: bold;
                padding: 0;
                justify-content: center;
                border-bottom: 0;
            }
        }
    }

    .will {
        margin-top: 1rem;
        background: linear-gradient(to right, #c49b6c 20%, #a77d52);
        border-radius: 6px;

        .card {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            font: 16px/20px "Rotobo";

            .item {
                display: flex;
                height: 5rem;
                text-align: center;
                flex-direction: row-reverse;
                margin-top: 1rem;

                .p1 {
                    font-weight: bold;
                    color: #fff;
                    margin-left: 0.2rem;
                }

                .p2 {
                    color: #fff;
                    font-weight: bold;
                }
            }
        }
    }

    .paylogBoxWrapper {
        box-sizing: border-box;

        .levelTab {
            margin-top: 1.45rem;
            margin-bottom: 3rem;

            :deep(.van-tabs__nav) {
                background-color: transparent;
            }

            :deep(.van-tabs__content) {
                margin-top: 1rem;
            }

            :deep(.van-tab) {
                .van-tab__text {
                    color: #ddd;
                    font-weight: bold;
                }
            }

            :deep(.van-tab--active) {
                .van-tab__text {
                    color: #fff;
                    padding: 0.2rem 0.8rem;
                    border: 1px solid #fff;
                    border-radius: 6px;
                }
            }

            :deep(.van-grid-item__content--center) {
                flex-direction: row;
                padding: 1rem 0.375rem;
            }

            :deep(.van-grid-item__content:after) {
                border-width: 0px !important;
            }

            .levelItem_right {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                font-size: 0.75rem;
                margin-left: 0.1875rem;
            }

            .levelTabMember {
                height: 2rem;
                margin: 1rem 0 0.6rem 5%;
                font-size: 0.8rem;

                .levelTabValidMember {
                    width: 45%;
                    height: 2rem;
                    line-height: 2rem;
                    float: left;
                    border-radius: 10px;
                    text-align: center;
                    background: #c49b6c;
                    color: #fff;
                    font-weight: bold;
                }

                .levelTabInactiveMember {
                    width: 45%;
                    height: 2rem;
                    line-height: 2rem;
                    float: right;
                    border-radius: 10px;
                    text-align: center;
                    background: #c49b6c;
                    color: #fff;
                    font-weight: bold;
                }
            }
        }
    }

    .list-box {
        .invite {
            p {
                margin-top: 1rem;
                font-weight: bold;
                color: #64523e;
            }

            .copy {
                display: flex;
                align-items: center;

                :deep(.van-button) {
                    height: 2.4rem;
                }

                :deep(.van-button__text) {
                    display: flex;
                    align-items: center;
                    justify-content: flex-start;

                    img {
                        width: 1rem;
                    }
                }
            }

            :deep(.van-field__control:read-only) {
                text-transform: none !important;
            }
        }

        :deep(.van-tabs__wrap) {
            top: -4.6rem;
            position: absolute;
            width: 100%;
        }

        .myListBox {
            display: flex;
            flex-direction: column;

            .listHead {
                font: bold 14px/20px "Rotobo";
                color: #cc1700;
            }

            .listitem {
                font: bold 12px/32px "Rotobo";

                td {
                    text-align: center;
                }

                td:last-child {
                    text-align: right;
                }

                .plus {
                    display: inline-block;
                    background: #cc1700;
                    color: #fff;
                    padding: 0 4px;
                    font: normal 10px/16px "微软雅黑";
                    border-radius: 10px;
                }
            }
        }
    }

    :deep(.van-field__control::-webkit-input-placeholder) {
        color: #64523e;
        font-weight: bold;
    }
}
</style>