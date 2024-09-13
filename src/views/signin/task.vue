<template>
    <div class="mission" style="background-color: #f6f6f6;min-height: 100%;">
        <MyNav leftText="">
        </MyNav>
        <div class="tasktent" style="height:61rem;">
            <div class="back">
                <div class="invition">
                    <div class="invititop"></div>
                    <div style="height: 2rem; margin-top: 0.5rem; text-align: center; line-height: 2rem; color: #63513d; display: flex; justify-content: space-evenly; align-items: center; ">
                        <div style="float: left" class="subscript"></div>
                        <div style="float: left;font-weight:bold;">FIRST INVITATION</div>
                        <div style="float: left" class="subscript"></div>
                    </div>
                    <div class="lisks">
                        <div style="font-size:0.75rem;">
                            Invite friend to complete their first investment and receive a <span style="font-weight: bold; color: #6b5946;">50RS</span> bonus
                        </div>
                        <span class="Receive" @click="clickReciveValidInvitation(1)">Receive</span>
                    </div>
                    <div style="height: 2rem; text-align: center; line-height: 2rem; color: #63513d; display: flex; justify-content: space-evenly; align-items: center; ">
                        <div style="float: left" class="subscript"></div>
                        <div style="float: left; font-weight: bold; ">INVITE TASKS</div>
                        <div style="float: left" class="subscript"></div>
                    </div>
                    <div class="tasklisk">
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:2rem;height:2rem;">Invite 5 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;">
                                        <p> <span style="margin-left: 1rem;"> 0/5 </span><span style="margin-right:1.5rem;">300 RS</span> </p>
                                        <p><span>Cumulative</span> invitation coupon</p>
                                    </div>
                                </div>
                            </div>
                            <span class="Receive" @click="clickReciveValidInvitation(1)">Receive</span>
                        </div>
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:2rem;height:2rem;">Invite 10 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;">
                                        <p> <span style="margin-left: 1rem;"> 0/10 </span><span style="margin-right:1.5rem;">800 RS</span> </p>
                                        <p><span>Cumulative</span> invitation coupon</p>
                                    </div>
                                </div>
                            </div>
                            <span class="Receive" @click="clickReciveValidInvitation(2)">Receive</span>
                        </div>
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:2rem;height:2rem;">Invite 15 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;">
                                        <p> <span style="margin-left: 1rem;"> 0/15 </span><span style="margin-right:1.5rem;">1500 RS</span> </p>
                                        <p><span>Cumulative</span> invitation coupon</p>
                                    </div>

                                </div>
                            </div>
                            <span class="Receive" @click="clickReciveValidInvitation(3)">Receive</span>
                        </div>
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:2rem;height:2rem;"> Invite 20 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;">
                                        <p> <span style="margin-left: 1rem;"> 0/20 </span><span style="margin-right:1.5rem;">3000 RS</span> </p>
                                        <p><span>Cumulative</span> invitation coupon</p>
                                    </div>
                                </div>
                            </div>
                            <span class="Receive" @click="clickReciveValidInvitation(4)">Receive</span>
                        </div>
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:2rem;height:2rem;"> Invite 50 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;">
                                        <p> <span style="margin-left: 1rem;"> 0/50 </span><span style="margin-right:1.5rem;">8000 RS</span> </p>
                                        <p><span>Cumulative</span>discount coupon</p>
                                    </div>
                                </div>
                            </div>
                            <span class="Receive" @click="clickReciveValidInvitation(5)">Receive</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>
<script lang="ts">
    //import { alert, lang } from "../../global/common";
    import { defineComponent, ref, reactive, onMounted, computed, toRaw, toRefs } from 'vue'
    import { Button, Form, Field, CellGroup } from 'vant'
    import { Icon, Calendar } from 'vant'
    import { _alert, lang, getSrcUrl, goRoute, cutOutNum } from '../../global/common'
    import MyNav from '../../components/Nav.vue'
    import MyTab from '../../components/Tab.vue'
    import http from '../../global/network/http'
    import MyLoading from "../../components/Loading.vue";

    import { useRouter } from 'vue-router'

    export default defineComponent({
        components: {
            MyNav, MyLoading,
            [Button.name]: Button,
            [Form.name]: Form,
            [Field.name]: Field,
            [CellGroup.name]: CellGroup,
            [Icon.name]: Icon,
        },
    })
</script>
  
