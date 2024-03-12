<template>
    <div class="choujiang" style="padding: 0 1rem;">
        <MyNav leftText=''> </MyNav>
        <div class="top"> LUCKY DRAW</div>
        <div class="lotteryNum">There are {{lotteryNum}} draws left</div>
        <div style="height: 22rem; margin-top: 1rem; display: flex; flex-direction: row; flex-wrap: wrap; justify-content: space-around; align-content: space-around;">
            
            <div class="rollbox" @click="FlippingOver($event)">
                
                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>
                
                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="lottery1" />
                    </div>
                </div>
            </div>
            
            <div class="rollbox" @click="FlippingOver($event)">
                
                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>
               
                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="lottery1" />
                    </div>
                </div>
            </div>
           
            <div class="rollbox" @click="FlippingOver($event)">
                
                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>
                
                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="lottery1" />
                    </div>
                </div>
            </div>
            
            <div class="rollbox" @click="FlippingOver($event)">
                
                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>
                
                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="lottery1" />
                    </div>
                </div>
            </div>
            
            <div class="rollbox" @click="FlippingOver($event)">
               
                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>
                
                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="lottery1" />
                    </div>
                </div>
            </div>
            
            <div class="rollbox" @click="FlippingOver($event)">
                
                <div class="rollbox_front">
                    <div class="contentbox">
                        <img :src="open" />
                    </div>
                </div>
                
                <div class="rollbox_behind">
                    <div class="contentbox">
                        <img :src="lottery1" />
                    </div>
                </div>
            </div>
        </div>
        <div class="lotteryNum" style=" width: 43%; margin-left: 28%; margin-top: 1rem;">Activity Rules</div>
        <div style="height: 15rem; margin-top: 0.5rem; font-size: 0.70rem; color: #64523e;">
            <p>
                <img :src="xx" style="width:0.5rem;float:left;margin-top:0.3rem;" />
                <span>Invite new users to recharge and get 1 lucky fraw chance</span>
            </p>
            <br />
            <p>
                <img :src="xx" style="width:0.5rem;float:left;margin-top:0.3rem;" />
                You can get 1 lucky draw chance when you buy a product
            </p>
            <br />
            <p>
                <img :src="xx" style="width:0.5rem;float:left;margin-top:0.3rem;" />
                How to use the voucher:<br />
                <div style="margin-left:1rem;">When you get a cash coupon,the amount you get gose directly into your accout</div>
            </p>
            <br />
            <p>
                <img :src="xx" style="width:0.5rem;float:left;margin-top:0.3rem;" />
                How to use the coupon:<br />
                <div style="margin-left:1rem;">After receiving the coupon,you can purchase the corresponding discounted product and enjoy the discount</div>
            </p>
            <br />
            <p>
                <img :src="xx" style="width:0.5rem;float:left;margin-top:0.3rem;" />
                How to use the invitation coupon:<br />
                <div style="margin-left:1rem;">After obtaining the invitation coupon,invite new members to join and purchase equipment to get extra cash rewards</div>
            </p>
        </div>
        <div style="height:15rem;">
            <img :src="back" />
        </div>
        
    </div>
</template>
<script lang="ts">
    import { defineComponent, ref, onMounted, onBeforeMount } from 'vue'
    import { getSrcUrl } from '../../global/common'
    import MyNav from '../../components/Nav.vue'
    import { Grid, GridItem, Tab, Icon, Button, Image, Popup } from 'vant'
    import MyTab from '../../components/Tab.vue'
    import MyNoticeBar from '../../components/NoticeBar.vue'
    import { Swipe, SwipeItem, NoticeBar, Tag, Col, Row } from 'vant'
    import MyPop from '../../components/Pop.vue'
    export default defineComponent({
        components: {
            MyPop,
            MyTab,
            MyNoticeBar,
            [Grid.name]: Grid,
            [Tab.name]: Tab,
            [GridItem.name]: GridItem,
            [Icon.name]: Icon,
            [Button.name]: Button,
            [Image.name]: Image,
            [Popup.name]: Popup,
            [Swipe.name]: Swipe,
            [SwipeItem.name]: SwipeItem,
            [NoticeBar.name]: NoticeBar,
            [Col.name]: Col,
            [Row.name]: Row,
        },
    })
</script>
<script lang="ts" setup>
    import { useStore } from 'vuex'
    import { checkLogin, doLogout, isLogin } from '../../global/user'
    import { useRoute, useRouter } from 'vue-router'
    import { Dialog } from 'vant'
    import http from '../../global/network/http'
    import { _alert, lang } from '../../global/common'

    import open from '../../assets/img/lottery/open.png'
    import lottery1 from '../../assets/img/lottery/lottery1.png'
    import xx from '../../assets/img/lottery/xx.png'
    import back from '../../assets/img/lottery/back.png'

    const FlippingOver = (val: any) => {
        val.target.parentNode.parentNode.parentNode.className += " box_rolling ";
        console.log(val.target.parentNode.parentNode.parentNode);
    }
</script>

<style scoped>
.choujiang :deep(.van-popup) {
  background: none;
}
</style>
<style lang="scss" scoped>
    .choujiang {
        .top {
            height: 3rem;
            text-align: center;
            font-size: 2.2rem;
            line-height: 3rem;
            font-style: italic;
            color: #64523e;
            font-weight: bold;
            margin: 1rem 0 0.5rem 0;
        }

        .lotteryNum {
            height: 1.5rem;
            text-align: center;
            color: #fff;
            background-color: #64523e;
            line-height: 1.5rem;
            width: 68%;
            margin-left: 16%;
            border-radius: 10px;
            font-size: 0.85rem;
        }

        .rollbox {
            position: relative;
            perspective: 1000px;
            width: 31%;
            height: 10rem;

            &_front,
            &_behind {
                transform-style: preserve-3d; 
                backface-visibility: hidden; 
                transition-duration: .5s;
                transition-timing-function: 'ease-in';
                background: #008080;

                .contentbox {
                    > img {
                        height: 10rem;
                    }
                }
            }

            &_behind {
                transform: rotateY(180deg);
                visibility: hidden; 
                position: absolute;
                top: 0;
                bottom: 0;
                right: 0;
                left: 0;
            }
        }

        .box_rolling {
            .rollbox_front {
                transform: rotateY(180deg);
                visibility: hidden;
            }

            .rollbox_behind {
                transform: rotateY(360deg);
                visibility: visible;
            }
        }
    }
</style>
