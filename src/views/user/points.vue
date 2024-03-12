<template>
    <div class="PointsMall">
        <!-- <MyNav leftText='' style="background-color:rgb(255, 99, 22);"></MyNav> -->
        <div class="mall-top">
            <div class="return">
                <van-icon @click="linkback()" style="color:#fff;margin-left: 1rem;" name="arrow-left" />
                <p>Points Mall</p>
            </div>
        </div>
        <div class="content">
            <div class="left">
                <img :src="imgFlag(user.headimgurl)">
                <span>{{ user.account }}</span>
            </div>
            <h1>{{ wallet3.balance }}</h1>
            <div class="record">
                <img src="../../assets/rs.png">
                <p>Points redemption record</p>
                <button type="reset" @click="Details">Details</button>
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
                            <p>Total revenue</p><span style="color:#000;"> {{ cutOutNum(((item.rate / 100) * item.price) *
                                item.days, 2) }} RS</span>
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
import rs from "../../assets/rs.png";  //   ../../assets/pointsbg.png
const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}
const router = useRouter()
const tableData = ref<any>({})

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
    }, delayTime),

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
            tableData.value = res.data.list
        })
    }, delayTime)
});
</script>
<style scoped>
.mall-top {
    /* background-image: url('../../assets/pointsbg.png'); */
    background-color: #64523e;
    height: 22vh;
    background-repeat: no-repeat;
    background-size: 100% 100%;
    color: #fff;
    font-weight: bold;

}

.return {
    padding-top: 18px;
    display: flex;
}

.return span {
    margin-left: 8px;
    vertical-align: top;
}

.return p {
    margin: 0 auto;
}

.content {
    height: 180px;
    width: 90%;
    margin: 0 auto;
    padding: 5px;
    box-sizing: border-box;
    box-shadow: 0px 0px 22px 4px rgb(0 0 0 / 60%);
    border-radius: 10px;
    background: linear-gradient(to right, #c49b6c 20%, #a77d52);
    position: relative;
    top: -7.6em;
    color: #fff;
}

.content img {
    width: 36px;
    margin-right: 10px;
    vertical-align: middle;
    display: inline-block;
    border-radius: 8rem;
}

.content .left {
    margin: 10px 0 0 8px;
}

.content span {
    font: bold 14px/36px'微软雅黑';
}

.content h1 {
    text-align: center;
    font-size: 3rem;
    margin-top: 0.5rem;
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
    position: relative;
    bottom: 100px;
    text-align: center;
    padding: 16px;
}

.mall-bottom .part {
    display: inline-block;
    width: 142px;
    border-bottom: 3px solid #64523e;
    padding-bottom: 3px;
    margin-bottom: 15px;
    color: #64523e;
    font-weight: bold;
}

.mall-bottom .product {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}

.mall-bottom .news {
    width: 40%;
    height: 80%;
    padding: 12px;
    margin: 0 auto;
    margin-bottom: 24px;
    border-radius: 10px;
    background-color: #fff;
    box-shadow: 0px 0px 12px 2px rgb(225, 225, 225);
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