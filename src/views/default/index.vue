<template>
  <div class="index">
    <MyTab :key="menuKey" :newscount="newscount"></MyTab>
    <div class="index_wrap">
      <div class="index_top">
        <div class="headbox">
          <van-image :src="nestie" style="width: 1.8rem; height: 1.8rem;" class="logs"></van-image>

          <van-image :src="illustration" style="width: 5rem; height: 1.4rem;left:-2px;"></van-image>

          <div class="u-flex u-center">
            <MyLanguage :showIcon="true" top="unset" :switchLanStyle="switchLanStyle"></MyLanguage>
          </div>
          <van-image @click="tipShow = true" :src="hasMsg ? Msg : NoMsg"
            style="width: 1.5rem; height: 1.5rem;left:0.3rem;" v-if="false"></van-image>
        </div>
        <div class="money" v-if="false">
          <div class="money-top">
            <div class="nbg1">
              <div class="nbg2">
                <img :src="Withdrawal">
                <div @click="onLink({ name: 'Finance_withdraw' })">
                  <p>₹ {{ cutOutNum(wallet2.balance, 2) }}</p>
                  <span class="desc">{{ t('钱包余额') }}</span>
                </div>
              </div>
              <div class="nbg2">
                <img :src="Recharge">
                <div @click="onLink({ name: 'Finance_recharge' })">
                  <p>₹ {{ cutOutNum(wallet.balance, 2) }}</p>
                  <span class="desc">{{ t('充值钱包2') }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="mynotice">
            <MyNoticeBar :notice-list="tdata.notice" :need-pop="false" height="1.375rem"></MyNoticeBar>
          </div>
        </div>
        <div class="backg" style="padding: 1rem">
          <div class="myswiper">
            <MySwiper :kv="tdata.kv" height="12.5rem"></MySwiper>
          </div>
          <div class="index_cer">
            <div class="menubox">
              <div
                style="display: flex;flex-wrap:wrap;justify-content: center;justify-content: space-between; width: 100%">
                <a class="divs" href="javascript:;" style="justify-content: flex-end;"
                  @click="onLink({ name: 'Finance_recharge' })">
                  <van-image :src="m1"></van-image>
                </a>
                <!-- <span class="line"></span> -->
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Finance_withdraw' })">
                  <van-image :src="m2"></van-image>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Gift_redpack' })">
                  <van-image :src="m3"></van-image>
                </a>
                <a class="divs" href="javascript:;" style="justify-content: flex-start;"
                  @click="onLink({ name: 'User_team' })">
                  <van-image :src="m4"></van-image>
                </a>
              </div>

              <div
                style="display: flex;flex-wrap:wrap;justify-content: center;justify-content: space-between; width: 100%">
                <a class="divs" href="javascript:;" style="justify-content: flex-end;"
                  @click="onLink({ name: 'Gift_lottery' })">
                  <van-image :src="m5"></van-image>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Purchase' })">
                  <van-image :src="m6"></van-image>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'monthly' })">
                  <van-image :src="m7"></van-image>
                </a>
                <a class="divs" href="javascript:;" style="justify-content: flex-start;" @click="appdload">
                  <van-image :src="m8"></van-image>
                </a>
              </div>
            </div>

            <div class="videobox">
              <video controlslist="nodownload noplaybackrate" disablePictureInPicture controls :src="videosrc"
                style="width: 100%;border-radius: 8px;"></video>
            </div>

            <div>
              <div class="column_title2">
                <img :src="horn1" style="width: 1.2rem;margin-right: 0.45rem;">
                task reward
              </div>
              <div class="malls u-flex u-bet">
                  <div @click="onLink({ name: 'Ext_task',params: { id: item.id } })" style="width: 32%;height: 4rem;margin-bottom: 5px;" v-for="(item, index) in taskdata" :key="index" >
                      <van-image :src="imgFlag(item.img)" style="height: 4rem;width: 100%;"></van-image>
                  </div>              
              </div>
              <div class="column_title2">
                <img :src="horn2">
                popular products
              </div>
              <div class="products">
                <HomeProjects />
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <van-dialog v-model:show="tipShow" style="border-radius:16px" :showConfirmButton="false" class-name="home_tip_show"
      class="home_tip_shows">
      <div class="dialog_top">
        <img :src="bulletin" style="width: 20rem;margin-bottom: -1px;">
        <!-- <p>OFFICIAL TIPS</p> -->
        <div @click="tipShow = false" style="position: absolute; top: 1rem; right: 1rem">
          <!-- <van-icon style="color:#64523e;font-size:26px;" name="close" /> -->
        </div>
      </div>
      <div class="dialog_content">
        <div class="notice_list">
          <div v-html="tdata.tip.content" style="padding: 0 1rem 1rem; max-height: 14rem; overflow-y: auto"></div>
        </div>
      </div>
      <div class="dialog_confirm_btn" @click="tipShow = false">
        <span>{{ t('确定') }}</span>
      </div>
    </van-dialog>
    <!-- <Service @doService="doService" /> -->
    <div style="bottom: 10rem" class="service" @click="doService76">
      <img :src="i76" />
    </div>

    <van-dialog v-model:show="tipShow1" style="border-radius: 0" :showConfirmButton="false" class-name="home_tip_show">
      <div class="dialog_top">
        <img :src="DialogBg1" />
      </div>
      <div style="margin-top: -6.05rem; margin-bottom: 3.4rem" class="dialog_confirm_btn" @click="t120lq()">
        <span
          style="height: 2.7rem; font-size: 1.5rem; color: #ffea75; border-radius: 13rem; text-transform: uppercase !important; line-height: 2.7rem">{{
            t('收到') }}</span>
      </div>
    </van-dialog>

    <van-dialog v-model:show="tipShow2" style="border-radius: 0; background: none" :showConfirmButton="false"
      class-name="home_tip_show">
      <div class="dialog_top">
        <img :src="DialogBg120" />
      </div>
      <div style="height: 3.4rem; width: 100%; margin-top: -4.9rem; margin-bottom: 3.4rem" class="dialog_confirm_btn"
        @click="t120()">
        <span
          style="height: 2.7rem; font-size: 1.5rem; color: #ffea75; border-radius: 13rem; text-transform: uppercase !important; line-height: 2.7rem">{{
            t('收到') }}</span>
      </div>
    </van-dialog>
  </div>
