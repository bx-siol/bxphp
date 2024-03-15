<template>
  <div class="Projects">
    <van-tabs v-model:active="active" type="card" :border="false" background="" title-active-color="#fff"
      title-inactive-color="#fff">
      <div>
        <van-tab :key="alltab" :title="t('全部')" v-if="false">
          <MyListBase :url="pageUrlAll" ref="pageRefAll" @success="onPageSuccess">
            <template #default="{ list }">
              <div class="basicProjects">
                <!-- <div class="index_cer_title" style="color: #fff">All</div> -->
                <div class="projectList">
                  <div v-for="(item, index) in tableData.list" :key="index">
                    <div class="back">
                      <!-- <div class="title">
                        <img :src="fire">
                        {{ item.goods_name }}
                        <p>Investment Cycle: {{ item.days }} day</p>
                      </div> -->
                      <div class="projectItem">
                        <div style="display: flex;align-items: center;width: 100%;justify-content: space-between;">
                          <div class="head">
                            <img :src="imgFlag(item.icon)" class="productImg" />
                            <div class="addRs" v-if="false">₹{{ cutOutNum(item.price) }}</div>
                          </div>
                          <div class="basicItemRight info">
                            <div class="detail">
                              <div class="goodname">{{ item.goods_name }}</div>

                              <div class="detailLeft">
                                <div class="unitprice">
                                  <span>{{ t('小时收益') }}</span>
                                  <span>₹{{ cutOutNum(((item.rate / 100) *
                                    item.price * item.num) / 24, 2) }}</span>
                                </div>
                                <div class="dailyearnings">
                                  <span>{{ t('每日收入') }}</span>
                                  <span>
                                    ₹{{ cutOutNum((item.rate / 100) * item.price * item.num, 2) }}
                                  </span>
                                </div>
                                <div class="totalrevenue">
                                  <span>{{ t('总收入') }}</span>
                                  <span>
                                    ₹{{ cutOutNum((item.rate / 100) * item.price * item.days * item.num, 2) }}
                                  </span>
                                </div>
                                <div class="dailyearnings">
                                  <span>{{ t('收到的天数') }}</span>
                                  <span style="color: #64503e;">{{ item.total_days }}</span>
                                </div>
                                <div class="dailyearnings">
                                  <span>{{ t('数量') }}</span>
                                  <span style="color: #64503e;">{{ item.num }}</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="receive">
                          <template v-if="item.status == 1">
                            <template v-if="item.receive == 1">
                              <van-button @click="onReceive(item)" round size="mini" class="receiveBtn" style=""
                                type="success">{{ t('领取') }}</van-button>
                            </template>
                            <template v-else>
                              <van-button @click="onReceiveNo(item)" round size="mini" class="receiveBtnNo" style=""
                                type="success">{{ t('明天领取') }}</van-button>
                            </template>
                          </template>
                          <template v-else>
                            <van-button round size="mini" class="receiveto" style="" type="successNo">{{ t('结束')
                            }}</van-button>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </MyListBase>
        </van-tab>
        <van-tab :title="t('进行中')">
          <MyListBase :url="pageUrlOnGoing" ref="pageRefOnGoing" @success="onPageSuccesso">
            <template #default="{ list }">
              <span class="total">{{ t('总计') }}: {{ cutOutNum(t_investment) }}RS</span>
              <div class="basicProjects">
                <!-- <div class="index_cer_title" style="color: #fff">OnGoing</div> -->
                <div class="projectList">
                  <div v-for="(item, index) in tableDatao.list" :key="index">
                    <div class="back">
                      <div class="projectItem">
                        <div style="display: flex;align-items: center;width: 100%;justify-content: space-between;">
                          <div class="head">
                            <img :src="imgFlag(item.icon)" class="productImg" />
                            <div class="addRs" v-if="false">₹{{ cutOutNum(item.price) }}</div>
                          </div>
                          <div class="basicItemRight info">
                            <div class="detail">
                              <div class="goodname">{{ item.goods_name }}</div>

                              <div class="detailLeft">
                                <div class="unitprice">
                                  <span>{{ t('价格') }}</span>
                                  <span>₹{{ cutOutNum(item.price) }}</span>
                                </div>
                                <div class="dailyearnings">
                                  <span>{{ t('周期') }}</span>
                                  <span>{{ item.total_days }}</span>
                                </div>
                                <div class="totalrevenue">
                                  <span>{{ t('总收入') }}</span>
                                  <span>
                                    ₹{{ cutOutNum(item.rate* item.price * item.total_days * item.num / 100 , 2) }}
                                  </span>
                                </div>
                                <div class="dailyearnings" v-if="false">
                                  <span>{{ t('收到的天数') }}</span>
                                  <span style="color: #64503e;">{{ item.total_days }}</span>
                                </div>
                                <div class="dailyearnings" v-if="false">
                                  <span>{{ t('数量') }}</span>
                                  <span style="color: #64503e;">{{ item.num }}</span>
                                </div>
                              </div>

                            </div>
                          </div>
                        </div>

                        <div class="receive">
                          <template v-if="item.status == 1">
                            <template v-if="item.receive == 1">
                              <van-button @click="onReceive(item)" round size="mini" class="receiveBtn" style=""
                                type="success">{{ t('领取') }}</van-button>
                            </template>
                            <template v-else>
                              <van-button @click="onReceiveNo(item)" round size="mini" class="receiveBtnNo" style=""
                                type="success">{{ t('明天领取') }}</van-button>
                            </template>
                          </template>
                          <template v-else>
                            <van-button round size="mini" class="receiveto" style="" type="successNo">{{ t('结束')
                            }}</van-button>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </MyListBase>

        </van-tab>
        <van-tab :title="t('结束')" v-show="false">
          <MyListBase :url="pageUrlFinish" ref="pageRefFinish" @success="onPageSuccessf">
            <template #default="{ list }">
              <div class="basicProjects" style="margin-top: 1rem;">
                <!-- <div class="index_cer_title" style="color: #fff">Finish</div> -->
                <div class="projectList">
                  <div v-for="(item, index) in tableDataf.list" :key="index">
                    <div class="back">
                      <!-- <div class="title">
                        <img :src="fire">
                        {{ item.goods_name }}
                        <p>Investment Cycle: {{ item.days }} day</p>
                      </div> -->
                      <div class="projectItem">
                        <div style="display: flex;align-items: center;width: 100%;justify-content: space-between;">
                          <div class="head">
                            <img :src="imgFlag(item.icon)" class="productImg" />
                            <div class="addRs" v-if="false">₹{{ cutOutNum(item.price) }}</div>
                          </div>
                          <div class="basicItemRight info">
                            <div class="detail">
                              <div class="goodname">{{ item.goods_name }}</div>

                              <div class="detailLeft">
                                <div class="unitprice">
                                  <span>{{ t('价格') }}</span>
                                  <span>₹{{ cutOutNum(item.price) }}</span>
                                </div>
                                <div class="dailyearnings">
                                  <span>{{ t('周期') }}</span>
                                  <span>{{ item.total_days }}</span>
                                </div>
                                <div class="totalrevenue">
                                  <span>{{ t('总收入') }}</span>
                                  <span>
                                    ₹{{ cutOutNum(item.rate  * item.price * item.total_days * item.num / 100, 2) }}
                                  </span>
                                </div>
                                <div class="dailyearnings" v-if="false">
                                  <span>{{ t('收到的天数') }}</span>
                                  <span style="color: #64503e;">{{ item.total_days }}</span>
                                </div>
                                <div class="dailyearnings" v-if="false">
                                  <span>{{ t('数量') }}</span>
                                  <span style="color: #64503e;">{{ item.num }}</span>
                                </div>
                              </div>

                            </div>
                          </div>
                        </div>
                        <div class="receive">

                          <template v-if="item.status == 1">
                            <template v-if="item.receive == 1">
                              <van-button @click="onReceive(item)" round size="mini" class="receiveBtn" style=""
                                type="success">{{ t('领取') }}</van-button>
                            </template>
                            <template v-else>
                              <van-button @click="onReceiveNo(item)" round size="mini" class="receiveBtnNo" style=""
                                type="success">{{ t('明天领取') }}</van-button>
                            </template>
                          </template>
                          <template v-else>
                            <van-button round size="mini" class="receiveto" style="" type="successNo">{{ t('结束')
                            }}</van-button>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </MyListBase>

        </van-tab>
      </div>
      <!-- <div class="receives" v-show="active != '2'">
        <van-button @click="ReceiveProfitAll()" size="mini" class="receiveBtns w100" style="" type="success">
        </van-button>
        <van-image :src="receiveall" style="width: 2.2rem"></van-image>
        <div></div>
      </div> -->
    </van-tabs>
    <!-- <MyTab></MyTab> -->
  </div>
  <MyLoading :show="loadingShow2" :title="loadtitle"></MyLoading>
