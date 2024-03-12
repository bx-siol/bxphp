<template>
    <div class="paylogBox" style="height: 100%;overflow-y: auto;">
        <Nav leftText=''></Nav>
        <div class="will">
            <div class="card">

                <div class="item" v-if="memberstatus === 1">
                    <p class="p1"> {{ tableData.unpaycount }}</p>
                    <p class="p1">Unrecharge Member</p>
                </div>
                <div class="item" v-else>
                    <p class="p1">{{ tableData.paycount }}</p>
                    <p class="p1">Active Member</p>
                </div>

            </div>
        </div>

        <div class="paylogBoxWrapper">
            <div class="list-box">
                <van-tabs @click-tab="onClickTab" line-height="0" v-model:active="active" class="levelTab">
                    <van-tab title="B 10%">
                        <div class="levelTabMember">
                            <div class="levelTabInactiveMember" @click="SwitchMembers(1, 1)">{{ t('Unrecharge Member') }}</div>
                            <div class="levelTabValidMember" @click="SwitchMembers(1, 0)">{{ t('Active Member') }}</div>
                        </div>
                        <MyListBase :url="requesturl1" ref="pageRef" @success="onPageSuccess">
                            <template #default="{ list }">
                                <div class="listHead">
                                    <p>{{ t('用户名') }}</p>
                                    <p>{{ t('推荐人') }}</p>
                                    <p>{{ t('团队规模') }}</p>
                                    <p>{{ t('资产') }}</p>
                                </div>
                                <div class="listitem" v-for="(item, index) in list" :key="index">
                                    <!-- <img :src="nestie"> -->
                                    <p>{{ item.account }}</p>
                                    <p>{{ item.referrer }}</p>
                                    <p>{{ item.teamSize }}</p>
                                    <p>{{ item.assets }} </p>
                                    <span class="plus"><van-icon name="arrow"></van-icon></span>
                                </div>
                            </template>
                        </MyListBase>
                    </van-tab>
                    <van-tab title="C 5%">
                        <div class="levelTabMember">
                            <div class="levelTabInactiveMember" @click="SwitchMembers(2, 1)">{{ t('Unrecharge Member') }}</div>
                            <div class="levelTabValidMember" @click="SwitchMembers(2, 0)">{{ t('Active Member') }}</div>
                        </div>
                        <MyListBase :url="requesturl2" ref="pageRef1" @success="onPageSuccess">
                            <template #default="{ list }">
                                <div class="listHead">
                                    <p>{{ t('用户名') }}</p>
                                    <p>{{ t('推荐人') }}</p>
                                    <p>{{ t('团队规模') }}</p>
                                    <p>{{ t('资产') }}</p>
                                </div>
                                <div class="listitem" v-for="(item, index) in list" :key="index">
                                    <p>{{ item.account }}</p>
                                    <p>{{ item.referrer }}</p>
                                    <p>{{ item.teamSize }}</p>
                                    <p>{{ item.assets }}</p>
                                    <span class="plus"><van-icon name="arrow"></van-icon></span>
                                </div>
                            </template>
                        </MyListBase>
                    </van-tab>
                    <van-tab title="D 2%">
                        <div class="levelTabMember">
                            <div class="levelTabInactiveMember" @click="SwitchMembers(3, 1)">{{ t('Unrecharge Member') }}</div>
                            <div class="levelTabValidMember" @click="SwitchMembers(3, 0)">{{ t('Active Member') }}</div>
                        </div>
                        <MyListBase :url="requesturl3" ref="pageRef2" @success="onPageSuccess">
                            <template #default="{ list }">
                                <div class="listHead">
                                    <p>{{ t('用户名') }}</p>
                                    <p>{{ t('推荐人') }}</p>
                                    <p>{{ t('团队规模') }}</p>
                                    <p>{{ t('资产') }}</p>
                                </div>
                                <div class="listitem" v-for="(item, index) in list" :key="index">
                                    <p>{{ item.account }}</p>
                                    <p>{{ item.referrer }}</p>
                                    <p>{{ item.teamSize }}</p>
                                    <p>{{ item.assets }} </p>
                                    <span class="plus"><van-icon name="arrow"></van-icon></span>
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
        Nav, MyListBase,
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

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const imgShow = (src: string) => {
    imgPreview(src)
}

