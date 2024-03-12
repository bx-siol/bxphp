<template>
  <div class="invite_wrapper">
    <MyTab></MyTab>
    <div class="successful_inv">
      <div class="left_dot"></div>
      <div class="right_dot"></div>
      <div class="left">
        <div>get bonus</div>
        <div class="left_bot">
          <span>{{ tdata.RS }}</span>
          <span>rs</span>
        </div>
      </div>
      <div class="right">
        <div>successful invitation</div>
        <div class="right_bot">
          <span>{{ tdata.people }}</span>
          <span>people</span>
        </div>
      </div>
    </div>
    <div class="my_team" @click="goUserTeam">
      <span>MY TEAM</span>
    </div>
    <div style="display:none" class="square_area">
      <img style=" width: 100%;" :src="imgFlag(tdata.qrcode)" @click="onPreview(tdata.qrcode)" />
    </div>
    <div class="share_link">
      <span @click="goInvitation">Share invitation link</span>
    </div>
  </div>
</template>
<script lang="ts" setup>
import { _alert, lang } from "../../global/common";
import http from "../../global/network/http";
import {
  img_banner
} from '../../global/assets';

import { copy, getSrcUrl, imgPreview } from "../../global/common";
import { useRouter } from 'vue-router';
import MyTab from "../../components/Tab.vue";
import { defineComponent, ref, onMounted } from "vue";
import MyNav from "../../components/Nav.vue";
import { Button, Grid, GridItem, Image, Stepper, Cell, CellGroup } from "vant";

const router = useRouter()

const goUserTeam = () => {
  router.push({ path: '/user/team' })
}
const goInvitation = () => {
  router.push({ path: '/share' })
}

const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const onPreview = (src: string) => {
  imgPreview(src)
}

const active = ref(0)
const value = ref(1);
const linkCopyRef = ref()

const tdata = ref({
  icode: '',
  url: '',
  qrocde: '',
  RS: 0,
  people: 0
})


onMounted(() => {
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
    http({
      url: 'c=Share&a=index'
    }).then((res: any) => {
      tdata.value = res.data
    })
  }, delayTime)
})
</script>
<style lang="scss" scoped>
.invite_wrapper {
  position: relative;
  min-height: 100%;
  background-image: url('../../assets/img/invite/invitebg.png');
  background-size: 100% 100%;
  padding: 0 1.25rem;
  box-sizing: border-box;
  position: relative;
  height: 46.875rem;

  .successful_inv {
    overflow: hidden;
    width: 100%;
    height: 5.625rem;
    position: relative;
    top: 21.25rem;
    box-sizing: border-box;
    box-shadow: rgb(14 30 37 / 12%) 0px 2px 0.25rem 0px, rgb(14 30 37 / 32%) 0px 2px 1rem 0px;
    background: #fff;
    border-radius: 0.625rem;
    padding: 1.25rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-around;

    .left_dot {
      position: absolute;
      width: 0.625rem;
      height: 0.625rem;
      background: #b84b43;
      border-radius: 50%;
      left: -0.3125rem;
      top: 2.5rem;
    }

    .right_dot {
      position: absolute;
      width: 0.625rem;
      height: 0.625rem;
      background: #b84b43;
      border-radius: 50%;
      right: -0.3125rem;
      top: 2.5rem;
    }

    .left {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      font-size: 0.875rem;

      .left_bot {
        color: #bd312d;
        margin-top: 0.625rem;

        span {
          &:nth-child(1) {
            font-size: 1rem;
          }

          &:nth-child(2) {
            font-size: 0.75rem;
          }
        }
      }
    }

    .right {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      font-size: 0.875rem;

      .right_bot {
        color: #bd312d;
        margin-top: 0.625rem;

        span {
          &:nth-child(1) {
            font-size: 1rem;
          }

          &:nth-child(2) {
            margin-left: 0.375rem;
            font-size: 0.75rem;
          }
        }
      }
    }
  }

  .my_team {
    position: relative;
    width: 100px;
    height: 32px;
    background: #f6c444;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 25px;
    z-index: 10;
    // top: 25.75rem;
    // left: 8.5625rem;
    margin: 0 auto;
    top: 20.1875rem;
    //left: 7.375rem;
  }

  .square_area {
    width: 8.5rem;
    height: 8.5rem;
    border: 0.25rem solid #eb3323;
    box-shadow: rgb(14 30 37 / 12%) 0px 2px 0.25rem 0px, rgb(14 30 37 / 32%) 0px 2px 1rem 0px;
    box-sizing: border-box;
    background: #fff;
    border-radius: 0.5rem;
    position: relative;
    margin: 0 auto;
    top: 21.5rem;
    // left: 6.125rem;
    // top: 29.0625rem;
    // left: 7.375rem;
  }

  .share_link {
    position: relative;
    top: 21.875rem;
    margin-top: 1.25rem;
    display: flex;
    justify-content: center;
    align-items: center;

    span {
      width: 10.25rem;
      height: 2.125rem;
      color: #fff;
      background: #f6c444;
      border-radius: 0.625rem;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 0.875rem;
    }
  }
}
</style>