<template>
    <div class="Projects">
        <van-tabs v-model:active="active" swipe-threshold="2" animated>
            <div v-for="(itemc, indexc) in newsdata">
                <van-tab :key="indexc" :title="itemc.name"
                         v-if="tableData.findIndex((itemsc: { category_name: any; }) => itemsc.category_name == itemc.name && itemc.id != 1019) > -1">
                    <div class="basicProjects">
                        <div class="basicProjectsList">
                                <div class="basicItem" v-for="(item, index) in tableData.filter(function(t){return t.category_name==itemc.name})" :key="index">
                                    <div style="display: flex;">
                                        <div class="basicItemRight">
                                            <div class="detail">
                                                <div class="detailLeft">
                                                    <div>
                                                        <img :src="imgFlag(item.icon)" class="productImg ">
                                                        <img :key="item.key" v-if="item.status == 9" :src="sold_out" class="sold_out">
                                                    </div>
                                                    <div class="name">
                                                        {{ item.name }}
                                                        <div class="djs">
                                                            <span v-if="item.djs > now">
                                                                <van-count-down @finish="onFinish(item)" format="HH:mm:ss" :time="djs(item.djs)"
                                                                                style="color:#fff;font-size: 12px;"> </van-count-down>
                                                            </span>
                                                            <span v-if="item.dssj > now">
                                                                <van-count-down @finish="onFinish(item)" format="HH:mm:ss" :time="djs(item.dssj)"
                                                                                style="color:#fff;font-size: 12px;"> </van-count-down>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="unitprice">
                                                        <span v-if="itemc.id == 1019">Points</span>
                                                        <span v-else>Unit Price</span>
                                                        <span v-if="itemc.id == 1019" style="color:black">{{ cutOutNum(item.price) }} IG</span>
                                                        <span v-else style="color:black">₹{{ cutOutNum(item.price) }}</span>
                                                    </div>
                                                    <div class="dailyearnings">
                                                        <span>Daily earnings</span>
                                                        <span style="color:black">₹{{ cutOutNum(item.rate * item.price / 100) }}</span>
                                                    </div>
                                                    <div class="dailyearnings">
                                                        <span>Days</span>
                                                        <span style="color:black">{{ item.days }}</span>
                                                    </div>
                                                    <div class="totalrevenue">
                                                        <span>Total revenue</span>
                                                        <span style="color:black">
                                                            ₹{{
 (item.rate * item.price * item.days / 100).toFixed(2)
                                                            }}
                                                        </span>
                                                    </div>
                                                    <div v-if="false" class="totalrevenue">
                                                        <span>Current inventory</span>
                                                        <span style="color:black">{{ item.status == 9 ? 0 : item.kc }} </span>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="detailRight" @click="getProjectDetail(item)">
                                        <div class="pay">
                                            <span>BUY</span>
                                        </div>
                                    </div>
                                </div>
                        </div>

                    </div>
                </van-tab>

            </div>

        </van-tabs>

        <BuyProject ref="buyp"></BuyProject>
        <!--<div v-for="(itemc, indexc) in newsdata  " :key="indexc" >
    </div>-->
        <MyTab></MyTab>
    </div>
  <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>
<script lang="ts">
    import { onMounted, ref, defineComponent, onUnmounted, watchEffect, reactive, computed } from "vue";
    import { useRoute, useRouter } from "vue-router";
    import Product from '../assets/img/project/product.png';
    import MyListBase from './ListBase.vue';
    import MyLoading from './Loading.vue';
    // import countdown from './countdown.vue'
    import MyTab from "./Tab.vue";
    import http from "../global/network/http";
    import sqimg from '../assets/sq.png';
    import sold_out from '../assets/img/project/sold_out.png'

    import { getSrcUrl, goRoute, imgPreview } from "../global/common";
    import { _alert, lang, cutOutNum } from "../global/common";
    import { Tab, Tabs, CountDown } from "vant";
    import { number, time } from "echarts";
    import BuyProject from "../components/BuyProject.vue";
    export default defineComponent({
        components: {
            MyListBase,
            BuyProject,
            [Image.name]: Image,
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [CountDown.name]: CountDown
        },
    })
</script>