</template>
<script lang="ts">
import {
  onMounted,
  ref,
  defineComponent,
} from "vue";
import { useRoute, useRouter } from "vue-router";
import MyListBase from "./ListBase.vue";
import MyLoading from "../components/Loading.vue";
import MyTab from "./Tab.vue";
import http from "../global/network/http";
import receiveall from '../assets/img/home/receiveall.png'
import fire from "../assets/ico/fire.png";
import trial from "../assets/img/2.png";
import { getSrcUrl, goRoute, imgPreview } from "../global/common";
import { _alert, lang, cutOutNum } from "../global/common";
import { Tab, Tabs, CountDown, Icon, Button } from "vant";
import { number, time } from "echarts";

export default defineComponent({
  components: {
    MyListBase, MyTab, MyLoading,
    [Image.name]: Image,
    [Tab.name]: Tab,
    [Tabs.name]: Tabs,
    [Icon.name]: Icon,
    [Button.name]: Button,
    [CountDown.name]: CountDown,
  },
});
</script>

<script lang="ts" setup>
import { useI18n } from 'vue-i18n'; const { t } = useI18n();
const onFinish = (item: any) => {
  item.status = 9;
  item.key++;
};
const imgFlag = (src: string) => {

  return getSrcUrl(src, 1);
};

