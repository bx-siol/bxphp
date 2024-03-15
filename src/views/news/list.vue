<template>
    <div class="news">
        <MyNav>
            <template #left>
                <div></div>
            </template>
        </MyNav>
        <div>
            <div class="big">
                <van-image :src="Newsimg" class="newsimg"></van-image>
              <!-- <MySwiper :kv="tableData.msgkv" height="12.5rem"></MySwiper> -->

                <div class="inform">
                    <img :src="bird">
                    <p class="title">News</p>
                </div>
            </div>

            <div class="news_wrap">
                <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
                    <template #default="{ list }">
                        <ul>
                            <li v-for="item in list" @click="onLink({ name: 'News_info', params: { id: item.id } })">
                                <van-image :src="imgFlag(item.cover)" width="7rem" height="4rem" class="imgs"></van-image>
                                <div class="infoRight">
                                    <p class="titles" :style="{ color: '#3d3d3b', }">{{ item.title }}</p>
                                    <p class="desc" v-html="item.ndesc"></p>
                                    <p class="time" :style="{ color: '#3d3d3b', }">{{ item.publish_time }}</p>
                                </div>
                            </li>
                        </ul>
                    </template>
                </MyListBase>
            </div>
        </div>
        <MyTab></MyTab>
    </div>

    <MyLoading :show="loadingShow"></MyLoading>
</template>
<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import { Image } from "vant";
import MyNav from "../../components/Nav.vue";
import MyTab from "../../components/Tab.vue";
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';
import bird from '../../assets/ico/bird.png'
import MySwiper from '../../components/Swiper.vue'
import Newsimg from '../../assets/img/Newsimg.png'


import rewards from '../../assets/img/home/home-banner-3-1.png'

export default defineComponent({
    name: "news",
    components: {
        MyNav, MyListBase, MyLoading,MySwiper,
        [Image.name]: Image,
    }
})
</script>
<script lang="ts" setup>
import { getSrcUrl, goRoute } from "../../global/common";

const onLink = (to: any) => {
    goRoute(to)
}

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const pageRef = ref()
const pageUrl = ref('c=News&a=list&s_cid=50')
const loadingShow = ref(false)

const tableData = ref({})

const onPageSuccess = (res: any) => {
    tableData.value = res.data
    loadingShow.value = false
}

</script>

<style scoped>
.news_list .van-list__error-text,
.news_list .van-list__finished-text,
.news_list .van-list__loading {
    line-height: 1.5rem;
}
</style>
<style scoped>
.big {
    padding: 1rem 1rem 0 1rem;
}

.big .newsimg {
    width: 100%;
    height: 12.5rem;
}

.big .newsimg :deep(.van-image__img) {
    border-radius: 4px;
}

.big .inform {
    display: flex;
    align-items: center;
}

.big .inform img {
    width: 1.8rem;
    margin-right: 0.2rem;
}

.big .title {
    margin: 0.8rem 0;
    font-weight: bold;
    color: #64523e;
    font-size: 20px;
}

.news_wrap {
    padding-bottom: 4rem;
}

.news_wrap ul {
    padding: 0 1rem;
}

.news_wrap .imgs :deep(.van-image__img) {
    border-radius: 6px;
}

.infoRight {
    width: 78%;
    padding-left: 1rem;
    color: #3d3d3d !important;
}

.infoRight .titles {
    font-size: 1.05rem;
    font-weight: bold;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    line-height: 1.4em;
    max-height: 2.4em;
}

.infoRight .desc {
    margin: 0.5rem 0;
    font-size: 0.725rem;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-height: 1.2em;
    max-height: 2.4em;
}

.infoRight .time {
    font-size: 0.625rem;
}
</style>