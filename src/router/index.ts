import { createRouter, createWebHistory, RouteRecordRaw, createWebHashHistory } from 'vue-router'
import { lang } from "../global/common";

const routes: Array<RouteRecordRaw> = [
    {
        path: '/login',
        name: 'Login',
        meta: {
            title: 'Login',
            needLogin: false
        },
        component: () => import('../views/login/index.vue')
    },
    {
        path: '/forget',
        name: 'Forget',
        meta: {
            title: 'Forget Password',
            needLogin: false
        },
        component: () => import('../views/login/forget.vue')
    },
    {
        path: '/register',
        name: 'Register',
        meta: {
            title:'Register',
            needLogin: false
        },
        component: () => import('../views/login/register.vue')
    },
    {
        path: '/',
        name: 'Default',
        meta: {
            title: 'Home',
             needLogin: false
        },
        component: () => import('../views/default/index.vue')
    },
    {
        path: '/invite',
        name: 'Invite',
        meta: {
            title: 'Invite',
             needLogin: false
        },
        component: () => import('../views/invite/index.vue')
    },
    {
        path: '/about',
        name: 'About',
        meta: {
            title: 'News',
             needLogin: false
        },
        component: () => import('../views/about/index.vue')
    },
    {
        path: '/about/FAQ',
        name: 'About_faq',
        meta: {
            title: 'FAQ',
             needLogin: false
        },
        component: () => import('../views/about/faq.vue')
    },
    {
        path: '/about/company/:id',
        name: 'About_company',
        meta: {
            title: '',
             needLogin: false
        },
        component: () => import('../views/about/company.vue')
    },
    {
        path: '/coupon/:type',
        name: 'coupon',
        meta: {
            title: 'Coupon',
             needLogin: false
        },
        component: () => import('../views/coupon/index.vue')
    },

    {
        path: '/project',
        name: 'Project',
        meta: {
            title: 'Project',
             needLogin: false
        },
        component: () => import('../views/project/index.vue')
    },
    {
        path: '/purchase',
        name: 'Purchase',
        meta: {
            title: 'My product',
            needLogin: true
        },
        component: () => import('../views/purchase/index.vue')
    },
    {
        path: '/project/:pid',
        name: 'Project_detail',
        meta: {
            title: 'Product',
             needLogin: false
        },
        component: () => import('../views/project/detail.vue')
    },
    {
        path: '/product/:cid?',
        name: 'Product',
        meta: {
            title: 'Product',
             needLogin: false
        },
        component: () => import('../views/product/index.vue')
    },
    {
        path: '/product/goods/:gsn',
        name: 'Product_goods',
        meta: {
            title: 'Project',
             needLogin: false
        },
        component: () => import('../views/product/goods.vue')
    },
    {
        path: '/product/order',
        name: 'Product_order',
        meta: {
            title: 'My product',
             needLogin: false
        },
        component: () => import('../views/product/order.vue')
    },
    {
        path: '/news',
        name: 'News',
        meta: {
            title: 'News',
             needLogin: false
        },
        component: () => import('../views/news/list.vue')
    },
    {
        path: '/community',
        name: 'community',
        meta: {
            title: 'community',
             needLogin: false
        },
        component: () => import('../views/news/community.vue')
    },
    {
        path: '/news/info/:id',
        name: 'News_info',
        meta: {
            title: 'Detail',
             needLogin: false
        },
        component: () => import('../views/news/info.vue')
    },
    {
        path: '/yeb',
        name: 'yeb',
        meta: {
            title: 'Fortune Treasure',
            needLogin: false
        },
        component: () => import('../views/finance/yeb.vue')
    },
    {
        path: '/revenuerecord',
        name: 'yeblog',
        meta: {
            title: 'RS Revenue record',
            needLogin: false
        },
        component: () => import('../views/finance/rewardyeb.vue')
    },
    {
        path: '/user',
        name: 'User',
        meta: {
            title: 'Me',
             needLogin: false
        },
        component: () => import('../views/user/index.vue')
    },

    {
        path: '/points',
        name: 'Points',
        meta: {
            title: 'Points Mall',
             needLogin: false
        },
        component: () => import('../views/user/points.vue')
    },
    {
        path: '/task',
        name: 'task',
        meta: {
            title: 'Task',
            needLogin: true
        },
        component: () => import('../views/signin/task.vue')
    },
    {
        path: '/user/team',
        name: 'User_team',
        meta: {
            title: 'My team',
             needLogin: false
        },
        component: () => import('../views/user/team.vue')
    },
    {
        path: '/user/team/:id',
        name: 'User_teamlist',
        meta: {
            title: 'team',
             needLogin: false
        },
        component: () => import('../views/user/teamlist.vue')
    },
    {
        path: '/setting',
        name: 'Setting',
        meta: {
            title: 'Setting',
             needLogin: false
        },
        component: () => import('../views/setting/index.vue')
    },
    {
        path: '/setting/uinfo',
        name: 'Setting_uinfo',
        meta: {
            title: 'Personal information',
             needLogin: false
        },
        component: () => import('../views/setting/uinfo.vue')
    },
    {
        path: '/setting/google',
        name: 'Setting_google',
        meta: {
            title: 'Google Authenticator',
             needLogin: false
        },
        component: () => import('../views/setting/google.vue')
    },
    {
        path: '/setting/auth',
        name: 'Setting_auth',
        meta: {
            title: 'Real-name authentication',
             needLogin: false
        },
        component: () => import('../views/setting/auth.vue')
    },
    {
        path: '/setting/password',
        name: 'Setting_password',
        meta: {
            title: 'Change Password',
             needLogin: false
        },
        component: () => import('../views/setting/password.vue')
    },
    {
        path: '/setting/password2',
        name: 'Setting_password2',
        meta: {
            title: 'Payment password',
             needLogin: false
        },
        component: () => import('../views/setting/password.vue')
    },
    {
        path: '/setting/bank',
        name: 'Setting_bank',
        meta: {
            title: 'Bind bank card',
             needLogin: false
        },
        component: () => import('../views/setting/bank.vue')
    },
    {
        path: '/share',
        name: 'Share',
        meta: {
            title: 'Invitation',
             needLogin: false
        },
        component: () => import('../views/share/index.vue')
    },
    {
        path: '/service/online',
        name: 'Service_online',
        meta: {
            title: 'Service',
             needLogin: false
        },
        component: () => import('../views/service/online.vue')
    },
    {
        path: '/recharge',
        name: 'Finance_recharge',
        meta: {
            title: 'Recharge',
            needLogin: false
        },
        component: () => import('../views/finance/recharge.vue')
    },
    {
        path: '/rechargelog',
        name: 'Finance_rechargelog',
        meta: {
            title: 'Recharge Record',
             needLogin: false
        },
        component: () => import('../views/finance/rechargelog.vue')
    },
    {
        path: '/orderdetails/:osn/:money/:par1/:par2/:par3',
        name: 'Finance_order',
        meta: {
            title: 'order details',
             needLogin: false
        },
        component: () => import('../views/finance/orderdetails.vue')
    },
    {
        path: '/payinfo',
        name: 'Finance_payinfo',
        meta: {
            title: 'Order details'
        },
        component: () => import('../views/finance/payInfo.vue')
    },
    {
        path: '/paylog',
        name: 'Finance_paylog',
        meta: {
            title: 'Recharge',
             needLogin: false
        },
        component: () => import('../views/finance/paylog.vue')
    },
    {
        path: '/withdraw',
        name: 'Finance_withdraw',
        meta: {
            title: 'Withdraw',
             needLogin: false
        },
        component: () => import('../views/finance/withdraw.vue')
    },
    {
        path: '/withdrawlog',
        name: 'Finance_withdrawlog',
        meta: {
            title: 'Withdrawal History',
             needLogin: false
        },
        component: () => import('../views/finance/withdrawlog.vue')
    },
    {
        path: '/orderdetails2/:osn/:money/:par1/:par2/:par3/:par4/:par5/:par6/:par7',
        name: 'Finance_order2',
        meta: {
            title: 'order details',
             needLogin: false
        },
        component: () => import('../views/finance/orderdetails2.vue')
    },
    {
        path: '/balancelog/:type?',
        name: 'Finance_balancelog',
        meta: {
            title: 'Financial records',
             needLogin: false
        },
        component: () => import('../views/finance/balancelog.vue')
    },
    {
        path: '/reward/:type?',
        name: 'Finance_reward',
        meta: {
            title: 'Financial reward',
             needLogin: false
        },
        component: () => import('../views/finance/reward.vue')
    },
    {
        path: '/gift/lottery/a3d044b074d37a89',
        name: 'Gift_lottery',
        meta: {
            title: 'Lottery',
             needLogin: false
        },
        component: () => import('../views/gift/lottery.vue')
    },
    {
        path: '/gift/redpack',
        name: 'Gift_redpack',
        meta: {
            title: 'Bonus',
             needLogin: false
        },
        component: () => import('../views/gift/redpack.vue')
    },
    {
        path: '/joinus',
        name: 'Joinus',
        meta: {
            title: 'Joinus',
             needLogin: false
        },
        component: () => import('../views/joinus/index.vue')
    },
    {
        path: '/service',
        name: 'Service',
        meta: {
            title: 'Service',
            needLogin: true
        },
        component: () => import('../views/user/service.vue')
    },
    {
        path: '/ext/task/:id',
        name: 'Ext_task',
        meta: {
            title: 'Task',
             needLogin: false
        },
        component: () => import('../views/ext/task.vue')
    },
    {
        path: '/monthly',
        name: 'monthly',
        meta: {
            title: 'monthly pay',
             needLogin: false
        },
        component: () => import('../views/user/monthlypay.vue')
    },
]

const router = createRouter({
    //   history: createWebHistory('/h5/'),
    history: createWebHashHistory('/'),
    routes
})

export default router
