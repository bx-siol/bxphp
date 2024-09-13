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

    :deep(.van-tabs__wrap) {
        height: auto;
    }

    .project {
        background: #fff;
        width: 100%;
        overflow-x: hidden;

        .projectWrapper {
            padding: 1vh 1rem;
            box-sizing: border-box;
            padding-bottom: 1.75rem;

            .projectTab {
                :deep(.van-tab) {
                    padding: 0;

                    &.van-tab--active {
                        position: relative;
                        background-color: transparent;
                        // &::after {
                        //     position: absolute;
                        //     bottom: -0.3rem;
                        //     content: ' ';
                        //     border: 2px solid #00b57e;
                        //     width: 1rem;
                        //     border-radius: 6px;
                        //     // border-top: 0.5rem solid ;
                        // }
                    }

                    .van-tab__text {
                        border: none !important;
                        color: black !important;
                        white-space: nowrap;
                        width: 6rem;
                        border-radius: 4rem;
                        text-align: center;
                        font-weight: bold;
                    }
                }

                :deep(.van-tab--grow:first-of-type) {
                    margin-left: -1rem;
                }

                :deep(.van-tab--active) {
                    .van-tab__text {
                        border: none !important;
                        background-color: #eb1700 !important;
                        color: #fff !important;
                        padding: 0.8rem 0.6rem;
                        white-space: nowrap;
                        width: 11rem;
                        border-radius: 4rem;
                        text-align: center;
                    }
                }

                :deep(.van-tabs__line) {
                    display: none;
                    background-color: #fff;
                }

                :deep(.van-tabs__nav--line) {
                    padding-top: 0.425rem;
                }

                :deep(.van-tabs__nav) {
                    background-color: white;
                    border-radius: 9rem;
                    padding: 0rem !important;
                    border: 1px solid #eb1700;
                }

                :deep(.van-grid-item__content--center) {
                    flex-direction: row;
                    padding: 1rem 0.375rem;
                }
            }
        }
    }
</style>