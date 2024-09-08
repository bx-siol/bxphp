<template>
    <div class="service_page">
        <MyNav leftText=''> </MyNav>
        <div style="color: #002544;border-radius: 16px;margin-top: 1rem;">
            <div class="content2">
                <div v-for="item in service_arr">
                    <img :src="item.type == 1 ? Telegram : WhatsApp">
                    <p>{{ item.name }}</p>
                    <van-button class="infoBtn" @click="OnLink(item.account, item.type == 2 ? 0 : 1)" :style="item.type ==1 ? 'background-color: #37aee2;' : 'background-color: #25d366;'" >Consult</van-button>
                </div>
            </div>
            <div class="content3">
                <p>Welcome to Life Fitness, the customer service manager is online from 10 am to 10 pm every day. </p>
                <p>The customer service manager will reply to your message as soon as possible! If you encounter any platform problems,please contact the online customer service manager directly.</p>
                <p>Do not trust any stranger who claims to be a customer service staff to contact you!!!</p>
            </div>
        </div>

    </div>
</template>


<script lang="ts">
import { defineComponent } from "vue";
import { Field, CellGroup, Cell, Button, Icon, RadioGroup, Radio, Image, Picker, Popup, Tab, Tabs } from "vant";
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
import service_manage from "../../assets/img/user/service_manage.png";
import { _alert, lang, copy, getSrcUrl } from "../../global/common";
import { ref, onMounted } from "vue";
import http from "../../global/network/http";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

const service_arr = ref<any>({})

const OnLink = (account: string, type: number) => {
    if (type == 0)
        window.location.href = 'https://wa.me/' + account
    else
        window.location.href = 'tg://resolve?domain=' + account
}

onMounted(() => {
    http({
        url: 'c=Service&a=GetService_Online&type=0'
    }).then((res: any) => {
        service_arr.value = res.data.list
    })
});
</script>

<style lang="scss" scoped>
.service_page {
    padding: 0 1.5rem;
    min-height: 100%;
    position: relative;
    background-color: #ebf9e8;


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

    .content2 {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        text-align: center;

        div {
            padding: 1rem 0;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            flex-direction: column;
            width: 47%;
            box-shadow: 0 0px 10px 0 #d1d1d1;
            background-color: white;

            img {
                width: 4rem;
            }

            p {
                margin: 1rem 0;
                color: black;
            }

            .infoBtn {
                border: none;
                border-radius: 6px;
                font-weight: bold;
                color: white;
                width: 80%;
                height: 2rem;
            }
        }

    }

    .content3 {
        p {
            color: black;
            margin: 1rem 0;
            font-size: 0.75rem;
            line-height: 1.1rem;
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