<template>
    <div class="task">
        <div class="topbg">
            <div class="topbg_bk" @click="callback">
                <van-icon name="arrow-left" /> Bank
            </div>
        </div>
        <div class="contentbg">
            <div class="contentbg_top">
                <div class="title">Daily Quests</div>
                <div style="height: 18vh;margin-top: 1vh;">
                    <div style="height: 17vh;width: 43%;float: left;border: 2px solid #feba05;border-radius: 7px;">
                        <img :src="m1" style="height: 6vh;width: 6vh;margin-left: 33%;" />
                        <p style="text-align: center;margin-top: 1vh;color: #869840;">{{tableData.todayregister}}/3</p>
                        <p style="color: #869840;text-align: center;font-size: 0.6rem;margin-top: 0.5vh;">INVITE 3 PEOPLE TO REGISTER</p>
                        <div @click="Claimpoints" :class=" tableData.todayregister < 3 ?'touming' :''" style="height: 3.5vh;background-color: rgb(254, 166, 4);width: 90%;margin: 0.6vh 0px 0px 5%;line-height: 3.5vh;text-align: center;color: rgb(255, 255, 255);border-radius: 6px;font-weight: bold;font-size: 0.8rem;">100 POINTS</div>
                    </div>
                    <div style="height: 17vh;width: 43%;float: right;border: 2px solid #feba05;border-radius: 7px;">
                        <img :src="m2" style="height: 6vh;width: 7vh;margin-left: 33%;" />
                        <p style="text-align: center;margin-top: 1vh;color: #869840;">{{tableData.todayRecharge}}/3</p>
                        <p style="color: #869840;text-align: center;font-size: 0.6rem;margin-top: 0.5vh;">INVITE 3 PEOPLE TO RECHARGE</p>
                        <div  @click="ClaimRS" :class=" tableData.todayRecharge < 3 ?'touming' :''" style="height: 3.5vh;background-color: rgb(254, 166, 4);width: 90%;margin: 0.6vh 0px 0px 5%;line-height: 3.5vh;text-align: center;color: rgb(255, 255, 255);border-radius: 6px;font-weight: bold;font-size: 0.8rem;">50 RS</div>
                    </div>
                </div>
                <p style="margin-top: 0.5vh;color: #787878;">
                    <span style="color: #899b45;">Notice</span>
                    : After completing the task on the same day, you cannotreceive rewards again, but you can continue to receive rewards after completing the task the next day.
                </p>
            </div>
            <div class="contentbg_content">
                <div class="title">3 Daily Task</div>
                <div style="font-size: 1.5vh;width: 88%;margin-top: 1vh;color: #787878;">
                    Invite 5 new friends to recharge within three days and get an extra 100RS.
                    <img :src="hot1" style="width: 1.5rem;height: 0.8rem;position: relative;float: right;top: -0.6rem;left: 1.9rem;" />
                </div>
                <div style="height: 3.5vh;margin-top: 1vh;">
                    <div style="display: flex;margin-top: 0.2rem;">
                        <div class="progress-bar">
                            <div class="progress" :style="{ width: progressBarWidth(tableData.threedayRecharge,5) }">
                            </div>
                        </div>
                        <p style="margin-left: 0.6rem;color: #7b7272;margin-top: 2px;"> {{tableData.threedayRecharge}}/5</p>
                        <div @click="FivePersonReward" :class=" tableData.todayRecharge < 5 ?'touming' :''" style="background: #fea804;height: 3vh;width: 25%;margin-left: 3%;text-align: center;line-height: 3vh;color: #fff;border-radius: 5px;">100 RS</div>
                    </div>
                </div>
                <div style="border-top:1px solid #c3c3c3;height: 0;margin-top: 0.5vh;"></div>
                <div style="font-size: 1.5vh;width: 88%;margin-top: 1vh;color: #787878;">
                    Invite 10 new friends to recharge within three days and get an extra 200RS.
                    <img :src="hot1" style="width: 1.5rem;height: 0.8rem;position: relative;float: right;top: -0.6rem;left: 1.9rem;" />
                </div>
                <div style="height: 3.5vh;margin-top: 1vh;">
                    <div style="display: flex;margin-top: 0.2rem;">
                        <div class="progress-bar">
                            <div class="progress" :style="{ width: progressBarWidth(tableData.threedayRecharge,10) }">
                            </div>
                        </div>
                        <p style="margin-left: 0.6rem;color: #7b7272;margin-top: 2px;"> {{tableData.threedayRecharge}}/10</p>
                        <div  @click="TenPersonReward" :class=" tableData.todayRecharge < 10 ?'touming' :''" style="background: #fea804;height: 3vh;width: 25%;margin-left: 3%;text-align: center;line-height: 3vh;color: #fff;border-radius: 5px;">200 RS</div>
                    </div>
                </div>
                <div style="font-size: 1.3vh;margin-top: 1vh;color: #787878;">
                    <span style="color: #83963b;">●</span> If the above conditions are met, you can click the button to receive it. The number of people will be recalculated the next day after receiving it.
                </div>
                <div style="font-size: 1.3vh;margin-top: 1vh;color: #787878;">
                    <span style="color: #83963b;">●</span> If you do not click the button to receive it, it will be invalid afte 3 days and the number of people will be recalculated.
                </div>
            </div>
        </div>
    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>
