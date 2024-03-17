<template>
    <div class="paylogBox" style="height: 100%;overflow-y: auto;">
        <Nav leftText=''></Nav>
        <div class="will">
            <div class="card">

                <div class="item">
                    <p class="p1"> {{ tableData.unpaycount }}</p>
                    <!-- <p class="p1">Unrecharge Member</p> -->
                </div>
                <div class="item">
                    <p class="p1">{{ tableData.paycount }}</p>
                    <!-- <p class="p1">Active Member</p> -->
                </div>

            </div>
        </div>

        <div class="paylogBoxWrapper">
            <div class="list-box">
                <van-tabs @click-tab="onClickTab" line-height="0" v-model:active="active" class="levelTab">
                    <van-tab :title="fy.lv1">
                        <div class="levelTabMember">
                            <div class="levelTabInactiveMember" @click="SwitchMembers(1, 1)">{{ t('Invalid Member') }}
                            </div>
                            <div class="levelTabValidMember" @click="SwitchMembers(1, 0)">{{ t('Active Member') }}</div>
                        </div>
                        <MyListBase :url="requesturl1" ref="pageRef" @success="onPageSuccess">
                            <template #default="{ list }">
                                <div class="listHead">
                                    <p>{{ t('用户名') }}</p>
                                    <p>{{ t('邀请') }}</p>
                                    <p>{{ t('资产') }}</p>
                                    <p>{{ t('Is Get') }}</p>
                                </div>
                                <div class="listitem" v-for="(item, index) in list" :key="index">
                                    <!-- <img :src="nestie"> -->
                                    <p>{{ item.account }}</p>
                                    <p>{{ item.referrer }}</p>
                                    <p>{{ item.assets }}</p>
                                    <p>{{ item.first_pay_day_flag }} </p>
                                    <span class="plus"><van-icon name="arrow"
                                            @click="onLink({ name: 'User_teamlist', params: { id: item.id } })"></van-icon></span>
                                </div>
                            </template>
                        </MyListBase>
                    </van-tab>
                    <van-tab :title="fy.lv2">
                        <div class="levelTabMember">
                            <div class="levelTabInactiveMember" @click="SwitchMembers(2, 1)">{{ t('Invalid Member') }}
                            </div>
                            <div class="levelTabValidMember" @click="SwitchMembers(2, 0)">{{ t('Active Member') }}</div>
                        </div>
                        <MyListBase :url="requesturl2" ref="pageRef1" @success="onPageSuccess">
                            <template #default="{ list }">
                                <div class="listHead">
                                    <p>{{ t('用户名') }}</p>
                                    <p>{{ t('邀请') }}</p>
                                    <p>{{ t('资产') }}</p>
                                    <p>{{ t('Is Get') }}</p>
                                </div>
                                <div class="listitem" v-for="(item, index) in list" :key="index">
                                    <p>{{ item.account }}</p>
                                    <p>{{ item.referrer }}</p>
                                    <p>{{ item.assets }}</p>
                                    <p>{{ item.first_pay_day_flag }}</p>
                                    <span class="plus"><van-icon name="arrow"
                                            @click="onLink({ name: 'User_teamlist', params: { id: item.id } })"></van-icon></span>
                                </div>
                            </template>
                        </MyListBase>
                    </van-tab>
                    <van-tab :title="fy.lv3">
                        <div class="levelTabMember">
                            <div class="levelTabInactiveMember" @click="SwitchMembers(3, 1)">{{ t('Invalid Member') }}
                            </div>
                            <div class="levelTabValidMember" @click="SwitchMembers(3, 0)">{{ t('Active Member') }}</div>
                        </div>
                        <MyListBase :url="requesturl3" ref="pageRef2" @success="onPageSuccess">
                            <template #default="{ list }">
                                <div class="listHead">
                                    <p>{{ t('用户名') }}</p>
                                    <p>{{ t('邀请') }}</p>
                                    <p>{{ t('资产') }}</p>
                                    <p>{{ t('Is Get') }}</p>
                                </div>
                                <div class="listitem" v-for="(item, index) in list" :key="index">
                                    <p>{{ item.account }}</p>
                                    <p>{{ item.referrer }}</p>
                                    <p>{{ item.assets }}</p>
                                    <p>{{ item.first_pay_day_flag }} </p>
                                    <span class="plus"><van-icon name="arrow"
                                            @click="onLink({ name: 'User_teamlist', params: { id: item.id } })"></van-icon></span>
                                </div>
                            </template>
                        </MyListBase>
                    </van-tab>
                </van-tabs>
            </div>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { _alert, lang } from "../../global/common"
