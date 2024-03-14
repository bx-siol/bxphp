<template>
  <div class="project_detail">
    <Nav leftText=''></Nav>
    <div style="background: ">
      <div class="project_img">
        <!--<img :src="describe" class="describe">-->
        <van-swipe indicator-color="white" :autoplay="3000">
          <van-swipe-item v-if="info.covers == []">
            <img :src="imgFlag(info.icon)" style="width: 100%; max-height: 200px" />
          </van-swipe-item>
          <van-swipe-item v-for="item in info.covers">
            <img :src="imgFlag(item)" style="width: 100%; max-height: 200px" />
          </van-swipe-item>
        </van-swipe>
      </div>
      <div style="padding: 1rem 1rem 0;">
        <div class="title">
          <div>{{ info.name }}</div>
          <div class="index_cer_title n_p_name">
            <span class="bold" v-if="info.cid == 1019">{{ cutOutNum(detailData?.price, 2) }} </span>
            <span class="bold" v-else>₹ {{ cutOutNum(detailData?.price, 2) }}</span>
            <!-- <span v-if="info.cid == 1019"> Points</span>
                <span v-else> {{ t('价格') }}</span> -->
          </div>
          <!-- <div class="title_right">Investment Cycle: {{ info.days }} Day</div> -->
        </div>
        <div class="detail">
          <div class="dailyincome">
            <span class="bold" v-if="info.cid == 1019">{{ cutOutNum(detailData?.dailyIncome / 24, 2) }} </span>
            <span class="bold" v-else>₹{{ cutOutNum(detailData?.dailyIncome / 24, 2) }}</span>
            <span v-if="info.cid == 1019"> Points</span>
            <span style="white-space: nowrap;color: #002544;" v-else>{{ t('小时收益') }}</span>
          </div>
          <div class="dailyincome">
            <span class="bold">₹{{ cutOutNum(detailData?.dailyIncome, 2) }}</span>
            <span style="white-space: nowrap;color: #002544;">{{ t('日收益') }} </span>
          </div>
          <div class="dailyincome">
            <span class="bold">₹{{ cutOutNum(detailData?.totalRevenue, 2) }}</span>
            <span style="white-space: nowrap;color: #002544;">{{ t('总收益') }} </span>
          </div>

          <div class="totalrevenue" v-if="false">
            <span class="bold">{{ cutOutNum(((info.price * (info.rate / 100) * info.days) / info.price) *
              100, 1) }}%</span>
            <span>{{ t('利润回报') }} </span>
          </div>
          <div class="dailyincome" v-if="false">
            <span class="bold">{{ info.days }} Day</span>
            <span>{{ t('投资周期') }} </span>
          </div>
          <div class="dailyincome" v-if="false">
            <span class="bold">VIP{{ 1 }} </span>
            <span>{{ t('购买等级') }} </span>
          </div>
        </div>
      </div>
      <div class="invest_wrap" v-if="info.status != 2 && info.status != 10">
        <div class="cont">
          <van-cell-group>
            <van-cell v-if="info.cid != 1019" :title="t('价格')"
              style="padding-bottom: 0;margin: 0;background: transparent;">
              <template #value v-if="couponId === -1">
                <span style="color: #fff;font-weight: bold;">₹{{ info.price }}</span>
              </template>
              <template #value v-else>
                <span style="color: #f00;font-weight: bold;">₹{{ info.prices }}</span>
              </template>
            </van-cell>
            <van-cell :title="t('数量限制')">
              <template #value>
                <span v-if="info.invest_limit > 0" class="gold">{{ info.invest_limit }}</span>
                <span v-else class="gold">{{ t('无限制') }}</span>
              </template>
            </van-cell>
            <van-collapse v-if="coupons != null && info.cid != 2" v-model="activeNames" class="collapse">
              <van-collapse-item :title="t('折扣券')" name="1">
                <div class="Discount">
                  <div v-for="(item, index) in coupons" :key="index" :style="styles[index]"
                    @click="changeColor(item, index, info)">
                    <p>{{ item.valueDesc }}{{ item.unitDesc }}</p>
                    <span>Discount Coupon</span>
                  </div>
                </div>
              </van-collapse-item>
            </van-collapse>
            <van-cell class="purchase_quantity" :title="t('采购数量')">
              <template #value>
                <van-stepper v-model="quantity" :step="1" :min="1" :max="info.invest_limit" button-size="20px"
                  input-width="48px" />
              </template>
            </van-cell>
          </van-cell-group>
        </div>
        <div class="amount">
          <van-grid v-if="info.cid != 1019" :border="false" :column-num="2">
            <van-grid-item>
              <span>₹{{ wallet2.balance }}</span>
              <p>{{ t('充值钱包') }}</p>
            </van-grid-item>
            <van-grid-item>
              <span>₹{{ wallet1.balance }}</span>
              <p>{{ t('余额钱包') }}</p>
            </van-grid-item>
          </van-grid>
          <van-grid v-if="info.cid == 1019" :border="false" :column-num="1">
            <van-grid-item>
              <span>{{ wallet3.balance }} </span>
              <p>You Points</p>
            </van-grid-item>
          </van-grid>
        </div>
      </div>
    </div>

    <div class="desc">
      <div class="desc_title">
        <img :src="leaf">
        <span>Project description</span>
      </div>
      <div class="desc_notice">
        <div class="noticeList">
          <div class="tablecon" v-html="info.content" style="padding-bottom: 1rem"></div>
        </div>
      </div>
    </div>

    <div class="touziBtns">
      <div v-if="info.cid != 1019">
        <div v-if="couponId === -1" class="Actual">Actual amount <span style="color: #f00;">₹{{
          info.price }}</span></div>
        <div v-else class="Actual">Discount amount<span style="color: #f00; margin-left: 0.4rem;">₹{{
          info.prices }}</span></div>
      </div>
      <div v-if="info.cid == 1019">
        <div class="Actual" style="margin-top: 0.4rem;">
          Wealth Value<span style="color: #f00;margin-left: 0.4rem;">{{ info.price }}</span>
        </div>
      </div>

      <van-button v-if="info.status == 2" class="touziBtn" @click="onPresale">{{ t('预售') }}</van-button>

      <van-button v-else-if="info.status == 10" class="touziBtn" @click="onPresale1">Not for sale</van-button>

      <van-button v-else class="touziBtn" @click="onSubmit">{{ t('立即加入') }}</van-button>
    </div>
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
import MySwiper from '../../components/Swiper.vue'
import MyLoading from "../../components/Loading.vue";

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
// import Product from '../../assets/img/project/product.png';
import bird from '../../assets/ico/bird.png'
import leaf from '../../assets/ico/leaf.png'
import lb1 from '../../assets/img/project/lb1.jpg'
import MyTab from "../../components/Tab.vue";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

