<template>
  <div class="Projects">
    <div class="basicProjects">
      <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #default="{ list }">
          <div class="basicProjectsList">
            <div class="basicItem" v-for="(item, index) in tableData.list" :key="index">

              <div class="basicItemLeft">
                <img :src="imgFlag(item.icon)" class="imgs">
                <span style="position: relative;color: #009900;left: 20%;left: 0.5rem;top: -6.5rem;font-weight: bold;font-size: 0.9rem;">{{ item.name }}</span>
                <div class="Countdown">
                  <span v-if="item.djs > now" style="position: absolute; right: 6px; top: 8px;font-size: 12px;">
                    <van-count-down @finish="onFinish(item)" format="HH:mm:ss" :time="djs(item.djs)"></van-count-down>
                  </span>
                  <span v-if="item.dssj > now" style="position: absolute; right:6px; top: 8px;font-size: 12px;">
                    <van-count-down @finish="onFinish(item)" format="HH:mm:ss" :time="djs(item.dssj)"></van-count-down>
                  </span>
                </div>
                <img v-if="item.status == 9" :src="sold_out" class="sold_out">
              </div>

              <div class="basicItemRight">
                <div class="detail">
                  <div class="detailLeft_left">
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
                      <span style="color: #ede000;">₹{{ (item.days * item.price * item.rate / 100).toFixed(2) }}</span>
                    </div>
                    <div class="totalrevenue" style="height: 2.5rem;margin-bottom: 0;">
                      <span style="color: #ede000;font-weight: bold;font-size: 1rem;">₹{{ item.price }}</span>
                      <div class="buy" @click="getProjectDetail(item)">Buy</div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </template>
      </MyListBase>
    </div>
  </div>
</template>
<script lang="ts">
import { CountDown } from "vant";
import sold_out from '../assets/img/project/sold_out.png'

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
import MyListBase from './ListBase.vue';
import { getSrcUrl, lang, _alert, cutOutNum } from "../global/common";
import http from "../global/network/http";
import { useI18n } from 'vue-i18n';
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
</script>
<style lang="scss" scoped>
.Projects {
  .basicProjects {
    .basicProjectsList {
      margin-top: 1rem;

      .basicItem {
        width: 100%;
        height: 7rem;
        box-shadow: 0px 0px 10px 0px #d1d1d1;
        margin-bottom: 1rem;
        background: url(../assets/img/project/peroject_bg.png);
        background-repeat: no-repeat;
        background-size: 100% 100%;
        border-radius: 10px;
        overflow: hidden;

        .basicItemLeft {
          width: 45%;
          height: 7rem;
          float: left;
          background-color: white;
          border-radius: 10px;

          .imgs {
            height: 7rem;
            border-radius: 10px;
          }

          .sold_out {
            position: relative;
            z-index: 1;
            width: 4rem;
            height: 4rem;
            opacity: 0.6;
            left: 3.5rem;
            top: -8.5rem;
          }

          .Countdown {
            position: relative;
            height: 2rem;
            width: 6rem;
            left: 4rem;
            top: -3.5rem;

            span {
              background: #009900 url(../assets/djs.png) 3px center no-repeat;
              background-size: 18px;
              padding: 2px 3px 2px 25px;
              border-radius: 10px;

              .van-count-down {
                color: #fff;
              }
            }
          }
        }

        .basicItemRight {
          height: 7rem;
          margin-left: 3%;
          width: 48%;
          float: left;

          .detail {
            font-size: 1rem;
            display: flex;
            align-items: flex-start;
            flex-direction: column;
            justify-content: center;
            margin-top: 0.6rem;

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

                .buy {
                  width: 6rem;
                  background-color: white;
                  color: #009900;
                  height: 1.6rem;
                  line-height: 1.6rem;
                  text-align: center;
                  border-radius: 30px;
                  font-weight: bold;
                }
              }
            }
          }
        }
      }
    }
  }
}
</style>