<template>
    <div class="PointsMall">
        <MyNav leftText=''>
            <template #left>
                <div></div>
            </template>
        </MyNav>

        <div class="content">
            <div class="left">
                <h1>{{ wallet3.balance }}</h1>
                <img :src="Pointsbi">
            </div>
        </div>

        <div class="exchange">
            <van-field v-model="dataForm.points" class="fieldbox"
                placeholder="Exchange amount for points (10 Points = 1 Rupee)">
                <template #button>
                    <van-button size="small" type="primary" color="#ff880c" @click="exchangePoints"
                        style="border-radius:8px;padding: 1rem 0.6rem;height: 3rem;">exchange</van-button>
                </template>
            </van-field>
        </div>
        <div class="mall-bottom">
            <span class="part">Points product</span>
            <div class="product">
                <div v-for="(item, idx) in tableData" class="news">
                    <div class="imgs">
                        <img :src="imgFlag(item.icon)">
                    </div>
                    <div style="padding: 0 0.4rem 0.45rem;">
                        <h2>{{ item.name }}</h2>
                        <ul>
                            <li>
                                <p>Points redemption</p><span>{{ item.price }}</span>
                            </li>
                            <li>
                                <p>Revenue days</p><span>{{ item.days }} Days</span>
                            </li>
                            <li>
                                <p>Daily earnings</p><span> {{ cutOutNum((item.rate / 100) * item.price, 2) }} RS</span>
                            </li>
                            <li>
                                <p>Total revenue</p><span> {{ cutOutNum(((item.rate / 100) * item.price) * item.days, 2) }}
                                    RS</span>
                            </li>

                            <li v-if="false">
                                <p>Stock</p><span style="color:#000;">{{ item.kc }} </span>
                            </li>

                        </ul>
                        <div class="obtain">
                            <p>{{ item.price }} points</p>
                            <button @click="getProjectDetail(item)" type="button">+</button>
                        </div>

                    </div>

                </div>

            </div>
        </div>


        <MyTab></MyTab>
    </div>
</template>
<script lang="ts">
//import { alert, lang } from "../../global/common";
import { defineComponent, ref, reactive, onMounted, computed } from 'vue';
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
import Goldenegg from "../../assets/index/Goldenegg.png";

const router = useRouter()

const amount = ref('');
const user = ref({})
const wallet3 = ref({})
const tableData = ref<any>({})


const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const getProjectDetail = (item: any) => {
    router.push({ name: 'Project_detail', params: { pid: item.gsn } })
}



const dataForm = reactive({
    points: ''
})


const exchangePoints = () => {
    
    http({
        url: 'c=Product&a=transforms?m='+dataForm.points,
        // data: {
        //     m: dataForm.points,
        // }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        };
        _alert(res.msg)

    })
};



onMounted(() => {
    http({
        url: 'c=User&a=index'
    }).then((res: any) => {
        user.value = res.data.user
        wallet3.value = res.data.wallet3

    })

    http({
        url: 'c=Product&a=list&type=pointshop',

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
});
</script>
<style lang="scss" scoped>
.PointsMall {
    background: #84973b;
    min-height: 100%;
    padding: 0 1rem;

    .content {
        height: 8.65rem;
        margin: 0 auto;
        padding: 1rem;
        box-sizing: border-box;
        border-radius: 10px;
        background: #fff7e1;
        display: flex;
        align-items: center;

        .left {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex: 1;
        }

        img {
            width: 6.8rem;
            vertical-align: middle;
            display: inline-block;
            border-radius: 8rem;
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-top: 0.5rem;
            color: rgb(255, 136, 12);
        }
    }

    .exchange {
        margin-top: 1rem;

        .van-cell {
            padding: 0;
            padding-left: 0.5rem;
            border-radius: 8px;
        }

        :deep(.van-field__control::-webkit-input-placeholder) {
            // width: 5rem;
            font-size: 10px;
        }
    }


    .mall-bottom {
        padding-bottom: 4rem;

        .part {
            width: 142px;
            border-left: 3px solid rgb(245, 159, 54);
            padding-left: 0.4rem;
            margin: 1rem 0;
            color: rgb(245, 159, 54);
            font-weight: bold;
            display: flex;
        }

        .product {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            background: #84973b;

            .news {
                width: 48%;
                height: 80%;
                // padding: 10px 6px;

                margin-bottom: 16px;
                border-radius: 10px;
                background-color: #fff;
                text-align: center;

                .imgs {
                    background: #fff7e1;
                    border-radius: 8px 8px 0 0;
                    display: flex;
                    justify-content: center;

                    img {
                        width: 10rem;
                        height: 8.8rem;
                        border-radius: 8px 8px 0 0;
                    }

                }

                h2 {
                    font: 16px/24px '微软雅黑';
                    color: #002544;
                    margin: 4px 0;
                    font-weight: bold;
                    text-align: left;
                }

                li {
                    text-align: left;
                    font: 0.675rem/16px '微软雅黑';
                    color: #000;
                    display: flex;
                    justify-content: space-between;

                    p {
                        display: inline-block;
                        color: #aaa;
                    }

                    span {
                        font: bold 0.625rem/16px '微软雅黑';
                        color: #84973b;
                    }
                }

                .obtain {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-top: 0.2rem;

                    p {
                        color: #002544;
                        font-size: 16px;
                        font-weight: bold;
                    }

                    button {
                        width: 20%;
                        height: 30px;
                        font-size: 26px;
                        border-radius: 4px;
                        color: #fff;
                        background: #84973b;
                        border: none;
                    }
                }

            }

        }

    }

}
</style>