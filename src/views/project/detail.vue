<template>
  <div class="project_detail">
    <Nav leftText=''>
      <template #title>
        {{ title }}
      </template>
    </Nav>
    <div class="project_img">
      <van-swipe indicator-color="#009900" :autoplay="3000">
        <van-swipe-item v-if="info.covers == []">
          <img :src="imgFlag(info.icon)" style="max-height: 200px" />
        </van-swipe-item>
        <van-swipe-item v-else v-for="item in info.covers">
          <img :src="imgFlag(item)" style="max-height: 200px" />
        </van-swipe-item>
      </van-swipe>
    </div>

    <div class="ProductDetails">
      <div class="title">
        <div style="color: #009900;">Investment Amount</div>
        <div style="color: #282b8e;">₹{{ info.price }}</div>
      </div>
      <div class="detail">
        <div class="dailyincome">
          <span class="bold">{{ info.days }} Day</span>
          <span>{{ t('投资周期') }} </span>
        </div>
        <div class="dailyincome">
          <span class="bold">₹{{ detailData?.dailyIncome }}</span>
          <span>{{ t('日收益') }} </span>
        </div>
        <div class="dailyincome">
          <span class="bold">₹{{ cutOutNum(detailData?.totalRevenue, 2) }}</span>
          <span style="white-space: nowrap;">{{ t('总收益') }} </span>
        </div>
        <div class="index_cer_title">
          <span class="bold"> {{ (info.rate - 0).toFixed(2) }}%</span>
          <span> {{ t('每日利润回报') }}</span>
        </div>
        <div class="totalrevenue">
          <span class="bold">{{ (info.price * info.rate * info.days / info.price).toFixed(2) }}%</span>
          <span>{{ t('利润回报') }} </span>
        </div>
        <div class="totalrevenue">
          <span v-if="info.invest_limit > 0" class="bold">{{ (info.invest_limit) }}</span>
          <span v-else class="bold">{{ t('无限制') }}</span>
          <span>{{ t('数量限制') }}</span>
        </div>
      </div>

      <div class="productDet_bot">
        <van-button v-if="info.status == 2" class="touziBtn" @click="onPresale">{{ t('预售') }}</van-button>
        <van-button v-else-if="info.status == 10" class="touziBtn" @click="onPresale1">Not for sale</van-button>
        <van-button v-else-if="info.status == 9" class="touziBtn">{{ t('售罄') }}</van-button>
        <van-button v-else class="touziBtn" @click="onInvest">{{ t('立即购买') }}</van-button>
      </div>

    </div>

    <div class="desc">
      <div class="desc_title">
        <span>Project description</span>
      </div>
      <div class="desc_notice">
        <div class="noticeList">
          <div class="tablecon" v-html="info.content"></div>
        </div>
      </div>
    </div>

    <van-popup v-model:show="investShow" position="bottom" closeable round class="goodsBuyPop"
      :style="{ height: 'auto', background: '#ebf9e8' }">
      <div class="invest" style="background: #ebf9e8">
        <div class="invest_wrap">
          <div class="title2">Order Details</div>
          <div class="cont">
            <div class="flex">
              <div class="imgbox">
                <img :src="imgFlag(info.icon)" />
              </div>
              <van-cell-group>
                <div style="color: #009900;font-weight: bold;padding:0.4rem 0;">
                  <p>{{ info.name }}</p>
                </div>
                <van-cell style="padding-bottom: 0;margin: 0;background: transparent;">
                  <span style="color: #282b8e;font-weight: bold;">₹{{ info.price }}</span>
                  <van-stepper v-model="quantity" disable-input :step="1" :min="1" :max="info.invest_limit"
                    button-size="20px" input-width="40px" @change="quantitychange" />
                </van-cell>
              </van-cell-group>
            </div>
            <div class="amount">
              <div style="display: flex;justify-content: space-between;">
                <span>{{ t('充值钱包') }}</span>
                <span style="color: #dedc00;font-weight: bold;">₹ {{ wallet1.balance }}</span>
              </div>
              <div style="display: flex;justify-content: space-between;margin-top: 0.5rem;">
                <span>{{ t('余额钱包') }}</span>
                <span style="color: #dedc00;font-weight: bold;">₹ {{ wallet2.balance }}</span>
              </div>
            </div>
            <div class="invest_limit">
              <span>{{ t('采购数量') }}</span>
              <span style="color: #009900;font-weight: bold;">{{ info.invest_limit }}</span>
            </div>
            <van-collapse v-if="coupons != null && info.cid != 2" v-model="activeNames" class="collapse">
              <van-collapse-item :title="t('券')" name="1">
                <div class="Discount">
                  <div v-for="(item, index) in coupons" :key="index" :style="styles[index]">
                    <label :for="forid(item.id)" style="display: flex;align-items: center;justify-content: flex-start;">
                      <input type="radio" :value="item.id" v-model="couponId" :id="forid(item.id)" name="isOpen" @click="changeColor(item, index, info)">
                      <span>Discount Coupon</span>
                      <span style="color: #009900;font-weight:bold;width: 50%;text-align: right;">{{ item.valueDesc }}{{
                        item.unitDesc }} </span>
                    </label>
                  </div>
                </div>
              </van-collapse-item>
            </van-collapse>
          </div>
          <div class="touziBtns">
            <div v-if="couponId === -1" class="Actual">Actual amount <span
                style="color: #282b8e;margin-left: 0.4rem;">₹{{
                money }}</span></div>
            <div v-else class="Actual">Discount amount<span style="color: #282b8e; margin-left: 0.4rem;">₹{{ money
                }}</span>
            </div>
            <van-button class="touziBtn" @click="onSubmit">buy</van-button>
          </div>
        </div>
      </div>
    </van-popup>

  </div>
  <MyLoading :show="loadingShow" title="Submit"></MyLoading>
