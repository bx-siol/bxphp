<template>
    <div class="mission" style="background-color: #f6f6f6;min-height: 100%;">
        <MyNav leftText=""></MyNav>
        <div class="tasktent" style="height:61rem;">
            <div class="back">
                <div class="invition">
                    <div style="height: 2rem; text-align: center; line-height: 2rem; color: white; display: flex; justify-content: space-evenly; align-items: center;background-color: #cc1700; border-radius: 30px;">
                        <div style="font-weight: bold; ">INVITE TASKS</div>
                    </div>
                    <div class="tasklisk">
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:1rem;height:1rem;">Invite 5 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;display: flex;width: 17rem;">
                                        <img :src="taskicon2" style="width:3rem;height:3rem;">
                                        <div>
                                            <p> 
                                                <span style="margin-left: 1rem;color: #cc1700;"> {{ InviteesNum >= 5 ?'5' : (InviteesNum < 5 ? InviteesNum : '0') }}/5 </span>
                                                <span style="margin-right:1.5rem;color: #cc1700;">300 RS</span> 
                                            </p>
                                            <div style="height: 0.3rem;background-color: #d1d1d1;width: 12rem;border-radius: 5px;"></div>
                                            <div :style="ProgressBar(5)" class="ProgressBar"></div>
                                            <p><span>Quantity</span> invitation Bonus</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span :style="InviteesNum >= 5 ? 'background-color: #cc1700;':''" class="Receive"  @click=" InviteesNum >= 5 ? clickReciveValidInvitation(1) : '' ">Receive</span>
                        </div>
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:1rem;height:1rem;">Invite 10 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;display: flex;width: 17rem;">
                                        <img :src="taskicon2" style="width:3rem;height:3rem;">
                                        <div>
                                            <p> 
                                                <span style="margin-left: 1rem;color: #cc1700;"> {{ InviteesNum >= 10 ?'10' : (InviteesNum < 10 ? InviteesNum : '0') }}/10 </span>
                                                <span style="margin-right:1.5rem;color: #cc1700;">800 RS</span> 
                                            </p>
                                            <div style="height: 0.3rem;background-color: #d1d1d1;width: 12rem;border-radius: 5px;"></div>
                                            <div :style="ProgressBar(10)" class="ProgressBar"></div>
                                            <p><span>Quantity</span> invitation Bonus</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span :style="InviteesNum >= 10 ? 'background-color: #cc1700;':''" class="Receive" @click=" InviteesNum >= 10 ? clickReciveValidInvitation(2) : '' ">Receive</span>
                        </div>
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:1rem;height:1rem;">Invite 15 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;display: flex;width: 17rem;">
                                        <img :src="taskicon2" style="width:3rem;height:3rem;">
                                        <div>
                                            <p> 
                                                <span style="margin-left: 1rem;color: #cc1700;"> {{ InviteesNum >= 15 ?'15' : (InviteesNum < 15 ? InviteesNum : '0') }}/15 </span>
                                                <span style="margin-right:1.5rem;color: #cc1700;">1500 RS</span> 
                                            </p>
                                            <div style="height: 0.3rem;background-color: #d1d1d1;width: 12rem;border-radius: 5px;"></div>
                                            <div :style="ProgressBar(15)" class="ProgressBar"></div>
                                            <p><span>Quantity</span> invitation Bonus</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span :style="InviteesNum >= 15 ? 'background-color: #cc1700;':''" class="Receive" @click=" InviteesNum >= 15 ? clickReciveValidInvitation(3) : '' ">Receive</span>
                        </div>
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:1rem;height:1rem;"> Invite 20 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;display: flex;width: 17rem;">
                                        <img :src="taskicon2" style="width:3rem;height:3rem;">
                                        <div>
                                            <p> 
                                                <span style="margin-left: 1rem;color: #cc1700;"> {{ InviteesNum >= 20 ?'20' : (InviteesNum < 20 ? InviteesNum : '0') }}/20 </span>
                                                <span style="margin-right:1.5rem;color: #cc1700;">2500 RS</span> 
                                            </p>
                                            <div style="height: 0.3rem;background-color: #d1d1d1;width: 12rem;border-radius: 5px;"></div>
                                            <div :style="ProgressBar(20)" class="ProgressBar"></div>
                                            <p><span>Quantity</span> invitation Bonus</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span :style="InviteesNum >= 20 ? 'background-color: #cc1700;':''" class="Receive" @click=" InviteesNum >= 20 ? clickReciveValidInvitation(4) : '' ">Receive</span>
                        </div>
                        <div class="lisks">
                            <div>
                                <p class="p1">
                                    <img :src="taskicon1" style="width:1rem;height:1rem;"> Invite 50 friends to Register
                                </p>
                                <div class="liskbox">
                                    <div style="width: 12.5rem; overflow: hidden;display: flex;width: 17rem;">
                                        <img :src="taskicon2" style="width:3rem;height:3rem;">
                                        <div>
                                            <p>
                                                 <span style="margin-left: 1rem;color: #cc1700;"> {{ InviteesNum >= 50 ?'50' : (InviteesNum < 50 ? InviteesNum : '0') }}/50 </span>
                                                 <span style="margin-right:1.5rem;color: #cc1700;">8000 RS</span> 
                                                </p>
                                            <div style="height: 0.3rem;background-color: #d1d1d1;width: 12rem;border-radius: 5px;"></div>
                                            <div :style="ProgressBar(50)" class="ProgressBar"></div>
                                            <p><span>Quantity</span> invitation Bonus</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span :style="InviteesNum >= 50 ? 'background-color: #cc1700;':''" class="Receive" @click=" InviteesNum >= 50 ? clickReciveValidInvitation(5) : '' ">Receive</span>
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
    import taskicon2 from '../../assets/img/signin/taskicon2.png'
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();
    const router = useRouter()
    const loadtitle = ref("Loading...")
    const loadingShow = ref(false);
    const InviteesNum = ref(0)


    const pageData = ref(null)
    let isRequest = false
    // 获取页面初始化数据
    const initPageData = () => {
        http({
            url: "c=Ext&a=pageData",
            data: {},
        }).then((res: any) => {
            InviteesNum.value = res.data.newmember
        })
    }

    const ProgressBar = (num:number)=>{
        return 'width:'+ (InviteesNum.value >=num ? num : InviteesNum.value) /num*100 +'%';
    }

    //有效邀请奖
    const clickReciveValidInvitation = (level) => {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        http({
            url: "c=Ext&a=reciveValidInvitation",
            data: {type:level},
        }).then((res: any) => {
            loadingShow.value = false;
            isRequest = false
            if (res.code != 200 && res.code != 204) {
                _alert(res.msg)
                return;
            }
            _alert(res.msg);
        })
    }


    onMounted(() => {
        initPageData()
    })
</script>
<style lang="scss" scoped>
    .myNavBar {
        height: 45px !important;
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

            .lisks:last-child {
                border: none;
            }

            .lisks {
                padding: 1rem 0.5rem;
                display: flex;
                align-items: center;
                justify-content: space-around;
                border-bottom: 1px solid #ddd;

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

                    .ProgressBar{
                        height: 0.3rem;
                        background-color: #cc1700;
                        position: relative;
                        top: -6px;
                        border-radius: 5px;
                    }
                }

                .Receive {
                    background: black;
                    padding: 0.2rem 0.4rem;
                    border-radius: 5px;
                    color: #fff;
                }
            }
        }
    }
</style>
  