const doService = () => {
    console.log('im service')
}
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

const linkCopyRef = ref();
const linkCopyRefCode = ref();


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
onMounted(() => {
    getusercount()
})

const SwitchMembers = (lv: number, type: number) => {
    memberstatus.value = type;
    // loadingShow.value = true
    
    // if (type == 0) {
    //     requesturl1.value = "GetTeamValidMember?lv=1";
    //     requesturl2.value = "GetTeamValidMember?lv=2";
    //     requesturl3.value = "GetTeamValidMember?lv=2";
    //     cpageRef.value.ValidMember("GetTeamValidMember?lv=" + lv);
    // }
    // else {
    //     requesturl1.value = "GetTeamInactiveMember?lv=1";
    //     requesturl2.value = "GetTeamInactiveMember?lv=2";
    //     requesturl3.value = "GetTeamInactiveMember?lv=2";
    //     cpageRef.value.ValidMember("GetTeamInactiveMember?lv=" + lv);
    // }

    var InactiveMember = document.getElementsByClassName('levelTabValidMember');
    var levelTabInactiveMember = document.getElementsByClassName('levelTabInactiveMember');
    if (lv == 1) {
        if (type == 0) {
            InactiveMember[0].style.borderBottom = "2px solid #fff";
            InactiveMember[0].style.color = "#fff";

            levelTabInactiveMember[0].style.borderBottom = "none";
            levelTabInactiveMember[0].style.color = "#424242";
        }
        else {
            InactiveMember[0].style.borderBottom = "none";
            InactiveMember[0].style.color = "#424242";

            levelTabInactiveMember[0].style.borderBottom = "2px solid #fff";
            levelTabInactiveMember[0].style.color = "#fff";
        }
    }
    else if (lv == 2) {
        if (type == 0) {
            InactiveMember[1].style.borderBottom = "2px solid #fff";
            InactiveMember[1].style.color = "#fff";

            levelTabInactiveMember[1].style.borderBottom = "none";
            levelTabInactiveMember[1].style.color = "#424242";
        }
        else {
            InactiveMember[1].style.borderBottom = "none";
            InactiveMember[1].style.color = "#424242";

            levelTabInactiveMember[1].style.borderBottom = "2px solid #fff";
            levelTabInactiveMember[1].style.color = "#fff";
        }
    }
    else if (lv == 3) {
        if (type == 0) {
            InactiveMember[2].style.borderBottom = "2px solid #fff";
            InactiveMember[2].style.color = "#fff";

            levelTabInactiveMember[2].style.borderBottom = "none";
            levelTabInactiveMember[2].style.color = "#424242";
        }
        else {
            InactiveMember[2].style.borderBottom = "none";
            InactiveMember[2].style.color = "#424242";

            levelTabInactiveMember[2].style.borderBottom = "2px solid #fff";
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
                width: 50%;
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
                    width: 90%;
                    height: 2rem;
                    margin: 1rem 0 1rem 5%;
                    font-size: 0.8rem;
                    position: absolute;
                    top: -7rem;

                    .levelTabValidMember {
                        width: 32%;
                        height: 2rem;
                        line-height: 2rem;
                        float: left;
                        white-space: nowrap;
                        text-align: center;
                        color: #424242;
                        font-weight: bold;
                    }

                    .levelTabInactiveMember {
                        width: 42%;
                        height: 2rem;
                        line-height: 2rem;
                        float: right;
                        white-space: nowrap;
                        text-align: center;
                        color: #424242;
                        font-weight: bold;
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
                        width: 35%;
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
                        width: 20%;
                    }

                    &:nth-child(4) {
                        width: 28%;
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