</template>

<script lang="ts">
import { _alert, lang } from "../../global/common";
import { defineComponent, ref, reactive, onMounted } from "vue";
import MyTab from '../../components/Tab.vue';
import MySwiper from '../../components/Swiper.vue'
import MyNoticeBar from '../../components/NoticeBar.vue'
import { Image, ActionSheet, Dialog } from "vant";
import MyLanguage from "../../components/Language.vue";
import horn1 from '../../assets/img/signin/horn1.png'
import horn2 from '../../assets/img/signin/horn2.png'
import bulletin from "../../assets/img/home/bulletin.png";
import nestie from '../../assets/img/home/nestie.jpg'
import illustration from '../../assets/img/login/illustration.png';
// import DialogBg1 from '../../assets/img/120lq.jpg';
// import DialogBg120 from '../../assets/img/120.png';
import i76 from '../../assets/img/76.png';
import m1 from '../../assets/img/home/home-icon-1-1.png'
import m2 from '../../assets/img/home/home-icon-1-2.png'
import m3 from '../../assets/img/home/home-icon-1-3.png'
import m4 from '../../assets/img/home/home-icon-1-4.png'
import m5 from '../../assets/img/home/home-icon-1-5.png'
import m6 from '../../assets/img/home/home-icon-1-6.png'
import m7 from '../../assets/img/home/home-icon-1-7.png'
import m7s from '../../assets/img/home/home-icon-1-7s.png'
import m8 from '../../assets/img/home/home-icon-1-8.png'
import rewards from '../../assets/img/home/home-banner-3-1.png'
import income from '../../assets/img/home/home-banner-3-2.png'
import invite from '../../assets/img/home/home-banner-3-3.png'
import videosrc from '../../assets/video/video.mp4'
import { Card, Button, Tag, Tab, Tabs, Swipe, SwipeItem, Icon } from 'vant';
import Nav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';
import HomeProjects from '../../components/HomeProject.vue';
import HomePurchased from '../../components/HomePurchased.vue';

export default defineComponent({
  name: "index",
  components: {
    MyTab, MySwiper, MyNoticeBar, MyListBase,
    [Icon.name]: Icon,
    [Image.name]: Image,
    [ActionSheet.name]: ActionSheet,
    [Dialog.Component.name]: Dialog.Component,
    [Card.name]: Card,
    [Button.name]: Button,
    [Tag.name]: Tag,
    [Tab.name]: Tab,
    [Tabs.name]: Tabs,
    [Swipe.name]: Swipe,
    [SwipeItem.name]: SwipeItem
  }
})
</script>
<script lang="ts" setup>
import { CSSProperties, computed } from 'vue';
import { useRouter } from 'vue-router';


