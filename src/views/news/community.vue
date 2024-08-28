<template>
    <div class="community">
        <MyNav>
            <template #right>
                <span style="width: 20px;" ref="mask" @click="onclick">
                    <img :src="p97"></span>
            </template>
        </MyNav>

        <div style="height: 100vh;overflow: auto;">
            <div style="margin-bottom: 10rem;">
                <MyListBase url="c=News&a=communitylist" ref="pageRef" @success="onPageSuccess">
                    <template #default="{ list }">
                        <div v-for="(item, index)  in list" :key='index' class="username">
                            <div class="name">
                                <img :src="imgFlag(item.headimg)" class="left">
                                <span>{{ item.nikename }}</span>
                                <p v-html="item.content"
                                    style="margin-left:35px;width: auto;white-space: normal;word-break: break-all;"></p>
                            </div>
                            <div class="imgbox">


                                <!-- <img @click="h5showImg(imgFlag('/h5/src/assets/img/home/swipbot3.png'))" src="/h5/src/assets/img/home/swipbot3.png"> -->
                                <img @click="h5showImg(item.imgs, indexc)" v-for="(itemc, indexc)  in item.imgs.split(',')"
                                    :key='indexc' :src="imgFlag(itemc)">

                            </div>
                            <div class="thumbs">
                                <div class="time"><span>{{ item.releasetime }}</span>
                                    <span style="float:right;" @click="renewal(item)">
                                        <img v-if="!item.praiseflg" :src="praise">
                                        <img v-if="item.praiseflg" :src="praise2">Good
                                    </span>
                                </div>
                                <div class="praise">
                                    <img src="../../assets/img/team/praise2.png">
                                    <p><span>{{ item.commendatory }}</span> people thought it was great</p>
                                </div>
                                <!-- <div class="comment">
                                    <p><span>{{ item.nikename }}:</span>{{ item.comments }}</p>
                                </div> -->
                            </div>
                        </div>
                    </template>
                </MyListBase>
            </div>

        </div>
        <MyTab></MyTab>
    </div>
</template>
<script lang="ts" >
import { _alert, lang, showImg, getSrcUrl, goRoute } from "../../global/common";
import { defineComponent, ref, reactive, onMounted } from 'vue';
import { Button, Form, Field, CellGroup, Overlay, ImagePreview } from 'vant';

import MyTab from "../../components/Tab.vue";
import headimg from '../../assets/img/team/avatar.png';
import p97 from "../../assets/img/team/97.png";
import praise from "../../assets/img/team/praise.png";
import praise2 from "../../assets/img/team/praise2.png";

import { Image } from "vant";
import MyNav from "../../components/Nav.vue";
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';


export default defineComponent({
    name: "community",
    components: {
        MyNav, MyListBase, MyLoading,
        [Image.name]: Image,
        MyTab,
        [Button.name]: Button,
        [Form.name]: Form,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [ImagePreview.Component.name]: ImagePreview.Component,
    }
})
</script>

<script lang="ts" setup>
import { useRouter } from "vue-router";
import http from "../../global/network/http";


const onLink = (to: any) => {
    goRoute(to)
}
const h5showImg = (str: string, indexc: number) => {

    var arrimg = str.split(',')

    for (let index = 0; index < arrimg.length; index++) {
        arrimg[index] = imgFlag(arrimg[index])
    }

    ImagePreview({
        images: arrimg,
        startPosition: indexc,
    });
    // showImg(str)
}
const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

let isRequest = false
const renewal = (item: any) => {
    if (!item.praiseflg) {
        item.praiseflg = true
        item.commendatory++
        http({
            url: 'c=News&a=communityadd',
            data: { id: item.id }
        }).then((res: any) => {
            if (res.code != 1) {
                isRequest = false
                return
            }
        })
    }
}
const onPageSuccess = () => {

}

const clicked = ref(false);
const showTip = ref(false);
const count = ref(38449);
const praiseflg = ref(true)

const router = useRouter()
const onclick = () => {
    _alert({
        message: "If you want to send content to the community for teampromotion,please edit your content and send it to your manager to publish for you.Get 10RS per submission",
    })
}

onMounted(() => {

})
</script>
<style scoped lang="scss">
.username {
    margin: 6px 0;
    padding: 12px;
    border-bottom: 1px solid rgb(225, 225, 225);

    .left {
        width: 35px;
        float: left;
        margin: 6px 6px 0 0;
        border-radius: 1rem;
    }
}

.username .name {
    font: 14px/20px '微软雅黑';

    span {
        color: rgb(206, 28, 36);
        font-weight: bold;
    }
}

.imgbox {
    max-width: 92%;
    height: 92%;
    margin: 4px auto;
    margin-left: 39px;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: space-between;

    img {
        width: 33.3%;
        min-height: 80px;
        max-height: 140px;
        padding: 2px;
        object-fit: cover;
    }
}

.imgbox:after {
    content: '';
    display: block;
    width: 33.3%;
}

.thumbs {
    margin-left: 41px;

    .time {
        vertical-align: middle;
        font: 12px/18px '微软雅黑';
        color: rgb(144, 144, 144);
    }
}

.disabled {
    opacity: 0.9;
    cursor: not-allowed;
}

.thumbs img {
    width: 14px;
}

.thumbs .time img {
    display: inline-block;
    margin-right: 6px;
}

.thumbs .praise {
    margin: 8px 0;
    font: 13px/20px '微软雅黑';

    img {
        float: left;
        margin: 3px 6px 0 0;
    }
}

.thumbs .comment {
    font: 14px/20px '微软雅黑';

    span {
        color: rgb(206, 28, 36);
        font-weight: bold;
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}

.tip {
    width: 80%;
    height: 12%;
    border-radius: 10px;
    text-align: left;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 10px;
    color: #fff;
    /* border: 1px solid #ccc;*/
    background-color: rgb(189, 49, 45);
}
</style>