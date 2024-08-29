<template>
  <div class="Projects">
    <van-tabs v-model:active="active" swipe-threshold="2" animated>
      <div v-for="(itemc, indexc) in newsdata">
        <van-tab :key="indexc" :title="itemc.name" v-if="tableData.findIndex((itemsc: { category_name: any; }) => itemsc.category_name == itemc.name) > -1">
          <div class="basicProjects">
            <div class="basicProjectsList">
              <div v-for="(item, index) in tableData" :key="index">
                <div v-if="itemc.name == item.category_name" class="basicItem">
                  <div class="detailLeft">
                    <div class="basicItemLeft">
                      <div class="Countdown">
                        <div class="djs">
                          <span v-if="item.djs > now" style="position: absolute; right: 6px; top: 8px;font-size: 12px;">
                            <van-count-down @finish="onFinish(item)" format="HH:mm:ss" :time="djs(item.djs)">
                            </van-count-down>
                          </span>
                          <span v-if="item.dssj > now" style="position: absolute; right:6px; top: 8px;font-size: 12px;">
                            <van-count-down @finish="onFinish(item)" format="HH:mm:ss" :time="djs(item.dssj)">
                            </van-count-down>
                          </span>
                        </div>
                      </div>
                      <div class="detail">
                        <img :src="logo" style="height: 1.5rem;width: 7rem;margin: 0.5rem 0;">
                        <div class="detailLeft_left">
                          <div class="unitprice">
                            <span>{{ item.name }}</span>
                            <span style="color:#cb1a00">₹{{ cutOutNum(item.price) }}</span>
                          </div>
                          <div class="unitprice">
                            <span>{{ t('收入天数') }}</span>
                            <span>{{ item.days }} days</span>
                          </div>
                          <div class="dailyearnings">
                            <span>{{ t('每日收入') }}</span>
                            <span>₹{{ cutOutNum(item.price * item.rate / 100) }}</span>
                          </div>
                          <div class="totalrevenue">
                            <span>{{ t('总收入') }}</span>
                            <span style="color: #cb1a00;">₹{{ (item.days * item.price * item.rate / 100).toFixed(2)
                              }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="basicItemRight">
                      <img :src="imgFlag(item.icon)" class="imgs ">
                      <img v-if="item.status == 9" :src="sold_out" class="sold_out">
                    </div>
                  </div>

                  <div class="detailRight" @click="getProjectDetail(item)">
                    <img :src="pay" style="height: 1.5rem; width: 1.5rem;">
                  </div>

                </div>
              </div>
            </div>

          </div>
        </van-tab>
      </div>
    </van-tabs>
    <MyTab></MyTab>
  </div>
  <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>
<script lang="ts">
import { onMounted, ref, defineComponent, onUnmounted, watchEffect, reactive, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import MyListBase from './ListBase.vue';
import MyLoading from './Loading.vue';
import MyTab from "./Tab.vue";
import http from "../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../global/common";
import { _alert, lang, cutOutNum } from "../global/common";
import { Tab, Tabs, CountDown } from "vant";
export default defineComponent({
  components: {
    MyListBase,
    MyLoading,
    MyTab,
    [Image.name]: Image,
    [Tab.name]: Tab,
    [Tabs.name]: Tabs,
    [CountDown.name]: CountDown
  },
})
</script>

<script lang="ts" setup>
import { useI18n } from 'vue-i18n';
import sold_out from '../assets/img/project/sold_out.png'
import pay from '../assets/img/project/pay.png';
import logo from '../assets/img/home/home_top.png';

const { t } = useI18n();

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
  http({
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
})
</script>

<style lang="scss" scoped>
.Projects {
  :deep(.van-tabs__wrap) {
    .van-tabs__nav--line {
      width: 96% !important;
      margin-left: 3%;
    }
  }

  :deep(.van-tabs__content) {
      margin-top: 1rem;
    }

  :deep(.van-tab) {
    padding:  0 0.5rem  !important;

    &.van-tab--active {
      position: relative;
      background-color: transparent;
    }

    .van-tab__text {
      border: none !important;
      background-color: #e6e6e6 !important;
      color: #222 !important;
      padding: 0.3rem 0.4rem;
      white-space: nowrap;
      width: 6rem;
      border-radius: 1rem;
      text-align: center;
    }
  }

  :deep(.van-tab--active) {
    .van-tab__text {
      border: none !important;
      background-color: #64523e !important;
      color: #fff !important;
      padding: 0.3rem 0.4rem;
      white-space: nowrap;
      width: 6rem;
      border-radius: 1rem;
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

  .basicProjects {
    .basicProjectsList {
      margin-bottom: 2rem;

      .basicItem {
        height: 7.5rem;
        margin-bottom: 1rem;
        display: flex;
        flex-direction: row;
        position: relative;

        .detailLeft {
          height: 7.5rem;
          float: left;
          width: 88%;
          background: url(../assets/img/project/peroject_bg.png);
          background-repeat: no-repeat;
          background-size: 100% 100%;

          .basicItemLeft {
            height: 7.5rem;
            padding-left: 3%;
            width: 44%;
            float: left;

            .Countdown {
              position: absolute;
              height: 2rem;
              width: 6rem;
              left: 9rem;

              span {
                background: red url(../assets/djs.png) 3px center no-repeat;
                background-size: 18px;
                padding: 2px 3px 2px 25px;
                border-radius: 10px;

                .van-count-down {
                  color: #fff;
                }
              }
            }

            .detail {
              font-size: 1rem;
              display: flex;
              align-items: flex-start;
              flex-direction: column;
              justify-content: center;
              margin-top: 0.2rem;

              .detailLeft_left {
                font-size: 1rem;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: flex-start;
                color: #fff;
                width: 100%;

                &>div {
                  margin-bottom: 0.3125rem;
                  width: 100%;
                  display: flex;
                  justify-content: space-between;
                  flex-direction: row;
                  align-items: center;

                  span:first-child {
                    font-size: 0.75rem;
                    white-space: nowrap;
                    font-weight: normal;
                  }

                  span {
                    font-size: 0.75rem;
                    font-weight: bold;
                  }
                }
              }
            }
          }

          .basicItemRight {
            width: 35%;
            height: 7.5rem;
            float: left;
            display: flex;
            align-items: center;
            justify-content: space-around;
            margin-left: 15%;

            .sold_out {
              position: relative;
              z-index: 1;
              width: 4rem;
              height: 4rem;
              opacity: 0.6;
              left: -47%;
              top: 29%;
              transform: translate(-50%, -50%);
            }
          }
        }

        .detailRight {
          width: 10%;
          height: 7.5rem;
          margin-left: 2%;
          background-color: black;
          display: flex;
          align-items: center;
          justify-content: space-around;
        }
      }
    }
  }
}
</style>