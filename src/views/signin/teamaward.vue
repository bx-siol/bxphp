<template>
    <div class="topbg">
        <div class="topbg_bk" @click="callback">
            <van-icon name="arrow-left" /> Bank
        </div>
    </div>
    <div class="contentbg">
        <div class="content_top">
            <div class="content_top_up">
                <swiper :slidesPerView="5" :spaceBetween="30" :loop="true" :autoplay="{delay: 1500}" 
                :effect="'coverflow'" :coverflowEffect="{depth: 80,modifier: 2,rotate: 10,slideShadows: false,stretch: -20}"  class="swiper">
                    <swiper-slide>
                        <van-image :src="gift1" class="swiper_img"></van-image>
                    </swiper-slide>
                    <swiper-slide>
                        <van-image :src="gift2" class="swiper_img"></van-image>
                    </swiper-slide>
                    <swiper-slide>
                        <van-image :src="gift3" class="swiper_img"></van-image>
                    </swiper-slide>
                    <swiper-slide>
                        <van-image :src="gift4" class="swiper_img"></van-image>
                    </swiper-slide>
                    <swiper-slide>
                        <van-image :src="gift5" class="swiper_img"></van-image>
                    </swiper-slide>
                    <swiper-slide>
                        <van-image :src="gift6" class="swiper_img"></van-image>
                    </swiper-slide>
                </swiper>
            </div>
            <div class="content_top_down">Develop into the best version of yourself</div>
        </div>
        <div class="content_content">
            <div class="content_content_item" style="margin-bottom: 0.5rem;">
                <div class="content_content_item_top">LUXURY GIF 1</div>
                <P class="content_content_item_first">BCD TEAM MEMBERS ARRIVE AT <span style="color: #cf1c17;font-weight: bold;font-size: 0.6rem;">10</span></P>
                <P class="content_content_item_second">REWARD A PIECE OF WORK CLOTHES</P>
                <P class="content_content_item_second">AN EMPLOYEE ID CARD</P>
                <div class="content_content_item_third">
                    <div class="content_content_item_third_text">Current progress: {{hy.lv1}}/10</div>
                    <div class="content_content_item_third_img img1"></div>
                    <div class="content_content_item_third_img_hot"></div>
                </div>
            </div>
            <div class="content_content_item" style="margin-bottom: 0.5rem;">
                <div class="content_content_item_top">LUXURY GIF 2</div>
                <P class="content_content_item_first">BCD TEAM MEMBERS ARRIVE AT <span style="color: #cf1c17;font-weight: bold;font-size: 0.6rem;">100</span></P>
                <P class="content_content_item_second">REWARD WITH A BEAUTIFUL GIFT</P>
                <P class="content_content_item_second">(WORTH RS 1,000)</P>
                <div class="content_content_item_third">
                    <div class="content_content_item_third_text">Current progress: {{hy.lv2}}/100</div>
                    <div class="content_content_item_third_img img2"></div>
                    <div class="content_content_item_third_img_hot"></div>
                </div>
            </div>
            <div class="content_content_item" style="margin-bottom: 0.5rem;">
                <div class="content_content_item_top">LUXURY GIF 3</div>
                <P class="content_content_item_first">BCD TEAM MEMBERS ARRIVE AT <span style="color: #cf1c17;font-weight: bold;font-size: 0.6rem;">300</span></P>
                <P class="content_content_item_second">REWARD WITH A BEAUTIFUL GIFT</P>
                <P class="content_content_item_second">(WORTH RS 3,000)</P>
                <div class="content_content_item_third">
                    <div class="content_content_item_third_text">Current progress: {{hy.lv3}}/300</div>
                    <div class="content_content_item_third_img img3"></div>
                    <div class="content_content_item_third_img_hot"></div>
                </div>
            </div>
            <div class="content_content_item" style="margin-bottom: 0.5rem;">
                <div class="content_content_item_top">LUXURY GIF 4</div>
                <P class="content_content_item_first">BCD TEAM MEMBERS ARRIVE AT <span style="color: #cf1c17;font-weight: bold;font-size: 0.6rem;">700</span></P>
                <P class="content_content_item_second">REWARD AN APPLE MOBILE PHONE</P>
                <P class="content_content_item_second">(WORTH RS 70,000)</P>
                <div class="content_content_item_third">
                    <div class="content_content_item_third_text">Current progress: {{hy.lv4}}/700</div>
                    <div class="content_content_item_third_img img4"></div>
                    <div class="content_content_item_third_img_hot"></div>
                </div>
            </div>
            <div class="content_content_item">
                <div class="content_content_item_top">LUXURY GIF 5</div>
                <P class="content_content_item_first" style="font-size: 0.45rem;">BCD TEAM MEMBERS ARRIVE AT <span style="color: #cf1c17;font-weight: bold;font-size: 0.6rem;">1500</span></P>
                <P class="content_content_item_second">REWARD A MOTORCYCLE</P>
                <P class="content_content_item_second">(WORTH RS 200,000)</P>
                <div class="content_content_item_third">
                    <div class="content_content_item_third_text">Current progress: {{hy.lv5}}/1500</div>
                    <div class="content_content_item_third_img img5"></div>                    
                    <div class="content_content_item_third_img_hot"></div>
                </div></div>
            <div class="content_content_item">
                <div class="content_content_item_top">LUXURY GIF 6</div>
                <P class="content_content_item_first" style="font-size: 0.45rem;">BCD TEAM MEMBERS ARRIVE AT <span style="color: #cf1c17;font-weight: bold;font-size: 0.6rem;">3000</span></P>
                <P class="content_content_item_second">A REWARD CAR</P>
                <P class="content_content_item_second">(WORTH RS 1,299,999)</P>
                <div class="content_content_item_third">
                    <div class="content_content_item_third_text">Current progress: {{hy.lv6}}/3000</div>
                    <div class="content_content_item_third_img img6"></div>                    
                    <div class="content_content_item_third_img_hot"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script lang="ts">
