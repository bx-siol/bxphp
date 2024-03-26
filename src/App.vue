<template>
    <router-view @loadingClose="onLoadingClose"></router-view>
</template>

<script lang="ts" setup>
import { ElLoading, ILoadingInstance } from 'element-plus'
import { onMounted } from 'vue'
import { useStore } from 'vuex'
import { createWebsocket } from './global/network/ws'
import { isLogin } from "./global/user";

const store = useStore()
let loadingObj: ILoadingInstance

const loading = () => {
    loadingObj = ElLoading.service({
        lock: true,
        text: 'Loading',
        spinner: 'el-icon-loading',
        background: 'rgba(249, 249, 249, 0.5)'
    });
}

const onLoadingClose = () => {
    if (loadingObj) {
        loadingObj.close()
    }
}

onMounted(() => {
    //loading()
    if (isLogin() && store.state.config.ws_admin) {
        //createWebsocket()
    }
})
</script>

<style>
* {
    margin: 0;
    padding: 0;
}

html,
body {
    height: 100%;
    overflow: hidden;
}

#app {
    height: 100%;
    font-weight: bold;
}

.el-header {
    border-bottom: 1px solid #f2f2f2;
    box-sizing: border-box;
}

.el-sub-menu__title {
    height: 48px;
    line-height: 48px;
}

.el-sub-menu .el-menu-item {
    height: 40px;
    line-height: 40px;
}

.el-popover.el-popper {
    line-height: 2rem;
}

.el-form-item {
    margin-bottom: 10px;
}

.el-dialog__body {
    padding: 10px 20px;
}

.el-message-box-showimg {
    width: auto;
    min-width: 420px;
    max-width: 800px;
    max-height: 700px;
    overflow: auto;
}

.img-upload-box {
    position: relative;
    overflow: hidden;
    width: 120px;
    height: 120px;
    border: 1px dashed #c0ccda;
    background-color: #fbfdff;
    font-weight: bold;
    text-align: center;
    font-size: 1.5rem;
}

.img-upload-box img {
    width: 120px;
    height: 120px;
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
}

.img-upload-box .el-upload--text i {
    display: block;
    width: 120px;
    height: 120px;
    line-height: 120px;
    position: absolute;
    left: 0;
    top: 0;
    z-index: 2;
}

.conbar {
    border-bottom: 1px solid #ececec;
    width: 100%;
    clear: both;
    overflow: hidden;
    position: relative;
}

.conbox {
    padding: 0px 0px;
}

.consearch {
    padding-top: 2px;
    padding-bottom: 2px;
    text-align: right;
}

.consummary {
    text-align: center;
    min-height: 36px;
    line-height: 36px;
    color: #606266;
    border: 1px solid #EBEEF5;
    border-top: 0;
}

.consummary span {
    padding: 0px;
    margin: 0 6px;
}

.conpage {
    text-align: center;
}

.conpage .el-pagination {
    white-space: normal;
}

.consearch .el-input-group__append,
.consearch .el-input-group__prepend {
    padding: 0 8px;
}

.el-table .el-table__cell,
.el-sub-menu__title {
    color: #000000;
}

.conbar .el-breadcrumb__item .el-breadcrumb__inner {
    color: #000000;
}

.el-table .el-table__cell {
    padding: 1px 0 !important;
}

.el-table .cell {
    line-height: 24px !important;
    padding-left: 1px !important;
    padding-right: 1px !important;
}

.el-table th.el-table__cell>.cell {
    padding: 10px 0 !important;
}

.el-button--small {
    min-height: 24px;
    padding: 0px 5px;
    font-size: 12px;
}

#app>section>section>main>div>div.el-scrollbar__wrap.el-scrollbar__wrap--hidden-default>div>div>div.el-tabs.el-tabs--card.el-tabs--top {
    height: 38px;
}
</style>