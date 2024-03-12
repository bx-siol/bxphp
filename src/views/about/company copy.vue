<template>
  <div class="aboutus_company">
      <MyNav leftText=''></MyNav>
      <div class="aboutus_company_wrapper">
        <div class="para_list">
          <div class="para_item" v-for="(item, index) in paraList" :key="index">
            <div class="title">
              {{ item.title }}
            </div>
            <div class="content">
              <img :src="item.img">
              <div class="content_text">
                {{ item.content }}
              </div>
            </div>
          </div>
        </div>
      </div>
    <MyTab></MyTab>
  </div>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import MyNav from "../../components/Nav.vue";
import MyTab from "../../components/Tab.vue";
import AboutUsLogo from '../../assets/img/about/aboutus_logo.png'
import Award from '../../assets/img/about/award.png'
import Result from '../../assets/img/about/result.png'

type paraItem = {
  title: string,
  img: string,
  content: string
}
const paraList = ref<Array<paraItem>>([
  { title: 'Canadian Solar', img: AboutUsLogo, content: 'Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.'  },
  { title: 'Honorary awards', img: Award, content: 'Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.'  },
  { title: 'R & D strength', img: Result, content: 'Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.'  },
])

</script>
<style lang="scss" scoped>
  .aboutus_company {
    height: 100%;
    box-sizing: border-box;
    .aboutus_company_wrapper {
      height: calc(100% - 6rem);
      padding: 0 1.25rem;
      overflow: auto;
      .para_list {
        padding-bottom: 3.125rem;
        .para_item {
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          margin-top: 1.5625rem;
          .title {
            font-weight: bold;
            color: #bd312d;
            font-size: 0.875rem;
          }
          .content {
            .content_text {
              line-height: 1.2rem;
              word-break: break-all;
              font-size: 0.75rem;
            }
          }
          &:nth-child(2) {
            .content {
              margin-top: 1rem;
              img {
                width: 6.5rem;
                height: 6.5rem;
                float: left;
                margin-right: 0.625rem;
              }
            }
          }
          &:nth-child(3) {
            .content {
              img {
                margin-top: -5rem;
              }
              .content_text {
                margin-top: -4.6875rem;
              }
            }
          }
        }
      }
    }
  }
</style>  