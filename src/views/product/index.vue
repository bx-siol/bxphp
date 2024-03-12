<template>
    <div class="product">
        <MyTab></MyTab>
        <!--        <Nav>
            <template #left>&nbsp;</template>
            <template #title>
                <span v-if="categoryArr[0]">{{categoryArr[0].name}}</span>
            </template>
        </Nav>-->
        <div class="product_menu">
            <ul>
                <!--                <li :class="cid==0?'on':''" @click="onClickCat(0)">All</li>-->
                <li :style="'color: white !important;width:' + (100 / categoryArr.length) + '%;text-align: center;padding: 0.2rem 0.5rem !important;background: #0098a2 !important;border-radius: 0.5rem;margin-top: 0.5rem;line-height: 2rem;'"
                    :class="cid == item.id ? 'on' : ''" v-for="item in categoryArr" @click="onClickCat(item.id)">{{
                        item.name }}
                </li>
            </ul>
            <div style="height: 0.2rem;"></div>
            <!--            <div style="height: 0.2rem;"></div>
            <van-tabs v-model:active="active" color="#0098a2" @click-tab="onClickCat"  :ellipsis="false">
&lt;!&ndash;                <van-tab title="All categories" :name="0"></van-tab>&ndash;&gt;
                <van-tab v-for="item in categoryArr" :title="item.name" :name="item.id"></van-tab>
            </van-tabs>-->
        </div>
        <div class="product_wrap" style="top: 3.3rem;">
            <MyListBase :url="pageUrl" ref="pageRef" @success="onListSuccess" :auto-load="false">
                <template #default="{ list }">
                    <ul style="padding-bottom: 1rem;">
                        <li v-for="item in list" style="position: relative;">
                            <!--                            <van-image :src="imgFlag(vo)" class="pro_pic" v-for="vo in item.covers"></van-image>-->
                            <MySwiper :kv="getCovers(item.covers)" height="20rem"></MySwiper>
                            <div class="pro_name">
                                <p style="word-break:break-all;">
                                    <van-image :src="imgFlag(item.icon)"
                                        style="width: 1.2rem;height: 1.2rem;vertical-align: middle;border-radius: 3px;overflow: hidden;"
                                        fit="cover" class="pro_pic"></van-image>
                                    {{ item.name }}
                                </p>
                                <i class="hot" v-if="item.is_hot == 1" style="width: 2rem;">Hot</i>
                            </div>
                            <van-grid :column-num="3" class="pro_grid" :border="false">
                                <van-grid-item>
                                    <p class="nums"><span class="gold">{{ item.price }}</span></p>
                                    <p class="txt">{{ (t('价格')) }} RS</p>
                                </van-grid-item>
                                <van-grid-item>
                                    <p class="nums"><span class="gold">{{ item.rate }}</span></p>
                                    <p class="txt">{{ (t('日产量')) }} (%)</p>
                                </van-grid-item>
                                <van-grid-item>
                                    <p class="nums"><span class="gold">{{ item.days }}</span></p>
                                    <p class="txt">{{ t('周期') }} ({{ t('天') }})</p>
                                </van-grid-item>
                            </van-grid>
                            <div class="pro_info">
                                <!--                                <p>预期收益率：<span class="gold">0.62%</span></p>-->
                                <p>
                                    {{ t('限制数量') }}：
                                    <span class="gold" v-if="item.invest_limit > 0">{{ item.invest_limit }}</span>
                                    <span class="gold" v-else>Unlimited</span>
                                </p>
                                <!--                                <p>Repayment of principal and interest</p>-->
                                <p>{{ item.category_name }}</p>
                                <van-button v-if="item.status == 2" class="touziBtn"
                                    style="height: 2.5rem;width: 7rem;background: #0098a2;"
                                    @click="onLink({ name: 'Product_goods', params: { gsn: item.gsn } })">
                                    {{ t('预售') }}</van-button>
                                <van-button v-else class="touziBtn" style="height: 2.5rem;width: 7rem;background: #0098a2;"
                                    @click="onLink({ name: 'Product_goods', params: { gsn: item.gsn } })">
                                    {{ t('租') }}</van-button>
                            </div>
                            <div class="pro_speed" v-show="false">
                                <span>Schedule: </span>
                                <p class="jindu"><i :style="{ width: item.percent + '%' }"></i></p>
                                <span>{{ item.percent }}%</span>
                            </div>
                            <div class="saleOut" v-if="item.status == 9">{{ t('售罄') }}</div>
                        </li>
                    </ul>
                </template>
            </MyListBase>
            <div style="height: 1rem;"></div>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from "vue";
import { Button, Image, Grid, GridItem, Tabs, Tab } from "vant";
import MyTab from "../../components/Tab.vue";
import Nav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import MySwiper from '../../components/Swiper.vue';
import MyLoading from '../../components/Loading.vue';

