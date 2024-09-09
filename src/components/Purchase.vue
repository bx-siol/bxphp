<template>
  <div class="Projects">
    <MyListBase :url="pageUrlOnGoing" ref="pageRefOnGoing" @success="onPageSuccesso">
      <template #default="{ list }">
        <div class="basicProjects">
          <div class="projectList">
            <div v-for="(item, index) in tableDatao.list" :key="index" class="projectItem">

              <div class="basicItemLeft">
                <img :src="imgFlag(item.icon)" class="imgs" />
                <span style="position: relative;color: #009900;left: 20%;left: 0.5rem;top: -6.5rem;font-weight: bold;font-size: 0.9rem;">{{item.goods_name }}</span>
              </div>

              <div class="basicItemRight">
                <div class="detail">
                  <div class="detailLeft_left">
                    <div class="dailyearnings">
                      <span>{{ t('周期') }}</span>
                      <span>{{ item.total_days }}/{{ item.days }}</span>
                    </div>
                    <div class="totalrevenue">
                      <span>{{ t('总收入') }}</span>
                      <span style="color: #ede000;">
                        ₹{{ (item.rate * item.price * item.total_days * item.num / 100).toFixed(2) }}
                      </span>
                    </div>
                    <div class="dailyearnings">
                      <span>{{ t('数量') }}</span>
                      <span>{{ item.num }}</span>
                    </div>
                    <div class="totalrevenue" style="height: 2.5rem;margin-bottom: 0;">
                      <span style="color: #ede000;font-weight: bold;font-size: 1rem;"> </span>
                      <template v-if="item.status == 1">
                        <template v-if="item.receive == 1">
                          <div class="buy" @click="onReceive(item)">{{t('领取')}} </div>
                        </template>
                        <template v-else>
                          <div class="buy" @click="onReceiveNo(item)" style="background-color: #808080;color:white;">{{t('明天领取')}}</div>
                        </template>
                      </template>
                      <template v-else>
                        <div></div>
                      </template>
                    </div>
                  </div>
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
  padding: 0 0.6rem;
  background-color: #ebf9e8;

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
      margin-top: 1rem;

      .projectItem {
        width: 100%;
        height: 7rem;
        box-shadow: 0px 0px 10px 0px #d1d1d1;
        margin-bottom: 1rem;
        background: url(../assets/img/project/peroject_bg.png);
        background-repeat: no-repeat;
        background-size: 100% 100%;
        border-radius: 10px;
        overflow: hidden;

        .basicItemLeft {
          width: 45%;
          height: 7rem;
          float: left;
          border-radius: 10px;

          .imgs {
            height: 7rem;
            border-radius: 10px;
          }
        }

        .basicItemRight {
          height: 7rem;
          margin-left: 3%;
          width: 48%;
          float: left;

          .detail {
            font-size: 1rem;
            display: flex;
            align-items: flex-start;
            flex-direction: column;
            justify-content: center;
            margin-top: 0.6rem;

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

                .buy {
                  width: 6rem;
                  background-color: white;
                  color: #009900;
                  height: 1.6rem;
                  line-height: 1.6rem;
                  text-align: center;
                  border-radius: 30px;
                  font-weight: bold;
                }
              }
            }
          }

        }
      }
    }
  }
}
</style>
