<template>
    <van-tabbar class="myTabBox" v-model="menu.active" @change="onBarChange" route :inactive-color="menu.inactiveColor"
        :active-color="menu.activeColor" style="background:#fff;box-shadow: 0px -1px 4px 0 rgb(0 0 0/10%);">
        <van-tabbar-item v-for="(item, idx) in menu.tabs" :to="!item.url ? item.path : ''" :url="item.url"
            style="line-height: 0.8;" :dot="item.dot" :badge="item.badge">
            <span>{{ item.text }}</span>
            <template #icon="{ active }">
                <van-image :src="(active ? item.iconOn : item.icon)" width="1.5rem" height="1.5rem" />
            </template>
        </van-tabbar-item>
    </van-tabbar>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { TabbarItemProps, Tabbar, TabbarItem, Image } from "vant";
export default defineComponent({
    components: {
        [Image.name]: Image,
        [Tabbar.name]: Tabbar,
        [TabbarItem.name]: TabbarItem
    }
})
</script>

<script lang="ts" setup>
import { ref, defineProps, watch, onMounted } from "vue";
import { useStore } from "vuex";
import home from "../assets/tab/ft11.png";
import homeactive from "../assets/tab/ft11-on.png";
import prizes from "../assets/tab/prizes.png";
import prizesactive from "../assets/tab/prizesactive.png";
import project from "../assets/tab/ft22.png";
import projectactive from "../assets/tab/ft22-on.png";

import company from "../assets/tab/ft55.png";
import companyactive from "../assets/tab/ft55-on.png";
import aboutactive from '../assets/img/user/aboutactive.png';
import user from "../assets/tab/ft44.png";
import useractive from "../assets/tab/ft44-on.png";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
// const newscount = defineProps(['newscount'])
const newscountc = ref('')
const store = useStore()
const router = useRouter()
// console.log(newscount.newscount)

// window.addEventListener('setItemEvent', function (e) {
//     // 这里的info是我存入localstorage的key值
//     if (e.key === 'newscount') {
//         newscount.newscount = e.newValue;
//         // console.log('##', e.newValue)
//         // 如果发生变化的e.key=想要监听的key值，则执行需要的操作
//         // e.newValue是变化后的值
//     }
// })

onMounted(() => {

    //根据自己需要来监听对应的key
    // window.addEventListener("setItemEvent", function (e) {
    //     //e.key : 是值发生变化的key
    //     //例如 e.key==="token";
    //     //e.newValue : 是可以对应的新值
    //     if (e.key === "newscount") {
    //         console.log('c:' + e.newValue);
    //         newscountc.value = e.newValue;
    //     }
    // })
})

//解决this指向问题，在window.addEventListener中this是指向window的。
//需要将vue实例赋值给_this,这样在window.addEventListener中通过_this可以为vue实例上data中的变量赋值





const onBarChange = (ev: any) => {
    menu.value.active = parseInt(ev)
}

const dotShow = ref(false)

interface Tab {
    text: string,
    icon: string,
    iconOn: string,
    path: string,
    url?: string, //跳转外部链接
    dot?: boolean,
    badge?: string,
    c?: TabbarItemProps,
}

const tabs: Tab[] = [
    {
        text: t('首页'),
        icon: home,
        iconOn: homeactive,
        path: '/'
    },
    {
        text: t('项目'),
        icon: project,
        iconOn: projectactive,
        path: '/project'
    },
    {
        text: t('积分'),
        icon: prizes,
        iconOn: prizesactive,
        path: '/points'
    },
    // {
    //     text: t('利润'),
    //     icon: prizes,
    //     iconOn: prizesactive,
    //     path: '/purchase'
    // },
    {
        text: t('公司'),
        icon: company,
        iconOn: companyactive,
        path: '/news',
        //badge: store.state.newscountc
    },
    {
        text: t('家'),
        icon: user,
        iconOn: useractive,
        path: '/user'
    }
]

const menu = ref<any>({
    active: 0,
    inactiveColor: '#000',//未选中颜色
    activeColor: '#84973b',//选中颜色
    tabs: tabs
})

const onClickLogo = () => {
    router.push({ path: '/' })
    menu.value.active = 2
}

</script>

<style>
.van-tabbar-item__icon img {
    height: 1.5rem;
}

.myTabBox .van-tabbar-item--active {
    background: transparent;
}

.myTabBox .van-tabbar .van-image__img {
    height: auto;
}

.van-hairline--top-bottom::after,
.van-hairline-unset--top-bottom::after {
    border: 0;
}
</style>