</template>
<script lang="ts">
import { defineComponent } from "vue";
import {
  Swipe, SwipeItem, Button, Grid, GridItem, Image, Tab, Tabs, Cell,
  CellGroup, Stepper, Icon, Field, Popup, CouponCell, CouponList, Collapse, CollapseItem
} from "vant";
import { getSrcUrl, goRoute } from "../../global/common";
import Nav from '../../components/Nav.vue';
import MyLoading from "../../components/Loading.vue";

import MySwiper from '../../components/Swiper.vue'
const imgFlag = (src: string) => {
  return getSrcUrl(src, 1);
}
export default defineComponent({
  name: "productDet",
  components: {
    MySwiper, Nav, MyLoading,
    [Swipe.name]: Swipe,
    [SwipeItem.name]: SwipeItem,
    [Image.name]: Image,
    [Button.name]: Button,
    [Grid.name]: Grid,
    [GridItem.name]: GridItem,
    [Tab.name]: Tab,
    [Tabs.name]: Tabs,
    [Cell.name]: Cell,
    [Field.name]: Field,
    [Stepper.name]: Stepper,
    [CellGroup.name]: CellGroup,
    [Icon.name]: Icon,
    [Popup.name]: Popup,
    [CouponCell.name]: CouponCell,
    [CouponList.name]: CouponList,
    [Collapse.name]: Collapse,
    [CollapseItem.name]: CollapseItem,
  }
})
</script>
<script lang="ts" setup>
import { ref, onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import md5 from "md5";
import { _alert, lang, cutOutNum } from "../../global/common";
import http from "../../global/network/http";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

const route = useRoute()
const router = useRouter()
const detailData = ref<any>({})
const title = ref('')
const activeNames = ref(['0'])
const wallet1 = ref({})
const wallet2 = ref({})
const wallet3 = ref({})
const investShow = ref(false)
const quantity = ref(1)
const step = ref(1)
let isRequest = false
const money = ref(0)
const loadingShow = ref(false);
const coupon = {};
const coupons = ref([coupon]);
const couponId = ref(-1);
const styles = reactive<any>({});

const dataForm = reactive({
  password2: ''
})
const info = ref({
  invest_min: 0,
  covers: []
})

const onInvest = () => {
  investShow.value = true
}

const changeColor = (item: any, index: number, info: any) => {
  for (let key in styles) {
    styles[key] = {};
  }
  styles[index] = {
    border: 'none',
    color: '#000',
  };
  if (couponId.value == item.id) {
    // 取消选择
    styles[index] = {};
    couponId.value = -1
    money.value = info.price * quantity.value;

  } else {
    couponId.value = item.id
    money.value = (info.price * quantity.value) - (info.price * (item.valueDesc / 100) * quantity.value)
  }

};

const onPresale = () => {
  _alert('Unable to activate during pre-sale')
}
const onPresale1 = () => {
  _alert('This product is not for sale')
}

const quantitychange = () => {
  if (couponId.value == -1) {
    money.value = info.value.price * quantity.value;
  } else {
    for (let item of coupons.value) {
      if (item.id == couponId.value) {
        money.value = info.value.price * (100 - item.valueDesc) / 100 * quantity.value;
        break;
      }
    }
  }
}

const onSubmit = () => {
  if (isRequest) {
    return
  } else {
    isRequest = true
  }
  if (info.value.gift == 1) {
    isRequest = false
    _alert("Please invite members to join and contact customer service manager to redeem.");
    return
  }
  loadingShow.value = true;
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
    http({
      url: 'c=Product&a=invest',
      data: {
        gsn: info.value.gsn,
        money: money.value,
        coupon: couponId.value,
        quantity: quantity.value,
        password2: md5(dataForm.password2)
      }
    }).then((res: any) => {
      loadingShow.value = false;
      if (res.code != 1) {
        isRequest = false
        _alert(res.msg)
        return
      }
      dataForm.password2 = ''

      _alert(res.msg, function () {        
        isRequest = false
        investShow.value = false
        quantity.value = 1
      })
    })
  }, delayTime)
}

