<template>
    <div class="project_detail">
        <Nav leftText=""></Nav>
        <div class="project_img">
            <van-swipe indicator-color="white">
                <van-swipe-item v-if="info.covers == []">
                    <img :src="imgFlag(info.icon)" style="width: 100%; max-height: 300px" />
                </van-swipe-item>
                <van-swipe-item v-for="item in info.covers">
                    <img :src="imgFlag(item)" style="width: 100%; max-height: 300px" />
                </van-swipe-item>
            </van-swipe>
        </div>
        <div class="title">
            <div class="index_cer_title n_p_name">{{ detailData?.name }}</div>
            <div class="title_right">purchase limit: 3</div>
        </div>

        <div class="detail">
            <div style="width: 31%" class="unitprice">
                <span class="bold" v-if="info.cid == 1019" style="color: #1e1e2a">{{ cutOutNum(detailData?.price, 2) }} </span>
                <span class="bold" v-else style="color: #1e1e2a">{{ cutOutNum(detailData?.price, 2) }} RS</span>
                <span v-if="info.cid == 1019"> Points</span>
                <span v-else>{{ t('价格') }}</span>
            </div>
            <div style="width: 30%" class="dailyincome">
                <span class="bold" style="color: #1e1e2a">{{ cutOutNum(detailData?.dailyIncome, 2) }} RS</span>
                <span>{{ t('日收益') }} </span>
            </div>
            <div style="width: 30%" class="totalrevenue">
                <span class="bold" style="color: #1e1e2a">{{ cutOutNum(detailData?.totalRevenue, 2) }} RS</span>
                <span>{{ t('总收益') }} </span>
            </div>
            <div style="width: 31%" class="unitprice">
                <span class="bold" v-if="info.cid == 1019" style="color: #1e1e2a">{{ cutOutNum(detailData?.price, 2) }} </span>
                <span class="bold" v-else style="color: #1e1e2a">{{ cutOutNum(detailData?.price, 2) }} RS</span>

                <span v-if="info.cid == 1019"> Points</span>
                <span v-else>{{ t('价格') }}</span>
            </div>
            <div style="width: 30%" class="dailyincome">
                <span class="bold" style="color: #1e1e2a">{{ cutOutNum(detailData?.dailyIncome, 2) }} RS</span>
                <span>{{ t('日收益') }} </span>
            </div>
            <div style="width: 30%" class="totalrevenue">
                <span class="bold" style="color: #1e1e2a">{{ cutOutNum(detailData?.totalRevenue, 2) }} RS</span>
                <span>{{ t('总收益') }} </span>
            </div>
        </div>
        <div class="desc">
            <div class="desc_title">
                <span>Project description</span>
            </div>
            <!-- <div style="margin-top:1.25rem;font-size: 0.75rem;">
                      Balance wallet can buy products. when purchasing a product, you can use [recharge wallet] and [balance wallet]
                      at the same time, deduct [balance wallet] first.
                    </div> -->
            <div class="desc_notice">
                <div class="noticeList">
                    <div class="tablecon" v-html="info.content" style="padding-bottom: 1rem"></div>
                </div>
            </div>
        </div>
        <div class="productDet_bot" style="height: 4rem">
            <van-button v-if="info.status == 2" class="touziBtn" @click="onPresale" style="background: #1e1e2a; height: 3rem">
                {{ t('预售') }}
            </van-button>

            <van-button v-else-if="info.status == 10" class="touziBtn" @click="onPresale1"
                        style="background: #1e1e2a; height: 3rem">
                Not for sale
            </van-button>

            <van-button v-else class="touziBtn" @click="onInvest" style="background: #1e1e2a; height: 3rem">
                {{ t('立即购买') }}
            </van-button>
        </div>
        <van-popup v-model:show="investShow" close-icon="close" position="bottom" closeable round class="goodsBuyPop"
                   :style="{ height: 'auto', background: '#ffffff' }">
            <div class="invest" style="background: #fff">
                <div class="invest_wrap" style="padding-top: 3rem">
                    <div class="amount">
                        <van-grid v-if="info.cid != 200019" :border="false" :column-num="2">
                            <van-grid-item>
                                <p>{{ t('充值钱包') }}</p>
                                <span>{{ wallet2.balance }} RS</span>
                            </van-grid-item>
                            <van-grid-item>
                                <p>{{ t('余额钱包') }}</p>
                                <span>{{ wallet1.balance }} RS</span>
                            </van-grid-item>
                        </van-grid>
                        <van-grid v-if="info.cid == 1019" :border="false" :column-num="1">
                            <van-grid-item>
                                <p>You Points</p>
                                <span>{{ wallet3.balance }} </span>
                            </van-grid-item>
                        </van-grid>
                    </div>
                    <div class="cont">
                        <van-cell-group>
                            <van-cell v-if="info.cid != 200019" :title="t('价格')">
                                <template #value>
                                    <span style="color: #1e1e2a">{{ info.price }}RS</span>
                                </template>
                            </van-cell>

                            <van-cell v-if="info.cid == 1019" title="Points">
                                <template #value>
                                    <span style="color: #1e1e2a">{{ info.price }} </span>
                                </template>
                            </van-cell>
                            <van-cell :title="t('数量限制')">
                                <template #value>
                                    <span v-if="info.invest_limit > 0" class="gold">{{ info.invest_limit }}</span>
                                    <span v-else class="gold">{{ t('无限制') }}</span>
                                </template>
                            </van-cell>
                            <van-cell :title="t('利息时间')" value="Settlement at 24:00 every day"></van-cell>

                            <van-coupon-cell :title="t('折扣券')" currency="%" :coupons="coupons" :chosen-coupon="chosenCoupon"
                                             @click="showList = true" />
                            <van-popup v-if="info.cid != 200019" v-model:show="showList" round position="bottom"
                                       style="height: 90%; padding-top: 4px">
                                <van-coupon-list :empty-image="' '" :show-close-button="false" :enabled-title="t('可用')"
                                                 :show-exchange-bar="false" :disabled-title="t('不可用')" :coupons="coupons" :chosen-coupon="chosenCoupon"
                                                 :disabled-coupons="disabledCoupons" @change="onChange" @exchange="onExchange" />
                            </van-popup>

                            <van-cell :title="t('采购数量')" class="purchase_quantity">
                                <template #value>
                                    <van-stepper v-model="quantity" :step="1" :min="1" :max="info.invest_limit" button-size="20px"
                                                 input-width="40px" />
                                </template>
                            </van-cell>
                            <van-cell title="" v-if="false">
                                <template #value>
                                    Minimum start-up ${{ info.invest_min }} , step ${{ info.invest_min }}
                                </template>
                            </van-cell>
                            <van-field v-show="false" label-width="4rem" input-align="right" label="Password"
                                       v-model="dataForm.password2" type="password" placeholder="Enter payment password " />
                        </van-cell-group>
                    </div>
                    <van-button class="touziBtn" @click="onSubmit" style="height: 3rem; background: #1e1e2a">
                        Confirm buy
                    </van-button>
                </div>
            </div>
        </van-popup>
    </div>
    <MyTab></MyTab>
    <MyLoading :show="loadingShow" title="Submit"></MyLoading>
