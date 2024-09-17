<template>
  <div class="index">
    <MyTab :key="menuKey" :newscount="newscount"></MyTab>
    <div class="index_wrap">
      <div class="index_top">
        <div class="headbox">
          <van-image :src="hornhome_top" style="height: 2rem;"></van-image>
          <div class="u-flex u-center">
            <MyLanguage :showIcon="true" top="unset" :switchLanStyle="switchLanStyle"></MyLanguage>
          </div>
        </div>

        <div class="backg">
          <div class="index_cer">
            <div class="myswiper" v-if=false>
              <MySwiper :kv="tdata.kv" height="12.5rem"></MySwiper>
            </div>
            <div class="videobox">
              <video controlslist="nodownload noplaybackrate" disablePictureInPicture controls :src="videosrc" style="width: 100%;"></video>
            </div>

            <div class="index_msg" style="margin-top: 1rem;">
              <MyNoticeBar :notice-list="tdata.notice" :need-pop="false" color="#009900" height="1rem"></MyNoticeBar>
            </div>

            <div style="display: flex;padding: 0 1rem;">
              <img :src="recharge" style="width: 53.5%;margin-left: -0.8rem;"
                @click="onLink({ name: 'Finance_recharge' })" />
              <img :src="withdraw" style="width: 53.5%;" @click="onLink({ name: 'Finance_withdraw' })" />
            </div>

            <div class="menubox" style="padding: 0 1rem;">
              <div
                style="display: flex;flex-wrap:wrap;justify-content: center;justify-content: space-between; width: 100%">
                <a class="divs" href="javascript:;" @click="onLink({ name: 'User_team' })">
                  <van-image :src="m1"></van-image>
                  <p style="margin-left: -0.5rem;">{{ t('团队') }}</p>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Share' })">
                  <van-image :src="m2"></van-image>
                  <p>{{ t('邀请') }}</p>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Gift_lottery' })">
                  <van-image :src="m3"></van-image>
                  <p>{{ t('幸运抽奖') }}</p>
                </a>
                <a class="divs" href="javascript:;" @click="onLink({ name: 'Gift_redpack' })">
                  <van-image :src="m4"></van-image>
                  <p>{{ t('红包') }}</p>
                </a>
                <a class="divs" href="javascript:;" @click="appdload">
                  <van-image :src="m5"></van-image>
                  <p>{{ t('App') }}</p>
                </a>
              </div>
            </div>

            <div style="padding:1rem;">
              <div class="column_title2">
                Government Subsidies
                <img :src="m6" style="width: 0.5rem;margin-left: 1rem;">
              </div>
              <div class="zx">
                <div class="item">
                  <div
                    style="margin: 0.2rem 0;display: flex;justify-content: space-around;color: #808080;font-size: 0.9rem">
                    <div style="display: flex;">
                      <img :src="people" style="height: 1rem;width: 1rem;margin-right: 0.5rem;">
                      <b>{{ InviteesNum >= 3 ?'3' : (InviteesNum < 3 ? InviteesNum : '0')  }}/3</b>
                    </div>
                    <div style="display: flex;">
                      <img :src="rs" style="height: 1rem;width: 1rem;margin-right: 0.5rem;"><b>200 RS</b>
                    </div>
                  </div>
                  <div
                    style="margin: 0.2rem 0; display: flex;justify-content: space-around;color: #808080;font-size: 0.9rem;">
                    <div>Week Invitation</div>
                    <div>Bonus Amount</div>
                  </div>
                  <div class="savebtn" :style="InviteesNum >= 3 ? 'background-color: #009900;':''" @click=" InviteesNum >=3 ? GetRewards(1) : ''" >Receive bonus</div>
                </div>
                <div class="item">
                  <div
                    style="margin: 0.2rem 0;display: flex;justify-content: space-around;color: #808080;font-size: 0.9rem">
                    <div style="display: flex;">
                      <img :src="people" style="height: 1rem;width: 1rem;margin-right: 0.5rem;">
                      <b>{{ InviteesNum >= 5 ?'5' : (InviteesNum < 5 ? InviteesNum : '0')  }}/5</b>
                    </div>
                    <div style="display: flex;">
                      <img :src="rs" style="height: 1rem;width: 1rem;margin-right: 0.5rem;"><b>200 RS</b>
                    </div>
                  </div>
                  <div
                    style="margin: 0.2rem 0; display: flex;justify-content: space-around;color: #808080;font-size: 0.9rem;">
                    <div>Week Invitation</div>
                    <div>Bonus Amount</div>
                  </div>
                  <div class="savebtn" :style="InviteesNum >= 5 ? 'background-color: #009900;':''" @click=" InviteesNum >= 5 ? GetRewards(2) : ''"  >Receive bonus</div>
                </div>
                <div class="item">
                  <div
                    style="margin: 0.2rem 0;display: flex;justify-content: space-around;color: #808080;font-size: 0.9rem">
                    <div style="display: flex;">
                      <img :src="people" style="height: 1rem;width: 1rem;margin-right: 0.5rem;">
                      <b>{{ InviteesNum >= 10 ?'10' : (InviteesNum < 10 ? InviteesNum : '0')  }}/10</b>
                    </div>
                    <div style="display: flex;">
                      <img :src="rs" style="height: 1rem;width: 1rem;margin-right: 0.5rem;"><b>400 RS</b>
                    </div>
                  </div>
                  <div
                    style="margin: 0.2rem 0; display: flex;justify-content: space-around;color: #808080;font-size: 0.9rem;">
                    <div>Week Invitation</div>
                    <div>Bonus Amount</div>
                  </div>
                  <div class="savebtn" :style="InviteesNum >= 10 ? 'background-color: #009900;':''" @click=" InviteesNum >= 10 ? GetRewards(3) : ''"> Receive bonus</div>
                </div>
              </div>
            </div>

            <div style="padding: 0 1rem 1rem;">
              <div class="column_title2">
                Recommended Project
                <img :src="m6" style="width: 0.5rem;margin-left: 1rem;">
              </div>
              <div class="products">
                <HomeProjects />
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <van-dialog v-model:show="tipShow" style="border-radius:0;background-color:transparent;" :showConfirmButton="false"
      class-name="home_tip_show" class="home_tip_shows">
      <div class="dialog_top">
        <img :src="bulletin" style="width: 20rem;margin-bottom: -1px;">
        <div @click="tipShow = false" style="position: absolute; top: 1rem; right: 1rem">
        </div>
      </div>
      <div class="dialog_content">
        <div class="notice_list">
          <div v-html="tdata.tip.content" style="padding: 0 1rem 1rem; max-height: 14rem; overflow-y: auto"></div>
        </div>
      </div>
      <div class="dialog_confirm_btn" @click="confirmTip">
        <span>{{ t('确定') }}</span>
      </div>
    </van-dialog>
  </div>
  <MyLoading :show="loadingShow" title="Submit"></MyLoading>
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
import m5 from '../../assets/img/home/home-icon-1-5.png'
import recharge from '../../assets/img/home/recharge.png'
import withdraw from '../../assets/img/home/withdraw.png'
import m6 from '../../assets/img/home/home-icon-1-6.png'
import people from '../../assets/img/home/people.png'
import rs from '../../assets/img/home/rs.png'
import videosrc from '../../assets/video/video.mp4'
import MyLoading from "../../components/Loading.vue";

