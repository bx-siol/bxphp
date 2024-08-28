<template>
  <div class="project" style="height: 100%;overflow-y: auto;">
    <Nav leftText=''></Nav>
    <div class="projectWrapper">
      <van-tabs v-model:active="active" class="projectTab">
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
    </div>
    <!-- <Service @doService="doService" /> -->
  </div>
  <!-- <MyLoading :show="loadingShow" title="Loading..."></MyLoading> -->
</template>

<script lang="ts">
import { _alert, lang } from "../../global/common"
import { defineComponent, onMounted, reactive, ref } from 'vue';
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

const doService = () => {
  console.log('im service')
}

const loadingShow = ref(true)
const pageRef = ref()
// let pageUrl = ref('c=Product&a=order')
const tableData = ref<any>({})

const onPageSuccess = (res: any) => {
  tableData.value = res.data
  loadingShow.value = false
}

onMounted(() => {

})

</script>
<style lang="scss" scoped>
.project {
  background: #fff;
  width: 100%;
  overflow-x: hidden;

  .projectWrapper {
    padding: 0 1rem;
    box-sizing: border-box;
    padding-bottom: 1.75rem;

    .projectTab {
      :deep(.van-tabs__wrap) {
        .van-tabs__nav--line {
          position: fixed;
          width: 100%;
          height: auto;
          left: 50%;
          transform: translateX(-50%);
          box-sizing: border-box;
          z-index: 10;
          background: #fff;
          padding: 1rem 0.625rem 0.625rem;
          max-width: 640px;

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
          color: #64523e;
          border: 1px solid #64523e;
          background: #fff;
          width: 100%;
          height: 1.75rem;
          border-radius: 0.8125rem;
          box-sizing: border-box;
          display: flex;
          justify-content: center;
          align-items: center;
        }
      }

      :deep(.van-tab--active) {
        .van-tab__text {
          color: #fff;
          background: #64523e;
          border: 1px solid #64523e;
          width: 100%;
          height: 1.75rem;
          border-radius: 0.8125rem;
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