</template>
<script lang="ts">
    import { defineComponent } from 'vue'
    import {
        Swipe,
        SwipeItem,
        Button,
        Grid,
        GridItem,
        Image,
        Tab,
        Tabs,
        Cell,
        CellGroup,
        Stepper,
        Icon,
        Field,
        Popup,
        CouponCell,
        CouponList,
    } from 'vant'
    import { getSrcUrl, goRoute } from '../../global/common'
    import Nav from '../../components/Nav.vue'
    import MyLoading from "../../components/Loading.vue";
    import MySwiper from '../../components/Swiper.vue'
    const imgFlag = (src: string) => {
        return getSrcUrl(src, 1);
    }
    export default defineComponent({
        name: 'productDet',
        components: {
            MySwiper,
            Nav,
            MyLoading,
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
        },
    })
</script>
<script lang="ts" setup>
    import { ref, onMounted, reactive } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import md5 from 'md5'
    import { _alert, lang, cutOutNum } from '../../global/common'
    import http from '../../global/network/http'
    import Product from '../../assets/img/project/product.png'
    import MyTab from '../../components/Tab.vue'
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();
    const route = useRoute()
    const router = useRouter()
    const pid = ref(route.params.pid)
    const detailData = ref<any>({})
    const dataForm = reactive({
        password2: '',
    })
    const info = ref({
        invest_min: 0,
        covers: [],
    })
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
    }
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
    }
    const disabledCoupons = ref([disabledCouponsc])
    const coupons = ref([coupon])
    const showList = ref(false)
    const chosenCoupon = ref(0)
    const couponId = ref(-1)
    const onChange = index => {
        showList.value = false
        chosenCoupon.value = index
        couponId.value = coupons.value[chosenCoupon.value].id
    }
    const onExchange = code => {
        coupons.value.push(coupon)
    }

    const getProjectDetail = () => {
        // 根据id查询详细信息
        // doSearch(pid.value)
        // 查询结赋值
        // detailData.value = res
        // 模拟数据返回
        detailData.value = {
            id: 1,
            img: Product,
            name: 'CS3Y-MB-AG',
            tags: ['45 Days', 'Daily interest rate 4.5%', 'Return rate 200%'],
            remainingCycle: '19 Day',
            price: '450',
            totalRevenue: '1,000',
            dailyIncome: '15',
            cumulativeIncome: '150',
            content: '',
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
                url: 'GoodsInvest',
                data: {
                    gsn: info.value.gsn,
                    money: money.value,
                    coupon: couponId.value,
                    quantity: quantity.value,
                    password2: md5(dataForm.password2),
                },
            }).then((res: any) => {
                loadingShow.value = false;
                if (res.code != 200) {
                    isRequest = false
                    _alert(res.message)
                    return
                }
                dataForm.password2 = ''
                _alert(res.message, function () {
                    init()
                    isRequest = false
                    step.value = 1
                    investShow.value = false
                })
            })
        }, delayTime)
    }

    const init = () => {
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => {
            http({
                url: 'goods/H5Goods',
                data: { gsn: route.params.pid },
            }).then((res: any) => {
                if (res.code != 200) {
                    _alert(res.message)
                    return
                }

                info.value = res.data.info

                if (info.value.djs != 0 && info.value.djs != null && info.value.djs <= info.value.djss) {
                    info.value.status = 10
                }
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

                coupons.value = []

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
                })

                for (let index = 0; index < res.data.coupon_arr.length; index++) {
                    const element = res.data.coupon_arr[index]
                    coupons.value.push({
                        available: 1,
                        condition: t('折扣券'),
                        reason: '',
                        value: (100 - element.discount) * 100,
                        name: element.coupon_name,
                        startAt: element.create_time,
                        endAt: element.effective_time == 0 ? element.create_time + 60 * 60 * 24 * 3650 : element.effective_time,
                        valueDesc: (100 - element.discount).toString(),
                        unitDesc: '%',
                        id: element.id,
                    })
                }
            })
        }, delayTime)
    }

    onMounted(() => {
        init()
        getProjectDetail()
    })
