<template>
  <div class="project" style="height: 100%;overflow-y: auto;">
    <Nav leftText=''>
      <template #left>
        <div></div>
      </template>
    </Nav>
    <div class="projectWrapper">
      <Projects /> 

      <!-- <van-tabs v-model:active="active" class="projectTab">
        <van-tab name="0" title="PRODUCT">
          <Projects /> 
        </van-tab>
        <van-tab name="1" title="RECEIVE">
          <Purchase />
        </van-tab>
      </van-tabs> -->
    </div>

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
import Projects from '../../components/Projects.vue';
import Finished from '../../components/Finished.vue';
import Ongoing from '../../components/Ongoing.vue';
import Purchase from "../../components/Purchase.vue";

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

let isRequest = false
const active = ref('0')

const doService = () => {
  console.log('im service')
}

const loadingShow = ref(true)
const pageRef = ref()
let pageUrl = ref('c=User&a=team')
const tableData = ref<any>({})

const onPageSuccess = (res: any) => {
  tableData.value = res.data
  loadingShow.value = false
}

onMounted(() => {
  if (location.href.indexOf('?t=project') > 0) {
    active.value = '1'
  }
})

</script>
<style lang="scss" scoped>
.project {
  background: #84973b;
  width: 100%;
  overflow-x: hidden;

  .projectWrapper {
    padding: 0 1.02rem;
    box-sizing: border-box;
    padding-bottom: 1.75rem;

    .projectTab {
      :deep(.van-tabs__wrap) {
        .van-tabs__nav--line {
          position: fixed;
          left: 50%;
          transform: translateX(-50%);
          width: 100%;
          height: auto;
          box-sizing: border-box;
          z-index: 10;
          background: #84973b;
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
          color: #fff;
          border: 1px solid #666;
          background: #666;
          width: 100%;
          height: 1.75rem;
          border-radius: 1rem;
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
          border: 1px solid #fff;
          width: 100%;
          height: 1.75rem;
          border-radius: 1rem;
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