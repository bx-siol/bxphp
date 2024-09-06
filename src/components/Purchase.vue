<template>
  <div class="Projects">
    <MyListBase :url="pageUrlOnGoing" ref="pageRefOnGoing" @success="onPageSuccesso">
      <template #default="{ list }">
        <div class="basicProjects">
          <div class="projectList">
            <div v-for="(item, index) in tableDatao.list" :key="index">

              <div class="projectItem">
                <div class="detailLeft">

                  <div class="basicItemLeft">
                    <div class="detail">
                      <img :src="logo" style="height: 1.5rem;width: 7rem;margin: 0.5rem 0;">
                      <div class="detailLeft_left">
                        <div class="unitprice">
                          <span>{{ item.goods_name }}</span>
                          <span style="color:#cb1a00">₹{{ cutOutNum(item.price) }}</span>
                        </div>
                        <div class="dailyearnings">
                          <span>{{ t('周期') }}</span>
                          <span>{{ item.total_days }}/{{item.days}}</span>
                        </div>
                        <div class="totalrevenue">
                          <span>{{ t('总收入') }}</span>
                          <span>
                            ₹{{ (item.rate * item.price * item.total_days * item.num / 100).toFixed(2) }}
                          </span>
                        </div>
                        <div class="dailyearnings">
                          <span>{{ t('数量') }}</span>
                          <span>{{ item.num }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="basicItemRight">
                    <img :src="imgFlag(item.icon)" class="productImg" />
                  </div>
                </div>

                <div class="detailRight">
                  <template v-if="item.status == 1">
                    <template v-if="item.receive == 1">
                      <div class="receiveBtn" @click="onReceive(item)">
                        <img :src="receive" style="height: 1.5rem; width: 1.5rem;">
                      </div>
                    </template>
                    <template v-else>
                      <div class="receiveBtnNo" @click="onReceiveNo(item)">
                        <img :src="receive" style="height: 1.5rem; width: 1.5rem;">
                      </div>
                    </template>
                  </template>
                  <template v-else>
                    <div class="receiveto"></div>
                  </template>
                </div>
              </div>

            </div>
          </div>
        </div>
      </template>
    </MyListBase>
    <MyTab></MyTab>
  </div>
  <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>
<script lang="ts">
import { onMounted, ref, defineComponent } from "vue";
import { useRoute, useRouter } from "vue-router";
import MyListBase from "./ListBase.vue";
import MyLoading from "../components/Loading.vue";
import MyTab from "./Tab.vue";
import http from "../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../global/common";
import { _alert, lang, cutOutNum } from "../global/common";
import { Tab, Tabs, CountDown, Icon, Button } from "vant";

export default defineComponent({
  components: {
    MyListBase,
    MyLoading,
    MyTab,
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
import { useI18n } from 'vue-i18n';
import receive from '../assets/img/project/receive.png';
import logo from '../assets/img/home/home_top.png';

const { t } = useI18n();

const loadtitle = ref("Loading...")
const pageUrlOnGoing = ref('c=Product&a=order&status=1');
const pageRefOnGoing = ref()
const loadingShow = ref(true);
const tableDatao = ref<any>({});
let isRequest = false
const Receiveh = ref<any>({})
const tipShow = ref(false);

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

const imgFlag = (src: string) => {
  return getSrcUrl(src, 1);
};

const onPageSuccesso = (res: any) => {
  tableDatao.value = res.data;
  loadingShow.value = false;
  basicProjectsd.value.list = res.data
};

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
  loadingShow.value = true;
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
    http({
      url: 'c=Product&a=receiveProfit',
      data: { osn: Receiveh.value.osn }
    }).then((res: any) => {
      isRequest = false;
      loadingShow.value = false;
      if (res.code != 1) {
        _alert(res.msg)
        return
      }
      _alert(res.msg, function () {
        Receiveh.value.receive = 0
      })
    })
  }, delayTime)
}
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
  position: fixed;
  width: 100%;
  height: auto;
  left: -1.3125rem;
  padding-left: 0.625rem;
  box-sizing: border-box;
  padding-right: 0.625rem;
  padding-top: 0.625rem;
  padding-bottom: 0.625rem;
  z-index: 10;
  background: white;
  top: 6.6rem;
}

.Projects /deep/.van-tabs__nav--line {
  display: flex !important;
  justify-content: center;
  padding: 0.8rem 0 0.2rem;
  background-color: #fff;
}

.Projects /deep/.van-tabs__content {
  padding: 0 0.4rem;
  margin-top: 4rem;
}

.Projects /deep/.van-swipe__track {
  justify-content: center
}
</style>
<style lang="scss" scoped>
.Projects {
  color: black;

  .van-tabs__nav--card {
    border: none;
    border-color: #00a8a9;
    border-radius: 3px;
    overflow: hidden;
  }

  :deep(.van-tab) {
    &.van-tab--active {
      position: relative;
      background-color: transparent;
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

  .basicProjects {
    .projectList {
      margin-bottom: 2rem;

      .projectItem {
        height: 7.5rem;
        margin-bottom: 1rem;
        display: flex;
        flex-direction: row;
        position: relative;

        .detailLeft {
          height: 7.5rem;
          float: left;
          width: 88%;
          background: url(../assets/img/project/peroject_bg.png);
          background-repeat: no-repeat;
          background-size: 100% 100%;
          display: flex;
          align-items: center;

          .basicItemLeft {
            height: 7.5rem;
            padding-left: 3%;
            width: 50%;
            float: left;

            .detail {
              font-size: 1rem;
              display: flex;
              align-items: flex-start;
              flex-direction: column;
              justify-content: center;
              margin-top: 0.2rem;

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
                }
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

          .basicItemRight {
            width: 35%;
            height: 6.5rem;
            float: left;
            display: flex;
            align-items: center;
            justify-content: space-around;
            margin-left: 11%;

            .sold_out {
              position: relative;
              z-index: 1;
              width: 4rem;
              height: 4rem;
              opacity: 0.6;
              left: -47%;
              top: 29%;
              transform: translate(-50%, -50%);
            }
          }
        }

        .detailRight {
          height: 7.5rem;
          width: 10%;
          margin-left: 2%;

          .receiveBtn {
            height: 7.5rem;
            width: 100%;
            background-color: black;
            display: flex;
            align-items: center;
            justify-content: space-around;
          }

          .receiveBtnNo {
            height: 7.5rem;
            width: 100%;
            background: #c3c3c3;
            display: flex;
            align-items: center;
            justify-content: space-around;
          }

          .receiveto {height: 7.5rem;
            width: 100%;
            background: #c3c3c3;
            display: flex;
            align-items: center;
            justify-content: space-around;
          }
        }
      }
    }
  }
}
</style>
