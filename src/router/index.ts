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
        name: '后台',
        meta: {
            title: '管理后台'
        },
        component: () => import('../views/admin.vue'),
        children: [
            {
                path: '/index',
                name: '首页',
                component: () => import('../views/default/index2.vue')
            },
            {
                path: 'test',
                name: 'Default_test',
                component: () => import('../views/default/test.vue')
            },
            {
                path: 'sys/bset',
                name: '基础配置',
                component: () => import('../views/sys/bset.vue')
            },
            {
                path: 'sys/node',
                name: '节点管理',
                component: () => import('../views/sys/node.vue')
            },
            {
                path: 'sys/log',
                name: '操作日志',
                component: () => import('../views/sys/log.vue')
            },
            {
                path: 'sys/pset',
                name: '平台设置',
                component: () => import('../views/sys/pset.vue')
            },
            {
                path: 'news/community',
                name: 'new_community',
                component: () => import('../views/news/community.vue')
            },
            {
                path: 'sys/trans',
                name: '语言翻译',
                component: () => import('../views/sys/trans.vue')

            },
            {
                path: 'sys/oauth',
                name: '权限管理',
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
                name: '文章分类',
                component: () => import('../views/news/category.vue')
            },
            {
                path: 'news/community',
                name: 'News_community',
                component: () => import('../views/news/community.vue')
            },
            {
                path: 'news/article',
                name: '文章列表',
                component: () => import('../views/news/article.vue')
            },
            {
                path: 'news/notice',
                name: '系统公告',
                component: () => import('../views/news/notice.vue')
            },
            {
                path: 'user/group',
                name: '用户分组',
                component: () => import('../views/user/group.vue')
            },
            {
                path: 'user/user',
                name: '用户列表',
                component: () => import('../views/user/user.vue')
            },
            {
                path: 'user/rauth',
                name: 'User_rauth',
                component: () => import('../views/user/rauth.vue')
            },
            {
                path: 'user/ulink',
                name: '邀请链接',
                component: () => import('../views/user/ulink.vue')
            },
            {
                path: 'user/statistics',
                name: '会员统计',
                component: () => import('../views/user/statistics.vue')
            },
            {
                path: 'user/agent',
                name: '代理查询',
                component: () => import('../views/user/agent.vue')
            },
            {
                path: 'user/message',
                name: 'User_message',
                component: () => import('../views/user/message.vue')
            },
            {
                path: 'finance/wallet',
                name: '资产列表',
                component: () => import('../views/finance/wallet.vue')
            },
            {
                path: 'finance/walletLog',
                name: '资产账变记录',
                component: () => import('../views/finance/walletLog.vue')
            },
            {
                path: 'finance/banklog',
                name: '收款卡号',
                component: () => import('../views/finance/banklog.vue')
            },
            {
                path: 'finance/paylog',
                name: '充值记录',
                component: () => import('../views/finance/paylog.vue')
            },
            {
                path: 'finance/cashlog',
                name: '提现记录',
                component: () => import('../views/finance/cashlog.vue')
            },
            {
                path: 'finance/ptype',
                name: '支付通道管理',
                component: () => import('../views/finance/ptype.vue')
            },
            {
                path: 'finance/dtype',
                name: '代付通道管理',
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
                name: '优惠券列表',
                component: () => import('../views/gift/coupon.vue')
            },
            {
                path: 'gift/couponLog',
                name: '领券记录',
                component: () => import('../views/gift/couponLog.vue')
            },
            {
                path: 'gift/lottery',
                name: '抽奖设置',
                component: () => import('../views/gift/lottery.vue')
            },
            {
                path: 'gift/lotteryLog',
                name: '抽奖记录',
                component: () => import('../views/gift/lotteryLog.vue')
            },
            {
                path: 'gift/redpack',
                name: '红包码管理',
                component: () => import('../views/gift/redpack.vue')
            },
            {
                path: 'gift/redpackLog',
                name: '红包领取记录',
                component: () => import('../views/gift/redpackLog.vue')
            },
            {
                path: 'product/category',
                name: '产品分类',
                component: () => import('../views/product/category.vue')
            },
            {
                path: 'product/goods',
                name: '产品列表',
                component: () => import('../views/product/goods.vue')
            },
            {
                path: 'product/order',
                name: '订单列表',
                component: () => import('../views/product/order.vue')
            },
            {
                path: 'product/order1',
                name: '奖金审核',
                component: () => import('../views/product/order1.vue')
            },
            {
                path: 'product/reward',
                name: '收益记录',
                component: () => import('../views/product/reward.vue')
            },
            {
                path: 'product/rebate',
                name: '佣金明细',
                component: () => import('../views/product/rebate.vue')
            },
            {
                path: 'product/guser',
                name: '赠送管理',
                component: () => import('../views/product/guser.vue')
            },
            {
                path: 'ext/service',
                name: '客服管理',
                component: () => import('../views/ext/service.vue')
            },
            {
                path: 'ext/task',
                name: '任务管理',
                component: () => import('../views/ext/task.vue')
            },
            {
                path: 'ext/tasklog',
                name: '任务领取记录',
                component: () => import('../views/ext/tasklog.vue')
            }, {
                path: 'finance/utr',
                name: 'Finance_utr',
                component: () => import('../views/finance/utr.vue')
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