const loadtitle = ref("Loading...")
const loadingShow2 = ref(false);
const pageRefAll = ref()

const pageUrlAll = ref('c=Product&a=receiveProfit');
const pageUrlOnGoing = ref('c=Product&a=order&status=1');
const pageUrlFinish = ref('c=Product&a=order&status=9');

const pageRefOnGoing = ref()
const pageRefFinish = ref()
const alltab = ref(0);
const onging = ref(0);
const finish = ref(0);
const router = useRouter();
const loadingShow = ref(true);
const newsdata = ref<any>([]);
const tableData = ref<any>({});

const tableDatao = ref<any>({});
const tableDataf = ref<any>({});
const active = ref("0");
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
const basicProjectsd = ref<basicProjects>({
  sort: 'Basic',
  list: []
})

const onPageSuccess = (res: any) => {
  tableData.value = res.data;
  loadingShow.value = false;
  basicProjectsd.value.list = res.data
};
const t_investment = ref(0.0);

const onPageSuccesso = (res: any) => {
  tableDatao.value = res.data;
  loadingShow.value = false;
  basicProjectsd.value.list = res.data
  var price = 0;
  for (var i = 0; i < tableDatao.value.list.length; i++) {
    price += tableDatao.value.list[i].price - 0
  }
  t_investment.value = price;
};
const onPageSuccessf = (res: any) => {
  tableDataf.value = res.data;
  loadingShow.value = false;
  basicProjectsd.value.list = res.data
};
const getProjectDetail = (item: any) => {
  router.push({ name: "Project_detail", params: { pid: item.gsn } });
};
let isRequest = false
const qdtxt = ref(t('收到'));
const Receiveh = ref<any>({})
const tipShow = ref(false);
const onReceive = (item: any) => {
  tipShow.value = true;
  Receiveh.value = item;
  onReceivehttp()
  return;
}
const onReceiveNo = (item: any) => {
  _alert('Currently unavailable Tomorrow')
}

const onReceivehttp = () => {

  if (isRequest) {
    return
  } else {
    isRequest = true
  }
  loadingShow2.value = true;
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
    http({
      url: 'c=Product&a=receiveProfit',
      data: { osn: Receiveh.value.osn }
    }).then((res: any) => {
      isRequest = false;
      loadingShow2.value = false;
      if (res.code != 1) {
        // toast.clear()
        // tipShow.value = false;
        // qdtxt.value = lang('收到');
        _alert(res.msg)
        return
      }
      _alert(res.msg, function () {
        Receiveh.value.receive = 0
      })
    })
  }, delayTime)
}
const ReceiveProfitAll = () => {

  if (isRequest) {
    return
  } else {
    isRequest = true
  }
  loadingShow2.value = true;
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
    http({
      url: 'order/ReceiveProfitAll',
      data: { osn: "0" }
    }).then((res: any) => {
      isRequest = false;
      loadingShow2.value = false;
      if (res.code != 200) {
        // toast.clear()
        // tipShow.value = false;
        // qdtxt.value = lang('收到');
        _alert(res.message)
        return
      }
      _alert(res.message, function () {
        Receiveh.value.receive = 0;
        window.location.reload();
      })
    })
  }, delayTime)
}