import { defineComponent, onMounted, reactive, ref, computed } from 'vue';
import { Image } from 'vant';
import Nav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import Service from '../../components/service.vue';
import leaf from '../../assets/ico/leaf.png'
import nestie from '../../assets/img/home/nestie.jpg'

import MyLoading from '../../components/Loading.vue';
import { Button, Tab, Tabs, Grid, GridItem, Cell, Field, Icon } from "vant";
import { type } from 'os';
export default defineComponent({
    components: {
        Nav, MyListBase, MyLoading,
        [Button.name]: Button,
        [Image.name]: Image,
        [Tab.name]: Tab,
        [Tabs.name]: Tabs,
        [Grid.name]: Grid,
        [GridItem.name]: GridItem,
        [Cell.name]: Cell,
        [Field.name]: Field,
        [Icon.name]: Icon,
    }
})
</script>
<script lang="ts" setup>

import { getSrcUrl, imgPreview, copy, goRoute } from "../../global/common";
import http from "../../global/network/http";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

let isRequest = false
const active = ref(0)

type level = {
    img: string,
    numbering: string,
    number: number | string
}
const levelList = ref<Array<level>>([
    { img: 'avatar', numbering: 'numbering', number: 910284049 },
    { img: 'avatar', numbering: 'numbering', number: 910284049 },
])


const onLink = (to: any) => {
    goRoute(to)
}
const memberstatus = ref(0)
const loadingShow = ref(true)
const pageRef = ref()
const pageRef1 = ref()
const pageRef2 = ref()

const cpageRef = ref()
const lv = ref()

const tableData = ref<any>({})
const pdata = reactive({})
const atype = ref();
const teamusercount = ref(0);
const teamusercount1 = ref(0);
const LVv = ref('');
const lists = [
    {
        account: 'Alice',
        referrer: 'Bob',
        teamSize: 5,
        assets: 100
    },
    {
        account: 'Bob',
        referrer: 'Eve',
        teamSize: 4,
        assets: 200
    }
]

const fy = ref({
    lv1: '',
    lv2: '',
    lv3: ''
})

const lv1 = ref({
    people: 0
})
const lv2 = ref({
    people: 0
})
const lv3 = ref({
    people: 0
})

const tdata = ref({
    icode: "",
    url: "",
    qrocde: "",
});

const montage = computed(() => {
    return {
        urls: location.origin + '/#/Register?Icode=' + tdata.value.icode,
    };
});

const requesturl1 = ref('c=User&a=team&lv=1')
const requesturl2 = ref('c=User&a=team&lv=2')
const requesturl3 = ref('c=User&a=team&lv=3')


const onPageSuccess = (res: any) => {
    // console.log(res.all)
    tableData.value = res.all
    loadingShow.value = false
    if (cpageRef.value == undefined) {
        cpageRef.value = pageRef.value
    } else if (res.lv == 1) {
        cpageRef.value = pageRef.value
    } else if (res.lv == 2) {
        cpageRef.value = pageRef1.value
    } else if (res.lv == 3) {
        cpageRef.value = pageRef2.value
    }
}

// gettodayregusercount
const onClickTab = (title: any) => {
    switch (title.name) {
        case 0:
            cpageRef.value = pageRef.value
            LVv.value = 'Lv1'
            requesturl1.value = "c=User&a=team&lv=1"
            break;

        case 1:
            cpageRef.value = pageRef1.value
            LVv.value = 'Lv2'
            requesturl2.value = "c=User&a=team&lv=2"
            break;

        case 2:
            cpageRef.value = pageRef2.value
            LVv.value = 'Lv3'
            requesturl3.value = "c=User&a=team&lv=3"
            break;

    }
    if (cpageRef.value != undefined) {
        loadingShow.value = true
        cpageRef.value.doSearch()
    }

};

