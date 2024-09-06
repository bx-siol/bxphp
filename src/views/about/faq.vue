<template>
  <div class="FAQ">
    <MyNav leftText=''></MyNav>
    <div class="FAQWrapper">
      <div class="faq_list">
        <div class="faq_list_item" v-for="(item, index) in faqList" :key="index">
          <div class="faq_list_title">
            <span class="index_number">{{ index + 1 + '.' }}</span>
            <span class="">{{ item.title }}</span>
          </div>
          <div class="faq_list_content">
            <span class="">{{ item.content }}</span>
          </div>
        </div>
        <div class="faq_list_content">
          <span>To ensure compliance with business regulations and rules, CSIQ will comply with the National Tax Policy
            of India. Investor income is subject to 6% income tax and will be reported to the relevant
            authorities.</span>
        </div>
      </div>
    </div>
  </div>
  <MyTab></MyTab>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import { useRoute, useRouter } from "vue-router"
import MyNav from "../../components/Nav.vue";
import MyTab from "../../components/Tab.vue";

type faq = {
  title: string,
  content: string
}
const faqList = ref<Array<faq>>([
  { title: 'What is Canadian Solar\'s ticker symbol, stok exchange , and CUSIP?', content: 'Canadian Solar ticker symbol is CSIQ, its shares trade on the NASDAQ; the CUSIP number is 136635109.' },
  { title: 'When was Canadian Solar founded ?', content: 'Canadian Solar was founded by Dr.Shawn Qu, Chairman and CEO of the Company in Ontario, Canada in 2001.' },
  { title: 'How many of the Company\'s shares are outstanding?', content: 'As of December 31, 2021, Canadian Solar has 63,470,059 ordinary shares issued and outstanding.' },
  { title: 'How many employees does Canadian Solar have?', content: 'As of December 31, 2021, Canadian Solar had over 13,124 full-time employees.' },

  { title: 'What should I do if I forget my password?', content: 'If you forget your password, please contact your manager to retrieve it for you.' },
 
  { title: 'What is the commission rate for CSIQ members?', content: 'Grade A 10 %, Grade B 5 %, Grade C 2 %' },
  { title: 'About tax?', content: 'Withdrawal tax rules:' },
  
])
</script>
<style lang="scss" scoped>
.FAQ {
  height: 100%;
  box-sizing: border-box;

  .FAQWrapper {
    height: calc(100% - 6rem);
    padding: 0 1rem 0;
    box-sizing: border-box;

    .faq_list {
      .faq_list_item {
        margin-top: 1.25rem;

        .faq_list_title {
          font-weight: bold;
          font-size: 0.875rem;

          .index_number {
            margin-right: 0.25rem;
          }
        }

        .faq_list_content {
          font-size: 0.75rem;
          margin-top: 0.5rem;
          line-height: 1rem;
        }
      }
    }
  }
}
</style>