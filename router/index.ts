import { _alert, lang } from "../global/common";
import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'

const routes: Array<RouteRecordRaw> = [
    {
        path: '/login',
        name: 'Login',
        meta: {
            title: lang('登录'),
            needLogin: false
        },
        component: () => import('../views/login/index.vue')
    },
    {
        path: '/forget',
        name: 'Forget',
        meta: {
            title: lang('找回密码'),
            needLogin: false
        },
        component: () => import('../views/login/forget.vue')
    },
    {
        path: '/register',
        name: 'Register',
        meta: {
            title: lang('注册'),
            needLogin: false
        },
        component: () => import('../views/login/register.vue')
    },

    {
        path: '/',
        name: 'Default',
        meta: {
            itle: lang('个人中心'),
            needLogin: true
        },
        component: () => import('../views/default/index.vue')
    },
    {
        path: '/product/:cid?',
        name: 'Product',
        meta: {
            itle: lang('产品'),
            needLogin: true
        },
        component: () => import('../views/product/index.vue')
    },
    {
        path: '/product/goods/:gsn',
        name: 'Product_goods',
        meta: {
            title: lang('项目'),
            needLogin: true
        },
        component: () => import('../views/product/goods.vue')
    },
    {
        path: '/product/order',
        name: 'Product_order',
        meta: {
            title: lang('我的产品'),
            needLogin: true
        },
        component: () => import('../views/product/order.vue')
    },
    {
        path: '/news',
        name: 'News',
        meta: {
            title: 'News',
            needLogin: true
        },
        component: () => import('../views/news/list.vue')
    },
    {
        path: '/news/info/:id',
        name: 'News_info',
        meta: {
            title: 'Detail',
            needLogin: true
        },
        component: () => import('../views/news/info.vue')
    },
    {
        path: '/user',
        name: 'User',
        meta: {
            title: 'Me',
            needLogin: true
        },
        component: () => import('../views/user/index.vue')
    },
    {
        path: '/user/team',
        name: 'User_team',
        meta: {
            title: lang('我的团队'),
            needLogin: true
        },
        component: () => import('../views/user/team.vue')
    }, {
        path: '/points',
        name: 'Points',
        meta: {
            title: 'Points Mall',
            needLogin: true
        },
        component: () => import('../views/user/points.vue')
    },
    {
        path: '/setting',
        name: 'Setting',
        meta: {
            title: lang('设置'),
            needLogin: true
        },
        component: () => import('../views/setting/index.vue')
    },
    {
        path: '/setting/uinfo',
        name: 'Setting_uinfo',
        meta: {
            title: lang('个人资料'),
            needLogin: true
        },
        component: () => import('../views/setting/uinfo.vue')
    },
    {
        path: '/setting/google',
        name: 'Setting_google',
        meta: {
            title: 'Google Authenticator',
            needLogin: true
        },
        component: () => import('../views/setting/google.vue')
    },
    {
        path: '/setting/auth',
        name: 'Setting_auth',
        meta: {
            title: 'Real-name authentication',
            needLogin: true
        },
        component: () => import('../views/setting/auth.vue')
    },
    {
        path: '/setting/password',
        name: 'Setting_password',
        meta: {
            title: lang('找回密码'),
            needLogin: true
        },
        component: () => import('../views/setting/password.vue')
    },
    {
        path: '/setting/password2',
        name: 'Setting_password2',
        meta: {
            title: lang('支付密码'),
            needLogin: true
        },
        component: () => import('../views/setting/password.vue')
    },
    {
        path: '/setting/bank',
        name: 'Setting_bank',
        meta: {
            title: lang('绑定银行卡'),
            needLogin: true
        },
        component: () => import('../views/setting/bank.vue')
    },
    {
        path: '/share',
        name: 'Share',
        meta: {
            title: lang('邀请链接'),
            needLogin: true
        },
        component: () => import('../views/share/index.vue')
    },
    {
        path: '/service/online',
        name: 'Service_online',
        meta: {
            title: 'Service',
            needLogin: true
        },
        component: () => import('../views/service/online.vue')
    },
    {
        path: '/recharge',
        name: 'Finance_recharge',
        meta: {
            title: lang('充值'),
            needLogin: false
        },
        component: () => import('../views/finance/recharge.vue')
    },
    {
        path: '/payinfo',
        name: 'Finance_payinfo',
        meta: {
            title: lang('订单详情')
        },
        component: () => import('../views/finance/payInfo.vue')
    },
    {
        path: '/paylog',
        name: 'Finance_paylog',
        meta: {
            title: lang('充值记录'),
            needLogin: true
        },
        component: () => import('../views/finance/paylog.vue')
    },
    {
        path: '/withdraw',
        name: 'Finance_withdraw',
        meta: {
            title: lang('提现'),
            needLogin: true
        },
        component: () => import('../views/finance/withdraw.vue')
    },
    {
        path: '/cashlog',
        name: 'Finance_cashlog',
        meta: {
            title: lang('提现记录'),
            needLogin: true
        },
        component: () => import('../views/finance/cashlog.vue')
    },
    {
        path: '/balancelog/:type?',
        name: 'Finance_balancelog',
        meta: {
            title: lang('财务记录'),
            needLogin: true
        },
        component: () => import('../views/finance/balancelog.vue')
    },
    {
        path: '/reward/:type?',
        name: 'Finance_reward',
        meta: {
            title: lang('奖励明细'),
            needLogin: true
        },
        component: () => import('../views/finance/reward.vue')
    },
    {
        path: '/gift/lottery/:rsn',
        name: 'Gift_lottery',
        meta: {
            title: 'Lottery',
            needLogin: true
        },
        component: () => import('../views/gift/lottery.vue')
    },
    {
        path: '/gift/redpack',
        name: 'Gift_redpack',
        meta: {
            title: 'Bonus',
            needLogin: true
        },
        component: () => import('../views/gift/redpack.vue')
    },
    {
        path: '/ext/task',
        name: 'Ext_task',
        meta: {
            title: lang('任务中心'),
            needLogin: true
        },
        component: () => import('../views/ext/task.vue')
    },
]

const router = createRouter({
    history: createWebHistory('/h5'),
    routes
})

export default router
