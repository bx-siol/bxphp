<template>
    <van-notice-bar :scrollable="scrollable" :left-icon="Notice" style="background: transparent;" :color="color"
        :mode="mode" @click="onNoticeBarClick">
        <van-swipe vertical :style="{ height: height, lineHeight: height }" :autoplay="autoplay"
            :show-indicators="false" @change="onNoticeBarChange">
            <van-swipe-item v-for="item in noticeList">{{ item.title }}</van-swipe-item>
        </van-swipe>
        <template #right-icon v-if="slots['right-icon']">
            <slot name="right-icon"></slot>
        </template>
    </van-notice-bar>

    <MyPop v-model:show="popShow" title="Notice" :style="{ color: '#cbac8c' }" :close-type="2"
        :wrapper-style="{ marginTop: '5rem', height: '60%', width: '80%', borderRadius: '10px' }"
        :content-style="{ padding: '4%', paddingBottom: '6%' }">
        <div><b>{{ arcItem.title }}</b></div>
        <div style="padding: 0.3rem 0;padding-bottom: 1rem;">
            <!--            <van-tag plain type="success" style="margin-right: 0.3rem;">{{arcItem.tag}}</van-tag>-->
            <span style="vertical-align: middle;color: #969799;">{{ arcItem.time }}</span>
        </div>
        <div>
            <div v-html="arcItem.content"></div>
        </div>
    </MyPop>

</template>

<script lang="ts">
import { defineComponent } from "vue"
import { Swipe, SwipeItem, NoticeBar, Tag } from "vant"
import MyPop from './Pop.vue';

export default defineComponent({
    components: {
        MyPop,
        [Tag.name]: Tag,
        [Swipe.name]: Swipe,
        [SwipeItem.name]: SwipeItem,
        [NoticeBar.name]: NoticeBar,
    }
})
</script>

<script lang="ts" setup>
import { useSlots, ref, watch, onMounted } from 'vue';
import { ico_voice } from '../global/assets';
import Notice from '../assets/img/home/notice.png';
import { useRouter } from "vue-router";

const slots = useSlots()

const emit = defineEmits(['update:currentNotice', 'noticeClick'])

const props = defineProps({
    noticeList: {
        type: Array,
        required: true
    },
    height: {
        type: String,
        default: '2rem'
    },
    color: String,
    autoplay: {
        type: Number,
        default: 3000
    },
    mode: { type: String, default: 'link' },
    to: { type: [String, Object] },
    url: { type: String },
    currentNotice: { type: Object },
    scrollable: { type: Boolean, default: false },
    needPop: { type: Boolean, default: false }
})

const router = useRouter()
const nowIndex = ref(0)
const arcItem = ref<any>({})

const getCurrentItem = () => {
    let item: any | null
    for (let i = 0; i < props.noticeList.length; i++) {
        if (i == nowIndex.value) {
            item = props.noticeList[i]
            emit('update:currentNotice', item)
            break;
        }
    }
    return item
}

const onNoticeBarClick = () => {
    if (props.url) {
        location.href = props.url
        return
    }
    if (props.to) {
        if (typeof props.to == 'string') {
            router.push({ path: props.to })
        } else if (typeof props.to == 'object') {
            router.push(props.to)
        }
        return
    }
    if (props.mode == 'link' && !slots['right-icon']) {
        doItemClick()
    }
}

const onNoticeBarChange = (idx: number) => {
    nowIndex.value = idx
}

const popShow = ref(false)

const doItemClick = () => {
    let item = getCurrentItem()
    if (!item) {
        return
    }
    if (item.url) {
        location.href = item.url
    } else if (item.to) {
        if (typeof item.to == 'string') {
            router.push({ path: item.to })
        } else if (typeof item.to == 'object') {
            router.push(item.to)
        }
    } else {
        emit('noticeClick', item)
        if (!props.needPop) {
            return
        }
        if (item.title) {
            arcItem.value = item
            popShow.value = true
        }
    }
}

onMounted(() => {

})

</script>