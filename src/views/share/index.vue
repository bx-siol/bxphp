<template>
    <div class="invite">
        <MyNav leftText=''></MyNav>
        <div style="text-align: center;" class="invite_wrap">
            <img :src="title" style="margin-top: 1rem;width: 90%;" >
            <div class="qrcode">
                <!-- <van-image :src="imgFlag(tdata.qrcode)" @click="onPreview(tdata.qrcode)"></van-image> -->                
                <vue-qrcode :value="montage.urls"></vue-qrcode>
            </div>
            <div class="idbox">
                <p style="display: flex;justify-content: space-evenly;align-items: center;">
                    <span>{{ t('您的邀请码') }}</span>
                    <span style="color:#ffed32;font-weight: bold;font-size: 1.2rem;">{{ tdata.icode }}</span>
                </p>                
            </div>
            <div class="link">{{ montage.urls }}</div>
            <van-button class="copyLinkBtn" style="background: rgb(235 23 0) " ref="linkCopyRef">{{ t('复制邀请链接') }}</van-button>
        </div>
    </div>
</template>

<script lang="ts">

import { _alert, lang } from "../../global/common";
import { defineComponent, ref, onMounted, computed } from "vue";
import MyNav from "../../components/Nav.vue";
import { Button, Grid, GridItem, Image, Stepper, Cell, CellGroup } from "vant";
import VueQrcode from 'vue-qrcode'
import title from '../../assets/img/share/title.png';

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