const onLinkc = (type: string) => {
    cpageRef.value.delall();
    loadingShow.value = true
    if (type == 'pay') {
        atype.value = "pay"
        if (tableData.paycount != 0)
            cpageRef.value.doSearch({ type: atype.value })
        else
            loadingShow.value = false
    } else if (type == 'unpay') {
        atype.value = "unpay"
        if (tableData.unpaycount != 0)
            cpageRef.value.doSearch({ type: atype.value })
        else
            loadingShow.value = false
    }
}

const getusercount = () => {
    http({
        url: 'c=User&a=gettodayregusercount',
    }).then((res: any) => {
        if (res.code != 1) {
            _alert({
                type: 'error',
                message: res.msg,
                onClose: () => {

                }
            })
            return
        }
        teamusercount.value = res.data.count;
        teamusercount1.value = res.data.count1;
    })
}
const getTeam = () => {
    var delayTime = Math.floor(Math.random() * 1000);
    // setTimeout(() => {
        http({
            url: 'c=User&a=GetTeamHierarchyPeopleNum'
        }).then((res: any) => {
            for (var it of res.data.list) {
                if (it.level == "1") {
                    lv1.value.people += 1;
                } else if (it.level == "2") {
                    lv2.value.people += 1;
                } else if (it.level == "3") {
                    lv3.value.people += 1;
                }
            }

            var fylStr = res.data.fy;
            fy.value.lv1 = 'B ' + (fylStr.split(',')[0]).split('=')[1] + '%(' + lv1.value.people + ')';
            fy.value.lv2 = 'C ' + (fylStr.split(',')[1]).split('=')[1] + '%(' + lv2.value.people + ')';
            fy.value.lv3 = 'D ' + (fylStr.split(',')[2]).split('=')[1] + '%(' + lv3.value.people + ')';
        })
    // }, delayTime)
}

onMounted(() => {
    getusercount()
    getTeam()
})