export default defineComponent({
  name: "index",
  components: {
    MyTab, MySwiper, MyNoticeBar, MyListBase,MyLoading,
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
const tipShow = ref(false)
const appshow = ref(true)
const newscount = ref('')
const menuKey = ref(0)
const InviteesNum = ref(0)
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
    InviteesNum.value = res.data.InviteesNum;

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

let isRequest = false
const loadingShow = ref(false);
const GetRewards = (type:number)=>{
  if (isRequest) {
    return
  } else {
    isRequest = true
  }
  loadingShow.value = true;
  http({
    url: 'a=GetRewards',
    data: { type: type }
  }).then((res: any) => {
    loadingShow.value = false;
    if (res.code != 1) {
        isRequest = false
        _alert(res.msg)
        return
    }
    _alert(res.msg, function () {        
        isRequest = false
      })

  })
}

onMounted(() => {
  init()
})
</script>

<style lang="scss" scoped>
.home_tip_show {
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

  .dialog_content {
    background-color: #ebf9e8;
  }

  .dialog_confirm_btn {
    width: 100%;
    background-color: #fff;

    span {
      display: inline-block;
      height: 2.5rem;
      width: 100%;
      line-height: 2.5rem;
      text-align: center;
      font-size: 0.875rem;
      background-color: #009900;
      color: #fff;
    }
  }
}

.index_wrap {
  background-color: #ebf9e8;

  .index_msg {
    height: 2.5rem;
    overflow: hidden;
    padding: 0 1rem;

    :deep(.van-icon__image) {
      height: 1.5rem;
      width: 1.5rem;
      margin-left: -0.5rem;
    }

    :deep(.van-notice-bar) {
      background-color: white !important;
      border-radius: 5px;
    }
  }

  .zx {
    .item {
      background-color: white;
      margin-top: 1rem;
      border-radius: 10px;
      box-shadow: 0px 0px 10px 0 #d1d1d1;
      padding: 0.5rem 1rem;

      .savebtn {
        background-color: #808080;
        text-align: center;
        color: white;
        height: 1.5rem;
        border-radius: 20px;
        line-height: 1.5rem;
        font-size: 0.9rem;
      }
    }
  }
}
</style>