import { checkLogin, goLogin, isLogin } from "../../global/user";
import http from "../../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../../global/common";
import { config } from "process";
import { useStore } from "vuex";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const store = useStore()
const pageuser = isLogin()

const doService76 = () => {

  tipShow1.value = true;
}
const active = ref(0)
const appdload = () => {

  window.location.href = '/app'
}
const router = useRouter()
const switchLanStyle = computed<CSSProperties>(() => {
  return {
    border: '1px solid #fff',
    borderRadius: '0.275rem',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    boxSizing: 'border-box',
    padding: '0 0.2rem',
  }
})
const hasMsg = ref<boolean>(false)

const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const onLink = (to: any) => {
  goRoute(to)
}

const goInvite = () => {
  // router.push({ path: '/invite' })
  router.push({ path: '/points' })

}

const doService = () => {
  console.log('doService')
}
const newsdata = ref<any>([])
const taskdata = ref<any>([])
const tdata = ref<any>({
  user: {},
  kv: [],
  notice: [],
  news: [],
  about: {},
  tip: {},
  video: {}
})
// let pageUrl = ref('c=Product&a=order')

const onClickVideo = () => {
  if (tdata.value.video.url) {
    window.open(tdata.value.video.url)
    return
  }
  onLink({ name: 'News_info', params: { id: tdata.value.video.id } })
}

const loading = ref(false)
const t120 = () => {
  if (loading.value) {
    return;
  } else {
    loading.value = true;
  }
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
    http({
      url: 'a=get120rs'
    }).then((res: any) => {
      if (res.code != 1) {
        _alert({
          message: 'You have received the new reward',
          onClose: () => {
            tipShow2.value = false;
            setTimeout(() => {
              tipShow1.value = false;
            }, 300);
          }
        });
        return
      }
      _alert({
        message: 'Received successfully',
        onClose: () => {
          tipShow2.value = false;
          setTimeout(() => {
            tipShow1.value = false;
          }, 300);
        }
      });
    })
  }, delayTime)
}
const t120lq = () => {
  if (t120ok.value != 0) {
    _alert({
      message: 'You have received the new reward',
      onClose: () => {
        setTimeout(() => {
          tipShow1.value = false;
        }, 300);
      }
    });
    return;
  }
  tipShow2.value = true;
}
const t120ok = ref(0)
const actions = ref([])
const tipShow2 = ref(false)
const tipShow1 = ref(false)
const tipShow = ref(false)
const appshow = ref(true)
const newscount = ref('')
const menuKey = ref(0)
const init = () => {
  if (window.location.href.indexOf('csisolar.in') > 0 || window.location.href.indexOf('csisolar.life ') > 0) {
    appshow.value = false;
  }

  http({
    url: 'c=Ext&a=task'
  }).then((res: any) => {
    if (res.code != 1) {
      return
    }
    taskdata.value = res.data.list;
  })

  http({
    url: 'a=index'
  }).then((res: any) => {
    if (res.code != 1) {
      return
    }
    tdata.value = res.data
    if (tdata.value.tip) {
      tipShow.value = true
      hasMsg.value = true
    }
    t120ok.value = res.data.gift;
    newscount.value = res.data.newscount;
    localStorage.newscount = res.data.newscount;

    var str = ',';
    res.data.newsids.forEach(itemnews => { str += itemnews.id + ','; });
    if (localStorage.newsids == undefined) localStorage.newsids = '';
    if (str != localStorage.newsids) { localStorage.newsids = str; }
    var ckcount = '';
    if (localStorage.rnewsids != undefined) {
      var ac = {};
      var rstr = localStorage.rnewsids.split(',');
      var cstr = ',';
      rstr.forEach(element => {
        if (element) {
          //检查id 是否与服务端一致
          if (localStorage.newsids.indexOf(',' + element + ',') >= 0) {
            cstr += element + ',';
          }
        }
      });
      localStorage.rnewsids = cstr;
      for (var i = 0; i < cstr.length; i++) {
        var chars = cstr.charAt(i);
        if (ac[chars]) {
          ac[chars]++;
        } else {
          ac[chars] = 1;
        }
      }
      ckcount = ac[','] - 1;
      ckcount = localStorage.newscount - ckcount;
      if (ckcount <= 0) ckcount = '';
    } else {
      ckcount = res.data.newscount;
    }
    store.commit('setnewscountc', ckcount);
    menuKey.value++;
    if (res.data.service_arr && res.data.service_arr.length > 0) {
      for (let i in res.data.service_arr) {
        let item = res.data.service_arr[i]
        actions.value.push({
          name: item.name,
          subname: item.type_flag + ': ' + item.account,
          account: item.account,
          type: item.type
        })
      }
    }
  })

}


