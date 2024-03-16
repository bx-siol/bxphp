<template>
    <div class="invite">
        <MyNav></MyNav>
        <div style="text-align: center;" class="invite_wrap">
            <div class="qrcode">
                <van-image :src="imgFlag(tdata.qrcode)" @click="onPreview(tdata.qrcode)"></van-image>
            </div>
            <div class="idbox">
                <p style="text-transform: capitalize !important;">{{ t('您的邀请码') }}</p>
                <span style="color:#e32e43;"><b>{{ tdata.icode }}</b></span>
            </div>
            <p style="text-align:center;text-transform: capitalize !important;font-size:14px;" class="tittxt_1">{{
                t('亲爱的会员，以下是您的邀请链接')
            }}</p>
            <div style="text-transform: lowercase !important;" class="link">{{ montage.urls }}</div>
            <van-button class="copyLinkBtn" ref="linkCopyRef">{{ t('复制邀请链接') }}</van-button>

        </div>
    </div>
</template>

<script lang="ts">

import { _alert, lang } from "../../global/common";
import { defineComponent, ref, onMounted, computed } from "vue";
import MyNav from "../../components/Nav.vue";
import { Button, Grid, GridItem, Image, Stepper, Cell, CellGroup } from "vant";

export default defineComponent({
    name: "invite",
    components: {
        MyNav,
        [Image.name]: Image,
        [Button.name]: Button,
        [Grid.name]: Grid,
        [GridItem.name]: GridItem,
        [Stepper.name]: Stepper,
        [Cell.name]: Cell,
        [CellGroup.name]: CellGroup,

    }
})
</script>
<script lang="ts" setup>
import http from "../../global/network/http";
import { copy, getSrcUrl, imgPreview } from "../../global/common";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const onPreview = (src: string) => {
    imgPreview(src)
}

const active = ref(0)
const value = ref(1);

const linkCopyRef = ref()

const tdata = ref({
    icode: '',
    url: '',
    qrocde: ''
})

const montage = computed(() => {
    return {
        urls: location.origin + '/#/Register?Icode=' + tdata.value.icode,
    };
});

onMounted(() => {
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
    // }, delayTime)

})

</script>
<style scoped>
* {
    text-transform: none !important;
}
</style>