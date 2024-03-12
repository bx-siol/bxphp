<template>
  <div class="Projects">
    <div class="basicProjects">
      <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #default="{ list }">
          <!-- levelList为临时数据，将levelList 换成list即可 -->
          <div class="basicProjectsList">
            <div class="basicItem" v-for="(item, index) in tableData.list" :key="index">
              <div style="display: flex;width: 100%;align-items: center;flex-direction: column;">
                <div class="basicItemLeft">
                  <img :src="imgFlag(item.icon)" class="imgs">
                  <img v-if="item.status == 9" :src="sold_out" class="sold_out">
                </div>
                <div class="basicItemRight">
                  <div class="name">
                    <div style="color: #64503e;">
                      <div>{{ item.name }}</div>
                    </div>
                    <span v-if="item.djs > now" style="position: absolute; right: 6px; top: 8px;font-size: 12px;">
                      <van-count-down @finish="onFinish(item)" format="HH:mm:ss" :time="djs(item.djs)"> </van-count-down>
                    </span>
                    <span v-if="item.dssj > now" style="position: absolute; right:6px; top: 8px;font-size: 12px;">
                      <van-count-down @finish="onFinish(item)" format="HH:mm:ss" :time="djs(item.dssj)"> </van-count-down>
                    </span>
                  </div>
                  <div class="detail">
                    <div class="detailLeft">
                      <div class="unitprice">
                        <span>{{ t('单价') }}: </span>
                        <span>₹{{ cutOutNum(item.price) }}</span>
                      </div>
                      <div class="dailyearnings">
                        <span>{{ t('每日收入') }}: </span>
                        <span>₹{{ cutOutNum(item.price * item.rate / 100) }}</span>
                      </div>
                      <div class="totalrevenue">
                        <span>{{ t('总收入') }}: </span>
                        <span>₹{{ cutOutNum(item.days * (item.price * item.rate / 100)) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="detailRight">
                <!-- <div class="addRs">₹{{ cutOutNum(item.price) }}</div> -->

                <div class="pay" @click="getProjectDetail(item)">
                  buy
                </div>
              </div>
            </div>
          </div>
        </template>
      </MyListBase>
    </div>
  </div>
  <!-- <MyLoading :show="loadingShow" title="Loading..."></MyLoading> -->
</template>
<script lang="ts">
import { CountDown } from "vant";
import sold_out from '../assets/img/project/sold_out.png'

import trial from "../assets/img/2.png";

export default defineComponent({
  components: {
    MyListBase,
    [Image.name]: Image,
    [CountDown.name]: CountDown
  },
})

</script>
<script lang="ts" setup>
import { onMounted, ref, defineComponent } from "vue";
import { useRoute, useRouter } from "vue-router";
import Product from '../assets/img/project/product.png';
import sqimg from '../assets/sq.png';
import MyListBase from './ListBase.vue';
// import MyLoading from './Loading.vue';
import { getSrcUrl, lang, _alert, cutOutNum } from "../global/common";
import http from "../global/network/http";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();
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
type project = {
  id: string | number,
  img: string,
  name: string,
  unitPrice: string | number,
  dailyEarnings: string | number,
  totalRevenue: string | number,
  tags: Array<string>
}
type basicProjects = {
  sort: string,
  list: Array<project>
}

const router = useRouter()
const loadingShow = ref(true)
const pageRef = ref()
let pageUrl = ref('c=Product&a=list&ishot=1')
const tableData = ref<any>({})

const onPageSuccess = (res: any) => {
  tableData.value = res.data
  loadingShow.value = false

}
const getProjectDetail = (item: any) => {
  router.push({ name: 'Project_detail', params: { pid: item.gsn, gsn: item.gsn } })
}


const basicProjects = ref<basicProjects>({
  sort: 'Basic',
  list: []
})
// 获取project详细信息
const goProjectDetail = (item: any) => {
  router.push({ name: 'Project_detail', params: { gsn: item.gsn } })
}

</script>
<style lang="scss" scoped>
.Projects {
  .basicProjects {

    .basicProjectsSplit {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .basicProjectsList {
      // column-count: 2;
      // column-gap: 10px;
      margin-top: 1rem;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;

      .basicItem {
        box-sizing: border-box;
        height: auto;
        display: flex;
        flex-direction: column;
        background: #fff;
        position: relative;
        border-radius: 10px;
        box-shadow: 0px 0px 12px 2px rgba(225, 225, 225);
        margin-bottom: 1rem;
        padding: 0.625rem;
        width: 48%;  

        .basicItemLeft {
          width: 6rem;
          height: 5rem;
          // margin: 1rem auto 0.8rem;
          position: relative;

          .imgs {
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

          .name {
            font-weight: bold;
            display: flex;
            align-items: flex-start;
            flex-direction: column;
            margin: 0.6rem 0;
            font-size: 14px;
            white-space: nowrap;
            
            span {
              background: #64503e url(../assets/djs.png) 3px center no-repeat;
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

            .detailLeft {
              font-size: 1rem;
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: flex-start;
              color: #3d3d3d !important;
              width: 100%;

              &>div {
                margin-bottom: 0.3125rem;
                width: 100%;
                display: flex;
                justify-content: flex-start;
                flex-direction: row;
                align-items: center;

                span:first-child {
                  color: #222;
                  font-size: 0.75rem;
                  white-space: nowrap;
                  font-weight: normal;
                }

                span {
                  font-size: 0.75rem;
                  color: #64503e;
                  font-weight: bold;
                }
              }
            }

          }
        }
      }

      .detailRight {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.4rem;
        background: linear-gradient(to bottom, #c49b6c 20%, #a77d52);
        border-radius: 6px;
        font-size: 14px;
        .addRs {
          color: #ff9900;
          font-weight: bold;
        }

        .pay {
          color: #fff;
          font-weight: bold;

          img {
            width: 2rem;
          }
        }
      }
    }
  }
}
</style>