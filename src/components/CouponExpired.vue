<template>
  <div class="Projects">
    <div class="basicProjects">

      <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #default="{ list }">

          <div class="basicProjectsList">
            <div v-for="(item, index) in list" :key="index">
              <div :style="{ 'width': '100%', 'background': `url(${hs})`, 'background-size': '100% 100%', }"
                class="basicItem">
                <table style="width:100%;color: #f5f7fd;">
                  <tr>
                    <td class="tdbox">
                      <div style="display:flex;width:8rem;align-items:center;">
                        <div class="big" v-if="item.money != 0">₹{{ item.money }}</div>
                        <div class="big" v-if="item.money == 0">{{ 100 - item.discount }}%</div>
                        <div class="tdboxs" v-if="item.money != 0">{{ t("邀请券") }}</div>
                        <div class="tdboxs" v-if="item.money == 0">{{ t("折扣券") }}</div>
                      </div>
                      <div style="font-size:12px;position: relative;bottom: -6px;">{{ t("有效期至") }}:{{ item.effective_time }}</div>
                    </td>

                    <td class="tdbox2">
                      <div class="tdboxs2" v-if="item.money != 0">₹{{ item.money }}</div>
                      <div class="tdboxs2" v-if="item.money == 0">{{ 100 - item.discount }}%</div>
                      <div class="small" v-if="item.money != 0">{{ t("邀请券") }}</div>
                      <div class="small" v-if="item.money == 0">{{ t("折扣券") }}</div>
                      <p class="Expired"> {{ t("已过期") }}</p>
                    </td>
                  </tr>
                </table>
                <div class="remark" v-if="false" style="font-size: 12px;width: 80%;margin-right: 4rem;">
                  <van-collapse :border="false" v-model="activeNames">
                    <van-collapse-item :border="false" :title="t('使用说明')" :name="item.id">
                      <!-- {{ item.remark }} -->
                      <p> 1. Invite friends to buy any equipment to get an extra 50 Rs</p>
                      <p>2. You can only use 1 card each time you invite friends</p>
                      <p> 3. It can be directly exchanged and recharged to the balance</p>
                    </van-collapse-item>
                  </van-collapse>
                </div>
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
import Product from '../assets/img/project/product.png';
import MyListBase from './ListBase.vue';
import MyLoading from './Loading.vue';
import MyTab from "./Tab.vue";
import http from "../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../global/common";
import { _alert, lang } from "../global/common";
import hs from "../assets/c/hs.png";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const analogdata = ref<any>([
  { effective_time: '11:30', money: 50, discount: 80, },
  { effective_time: '11:30', money: 0, discount: 85, },
])
const activeNames = ref(['0']);
const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const router = useRouter()
const loadingShow = ref(true)
const pageRef = ref()
const route = useRoute()
console.log(route.params)

let pageUrl = ref('c=Coupon&a=list&type=' + route.params.type + '&status=3')
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
<style lang="scss"  scoped >
.Projects {


  .basicProjects {

    .basicProjectsList {
      .basicItem {
        margin-bottom: 1.25rem;
        padding: 0.8rem 1rem 0.8rem 0.2rem;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        flex-direction: column;
        justify-content: center;
        position: relative;
        border-radius: 6px;
        height: 6.8rem;
        .tdbox {
          padding-left: 0.5rem;
          width: 80%;
          line-height: 20px;

          .big {
            font-size: 36px;
            font-weight: bold;
            margin-right: 0.25rem;
          }

          .tdboxs {
            font-size: 16px;
            font-weight: bold;
          }
        }

        .tdbox2 {
          margin-top: -0.8rem;
          display: block;
          .tdboxs2 {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
          }
          .small {
            font-size: 12px;
            margin: 0.3rem 0;
            text-align: right;
          }

        }

        .Expired {
          font-size: 12px;
          border: 1px solid #fff;
          border-radius: 4px;
          padding: 4px;
          margin-bottom: -2rem;
          text-align: center;
        }
      }
    }
  }

}
</style>

<style  scoped>
.remark /deep/ .van-cell--clickable:active {
  background: #0000;
}

.remark /deep/.van-cell__title,
.remark /deep/.van-cell__value {
  color: rgb(245, 247, 253);
  text-align: left;
  flex: 0;
  width:10rem;
  white-space: nowrap;
}

.remark /deep/.van-collapse-item__content {
  padding:8px !important;
  font-size: 10px;
  background: #0000;
  color: #2d2d2d;
}

.remark /deep/.van-collapse-item__wrapper {
  background: #f2f2f2;
  margin-top: 0.3rem;
  padding: 0rem;
  position: absolute;
  top: 2.225rem;
  left: 50%;
  transform: translatex(-38.5%);
  width: 129%;
  z-index: 2;
  border-radius:0 0 6px 6px;
}

.remark /deep/.van-cell__right-icon {
  display: block !important;
  color: #fff;
}

.remark /deep/.van-cell {
  background: rgba(0, 0, 0, 0) !important;
  padding: 0px !important;
  padding-top: 5px !important;
  padding-left: 0.7rem !important;
}
</style>