const init = () => {
  http({
    url: 'c=Product&a=goods',
    data: { gsn: route.params.pid }
  }).then((res: any) => {
    if (res.code != 1) {
      _alert({
        type: 'error',
        message: res.msg,
      })
      return
    }

    title.value = res.data.info.name;
    info.value = res.data.info
    money.value = res.data.info.price

    if (info.value.djs != 0 && info.value.djs != null && info.value.djs <= info.value.djss) {
      info.value.status = 10;
    }
    detailData.value.name = res.data.info.name
    detailData.value.price = res.data.info.price
    detailData.value.dailyIncome = (res.data.info.price * res.data.info.rate / 100).toFixed(2)
    detailData.value.totalRevenue = (res.data.info.price * res.data.info.rate * res.data.info.days / 100).toFixed(2)
    detailData.value.content = res.data.info.content
    detailData.value.tags = [
      res.data.info.days + ' Days',
      'Daily interest rate ' + res.data.info.rate + '%',
      'Return rate ' + cutOutNum(res.data.info.price * res.data.info.rate * res.data.info.days / res.data.info.price, 1) + '%',
    ]
    wallet1.value = res.data.wallet1
    wallet2.value = res.data.wallet2
    wallet3.value = res.data.wallet3

    coupons.value = [];

    for (let index = 0; index < res.data.coupon_arr.length; index++) {
      const element = res.data.coupon_arr[index];
      coupons.value.push({
        available: 1,
        condition: t('折扣券'),
        reason: '',
        value: (100 - element.discount) * 100,
        name: element.coupon_name,
        startAt: element.create_time,
        endAt: element.effective_time == 0 ? element.create_time + (60 * 60 * 24 * 3650) : element.effective_time,
        valueDesc: (100 - element.discount).toString(),
        unitDesc: '%',
        id: element.id
      });
    }

  })
}
const forid = (id: number) => {
  return "forid_" + id;
}

onMounted(() => {
  init()
})
</script>
<style lang="scss" scoped>
.project_detail {
  position: relative;
  background-color: #ebf9e8;

  .project_img {
    height: 12.75rem;
    position: relative;
    left: 50%;
    transform: translateX(-50%);
    background-color: white;

    :deep(.van-swipe__track) {
      display: flex;
    }

    img {
      width: 100%;
      height: auto;
      position: relative;
      left: 50%;
      transform: translateX(-50%);
    }
  }

  .ProductDetails {
    padding: 1rem;
    background-color: white;

    .title {
      display: flex;
      justify-content: space-between;
      font-weight: bold;
    }

    .detail {
      display: flex;
      font-size: 0.8rem;
      flex-wrap: wrap;
      background-color: #009900;
      margin-top: 0.5rem;
      border-radius: 10px;
      box-shadow: 0 0 15px 0 #9999;

      .bold {
        margin-bottom: .2rem;
        font-size: 14px;
        font-weight: bold;
        color: white;
      }

      &>div {
        display: flex;
        width: 33%;
        flex-direction: column;
        align-items: center;
        color: #dedc00;
        height: 3.5rem;
        justify-content: center;
      }
    }
  }

  .desc {
    margin-top: 0.8rem;
    padding: 1rem;
    display: flex;
    justify-content: center;
    flex-direction: column;
    background-color: white;

    .desc_title {
      text-align: center;
      font-weight: bold;
      color: #64523e;
      font-size: 1rem;
      display: flex;
      align-items: center;
      justify-content: space-evenly;
      color: #009900;

      img {
        width: 2rem;
      }
    }

    .desc_notice {
      margin-top: 0.8rem;

      .tablecon {
        font-size: 14px;
        color: #666;
        width: 100%;
        overflow-x: auto;

        p {
          word-wrap: break-word;
        }
      }

    }
  }

  :deep(.van-popup__close-icon--top-right) {
    color: #98cc00 !important;
  }

  .goodsBuyPop {
    .title2 {
      margin-top: 1rem;
      display: flex;
      justify-content: center;
      color: #009900;
      font-weight: bold;
      font-size: 1.2rem;
    }

    .Discount {
      display: flex;
      flex-direction: column;
      height: 6rem;
      overflow-y: auto;
      background-color: white;

      div:first-child {
        border-radius: 6px 6px 0 0;
      }

      div:last-child {
        border-radius: 0 0 6px 6px;
      }

      div {
        flex-direction: column;
        text-align: center;
        padding: 0.8rem;
        color: #919191;
      }

      input {
        accent-color: #009900;
        margin-right: 0.6rem;
        zoom: 1.2;
      }

      :deep(.van-collapse-item__content) {
        padding: 0 0 0.8rem 0;
        background: #f4f7ff;
      }
    }
  }
}
</style>