<template>
  <div class="about">
    <MyNav leftText=''></MyNav>
    <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">


      <template #default="{ list }">


        <div class="aboutWrapper">
          <div style="    margin-top: 0.5rem;" v-for="item in list"
            @click="onLink({ name: 'About_company', params: { id: item.id } })">
            <div class="logo">
              <img  :src="imgFlag(item.cover)" class="logoImg">
            </div>
            <router-link class="aboutCanadianSolar" :to="{ name: 'About_company', params: { id: item.id } }">
              <span>{{ item.title }}</span>
            </router-link>
          </div>

          <div class="logo">
            <img :src="AboutUsLogo" class="logoImg">
          </div>
          <router-link class="aboutCanadianSolar" :to="{ name: 'About_company', params: { id: 0 } }">
            <span>About Canadian Solar</span>
          </router-link>
          <div class="faq">
            <img :src="FAQImg" class="faqImg">
          </div>
          <div class="faqText" @click="goFAQ">
            <span>FAQ</span>
          </div>
        </div>
      </template>
    </MyListBase>



    <!--  <div class="aboutWrapper">
      <div class="logo">
        <img :src="AboutUsLogo" class="logoImg">
      </div>
      <div class="aboutCanadianSolar" @click="goAboutUsCompany">
        <span>About Canadian Solar</span>
      </div>
      <div class="faq">
        <img :src="FAQImg" class="faqImg">
      </div>
      <div class="faqText" @click="goFAQ">
        <span>FAQ</span>
      </div>
    </div> -->

    <MyTab></MyTab>
  </div>
</template>
<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import { Image } from "vant";
import MyNav from "../../components/Nav.vue";
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';

export default defineComponent({
  name: "news",
  components: {
    MyNav, MyListBase, MyLoading,
    [Image.name]: Image,
  }
})
</script>
<script lang="ts" setup>

import { useRoute, useRouter } from "vue-router"
import MyTab from "../../components/Tab.vue";
// import AboutUsLogo from '../../assets/img/about/aboutus_logo.png'
// import FAQImg from '../../assets/img/about/faq.png'
import { getSrcUrl, goRoute } from "../../global/common";

const onLink = (to: any) => {
  goRoute(to)
}

const imgFlag = (src: string) => {
  return getSrcUrl(src, 1)
}

const pageRef = ref()
const pageUrl = ref('c=News&a=list&s_cid=50')
const router = useRouter()

const goAboutUsCompany = (): void => {
  router.push({ path: '/about/company', params: { id: 0 } })
}
const goFAQ = (): void => {
  router.push({ path: '/about/FAQ' })
}
</script>
<style lang="scss" scoped>
.about {
  height: 100%;
  box-sizing: border-box;

  .aboutWrapper {
    height: calc(100% - 6rem);
    box-sizing: border-box;
    padding: 0.625rem 0.5rem;
    box-sizing: border-box;
    margin-bottom: 5rem;

    .aboutCanadianSolar,
    .faqText {
      width: 100%;
      height: 1.625rem;
      justify-content: center;
      display: flex;
      align-items: center;
      font-size: 0.75rem;
      background: #bd312d;
      color: #fff;
      margin-top: 1rem;
      border-radius: 0.5rem;
    }

    .faqText {
      position: relative;
      z-index: 0;
      margin-top: -1.25rem;
    }
  }
}
</style>