let isRequest = false

const imgShow = (src: string) => {
  imgPreview(src)
}

const loadingShow = ref(true)
const pageRef = ref()

const tableData = ref<any>({})
const pdata = reactive({})

const onPageSuccess = (res: any) => {
  tableData.value = res.data
  loadingShow.value = false
}



const onReceive = (item: any) => {
  if (isRequest) {
    return
  } else {
    isRequest = true
  }
  http({
    url: 'c=Product&a=receiveProfit',
    data: { osn: item.osn }
  }).then((res: any) => {
    isRequest = false
    if (res.code != 1) {
      _alert(res.msg)
      return
    }
    _alert({
      type: 'success',
      message: res.msg,
      onClose: () => {
        item.receive = 0
      }
    })
  })
}

const onReceiveNo = (item: any) => {
  _alert('Currently unavailable')
}


onMounted(() => {
  init()
})
</script>

 

<style scoped>
.service {
  width: 2.5rem;
  height: 2.5rem;
  position: fixed;
  right: 0.625rem;
  bottom: 6.75rem;
  z-index: 10;
}

.van-stepper__input,
.invest_wrap .cont .van-stepper button {
  color: #3d3d3d;
}

.invest_wrap .cont .van-cell__title,
.invest_wrap .cont .van-cell__value {
  color: #3d3d3d;
}

.invest .van-field__control {
  color: white;
}

.invest_wrap .cont .van-cell::after {
  border-color: #544c4c;
}

.goodsBuyPop {
  right: 0;
}
</style>
<style lang="scss" scoped>
@keyframes bg-pan-left {
  0% {
    background-position: 100% 50%
  }

  100% {
    background-position: 0 50%
  }
}

@keyframes clippath {

  0%,
  to {
    -webkit-clip-path: inset(0 0 98% 0);
    clip-path: inset(0 0 98% 0)
  }

  25% {
    -webkit-clip-path: inset(0 98% 0 0);
    clip-path: inset(0 98% 0 0)
  }

  50% {
    -webkit-clip-path: inset(98% 0 0 0);
    clip-path: inset(98% 0 0 0)
  }

  75% {
    -webkit-clip-path: inset(0 0 0 98%);
    clip-path: inset(0 0 0 98%)
  }
}

.home_tip_show {
  .dialog_top {}

  .dialog_content {
    padding: 0.825rem 0.425rem;
    box-sizing: border-box;
    font-size: 0.75rem;
    background-color: #fff;

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
    background-color: #fff;

    span {
      display: inline-block;
      height: 2.25rem;
      width: 14.0625rem;
      line-height: 2.25rem;
      text-align: center;
      font-size: 0.875rem;
      background: linear-gradient(to right, #c49b6c 20%, #a77d52);
      color: #fff;
      border-radius: 1.3125rem;
    }
  }
}

.index_wrap {
  .invite_icon {
    width: 100%;
    height: auto;
    overflow: hidden;
    position: relative;
    margin: 0 auto;
    box-sizing: border-box;

    .bg-pan-left {
      animation: bg-pan-left 8s both
    }

    .marquee_shine :after {
      animation: clippath 3s infinite -1.5s linear;
    }

    .marquee_shine :before,
    .marquee_shine :after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border: 0.38rem solid rgba(234, 51, 35, .8);
      transition: all .5s;
      border-radius: 10.28rem;
      animation: clippath 3s infinite linear; //bg-pan-left 8s both, //
    }

    img {
      width: 99%;
      margin: 0 auto;
      // position: absolute;
      // top: -2.25rem;
      // left: 0.75rem;
    }
  }

  .invite_icon:before,
  .invite_icon::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 0.3rem solid rgb(241 199 24 / 90%);
    transition: all .5s;
    border-radius: 11.28rem;
    animation: clippath 2s infinite linear;
  }

  .home_tab_wrapper {
    :deep(.van-tabs__wrap) {
      .van-tabs__nav--line {
        position: relative;
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
      .van-tab__text {
        color: #bcbbbc;
        border: 1px solid #bcbbbc;
        background: #fff;
        padding: 0 0.375rem;
        width: 100px;
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
        background: #bd312d;
        border: 1px solid #bd312d;
        padding: 0 0.375rem;
        width: 100px;
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
</style>