<script lang="ts" setup>
    import taskicon1 from '../../assets/img/signin/taskicon1.png'
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();
    const router = useRouter()
    const loadtitle = ref("Loading...")
    const loadingShow = ref(false);


    const pageData = ref(null)
    let isRequest = false
    // 获取页面初始化数据
    const initPageData = () => {
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => {
            http({
                url: "ext_inviteTask/pageData",
                data: {},
                method: "GET"
            }).then((res: any) => {
                pageData.value = res.data
            })
        }, delayTime)

    }

    //有效邀请奖
    const clickReciveValidInvitation = (level) => {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => {
            http({
                url: "ext_inviteTask/reciveValidInvitation?level=" + level,
                method: "GET"
            }).then((res: any) => {
                loadingShow.value = false;
                if (res.code != 200 && res.code != 204) {
                    _alert(res.message)
                    isRequest = false
                    return;
                }
                initPageData()
                _alert("Successfully", function () {
                    isRequest = false
                });
            })
        }, delayTime)
    }


    onMounted(() => {
        initPageData()
    })
</script>
<style lang="scss" scoped>
    .myNavBar {
        height: 45px !important;

        :deep(.van-nav-bar) {
            background-color: #64523e;
        }

        :deep(.van-nav-bar__title) {
            .alter {
                color: #fff !important;
                font-weight: bold;
            }
        }
    }

    .tasktent {
        padding: 1rem 1rem 0;
        background-color: #fff;

        .back {
            // background-color: rgb(255, 255, 255);
            // padding: 1rem;
            // border-radius: 8px;
        }

        .invites {
            .inviteimg {
                margin: 1.2rem 0;
            }

            .money {
                display: flex;
                justify-content: space-around;

                div {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;

                    p {
                        margin: 0.3rem 0;
                        font-size: 1.2rem;
                        color: #ff9900;
                        font-weight: bold;
                    }

                    span {
                        font-size: 0.8rem;
                        font-weight: 500;
                        color: #aaa;
                    }
                }
            }
        }

        .flrst {
            border: 1px solid #222;
            border-radius: 8px;
            padding: 0.5rem;
            margin-top: 1rem;

            .flrsttop {
                display: flex;
                justify-content: space-evenly;
                align-items: center;
                margin: 0.6rem auto;
                width: 90%;

                p {
                    color: #000;
                    font-weight: bold;
                    font-size: 16px;
                }
            }

            .flrstbot {
                display: flex;
                align-items: center;
                height: 3.5rem;
                padding-bottom: 1rem;
                justify-content: space-around;

                p {
                    font: bold 12px/18px "微软雅黑";
                    margin-left: 0.5rem;
                    width: 60%;
                }

                span:nth-child(2) {
                    background-color: #222;
                    padding: 0.2rem 0.4rem;
                    border-radius: 16px;
                    color: #fff;
                }
            }
        }

        .invition {
            .invititop {
                background: url("../../assets/img/signin/task_top.png") no-repeat left center/100% 100%;
                height: 7rem;
                width: 100%;

                span {
                    display: block;
                    width: 30px;
                    height: 4px;
                    border-radius: 4px;
                    background: linear-gradient(to right, #fff, #4eb848);
                }
                // span:first-child {}
                span:last-child {
                    background: linear-gradient(to left, #fff, #4eb848);
                }

                p {
                    color: #333;
                    font-weight: bold;
                    font-size: 16px;
                }
            }

            .subscript {
                background: url("../../assets/img/signin/taskicon2.png") no-repeat left center/100% 100%;
                width: 1.5rem;
                height: 1.5rem;
            }

            .lisks:last-child {
                border: none;
            }

            .lisks {
                padding: 1rem 0.5rem;
                display: flex;
                align-items: center;
                justify-content: space-around;
                margin: 1rem auto;
                border: 1px solid #ddd;
                border-radius: 10px;
                box-shadow: 0px 0px 6px #c6c2c2;

                .p1 {
                    display: flex;
                    align-items: center;
                    font-weight: bold;
                    font-size: 14px;
                    color: #6b5946;

                    img {
                        width: 1rem;
                        height: 1rem;
                        margin-right: 0.6rem;
                    }
                }

                .liskbox {
                    display: flex;
                    flex-direction: row;
                    font-size: 12px;
                    margin-top: 0.5rem;
                    line-height: 18px;
                    color: #666;

                    img {
                        width: 3rem;
                        height: 3rem;
                        margin-right: 0.6rem;
                    }

                    p {
                        display: flex;
                        justify-content: space-between;
                        color: #aaa;
                        width: 100%;
                        font-size: 0.68rem;
                    }

                    p:nth-child(1) {
                        color: #6b5946;
                        font-weight: bold;
                    }

                    span {
                        display: block;
                    }

                    .progress {
                        height: 100%;
                        background-color: rgb(255, 135, 38);
                        border-radius: 10px;
                    }
                }

                .Receive {
                    background: linear-gradient(to right,#c59b6c, #bc9264, #aa8056);
                    padding: 0.2rem 0.4rem;
                    border-radius: 5px;
                    color: #fff;
                }
            }
        }
    }
</style>
  