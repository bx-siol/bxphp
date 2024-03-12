<template>
    <div class="mission">
        <MyNav leftText="">
        </MyNav>
        <div class="tasktent">
            <div class="invites">
                <img :src="taskinvite" class="inviteimg" @click="onLink({name:'User_team'})">
            </div>
            <div class="invition">
                <div class="tasklisk" v-for="(item, index) in tableData" :key="index">
                    <div class="lisks">
                        <div style="height:6rem;">
                            <div style="width:30%;float:left;height:5.5rem;">
                                <img :src="imgFlag1(item.icon)" style="height: 4.8rem;margin:0.3rem auto;width:75%;">
                            </div>
                            <div style="width: 70%; float: right; height: 4rem;">
                                <div style="font-size: 0.85rem; font-weight: bold;" v-if="item.id == 33">Invite 2 people join 【{{item.name}}】</div>
                                <div style="font-size: 0.85rem; font-weight: bold;" v-else>Invite 1 people join 【{{item.name}}】</div>
                                <div style="margin-top:0.5rem;">
                                    <div style="width: 55%; float: left; color: #7b7b7b;font-size:0.8rem;">Daily Earnings</div>
                                    <div style="width: 45%; float: right; color: #008260; text-align: right; font-size: 0.8rem;">{{ cutOutNum((item.rate / 100) * item.price) }} RS</div>
                                </div>
                                <div style="margin-top:0.5rem;">
                                    <div style="width: 55%; float: left; color: #7b7b7b; font-size: 0.8rem;">Total Revenue</div>
                                    <div style="width: 45%; float: right; color: #008260; text-align: right; font-size: 0.8rem;">{{ cutOutNum((item.rate / 100) * item.price * item.days) }} RS</div>
                                </div>
                                <div style="margin-top:0.5rem;">
                                    <div style="width: 55%; float: left; color: #7b7b7b; font-size: 0.8rem;">Revenue Cycle</div>
                                    <div style="width: 45%; float: right; color: #008260; text-align: right; font-size: 0.8rem;">{{item.days}} Day</div>
                                </div>
                            </div>
                        </div>                       
                        <div class="lisks_save" @click="Receive_Save(item.id)">Receive</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>
<script lang="ts">
    //import { alert, lang } from "../../global/common";
    import { defineComponent, ref, reactive, onMounted, computed, toRaw, toRefs } from 'vue'
    import { Button, Form, Field, CellGroup } from 'vant'
    import { Icon, Calendar } from 'vant'
    import { _alert, lang, getSrcUrl, goRoute,cutOutNum  } from '../../global/common'
    import MyNav from '../../components/Nav.vue'
    import http from '../../global/network/http'
    import MyLoading from "../../components/Loading.vue";

    import { useRouter } from 'vue-router'

    export default defineComponent({
        components: {
            MyNav,MyLoading,
            [Button.name]: Button,
            [Form.name]: Form,
            [Field.name]: Field,
            [CellGroup.name]: CellGroup,
            [Icon.name]: Icon,
        },
    })
</script>
  
<script lang="ts" setup>
    import taskinvite from '../../assets/img/signin/taskinvite.png'
    import gift from '../../assets/img/signin/gift.png'
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();

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
        return getSrcUrl(src, 1);
    };

    const loadtitle = ref("Loading...")
    const loadingShow = ref(false);

    const router = useRouter()
    const tableData = ref<any>({})
    const detailData = ref<any>({})
    const Details = () => {
        router.push({ path: '/balancelog/1019' })
    }
    const linkback = () => {
        router.push({ path: '/' })
    }

    const pageData = ref(null)
    let isRequest = false

    const Receive_Save = (id : number ) => {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => {
            http({
                    url: 'giftbonussave',
                    data: {
                        GoodId: id,
                    }
                }).then((res: any) => {
                    loadingShow.value = false;
                    if (res.code != 200) {
                        isRequest = false
                        _alert(res.message)
                        return
                    }
                    _alert(res.message)
                    isRequest = false
                })
            
        },delayTime)
    }

    onMounted(() => {
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => { 
            http({
          url: 'goods/GetList',
          data: { size: 245, page: 1, cid: 8 }
        }).then((res: any) => {
          if (res.code != 200) {
            _alert(res.message, function () {
              router.go(-1)
            })
            return
          }
          tableData.value = res.data.list
        })
        },delayTime)
    })
</script>
<style lang="scss" scoped>
    .tasktent {
        padding: 0 1rem;

        .invites {
            .inviteimg {
                margin: 1.2rem 0;
            }

            .money {
                display: flex;
                justify-content: space-around;

                div {
                    background: url(../../assets/img/signin/back.png);
                    background-repeat: no-repeat;
                    background-size: 100% 100%;
                    width: 6rem;
                    height: 5.8rem;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;

                    h1 {
                        margin: 0.3rem 0;
                        font-size: 1.2rem;
                        color: rgb(253, 211, 55);
                    }

                    img {
                        width: 1.6rem;
                        height: 1.5rem;
                    }

                    span {
                        font-size: 0.8rem;
                        font-weight: bold;
                    }
                }
            }
        }

        .flrst {
            .flrsttop {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 1.2rem 0;

                img {
                    width: 1.2rem;
                    height: 1.2rem;
                }

                p {
                    color: #008260;
                    font-weight: bold;
                    font-size: 1.4rem;
                    margin: 0 1rem;
                }
            }

            .flrstbot {
                border: 1px solid #008260;
                border-radius: 0.5rem;
                padding: 0.8rem 0.4rem;
                display: flex;
                align-items: center;
                height: 3.5rem;

                img:nth-child(1) {
                    width: 1.8rem;
                    height: 1.4rem;
                }

                img:nth-child(3) {
                    width: 4rem;
                    height: 1.6rem;
                }

                p {
                    font: bold 12px/18px "微软雅黑";
                    zoom: 0.8;
                    margin-left: 0.5rem;
                }
            }
        }

        .invition {
            .invititop {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 1.2rem 0;

                img {
                    width: 1.2rem;
                    height: 1.2rem;
                }

                p {
                    color: #008260;
                    font-weight: bold;
                    font-size: 1.4rem;
                    margin: 0 1rem;
                }
            }

            .tasklisk {

                .lisks {
                    box-shadow: 0 0 10px 2px rgb(0 0 0/15%);
                    border-radius: 0.5rem;
                    padding: 0.8rem 0.4rem;
                    margin: 1.2rem auto;
                    height: 8rem;

                    img:nth-child(1) {
                        width: 4.5rem;
                        height: 4.5rem;
                    }

                    img:nth-child(3) {
                        width: 4rem;
                        height: 1.6rem;
                    }

                    p {
                        font: bold 12px/20px "微软雅黑";
                        margin-left: 0.5rem;
                        color: #008260;
                    }

                    span {
                        font-weight: normal;
                    }

                    .lisks_save {
                        width: 100%;
                        height: 2rem;
                        background-color: #008260;
                        text-align: center;
                        color: #fff;
                        line-height: 2rem;
                        border-radius: 21px;
                    }
                }
            }
        }
    }
</style>
  