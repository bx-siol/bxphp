<template>
    <div class="invite">
        <MyNav></MyNav>
        <div style="text-align: center;" class="invite_wrap">
            <div class="idbox">
                <p style="text-transform: capitalize !important;">{{ t('您的邀请码') }}</p>
                <span><b>{{ tdata.icode }}</b></span>
            </div>
            <p style="text-align:center;text-transform: capitalize !important;" class="tittxt_1">1.{{
                t('亲爱的会员，以下是您的邀请链接')
            }}</p>
            <div style="text-transform: lowercase !important;" class="link">{{ tdata.url }}</div>
            <van-button class="copyLinkBtn" ref="linkCopyRef">{{ t('复制邀请链接') }}</van-button>
            <p style="text-transform: capitalize !important;" class="tittxt_2"> If you are A, then B, C, and D belong to
                your team members. The team offers you 3
                levels of commission.
            </p>
            <p style="padding-left: 3px;">B=10%</p>
            <p>C= 5%</p>
            <p>D= 2%</p>
            <div style="display:none" class="qrcode">
                <van-image :src="imgFlag(tdata.qrcode)" @click="onPreview(tdata.qrcode)"></van-image>
            </div>
        </div>
    </div>
</template>

<script lang="ts">

import { _alert, lang } from "../../global/common";
import { defineComponent, ref, onMounted } from "vue";
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
import {
    img_banner
} from '../../global/assets';
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

onMounted(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Share&a=index'
        }).then((res: any) => {
            tdata.value = res.data
            copy(linkCopyRef.value.$el, {
                text: (target: HTMLElement) => {
                    return tdata.value.url.toLocaleLowerCase()
                }
            })
        })
    }, delayTime)

})

</script>
<style>
* {
    text-transform: none !important;
}
</style>