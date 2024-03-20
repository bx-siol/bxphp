<template>
  <div class="Projects">
    <van-tabs v-model:active="active" swipe-threshold="2" animated>
      <div v-for="(itemc, indexc) in newsdata">
        <van-tab :key="indexc" :title="itemc.name"
          v-if="tableData.findIndex((itemsc: { category_name: any; }) => itemsc.category_name == itemc.name && itemc.id != 1019) > -1">
          <div class="basicProjects">
            <div class="basicProjectsList">
              <template v-for="(item, index) in tableData" :key="index">
                <div v-if="itemc.name == item.category_name" class="basicItem">
                  <div class="basicItemLeft">
                    <img :src="imgFlag(item.icon)" class="productImg ">
                    <img :key="item.key" v-if="item.status == 9" :src="sold_out" class="sold_out">
                  </div>
                  <div class="basicItemRight">
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
                    <div class="detail">
                      <div class="detailLeft">

                        <div class="unitprice">
                          <span v-if="itemc.id == 1019">Points: </span>
                          <span v-else>Unit Price: </span>
                          <span v-if="itemc.id == 1019">{{ cutOutNum(item.price) }} IG</span>
                          <span v-else>₹{{ cutOutNum(item.price) }}</span>
                        </div>
                        <div class="dailyearnings">
                          <span>Daily earnings: </span>
                          <span>₹{{ cutOutNum(item.rate / 100 * item.price) }}</span>
                        </div>
                        <div class="totalrevenue">
                          <span>Total revenue: </span>
                          <span>₹{{ cutOutNum(item.rate / 100 * item.price * item.days) }}</span>
                        </div>
                        <div v-if="item.is_xskc == 1" class="totalrevenue">
                          <span>Current inventory</span>
                          <span>{{ item.status == 9 ? 0 : item.kc }} </span>
                        </div>
                      </div>

                    </div>
                  </div>
                  <div class="detailRight" @click="getProjectDetail(item)">
                    <div class="pay">
                      join
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>

        </van-tab>

      </div>

    </van-tabs>

    <!-- <div v-for="(itemc, indexc) in newsdata  " :key="indexc" >
    </div> -->
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
export default defineComponent({
  components: {
    MyListBase,
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
const getProjectDetail = (item: any) => {
  router.push({ name: 'Project_detail', params: { pid: item.gsn } })

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
      color: #fff !important;
      background: #666 !important;
      border: 1px solid #666 !important;
      padding: 0.3rem 0.4rem;
      white-space: nowrap;
      width: 6.85rem;
      border-radius: 1rem;
      text-align: center;
      font-weight: bold;
      overflow: hidden;   
    }
  }

  :deep(.van-tab--active) {
    .van-tab__text {
      color: #84973b !important;
      background: #fff !important;
      border: 1px solid #fff !important;
      padding: 0.3rem 0.4rem;
      white-space: nowrap;
      width: 6.85rem;
      border-radius: 1rem;
      text-align: center;
      font-weight: bold;
      overflow: hidden;
    }
  }

  :deep(.van-tabs__line) {
    display: none;
    background-color: #fff;
  }

  :deep(.van-tabs__nav--line) {
    padding-top: 0.425rem;
    background: #84973b;
  }




  .basicProjects {
    margin-top: 1rem;

    .basicProjectsList {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;

      .basicItem {
        background-color: #fff;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        width: 100%;
        .basicItemLeft {
          width: 8rem;
          height: 6rem;
          margin: 0.675rem auto 0.875rem;
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
          margin-right: 0.625rem;
          flex: 1;

          .name {
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            padding-bottom: 2px;
            margin: 0.625rem 0;

            .djs {
              width: 6rem;
              height: 2rem;
              display: flex;
              align-items: center;
              justify-content: center;
              position: absolute;
              top: 4px;
              right: -4px;
            }

            .djs span {
              background: #84973b url(/src/assets/djs.png) 3px center no-repeat;
              background-size: 18px;
              padding: 2px 3px 2px 25px;
              border-radius: 10px;
            }
          }


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
              flex-direction: row;
              justify-content: center;
              align-items: flex-start;
              flex-direction: column;

              &>div {
                margin-bottom: 0.3125rem;
                width: 100%;
                display: flex;
                flex-direction: row;
                justify-content: flex-start;
                text-align: center;
                line-height: 0.875rem;

                span:nth-child(2) {
                  color: #84973b;
                  font-size: 0.75rem;
                  font-weight: bold;
                }

                span {
                  font-size: 0.75rem;
                  color: #222;
                  white-space: nowrap;
                }
              }
            }
          }

        }

        .detailRight {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 0.3rem 3rem;
          margin: 0.4rem auto;
          background: #84973b;
          border-radius: 6px;
          height: 1rem;

          .pay {
            color: #fff;
            font-weight: bold;
            text-transform: uppercase !important;
          }
        }
      }
    }
  }
}
</style>