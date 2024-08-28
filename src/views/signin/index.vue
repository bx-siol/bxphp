<template>
    <div class="signinMall">
        <MyNav leftText="">
            <template #right>
            </template>
        </MyNav>
        <div class="sigtent">
            <div class="sigbtn">
                <img :src="signinbtn" @click="signIn">
                <p v-if="pageData!=null">You have signed in for {{ pageData.consecutiveDays }} consecutive days please continue to work hard!</p>
            </div>
            <div class="sigcash">
                <div class="cash">
                    <h1 v-if="pageData!=null">{{ pageData.totalAward }}</h1>
                    <div style="width: 34%;"><img :src="cash">Cash</div>
                </div>
                <span></span>
                <div class="cash">
                    <h1 v-if="pageData!=null">{{ pageData.totalDays }}</h1>
                    <div style="width: 68%;"><img :src="days">Check-in days</div>
                </div>
            </div>
            <div class="calendar">
                <div class="head">
                    <div class="left"><span class="prevM" @click.stop="changeM('prev')">◀</span></div>
                    <!-- <span class="prevY" @click="changeY('prev')">《</span> -->
                    <div class="title">{{ fullDate }}</div>
                    <!-- <span class="nextY" @click.stop="changeY('next')">》</span> -->
                    <div class="right"><span class="nextM" @click="changeM('next')">▶</span></div>
                </div>
                <div class="week">
                    <ul>
                        <li>Sun</li>
                        <li>Mon</li>
                        <li>Tues</li>
                        <li>Wed</li>
                        <li>Thurs</li>
                        <li>Fri</li>
                        <li>Sat</li>
                    </ul>
                    <div class="list">
                        <template v-for="item in state.dataCount" :key="item">
                            <div class="block">
                                <div class="dot" v-if="isShowDot(item)" :class="isCurrentDate(item) ? 'active' : ''">{{ filteredDate(item) }} </div>
                                <div v-else :class="isCurrentDate(item) ? 'active' : ''">{{ filteredDate(item) }} </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="earn" @click="onLink({name:'task'})"></div>
            <div class="illustrate">
                <div>CHECK-IN RULES</div>
                <p><img :src="prefix"> Daily check-in 1-8RS</p>
            </div>
        </div>
        <!-- <MyTab></MyTab> -->
    </div>
    <MyLoading :show="loadingShow" title="Submit"></MyLoading>
</template>
<script lang="ts">
    //import { alert, lang } from "../../global/common";
    import { defineComponent, ref, reactive, onMounted, computed, toRaw, toRefs } from 'vue'
    import { Button, Form, Field, CellGroup } from 'vant'
    import { Icon, Calendar } from 'vant'
    import { _alert, lang, getSrcUrl, goRoute, cutOutNum } from '../../global/common'
    import MyNav from '../../components/Nav.vue'
    import MyTab from '../../components/Tab.vue'
    import MyLoading from "../../components/Loading.vue";
    import http from '../../global/network/http'
    import { useRouter } from 'vue-router'

    export default defineComponent({
        components: {
            MyNav,
            MyLoading,
            [Button.name]: Button,
            [Form.name]: Form,
            [Field.name]: Field,
            [CellGroup.name]: CellGroup,
            [Icon.name]: Icon,
        },
    })
</script>
  
