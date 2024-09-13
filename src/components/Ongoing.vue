<template>
  <div class="Ongoing">
    <div class="OngoingWrapper">
      <swiper class="OngoingSwip" :modules='modules' :navigation="true">
        <swiper-slide v-for="(item, index) in info" :key="index">
          <div class="OngoingSwipItem">
            <div class="title">
              <span> {{ item.goods_name }}</span>
            </div>
            <div class="img">
              <img :src="imgFlag(item.icon)">
            </div>
            <div class="tags">


              <van-row style="width: 100%;">
                <van-col style="text-align: center;
            padding: 0.25rem 0.25rem;
            border-radius: 0.375rem;
            background: #e0e0e0;zoom: 0.7;" span="4"> {{ item.days }} Days</van-col>
                <van-col span="3"> </van-col>
                <van-col style="text-align: center;
            padding: 0.25rem 0.25rem;
            border-radius: 0.375rem;
            background: #e0e0e0;zoom: 0.7;" span="8">Daily interest rate {{ item.rate }}%</van-col>
                <van-col span="2"> </van-col>
                <van-col style="text-align: center;
            padding: 0.25rem 0.25rem;
            border-radius: 0.375rem;
            background: #e0e0e0;zoom: 0.7;" span="7">Return rate {{ cutOutNum(((item.price * (item.rate / 100)
              * item.days) / item.price) * 100, 1)
            }}%</van-col>

              </van-row>
            </div>
            <div class="remainingcycle">
              <div class="top">
                <span>Remaining Cycle</span>
                <span>{{ item.days - item.total_days }} {{ t('天') }}</span>
              </div>
              <div class="bot">
                <van-progress :percentage="(item.total_days / item.days) * 100" color="#e06e38" :show-pivot="false" />
              </div>
            </div>
            <div class="detail">
              <div class="unitprice" style="width:22%">
                <span>Unit price</span>
                <span style="color:#bd312d">{{ cutOutNum(item.price) }} RS</span>
              </div>
              <div class="dailyincome" style="width:22%">
                <span>Daily Income</span>
                <span style="color:#bd312d">{{ cutOutNum(item.money * (item.rate / 100)) }} RS</span>
              </div>
              <div class="cumulativeincome" style="width:28%">
                <span>Cumulative Income</span>
                <span style="color:#bd312d">{{ cutOutNum(item.total_reward ) }} RS</span>
              </div>
              <div class="totalrevenue" style="width:25%">
                <span>Total Revenue</span>
                <span style="color:#bd312d">{{ cutOutNum(item.days * (item.money * (item.rate / 100)) ) }} RS</span>
              </div>
            </div>
            <div class="notice">
              <!-- <span>Click to view project details</span> -->
            </div>


            <template v-if="item.status == 1">
              <template v-if="item.receive == 1">
                <div class="getBtn" @click="onReceive(item)"> <span>Get</span> </div>
              </template>
              <template v-else>
                <div class="getBtn" style="background: #e0e0e0;" @click="onReceiveNo(item)"> <span>Get</span> </div>

              </template>
            </template>
            <template v-else>
              <div class="getBtn" style="background: #e0e0e0;"> <span>{{ t('结束') }}</span> </div>
            </template>


          </div>
        </swiper-slide>
      </swiper>



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
      <!-- <div :v-show="rg">
        <h3>Collection instructions</h3>
        <p>
          After purchasing the equipment,you canreceive the income of the day 24 hours laterand you can also accumulate
          daily incomeand receive income at any time
        </p>
      </div> -->

    </div>
    <MyTab></MyTab>
  </div>
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
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import Product from '../assets/img/project/product.png';
import rgimg from '../assets/rg.gif';
import rgimgb from '../assets/rgb.png';
import rgimgd from '../assets/rgd.png';
import MyTab from "./Tab.vue";
import http from "../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../global/common";
import { _alert, lang, cutOutNum } from "../global/common";
import { Toast } from "vant";
import rechargeVue from "../../../h2/src/views/finance/recharge.vue";
import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();
const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const info = ref<any>({})
const router = useRouter()
const modules = ref<any>([Navigation]);
const getProjectDetail = (item: any) => { }
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

  // const toast = Toast.loading({
  //   duration: 0,
  //   forbidClick: true,
  //   message: "loading..."
  // });
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
        load()
      }
    })
  })

}