<script lang="ts" setup>
    const now = Date.parse(new Date()) / 1000;

    const onFinish = (item: any) => {
        item.status = 9;
        item.key++;

    }

    const djs = (time: number) => {
        time = time * 1000;
        var djs = time - (now * 1000);
        return djs;
    }


    const imgFlag = (src: string) => {
        return getSrcUrl(src, 1)
    }
    const router = useRouter()
    const loadingShow = ref(true)
    const newsdata = ref<any>([])
    const tableData = ref<any>({})

    const active = ref('0')

    const onPageSuccess = (res: any) => {
        tableData.value = res.data
        loadingShow.value = false
    }
    const buyp = ref()
    const getProjectDetail = (item: any) => {
        router.push({ name: 'Project_detail', params: { pid: item.gsn } })
        //buyp.value.init(item.gsn);
    }

    onMounted(() => {
        //自己封装的接口请求方法 aiox
        const delayTime = Math.floor(Math.random() * 1000);
        // setTimeout(() => {
        http({
            //url 就是请求的地址
            url: 'c=Product&a=list',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert({
                    type: 'error',
                    message: res.msg,
                    onClose: () => {
                        router.go(-1)
                    }
                })
                return
            }
            newsdata.value = res.data.category_arr;
            tableData.value = res.data.list
            loadingShow.value = false
        })
        // }, delayTime)

    })
</script>
<style lang="scss" scoped>
    .Projects {

        :deep(.van-tab) {

            &.van-tab--active {
                position: relative;
                background-color: transparent;
                // &::after {
                //     position: absolute;
                //     bottom: -0.3rem;
                //     content: ' ';
                //     border: 2px solid #00b57e;
                //     width: 1rem;
                //     border-radius: 6px;
                //     // border-top: 0.5rem solid ;
                // }
            }

            .van-tab__text {
                border: none !important;
                color: black;
                padding: 0.7rem 0.4rem;
                white-space: nowrap;
                width: 6rem;
                border-radius: 4rem;
                text-align: center;
            }
        }

        :deep(.van-tab--grow:first-of-type) {
            margin-left: -1rem;
        }

        :deep(.van-tabs__wrap) {
            height: auto;
            margin: 1vh 2vw;
        }

        :deep(.van-tab--active) {
            .van-tab__text {
                border: none !important;
                background-color: #eb1700 !important;
                color: #fff !important;
                padding: 0.8rem 0.6rem;
                white-space: nowrap;
                border-radius: 4rem;
                text-align: center;
            }
        }

        :deep(.van-tabs__line) {
            display: none;
            background-color: #fff;
        }

        :deep(.van-tabs__nav--line) {
            padding-top: 0.425rem;
        }

        :deep(.van-tabs__nav) {
            border: 1px solid #eb1700;
            border-radius: 9rem;
            padding: 0rem !important;
        }


        .basicProjects {
            // margin-top: 1rem;
            margin: 1rem 0.6rem 0;

            .basicProjectsList {
                margin-top: 1rem;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                margin-bottom: 4vh;

                .basicItem {
                    box-sizing: border-box;
                    height: auto;
                    display: flex;
                    flex-direction: column;
                    background: #fff;
                    position: relative;
                    border-radius: 10px;
                    margin-bottom: 1rem;
                    padding: 0.425rem 0.625rem 0.625rem;
                    width: 48%;
                    border: 1px solid #8080803b;

                    .basicItemLeft {
                        width: 8rem;
                        height: 7rem;
                        // margin: 1rem auto 0.8rem;
                        position: relative;

                        .productImg {
                            max-width: 100%;
                            max-height: 100%;
                            width: auto;
                            height: auto;
                            position: relative;
                            left: 50%;
                            transform: translateX(-50%);
                        }

                        .sold_out {
                            position: absolute;
                            z-index: 1;
                            width: 4rem;
                            height: 4rem;
                            opacity: 0.6;
                            left: 50%;
                            top: 50%;
                            transform: translate(-50%, -50%);
                        }
                    }

                    .basicItemRight {
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        flex: 1;


                        .detail {
                            font-size: 1rem;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 100%;

                            .detailLeft {
                                font-size: 1rem;
                                flex: 1;
                                display: flex;
                                flex-direction: column;
                                justify-content: center;
                                align-items: flex-start;

                                .name {
                                    font-size: 16px;
                                    font-weight: bold;
                                    display: flex;
                                    align-items: center;
                                    padding-bottom: 2px;
                                    color: black;

                                    .djs {
                                        width: 6rem;
                                        height: 2rem;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        position: absolute;
                                        top: 0.2rem;
                                        right: -0.5rem;
                                    }

                                    .djs span {
                                        background: black url(/src/assets/djs.png) 3px center no-repeat;
                                        background-size: 18px;
                                        padding: 2px 3px 2px 25px;
                                        border-radius: 10px;
                                    }
                                }

                                & > div {
                                    margin-bottom: 0.3125rem;
                                    width: 100%;
                                    display: flex;
                                    justify-content: space-between;

                                    span {
                                        font-size: 0.75rem;
                                        font-weight: bold;
                                        color: #222;
                                    }

                                    span:first-child {
                                        font-weight: normal;
                                    }
                                }
                            }
                        }
                    }

                    .detailRight {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        padding: 0.3rem;
                        background: #eb1700;
                        border-radius: 1rem;
                        color: #fff;
                    }
                }
            }
        }
    }
</style>