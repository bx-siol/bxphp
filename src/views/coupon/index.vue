<template>
  <div class="project" style="height: 100%;overflow-y: auto;">
    <Nav leftText=''></Nav>
    <div class="projectWrapper">
      <van-tabs v-model:active="active" class="projectTab" @click-tab="onClickTab">
        <van-tab :title="t('Discount')">
          <van-tabs v-model:active="actives" class="projectTabs">
            <van-tab :title="t('待使用')">
              <CouponEXisting />
            </van-tab>
            <van-tab :title="t('已使用')" v-if="false">
              <CouponUsed />
            </van-tab>
            <van-tab :title="t('已过期')">
              <CouponExpired />
            </van-tab>
          </van-tabs>
        </van-tab>
        <van-tab :title="t('Invitation')">
          <van-tabs v-model:active="actives2" class="projectTabs">
            <van-tab :title="t('待使用')">
              <CouponEXisting />
            </van-tab>
            <van-tab :title="t('已使用')" v-if="false">
              <CouponUsed />
            </van-tab>
            <van-tab :title="t('已过期')">
              <CouponExpired />
            </van-tab>
          </van-tabs>
        </van-tab>
      </van-tabs>

    </div>
    <!-- <Service @doService="doService" /> -->
  </div>
  <!-- <MyLoading :show="loadingShow" title="Loading..."></MyLoading> -->
</template>

<script lang="ts">
import { _alert, lang } from "../../global/common"
import { defineComponent, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from "vue-router";
import { Image } from 'vant';
import Nav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import Service from '../../components/service.vue';
// import MyLoading from '../../components/Loading.vue';
import CouponEXisting from '../../components/CouponEXisting.vue';
import CouponUsed from '../../components/CouponUsed.vue';
import CouponExpired from '../../components/CouponExpired.vue';
import { Button, Tab, Tabs } from "vant";
export default defineComponent({
  components: {
    Nav, MyListBase,
    [Button.name]: Button,
    [Image.name]: Image,
    [Tab.name]: Tab,
    [Tabs.name]: Tabs
  }
})
</script>
<script lang="ts" setup>
import { getSrcUrl, imgPreview } from "../../global/common";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

let isRequest = false
const active = ref(0)
const actives = ref(0);
const actives2 = ref(0);

const loadingShow = ref(true)
const tableData = ref<any>({})
const route = useRoute();

const onClickTab = (index: number) => {
  index = active.value
  if (index == 0) {
    route.params.type = '1';

  } else if (index == 1) {
    route.params.type = '2';
  }
};


const onPageSuccess = (res: any) => {
  tableData.value = res.data
  loadingShow.value = false
}

onMounted(() => {

})

</script>
<style lang="scss" scoped>
.project {
  background: #84973b;
  width: 100%;
  overflow-x: hidden;

  .projectWrapper {
    padding: 0 1rem;
    box-sizing: border-box;
    padding-bottom: 1.75rem;

    .projectTab {
      top: -1px;
      :deep(.van-tabs__wrap) {
        .van-tabs__nav--line {
          position: fixed;
          width: 100%;
          height: auto;
          left: -0.3125rem;
          padding-left: 0.625rem;
          box-sizing: border-box;
          z-index: 10;
          background: #84973b;
          padding: 1rem 0.625rem 0.625rem;
        }
      }

      :deep(.van-tabs__line) {
        display: none;
        background-color: #fff;
      }

      :deep(.van-tab) {
        &.van-tab--active {
          position: relative;

          // &::after {
          //   position: absolute;
          //   bottom: -1rem;
          //   content: ' ';
          //   border: 0.5rem solid transparent;
          //   border-top: 0.5rem solid #bd312d;
          // }
        }

        .van-tab__text {
          color: #fff;
         
          background: rgb(102,102,102);
          width: 100%;
          height: 1.75rem;
          border-radius: 0.8125rem;
          box-sizing: border-box;
          display: flex;
          justify-content: center;
          align-items: center;
          font-weight: bold;
        }
      }

      :deep(.van-tab--active) {
        .van-tab__text {
          color: #84973b;
          background: #fff;
         
          width: 100%;
          height: 1.75rem;
          border-radius: 0.8125rem;
          box-sizing: border-box;
          display: flex;
          justify-content: center;
          align-items: center;
          font-weight: bold;
        }

      }

      :deep(.van-grid-item__content--center) {
        flex-direction: row;
        padding: 1rem 0.375rem;
      }
    }

    .projectTabs {
      top: -1px;

      :deep(.van-tabs__wrap) {
        display: flex;
        justify-content: center;
        padding: 0.6rem 1rem;

        .van-tabs__nav--line {
          position: fixed;
          box-sizing: border-box;
          
          width: 100%;
          padding: 0.625rem 1rem;
          border-radius: 8px;
        }
      }

      :deep(.van-tabs__line) {
        display: none;
        background-color: transparent !important;
      }

      :deep(.van-tab) {
        &.van-tab--active {
          position: relative;

          // &::after {
          //   position: absolute;
          //   bottom: -0.8rem;
          //   content: " ";
          //   border: 2px solid #4eb848;
          //   width: 1rem;
          //   border-radius: 6px;
          // }
        }

        .van-tab__text {
          font-weight: bold;
          color: #b3b3b3;
          border: none;
          background: transparent;
          width: 100%;
          height: 1.75rem;
          border-radius: 0.3125rem;
          box-sizing: border-box;
          display: flex;
          justify-content: center;
          align-items: center;
        }
      }

      :deep(.van-tab--active) {
        .van-tab__text {
          font-weight: bold;
          color: #fff;
          // background: #ed1c25;
          // border: 1px solid #ed1c25;
          width: 100%;
          height: 1.75rem;
          border-radius: 0.3125rem;
          box-sizing: border-box;
          display: flex;
          justify-content: center;
          align-items: center;
        }
      }

      :deep(.van-grid-item__content--center) {
        flex-direction: row;
        padding: 1rem 0.375rem;
      }
    }
  }
}
</style>