<template>
    <router-view></router-view>
</template>

<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue';
import { useStore } from "vuex";
import { isLogin } from "./global/user";
import { createWebsocket } from './global/network/ws';

const store = useStore()
onMounted(() => {
    if (store.state.config.ws_home) {
        if (isLogin()) {
            //createWebsocket()
        } else {
            //createWebsocket(true)
        }
    }
})

</script>

<style>
@import "assets/css/main.css";

@media only screen and (max-device-width: 420px) {}

@media only screen and (min-device-width: 1025px) {
    /*#app{margin: 0 auto;max-width: 640px;}*/
}

#app {
    background: #fff;
}

#app .myBtn {
    background-size: 100% 100%;
    border: 0;
    color: #84973b;
    background: #fff;
    font-weight: bold;
    border-radius: 2rem;
}

.van-button.sendCodeBtn {
    border: none;
    color: #84973b;
    font-weight: bold;
    vertical-align: middle;
    font-size: 16px;
}

.sendCodeBtn .van-count-down {
    color: #0098a2 !important;
}

.navRightBtn {
    background: transparent;
    border: 1px solid #ffffff;
    border-radius: 3px;
    color: #ffffff;
    vertical-align: middle;
}

.myListBox .van-list__error-text,
.myListBox .van-list__finished-text,
.myListBox .van-list__loading {
    line-height: 1.5rem;
    color: #ccc;
}

.toastBox {
    background: #84973b !important;
    padding: 1rem !important;
    /* font-size: 1.135rem !important; */
    word-break: break-word !important;
    /* width: 45% !important; */
}

.conBox .van-cell-group {
    background-color: transparent !important;
}

.conBox .van-cell {
    color: white;
    background-color: transparent !important;
}

.conBox .van-cell::after {
    /* border-bottom: 1px solid #2c2c2c; */
}

.conBox .van-hairline--top-bottom::after,
.conBox .van-hairline-unset--top-bottom::after {
    border-color: none;
}

.conBox .van-field__control {
    color: #fff;
}

.conBox .van-field__label {
    color: #fff ;
    text-align: left;
}

.conBox .van-radio__label {
    color: #3d3d3b;
}

.conBox .van-radio__icon--checked .van-icon {
    background-color: #0098a2;
    border-color: #0098a2;
}

.conBox .van-field__left-icon .van-icon,
.conBox .van-field__right-icon .van-icon {
    font-size: 1.3rem;
}

.conBox .van-card {
    background: #fff;
    color: #3d3d3b;
    border-radius: 5px;
}
</style>