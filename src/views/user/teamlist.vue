<template>
    <div class="teamlist">
        <div style="height: 2rem; width: 100%; margin-top: 0.1rem;text-align:center;font-size:1rem;font-weight:bold">
            <div style="width:33%;float:left;">Product</div>
            <div style="width:33%;float:left">Price</div>
            <div style="width:33%;float:left">Amount</div>
        </div>
        <div style="height: 20rem; width: 100%;overflow-y:auto;">
            <div v-for="(item, index) in GoodsList" :key="index"
                style="width: 100%; text-align: center; color: #696767;height:2rem; font-size:0.9rem;">
                <div style="width:33%;float:left">{{ item.goods_name }}</div>
                <div style="width:33%;float:left">{{ item.price }} RS</div>
                <div style="width:33%;float:left">{{ item.num }}</div>
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

const GoodsList = ref([]);


onMounted(() => {
    var delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'PurchasedOrder',
            data: { size: 10000 }
        }).then((res: any) => {
            GoodsList.value = res.data.list;
        })
    })

});
</script>

<style lang="scss" scoped>
.teamlist{
    padding: 1rem;
}
</style>