<template>
  <div class="Projects">
    <div class="basicProjects">
      <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #default="{ list }">
          <!-- levelList为临时数据，将levelList 换成list即可 -->
          <div class="basicProjectsList">
            <div class="basicItem" v-for="(item, index) in tableData.list" :key="index">
              <div class="basicItemLeft">
                <img :src="imgFlag(item.icon)">
              </div>
              <div class="basicItemRight">
                <div class="name">
                  {{ item.goods_name }}
                </div>
                <div class="detail">
                  <div class="detailLeft">
                    <div class="unitprice">
                      <span>Unit Price</span>
                      <span style="color:#bd312d">{{ cutOutNum(item.money) }} RS</span>
                    </div>
                    <div class="dailyearnings">
                      <span>Daily earnings</span>
                      <span style="color:#e18353">{{ cutOutNum(item.money * (item.rate / 100)) }} RS</span>
                    </div>
                    <div class="totalrevenue">
                      <span>Total revenue</span>
                      <span style="color:#484846">{{ cutOutNum(item.total_reward) }} RS</span>
                    </div>
                  </div>
                  <div class="detailRight">

                    <template v-if="item.status == 1">
                      <template v-if="item.receive == 1">
                        <div class="pay"> <span @click="onReceive(item)" size="mini"> Get</span> </div>

                      </template>
                      <template v-else>
                        <div class="pay" style="background: #e0e0e0;"> <span @click="onReceiveNo(item)" size="mini">
                            Get</span> </div>
                      </template>
                    </template>
                    <template v-else>
                      <span>{{ t('结束') }}</span>
                    </template>



                  </div>
                </div>
                <div class="tags">
                  <span v-for="(tag, i) in item.tags">
                    {{ tag }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </template>
      </MyListBase>
    </div>

    <van-dialog :closeOnClickOverlay="true" v-model:show="tipShow" style="border-radius: 0" :showConfirmButton="false"
      class-name="home_tip_show">
      <div class="dialog_top">
        <img :src="rgimg">
      </div>
      <div class="dialog_content">
        <div style="color: #fff;   margin-top: -200px;" class="notice_list">
          <div style="padding: 0 1rem;padding-bottom: 1rem;max-height: 24rem;overflow-y: auto;">
            <h3 style="text-align: center;">Collection instructions</h3>
            <p>
              After purchasing the equipment,you canreceive the income of the day 24 hours laterand you can also
              accumulate
              daily incomeand receive income at any time
            </p>
          </div>
        </div>
      </div>
      <div class="dialog_confirm_btn" @click="onReceivehttp">
        <img style="width: 65%;margin: 0 auto;" :src="rgimgb" />
        <div style="margin-top: -26px;height: 45px;color: #fff;">{{ qdtxt }}</div>
        <img style="width: 15px;margin-top: -48px;margin-left: 75px;" :src="rgimgd" />
        <img style="width: 15px;margin-top: -26px;margin-left: 220px;" :src="rgimgd" />
      </div>
    </van-dialog>
  </div>
  <!-- <MyLoading :show="loadingShow" title="Loading..."></MyLoading> -->
</template>

<script lang="ts">
import "swiper/css";
import "swiper/css/navigation";
import { defineComponent } from 'vue'
import { Swiper, SwiperSlide } from "swiper/vue";
import { Swipe, SwipeItem, Progress, Col, Row } from 'vant'
import { Navigation, Pagination } from 'swiper';
import { Dialog } from "vant";
export default defineComponent({
  components: {
    [Swipe.name]: Swipe,
    [SwipeItem.name]: SwipeItem,
    [Swiper.name]: Swiper,
    [Navigation.name]: Navigation,
    [Pagination.name]: Pagination,
    [Progress.name]: Progress,
    [Col.name]: Col,
    [Row.name]: Row,
    [Dialog.Component.name]: Dialog.Component,
  }
})
</script>

<script lang="ts" setup>
import rgimg from '../assets/rg.gif';
import rgimgb from '../assets/rgb.png';
import rgimgd from '../assets/rgd.png';
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import Product from '../assets/img/project/product.png';
import MyListBase from './ListBase.vue';
// import MyLoading from './Loading.vue';
import { getSrcUrl, lang, _alert, cutOutNum } from "../global/common";
import http from "../global/network/http";
import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();
const router = useRouter()
const loadingShow = ref(true)
const pageRef = ref()
let pageUrl = ref('c=Product&a=order&status=1')
const tableData = ref<any>({})

const onPageSuccess = (res: any) => {
  tableData.value = res.data
  loadingShow.value = false
  basicProjects.value.list = res.data
}
const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}


