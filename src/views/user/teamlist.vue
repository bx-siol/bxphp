<template>
    <MyNav leftText=''></MyNav>
    <div class="teamlist">
        <div style="height: 2rem; width: 100%; margin-top: 0.1rem;text-align:center;font-size:1rem;font-weight:bold;color:#64523e ;">
            <div style="width:40%;float:left;">Product name</div>
            <div style="width:20%;float:left">money</div>
            <div style="width:40%;float:left">Remaining days</div>
        </div>
        <div style="height: 20rem; width: 100%;overflow-y:auto;">
            <div v-for="(item, index) in GoodsList" :key="index"
                style="width: 100%; text-align: center; color: #002544;height:2rem; font-size:12px;">
                <div style="width:40%;float:left">{{ item.goods_name }}</div>
                <div style="width:26%;float:left;">{{ item.price }} RS</div>
                <div style="width:20%;float:left">{{ item.days }}</div>
            </div>
        </div>
    </div>
</template>


<script lang="ts">
import { defineComponent } from "vue";
import MyNav from "../../components/Nav.vue";
import MyLoading from "../../components/Loading.vue";

export default defineComponent({
    components: {
        MyNav,
        MyLoading,
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
import { useRouter, useRoute } from "vue-router";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();
const route = useRoute()

const GoodsList = ref([]);


onMounted(() => {
    var delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        let url = 'c=Product&a=PurchasedOrder&id=' + route.params.id
        http({
            url: url,
            data: { size: 10000 }
        }).then((res: any) => {
            GoodsList.value = res.data;
        })
    })

});
</script>

<style lang="scss" scoped>
.teamlist {
    padding: 1rem;
}


:deep(.van-nav-bar) {
    background-color: transparent;
    
}
:deep(.van-nav-bar__left) {
    .alter {
        color: #84973b !important;
    }
}

:deep(.van-nav-bar__title) {
    .alter {
        color: #84973b !important;
    }
}

</style>