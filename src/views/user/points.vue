<template>
    <div class="PointsMall">
        <MyNav leftText=''>
            <template #left>
                <div></div>
            </template>
        </MyNav>
        <div v-if="false">
            <div class="content">
            <div class="left">
                <h1>{{ wallet3.balance }}</h1>
                <img :src="Pointsbi">
            </div>
        </div>
        <div class="mall-bottom">
            <span class="part">Points product</span>
            <div class="product">
                <div v-for="(item, idx) in tableData" class="news">
                    <img :src="imgFlag(item.icon)">
                    <h2>{{ item.name }}</h2>
                    <ul>
                        <li>
                            <p>Points redemption</p><span>{{ item.price }}</span>
                        </li>
                        <li>
                            <p>Daily earnings</p><span> {{ cutOutNum((item.rate / 100) * item.price, 2) }} RS</span>
                        </li>
                        <li>
                            <p>Total revenue</p><span style="color:#000;"> {{ cutOutNum(((item.rate / 100) * item.price)
                                * item.days, 2) }} RS</span>
                        </li>
                        <li>
                            <p>The Time</p><span style="color:#000;">{{ item.days }} Days</span>
                        </li>

                        <li v-if="item.kc > 0">
                            <p>Stock</p><span style="color:#000;">{{ item.kc }} </span>
                        </li>

                    </ul>
                    <button @click="getProjectDetail(item)" type="button">Exchange</button>
                </div>

            </div>
        </div>
        </div>

        <MyTab></MyTab>
    </div>
</template>
<script lang="ts">
//import { alert, lang } from "../../global/common";
import { defineComponent, ref, reactive, onMounted } from 'vue';
import { Button, Form, Field, CellGroup } from 'vant';
import { Icon } from 'vant';
import { _alert, lang, getSrcUrl, goRoute, cutOutNum } from "../../global/common";
import MyNav from "../../components/Nav.vue";
import MyTab from "../../components/Tab.vue";
import http from "../../global/network/http";
import { useRouter } from "vue-router";
export default defineComponent({
    components: {
        MyNav,
        [Button.name]: Button,
        [Form.name]: Form,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Icon.name]: Icon,

    },
})

</script>

<script lang="ts" setup>
import Pointsbi from "../../assets/index/Pointsbi.png";
const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}
const router = useRouter()
// const tableData = ref<any>({})
const tableData = ref([
    {
        icon: 'path/to/your/icon1.png',
        title: 'Project A',
        pointsRedemption: '100 Points',
        dailyEarnings: '5',
        rate: 10, // Assume a 10% rate
        price: 1000, // Assume a price of 1000
        days: 30, // Total days
        kc: 50 // Stock
    },
    // Add more items as needed...
])
const Details = () => {
    router.push({ path: '/balancelog/1019' })
}
const linkback = () => {
    router.push({ path: '/' })
}
const getProjectDetail = (item: any) => {
    router.push({ name: 'Project_detail', params: { pid: item.gsn } })
}
const user = ref({})
const wallet3 = ref({})
onMounted(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=User&a=index'
        }).then((res: any) => {
            user.value = res.data.user
            wallet3.value = res.data.wallet3

        })
    }, delayTime)

    setTimeout(() => {
        http({
            url: 'c=Product&a=list&cid=1019',
        }).then((res: any) => {
            if (res.code != 1) {
                _alert({
                    type: 'error',
                    message: res.msg,
                    onClose: () => {
                        router.go(-1)
                    }
                })
                return
            }
            // tableData.value = res.data.list
        })
    }, delayTime)
});
</script>
<style scoped>
.PointsMall {
    background: #84973b;
    height: 100%;
    padding: 0 1rem;
}

.content {
    height: 8.65rem;
    margin: 0 auto;
    padding: 1rem;
    box-sizing: border-box;
    border-radius: 10px;
    background: rgb(255, 247, 225);
    display: flex;
    align-items: center;
}

.content .left {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex: 1;
}

.content img {
    width: 6.8rem;
    vertical-align: middle;
    display: inline-block;
    border-radius: 8rem;
}

.content h1 {
    text-align: center;
    font-size: 2.5rem;
    margin-top: 0.5rem;
    color: rgb(255, 136, 12);
}

.record {
    margin-top: 1rem;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.record img {
    width: 20px;
}

.record p {
    display: inline-block;
    font: 14px/16px '微软雅黑';
}

.record button {
    width: 70px;
    height: 18px;
    border-radius: 10px;
    margin-left: 1.675rem;
    box-shadow: 0px 3px 3px 0px rgb(0 0 0 / 24%);
    border: 1px solid #f06404;
    background: linear-gradient(to top, #ED1A1F, #FFC10E);
    font: 12px/14px '微软雅黑';
}

.mall-bottom {
    bottom: 100px;
    text-align: center;
}

.mall-bottom .part {
    display: inline-block;
    width: 142px;
    border-left: 3px solid rgb(245, 159, 54);
    padding-left: 0.4rem;
    margin: 1rem 0;
    color: rgb(245, 159, 54);
    font-weight: bold;
    display: flex;
}

.mall-bottom .product {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    background: #84973b;
}

.mall-bottom .news {
    width: 40%;
    height: 80%;
    padding: 12px;
    margin: 0 auto;
    margin-bottom: 24px;
    border-radius: 10px;
    background-color: #fff;
    text-align: center;
}

.mall-bottom .news img {
    width: 96%;
    margin: 4px auto;
}

.mall-bottom .news h2 {
    font: 18px/30px '微软雅黑';
    color: #64523e;
    margin: 10px auto;
    font-weight: bold;

}

.mall-bottom .news li {
    text-align: left;
    font: 12px/18px '微软雅黑';
    color: #000;
}

.mall-bottom .news p {
    display: inline-block;
}

.mall-bottom .news span {
    float: right;
    font: 12px/18px '微软雅黑';
    color: rgb(242, 100, 34);
}

.mall-bottom button {
    width: 92%;
    height: 30px;
    font: bold 16px/24px '微软雅黑';
    border-radius: 20px;
    color: #fff;
    background: linear-gradient(to right, #c49b6c 20%, #a77d52);
    border: none;
    margin-top: 16px;
}
</style>