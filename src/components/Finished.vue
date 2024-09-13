<template>
  <div class="Finished">
    <div class="FinishedProjects">
      <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #default="{ list }">

          <div class="FinishedProjectsList">
            <div class="FinishedItem" v-for="(item, index) in list" :key="index">
              <div class="FinishedItemTop">
                <div class="FinishedItemLeft">
                  <img :src="imgFlag(item.icon)">
                </div>
                <div class="FinishedItemRight">
                  <div class="name">
                    {{ item.goods_name }}
                  </div>
                  <div class="detail">
                    <div class="detailLeft">
                      <div class="totalrevenue">
                        <span>Total Revenue</span>
                        <span style="color:#bd312d">{{ cutOutNum(item.total_reward) }} RS</span>
                      </div>
                      <div class="dailyearnings">
                        <span>Daily earnings</span>
                        <span> {{ cutOutNum(item.rate / 100 * item.money ) }} RS</span>
                      </div>
                      <div class="returnrate">
                        <span>Return Rate</span>
                        <span>{{ ((item.price * (item.rate / 100) * item.days) / item.price) * 100 }}%</span>
                      </div>
                      <div class="projectcycle">
                        <span>Project Cycle</span>
                        <span>{{ item.days }} Day</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="FinishedItemBot">
                <span>Finish</span>
              </div>
            </div>
          </div>
        </template>
      </MyListBase>
    </div>
    <MyTab></MyTab>
  </div>
  <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>
<script lang="ts" setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import Product from '../assets/img/project/product.png';
import MyListBase from './ListBase.vue';
import MyLoading from './Loading.vue';
import MyTab from "./Tab.vue";

import http from "../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../global/common";
import { _alert, lang, cutOutNum } from "../global/common";

const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const info = ref<any>({})
type finishedProject = {
  id: string | number,
  img: string,
  name: string,
  totalRevenue: string | number,
  dailyEarnings: string | number,
  returRate: string | number,
  projectCycle: string
}


const router = useRouter()
const loadingShow = ref(true)
const pageRef = ref()
let pageUrl = ref('c=Product&a=order&status=9')
const tableData = ref<any>({})

const onPageSuccess = (res: any) => {
  tableData.value = res.data
  loadingShow.value = false
}

// 获取project详细信息
const goFinishedProjectDetail = (item: finishedProject) => {
  router.push({ name: 'Project_detail', params: { pid: item.id } })
}



const finishedProjects = ref<Array<finishedProject>>([
  { id: 1, img: Product, name: 'CS3Y-MB-AG', totalRevenue: '1,000', dailyEarnings: '15', returRate: '200%', projectCycle: '45 Day' },

])
</script>
<style lang="scss">
.Finished {
  .FinishedProjects {
    margin-top: 1rem;

    .FinishedProjectsList {
      overflow: auto;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding-bottom: 0.625rem;

      .FinishedItem {
        // height: 10.3125rem;
        height: auto;
        display: flex;
        align-items: center;
        flex-direction: column;
        background: #f5f6fe;
        border-radius: 0.875rem;
        margin-top: 1.25rem;
        width: 98%;
        box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 0.25rem 0px, rgba(14, 30, 37, 0.32) 0px 2px 1rem 0px;

        .FinishedItemTop {
          margin-top: 1.25rem;
          box-sizing: border-box;
          height: 8.5rem;
          display: flex;
          align-items: center;
          width: 100%;
          border-radius: 0.875rem;
          position: relative;
        }

        .FinishedItemLeft {
          margin-left: 1rem;
          width: 5.9375rem;
          display: flex;
          justify-content: center;
          align-items: center;

          img {
            width: 5.9375rem;
          }
        }

        .FinishedItemRight {
          display: flex;
          flex-direction: column;
          margin-left: 0.375rem;
          flex: 1;

          .name {
            font-size: 0.875rem;
            font-weight: bold;
          }

          .detail {
            font-size: 1rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-top: 0.625rem;
            width: 100%;

            .detailLeft {
              font-size: 1rem;
              flex: 1;
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: flex-start;
              margin-right: 1.25rem;

              &>div {
                margin-bottom: 0.3125rem;
                width: 100%;
                display: flex;
                justify-content: space-between;

                span {
                  font-size: 0.75rem;
                }
              }
            }
          }
        }

        .FinishedItemBot {
          width: 100%;
          margin-bottom: 0.625rem;
          height: 1.25rem;
          display: flex;
          align-items: center;
          justify-content: center;

          span {
            display: inline-block;
            width: 82%;
            box-sizing: border-box;
            text-align: center;
            background: #8e8b8a;
            border-radius: 0.3125rem;
            font-size: 0.875rem;
            height: 1.25rem;
            line-height: 1.25rem;
            color: #3d3d3b;
          }
        }
      }
    }
  }
}
</style>