const route = useRoute()
const router = useRouter()
const pid = ref(route.params.pid)
const detailData = ref<any>({})
const dataForm = reactive({
  password2: ''
})
const info = ref({
  invest_min: 0,
  covers: []
})

const styles = reactive<any>({});
const changeColor = (item: any, index: number, info: any) => {
  for (let key in styles) {
    styles[key] = {};
  }
  styles[index] = {
    border: '1px solid #84973b',
    background: '#fff',
    color: '#84973b',
  };
  if (couponId.value == item.id) {
    // 取消选择
    styles[index] = {
      border: '1px solid #ccc',

      color: '#000',
    };

    couponId.value = -1
    info.prices = info.price;

  } else
    couponId.value = item.id
  info.prices = info.price - (info.price * (item.valueDesc / 100))
};

const activeNames = ref(['0'])
const wallet1 = ref({})
const wallet2 = ref({})
const wallet3 = ref({})
const investShow = ref(false)
const quantity = ref(1)
const step = ref(1)
let isRequest = false
const money = ref(info.value.invest_min)
const loadingShow = ref(false);

const disabledCouponsc = {
  id: -2,
  available: 11,
  condition: t('折扣券'),
  reason: '',
  value: 0,
  name: t('折扣券'),
  startAt: 1489104000,
  endAt: 1514592000,
  valueDesc: '0',
  unitDesc: '%',
};
const coupon = {
  id: -1,
  available: 1,
  condition: t('折扣券'),
  reason: '',
  value: 0,
  name: t('折扣券'),
  startAt: 1489104000,
  endAt: 1514592000,
  valueDesc: '0',
  unitDesc: '%',
};
const disabledCoupons = ref([disabledCouponsc]);
const coupons = ref([coupon]);
const showList = ref(false);
const chosenCoupon = ref(0);
const couponId = ref(-1);

const onChange = (index) => {
  showList.value = false;
  chosenCoupon.value = index;
  couponId.value = coupons.value[chosenCoupon.value].id;


};
const onExchange = (code) => {
  coupons.value.push(coupon);
};



const getProjectDetail = () => {
  // 根据id查询详细信息
  // doSearch(pid.value)
  // 查询结赋值
  // detailData.value = res
  // 模拟数据返回
  detailData.value = {
    id: 1,
    img: '',
    name: 'CS3Y-MB-AG',
    tags: ['45 Days', 'Daily interest rate 4.5%', 'Return rate 200%'],
    remainingCycle: '19 Day',
    price: '450',
    totalRevenue: '1,000',
    dailyIncome: '15',
    cumulativeIncome: '150',
    content: ''
  }
}

const onPresale = () => {
  _alert('Unable to activate during pre-sale')
}
const onPresale1 = () => {
  _alert('This product is not for sale')
}

// Not for sale
const onInvest = () => {
  // step.value=2
  investShow.value = true
}

