<template>
  <div>
    <div class="service" @click="doService">
      <img :src="service">
    </div>

    <div style="    bottom: 10rem;" class="service" @click="app">
      <img :src="app98">
    </div>

    <van-action-sheet v-model:show="serviceShow" :cancel-text="t('关闭')" :description="t('联系经理')" close-on-click-action>
      <template #default>
        <div v-for="item in actions" @click="onServiceSelect(item)"
          style="overflow: hidden;padding: 1rem 5%;border-bottom: 1px solid #efefef;">

          <van-image :src="item.type == 1 ? img_telegram : img_whatsapp" style="float: left;" width="2.5rem"
            height="2.5rem" />

          <div style="float: right;text-align: right;">
            <div>{{ item.name }}</div>
            <div style="font-size: 0.9rem;color: #666666;">{{ item.account }}</div>
          </div>
        </div>
      </template>
    </van-action-sheet>

    <van-dialog :key="did" :id="did" v-model:show="tipShow2" style="border-radius: 0;background: none;"
      :showConfirmButton="false" class-name="home_tip_show">
      <div class="dialog_top">
        <iframe id="ifesc" :src="urcl" style="height: calc(100% - 50px)" width="100%" frameborder="no" border="0"
          marginwidth="0" marginheight="0" scrolling="no" allowtransparency="yes"></iframe>
      </div>
      <div style="    width: 100%;
    margin-top: 0rem;
    margin-bottom: 0rem;
    cursor: pointer;
    height: 40px;
    line-height: 41px; 
    background-color: #4e6ef2;
    border-radius: 10px;
    font-size: 17px;
    box-shadow: none;
    font-weight: 400;
    border: 0;
    outline: 0;
    letter-spacing: normal;
    text-align: center;
    color: #fff;" class="dialog_confirm_btn" @click="t120()">
        Reload
      </div>

      <div style=" margin-top: 0.2rem;   width: 100%;
    margin-top: 0rem;
    margin-bottom: 0rem;
    cursor: pointer;
    height: 30px;
    line-height: 30px; 
    background: linear-gradient(to right, #bb332d, #e06c3b);
    border-radius: 10px;
    font-size: 17px;
    box-shadow: none;
    font-weight: 400;
    border: 0;
    outline: 0;
    letter-spacing: normal;
    text-align: center;
    color: #fff;" class="dialog_confirm_btn" @click="tipShow2 = false">
        Close
      </div>
    </van-dialog>
  </div>
</template>
 
<script lang="ts">
import { Image, ActionSheet, Dialog } from "vant";
import { defineComponent, ref, onMounted } from 'vue'
import { _alert, lang } from "../global/common";
import app98 from "../assets/img/team/app98.png";
export default defineComponent(
  {
    name: "index",
    components: {
      [ActionSheet.name]: ActionSheet,
      [Image.name]: Image,
      [Dialog.Component.name]: Dialog.Component,
    }
  })
</script>

<script lang="ts" setup>
import service from '../assets/img/team/service.png';
import http from "../global/network/http";

import img_whatsapp from '../assets/img/whatsapp.png';
import img_telegram from '../assets/img/telegram.png';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const did = ref(0)
const tipShow2 = ref(false)
const emit = defineEmits(['doService'])
const tipShow = ref(false)
const doService = () => {
  serviceShow.value = true;
}
const app = () => {
  window.location.href = '/app'
}

const urcl = ref('')
const actions = ref([])
const serviceShow = ref(false)
const onServiceSelect = (ev: any) => {

  let url = ''
  if (ev.type == 1) {
    url = 'https://t.me/' + ev.account
  } else if (ev.type == 2) {
    //url = 'https://api.whatsapp.com/send/?phone=' + ev.account
    url = 'https://wa.me/' + ev.account
  } else {
    return
  }
  serviceShow.value = false;

  //tipShow2.value = true;
  window.open(url, 'blank')
  //window.location.href = url 
  //urcl.value = url 
}
const t120 = () => {
  did.value++
}

onMounted(() => {
  const delayTime = Math.floor(Math.random() * 1000);
  // setTimeout(() => {
    http({
      url: 'a=GetService'
    }).then((res: any) => {
      if (res.code != 1) {
        return
      }
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
  // }, delayTime)
})

</script>
 
<style lang="scss" scoped>
.service {
  width: 2.5rem;
  height: 2.5rem;
  position: fixed;
  right: 0.625rem;
  bottom: 6.75rem;
  z-index: 10;
}
</style>