</script>
<style lang="scss" scoped>
.project_detail {
  .productDet_bot {
    margin-top: 1.25rem;
    position: relative;
  }

  position: relative;
  // display: flex;
  // flex-direction: column;
  align-items: center;
  padding: 0 0.625rem;
  padding-bottom: 4rem;

  .project_img {
    width: 18.75rem;
    height: 18.75rem;
    margin-top: 0.375rem;
    position: relative;
    left: 50%;
    transform: translateX(-50%);

    img {
      width: 18.75rem;
      height: 18.75rem;
    }
  }

  .title {
    margin-top: 1rem;
    display: flex;
    justify-content: space-between;
    color: #a2754c;
  }

  .n_p_name {
    text-align: left;
  }

  .title_right {
    font-size: 0.8rem;
  }

  .project_name {
    margin-top: 1.25rem;
    width: 100%;
    justify-content: center;
    display: flex;
    align-items: center;
    height: 1.5625rem;
    color: #fff;
    background: #1e1e2a;
    border-radius: 0.3125rem;
    font-size: 0.75rem;
  }

  .tags {
    display: flex;
    flex-wrap: wrap;
    height: auto;
    font-size: 0.75rem;
    color: #6c6b6a;
    position: relative;
    margin: 0 auto;
    margin-top: 0.875rem;
    width: auto;

    // height: 0.625rem;
    span {
      // position: absolute;
      padding: 0.25rem 0.25rem;
      border-radius: 0.375rem;
      background: #e0e0e0;
      display: inline-block;
      transform: scale(0.8);
    }
  }

  .detail {
    width: 100%;
    height: auto;
    display: flex;
    flex-wrap: wrap;
    height: auto;
    font-size: 0.9rem;
    margin-top: 1rem;
    position: relative;
    border: 1px solid #a2754c;
    padding: 1rem 0 0.5rem 0;
    border-radius: 10px;
    color: #a2754c;

    .bold {
      margin-bottom: .2rem;
    }

    &>div {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      // margin-right: 0.625rem;
      margin-top: 0.5rem;
      padding-left: 0.3125rem;
      padding-right: 0.3125rem;
      // border-right: 1px solid #e0e0e0;
      margin-bottom: 0.5rem;

      // zoom: 0.9;
      &:last-child {
        border: 0;
      }

      & span {
        display: inline-block;
        transform: scale(0.9);
      }
    }
  }

  .desc {
    margin-top: 1.25rem;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;

    // padding-bottom: 3.75rem;
    .desc_title {
      color: #1e1e2a;
      width: auto;
      font-size: 0.875rem;
      padding: 0 0.625rem 0.3125rem;
      border-bottom: 3px solid #1e1e2a;
      font-weight: bold;
    }

    .desc_notice {
      margin-top: 1.25rem;

      .noticeList {
        .noticeListItem {
          font-size: 0.75rem;
          margin-top: 0.75rem;
        }
      }
    }
  }
}
</style>
