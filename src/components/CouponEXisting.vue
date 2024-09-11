<template>
  <div class="Projects">
    <div class="basicProjects">
      <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
        <template #default="{ list }">
          <div class="basicProjectsList">
            <div v-for="(item, index) in list" :key="index" class="bottom">
              <div
                :style="{ 'width': '100%', 'box-shadow': 'none', 'background': `url(${item.money != 0 ? yhj1 : yhj2})`, 'background-size': '100% 100%', }"
                class="basicItem">
                  <div style=" color: white; line-height: 1.4vh;">
                      <div style="display: inline-block; width: 44vw; font-size: 1rem; font-weight: bold; margin-left: 13vw; vertical-align: text-top; }" v-if="item.money != 0">
                          {{ t("邀请券") }}
                      </div>
                      <div style="display: inline-block; width: 44vw; font-size: 1rem; font-weight: bold; margin-left: 13vw; vertical-align: text-top; }" v-if="item.money == 0">
                          {{ t("折扣券") }}
                      </div>
                      <div style="display: inline-block; width: 30vw; font-size: .7rem; text-align: right;">{{ t("有效期至") }}<br />{{ item.effective_time }}</div>
                  </div>
                  <div style=" margin-top: 1vh;">
                      <div style="display: inline-block; width: 56vw; font-size: 3.8rem; font-weight: bold; vertical-align: super; margin-top: -7vh; text-align: center; color: #cc1700; " v-if="item.money != 0">{{item.money }} RS</div>
                      <div style=" display: inline-block; width: 56vw; font-size: 3.8rem; font-weight: bold; vertical-align: super; margin-top: -7vh; text-align: center; color: #7f47dd;" v-if="item.money == 0">{{ 100 - item.discount }}%</div>
                      <div style="display: inline-block; width: 29vw; font-size: 0.5rem; margin-left: 3vw;">
                          <div>
                              1. Invite friends to buy anyequipment to get an extra 50 Rs<br />
                              2. You can only use 1 card eachtime you invite friends<br />
                              3. it can be directly exchanged and recharged to the balance
                          </div>
                          <div @click="usefun(item)" style="margin-top: .4vh; text-align: center; border: 1px solid; border-radius: 20px; width: 20vw; height: 2vh; line-height: 2vh; margin-left: 1vw; color: #cc1700; font-weight: bold; font-size: .8rem; "  v-if="item.money != 0">
                              {{ t('待使用') }}
                          </div>
                          <div @click="usefun(item)" style="margin-top: .4vh; text-align: center; border: 1px solid; border-radius: 20px; width: 20vw; height: 2vh; line-height: 2vh; margin-left: 1vw; color: #7f47dd; font-weight: bold; font-size: .8rem; " v-if="item.money == 0">
                              {{ t('待使用') }}
                          </div>
                      </div>
                  </div>
              </div>
              <div class="remark" v-if="false" style="font-size: 12px; margin-top: -2.1rem;">
                <van-collapse :border="false" v-model="activeNames">
                  <van-collapse-item :border="false" :title="t('使用说明')" :name="item.id">
                    <!-- {{ item.remark }} -->
                    <p>1. Invite friends to buy any equipment to get an extra 50 Rs</p>
                    <p>2. You can only use 1 card each time you invite friends</p>
                    <p>3. It can be directly exchanged and recharged to the balance</p>
                  </van-collapse-item>
                </van-collapse>
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

    import yhj1 from "../assets/c/cp1.png";
    import yhj2 from "../assets/c/cp3.png";
    import yqj from "../assets/c/cp2.png";
    import { useI18n } from 'vue-i18n';
    const { t } = useI18n();


    const activeNames = ref(['0']);
    const imgFlag = (src: string) => {
        return getSrcUrl(src, 1)
    }

    const router = useRouter()
    const loadingShow = ref(true)
    const pageRef = ref()
    const route = useRoute()

    let pageUrl = ref('c=Coupon&a=list&type=' + route.params.type + '&status=1')
    const tableData = ref<any>({})

    const onPageSuccess = (res: any) => {
        tableData.value = res.data
        loadingShow.value = false
    }

    const usefun = (item: any) => {
        if (item.money == 0) {
            location.href = location.origin + '/#/project?t=project'
        } else {
            // 'http://47.243.82.107:8288/api/?c=Coupon&a=exchange'
            // const delayTime = Math.floor(Math.random() * 1000);
            // setTimeout(() => {
            http({
                url: 'c=Coupon&a=exchange',
                data: { id: item.id }
            }).then((res: any) => {
                if (res.code != 1) {
                    _alert(res.msg)
                    return
                }
                _alert({
                    type: 'success',
                    message: res.msg,
                    onClose: () => { }
                })
            })
            // }, delayTime)

        }
        // alert(item);
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

                & > div {
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

                .pay {
                    width: 3rem;
                    height: 3rem;
                    background: #bd312d;
                    color: #fff;
                    border-radius: 0.3125rem;
                    display: flex;
                    justify-content: center;
                    align-items: center;
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

                .splitName {
                    font-size: 0.75rem;
                    color: #bd312d;
                }
            }

            .basicProjectsList {
                .basicItem {
                    margin-top: 1.25rem;
                    padding: 0.3rem 0.3rem;
                    box-sizing: border-box;
                    height: 9rem;
                    align-items: center;
                    width: 100%;
                    background: #fff;
                    position: relative;

                    .Exchange {
                        font-size: 12px;
                        border: 1px solid #fff;
                        border-radius: 4px;
                        padding: 4px;
                        // margin-bottom: -1rem;
                        text-align: center;
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

                .splitName {
                    font-size: 0.75rem;
                    color: #bd312d;
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
<style scoped>
.remark /deep/ .van-cell--clickable:active {
  background: #0000;
}

.remark /deep/.van-cell__title,
.remark /deep/.van-cell__value {
  color: rgb(245, 247, 253);
  text-align: left;
}

.remark /deep/.van-collapse-item__content {
  padding: 0px 5px !important;
  font-size: 10px;
  background: #0000;
  color: #2d2d2d;
}

.remark /deep/.van-collapse-item__wrapper {
  background: #e0e0e0;
  padding-top: 0.5rem;
  margin-top: 0.3rem;
  padding: 0.2rem;

}

.remark /deep/.van-cell__right-icon {
  display: block !important;
  margin-right: 7rem;
  color: #ffffff;
}

.remark /deep/.van-cell {
  background: rgba(0, 0, 0, 0) !important;
  padding: 0px !important;
  padding-top: 5px !important;
  padding-left: 1.2rem !important;
}
</style>
