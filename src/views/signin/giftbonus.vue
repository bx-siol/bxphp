<template>
    <MyNav leftText=""></MyNav>
    <div class="mission">
        <div class="introduce">
            <h1 style="padding-top: 0.7rem;color: #703e2f;display: flex;justify-content: center;align-items: center;">
                <!-- <img :src="h1" style="width: 1rem;height: 1rem;margin-right: 1.5rem;" /> -->
                NESTLE GIFTS
                <!-- <img :src="h1" style="width: 1rem;height: 1rem;margin-left: 1.5rem;" /> -->
            </h1>
            <h6 style="color: #724031;margin-top: 0.3rem;">Every time you invite a friend to join the corresponding Nestle </h6>
            <h6 style="color: #724031;margin-top: 0.3rem;">projext,you can get a corresponding goft for free</h6>
        </div>
        <div class="projects">
            <div v-for="(itemc, indexc) in tableData" :style=" indexc !==0 ?'margin-top: 2vh;':'margin-top: 1.5vh;' ">
                <div class="projects_top">
                    <div>
                        <p style="margin-top: 1vh;">PRODUCT</p>
                    </div>
                    <div>
                        <p style="margin-top: 0.8vh;">FRIENDS</p>
                        <p>BUY</p>
                    </div>
                    <div>
                        <p style="margin-top: 0.8vh;">DAILY</p>
                        <p>INCOME</p>
                    </div>
                    <div>
                        <p style="margin-top: 0.8vh;">INVESTMENT</p>
                        <p>CYCLE</p>
                    </div>
                    <div>
                        <p style="margin-top: 0.8vh;">TOTAL</p>
                        <p>REVENUE</p>
                    </div>
                </div>
                <div class="projects_content">
                    <div class="projects_content_left">
                        <img :src="imgFlag(itemc.icon)">
                    </div>
                    <div class="projects_content_right">
                        <div>
                            <div>{{itemc.name}}</div>
                            <div>{{itemc.price *itemc.rate /100}}</div>
                            <div>{{itemc.days}} Days</div>
                            <div>{{itemc.price *itemc.rate*itemc.days /100}} RS</div>
                        </div>
                        <div class="projects_content_right_save" @click="Receive_Save(itemc.id)">
                            <img :src="h2" style="width: 1rem;height: 1rem;" />
                            RECEIVE GIFTS
                            <img :src="h2" style="width: 1rem;height: 1rem;" />
                        </div>
                    </div>
                </div>
                <!-- <div v-if="indexc !== tableData.length - 1" style="border-top: 1px dashed #b6aeae;height: 0;width: 100%;position: relative;top: 4px;"></div> -->
            </div>            
        </div>
        <div style="text-align: center;color: #7c4d3d;margin-top: 0.4rem;">
            <h6>Nestle gift can be obtained repeatedly.The more</h6>
            <h6 style="margin-top: 0.3rem;">members you invute to join,the more gifts you will get.</h6>
            <h4 style="margin-top: 0.6rem;">Gift deadline：June 5,2024</h4>
        </div>
        <div class="bottom"></div>
    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>
<script lang="ts">
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
    // import h1 from '../../assets/img/signin/h1.png'    
    // import h2 from '../../assets/img/signin/h2.png'
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();

    const imgFlag = (src: string) => {
        return getSrcUrl(src, 1);
    }

    const loadtitle = ref("Loading...")
    const loadingShow = ref(false);
    const router = useRouter()
    const tableData = ref<any>({})

    const pageData = ref(null)
    let isRequest = false

    const Receive_Save = (id : number ) => {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        http({
            url: 'c=Share&a=giftreceive',
            data: { goodsid: id }
        }).then((res: any) => {
            loadingShow.value = false;
            if (res.code != 200) {
                isRequest = false
                _alert(res.msg)
                return
            }
            _alert(res.msg)
            isRequest = false
        })
    }

    onMounted(() => {
        http({
            url: 'c=Share&a=giftproject',
            data: { cid: 1020 }
        }).then((res: any) => {
            tableData.value = res.data.list
        })
    })
</script>
<style lang="scss" scoped>
    .mission{
        background-color: #f4e6c8;
        padding:  0 1rem;
        height: calc(100% - 2.9rem);
        position: relative;
        z-index: 1;

        .introduce{
            text-align: center;
        }

        .projects{
            height: calc(100% - 17rem);
            width: 100%;
            margin-top:calc(100% - 21rem) ;

            .projects_top{
                height: 4vh;
                font-size: 0.5rem;
                display: flex;
                justify-content: space-between;

                div{
                    width: 17%;
                    height: 4vh;
                    background-color: #305753;
                    color: #f3e5c0;
                    text-align: center;
                    border-radius: 5px;
                }
            }

            .projects_content{
                height: 9.2vh;
                margin-top: 1vh;

                .projects_content_left{
                    width: 18%;
                    height: 10vh;
                    float: left;
                }

                .projects_content_right{
                    width: 79.5%;
                    height: 10vh;
                    float: right;

                    div{
                        display: flex;
                        justify-content: space-around;

                        div{
                            height: 4vh;
                            width: 22%;
                            background-color: #fefefe;
                            font-size: 0.55rem;
                            line-height: 4vh;
                            border:  1px solid #305753;
                            border-radius: 5px;
                            font-weight: bold;
                            color: #305753;
                        }
                    }

                    .projects_content_right_save{
                        width: 100%;
                        margin-top: 0.5rem;
                        background-color: #703e2f;
                        height: 3.5vh;
                        line-height: 3.5vh;
                        color: #e6d7c2;
                        border-radius: 10px;
                        font-size: 0.8rem;
                        font-weight: bold;
                        display: flex;
                        align-items: center;
                        justify-content: space-around;
                    }
                }
            }
        }

        .bottom{
            background-image: url('../../assets/img/signin/bottom.png');
            background-size: 100% 100%;
            background-repeat: no-repeat;
            height: 10rem;
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
            z-index: -1;
        }
    }
</style>
  