const tipShow = ref(false);
const qdtxt = ref(t('收到'));
let isRequest = false
const onReceive = (item: any) => {
  tipShow.value = true;
  Receiveh.value = item;
  return;
}
const Receiveh = ref<any>({})

const onReceivehttp = () => {

  if (isRequest) {
    return
  } else {
    isRequest = true
  }
  qdtxt.value = 'loading…';
  http({
    url: 'c=Product&a=receiveProfit',
    data: { osn: Receiveh.value.osn }
  }).then((res: any) => {
    isRequest = false
    if (res.code != 1) {
      // toast.clear()
      tipShow.value = false;
      qdtxt.value = t('收到');
      _alert(res.msg)
      return
    }
    _alert({
      type: 'success',
      message: res.msg,
      onClose: () => {
        tipShow.value = false;
        qdtxt.value = t('收到');
        Receiveh.value.receive = 0
      }
    })
  })

}

// const onReceive = (item: any) => {
//   if (isRequest) {
//     return
//   } else {
//     isRequest = true
//   }
//   http({
//     url: 'c=Product&a=receiveProfit',
//     data: { osn: item.osn }
//   }).then((res: any) => {
//     isRequest = false
//     if (res.code != 1) {
//       _alert(res.msg)
//       return
//     }
//     _alert({
//       type: 'success',
//       message: res.msg,
//       onClose: () => {
//         item.receive = 0
//       }
//     })
//   })
// }

// let isRequest = false
const onReceiveNo = (item: any) => {
  _alert('Currently unavailable')
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
const basicProjects = ref<basicProjects>({
  sort: 'Basic',
  list: []
})
// 获取project详细信息
const goProjectDetail = (item: project) => {
  router.push({ name: 'Project_detail', params: { pid: item.id } })
}

</script>
<style lang="scss" scoped>
.dialog_confirm_btn {
  text-align: center;
  height: 55px;
  background: #369642;
}

.Projects {
  .basicProjects {
    margin-top: 1rem;

    .basicProjectsSplit {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .basicProjectsList {
      .basicItem {
        box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 0.25rem 0px, rgba(14, 30, 37, 0.32) 0px 2px 1rem 0px;
        margin-top: 1.25rem;
        padding: 1.375rem 0.625rem;
        box-sizing: border-box;
        // height: 8.5rem;
        height: auto;
        display: flex;
        align-items: center;
        width: 100%;
        background: #fff;
        border-radius: 0.875rem;
        position: relative;

        .basicItemLeft {
          width: 5.9375rem;
          display: flex;
          justify-content: center;
          align-items: center;

          img {
            width: 5.9375rem;
          }
        }

        .basicItemRight {
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

            .detailRight {
              width: 3rem;
              height: 3rem;

              .pay {
                width: 3rem;
                height: 3rem;
                background: #bd312d;
                color: #fff;
                border-radius: 0.3125rem;
                display: flex;
                justify-content: center;
                align-items: center;
              }
            }
          }

          .tags {
            display: flex;
            flex-wrap: wrap;
            height: auto;
            font-size: 0.75rem;
            color: #6c6b6a;
            position: relative;
            width: 100%;

            span {
              padding: 0.25rem 0.25rem;
              border-radius: 0.375rem;
              background: #e0e0e0;
              display: inline-block;
              color: #bd312d;
              zoom: 0.5;
              margin-top: 0.25rem;
              -moz-transform: scale(0.5);
              -moz-transform-origin: top left;
              -o-transform: scale(0.5);
              -o-transform-origin: top left;
              margin-right: 0.625rem;
            }
          }
        }
      }
    }
  }
}
</style>