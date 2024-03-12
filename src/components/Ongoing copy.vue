<template>
  <div class="Ongoing">
    <div class="OngoingWrapper">
      <van-swipe @change="onChange" :loop="false" :show-indicators="false" class="OngoingSwip">
        <van-swipe-item v-for="(item, index) in info.list" :key="index">
          <div class="OngoingSwipItem">
            <div class="title">
              <span> {{ item.goods_name }}</span>
            </div>
            <div class="img">
              <img :src="imgFlag(item.icon)">
            </div>
            <div class="tags">
              <span>
                {{ item.days }} Days
              </span>
              <span>
                Daily interest rate {{ item.rate }}%
              </span>
              <span>
                Return rate {{ ((item.price * (item.rate / 100) * item.days) / item.price) * 100 }}%
              </span>

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
                <span style="color:#bd312d">{{ item.price }} RS</span>
              </div>
              <div class="dailyincome" style="width:22%">
                <span>Daily Income</span>
                <span style="color:#bd312d">{{ (item.money * (item.rate / 100)) }} RS</span>
              </div>
              <div class="cumulativeincome" style="width:28%">
                <span>Cumulative Income</span>
                <span style="color:#bd312d">{{ item.total_reward }} RS</span>
              </div>
              <div class="totalrevenue" style="width:25%">
                <span>Total Revenue</span>
                <span style="color:#bd312d">{{ item.days * (item.money * (item.rate / 100)) }} RS</span>
              </div>
            </div>
            <div class="notice">
              <span>Click to view project details</span>
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
        </van-swipe-item>
      </van-swipe>
    </div>
    <MyTab></MyTab>
  </div>
</template>
<script lang="ts">
import { defineComponent, reactive } from 'vue'
import { Swipe, SwipeItem, Progress } from 'vant'
export default defineComponent({
  components: {
    [Swipe.name]: Swipe,
    [SwipeItem.name]: SwipeItem,
    [Progress.name]: Progress
  }
})
</script>
 
<script lang="ts" setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import Product from '../assets/img/project/product.png';
import MyTab from "./Tab.vue";
import http from "../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../global/common";
import { _alert, lang } from "../global/common";
import { Toast } from "vant";
import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();
const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const info = ref<any>({
  count: 0,
  limit: 15,
  loading: false,
  finished: false,
  list: [],
  page: 1,
})
const router = useRouter()

const getProjectDetail = (item: any) => {

}

const onChange = (index: number) => {
  console.log(index);
  if (!info.finished.value) {
    init()
  }
}

let isRequest = false
const onReceive = (item: any) => {
  if (isRequest) {
    return
  } else {
    isRequest = true
  }
  const toast = Toast.loading({
    duration: 0,
    forbidClick: true,
    message: "loading..."
  });

  http({
    url: 'c=Product&a=receiveProfit',
    data: { osn: item.osn }
  }).then((res: any) => {
    isRequest = false
    if (res.code != 1) {
      setTimeout(() => {
        toast.clear()
        _alert(res.msg)
      }, 1500);
      return
    }
    _alert({
      type: 'success',
      message: res.msg,
      onClose: () => {
        setTimeout(() => {
          item.receive = 0
          toast.clear()
          location.reload()
        }, 1500);
      }
    })
  })
}

const onReceiveNo = (item: any) => {
  _alert('Currently unavailable')
}

const init = () => {
  if (isRequest) {
    return
  }
  isRequest = true
  http({
    url: 'c=Product&a=order&status=1&page=' + info.page.value,
  }).then((res: any) => {
    isRequest = false
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
    for (let j in res.data.list) {
      info.list.value.push(res.data.list[j])
    }
    info.count.value = res.data.count
    info.page.value = res.data.page
    info.page.finished.value = res.data.finished
  })
}

onMounted(() => {
  init()
})




</script>


<style lang="scss" scoped>
.Ongoing {
  margin-top: 2.5rem;

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
        width: 19.375rem;
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
          width: 12.625rem;
          height: 1.5625rem;
          background: #bd312d;
          color: #fff;
          font-size: 0.875rem;
          display: flex;
          justify-content: center;
          align-items: center;
          border-radius: 0.3125rem;
          top: -0.78125rem;
          z-index: 20;
          left: 3.375rem;
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
            margin-right: 0.625rem;
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