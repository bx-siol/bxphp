import { createApp } from 'vue';
import store from './store';
import router from './router';
import 'animate.css';
import App from './App.vue';
import http from "./global/network/http";
import { getLocalToken, getLocalUser, isLogin } from "./global/user";
import { _alert, isWx, lang, setConfig } from "./global/common";
import tool from "./global/tool.js";
import i18n from './i18n/index'


// 完整加载
import VueLuckyCanvas from '@lucky-canvas/vue'

//@ts-ignore
// import wx from 'weixin-js-sdk';
import VConsole from 'vconsole';

function getQueryVariable(variable: string) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) { return pair[1]; }
    }
    return (false);
}

let debug = getQueryVariable('debug');
if (debug) {
    const vConsole = new VConsole();
}

//导航守卫
const routerAct = () => {

    router.beforeEach(function (to, from, next) {
        let title = ''
        if (to.meta && to.meta.title) {
            title = to.meta.title as string
        }
        document.title = (title)

        next()
    })

    createApp(App).use(store).use(router).use(VueLuckyCanvas).use(tool).use(i18n).mount('#app')
}

//页面初始化
const init = (callback: Function) => {
    //获取基本配置
    // http({
    //     url: 'a=getConfig'
    // }).then((res: any) => {
    //     if (res.code != 200) {
    //         _alert(res.message)
    //         return
    //     }
    //     // res.data.img_url = location.origin;
    //     //console.log(res.data);
    //     setConfig(res.data)
    //     //初始化其他数据
    //     store.dispatch('init', { token: getLocalToken(), user: getLocalUser() })
    //     callback()
    // })

    callback()
}

init(() => {
    routerAct()
})
