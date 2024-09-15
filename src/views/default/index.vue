<template>
  <div class="index">
    <MyTab :key="menuKey" :newscount="newscount"></MyTab>
    <div class="index_wrap">
      <div class="index_top">
        <div class="headbox">
          <van-image :src="hornhome_top" style="height: 2rem;" ></van-image>
          <van-image :src="manage" style="height: 1.5rem;margin-left: 4rem;" @click="onLink({ name: 'Service' })" ></van-image>
          <div class="u-flex u-center">
            <MyLanguage :showIcon="true" top="unset" :switchLanStyle="switchLanStyle"></MyLanguage>
          </div>
        </div>
        <div class="backg" style="padding: 0 1rem 1rem 1rem;">
          <div class="index_cer">
            <div class="menubox">
              <div
                style="display: flex;flex-wrap:wrap;justify-content: center;justify-content: space-between; width: 100%">
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Finance_recharge' })">
                  <van-image :src="m1"></van-image>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Finance_withdraw' })">
                  <van-image :src="m2"></van-image>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'User_team' })">
                  <van-image :src="m3"></van-image>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Service' })">
                  <van-image :src="m4"></van-image>
                </a>
              </div>
            </div>

            <div class="videobox">
              <video controlslist="nodownload noplaybackrate" disablePictureInPicture controls :src="videosrc"
                style="width: 100%;border-radius: 8px;"></video>
            </div>

            <div class="index_msg">
              <MyNoticeBar :notice-list="tdata.notice" :need-pop="false" color="#000" height="1rem"></MyNoticeBar>
            </div>

            <div>
              <div class="column_title2">
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
        <div @click="tipShow = false" style="position: absolute; top: 1rem; right: 1rem">
        </div>
      </div>
      <div class="dialog_content">
        <div class="notice_list">
          <div v-html="tdata.tip.content" style="padding: 0 1rem 1rem; max-height: 14rem; overflow-y: auto"></div>
        </div>
      </div>
      <div class="dialog_confirm_btn" @click="confirmTip">
        <div></div>
      </div>
    </van-dialog>
  </div>
</template>

<script lang="ts">
import { _alert, lang } from "../../global/common";
import { defineComponent, ref, onMounted } from "vue";
import MyTab from '../../components/Tab.vue';
import MySwiper from '../../components/Swiper.vue'
import MyNoticeBar from '../../components/NoticeBar.vue'
import { Image, ActionSheet, Dialog } from "vant";
import MyLanguage from "../../components/Language.vue";
import { Card, Button, Tag, Tab, Tabs, Swipe, SwipeItem, Icon } from 'vant';
import MyListBase from '../../components/ListBase.vue';
import HomeProjects from '../../components/HomeProject.vue';

  import hornhome_top from '../../assets/img/home/home_top.png'
  import bulletin from "../../assets/img/home/bulletin.png";
  import m1 from '../../assets/img/home/home-icon-1-1.png'
  import m2 from '../../assets/img/home/home-icon-1-2.png'
  import m3 from '../../assets/img/home/home-icon-1-3.png'
  import m4 from '../../assets/img/home/home-icon-1-4.png'
  import videosrc from '../../assets/video/video.mp4'  
  import manage from '../../assets/img/home/manage.png'

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
import { isLogin } from "../../global/user";
import http from "../../global/network/http";
import { getSrcUrl, goRoute, imgPreview } from "../../global/common";
import { useStore } from "vuex";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const store = useStore()
const router = useRouter()
const hasMsg = ref<boolean>(false)
const taskdata = ref<any>([])

const appdload = () => {
  window.location.href = '/app'
}
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
const tdata = ref<any>({
  user: {},
  kv: [],
  notice: [],
  news: [],
  about: {},
  tip: {},
  video: {}
})

const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const onLink = (to: any) => {
  goRoute(to)
}

const t120ok = ref(0)
const actions = ref([])
const tipShow = ref(true)
const appshow = ref(true)
const newscount = ref('')
const menuKey = ref(0)
const init = () => {
  if (window.location.href.indexOf('csisolar.in') > 0 || window.location.href.indexOf('csisolar.life ') > 0) {
    appshow.value = false;
  }

  http({
    url: 'a=index'
  }).then((res: any) => {
    if (res.code != 1) {
      return
    }
    var needTip = getCookie("closeIndexTip") != 1
    tdata.value = res.data
    if (tdata.value.tip && needTip) {
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

const confirmTip = (item) => {
  add_cookie("closeIndexTip", "1")
  tipShow.value = false
  //router.push({ path: '/project' })
}

const add_cookie = (name, val) => {
  var exp = new Date();
  exp.setTime(exp.getTime() + 5 * 60 * 1000);
  document.cookie = name + "=" + escape(val) + ";expires=" + exp.toGMTString();//把cookie_name添加进cookie
}

const getCookie = (cookieName) => {
  if (document.cookie.length > 0) {
    /**通过String对象的indexOf()来检查这个cookie是否存在，不存在就为 -1**/
    var c_start = document.cookie.indexOf(cookieName + "=");
    if (c_start != -1) {
      /**最后这个+1其实表示"="，获取到cookie值的开始位置**/
      c_start = c_start + cookieName.length + 1;
      var c_end = document.cookie.indexOf(";", c_start);
      if (c_end == -1) c_end = document.cookie.length;

      /**通过substring()得到值**/
      var cookieValue = unescape(document.cookie.substring(c_start, c_end));
      return cookieValue;
    }
  }
  return null;
}

onMounted(() => {
  init()
})
</script>

<style lang="scss" scoped>
:deep(.van-dialog) {
  background: url(/src/assets/img/home/top_bg.png);
  background-repeat: no-repeat;
  background-size: 100% 100%;
}

.home_tip_show {
  .dialog_content {
    padding: 0.825rem 0.425rem;
    box-sizing: border-box;
    font-size: 0.75rem;
    height: 20rem;

    .notice_list {

      .notice_item {
        line-height: 1rem;
        margin-bottom: 1.25rem;
        text-align: center;
      }
    }
  }

  .dialog_confirm_btn {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1.25rem;
    height: 4rem;

    div {
      background: url(/src/assets/img/home/top_btn.png);
      background-repeat: no-repeat;
      background-size: 100% 100%;
      height: 3rem;
      width: 12rem;
    }
  }
}

.index_wrap {

  .index_msg {
    height: 2.5rem;
    background-color: #d9d9d9;
    border-radius: 5px;
    margin-bottom: 1.3rem;
    overflow: hidden;
  }
}
</style>