<script lang="ts">
    import { defineComponent, ref, onMounted } from 'vue'
    import { Button, Form, Field, CellGroup } from 'vant'
    import { Icon, Calendar } from 'vant'
    import { _alert, lang, getSrcUrl, goRoute, cutOutNum } from '../../global/common'
    import MyNav from '../../components/Nav.vue'
    import http from '../../global/network/http'
    import MyLoading from "../../components/Loading.vue";

    import { useRouter } from 'vue-router'

    export default defineComponent({
        components: { 
            MyNav, MyLoading,
            [Button.name]: Button,
            [Form.name]: Form,
            [Field.name]: Field,
            [CellGroup.name]: CellGroup,
            [Icon.name]: Icon,
        },
    })
</script>
  
<script lang="ts" setup>
    import m1 from '../../assets/img/signin/m1.png'
    import m2 from '../../assets/img/signin/m2.png'
    import hot1 from '../../assets/img/signin/hot1.png'
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();

    const loadtitle = ref("Loading...")
    const loadingShow = ref(false);
    const router = useRouter()
    const tableData = ref<any>({})
    let isRequest = false

    const callback = ()=>{
        router.push({ path: '/' });
    }

    const progressBarWidth = (task:number,total:number) => {
          return `${((task >total ? total : task)  / total) * 100}%`;
    };

    const Claimpoints = ()=>{
        if(tableData.value.todayregister < 3){
            return
        }
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        http({
            url: 'c=Share&a=Claimpoints'
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
    const ClaimRS = ()=>{
        if(tableData.value.todayRecharge < 3){
            return
        }
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        http({
            url: 'c=Share&a=ClaimRS'
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
    const FivePersonReward = ()=>{
        if(tableData.value.threedayRecharge < 5){
            return
        }
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        http({
            url: 'c=Share&a=FivePersonReward'
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
    const TenPersonReward = ()=>{
        if(tableData.value.threedayRecharge < 10){
            return
        }
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        loadingShow.value = true;
        http({
            url: 'c=Share&a=TenPersonReward'
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
                url: 'c=Share&a=getTakDat',
        }).then((res: any) => {
            tableData.value = res.data;
        })
    })
</script>
<style lang="scss" scoped>
    .task{
        width: 100%;
        height: calc(100% - 0px);

        .touming{
            opacity: 0.5;
        }

        .topbg{
            background: url(../../assets/img/signin/task1.png);
            background-repeat: no-repeat;
            background-size: 100% 100%;
            height: 30%;

            .topbg_bk{
                color: #83963b;
                position: absolute;
                top: 1rem;
                font-weight: bold;
                left: 0.5rem;
            }
        }

        .contentbg{
            background: url(../../assets/img/signin/task2.png);
            background-repeat: no-repeat;
            background-size: 100% 100%;
            height: 70%;
            margin-top: -1px;
            padding: 0 2rem;
            
            .title{
                background: url(../../assets/img/signin/rectangle.png);
                background-repeat: no-repeat;
                background-size: 100% 100%;
                height: 2.3rem;
                line-height: 2.3rem;
                width: 40%;
                margin-left: 30%;
                text-align: center;
                color: #fff;
                font-weight: bold;
            }

            .contentbg_top{
                height: 32vh;
                background-color: #fff;
                border-radius: 10px;
                padding: 0 1rem;

                p{
                    font-size: 1.5vh;
                }
            }

            .contentbg_content{
                margin-top: 2vh;
                height: 35vh;
                background-color: #fff;
                border-radius: 10px;
                padding: 0 1rem;

                .progress-bar {
                      width: 60%;
                      height: 4px;
                      background-color: #e3e3e3;
                      border-radius: 10px;
                      margin-top: 8px;
                  }
  
                  .progress {
                      height: 100%;
                      background-color: #83963b;
                      border-radius: 10px;
                  }
            }
        }

        
    }
</style>
  