<template>
    <div class="news">
        <MyNav>
            <template #left>
                <div></div>
            </template>
        </MyNav>
        <div>
            <div class="big">
                <div class="myimage">
                    <van-image :src="beijing" width="100%" height="12.5rem"></van-image>
                </div>
                <div class="inform">
                    <p class="title">{{ t('最新消息') }}</p>
                </div>
            </div>
            <div class="news_wrap">
                <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
                    <template #default="{ list }">
                        <ul>
                            <li v-for="item in list" @click="onLink({ name: 'News_info', params: { id: item.id } })"
                                style="display:flex">
                                <van-image :src="imgFlag(item.cover)" width="5.5rem" height="3.5rem"></van-image>
                                <div class="infoRight">
                                    <p class="titles" :style="{ color: '#fff', }">{{ item.title }}</p>
                                    <p class="desc" v-html="item.ndesc"></p>
                                    <p class="time" :style="{ color: '#002544', }">{{ item.publish_time }}</p>
                                </div>
                            </li>
                        </ul>
                    </template>
                </MyListBase>
            </div>
        </div>

    </div>
    <MyTab></MyTab>
    <MyLoading :show="loadingShow"></MyLoading>
</template>
<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import { Image } from "vant";
import MyNav from "../../components/Nav.vue";
import MyTab from "../../components/Tab.vue";
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';
import beijing from '../../assets/img/user/beijing.png';

export default defineComponent({
    name: "news",
    components: {
        MyNav, MyListBase, MyLoading,
        [Image.name]: Image,
    }
})
</script>
<script lang="ts" setup>
import {
    img_2
} from '../../global/assets';
import { getSrcUrl, goRoute } from "../../global/common";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

const onLink = (to: any) => {
    goRoute(to)
}

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const pageRef = ref()
const pageUrl = ref('c=News&a=list&s_cid=50')
const loadingShow = ref(false)

const tableData = ref({

})

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

.news_wrap li {
    display: block;

    background: none;
}
</style>

<style scoped>
.big{
    padding: 1rem 1rem 0 1rem;
}
.big .title{
    margin: 1rem 0;
    font-weight: bold;
    color: #fff;
}
.news_wrap{
    padding-bottom: 4rem;
}
.news_wrap ul{
    padding: 0 1rem;
}
.infoRight {
    width: 78%;
    padding-left: 1rem;
    color: #3d3d3d !important;
}

.infoRight .titles {
    font-size: 1rem;
    font-weight: bold;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    line-height: 1.2em;
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
