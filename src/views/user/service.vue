<template>
    <div class="service_page">
        <MyNav leftText=''> </MyNav>
        <div class="content" v-if="false">
            <van-tabs @click-tab="onClickTab" line-height="0" v-model:active="active" class="levelTab">
                <van-tab title="WhatsApp">
                    <MyListBase :url="'GetService_Online?type=2'" ref="pageRef1" @success="onPageSuccess">
                        <template #default="{ list }">
                            <div class="listitem flex" v-for="(item, index) in list" :key="index">
                                <van-image :src="imgFlag(item.portrait)" style="width: 3.6rem;"></van-image>
                                <div class="flex info">
                                    <div>
                                        <p>{{ item.name }}</p>
                                        <p class="desc">{{ item.account }}</p>
                                    </div>
                                    <van-button class="infoBtn" style=""
                                        @click="OnLink(item.account, 0)">Consult</van-button>
                                </div>
                            </div>
                        </template>
                    </MyListBase>
                </van-tab>
                <van-tab title="Telegram">
                    <MyListBase :url="'GetService_Online?type=1'" ref="pageRef" @success="onPageSuccess">
                        <template #default="{ list }">
                            <div class="listitem flex" v-for="(item, index) in list" :key="index">
                                <van-image :src="item.avatar" style="width: 3.6rem;"></van-image>
                                <div class="flex info">
                                    <div>
                                        <p>{{ item.name }}</p>
                                        <p class="desc">{{ item.account }}</p>
                                    </div>
                                    <van-button class="infoBtn" style=""
                                        @click="OnLink(item.account, 1)">Consult</van-button>
                                </div>
                            </div>
                        </template>
                    </MyListBase>
                </van-tab>
            </van-tabs>
        </div>
        <div style="background-color: #fff;color: #002544;border-radius: 16px;padding: 1rem;">
            <div class="content">
                <!--<div class="content1">
        <div class="Online">
            <div style="display: flex;align-items:center;margin-bottom: 1rem;">
                <img :src="telephone" style="width:1.5rem;height: 1.5rem;margin-right: 0.2rem;">
                <p style="font-weight: bold;">Online Time</p>
            </div>
            <div style="display: flex;justify-content: space-evenly;width: 100%;">
                <p>10:30-14:30</p>
                <p>16:30-24:00</p>
            </div>
        </div>
    </div>-->
                <van-image style=" width: 23vw; display: inline-block;" :src="robbot"></van-image>
                <div style="display: inline-block; width: 59vw; font-size: .9rem; vertical-align: top; margin-top: 2vh;">
                    <div>Hello,I am dedicated customer service</div>
                    <div style=" color: #8080808c; margin-top: 0.5vh;">Glad to serve you</div>
                </div>
            </div>
            <div class="content2">
                <div v-for="item,index in service_arr" :style="index ==0?' border-right: 1px solid #8080804f; ':''">
                    <img :src="item.type == 1 ? Telegram : WhatsApp">
                    <div style="display: inline-block; width: 23vw; height: 6vh; line-height: 3vh; ">
                        <p>{{ item.name }}</p>
                        <van-button class="infoBtn" style=""
                                    @click="OnLink(item.account, item.type == 2 ? 0 : 1)">Consult</van-button>
                    </div>
                </div>
                <!--<div>
                <img :src="WhatsApp">
                <p>Whats</p>
                <van-button class="infoBtn" style="" @click="OnLink('0', 0)">Consult</van-button>
            </div>-->
            </div>
            <div>
                <van-image style=" width: 6vw;" :src="light"></van-image><span style=" font-weight: bold;"> Kind tips</span>
            </div>
            <div class="content3">
                <div>Welcome to Life Fitness, the customer service manager is online from 10 am to 10 pm every day.</div>
                <div>The customer service manager will reply to your message as soon aspossible! If you encounter any platform problems, please contact theonline customer service manager directly.</div>
                <div>Do not trust any stranger who claims to be a customer service staff tocontact you! ! !</div>
            </div>
        </div>

    </div>
</template>


<script lang="ts">
    import { defineComponent } from "vue";
    import {
        Field,
        CellGroup,
        Cell,
        Button,
        Icon,
        RadioGroup,
        Radio,
        Image,
        Picker,
        Popup,
        Tab,
        Tabs,
    } from "vant";
    import MyNav from "../../components/Nav.vue";
    import MyLoading from "../../components/Loading.vue";
    import MyListBase from "../../components/ListBase.vue";
    import light from '/src/assets/img/user/light.png'
    import robbot from '/src/assets/img/user/robbot.png'

    export default defineComponent({
        components: {
            MyNav,
            MyLoading,
            MyListBase,
            [Field.name]: Field,
            [Tabs.name]: Tabs,
            [Tab.name]: Tab,
            [CellGroup.name]: CellGroup,
            [Cell.name]: Cell,
            [RadioGroup.name]: RadioGroup,
            [Radio.name]: Radio,
            [Icon.name]: Icon,
            [Image.name]: Image,
            [Button.name]: Button,
            [Picker.name]: Picker,
            [Popup.name]: Popup,
        },
    });
</script>

