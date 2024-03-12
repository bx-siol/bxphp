<template>
    <div class="invite">
        <MyNav></MyNav>
        <div class="invite_wrap" style="width: 70%;">
            <div class="qrcode">
                <van-image :src="imgFlag(tdata.qrcode)" @click="onPreview(tdata.qrcode)"></van-image>
            </div>
            <div class="idbox">
                <div class="link">{{ tdata.icode }}</div>
                <div class="link">{{ montage.urls }}</div>
                <van-button class="copyLinkBtn" ref="linkCopyRef">{{ t('共享邀请链接') }}</van-button>
            </div>

        </div>
    </div>
</template>

<script lang="ts">

import { _alert, lang } from "../../global/common";
import { defineComponent, ref, onMounted,computed } from "vue";
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
import Product from '../../assets/img/project/product.png';

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
        urls: location.origin+'/#/Register?Icode='+tdata.value.icode,
    };
});

onMounted(() => {
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
})

</script>
<style>

</style>
<style lang="scss" scoped>
.invite{

    :deep(.van-nav-bar) {
        background-color: transparent;
        
    }
    :deep(.van-nav-bar__left) {
        .alter {
            color: #ffffff !important;
        }
    }

    :deep(.van-nav-bar__title) {
        .alter {
            color: #ffffff !important;
        }
    }
}
</style>