<template>
    <div class="news">
        <MyNav>
            <template #left>
                <div></div>
            </template>
        </MyNav>
        <div>
            <div class="big">
                <van-swipe indicator-color="#009900" :autoplay="3000">
                    <van-swipe-item v-for="item in covers">
                        <img :src="item" style="max-height: 200px;" />
                    </van-swipe-item>
                </van-swipe>
            </div>

            <div class="news_wrap">
                <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
                    <template #default="{ list }">
                        <ul>
                            <li v-for="item in list" @click="onLink({ name: 'News_info', params: { id: item.id } })">
                                <van-image :src="imgFlag(item.cover)" width="13rem" height="5rem" class="imgs"></van-image>
                                <div class="infoRight">
                                    <p class="titles">{{ item.title }}</p>
                                    <p class="desc" v-html="item.ndesc"></p>
                                    <p class="time">{{ item.publish_time }}</p>
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
import { Image, Swipe, SwipeItem, } from "vant";
import MyNav from "../../components/Nav.vue";
import MyTab from "../../components/Tab.vue";
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';
import MySwiper from '../../components/Swiper.vue'
import lbt1 from '../../assets/index/lbt1.jpg'
import lbt2 from '../../assets/index/lbt2.jpg'
import lbt3 from '../../assets/index/lbt3.jpg'
import lbt4 from '../../assets/index/lbt4.jpg'

export default defineComponent({
    name: "news",
    components: {
        MyNav, MyListBase, MyLoading, MySwiper,
        [Image.name]: Image,
        [Swipe.name]: Swipe,
        [SwipeItem.name]: SwipeItem,
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
const covers = ref([lbt1, lbt2, lbt3, lbt4])

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
.news {
    background-color: #ebf9e8;

    .news_wrap {
        padding-bottom: 4rem;
        margin-top: 2rem;

        ul {
            padding: 0 1rem;

            li{
                background-color: transparent;
            }
        }

        .infoRight {
            width: 78%;
            padding-left: 1rem;
            color: black !important;

            .titles {
                font-size: 0.8rem;
                font-weight: bold;
                overflow: hidden;
                text-overflow: ellipsis;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 1;
                line-height: 1.4em;
                max-height: 2.4em;
                color: #009900;
            }

            .desc {
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

            .time {
                font-size: 0.625rem;
            }
        }
    }
}
</style>