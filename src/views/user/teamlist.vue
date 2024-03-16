<template>
    <MyNav leftText=''></MyNav>
    <div class="teamlist">
        <div class="list_top">
            <div style="width:30%;;">Product name</div>
            <div style="width:20%;">money</div>
            <div style="width:20%;">Remaining days</div>
        </div>
        <div class="list_bottom">
            <div v-for="(item, index) in GoodsList" :key="index" class="listfor">
                <div style="width:30%;">{{ item.goods_name }}</div>
                <div style="width:26%;;">{{ item.price }} RS</div>
                <div style="width:20%;" v-if="item.total_days < item.days">{{ item.total_days }}</div>
                <div style="width:20%;" v-else>Finished</div>
            </div>
        </div>
    </div>
</template>


<script lang="ts">
import { defineComponent } from "vue";

import MyNav from "../../components/Nav.vue";

export default defineComponent({
    components: {
        MyNav,

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

const imgFlag = (src: string) => {
    return getSrcUrl(src, 0)
}

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
    padding: 1rem 0.6rem;

    .list_top {
        height: 2rem;
        width: 100%;
        margin-top: 0.1rem;
        text-align: center;
        font-size: 0.75rem;
        font-weight: bold;
        color: #64523e;
        display: flex;
        justify-content: space-between;
        align-items: center
    }

    .list_bottom {

        width: 100%;
        overflow-y: auto;

        .listfor {
            width: 100%;
            text-align: center;
            color: #002544;
            height: 4rem;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    }
}
</style>