const onSubmit = () => {
  if (isRequest) {
    return
  } else {
    isRequest = true
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
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
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
      console.log(info.value.djs);
      console.log(info.value.djss);

      if (info.value.djs != 0 && info.value.djs != null && info.value.djs <= info.value.djss) {
        info.value.status = 10;
      }
      console.log(info.value.djs <= info.value.djss);
      // console.log(info.value.djss);
      detailData.value.name = res.data.info.name
      detailData.value.price = res.data.info.price
      detailData.value.dailyIncome = res.data.info.price * (res.data.info.rate / 100)
      detailData.value.totalRevenue = res.data.info.price * (res.data.info.rate / 100) * res.data.info.days
      detailData.value.content = res.data.info.content
      detailData.value.tags = [
        res.data.info.days + ' Days',
        'Daily interest rate ' + res.data.info.rate + '%',
        'Return rate ' + cutOutNum(((res.data.info.price * (res.data.info.rate / 100) * res.data.info.days) / res.data.info.price) * 100, 1) + '%',
        //'Limit ' + res.data.info.invest_limit + ' copy'
      ]
      wallet1.value = res.data.wallet1
      wallet2.value = res.data.wallet2
      wallet3.value = res.data.wallet3

      coupons.value = [];

      coupons.value.push({
        id: -1,
        available: 1,
        condition: t('折扣券'),
        reason: '',
        value: 0,
        name: t('折扣券'),
        startAt: 1489104000,
        endAt: 1514592000,
        valueDesc: '0',
        unitDesc: '%',
      });

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
  }, delayTime)
}
const forid = (id: number) => {
  return "forid_" + id;
}

onMounted(() => {
  init()
  getProjectDetail()
})
</script>
<style lang="scss" scoped>
.project_detail {
  position: relative;
  height: 100%;
  background-color: #84973b;

  .productDet_bot {
    position: relative;
  }

  :deep .van-collapse-item__content {
    padding: 0 0 0.8rem 0;
    background: #84973b;
  }

  .Discount {
    display: flex;
    flex-direction: row;
    overflow-x: auto;
    width: 22rem;


    div {
      flex-direction: column;
      text-align: center;
      padding: 0.4rem 1rem;
      margin: 0 1rem;
      color: #000;
      border: 1px solid #ccc;
      border-radius: 6px;

      span {
        white-space: nowrap;
      }
    }

    input {
      accent-color: #ff3737;
      margin-right: 0.6rem;
      zoom: 1.2;
    }
  }

  .project_img {
    width: 15.75rem;
    height: 12.75rem;
    position: relative;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.4rem 2rem;
    background: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    .describe {
      width: 3rem;
      height: 3rem;
      position: absolute;
      top: 0;
      right: 0;
      z-index: 1;
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
    justify-content: space-between;
    color: #fff;
    font-weight: bold;
  }

  .title2 {
    margin-top: 0.375rem;
    display: flex;
    justify-content: center;
    color: #64523e;
    font-weight: bold;
  }

  .n_p_name {
    text-align: left;
    color: #fff;
  }

  .detail {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-around;
    font-size: 0.8rem;
    position: relative;
    padding: 0.5rem 0;
    color: #4d4d4d;

    .bold {
      margin-bottom: .2rem;
      color: #ccc;
      font-size: 14px;
      font-weight: bold;
    }

    &>div {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 0.8rem;

      & span:nth-child(1) {
        // display: inline-block;
        // transform: scale(0.9);
        color: #fff;
      }
    }

    .bord {
      border: 1px solid #999;
      height: 2rem;
    }
  }

  .desc {
    margin-top: 1rem;
    padding: 0 1rem 3rem;
    display: flex;
    justify-content: center;
    flex-direction: column;
    background: #84973b;

    .desc_title {
      text-align: center;
      font-weight: bold;
      color: #fff;
      font-size: 1rem;
      display: flex;
      align-items: center;
      justify-content: flex-start;

      img {
        width: 1rem;
        margin-right: 0.525rem;
      }
    }


    .desc_notice {
      margin-top: 1.25rem;

      .noticeList {
        .tablecon {
          font-size: 14px;
          color: #fff;
        }
      }
    }
  }

  .touziBtns {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #84973b;
    font-size: 14px;
    width: 100%;
    position: fixed;
    bottom: 0.5rem;
    left: 50%;
    transform: translateX(-50%);
  }

  .touziBtns .Actual {
    color: #002544;
    margin-left: 1rem;
  }

  .touziBtns span {

    font-weight: bold;
  }

  .touziBtn {
    display: block;
    background: #fff;
    border: 0;
    color: #84973b;
    width: 40%;
    padding: 0;
    height: 2.4rem;
    font-size: 0.9rem;
    border-radius: 2.4rem;
    margin-right: 1rem;
  }

}
</style>