export default defineComponent({
    name: "product",
    components: {
        MyTab, Nav, MyListBase, MySwiper, MyLoading,
        [Image.name]: Image,
        [Button.name]: Button,
        [Grid.name]: Grid,
        [GridItem.name]: GridItem,
        [Tabs.name]: Tabs,
        [Tab.name]: Tab,
    }
})
</script>
<script lang="ts" setup>
import {
    img_banner
} from '../../global/assets';
import { _alert, lang } from "../../global/common";
import { useRoute, useRouter } from "vue-router";
import http from "../../global/network/http";
import { getSrcUrl, goRoute } from "../../global/common";
import { ref } from "vue";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const onLink = (to: any) => {
    goRoute(to)
}

const active = ref(0)

const route = useRoute()
const router = useRouter()
const cid = ref(route.params.cid ? route.params.cid : 0)
const categoryArr = ref([])
const loadingShow = ref(true)

const onClickCat = (item: number) => {
    let id = item
    cid.value = id
    router.push({ name: 'Product', params: { cid: id } })
    loadingShow.value = true
    getList()
}

const pageRef = ref()
let pageUrl = ref('c=Product&a=list')
const tableData = ref<any>({})
const pdata = reactive({})

const getList = () => {
    pdata.cid = cid.value
    pageRef.value.doSearch(pdata)
}

const onListSuccess = (res: any) => {
    tableData.value = res.data
    loadingShow.value = false
}

const getCovers = (covers: any) => {
    let kv = []
    for (let i in covers) {
        kv.push({ cover: covers[i] })
    }
    return kv
}

onMounted(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Product&a=index'
        }).then((res: any) => {
            categoryArr.value = res.data.category_arr
            if (res.data.category_arr && res.data.category_arr.length > 0) {
                active.value = res.data.category_arr[0].id
                cid.value = active.value
            }
            getList()
        })
    }, delayTime)
})

</script>

<style>
.product_menu li {
    padding: 0.2rem 0rem;
    margin: 0 0.6rem;
    color: white !important;
}

.product_menu .van-tabs__nav {
    background-color: transparent !important;
    color: white;
    margin-top: 0rem;
}

.product_menu .van-tab {
    border-radius: 0.3rem;
    background-color: #4aadc2 !important;
    color: white;
    margin: 0 0.2rem;
    border: 2px solid #ffffff;
}

.product_menu .van-tab--active {
    color: #ffffff;
    background-color: #5081bb !important;
}

.product_menu .van-tabs__line {
    background-color: #0098a2;
    display: none;
}

.product_wrap .pro_grid .nums span {
    font-size: 1.5rem;
}

.product_menu li.on {
    border-bottom: 0.2rem solid #ff3c20 !important;
    color: #fff !important;
    font-weight: bold;
}

.product_wrap .saleOut {
    position: absolute;
    background-color: #0098a2;
    color: #ffffff;
    border: 0.2rem solid #0098a2;
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 3.5rem;
    z-index: 2;
    width: 90%;
    left: 5%;
    text-align: center;
    bottom: 13%;
}
</style>









<!--
    <template>
    <div class="product">
        <MyTab></MyTab>
        
        <div class="product_menu">
            <ul>
              
                <li :style="'color: white !important;width:'+ (100/categoryArr.length) +'%;text-align: center;padding: 0.2rem 0.5rem !important;background: #0098a2 !important;border-radius: 0.5rem;margin-top: 0.5rem;line-height: 2rem;'"
                    :class="cid==item.id?'on':''" v-for="item in categoryArr" @click="onClickCat(item.id)">{{item.name}}
                </li>
            </ul>
            <div style="height: 0.2rem;"></div>
             
        </div>
        <div class="product_wrap" style="top: 3.3rem;">
            <MyListBase :url="pageUrl" ref="pageRef" @success="onListSuccess" :auto-load="false">
                <template #default="{list}">
                    <ul style="padding-bottom: 1rem;">
                        <li v-for="item in list" style="position: relative; float: left;width: 50% !important;">
                           
                            <MySwiper :kv="getCovers(item.covers)" height="10rem"></MySwiper>
                            <div class="pro_name">
                                <p style="word-break:break-all;">
                                    <van-image :src="imgFlag(item.icon)"
                                        style="width: auto;height: 1.2rem;vertical-align: middle;border-radius: 3px;overflow: hidden;"
                                        fit="cover" class="pro_pic"></van-image>
                                    {{item.name}}
                                </p>
                                <i class="hot" v-if="item.is_hot==1" style="width: 2rem;">Hot</i>
                            </div>
                            <van-grid :column-num="3" class="pro_grid" :border="false">
                                <van-grid-item>
                                    <p class="nums"><span class="gold">{{item.price}}</span></p>
                                    <p class="txt">{{(t('价格'))}} RS</p>
                                </van-grid-item>
                                <van-grid-item>
                                    <p class="nums"><span class="gold">{{item.rate}}</span></p>
                                    <p class="txt">{{(t('日产量'))}} (%)</p>
                                </van-grid-item>
                                <van-grid-item>
                                    <p class="nums"><span class="gold">{{item.days}}</span></p>
                                    <p class="txt">{{t('周期')}} ({{t('天')}})</p>
                                </van-grid-item>
                            </van-grid>
                            <div class="pro_info">
                               
                                <p>
                                    {{t('限制数量')}}：
                                    <span class="gold" v-if="item.invest_limit>0">{{item.invest_limit}}</span>
                                    <span class="gold" v-else>Unlimited</span>
                                </p>
                              
                                <p>{{item.category_name}}</p>
                                <p>
                                    <van-button v-if="item.status==2" class="touziBtn"
                                        style="height: 2.5rem;width: 7rem;background: #0098a2;"
                                        @click="onLink({name:'Product_goods',params:{gsn:item.gsn}})">
                                        {{t('预售')}}</van-button>
                                    <van-button v-else class="touziBtn"
                                        style="height: 2.5rem;width: 7rem;background: #0098a2;"
                                        @click="onLink({name:'Product_goods',params:{gsn:item.gsn}})">
                                        {{t('租')}}</van-button>
                                </p>
                            </div>
                            <div class="pro_speed" v-show="false">
                                <span>Schedule: </span>
                                <p class="jindu"><i :style="{width:item.percent+'%'}"></i></p>
                                <span>{{item.percent}}%</span>
                            </div>
                            <div class="saleOut" v-if="item.status==9">{{t('售罄')}}</div>
                        </li>
                    </ul>
                </template>
            </MyListBase>
            <div style="height: 1rem;"></div>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from "vue";
