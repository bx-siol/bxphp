<template>
  <div class="project_detail">
    <Nav leftText=''></Nav>
    <div class="project_img">
      <van-swipe indicator-color="#cb1a00" :autoplay="3000">
        <van-swipe-item v-if="info.covers == []">
          <img :src="imgFlag(info.icon)" style="max-height: 200px" />
        </van-swipe-item>
        <van-swipe-item v-else v-for="item in info.covers">
          <img :src="imgFlag(item)" style="max-height: 200px" />
        </van-swipe-item>
      </van-swipe>
    </div>
    <div style="padding: 0 1rem 1rem 1rem;">
      <div class="title">
        <div>{{ info.name }}</div>
      </div>
      <div class="detail">
        <div class="index_cer_title n_p_name">
          <span class="bold" style="color: #cb1a00;">₹ {{ cutOutNum(detailData?.price, 2) }}</span>
          <span> {{ t('价格') }}</span>
        </div>
        <div class="totalrevenue" v-if="info.is_xskc == 1 ? true : false">
          <span class="bold">{{ info.kc }}</span>
          <span>{{ t('剩余数量') }}</span>
        </div>
        <div class="totalrevenue">
          <span v-if="info.invest_limit > 0" class="bold">{{ info.invest_limit }}</span>
          <span v-else class="bold">{{ t('无限制') }}</span>
          <span>{{ t('限制数量') }}</span>
        </div>
        <div style="border-top: 1px solid #f5f1f1;margin: 0.7rem 0 0rem 0;"></div>
        <div class="dailyincome" v-if="false">
          <span class="bold">{{ info.days }} Day</span>
          <span>{{ t('投资周期') }} </span>
        </div>
        <div class="dailyincome" v-if="false">
          <span class="bold">₹{{ detailData?.dailyIncome }}</span>
          <span>{{ t('日收益') }} </span>
        </div>
        <div class="dailyincome" v-if="false">
          <span class="bold">₹{{ detailData?.totalRevenue }}</span>
          <span style="white-space: nowrap;">{{ t('总收益') }} </span>
        </div>
        <div class="totalrevenue" v-if="false">
          <span class="bold">{{ (info.price * info.rate * info.days / info.price).toFixed(2) }}%</span>
          <span>{{ t('利润回报') }} </span>
        </div>

        <div class="Coupons">
          <van-collapse v-if="coupons != null && info.cid != 2" v-model="activeNames" class="collapse">
            <van-collapse-item :title="t('折扣券')" name="1">
              <div class="Discount">
                <div v-for="(item, index) in coupons" :key="index" :style="styles[index]">
                  <label :for="forid(item.id)" style="display: flex;align-items: center;justify-content: flex-start;">
                    <input type="radio" :value="item.id" v-model="couponId" :id="forid(item.id)" name="isOpen"
                      @click="changeColor(item, index, info)">
                    <span>Discount Coupon</span>
                    <span style="color: #f00;font-weight:bold;width: 50%;text-align: right;">{{ item.valueDesc }}{{
                      item.unitDesc }} </span>
                  </label>
                </div>
              </div>
            </van-collapse-item>
          </van-collapse>
        </div>

        <div class="totalrevenue">
          <span class="bold">
            <van-stepper v-model="quantity" :step="1" :min="1" :max="info.invest_limit" button-size="30px"
              input-width="40px" @change="quantitychange" />
          </span>
          <span>{{ t('采购数量') }} </span>
        </div>
        <div class="Balance">
          <div>
            <span>₹{{ wallet1.balance }}</span>
            <span>{{ t('充值钱包') }}</span>
          </div>
          <div>
            <span>₹{{ wallet2.balance }}</span>
            <span>{{ t('钱包余额') }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="touziBtns">
      <div v-if="couponId === -1" class="Actual">Actual amount <span style="color: #f00;">₹{{ money }}</span></div>
      <div v-else class="Actual">Discount amount<span style="color: #f00; margin-left: 0.4rem;">₹{{ money }}</span>
      </div>

      <van-button v-if="info.status == 2" class="touziBtn" @click="onPresale">{{ t('预售') }}</van-button>
      <van-button v-else-if="info.status == 10" class="touziBtn" @click="onPresale1">Not for sale</van-button>
      <van-button v-else-if="info.status == 9" class="touziBtn">{{ t('售罄') }}</van-button>
      <van-button v-else class="touziBtn" @click="onSubmit">{{ t('立即购买') }}</van-button>
    </div>

  </div>
  <MyLoading :show="loadingShow" title="Submit"></MyLoading>
</template>
<script lang="ts">
import { defineComponent } from "vue";
import { Swipe, SwipeItem, Button, Grid, GridItem, Image, Tab, Tabs, Cell, CellGroup, Stepper, Icon, Field, Popup, CouponCell, CouponList, Collapse, CollapseItem } from "vant";
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
import bird from '../../assets/ico/bird.png'
import { useI18n } from 'vue-i18n';
import sold_out from '../../assets/img/project/sold_out.png';

const { t } = useI18n();
const route = useRoute()
const router = useRouter()
const pid = ref(route.params.pid)
const detailData = ref<any>({})
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
      _alert({
        type: 'success',
        message: res.msg,
        onClose: () => {
          init()
          isRequest = false
          step.value = 1
          investShow.value = false
        }
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
  background: #fff;

  .productDet_bot {
    position: fixed;
  }

  :deep .van-collapse-item__content {
    padding: 0 0 0.8rem 0;
    background: white;
  }

  .Coupons {
    display: block !important;

    :deep(.van-hairline--top-bottom) {
      position: static;
    }

    :deep(.van-cell) {
      padding: 0.3rem 0;
      border: none;
    }

    .Discount {
      display: flex;
      flex-direction: column;
      height: 6rem;
      overflow-y: auto;

      div:first-child {
        border-radius: 6px 6px 0 0;

      }

      div:last-child {
        border-radius: 0 0 6px 6px;
      }

      div {
        flex-direction: column;
        text-align: center;
        padding: 0.3rem;
        color: #000;
        background: white;
      }

      input {
        accent-color: #ff3737;
        margin-right: 0.6rem;
        zoom: 1.2;
      }
    }
  }

  .project_img {
    width: 12.75rem;
    height: 12.75rem;
    position: relative;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.4rem 1rem;
    display: flex;
    align-items: center;

    :deep(.van-swipe__track) {
      display: flex;
    }

    img {
      max-width: 100%;
      max-height: 100%;
      width: auto;
      height: auto;
      position: relative;
      left: 50%;
      transform: translateX(-50%);
    }
  }

  .title {
    margin-top: 0.375rem;
    display: flex;
    justify-content: space-around;
    font-weight: bold;
    color: #cb1a00;
    font-size: 1.2rem;
    font-weight: bold;
  }

  .title2 {
    margin-top: 0.375rem;
    display: flex;
    justify-content: center;
    color: black;
    font-weight: bold;
  }

  .n_p_name {
    text-align: left;
    color: black;
  }

  .detail {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-around;
    flex-direction: column;
    font-size: 0.8rem;
    position: relative;
    padding: 0.5rem 0;
    color: black;

    .bold {
      margin-bottom: .2rem;
      color: black;
      font-size: 14px;
      font-weight: bold;
    }

    &>div {
      display: flex;
      flex-direction: row-reverse;
      align-items: center;
      justify-content: space-between;
      padding: 0.2rem 0 0 0;
      width: 100%;

      & span:nth-child(1) {
        color: black;
      }
    }

    .bord {
      border: 1px solid black;
      height: 2rem;
    }

    .Balance {
      div {
        width: 45%;
        height: 5rem;
        background-color: #cc1700;
        border-radius: 5px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;

        span {
          color: white
        }

        span:first-child {
          font-size: 1rem;
          font-weight: bold;
        }

        span:last-child {
          font-size: 0.7rem;
        }
      }
    }
  }

  .touziBtns {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    width: 100%;
    position: fixed;
    bottom: 0.5rem;
    left: 50%;
    transform: translateX(-50%);
    max-width: 640px;
  }

  .touziBtns .Actual {
    color: #002544;
    margin-left: 1rem;
  }

  .touziBtns .Actuals {
    display: flex;
    align-items: center;
    margin-left: 1rem;

    img {
      width: 2rem;
    }
  }

  .touziBtns span {

    font-weight: bold;
  }

  .touziBtn {
    display: block;
    background: #cc1700;
    border: 0;
    color: white;
    width: 40%;
    padding: 0;
    height: 2.4rem;
    font-size: 0.9rem;
    border-radius: 2.4rem;
    margin-right: 1rem;
    font-weight: bold;
  }

}
</style>