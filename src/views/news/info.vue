<template>
    <div class="newsDet">
        <MyNav></MyNav>
        <div class="newsDet_wrap">
            <template v-if="info.id > 40">
                <div class="title">{{ info.title }}</div>
                <div class="topbox"><span>{{ info.cat_name }}</span> <span class="time">{{ info.publish_time }}</span></div>
            </template>
            <div class="txtbox" v-html="info.content">

            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import { Image } from "vant";
import MyNav from "../../components/Nav.vue";

export default defineComponent({
    name: "newsDet",
    components: {
        MyNav,
        [Image.name]: Image,
    }
})
</script>

<script lang="ts" setup>
import { useRoute, useRouter } from "vue-router";
import http from "../../global/network/http";
import { goRoute } from "../../global/common";

const route = useRoute()
const router = useRouter()

const onLink = (to: any) => {
    goRoute(to)
}

const info = ref<any>({})
const others = ref({
    pre: {},
    next: {}
})

const tdata = ref<any>({
    category_arr: []
})

const getNewsInfo = (id: number) => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=News&a=info',
            data: { id: id }
        }).then((res: any) => {
            if (res.code != 1) {
                router.go(-1)
                return
            }
            info.value = res.data.info
            others.value.pre = res.data.pre
            others.value.next = res.data.next
            document.title = info.value.title
        })
    }, delayTime)
}

onMounted(() => {
    getNewsInfo(route.params.id)
})
</script>