import { defineComponent, onMounted,ref } from 'vue'
import { Icon } from 'vant'
import { useRoute, useRouter } from "vue-router"
import { _alert, lang,  } from '../../global/common'
import { Image, } from "vant";
import http from '../../global/network/http'
import gift1 from '../../assets/img/signin/gift1.png'
import gift2 from '../../assets/img/signin/gift2.png'
import gift3 from '../../assets/img/signin/gift3.png'
import gift4 from '../../assets/img/signin/gift4.png'
import gift5 from '../../assets/img/signin/gift5.png'
import gift6 from '../../assets/img/signin/gift6.png'

import { Swiper, SwiperSlide } from 'swiper/vue';
import SwiperCore, { Autoplay,EffectCoverflow } from 'swiper';
import 'swiper/css';
import 'swiper/css/autoplay';
import 'swiper/css/effect-coverflow';

SwiperCore.use([Autoplay,EffectCoverflow]);

export default defineComponent({
    components: {   
        [Image.name]: Image,
        [Icon.name]: Icon,
        Swiper,
        SwiperSlide,
    },
})
</script>
  
<script lang="ts" setup>
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();

    const hy = ref({
        lv1:0,
        lv2:0,
        lv3:0,
        lv4:0,
        lv5:0,
        lv6:0,
    })
    
    const router = useRouter()


    const getYXHY = ()=>{
        http({
                url: 'c=User&a=gettodayregusercount',
        }).then((res: any) => {
            var yxhy = res.data.count1;
            hy.value.lv1 = yxhy;
            hy.value.lv2 = yxhy;
            hy.value.lv3 = yxhy;
            hy.value.lv4 = yxhy;
            hy.value.lv5 = yxhy;
            hy.value.lv6 = yxhy;
            
            if(yxhy >10  && yxhy<=100)
            {
                hy.value.lv1 = 10;
            }
            else if(yxhy >100  && yxhy <=300)
            {
                hy.value.lv1 = 10;
                hy.value.lv2 =  100;
            }
            else if(yxhy >300  && yxhy <=700)
            {
                hy.value.lv1 = 10;
                hy.value.lv2 =  100;
                hy.value.lv3 =  300;
            }
            else if(yxhy >700  && yxhy <=1500)
            {
                hy.value.lv1 = 10;
                hy.value.lv2 =  100;
                hy.value.lv3 =  300;
                hy.value.lv4 =  700;
            }
            else if(yxhy >1500  && yxhy <=3000)
            {
                hy.value.lv1 = 10;
                hy.value.lv2 =  100;
                hy.value.lv3 =  300;
                hy.value.lv4 =  700;
                hy.value.lv5 =  1500;
            }
            else if(yxhy > 3000)
            {
                hy.value.lv1 = 10;
                hy.value.lv2 =  100;
                hy.value.lv3 =  300;
                hy.value.lv4 =  700;
                hy.value.lv5 =  1500;
                hy.value.lv6 =  3000;
            }
        })
    }

    const callback = ()=>{
        router.push({ path: '/' });
    }

    onMounted(() => {
        getYXHY();
    })
