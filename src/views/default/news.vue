<template>
    <div class="index">
        <MyTab></MyTab>
        <div class="index_wrap">
            <div class="index_top">
                <div class="headbox" style="position: relative;padding-top: 0.5rem;padding-bottom: 0.5rem;">
                    <van-image @click="onLink({ name: 'User' })"
                        :src="pageuser.headimgurl ? imgFlag(pageuser.headimgurl) : img_i1" width="2.2rem"
                        height="2.2rem" round></van-image>
                    <p @click="onLink({ name: 'User' })">{{ pageuser.nickname ? pageuser.nickname : 'Welcome' }}
                    </p>
                    <van-image @click="onService" :src="ico_101" width="1.6rem" height="1.6rem"
                        style="position: absolute;right: 0;top: 0.7rem;"></van-image>
                </div>
                <div class="myswiper">
                    <MySwiper :kv="tdata.kv" height="12rem"></MySwiper>
                </div>
            </div>

        </div>
    </div>

</template>

<script lang="ts">

import { _alert, lang } from "../../global/common";
import { defineComponent, ref, reactive, onMounted } from "vue";
import MyTab from '../../components/Tab.vue';
import MySwiper from '../../components/Swiper.vue'
import MyNoticeBar from '../../components/NoticeBar.vue'
import { Image, ActionSheet, Dialog } from "vant";
export default defineComponent({
    name: "index",
    components: {
        MyTab, MySwiper, MyNoticeBar,
        [Image.name]: Image,
        [ActionSheet.name]: ActionSheet,
        [Dialog.Component.name]: Dialog.Component,
    }
})
</script>
<script lang="ts" setup>

import { checkLogin, goLogin, isLogin } from "../../global/user";
import http from "../../global/network/http";
import { getSrcUrl, goRoute } from "../../global/common";
import { config } from "process";

const pageuser = isLogin()

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const onLink = (to: any) => {
    goRoute(to)
}

const tdata = ref<any>({
    user: {},
    kv: [],
    notice: [],
    news: [],
    about: {},
    tip: {},
    video: {}
})

const onClickVideo = () => {
    if (tdata.value.video.url) {
        window.open(tdata.value.video.url)
        return
    }
    onLink({ name: 'News_info', params: { id: tdata.value.video.id } })
}

const serviceShow = ref(false)
const actions = ref([]) //{ name: '选项三', subname: '描述信息' }

const onService = () => {
    serviceShow.value = true
}

  
const tipShow = ref(false)

onMounted(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => { 
    http({
        url: 'a=index'
    }).then((res: any) => {
        if (res.code != 1) {
            return
        }
        tdata.value = res.data

        if (tdata.value.tip) {
            tipShow.value = true
        }

        if (res.data.service_arr && res.data.service_arr.length > 0) {
            for (let i in res.data.service_arr) {
                let item = res.data.service_arr[i]
                actions.value.push({
                    name: item.name,
                    subname: item.type_flag + ': ' + item.account,
                    account: item.account,
                    type: item.type
                })
            }
        }
        // console.log(tdata.notice);
        // if (tdata.notice == undefined) {
        //     http({
        //         url: 'c=Gift&a=lottery'
        //     }).then((res: any) => {
        //         if (res.code != 1) {
        //             _alert(res.msg)
        //             return
        //         }
        //         tdata.notice = res.data.notice;
        //     })
        // }

    })
}, delayTime)
})
</script>