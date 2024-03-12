<template>
  <div class="aboutus_company">
    <MyNav leftText=''></MyNav>
    <div class="aboutus_company_wrapper">
      <div class="para_list">
        <div v-if="ishttp" class="para_item" v-for="(item, index) in paraList" :key="index">
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



        <div v-else class="para_item">
          <div class="title">
            {{ info.title }}
          </div>
          <div class="content">
            <div v-html="info.content" class="content_text">

            </div>
          </div>
        </div>


      </div>
    </div>
    <MyTab :key="menuKey"></MyTab>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted } from "vue";
import { Image } from "vant";


export default defineComponent({
  name: "newsDet",
  components: {
    MyNav,
    [Image.name]: Image,
  }
})
</script>
<script lang="ts" setup>
import { ref } from 'vue'
import MyNav from "../../components/Nav.vue";
import MyTab from "../../components/Tab.vue";
import AboutUsLogo from '../../assets/img/about/aboutus_logo.png'
import Award from '../../assets/img/about/award.png'
import Result from '../../assets/img/about/result.png'
import { useRoute, useRouter } from "vue-router"
import { useStore } from "vuex";
import http from "../../global/network/http";
import { goRoute } from "../../global/common";

type paraItem = {
  title: string,
  img: string,
  content: string
}


const paraList = ref<Array<paraItem>>([
  {
    title: 'Canadian Solar',
    img: AboutUsLogo,
    content: 'Canadian Solar was founded in 2001 in Canada and is one of the world’s largest solar technology and renewable energy companies. It is a leading manufacturer of solar photovoltaic modules, provider of solar energy and battery storage solutions, and developer of utility-scale solar power and battery storage projects with a geographically diversified pipeline in various stages of development. Over the past 21 years, Canadian Solar has successfully delivered around 71 GW of premium-quality, solar photovoltaic modules to customers across the world. Likewise, since entering the project development business in 2010, Canadian Solar has developed, built and connected over 6.6 GWp in over 20 countries across the world. Currently, the Company has 800 MWp of projects in operation, 5.3 GWp of projects under construction or in backlog (late-stage), and an additional 18.5 GWp of projects in pipeline (mid- to early- stage). Canadian Solar is one of the most bankable companies in the solar and renewable energy industry, having been publicly listed on the NASDAQ since 2006.'
  },
  {
    title: 'Honorary awards',
    img: Award,
    content: `In 2021, Canadian Solar ranked among the top 500 global energy (group) companies for the eleventh consecutive year
              In 2020, Canadian Solar ranks first in Bloomberg New Energy Finance's list of the world's most financing value module brands
              From 2017 to 2020, Canadian Solar was rated as the first echelon supplier of photovoltaic modules by Bloomberg New Energy Finance
              Canadian Solar won the Best Structured Financing Award from Environmental Finance in 2018
              In 2017, Canadian Solar ranked the first echelon of PHOTON Consulting's "Global Photovoltaic Enterprise "Triathlon"
              In 2017, Canadian Solar was selected as the world's first developer of crystalline silicon solar power plants by Greentech Media Research`
  },
  {
    title: 'R & D strength',
    img: Result,
    content: 'Canadian Solar has been focusing on improving the performance and reliability of module products, insisting on independent research and development and innovation, and has mastered the core technology system with independent intellectual property rights, including large-size silicon wafer technology, high-efficiency monocrystalline PERC technology, and N-type HJT cell technology , Multi-busbar + half-cell battery technology, double-sided battery and double-glass module technology, photovoltaic grid-connected inverter technology, etc. As of June 30, 2021, Canadian Solar has 2,056 major patents in force, including 255 invention patents. The number of patents is in the forefront of the global photovoltaic industry, covering silicon wafer processes and methods, cell structures, processes and methods, components Structures and methods and related equipment and other fields. Canadian Solar provides customers with continuously upgraded technical support through continuous innovation, so as to meet the needs of the continuous development of the market.'
  },
])
const menuKey = ref(0)
const route = useRoute()
const ishttp = ref(true)
if (route.params.id != '0') {
  ishttp.value = false;
}
const router = useRouter()

const onLink = (to: any) => {
  goRoute(to)
}

const info = ref<any>({})
const others = ref({
  pre: {},
  next: {}
})
const store = useStore()
const tdata = ref<any>({
  category_arr: []
})

const getNewsInfo = (id: number) => {
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
    if (id == 0) { return; }
    http({
    url: 'c=News&a=info',
    data: { id: id }
    }).then((res: any) => {
    if (res.code != 1) {
      router.go(-1)
      return
    }
    info.value = res.data.info
    others.value.pre = res.data.pre
    others.value.next = res.data.next
    document.title = info.value.title
    })
  }, delayTime)
}

onMounted(() => {
  var idstr = ',' + route.params.id + ',';
  if (localStorage.rnewsids != undefined) {
    if (localStorage.rnewsids.indexOf(idstr) < 0) {
      localStorage.rnewsids = localStorage.rnewsids + route.params.id + ','
    }
  } else {
    localStorage.rnewsids = idstr
  }

  if (localStorage.rnewsids != undefined) {
    var ac = {};
    for (var i = 0; i < localStorage.rnewsids.length; i++) {
      var chars = localStorage.rnewsids.charAt(i);
      if (ac[chars]) {
        ac[chars]++;
      } else {
        ac[chars] = 1;
      }
    }
    var ckcount = ac[','] - 1;
    ckcount = localStorage.newscount - ckcount;
    if (ckcount <= 0) ckcount = '';
    store.commit('setnewscountc', ckcount);
    menuKey.value++;
  }
  // route.params.id
  // if(){

  // }
  getNewsInfo(route.params.id)
})
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