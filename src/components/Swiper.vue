<template>
    <van-swipe :autoplay="autoplay" indicator-color="white" :style="{ height: height }">
        <van-swipe-item v-for="item in kv" @click="onItemClick(item)">
            <!--            <van-image :src="imgFlag(item.cover)" fit="cover" width="100%" height="100%"/>-->
            <!-- 此处为后端返回的图片url -->
            <img :src="imgFlag(item.cover)" style="width: 100%;height: 100%;" />

            <!-- 注意 -->
            <!-- 此处只读取本地图片，自行将图片放入后端，将此处删除，用上面的代码 -->
            <!-- <img :src="item.cover" style="width: 100%;height: 100%;" /> -->
        </van-swipe-item>
    </van-swipe>
</template>

<script lang="ts">
import { defineComponent } from "vue"
import { Swipe, SwipeItem, Image } from "vant"
export default defineComponent({
    components: {
        [Image.name]: Image,
        [Swipe.name]: Swipe,
        [SwipeItem.name]: SwipeItem,
    }
})
</script>
 
<script lang="ts" setup>
import { getSrcUrl } from "../global/common"
import { useRouter } from "vue-router";
const router = useRouter()

const props = defineProps({
    kv: {
        type: Array,
        required: true
    },
    height: {
        type: String,
        default: '15rem'
    },
    autoplay: {
        type: Number,
        default: 3000
    }
})

const onItemClick = (item: any) => {
    if (item.path) {
        router.push({ path: item.path })
    } else if (item.url) {
        location.href = item.url
    }
}

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}
</script>