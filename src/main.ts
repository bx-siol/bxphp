import { createApp } from 'vue';
import store from './store';
import router from './router';
import ElementPlus from 'element-plus';
import zhCn from 'element-plus/es/locale/lang/zh-cn';
import 'element-plus/dist/index.css';
import 'animate.css';
import http from "./global/network/http";
import { _alert, setConfig } from "./global/common";
import { isLogin, getLocalToken, getLocalUser } from "./global/user";
import App from './App.vue';
 
//页面初始化
const init = (callback: Function) => {
    http({
        url: 'a=getConfig'
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }



        setConfig(res.data)
        //初始化其他数据
        store.dispatch('init', { token: getLocalToken(), user: getLocalUser() })
        callback()
    })
}

init(() => {

    //导航守卫
    router.beforeEach(function (to, from, next) {
        if (to.name == 'Login' || to.name == 'Login_tlog') {
            if (isLogin()) {
                next({ name: 'Default_index' })
                return
            }
        } else {
            if (!isLogin()) {
                next({ name: 'Login' })
                return
            }
        }
        if (to.meta && to.meta.title) {
            let title = to.meta.title as string
            document.title = title
        }
        next()
    })

    createApp(App).use(store).use(router).use(ElementPlus, { locale: zhCn }).mount('#app')
})
