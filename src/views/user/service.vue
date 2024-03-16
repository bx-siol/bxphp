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
                <div class="content1">
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
                </div>
            </div>
            <div class="content2">
                <div v-for="item in service_arr">
                    <img :src="item.type == 1 ? Telegram : WhatsApp">
                    <p>{{ item.name }}</p>
                    <van-button class="infoBtn" style=""
                        @click="OnLink(item.account, item.type == 2 ? 0 : 1)">Consult</van-button>
                </div>
                <!--<div>
                <img :src="WhatsApp">
                <p>Whats</p>
                <van-button class="infoBtn" style="" @click="OnLink('0', 0)">Consult</van-button>
            </div>-->
            </div>
            <div class="content3">
                <p>Welcome to join Nestle,Glad To Serve You. </p>
                <p>The customer service manager will reply to your message as soon as possible! If you encounter any
                    platform
                    problems, please contact the online customer service manager directly, </p>
                <p>Don't trust any stranger who claims to be a customer service person to contact you! ! !</p>
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
        padding: 0.6rem 0 1.5rem;

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

        div {
            padding: 1rem 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            flex-direction: column;
            width: 45%;

            img {
                width: 4rem;
            }

            p {
                margin: 1rem 0;
                color: #AAA;
            }

            .infoBtn {
                border: none;
                color: #fff;
                border-radius: 6px;
                font-weight: bold;
                background: linear-gradient(to right, #c49b6c 20%, #a77d52);
                width: 80%;
                height: 2rem;
                // background: url('../../assets/img/user/user_bg3.png');
                // background-size: 100% 100%;
                // background-repeat: no-repeat;
            }
        }

    }

    .content3 {
        p {
            color: #000;
            margin: 1.8rem 0;
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