onMounted(() => {
  //自己封装的接口请求方法 aiox
  // http({
  //   //url 就是请求的地址
  //   url: "order/IndexPurchased",
  //   data: { page: 1, size: 1000 },
  // }).then((res: any) => {
  //   if (res.code != 200) {
  //     _alert({
  //       type: "error",
  //       message: res.msg,
  //       onClose: () => {
  //         router.go(-1);
  //       },
  //     });
  //     return;
  //   }

  //   newsdata.value = res.data.category_arr;
  //   tableData.value = res.data.list;
  //   loadingShow.value = false;
  // });
});
</script>
<style scoped>
.Projects /deep/.van-tabs__nav--card {
  border: none !important;
  background: none;
  width: 100%;
}

.Projects /deep/.van-tab--card {
  border-right: none !important;
}

.Projects /deep/ .van-tab--card.van-tab--active {
  background-color: transparent !important;
}

.Projects /deep/.van-tabs__wrap {
  display: flex !important;
  justify-content: center;
  padding: 0.8rem 0 0.2rem;
  background-color: #fff;
}

.Projects /deep/.van-tabs__content {
  padding: 0 1rem;
}
</style>
<style lang="scss" scoped>
.Projects {

  color: #000;

  .van-tabs__nav--card {
    border: none;
    border-color: #00a8a9;
    border-radius: 3px;
    overflow: hidden;
  }

  .total {
    position: absolute;
    width: 100%;
    text-align: center;
    top: 3.6rem;
    left: 50%;
    transform: translateX(-50%);
    padding-bottom: 1rem;
    color: #64523e;
  }

  :deep(.van-tab) {
    &.van-tab--active {
      position: relative;
      background-color: transparent;

      // &::after {
      //   position: absolute;
      //   bottom: -0.3rem;
      //   content: ' ';
      //   border: 2px solid #00b57e;
      //   width: 1rem;
      //   border-radius: 5px;
      //   // border-top: 0.5rem solid ;
      // }
    }

    .van-tab__text {
      border: none !important;
      background-color: #e6e6e6 !important;
      color: #222 !important;
      padding: 0.3rem 0.4rem;
      width: 6rem;
      border-radius: 1rem;
      text-align: center;
      white-space: nowrap;
    }
  }

  :deep(.van-tab--active) {
    .van-tab__text {
      border: none !important;
      background-color: #64523e !important;
      color: #fff !important;
      padding: 0.3rem 0.4rem;
      width: 6rem;
      border-radius: 1rem;
      text-align: center;
      white-space: nowrap;
    }
  }


  .projectList {
    display: flex;
    flex-direction: column;

    .back {
      border-radius: 1rem;
      margin-bottom: 1rem;
      box-shadow: 0px 0px 12px 2px rgb(225, 225, 225);
      // width: 10.258rem;

      .title {
        padding: 0.6rem 1.2rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        color: #fff;
        justify-content: space-around;

        p {
          font-weight: lighter;
          margin-left: 2rem;
          font-size: 14px;
        }

        img {
          width: 1.5rem;
          margin-right: 0.8rem;
        }
      }

      .projectItem {
        background: #fff;
        border-radius: 8px;
        padding: 0.4rem 0.8rem 0.8rem;
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-direction: column;

        .head {
          display: flex;
          align-items: center;
          justify-content: space-between;
          width: 5rem;
          height: 4rem;

          .productImg {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
          }

          .addRs {
            color: #ff6b44;
            margin-top: 0.4rem;
          }

        }

        .tag {
          background: #010127;
          color: #fff;
          padding: 0.3rem 0.4rem;
          font-size: 0.6rem;

          >span {
            margin-right: 0.625rem;
          }
        }

        .info {
          color: #333;
          padding: 0 2px;
          width: 68%;
        }
      }

    }

    .detail {
      flex: 1;
      min-width: 0;

      .goodname {
        font: bold 16px/30px "Rotobo";
        color: #64503e;
      }

      .detailLeft {
        display: flex;
        flex-direction: column;

        &>div {
          display: flex;
          justify-content: space-between;
          padding: 1px;

          span {
            font-size: 0.7rem;
          }

          span:nth-child(1) {
            white-space: nowrap;
            margin-right: 1rem;
            color: #222;
          }

          span:nth-child(2) {
            color: #64503e;
            font-weight: bold
          }


        }
      }

      .add {
        margin-top: 0.7rem;
        display: flex;
        justify-content: space-between;
      }


      .addIcon {
        display: inline-block;
        width: 5.4rem;
        height: 1.4rem;
        background: #e22e2f;
        color: #fff;
        text-align: center;
        line-height: 1.4rem;
        border-radius: 12px;
      }


      .detailRight {
        width: 3rem;
        height: 3rem;

        .pay {
          width: 3rem;
          height: 3rem;
          background: #1e1e2a;
          color: #fff;
          border-radius: 0.3125rem;
          display: flex;
          justify-content: center;
          align-items: center;

          .disabled {
            cursor: not-allowed;
            background-color: #6c6b6a;
            color: #6c6b6a;
            opacity: 0.5;
          }
        }
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

  .basicProjects {
    margin-top: 3rem;

    .basicProjectsSplit {
      display: flex;
      align-items: center;
      justify-content: space-between;

      .splitLine {
        height: 1px;
        border-top: 1px dashed #bcbbbc;
        width: 6.875rem;
        display: inline-block;
      }

      .splitName {
        font-size: 0.75rem;
        color: #bd312d;
      }
    }

    .basicProjectsList {
      .basicItem {
        box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 0.25rem 0px,
          rgba(14, 30, 37, 0.32) 0px 2px 1rem 0px;
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
          background: #FFD337;
          flex: 1;

          .name {
            font-size: 0.875rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            padding-bottom: 5px;

            span {
              background: rgb(189, 49, 45) url(../assets/djs.png) 3px center no-repeat;
              background-size: 18px;
              padding: 2px 3px 2px 25px;
              border-radius: 10px;

              .van-count-down {
                color: #fff;
              }
            }
          }
        }
      }
    }
  }

  .coreProjects {
    margin-top: 1rem;

    .coreProjectsSplit {
      display: flex;
      align-items: center;
      justify-content: space-between;

      .splitLine {
        height: 1px;
        border-top: 1px dashed #bcbbbc;
        width: 6.875rem;
        display: inline-block;
      }

      .splitName {
        font-size: 0.75rem;
        color: #bd312d;
      }
    }

    .coreProjectsList {
      .coreItem {
        box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 0.25rem 0px,
          rgba(14, 30, 37, 0.32) 0px 2px 1rem 0px;
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

        .coreItemLeft {
          width: 5.9375rem;
          display: flex;
          justify-content: center;
          align-items: center;

          img {
            width: 5.9375rem;
          }
        }

        .coreItemRight {
          display: flex;
          flex-direction: column;
          margin-left: 0.375rem;
          flex: 1;

          .name {
            font-size: 0.875rem;
            font-weight: bold;
          }
        }
      }
    }
  }

  .receive {
    width: 100%;
    margin-top: 0.4rem;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;

    .receiveBtn {
      width: 100%;
      padding: 0.875rem 0;
      background: linear-gradient(to right, #c49b6c 20%, #a77d52);
      border: none;
      font-size: 0.8rem;
    }

    .receiveBtnNo {
      width: 100%;
      padding: 0.875rem 0;
      background: rgb(77, 77, 77);
      border: none;
      font-size: 0.8rem;
    }

    .receiveto {
      width: 100%;
      padding: 0.875rem 0;
      background: rgb(77, 77, 77);
      border: none;
      font-size: 0.8rem;
    }
  }

  .receiveBtns {
    padding: 1rem;
    background: #FFF;
    color: #008260;
    border: none;
    font-size: 0.7rem;
  }

  .receives {
    position: fixed;
    bottom: 6rem;
    right: 0.5rem;

    .receiveBtns {
      background: url(../assets/img/home/receiveall2.png);
      background-size: 100% 100%;
      border: none;
      width: 5rem;
      height: 5rem;
      // font-size: 0.7rem;
      // position: relative;
      // left: 50%;
      // transform: translateX(-50%);
    }

    :before {
      width: 0;
      height: 0;
    }
  }

  .w100 {
    width: 80%;
  }
}
</style>