const SwitchMembers = (lv: number, type: number) => {
    loadingShow.value = true

    if (type == 0) {
        requesturl1.value = "c=User&a=team&lv=1&type=pay";
        requesturl2.value = "c=User&a=team&lv=2&type=pay";
        requesturl3.value = "c=User&a=team&lv=3&type=pay";
        cpageRef.value.ValidMember("c=User&a=team&lv=" + lv + "&type=pay");
    }
    else {
        requesturl1.value = "c=User&a=team&lv=1&type=unpay";
        requesturl2.value = "c=User&a=team&lv=2&type=unpay";
        requesturl3.value = "c=User&a=team&lv=3&type=unpay";
        cpageRef.value.ValidMember("c=User&a=team&lv=" + lv + "&type=unpay");
    }
    var InactiveMember = document.getElementsByClassName('levelTabValidMember');
    var levelTabInactiveMember = document.getElementsByClassName('levelTabInactiveMember');
    if (lv == 1) {
        if (type == 0) {
            InactiveMember[0].style.background = "#666";
            InactiveMember[0].style.color = "#fff";
            levelTabInactiveMember[0].style.background = "#fff";
            levelTabInactiveMember[0].style.color = "#84973b";
        }
        else {
            InactiveMember[0].style.background = "#fff";
            InactiveMember[0].style.color = "#84973b";
            levelTabInactiveMember[0].style.background = "#666";
            levelTabInactiveMember[0].style.color = "#fff";
        }
    }
    else if (lv == 2) {
        if (type == 0) {
            InactiveMember[1].style.background = "#666";
            InactiveMember[1].style.color = "#fff";
            levelTabInactiveMember[1].style.background = "#fff";
            levelTabInactiveMember[1].style.color = "#84973b";
        }
        else {
            InactiveMember[1].style.background = "#fff";
            InactiveMember[1].style.color = "#84973b";
            levelTabInactiveMember[1].style.background = "#666";
            levelTabInactiveMember[1].style.color = "#fff";
        }
    }
    else if (lv == 3) {
        if (type == 0) {
            InactiveMember[2].style.background = "#666";
            InactiveMember[2].style.color = "#fff";
            levelTabInactiveMember[2].style.background = "#fff";
            levelTabInactiveMember[2].style.color = "#84973b";
        }
        else {
            InactiveMember[2].style.background = "#fff";
            InactiveMember[2].style.color = "#84973b";
            levelTabInactiveMember[2].style.background = "#666";
            levelTabInactiveMember[2].style.color = "#fff";
        }
    }

}

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
    background: #84973b;
    color: #000;

    .will {
        margin: 1rem;
        border-radius: 10px;
        margin-top: 4rem;
        width: 88%;

        .card {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            font: 10px/20px "Rotobo";

            .item {
                display: flex;
                flex-direction: row-reverse;
                justify-content: space-around;
                width: 48%;
                text-align: center;

                .p1 {
                    font-weight: bold;
                    color: #fff;
                    margin-bottom: 0.4rem;
                    font-size: 12px;
                }

                .p2 {
                    color: #ddd;
                    font-weight: bold;
                }
            }
        }
    }

    .paylogBoxWrapper {
        padding: 0 1rem;
        box-sizing: border-box;



        .list-box {

            .levelTab {
                margin-top: 0.8rem;
                margin-bottom: 3rem;

                :deep(.van-tabs__nav) {
                    background-color: transparent;
                }

                :deep(.van-tabs__content) {
                    margin-top: 1rem;
                }

                :deep(.van-tab) {
                    .van-tab__text {
                        color: #222;
                        font-weight: bold;
                        // padding: 2px 0.575rem;
                        // border-radius: 16px;
                        // border: 1px solid f6f6f6;
                        // background-color: #f6f6f6;
                    }
                }

                :deep(.van-tab--active) {
                    .van-tab__text {
                        color: #84973b;
                        background-color: #fff;
                        padding: 0.2rem 1rem;
                        border-radius: 1.2rem;
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
                    font-size: 0.8rem;
                    position: absolute;
                    top: -5.5rem;
                    display: flex;
                    justify-content: space-evenly;
                    align-items: center;
                    width: 100%;
                    flex-direction: row-reverse;

                    .levelTabValidMember {
                        width: 42%;
                        height: 2rem;
                        line-height: 2rem;
                        float: left;
                        white-space: nowrap;
                        text-align: center;
                        color: #84973b;
                        font-weight: bold;
                        background: #fff;
                        border-radius: 1rem;
                    }

                    .levelTabInactiveMember {
                        width: 42%;
                        height: 2rem;
                        line-height: 2rem;
                        float: right;
                        white-space: nowrap;
                        text-align: center;
                        color: #84973b;
                        font-weight: bold;
                        background: #fff;
                        border-radius: 1rem;
                    }
                }
            }

            .listHead {
                display: flex;
                justify-content: space-between;
                margin-bottom: 1rem;
                color: #fff;


                p {
                    text-align: center;
                    font: bold 14px/16px '微软雅黑';

                    &:nth-child(1) {
                        width: 30%;
                    }

                    &:nth-child(2) {
                        width: 30%;
                    }

                    &:nth-child(3) {
                        width: 30%;
                    }

                    &:nth-child(4) {
                        width: 30%;
                    }
                }
            }

            .listitem {
                display: flex;
                justify-content: space-between;
                margin-bottom: 1rem;
                font-size: 14px;
                color: #fff;

                p {
                    text-align: center;
                    font: bold 14px/16px 'Rotobo';

                    &:nth-child(1) {
                        width: 32%;
                    }

                    &:nth-child(2) {
                        width: 20%;
                    }

                    &:nth-child(3) {
                        width: 30%;
                    }

                    &:nth-child(4) {
                        width: 18%;
                    }
                }

                .plus {
                    display: flex;
                    align-items: center;
                    background: #fff;
                    color: #84973b;
                    padding: 0 3px;
                    font: normal 12px/16px '微软雅黑';
                    border-radius: 4px;
                    margin-left: 2px;
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