<script lang="ts" setup>
    import rs from '/../assets/rs.png' //   ../../assets/pointsbg.png
    import cart from '../assets/ico/cart.png'
    import Product from '../../assets/img/project/product.png';
    import signinbtn from '../../assets/img/user/signinbtn.png';
    import cash from '../../assets/img/user/cash.png';
    import days from '../../assets/img/user/days.png';
    import prefix from '../../assets/img/user/prefix.png';
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();
    let isRequest = false;

    const pageData = ref(null);
    const state = reactive({
        dataCount: [],
        //得到当前日期
        curYear: 0,
        curMonth: 0,
        curDate: 0,
        signInList: ['20230921']
    })


    let currentYEAR: number | null = null
    let currentMONTH: number | null = null
    const loadingShow = ref(false);

    onMounted(() => {
        initPageData()
        let date = new Date()
        state.curYear = date.getFullYear()
        state.curMonth = date.getMonth()
        state.curDate = date.getDate()
        //初始化执行
        getDayCounts(state.curYear, state.curMonth)
        getSignTime()
        currentYEAR = toRaw(state.curYear)
        currentMONTH = toRaw(state.curMonth)
    })

    const fullDate = computed(() => {
        const month = (state.curMonth + 1).toString().padStart(2, '0');
        return `${month}/${state.curYear}`
    })

    //签到
    const signIn = () => {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => {
            http({
                url: "ext_signinlog/add",
                data: {},
            }).then((res: any) => {
                loadingShow.value = false;
                if (res.code != 200 && res.code != 204) {
                    _alert(res.message)
                    isRequest = false
                    getSignTime()
                    return;
                }
                initPageData()
                _alert("Successfully check-in", function () {
                    isRequest = false
                });
            });
        }, delayTime)
    };

    //获取当月总天数
    const getDayCounts = () => {
        let counts = new Date(state.curYear, state.curMonth + 1, 0).getDate()
        //获取当前第一天是星期几
        let firstWeekDay = new Date(state.curYear, state.curMonth, 1).getDay()
        for (let i = 1; i <= counts + firstWeekDay; i++) {
            let val = i - firstWeekDay;
            state.dataCount.push(val)
        }
        let res = state.dataCount;
    }

    //获取页面初始化数据
    const initPageData = () => {
        var delayTime = Math.floor(Math.random() * 1000);
        setTimeout((() => {
            http({
                url: "ext_signinlog/pageData",
                data: {},
                method: "GET"
            }).then((res: any) => {
                pageData.value = res.data
            })
        }), delayTime)
    }

    //获取页面签到数据
    const getSignTime = () => {
        var delayTime = Math.floor(Math.random() * 1000);
        setTimeout((() => {
            let month = (state.curMonth + 1).toString().padStart(2, '0');
            let yearMonth = `${state.curYear}${month}`
            http({
                url: "ext_signinlog/list?yearMonth=" + yearMonth,
                method: "GET"
            }).then((res: any) => {
                state.signInList = res.data
            })
        }), delayTime)
    }

    //更改年份
    const changeY = (type: string) => {
        state.dataCount = [];
        type == "prev" ? state.curYear-- : state.curYear++
        getDayCounts(state.curYear, state.curMonth)
    }

    //更改月份
    const changeM = (type: string) => {
        state.dataCount = [];
        if (type == "prev") {
            state.curMonth--;
            if (state.curMonth == -1) {
                state.curMonth = 11;
                state.curYear--
            }
        } else {
            state.curMonth++;
            if (state.curMonth == 12) {
                state.curMonth = 0;
                state.curYear++
            }
        }
        getDayCounts(state.curYear, state.curMonth)
        getSignTime()
    }

    //判断是否是当前日期

    const isCurrentDate = (date: number) => {
        if (date > 0 && date <= state.dataCount.length) {
            if (date == state.curDate && currentYEAR == state.curYear && currentMONTH == state.curMonth) {
                return true
            }

        } else {
            return false
        }
    }
    //签到处理
    const isShowDot = (date: any) => {
        let { curYear, curMonth, curDate } = toRefs(state)
        let month = curMonth.value + 1
        if (month < 10)
            month = "0" + month;
        let day = date
        if (date < 10)
            day = "0" + date
        let itemDate = `${curYear.value}${month}${day}`
        let res = state.signInList.some(j => j == itemDate) ? true : false;
        return res
    }

    const filteredDate = (date: number) => {
        return date > 0 ? date : ""
    }
    const imgFlag = (src: string) => {
        return getSrcUrl(src, 1);
    }
    const onLink = (to: any) => {
        goRoute(to)
    }

    //h5 本地资源
    const imgFlag1 = (src: string) => {
        return getSrcUrl(src, 0);
    };
    const router = useRouter()
    const getProjectDetail = (item: any) => {
        router.push({ name: 'Project_detail', params: { pid: item.gsn } })
    }
</script>
<style lang="scss" scoped>

    .signinMall {
        background: url(../../assets/img/user/signinbacks2.jpg);
        background-repeat: no-repeat;
        background-size: 100% 100%;
/*        height: 100%;
        overflow: auto;*/
    }

    .sigtent {
        padding: 0 1rem;

        .sigbtn {
            img {
                width: 6rem;
                margin: 0.6rem auto 0.4rem;
            }

            p {
                text-align: center;
                color: #fff;
                padding: 0 2rem;
                font-size: 12px;
            }
        }

        .sigcash {
            background: #fff;
            border-radius: 5px;
            display: flex;
            justify-content: space-around;
            padding: 1.5rem 0;
            margin: 0.8rem 0 1rem;

            .cash {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 50%;
                border-right: 1px solid #80808047;

                h1 {
                    font-size: 2rem;
                    margin-bottom: 0.3rem;
                }

                div {
                    display: flex;
                    align-items: center;
                    justify-content: space-around;
                    height: 1.6rem;
                    color: #adadad;
                    font-size: 14px;

                    img {
                        width: 21px;
                    }
                }
            }
        }

        .earn {
            background: url(../../assets/img/user/earn.png)0 0.5rem;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            height: 3.8rem;
            margin: 0.6rem 0;
        }

        .illustrate {
            color: #fff;
            font: 14px/18px "微软雅黑";
            height: 28vh;

            div {
                background: url(../../assets/img/user/decoration.png)0px 5px;
                background-repeat: no-repeat;
                background-size: 100% 100%;
                width: 11rem;
                font-size: 16px;
                font-weight: bold;
                text-align: center;
            }

            p {
                margin: 1rem 0;
                display: flex;

                img {
                    width: 1.2rem;
                    height: 1.2rem;
                    margin-right: 0.5rem;
                }
            }
        }

        .calendar {
            background: #fff;
            border-radius: 5px;

            .head {
                display: flex;
                justify-content: center;
                padding: 0.4rem 0;
                border-bottom: 1px solid #eee;
                margin: 0.4rem 0;

                .title {
                    margin: 0 1rem;
                }
            }

            .week {
                ul {
                    display: flex;
                    justify-content: space-around;
                    padding: 0 0.2rem;

                    li {
                        width: 7%;
                    }
                }

                .list {
                    display: flex;
                    flex-wrap: wrap;
                    width: 100%;
                    height: 12rem;
                    align-items: center;

                    .block {
                        width: 14%;
                        height: 1rem;
                        display: flex;
                        justify-content: center;
                        align-items: center;

                        .dot {
                            background-color: #f9e046;
                            width: 23px;
                            height: 23px;
                            line-height: 23px;
                            text-align: center;
                            display: inline-block;
                            color: #fe5f4a;
                            border-radius: 20px;
                        }

                        .active {
                            color: #fe5f4a;
                        }
                    }
                }
            }
        }
    }
</style>
  