<script lang="ts" setup>
    import Telegram from "../../assets/img/user/Telegram.png";
    import WhatsApp from "../../assets/img/user/WhatsApp.png";
    import telephone from "../../assets/img/user/telephone.png";
    import { _alert, lang, copy, getSrcUrl } from "../../global/common";
    import {
        ref,
        reactive,
        onMounted,
        onBeforeUnmount,
        onBeforeMount,
        nextTick,
    } from "vue";
    import http from "../../global/network/http";
    import { useRouter } from "vue-router";
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();
    const active = ref(0);

    type level = {
        avatar: string;
        numbering: string;
        number: number | string;
        referrer: number | string;
        teamSize: number | string;
        amount: number | string;
        account: number | string;
    };

    const service_arr = ref<any>({})
    const cpageRef = ref();
    const LVv = ref("");
    const pageRef = ref();
    const pageRef1 = ref();
    const tableData = ref<any>({});

    const onPageSuccess = (res: any) => {
        tableData.value = res.all;
        loadingShow.value = false;
        if (cpageRef.value == undefined) {
            cpageRef.value = pageRef.value;
        } else if (res.lv == 1) {
            cpageRef.value = pageRef.value;
        } else if (res.lv == 2) {
            cpageRef.value = pageRef1.value;
        }
    };
    // gettodayregusercount
    const onClickTab = (title: any) => {
        switch (title.name) {
            case 0:
                cpageRef.value = pageRef.value;
                LVv.value = "Lv1";
                break;
            case 1:
                cpageRef.value = pageRef1.value;
                LVv.value = "Lv2";
                break;
        }
        if (cpageRef.value != undefined) {
            loadingShow.value = true;
            cpageRef.value.doSearch();
        }
    };

    const showPicker = ref<boolean>(false);

    const imgFlag = (src: string) => {
        return getSrcUrl(src, 1);
    };

    const OnLink = (account: string, type: number) => {
        if (type == 0)
            window.location.href = 'https://wa.me/' + account
        else
            window.location.href = 'tg://resolve?domain=' + account
    }

    const columns = ref<Array<string>>([]);
    const result = ref("");

    const onConfirm = (value: string) => {
        result.value = value;
        showPicker.value = false;
    };

    const doService = () => {
        console.log("im service");
    };

    let isRequest = false;
    const loadingShow = ref(false);
    const router = useRouter();
    const init = () => { };

    onMounted(() => {
        // const delayTime = Math.floor(Math.random() * 1000);
        // setTimeout(() => {
        http({
            url: 'c=Service&a=GetService_Online&type=0'
        }).then((res: any) => {
            service_arr.value = res.data.list
        })
        // }, delayTime) 
    });
</script>

<style lang="scss" scoped>
    .service_page {
        padding: 0 1rem;
        min-height: 100%;
        position: relative;


        :deep(.van-nav-bar__left) {
            .alter {
                color: #fff !important;
            }
        }

        :deep(.van-nav-bar__title) {
            .alter {
                color: #fff !important;
            }
        }

        .content {

            .content1 {
                display: flex;
                align-items: center;
                justify-content: space-around;
                padding: 1rem 0;
                background: url(../../assets/img/user/backdrop.png);
                background-repeat: no-repeat;
                background-size: 100% 100%;
                color: #fff;

                .Online {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    width: 100%;

                    p {
                        padding: 2px;
                    }
                }
            }
        }

        .content2 {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            text-align: center;
            box-shadow: 0px 0px 7px rgb(0 0 0 / 25%);
            border-radius: 9px;
            padding: 0.5vh 2vw;
            margin-bottom: 4vh;

            .div:first-of-type {
                border-right: 1px solid #8080804f;
            }

            div {
                padding: 1vh 0vw;
                align-items: center;
                width: 39vw;

                img {
                    width: 3rem;
                    display: inline-block;
                }

                p {
                    color: #AAA;
                }

                .infoBtn {
                    border: none;
                    color: #fff;
                    border-radius: 6px;
                    font-weight: bold;
                    background: linear-gradient(to right, #37aee2 20%, #37aee2);
                    width: 80%;
                    height: 2.5vh;
                    // background: url('../../assets/img/user/user_bg3.png');
                    // background-size: 100% 100%;
                    // background-repeat: no-repeat;
                }
            }
        }

        .content3 {
            div {
                color: #000;
                margin: .5rem 0;
                font: 0.8rem/20px '微软雅黑';
            }
        }

        .levelTab {
            margin-top: 1.25rem;

            :deep(.van-tabs__nav) {
                background-color: transparent;
            }

            :deep(.van-tab) {
                .van-tab__text {
                    color: #008260;
                    border: 1px solid #008260;
                    width: 90%;
                    text-align: center;
                    padding: 0.4rem 0;
                    border-radius: 10px;
                    font-size: 1rem;
                    font-weight: bold;
                    height: 1.5rem;
                }
            }

            :deep(.van-tab--active) {
                .van-tab__text {
                    color: #fff;
                    background: #008260;
                    border: 1px solid #008260;
                    background: url('../../assets/img/user/user_bg3.png');
                    background-size: 100% 100%;
                    background-repeat: no-repeat;
                    font-size: 1rem;
                    font-weight: bold;
                    height: 1.5rem;
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
        }

        .flex {
            display: flex;
        }

        .listitem {
            margin-top: 1rem;
            align-items: center;
        }

        .info {
            padding-left: 1rem;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            color: #000;

            .desc {
                font-size: 0.8rem;
                margin-top: 0.4rem;
            }

            .infoBtn {
                background-color: #e22e2f;
                border: none;
                color: #fff;
                border-radius: 4px;
                font-weight: bold;
                background: url('../../assets/img/user/user_bg3.png');
                background-size: 100% 100%;
                background-repeat: no-repeat;
            }
        }
    }
</style>