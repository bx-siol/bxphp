<template>
    <div class="paylogBox" style="        height: 100%;
        overflow-y: auto;">
        <Nav leftText=''>
            <template #left>
                <div></div>
            </template>
        </Nav>
        <div class="will">
            <div class="card">
                <div>
                    <vue-qrcode v-if="false" style="        width: 28vw;
        margin: auto;
" :value="montage.urls"></vue-qrcode>
                    <div class="topItem">
                        <div>{{ teamcount }}</div>
                        <div>{{t('所有成员')}}</div>
                    </div>
                    <div class="topItem">
                        <div>{{ recharge }}</div>
                        <div>{{ t('今日加入')}}</div>
                    </div>
                    <div class="topItem">
                        <div>{{ teamcount }}</div>
                        <div>{{t('有效成员')}}</div>
                    </div>
                    <div class="topItem">
                        <div>{{ recharge }}</div>
                        <div>{{ t('无效成员')}}</div>
                    </div>
                    <div class="topItem">
                        <div>{{ teamcount }}</div>
                        <div>{{t('日邀请')}}</div>
                    </div>
                    <div class="topItem">
                        <div>{{ recharge }}</div>
                        <div>{{ t('团队规模')}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div style="padding-left: 4vw; margin-bottom: 1vh;margin-top:2vh">{{t('邀请码')}}</div>
        <div style=" font-size: .7rem; border: 1px solid #db100096; border-radius: 6px; width: 90%; margin: auto; height: 5vh; line-height: 5vh; padding-left: 1vw;">
            <div style=" display: inline-block; width: 76vw; overflow: scroll; height: 2vh; line-height: 2.5vh; font-size: .8rem;">{{ tdata.icode }}</div>
            <div @click="copyToClipboard(tdata.icode)" style=" display: inline-block; width: 12vw; height: 5vh; line-height: 5vh; border-radius: 6px; background: rgb(235 23 0); color: white; text-align: center; float: right;">{{t('复制')}}</div>
        </div>
        <div style="padding-left: 4vw; margin-top: 2vh; margin-bottom: 1vh;">{{t('邀请链接')}}</div>
        <div style=" font-size: .7rem; border: 1px solid #db100096; border-radius: 6px; width: 90%; margin: auto; height: 5vh; line-height: 5vh; padding-left: 1vw;">
            <div style=" display: inline-block; width: 76vw; overflow: scroll; height: 2vh; line-height: 2.5vh; font-size: .8rem;">{{ montage.urls }}</div>
            <div @click="copyToClipboard(montage.urls)" style=" display: inline-block; width: 12vw; height: 5vh; line-height: 5vh; border-radius: 6px; background: rgb(235 23 0); color: white; text-align: center; float: right;">{{t('复制')}}</div>
        </div>

        <div class="paylogBoxWrapper">
            <div class="list-box">
                <van-tabs @click-tab="onClickTab" line-height="0" v-model:active="active" class="levelTab">
                    <van-tab :title="fy.lv1">
                        <div class="levelTabMember">
                            <div class="levelTabValidMember" @click="SwitchMembers(1, 0)">{{ t('有效成员') }}</div>
                            <div class="levelTabInactiveMember" @click="SwitchMembers(1, 1)">{{ t('无效成员') }}</div>
                        </div>
                        <MyListBase :url="requesturl1" ref="pageRef" @success="onPageSuccess">
                            <template #default="{ list }">
                                <table>
                                    <thead>
                                        <tr class="listHead">
                                            <th>{{ t('用户名') }}</th>
                                            <th>{{ t('等级') }}</th>
                                            <th>{{ t('时间') }}</th>
                                            <th>{{ t('资产') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="listitem" v-for="(item, index) in list" :key="index">
                                            <td>{{ item.account }}</td>
                                            <td>{{ item.level }}</td>
                                            <td>{{ item.reg_time }}</td>
                                            <td>
                                                {{ item.assets }}RS
                                                <span class="plus">
                                                    <!--<van-icon @click="onLink({ name: 'User_teamlist', params: { id: item.id } })"
                                                name="arrow"></van-icon>-->
                                                </span>
                                            </td>
                                            <td>
                                                <van-image @click="onLink({ name: 'User_teamlist', params: { id: item.id } })" :src="m1" style="        width: 1.2vw;"></van-image>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                        </MyListBase>
                    </van-tab>
                    <van-tab :title="fy.lv2">
                        <div class="levelTabMember">
                            <div class="levelTabValidMember" @click="SwitchMembers(2, 0)">{{ t('有效成员') }}</div>
                            <div class="levelTabInactiveMember" @click="SwitchMembers(2, 1)">{{ t('无效成员') }}</div>
                        </div>
                        <MyListBase :url="requesturl2" ref="pageRef1" @success="onPageSuccess">
                            <template #default="{ list }">
                                <table>
                                    <thead>
                                        <tr class="listHead">
                                            <th>{{ t('用户名') }}</th>
                                            <th>{{ t('等级') }}</th>
                                            <th>{{ t('时间') }}</th>
                                            <th>{{ t('资产') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="listitem" v-for="(item, index) in list" :key="index">
                                            <td>{{ item.account }}</td>
                                            <td>{{ item.level }}</td>
                                            <td>{{ item.reg_time }}</td>
                                            <td>
                                                {{ item.assets }}RS
                                                <span class="plus">
                                                    <!--<van-icon
                                                @click="onLink({ name: 'User_teamlist', params: { id: item.id } })"
                                                name="arrow"></van-icon>-->
                                                </span>
                                            </td>
                                            <td>
                                                <van-image @click="onLink({ name: 'User_teamlist', params: { id: item.id } })" :src="m1" style="        width: 1.2vw;"></van-image>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                        </MyListBase>
                    </van-tab>
                    <van-tab :title="fy.lv3">
                        <div class="levelTabMember">
                            <div class="levelTabValidMember" @click="SwitchMembers(3, 0)">{{ t('有效成员') }}</div>
                            <div class="levelTabInactiveMember" @click="SwitchMembers(3, 1)">{{ t('无效成员') }}</div>
                        </div>
                        <MyListBase :url="requesturl3" ref="pageRef2" @success="onPageSuccess">
                            <template #default="{ list }">
                                <table>
                                    <thead>
                                        <tr class="listHead">
                                            <th>{{ t('用户名') }}</th>
                                            <th>{{ t('等级') }}</th>
                                            <th>{{ t('时间') }}</th>
                                            <th>{{ t('资产') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="listitem" v-for="(item, index) in list" :key="index">
                                            <td>{{ item.account }}</td>
                                            <td>{{ item.level }}</td>
                                            <td>{{ item.reg_time }}</td>
                                            <td>
                                                {{ item.assets }}RS
                                                <!--<span class="plus">
                                                <van-icon
                                                    @click="onLink({ name: 'User_teamlist', params: { id: item.id } })"
                                                    name="arrow"></van-icon>
                                            </span>-->
                                            </td>
                                            <td>
                                                <van-image @click="onLink({ name: 'User_teamlist', params: { id: item.id } })" :src="m1" style="        width: 1.2vw;"></van-image>
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
    </div>
    <MyTab></MyTab>
    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
    import { _alert, lang, goRoute } from "../../global/common"
    import { defineComponent, onMounted, reactive, ref, computed } from 'vue';
    import { Image } from 'vant';
    import Nav from '../../components/Nav.vue';
    import MyTab from "../../components/Tab.vue";
    import MyListBase from '../../components/ListBase.vue';
    import Service from '../../components/service.vue';
    import MyLoading from '../../components/Loading.vue';
    import { Button, Tab, Tabs, Grid, GridItem, Cell, Field, Icon, Image as VanImage } from "vant";
    import { type } from 'os';
    import m1 from '/src/assets/img/team/rightArrow.png'
    import VueQrcode from 'vue-qrcode'
    export default defineComponent({
        components: {
            Nav, MyTab, MyListBase, MyLoading,
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


    //onMounted(() => {
    //    // }, delayTime)

    //})
</script>
<script lang="ts" setup>
    import { getSrcUrl, imgPreview, copy } from "../../global/common";
    import http from "../../global/network/http";
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();

    let isRequest = false
    const active = ref(0)
    type level = {
        img: string,
        numbering: string,
        number: number | string
    }
    const onLink = (to: any) => {
        goRoute(to)
    }
    const loadingShow = ref(true)
    const pageRef = ref()
    const pageRef1 = ref()
    const pageRef2 = ref()
    const cpageRef = ref()

    const lv = ref()
    const tableData = ref<any>({})
    const atype = ref();
    const teamusercount = ref(0);
    const teamusercount1 = ref(0);
    const teamcount = ref(0)
    const recharge = ref(0)
    const LVv = ref('');
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


    const requesturl1 = ref('c=User&a=team&lv=1')
    const requesturl2 = ref('c=User&a=team&lv=2')
    const requesturl3 = ref('c=User&a=team&lv=3')

    const linkCopyRef = ref()

    const montage = computed(() => {
        return {
            urls: location.origin + '/#/Register?Icode=' + tdata.value.icode,
        };
    });


    const onPageSuccess = (res: any) => {
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
            fy.value.lv1 = 'B ' + (fylStr.split(',')[0]).split('=')[1] + '%-(' + lv1.value.people + ')';
            fy.value.lv2 = 'C ' + (fylStr.split(',')[1]).split('=')[1] + '%-(' + lv2.value.people + ')';
            fy.value.lv3 = 'D ' + (fylStr.split(',')[2]).split('=')[1] + '%-(' + lv3.value.people + ')';
            teamcount.value = lv1.value.people + lv2.value.people + lv3.value.people;

        })
        // }, delayTime)
    }

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
                InactiveMember[0].style.background = "#ccc";
                InactiveMember[0].style.color = "#002544";
                levelTabInactiveMember[0].style.background = "#db1000";
                levelTabInactiveMember[0].style.color = "#fff";
            }
            else {
                InactiveMember[0].style.background = "#db1000";
                InactiveMember[0].style.color = "#fff";
                levelTabInactiveMember[0].style.background = "#ccc";
                levelTabInactiveMember[0].style.color = "#002544";
            }
        }
        else if (lv == 2) {
            if (type == 0) {
                InactiveMember[1].style.background = "#ccc";
                InactiveMember[1].style.color = "#002544";
                levelTabInactiveMember[1].style.background = "#db1000";
                levelTabInactiveMember[1].style.color = "#fff";
            }
            else {
                InactiveMember[1].style.background = "#db1000";
                InactiveMember[1].style.color = "#fff";
                levelTabInactiveMember[1].style.background = "#ccc";
                levelTabInactiveMember[1].style.color = "#002544";
            }
        }
        else if (lv == 3) {
            if (type == 0) {
                InactiveMember[2].style.background = "#ccc";
                InactiveMember[2].style.color = "#002544";
                levelTabInactiveMember[2].style.background = "#db1000";
                levelTabInactiveMember[2].style.color = "#fff";
            }
            else {
                InactiveMember[2].style.background = "#db1000";
                InactiveMember[2].style.color = "#fff";
                levelTabInactiveMember[2].style.background = "#ccc";
                levelTabInactiveMember[2].style.color = "#002544";
            }
        }
    }

    onMounted(() => {
        getusercount()
        getTeam();

        const delayTime = Math.floor(Math.random() * 1000);
        // setTimeout(() => {
        http({
            url: 'c=Share&a=index'
        }).then((res: any) => {
            tdata.value = res.data
            copy(linkCopyRef.value.$el, {
                text: (target: HTMLElement) => {
                    return montage.value.urls.toLocaleLowerCase()
                }
            })
        })

    })

    const copyToClipboard = (text) => {
        var textarea = document.createElement('textarea');
        textarea.style.position = 'fixed';
        textarea.style.opacity = 0;
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        _alert('Copy succeeded')
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
        :deep(.van-tab) {
            padding: 0;

            &.van-tab--active {
                position: relative;
                background-color: transparent;
                height: 91%;
                // &::after {
                //     position: absolute;
                //     bottom: -0.3rem;
                //     content: ' ';
                //     border: 2px solid #00b57e;
                //     width: 1rem;
                //     border-radius: 6px;
                //     // border-top: 0.5rem solid ;
                // }
            }

            .van-tab__text {
                border: none !important;
                color: black !important;
                padding: 0.7rem 0.4rem;
                white-space: nowrap;
                width: 6rem;
                border-radius: 4rem;
                text-align: center;
            }
        }

        :deep(.van-tab--grow:first-of-type) {
            margin-left: -1rem;
        }

        :deep(.van-tab--active) {
            .van-tab__text {
                border: none !important;
                background-color: #e11600 !important;
                color: #fff !important;
                padding: 0.8rem 0.6rem;
                white-space: nowrap;
                width: 6rem;
                border-radius: 4rem;
                text-align: center;
            }
        }

        :deep(.van-tabs__line) {
            display: none;
            background-color: #fff;
        }

        :deep(.van-tabs__nav--line) {
            padding-top: 0.425rem;
        }

        :deep(.van-tabs__nav) {
            border-radius: 9rem;
            padding: 0rem !important;
            border: 1px solid #eb1700;
            height: 90%;
        }
    }

    .topItem {
        display: inline-block;
        padding: 1vh 2vw;
        font-size: 0.6rem;
        width: 27%;

        div:nth-child(1) {
            font-size: 1.2rem;
        }
    }

    .paylogBox {
        background: #fff;
        color: #000;

        .will {
            margin: 1.1rem;
            background: linear-gradient(to right, #fff 20%, #fff);
            box-shadow: 0px 0px 12px 2px rgb(235, 235, 235);
            border-radius: 6px;

            .card {
                font: 16px/20px "Rotobo";
                text-align: center;
                padding: 3vh 3vw;
                background: #eb1700;
                color: white;
                border-radius: 5px;

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
            padding: 0 1rem;
            box-sizing: border-box;

            .levelTab {
                margin-top: 1.45rem;
                margin-bottom: 3rem;


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
                        border-radius: 28px;
                        width: 26vw;
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
                    margin: 1rem 0 0.6rem 5%;
                    font-size: 0.8rem;

                    .levelTabValidMember {
                        width: 45%;
                        height: 2rem;
                        line-height: 2rem;
                        float: left;
                        border-radius: 10px;
                        text-align: center;
                        background: #db1000;
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
                        background: #db1000;
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
                /*       top: -4.6rem;
                position: absolute;*/
                width: 100%;
            }

            .myListBox {
                display: flex;
                flex-direction: column;

                .listHead {
                    font: bold 14px/20px 'Rotobo';
                    color: #db1000;
                }

                .listitem {
                    font: bold 12px/32px 'Rotobo';

                    td {
                        text-align: center;
                    }

                    .plus {
                        display: inline-block;
                        background: #A2754C;
                        color: #fff;
                        padding: 0 4px;
                        font: normal 10px/16px '微软雅黑';
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