</script>
<style lang="scss" scoped>
.topbg{
    background: url(../../assets/img/signin/teamawardbg1.png);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    height: 30%;

    .topbg_bk{
        color: #fff;
        position: absolute;
        top: 1rem;
        font-weight: bold;
        left: 0.5rem;
    }
}
.contentbg{
    background: url(../../assets/img/signin/teamawardbg2.png);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    height: 70%;
    margin-top: -1px;

    .content_top{
        width: 90%;
        margin: 0 auto; 
        height: 7rem;
        background: -webkit-linear-gradient(right, #9ee1f3,#6ecffa); /* Safari 5.1 - 6.0 */
        background: -o-linear-gradient(right, #9ee1f3,#6ecffa); /* Opera 11.1 - 12.0 */
        background: -moz-linear-gradient(right,#9ee1f3,#6ecffa); /* Firefox 3.6 - 15 */
        background: linear-gradient(to right, #9ee1f3,#6ecffa); /* 标准的语法（必须放在最后） */
        border-radius: 10px;
        border: 3px #d0ecf5 solid;

        .content_top_up{
            height: 5.5rem;
            white-space: nowrap;
            overflow: hidden;

            .swiper{
                padding-top: 0.5rem;
                margin-left: -2rem;

                .swiper_img{
                    height: 4.2rem;
                    width: 5.5rem;
                    background-color: #fff;                    
                    box-shadow: 2px 3px 3px 1px #39b1f7;
                    border-radius: 10px;
                }
            }
        }

        .content_top_down{
            color: #fff;
            margin: 0 auto;
            width: 88%;
            height: 2.5rem;
            font-size: 0.8rem;
            line-height: 2.5rem;
            border-radius: 50px;
            font-weight: bolder;
            background-image: linear-gradient(#fbe046,#fc7021);
            padding: 0 auto;
            text-align: center;
            box-shadow: 1px 2px 0px 1px #fff;
        }
    }

    .content_content{
        width: 100%;
        margin: 0 auto; 
        height: calc(69% - 0.8rem);
        margin-top: 2rem;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-evenly;
        align-content: space-around;

        .content_content_item{
            width: 43%;
            height: 12vh;
            background-image: linear-gradient(#fcfdfd,#dde0f1);
            border: 3px #51c2f9 solid;
            border-radius: 10px;

            .content_content_item_top{
                color: #fff;
                margin: 0 auto;
                width: 80%;
                height: 1.5rem;
                font-size: 0.8rem;
                line-height: 1.5rem;
                border-radius: 50px;
                font-weight: bolder;
                background-image: linear-gradient(#fbe046,#fc7021);
                padding: 0 auto;
                text-align: center;
                box-shadow: 1px 2px 0px 1px #fff;
                margin-top: -0.8rem;
            }

            .content_content_item_first{
                font-size: 0.5rem;
                margin-left: 0.5rem;
                font-weight: bold;
            }
            .content_content_item_second{
                margin-top: 0.3rem;
                margin-left: 0.5rem;
                font-size: 0.5rem;
                -webkit-text-stroke: 1px #ff9c4b; // 根据需求不同改变颜色
                -webkit-text-fill-color : transparent;
                color:#fff;
            }

            .content_content_item_third{
                height: calc(70% - 2rem);

                .content_content_item_third_text{
                    background-color: #f83018;
                    width: 73%;
                    font-size: 0.5rem;
                    height: 3vh;
                    line-height: 3vh;
                    color: #fff;
                    text-align: center;
                    font-weight: bold;
                    margin-left: 0.3rem;
                    margin-top: 0.2rem;
                    border-radius: 8px;
                    float: left;
                }

                .content_content_item_third_img{
                    width: 20%;
                    height: 5vh;
                    float: right;
                    margin-top: -0.7rem;
                    margin-right: 3%;                    
                }

                .content_content_item_third_img_hot{
                        background: url(../../assets/img/signin/hot.png);
                        background-repeat: no-repeat;
                        background-size: 100% 100%;
                        width: 1.2rem;
                        height: 1.2rem;
                        position: relative;
                        right: -8.8rem;
                        top: -0.9rem;
                    }

                .img1{
                    background: url(../../assets/img/signin/gift1.png);
                    background-repeat: no-repeat;
                    background-size: 100% 100%;
                }
                .img2{
                    background: url(../../assets/img/signin/gift2.png);
                    background-repeat: no-repeat;
                    background-size: 100% 100%;
                    width: 23%;
                    margin-right: -1%;
                    height: 2.3rem;
                }
                .img3{
                    background: url(../../assets/img/signin/gift3.png);
                    background-repeat: no-repeat;
                    background-size: 100% 100%;
                    width: 24%;
                    margin-right: -2%;
                    height: 2.3rem;
                }
                .img4{
                    background: url(../../assets/img/signin/gift4.png);
                    background-repeat: no-repeat;
                    background-size: 100% 100%;
                    width: 22%;
                    margin-right: 0.5%;
                    height: 2.6rem;
                }
                .img5{
                    background: url(../../assets/img/signin/gift5.png);
                    background-repeat: no-repeat;
                    background-size: 100% 100%;
                    width: 41%;
                    margin-right: -9%;
                    margin-left: -50%;
                    height: 2.3rem;
                }
                .img6{
                    background: url(../../assets/img/signin/gift6.png);
                    background-repeat: no-repeat;
                    background-size: 100% 100%;
                    width: 28%;
                    margin-right: -4%;
                    margin-left: -3%;
                    height: 2.3rem;
                }
            }
        }
    }
}
</style>
  