<template>
  <div class="Projects">
    <div class="basicProjects">
      <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #default="{ list }">
          <div class="basicProjectsList">
            <div v-for="(item, index) in list" :key="index" class="bottom">
              <div :style="{ 'width': '100%', 'box-shadow': 'none', 'background': `url(${hs})`, 'background-size': '100% 100%', }" class="basicItem">
                <table style="width:100%;color: #f5f7fd;">
                  <tr>
                    <td style="width: 30%;">
                      <div style="font-size: 1.5rem;font-weight: bold;text-align: center;" v-if="item.money != 0">
                        {{ item.money }} RS
                      </div>
                      <div style="font-size: 1.8rem;font-weight: bold;text-align: center;" v-if="item.money == 0">{{ 100 - item.discount }}%</div>
                    </td>
                    <td style="width: 100%;text-align: left;height: 6rem;display: flex;flex-direction: column;justify-content: center;margin-left: 10%;">
                      <div style="font-weight: bold;" v-if="item.money != 0">{{ t("邀请券") }}</div>
                      <div style="font-weight: bold;" v-if="item.money == 0">{{ t("折扣券") }}</div>
                      <div style="font-size:12px;margin-top: 0.2rem;">{{ t("有效期至") }} : {{ item.effective_time.substring(0,10) }}</div>
                    </td>
                    <td style="width: 20%;">
                      <div class="Expired">
                        <img :src="expired" >
                      </div>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </template>
      </MyListBase>
    </div>
  </div>
  <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api'
import { Col, Row, Icon, Collapse, CollapseItem } from "vant"

export default defineComponent({
  components: {
    [Col.name]: Col,
    [Row.name]: Row,
    [Icon.name]: Icon,
    [Collapse.name]: Collapse,
    [CollapseItem.name]: CollapseItem
  }
})
</script>

<script lang="ts" setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import MyListBase from './ListBase.vue';
import MyLoading from './Loading.vue';
import http from "../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../global/common";
import { _alert, lang } from "../global/common";
import { useI18n } from 'vue-i18n';
import hs from "../assets/c/hs.png";
import expired from "../assets/c/expired.png";

const { t } = useI18n();

const activeNames = ref(['0']);
const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const router = useRouter()
const loadingShow = ref(true)
const pageRef = ref()
const route = useRoute()

let pageUrl = ref('c=Coupon&a=list&status=3') //&type=' + route.params.type + '
const tableData = ref<any>({})

const onPageSuccess = (res: any) => {
  tableData.value = res.data
  loadingShow.value = false
}

const usefun = (item: any) => {

  alert(item);
}

const getProjectDetail = (item: any) => {
  router.push({ name: 'Project_detail', params: { pid: item.gsn } })
}


</script>
<style lang="scss" scoped>
.Projects {
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
    margin-top: 1rem;

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
    }

    .basicProjectsList {
      .basicItem {
        margin-top: 1.25rem;
        box-sizing: border-box;
        height: 6rem;
        display: flex;
        align-items: center;
        width: 100%;
        background: #fff;
        position: relative;

        .Expired {
          width: 3.5rem;
          height: 3.5rem;
        }
      }

      .bottom:last-child {
        margin-bottom: 1rem;
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
    }

    .coreProjectsList {
      .coreItem {
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
}
</style>