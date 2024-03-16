<template>
  <div class="project" style="height: 100%;overflow-y: auto;">
    <Nav leftText=''>
      <template #left>
        <div></div>
      </template>
    </Nav>
    <div class="projectWrapper">
      <Purchase />
      <!-- <van-tabs v-model:active="active" class="projectTab">
        <van-tab name="1" title="Ongoing">
          <Ongoing />
        </van-tab>
        <van-tab name="2" title="Finish">
          <Finished />
        </van-tab>
      </van-tabs> -->
    </div>
    <!-- <Service @doService="doService" /> -->
  </div>
  <!-- <MyLoading :show="loadingShow" title="Loading..."></MyLoading> -->
  <MyTab></MyTab>
</template>

<script lang="ts">
import { _alert, lang } from "../../global/common";
import { defineComponent, reactive } from "vue";
import { Image } from "vant";
import Nav from "../../components/Nav.vue";
import MyTab from "../../components/Tab.vue";
import MyListBase from "../../components/ListBase.vue";
import Service from "../../components/service.vue";
// import MyLoading from '../../components/Loading.vue';
import Purchase from "../../components/Purchase.vue";
import Finished from '../../components/Finished.vue';
import Ongoing from "../../components/Ongoing.vue";
import { Button, Tab, Tabs } from "vant";

export default defineComponent({
  components: {
    Nav, MyTab,
    MyListBase,
    [Button.name]: Button,
    [Image.name]: Image,
    [Tab.name]: Tab,
    [Tabs.name]: Tabs,
  },
});
</script>
<script lang="ts" setup>
import { getSrcUrl, imgPreview } from "../../global/common";
import { onMounted, ref } from "vue";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();
let isRequest = false;
const active = ref("0");

const doService = () => {
  console.log("im service");
};

const loadingShow = ref(true);
const pageRef = ref();
let pageUrl = ref("c=User&a=team");
const tableData = ref<any>({});

const onPageSuccess = (res: any) => {
  tableData.value = res.data;
  loadingShow.value = false;
};

onMounted(() => {
  if (location.href.indexOf("?t=project") > 0) {
    active.value = "1";
  }
});
</script>
<style lang="scss" scoped>
.project {
  width: 100%;
  overflow-x: hidden;
  background: #84973b;
  // :deep(.van-nav-bar) {
  //   background-color: #4eb848;
  // }

  :deep(.van-nav-bar__title) {
    .alter {
      font-size: 18px;
    }
  }

  .projectWrapper {
    box-sizing: border-box;
    padding-bottom: 3.75rem;

    .projectTab {
      :deep(.van-tabs__wrap) {
        .van-tabs__nav--line {
          position: fixed;
          width: 100%;
          height: auto;
          left: -0.3125rem;
          padding-left: 0.625rem;
          box-sizing: border-box;
          padding-right: 0.625rem;
          padding-top: 0.625rem;
          padding-bottom: 0.625rem;
          z-index: 10;
          background: #fff;
          padding-top: 1rem;
        }
      }

      :deep(.van-tabs__line) {
        display: none;
        background-color: #fff;
      }

      :deep(.van-tab) {
        &.van-tab--active {
          position: relative;

          &::after {
            position: absolute;
            bottom: -1rem;
            content: " ";
            border: 0.5rem solid transparent;
            border-top: 0.5rem solid #1e1e2a;
          }
        }

        .van-tab__text {
          color: #bcbbbc;
          border: 1px solid #bcbbbc;
          background: #fff;
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
          color: #fff;
          background: #1e1e2a;
          border: 1px solid #1e1e2a;
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

.home_tip_show {
  .dialog_top {}

  .dialog_content {
    padding: 1.25rem 0.625rem;
    box-sizing: border-box;
    font-size: 0.75rem;

    .notice_list {
      .notice_item {
        line-height: 1rem;
        margin-bottom: 1.25rem;
        text-align: center;
      }
    }
  }

  .dialog_confirm_btn {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1.25rem;

    span {
      display: inline-block;
      height: 2.25rem;
      width: 14.0625rem;
      line-height: 2.25rem;
      text-align: center;
      font-size: 0.875rem;
      background: linear-gradient(to right, #bb332d, #e06c3b);
      color: #fff;
      border-radius: 0.3125rem;
    }
  }
}
</style>