<template>
    <div class="conBox" style="color: white;overflow: hidden;background-color: #fff;">
        <Nav></Nav>
        <div style="height: 1rem;"></div>
        <van-cell-group inset v-for="item in service_arr" style="margin-bottom: 1rem;">
            <div style="text-align: center;padding: 3% 4%;">
                <div style="padding-bottom: 0.5rem;font-size: 1.2rem;">{{ item.name }}</div>
                <div>
                    <van-image :src="imgFlag(item.qrcode)" @click="imgPreview(item.qrcode)" width="15rem"
                        style="min-height: 15rem;" />
                </div>
                <div style="font-weight: bold;font-size: 1.2rem;line-height: 2rem;text-decoration:underline;">
                    <template v-if="item.name == 'Telegram'">
                        <a :href="'tg://resolve?domain=' + item.account" style="color: white;">{{ item.account }}</a>
                    </template>
                    <template v-else>
                        <span>{{ item.account }}</span>
                    </template>
                </div>
            </div>
        </van-cell-group>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { CellGroup, Cell, Button, Image, Dialog } from 'vant';
import Nav from '../../components/Nav.vue';

export default defineComponent({
    components: {
        Nav,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [Image.name]: Image,
        [Button.name]: Button
    }
})
</script>

<script lang="ts" setup>
import { onMounted, ref } from "vue";
import http from "../../global/network/http";
import { getSrcUrl, imgPreview } from "../../global/common";

const service_arr = ref<any>({})

onMounted(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Service&a=online'
        }).then((res: any) => {
            service_arr.value = res.data.service_arr
        })
    }, delayTime)
})

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}
</script>