<template>
  <div class="successfullProjects">
    <div class="home_bottom_swip">
      <div class="title">
        <span>SUCCESSFUL PROJECTS</span>
      </div>
      <div class="swip_wrapper">
        <van-swipe :width="width" @change="change" :loop="true" :stop-propagation="true" indicator-color="white"
          :autoplay="3000" :immediate="true" :show-indicators="true" class="swip_box">
          <van-swipe-item v-for="(item, index) in successfullProjects" :key="index">
            <div class="swip_box_item">
              <div class="img">
                <img :src="item.img">
              </div>
              <div class="info">
                <div class="project_name">
                  <span>Project Name: </span>
                  <span>{{ item.projectName }}</span>
                </div>
                <div class="system_size">
                  <span>System Size / Type: </span>
                  <span>{{ item.systemSize }}</span>
                </div>
                <div class="module">
                  <span>Module: </span>
                  <span>{{ item.module }}</span>
                </div>
                <div class="location">
                  <span>Location: </span>
                  <span>{{ item.location }}</span>
                </div>
                <div class="installed">
                  <span>Installed: </span>
                  <span>{{ item.installed }}</span>
                </div>
              </div>
            </div>
          </van-swipe-item>
        </van-swipe>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from "vue";
import { Swipe, SwipeItem } from 'vant';
import Nav from '../../components/Nav.vue';
export default defineComponent({
  name: "index",
  components: {
    [Swipe.name]: Swipe,
    [SwipeItem.name]: SwipeItem
  }
})
</script>
<script lang="ts" setup>
import Swipbot1 from '../assets/img/home/swipbot1.png'
import Swipbot2 from '../assets/img/home/swipbot2.png'
import Swipbot3 from '../assets/img/home/swipbot3.png'
import internal from "stream";

type successfullProject = {
  id: string | number,
  img: string,
  projectName: string,
  systemSize: string,
  module: string,
  location: string,
  installed: string
}
const pageUrl = ref('c=News&a=list&s_cid=50')

const width = 160;
const successfullProjects = ref<Array<successfullProject>>([
  {
    id: 1,
    img: Swipbot1,
    projectName: 'Sree jagatguru Textiles Solar Plant',
    systemSize: '10MW/ Utility Solar Plant',
    module: 'CS7N-MB-AG',
    location: 'Tiruppur, Tamil Nadu, India',
    installed: '2022 March'
  },
  { id: 2, img: Swipbot2, projectName: 'Matgrow Renewable Solar Plant', systemSize: '3 MW/ Utility Solar Plant', module: 'CS7N-MB-AG', location: 'Bhuj, Gujarat, India', installed: '2022 August' },
  { id: 3, img: Swipbot3, projectName: 'Sree jagatguru Textiles Solar Plant', systemSize: '10MW/ Utility Solar Plant', module: 'CS7N-MB-AG', location: 'Tiruppur, Tamil Nadu, India', installed: '2022 March' },
  { id: 4, img: Swipbot1, projectName: 'Sree jagatguru Textiles Solar Plant', systemSize: '10MW/ Utility Solar Plant', module: 'CS7N-MB-AG', location: 'Tiruppur, Tamil Nadu, India', installed: '2022 March' },
  { id: 5, img: Swipbot2, projectName: 'Matgrow Renewable Solar Plant', systemSize: '3 MW/ Utility Solar Plant', module: 'CS7N-MB-AG', location: 'Bhuj, Gujarat, India', installed: '2022 August' },
  { id: 6, img: Swipbot3, projectName: 'Sree jagatguru Textiles Solar Plant', systemSize: '10MW/ Utility Solar Plant', module: 'CS7N-MB-AG', location: 'Tiruppur, Tamil Nadu, India', installed: '2022 March' },
])
const change = ref((index: any) => {

  // alert(index)
});
</script>
<style lang="scss" scoped>
.successfullProjects {
  .home_bottom_swip {
    margin-top: 1.25rem;

    .title {
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;

      span {
        display: inline-block;
        height: 1.625rem;
        width: 14.125rem;
        line-height: 1.625rem;
        text-align: center;
        background: #bd312d;
        color: #fff;
        border-radius: 0.3125rem;
        font-size: 0.875rem;
      }
    }

    .swip_wrapper {
      margin-top: 1.25rem;

      .swip_box {
        .swip_box_item {
          .info {
            height: 4rem;
            padding: 0.3125rem;
            box-sizing: border-box;
            background: #bd312d;
            color: #fff;
            width: 96%;
            font-size: 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            position: relative;

            &>div {
              display: flex;
              width: 100%;
              position: absolute;

              span {
                display: inline-block;
                transform: scale(0.5);
                position: absolute;
              }
            }

            .project_name {
              top: 0;

              span {
                &:nth-child(1) {
                  left: -0.625rem;
                }
              }

              span {
                &:nth-child(2) {
                  width: 12.5rem;
                  left: 0.125rem;
                }
              }
            }

            .system_size {
              top: 0.75rem;

              span {
                &:nth-child(1) {
                  left: -1.125rem;
                }
              }

              span {
                &:nth-child(2) {
                  width: 12.5rem;
                  left: 0.875rem;
                }
              }
            }

            .module {
              top: 1.5rem;

              span {
                &:nth-child(1) {
                  left: -0.125rem;
                }
              }

              span {
                &:nth-child(2) {
                  width: 12.5rem;
                  left: -0.8125rem;
                }
              }
            }

            .location {
              top: 2.25rem;

              span {
                &:nth-child(1) {
                  left: -0.125rem;
                }
              }

              span {
                &:nth-child(2) {
                  width: 12.5rem;
                  left: -0.625rem;
                }
              }
            }

            .installed {
              top: 3rem;

              span {
                &:nth-child(1) {
                  left: -0.125rem;
                }
              }

              span {
                &:nth-child(2) {
                  width: 12.5rem;
                  left: -0.625rem;
                }
              }
            }
          }
        }
      }
    }
  }
}


.swiper-container-free-mode .swiper-wrapper {

  /* 默认是ease-out */
  -webkit-transition-timing-function: linear;

  -ms-transition-timing-function: linear;

  -moz-transition-timing-function: linear;

  -o-transition-timing-function: linear;

  transition-timing-function: linear;

}
</style>