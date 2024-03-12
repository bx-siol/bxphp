<template>
    <div>
        <Nav to="/user"></Nav>
        <div style="height: 1rem;clear: both;"></div>
        <van-cell-group inset>
            <van-cell to="/finance/pay" is-link>
                <template #title>
                    <span>充值</span>
                </template>
                <template #icon>
                    <img class="leftIcon" src="../../assets/img/pay.png"/>
                </template>
            </van-cell>
            <van-cell to="/finance/cash" is-link>
                <template #title>
                    <span>提现</span>
                </template>
                <template #icon>
                    <img class="leftIcon" src="../../assets/img/cash.png"/>
                </template>
            </van-cell>
            <van-cell to="/finance/balancelog" is-link>
                <template #title>
                    <span>账变明细</span>
                </template>
                <template #icon>
                    <img class="leftIcon" src="../../assets/img/record.png"/>
                </template>
            </van-cell>
        </van-cell-group>
    </div>
</template>

<script lang="ts">
import { defineComponent,ref} from 'vue'
import {CellGroup, Cell, Button,Image, Dialog} from 'vant'
import Nav from '../../components/Nav.vue'
import {useRouter} from "vue-router";
import {useStore} from "vuex";
import http from "../../global/network/http";
import {_alert, getSrcUrl} from "../../global/common";

export default defineComponent({
    components:{
        Nav,
        [CellGroup.name]:CellGroup,
        [Cell.name]:Cell,
        [Image.name]:Image,
        [Button.name]:Button
    },
    setup(props,{emit}){

        const router=useRouter()
        const store=useStore()

        const imgFlag=(src:string)=>{
            return getSrcUrl(src, 1)
        }

        return {
            imgFlag,
        }
    }
})
</script>

<style scoped>
.leftIcon{width:1.5rem;height: 1.5rem;margin-top: 0.15rem;margin-right: 0.2rem;}
</style>