const onReceiveNo = (item: any) => {
  _alert('Currently unavailable')
}


const load = () => {
  http({
    url: 'c=Product&a=order&status=1',
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
    info.value = res.data.list
  })
}

onMounted(() => {
  load()
})

</script>
<style>
.swiper-button-next:after,
.swiper-rtl .swiper-button-prev:after,
.swiper-button-prev:after,
.swiper-rtl .swiper-button-next:after {
  color: #bd312d !important;
}
</style>
<style lang="scss" scoped>
.dialog_confirm_btn {
  text-align: center;
  height: 55px;
  background: #369642;
}

.Ongoing {
  margin-top: 2.5rem;
  min-height: 480px;


  .OngoingWrapper {
    .OngoingSwip {
      overflow: unset;
      margin-top: 1.5625rem;
      height: 26.875rem;

      :deep(.van-swipe-item) {
        display: flex;
        align-items: center;
        justify-content: center;
      }



      .OngoingSwipItem {
        position: relative;
        width: 80%;
        margin: 0 auto;
        margin-top: 5%;

        height: 105%;
        border-radius: 0.875rem;
        box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 0.25rem 0px, rgba(14, 30, 37, 0.32) 0px 2px 1rem 0px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: #f5f6fe;
        padding: 0 0.625rem;
        box-sizing: border-box;
        padding-bottom: 1.25rem;

        .title {
          position: absolute;
          width: 70%;
          height: 1.5625rem;
          background: #bd312d;
          color: #fff;
          font-size: 0.875rem;
          display: flex;
          justify-content: center;
          align-items: center;
          border-radius: 0.3125rem;

          z-index: 20;
          left: 15%;
          top: -0.78125rem;
        }

        .img {
          margin-top: 1.5rem;
          width: 13.75rem;
          height: 13.75rem;

          img {
            width: 13.75rem;
            height: 13.75rem;
          }
        }

        .tags {
          display: flex;
          flex-wrap: wrap;
          height: auto;
          font-size: 0.75rem;
          color: #6c6b6a;
          position: relative;
          margin-top: 0.875rem;
          width: 100%;

          span {
            text-align: center;
            padding: 0.25rem 0.25rem;
            border-radius: 0.375rem;
            background: #e0e0e0;
            display: inline-block;
            zoom: 0.7;
            // margin-right: 0.625rem;
            -moz-transform: scale(0.5);
            -moz-transform-origin: top left;
            -o-transform: scale(0.5);
            -o-transform-origin: top left;
          }


        }



        .remainingcycle {
          width: 100%;
          margin-top: 1.25rem;

          .top {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #e06e38;
          }

          .bot {
            margin-top: 0.375rem;
          }
        }

        .detail {
          width: 100%;
          display: flex;
          font-size: 0.75rem;
          margin-top: 0.75rem;
          position: relative;

          &>div {
            // position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 2px solid #e0e0e0;
            padding-right: 5px;
            zoom: 0.7;
            padding-left: 5px;

            &:last-child {
              border: 0;
            }

            & span {
              display: inline-block;
              transform: scale(0.9);
            }

          }

        }

        .notice {
          margin-top: 2.5rem;
          font-size: 0.75rem;
          width: 100%;
          text-align: center;

          span {
            display: inline-block;
            transform: scale(0.8);
            color: #8c8a8a;
          }
        }

        .getBtn {
          margin-top: 0.25rem;
          height: 1.25rem;
          font-size: 0.75rem;
          width: 100%;
          text-align: center;
          line-height: 1.25rem;
          color: #fff;
          background: #bd312d;
          border-radius: 0.3125rem;
        }
      }
    }
  }
}

span {
  text-align: center;
}
</style>