<template>
    <van-nav-bar class="myNavBar" :fixed="fixed" :placeholder="fixed">
        <template #title>
            <div :style="{ color: foregroundColor }">
                <slot name="title">{{ topTitle }}</slot>
            </div>
        </template>
        <template #left>
            <slot name="left" :color="foregroundColor">
                <div @click="onLeftClick" :style="{ color: foregroundColor,'font-weight': 'bold' }">
                    <van-icon name="arrow-left" size="1.3rem" :color="foregroundColor"
                        style="vertical-align: middle;top:0px;" />
                    <span style="vertical-align: middle;position: relative;left: -2px;">{{ leftText }}</span>
                </div>
            </slot>
        </template>
        <template #right>
            <slot name="right" :color="foregroundColor"></slot>
        </template>
    </van-nav-bar>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import { NavBar, Icon, ConfigProvider } from 'vant'
export default defineComponent({
    components: {
        [NavBar.name]: NavBar,
        [Icon.name]: Icon,
        [ConfigProvider.name]: ConfigProvider
    }
})
</script>

<script lang="ts" setup>
import { useRoute, useRouter } from "vue-router"
import { ref, reactive } from "vue"

const foregroundColor = ref('#fff')  //前景色

const route = useRoute()
const router = useRouter()

const props = defineProps({
    fixed: {
        type: Boolean,
        default: true
    },
    title: {
        type: String,
        default: ''
    },
    to: String | Object,
    leftText: {
        type: String,
        default: 'Back'
    }
})

const topTitle = ref(props.title)
if (!topTitle.value) {
    if (router.currentRoute.value.meta.topTitle) {
        topTitle.value = router.currentRoute.value.meta.topTitle
    } else {
        if (router.currentRoute.value.meta.title) {
            topTitle.value = router.currentRoute.value.meta.title
        }
    }
}

const onLeftClick = () => {
    if (route.query.share) {
        router.push({ path: '/' })
    } else {
        if (props.to) {
            if (typeof props.to == 'string') {
                router.push({ path: props.to })
            } else if (typeof props.to == 'object') {
                router.push(props.to)
            } else {
                console.error('left:router to error')
            }
        } else {
            router.go(-1)
        }
    }
}

</script>

<style>
.myNavBar .van-nav-bar {
    background-color: rgb(100, 82, 62);
    ;
    max-width: 640px;
    margin: 0 auto;
    right: 0;
}

/* .myNavBar .van-nav-bar{background-color: #0098a2;max-width: 640px;margin: 0 auto;right: 0;} */
.van-hairline--bottom::after {
    border-bottom-width: 0;
}

.myNavBar .van-nav-bar__left {
    padding: 0 6px;
}

.myNavBar .van-nav-bar__right {
    padding: 0 10px;
}

.van-hairline--bottom::after {
    border-bottom: 0px solid #c8d0dc;
}

/*box-shadow: 0px 0px 3px 2px #c8d0dc;*/

</style>