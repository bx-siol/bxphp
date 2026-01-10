<?php

class RedisKeys
{
    // 用户订单相关
    const USER_ORDER = 'userorder_';
    const ORDER_STATUS = 'orderstatus_';

    // 用户数据相关
    const USER_INFO = 'userinfo_';
    const USER_BALANCE = 'userbalance_';

    // 用户钱包相关 
    const USER_WALLET = 'userwallet_';

    // 商品数据相关 
    const GOODS = 'goods_';
    const GOODS_HOT = 'goodshot_';
    const GOODS_LIST = 'goodslist_';
    const GOODS_STOCK = 'goodsstock_';

    // 支付相关
    const PAY_LOG = 'paylog_';

    // 分布式锁相关
    const LOCK_USER_BALANCE = 'lock_user_balance_';
    const LOCK_ORDER_CREATE = 'lock_order_create_';
    const LOCK_GOODS_STOCK = 'lock_goods_stock_';

}

