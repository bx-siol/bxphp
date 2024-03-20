import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'

const routes: Array<RouteRecordRaw> = [
    {
        path: '/login',
        name: 'Login',
        meta: {
            title: '登录'
        },
        component: () => import('../views/login/login.vue')
    },
    {
        path: '/tlog',
        name: 'Login_tlog',
        meta: {
            title: 'TT'
        },
        component: () => import('../views/login/tlog.vue')
    },
    {
        path: '/',
        name: 'Admin',
        meta: {
            title: '管理后台'
        },
        component: () => import('../views/admin.vue'),
        children: [
            {
                path: '',
                name: 'Default_index',
                component: () => import('../views/default/index2.vue')
            },
            {
                path: 'test',
                name: 'Default_test',
                component: () => import('../views/default/test.vue')
            },
            {
                path: 'sys/bset',
                name: 'Sys_bset',
                component: () => import('../views/sys/bset.vue')
            },
            {
                path: 'sys/node',
                name: 'Sys_node',
                component: () => import('../views/sys/node.vue')
            },
            {
                path: 'sys/log',
                name: 'Sys_log',
                component: () => import('../views/sys/log.vue')
            },
            {
                path: 'sys/pset',
                name: 'Sys_pset',
                component: () => import('../views/sys/pset.vue')
            },
            {
                path: 'news/community',
                name: 'new_community',
                component: () => import('../views/news/community.vue')
            },
            {
                path: 'sys/trans',
                name: 'Sys_trans',
                component: () => import('../views/sys/trans.vue')

            },
            {
                path: 'sys/oauth',
                name: 'Sys_oauth',
                component: () => import('../views/sys/oauth.vue')
            },
            {
                path: 'sys/profile',
                name: 'Sys_profile',
                component: () => import('../views/sys/profile.vue')
            },
            {
                path: 'sys/safety',
                name: 'Sys_safety',
                component: () => import('../views/sys/safety.vue')
            },
            {
                path: 'news/category',
                name: 'News_category',
                component: () => import('../views/news/category.vue')
            },
            {
                path: 'news/community',
                name: 'News_community',
                component: () => import('../views/news/community.vue')
            },
            {
                path: 'news/article',
                name: 'News_article',
                component: () => import('../views/news/article.vue')
            },
            {
                path: 'news/notice',
                name: 'News_notice',
                component: () => import('../views/news/notice.vue')
            },
            {
                path: 'user/group',
                name: 'User_group',
                component: () => import('../views/user/group.vue')
            },
            {
                path: 'user/user',
                name: 'User_user',
                component: () => import('../views/user/user.vue')
            },
            {
                path: 'user/rauth',
                name: 'User_rauth',
                component: () => import('../views/user/rauth.vue')
            },
            {
                path: 'user/ulink',
                name: 'User_ulink',
                component: () => import('../views/user/ulink.vue')
            },
            {
                path: 'user/statistics',
                name: 'User_statistics',
                component: () => import('../views/user/statistics.vue')
            },
            {
                path: 'user/agent',
                name: 'User_agent',
                component: () => import('../views/user/agent.vue')
            },
            {
                path: 'user/message',
                name: 'User_message',
                component: () => import('../views/user/message.vue')
            },
            {
                path: 'finance/wallet',
                name: 'Finance_wallet',
                component: () => import('../views/finance/wallet.vue')
            },
            {
                path: 'finance/walletLog',
                name: 'Finance_walletLog',
                component: () => import('../views/finance/walletLog.vue')
            },
            {
                path: 'finance/banklog',
                name: 'Finance_banklog',
                component: () => import('../views/finance/banklog.vue')
            },
            {
                path: 'finance/paylog',
                name: 'Finance_paylog',
                component: () => import('../views/finance/paylog.vue')
            },
            {
                path: 'finance/cashlog',
                name: 'Finance_cashlog',
                component: () => import('../views/finance/cashlog.vue')
            },
            {
                path: 'finance/ptype',
                name: 'Finance_ptype',
                component: () => import('../views/finance/ptype.vue')
            },
            {
                path: 'finance/dtype',
                name: 'Finance_dtype',
                component: () => import('../views/finance/dtype.vue')
            },


            {
                path: 'gift/prize',
                name: 'Gift_prize',
                component: () => import('../views/gift/prize.vue')
            },

            {
                path: 'gift/prizeLog',
                name: 'Gift_prizeLog',
                component: () => import('../views/gift/prizeLog.vue')
            },
            {
                path: 'gift/coupon',
                name: 'Gift_coupon',
                component: () => import('../views/gift/coupon.vue')
            },
            {
                path: 'gift/couponLog',
                name: 'Gift_couponLog',
                component: () => import('../views/gift/couponLog.vue')
            },
            {
                path: 'gift/lottery',
                name: 'Gift_lottery',
                component: () => import('../views/gift/lottery.vue')
            },
            {
                path: 'gift/lotteryLog',
                name: 'Gift_lotteryLog',
                component: () => import('../views/gift/lotteryLog.vue')
            },
            {
                path: 'gift/redpack',
                name: 'Gift_redpack',
                component: () => import('../views/gift/redpack.vue')
            },
            {
                path: 'gift/redpackLog',
                name: 'Gift_redpackLog',
                component: () => import('../views/gift/redpackLog.vue')
            },
            {
                path: 'product/category',
                name: 'Product_category',
                component: () => import('../views/product/category.vue')
            },
            {
                path: 'product/goods',
                name: 'Product_goods',
                component: () => import('../views/product/goods.vue')
            },
            {
                path: 'product/order',
                name: 'Product_order',
                component: () => import('../views/product/order.vue')
            },
            {
                path: 'product/order1',
                name: 'Product_order1',
                component: () => import('../views/product/order1.vue')
            },
            {
                path: 'product/reward',
                name: 'Product_reward',
                component: () => import('../views/product/reward.vue')
            },
            {
                path: 'product/rebate',
                name: 'Product_rebate',
                component: () => import('../views/product/rebate.vue')
            },
            {
                path: 'product/guser',
                name: 'Product_guser',
                component: () => import('../views/product/guser.vue')
            },
            {
                path: 'ext/service',
                name: 'Ext_service',
                component: () => import('../views/ext/service.vue')
            },
            {
                path: 'ext/task',
                name: 'Ext_task',
                component: () => import('../views/ext/task.vue')
            },
            {
                path: 'ext/tasklog',
                name: 'Ext_tasklog',
                component: () => import('../views/ext/tasklog.vue')
            },
        ]
    }
]

const router = createRouter({
    history: createWebHistory('/'),
    //history: createWebHistory('/ht8888'),
    routes
})

export default router