import { Button, Image, Grid, GridItem, Tabs, Tab } from "vant";
import MyTab from "../../components/Tab.vue";
import Nav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import MySwiper from '../../components/Swiper.vue';
import MyLoading from '../../components/Loading.vue';

export default defineComponent({
    name: "product",
    components: {
        MyTab, Nav, MyListBase, MySwiper, MyLoading,
        [Image.name]: Image,
        [Button.name]: Button,
        [Grid.name]: Grid,
        [GridItem.name]: GridItem,
        [Tabs.name]: Tabs,
        [Tab.name]: Tab,
    }
})
</script>
<script lang="ts" setup>
import {
    img_banner
} from '../../global/assets';
import { _alert, lang } from "../../global/common";
import { useRoute, useRouter } from "vue-router";
import http from "../../global/network/http";
import { getSrcUrl, goRoute } from "../../global/common";
import { ref } from "vue";

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const onLink = (to: any) => {
    goRoute(to)
}

const active = ref(0)

const route = useRoute()
const router = useRouter()
const cid = ref(route.params.cid ? route.params.cid : 0)
const categoryArr = ref([])
const loadingShow = ref(true)

const onClickCat = (item: number) => {
    let id = item
    cid.value = id
    router.push({ name: 'Product', params: { cid: id } })
    loadingShow.value = true
    getList()
}

const pageRef = ref()
let pageUrl = ref('c=Product&a=list')
const tableData = ref<any>({})
const pdata = reactive({})

const getList = () => {
    pdata.cid = cid.value
    pageRef.value.doSearch(pdata)
}

const onListSuccess = (res: any) => {
    tableData.value = res.data
    loadingShow.value = false
}

const getCovers = (covers: any) => {
    let kv = []
    for (let i in covers) {
        kv.push({ cover: covers[i] })
    }
    return kv
}

onMounted(() => {
    http({
        url: 'c=Product&a=index'
    }).then((res: any) => {
        categoryArr.value = res.data.category_arr
        if (res.data.category_arr && res.data.category_arr.length > 0) {
            active.value = res.data.category_arr[0].id
            cid.value = active.value
        }
        getList()
    })
})

</script>

<style>
li:nth-of-type(odd) {
    clear: both;
}

.product_menu li {
    padding: 0.2rem 0rem;
    margin: 0 0.6rem;
    color: white !important;
}

.product_menu .van-tabs__nav {
    background-color: transparent !important;
    color: white;
    margin-top: 0rem;
}

.product_menu .van-tab {
    border-radius: 0.3rem;
    background-color: #4aadc2 !important;
    color: white;
    margin: 0 0.2rem;
    border: 2px solid #ffffff;
}

.product_menu .van-tab--active {
    color: #ffffff;
    background-color: #5081bb !important;
}

.product_menu .van-tabs__line {
    background-color: #0098a2;
    display: none;
}

.product_wrap .pro_grid .nums span {
    font-size: 1.5rem;
}

.product_menu li.on {
    border-bottom: 0.2rem solid #ff3c20 !important;
    color: #fff !important;
    font-weight: bold;
}

.product_wrap .saleOut {
    position: absolute;
    background-color: #0098a2;
    color: #ffffff;
    border: 0.2rem solid #0098a2;
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 3.5rem;
    z-index: 2;
    width: 90%;
    left: 5%;
    text-align: center;
    bottom: 13%;
}
</style>
-->