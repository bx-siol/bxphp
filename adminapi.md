# 管理后台 API 接口文档

> 项目：Admin 管理后台系统  
> 生成时间：2026-01-11  
> 接口基础路径：`/api/?m=Admin&`

## 目录
- [1. 基础接口](#1-基础接口)
- [2. Default - 默认/首页](#2-default---默认首页)
- [3. User - 用户管理](#3-user---用户管理)
- [4. Finance - 财务管理](#4-finance---财务管理)
- [5. Product - 产品管理](#5-product---产品管理)
- [6. Gift - 礼品管理](#6-gift---礼品管理)
- [7. News - 新闻/文章管理](#7-news---新闻文章管理)
- [8. Sys - 系统管理](#8-sys---系统管理)
- [9. Ext - 扩展功能](#9-ext---扩展功能)
- [10. Test - 测试](#10-test---测试)
- [11. Trans - 翻译](#11-trans---翻译)

---

## 1. 基础接口

### 1.1 获取系统配置
- **接口路径**: `a=getConfig`
- **完整路径**: `/api/?m=Admin&a=getConfig`
- **请求方法**: POST
- **功能说明**: 获取系统配置信息

### 1.2 用户登录
- **接口路径**: `a=login`
- **完整路径**: `/api/?m=Admin&a=login`
- **请求方法**: POST
- **功能说明**: 管理员登录

### 1.3 用户登出
- **接口路径**: `a=logout`
- **完整路径**: `/api/?m=Admin&a=logout`
- **请求方法**: POST
- **功能说明**: 管理员登出

### 1.4 获取用户信息
- **接口路径**: `a=userinfo`
- **完整路径**: `/api/?m=Admin&a=userinfo`
- **请求方法**: POST
- **功能说明**: 获取当前登录用户信息

### 1.5 清除缓存
- **接口路径**: `a=clearCache`
- **完整路径**: `/api/?m=Admin&a=clearCache`
- **请求方法**: POST
- **功能说明**: 清除系统缓存

### 1.6 获取验证码
- **接口路径**: `a=getVcode`
- **完整路径**: `/api/?m=Admin&a=getVcode`
- **请求方法**: POST
- **功能说明**: 获取图形验证码

### 1.7 修改表字段值
- **接口路径**: `a=changeTableVal`
- **完整路径**: `/api/?m=Admin&a=changeTableVal`
- **请求方法**: POST
- **功能说明**: 动态修改数据表字段值

### 1.8 获取PC信息
- **接口路径**: `a=getPc`
- **完整路径**: `/api/?m=Admin&a=getPc`
- **请求方法**: POST
- **功能说明**: 获取PC相关信息

---

## 2. Default - 默认/首页

### 2.1 获取首页数据
- **接口路径**: `c=Default&a=getData`
- **完整路径**: `/api/?m=Admin&c=Default&a=getData`
- **请求方法**: POST
- **功能说明**: 获取首页统计数据

### 2.2 获取数据1
- **接口路径**: `c=Default&a=getData1`
- **完整路径**: `/api/?m=Admin&c=Default&a=getData1`
- **请求方法**: POST
- **功能说明**: 获取首页数据统计1

### 2.3 获取数据2
- **接口路径**: `c=Default&a=getData2`
- **完整路径**: `/api/?m=Admin&c=Default&a=getData2`
- **请求方法**: POST
- **功能说明**: 获取首页数据统计2

### 2.4 获取数据3
- **接口路径**: `c=Default&a=getData3`
- **完整路径**: `/api/?m=Admin&c=Default&a=getData3`
- **请求方法**: POST
- **功能说明**: 获取首页数据统计3

### 2.5 获取短信验证码
- **接口路径**: `c=Default&a=yzm`
- **完整路径**: `/api/?m=Admin&c=Default&a=yzm`
- **请求方法**: POST
- **功能说明**: 获取短信验证码

---

## 3. User - 用户管理

### 3.1 用户统计
- **接口路径**: `c=User&a=statistics`
- **完整路径**: `/api/?m=Admin&c=User&a=statistics`
- **请求方法**: POST
- **功能说明**: 获取用户统计数据列表

### 3.2 代理列表
- **接口路径**: `c=User&a=agent`
- **完整路径**: `/api/?m=Admin&c=User&a=agent`
- **请求方法**: POST
- **功能说明**: 获取代理用户列表

### 3.3 更新用户
- **接口路径**: `c=User&a=user_update`
- **完整路径**: `/api/?m=Admin&c=User&a=user_update`
- **请求方法**: POST
- **功能说明**: 更新用户信息

### 3.4 删除用户
- **接口路径**: `c=User&a=user_delete`
- **完整路径**: `/api/?m=Admin&c=User&a=user_delete`
- **请求方法**: POST
- **功能说明**: 删除用户

### 3.5 踢出用户
- **接口路径**: `c=User&a=user_kick`
- **完整路径**: `/api/?m=Admin&c=User&a=user_kick`
- **请求方法**: POST
- **功能说明**: 强制用户下线

### 3.6 用户支付
- **接口路径**: `c=User&a=user_pay`
- **完整路径**: `/api/?m=Admin&c=User&a=user_pay`
- **请求方法**: POST
- **功能说明**: 用户支付操作

### 3.7 禁用状态
- **接口路径**: `c=User&a=DisableStatus`
- **完整路径**: `/api/?m=Admin&c=User&a=DisableStatus`
- **请求方法**: POST
- **功能说明**: 设置用户禁用状态

### 3.8 更新首次支付日期
- **接口路径**: `c=User&a=UpdateUserfirst_pay_day`
- **完整路径**: `/api/?m=Admin&c=User&a=UpdateUserfirst_pay_day`
- **请求方法**: POST
- **功能说明**: 更新用户首次支付日期

### 3.9 转账操作
- **接口路径**: `c=User&a=transferAct`
- **完整路径**: `/api/?m=Admin&c=User&a=transferAct`
- **请求方法**: POST
- **功能说明**: 用户之间转账

### 3.10 转账操作1
- **接口路径**: `c=User&a=transferAct1`
- **完整路径**: `/api/?m=Admin&c=User&a=transferAct1`
- **请求方法**: POST
- **功能说明**: 转账操作类型1

### 3.11 转账操作2
- **接口路径**: `c=User&a=transferAct2`
- **完整路径**: `/api/?m=Admin&c=User&a=transferAct2`
- **请求方法**: POST
- **功能说明**: 转账操作类型2

### 3.12 转账操作3
- **接口路径**: `c=User&a=transferAct3`
- **完整路径**: `/api/?m=Admin&c=User&a=transferAct3`
- **请求方法**: POST
- **功能说明**: 转账操作类型3

### 3.13 自有账户转账
- **接口路径**: `c=User&a=transferActOwn`
- **完整路径**: `/api/?m=Admin&c=User&a=transferActOwn`
- **请求方法**: POST
- **功能说明**: 自有账户之间转账

### 3.14 用户组列表
- **接口路径**: `c=User&a=group`
- **完整路径**: `/api/?m=Admin&c=User&a=group`
- **请求方法**: POST
- **功能说明**: 获取用户组列表

### 3.15 更新用户组
- **接口路径**: `c=User&a=group_update`
- **完整路径**: `/api/?m=Admin&c=User&a=group_update`
- **请求方法**: POST
- **功能说明**: 更新用户组信息

### 3.16 删除用户组
- **接口路径**: `c=User&a=group_delete`
- **完整路径**: `/api/?m=Admin&c=User&a=group_delete`
- **请求方法**: POST
- **功能说明**: 删除用户组

### 3.17 消息列表
- **接口路径**: `c=User&a=message`
- **完整路径**: `/api/?m=Admin&c=User&a=message`
- **请求方法**: POST
- **功能说明**: 获取用户消息列表

### 3.18 回复消息
- **接口路径**: `c=User&a=message_reply`
- **完整路径**: `/api/?m=Admin&c=User&a=message_reply`
- **请求方法**: POST
- **功能说明**: 回复用户消息

### 3.19 删除消息
- **接口路径**: `c=User&a=message_delete`
- **完整路径**: `/api/?m=Admin&c=User&a=message_delete`
- **请求方法**: POST
- **功能说明**: 删除用户消息

### 3.20 实名认证列表
- **接口路径**: `c=User&a=rauth`
- **完整路径**: `/api/?m=Admin&c=User&a=rauth`
- **请求方法**: POST
- **功能说明**: 获取实名认证列表

### 3.21 实名认证审核
- **接口路径**: `c=User&a=rauth_check`
- **完整路径**: `/api/?m=Admin&c=User&a=rauth_check`
- **请求方法**: POST
- **功能说明**: 审核实名认证申请

### 3.22 用户链接
- **接口路径**: `c=User&a=ulink`
- **完整路径**: `/api/?m=Admin&c=User&a=ulink`
- **请求方法**: POST
- **功能说明**: 获取用户推广链接

---

## 4. Finance - 财务管理

### 4.1 充值类型列表
- **接口路径**: `c=Finance&a=dtype`
- **完整路径**: `/api/?m=Admin&c=Finance&a=dtype`
- **请求方法**: POST
- **功能说明**: 获取充值类型列表

### 4.2 更新充值类型
- **接口路径**: `c=Finance&a=dtype_update`
- **完整路径**: `/api/?m=Admin&c=Finance&a=dtype_update`
- **请求方法**: POST
- **功能说明**: 更新充值类型配置

### 4.3 删除充值类型
- **接口路径**: `c=Finance&a=dtype_delete`
- **完整路径**: `/api/?m=Admin&c=Finance&a=dtype_delete`
- **请求方法**: POST
- **功能说明**: 删除充值类型

### 4.4 支付类型列表
- **接口路径**: `c=Finance&a=ptype`
- **完整路径**: `/api/?m=Admin&c=Finance&a=ptype`
- **请求方法**: POST
- **功能说明**: 获取支付类型列表

### 4.5 更新支付类型
- **接口路径**: `c=Finance&a=ptype_update`
- **完整路径**: `/api/?m=Admin&c=Finance&a=ptype_update`
- **请求方法**: POST
- **功能说明**: 更新支付类型配置

### 4.6 删除支付类型
- **接口路径**: `c=Finance&a=ptype_delete`
- **完整路径**: `/api/?m=Admin&c=Finance&a=ptype_delete`
- **请求方法**: POST
- **功能说明**: 删除支付类型

### 4.7 支付类型余额
- **接口路径**: `c=Finance&a=ptype_balance`
- **完整路径**: `/api/?m=Admin&c=Finance&a=ptype_balance`
- **请求方法**: POST
- **功能说明**: 查询支付类型余额

### 4.8 支付记录列表
- **接口路径**: `c=Finance&a=paylog`
- **完整路径**: `/api/?m=Admin&c=Finance&a=paylog`
- **请求方法**: POST
- **功能说明**: 获取支付记录列表

### 4.9 银行审核
- **接口路径**: `c=Finance&a=bank_check`
- **完整路径**: `/api/?m=Admin&c=Finance&a=bank_check`
- **请求方法**: POST
- **功能说明**: 银行充值审核

### 4.10 在线银行审核
- **接口路径**: `c=Finance&a=bank_check_onlie`
- **完整路径**: `/api/?m=Admin&c=Finance&a=bank_check_onlie`
- **请求方法**: POST
- **功能说明**: 在线银行充值审核

### 4.11 取消订单
- **接口路径**: `c=Finance&a=qxdd`
- **完整路径**: `/api/?m=Admin&c=Finance&a=qxdd`
- **请求方法**: POST
- **功能说明**: 取消支付订单

### 4.12 支付记录审核
- **接口路径**: `c=Finance&a=paylog_check`
- **完整路径**: `/api/?m=Admin&c=Finance&a=paylog_check`
- **请求方法**: POST
- **功能说明**: 审核支付记录

### 4.13 提现记录列表
- **接口路径**: `c=Finance&a=cashlog`
- **完整路径**: `/api/?m=Admin&c=Finance&a=cashlog`
- **请求方法**: POST
- **功能说明**: 获取提现记录列表

### 4.14 更新提现记录
- **接口路径**: `c=Finance&a=cashlog_update`
- **完整路径**: `/api/?m=Admin&c=Finance&a=cashlog_update`
- **请求方法**: POST
- **功能说明**: 更新提现记录信息

### 4.15 删除提现记录
- **接口路径**: `c=Finance&a=cashlog_delete`
- **完整路径**: `/api/?m=Admin&c=Finance&a=cashlog_delete`
- **请求方法**: POST
- **功能说明**: 删除提现记录

### 4.16 提现审核
- **接口路径**: `c=Finance&a=cashlog_check`
- **完整路径**: `/api/?m=Admin&c=Finance&a=cashlog_check`
- **请求方法**: POST
- **功能说明**: 审核提现申请

### 4.17 提现审核2
- **接口路径**: `c=Finance&a=cashlog_check2`
- **完整路径**: `/api/?m=Admin&c=Finance&a=cashlog_check2`
- **请求方法**: POST
- **功能说明**: 提现审核方式2

### 4.18 批量提现审核
- **接口路径**: `c=Finance&a=cashlog_check_all`
- **完整路径**: `/api/?m=Admin&c=Finance&a=cashlog_check_all`
- **请求方法**: POST
- **功能说明**: 批量审核提现申请

### 4.19 获取钱包ID
- **接口路径**: `c=Finance&a=getwid`
- **完整路径**: `/api/?m=Admin&c=Finance&a=getwid`
- **请求方法**: POST
- **功能说明**: 获取钱包ID信息

### 4.20 检查提款
- **接口路径**: `c=Finance&a=checktk`
- **完整路径**: `/api/?m=Admin&c=Finance&a=checktk`
- **请求方法**: POST
- **功能说明**: 检查提款状态

### 4.21 获取IFSC
- **接口路径**: `c=Finance&a=Getifsc`
- **完整路径**: `/api/?m=Admin&c=Finance&a=Getifsc`
- **请求方法**: POST
- **功能说明**: 获取IFSC银行代码

### 4.22 银行流水记录
- **接口路径**: `c=Finance&a=banklog`
- **完整路径**: `/api/?m=Admin&c=Finance&a=banklog`
- **请求方法**: POST
- **功能说明**: 获取银行流水记录列表

### 4.23 更新银行流水
- **接口路径**: `c=Finance&a=banklog_update`
- **完整路径**: `/api/?m=Admin&c=Finance&a=banklog_update`
- **请求方法**: POST
- **功能说明**: 更新银行流水信息

### 4.24 删除银行流水
- **接口路径**: `c=Finance&a=banklog_delete`
- **完整路径**: `/api/?m=Admin&c=Finance&a=banklog_delete`
- **请求方法**: POST
- **功能说明**: 删除银行流水记录

### 4.25 钱包列表
- **接口路径**: `c=Finance&a=wallet`
- **完整路径**: `/api/?m=Admin&c=Finance&a=wallet`
- **请求方法**: POST
- **功能说明**: 获取用户钱包列表

### 4.26 钱包支付
- **接口路径**: `c=Finance&a=wallet_pay`
- **完整路径**: `/api/?m=Admin&c=Finance&a=wallet_pay`
- **请求方法**: POST
- **功能说明**: 钱包支付操作

### 4.27 钱包记录
- **接口路径**: `c=Finance&a=walletLog`
- **完整路径**: `/api/?m=Admin&c=Finance&a=walletLog`
- **请求方法**: POST
- **功能说明**: 获取钱包流水记录

### 4.28 充值操作
- **接口路径**: `c=Finance&a=rechargeAct`
- **完整路径**: `/api/?m=Admin&c=Finance&a=rechargeAct`
- **请求方法**: POST
- **功能说明**: 充值操作

### 4.29 UTR查询
- **接口路径**: `c=Finance&a=utr`
- **完整路径**: `/api/?m=Admin&c=Finance&a=utr`
- **请求方法**: POST
- **功能说明**: UTR交易查询

---

## 5. Product - 产品管理

### 5.1 产品分类列表
- **接口路径**: `c=Product&a=category`
- **完整路径**: `/api/?m=Admin&c=Product&a=category`
- **请求方法**: POST
- **功能说明**: 获取产品分类列表（树形结构）

### 5.2 更新产品分类
- **接口路径**: `c=Product&a=category_update`
- **完整路径**: `/api/?m=Admin&c=Product&a=category_update`
- **请求方法**: POST
- **功能说明**: 更新产品分类信息

### 5.3 删除产品分类
- **接口路径**: `c=Product&a=category_delete`
- **完整路径**: `/api/?m=Admin&c=Product&a=category_delete`
- **请求方法**: POST
- **功能说明**: 删除产品分类

### 5.4 商品列表
- **接口路径**: `c=Product&a=goods`
- **完整路径**: `/api/?m=Admin&c=Product&a=goods`
- **请求方法**: POST
- **功能说明**: 获取商品列表

### 5.5 更新商品
- **接口路径**: `c=Product&a=goods_update`
- **完整路径**: `/api/?m=Admin&c=Product&a=goods_update`
- **请求方法**: POST
- **功能说明**: 更新商品信息

### 5.6 删除商品
- **接口路径**: `c=Product&a=goods_delete`
- **完整路径**: `/api/?m=Admin&c=Product&a=goods_delete`
- **请求方法**: POST
- **功能说明**: 删除商品

### 5.7 根据分类获取商品
- **接口路径**: `c=Product&a=getGoodsByCid`
- **完整路径**: `/api/?m=Admin&c=Product&a=getGoodsByCid`
- **请求方法**: POST
- **功能说明**: 根据分类ID获取商品列表

### 5.8 订单列表
- **接口路径**: `c=Product&a=order`
- **完整路径**: `/api/?m=Admin&c=Product&a=order`
- **请求方法**: POST
- **功能说明**: 获取订单列表

### 5.9 设置订单
- **接口路径**: `c=Product&a=order_set`
- **完整路径**: `/api/?m=Admin&c=Product&a=order_set`
- **请求方法**: POST
- **功能说明**: 设置订单状态

### 5.10 设置订单1
- **接口路径**: `c=Product&a=order_set1`
- **完整路径**: `/api/?m=Admin&c=Product&a=order_set1`
- **请求方法**: POST
- **功能说明**: 设置订单状态方式1

### 5.11 更新订单
- **接口路径**: `c=Product&a=order_update`
- **完整路径**: `/api/?m=Admin&c=Product&a=order_update`
- **请求方法**: POST
- **功能说明**: 更新订单信息

### 5.12 删除订单
- **接口路径**: `c=Product&a=order_delete`
- **完整路径**: `/api/?m=Admin&c=Product&a=order_delete`
- **请求方法**: POST
- **功能说明**: 删除订单

### 5.13 批量审核订单
- **接口路径**: `c=Product&a=order_check_all`
- **完整路径**: `/api/?m=Admin&c=Product&a=order_check_all`
- **请求方法**: POST
- **功能说明**: 批量审核订单

### 5.14 返利列表
- **接口路径**: `c=Product&a=rebate`
- **完整路径**: `/api/?m=Admin&c=Product&a=rebate`
- **请求方法**: POST
- **功能说明**: 获取返利列表

### 5.15 奖励列表
- **接口路径**: `c=Product&a=reward`
- **完整路径**: `/api/?m=Admin&c=Product&a=reward`
- **请求方法**: POST
- **功能说明**: 获取奖励列表

---

## 6. Gift - 礼品管理

### 6.1 优惠券列表
- **接口路径**: `c=Gift&a=coupon`
- **完整路径**: `/api/?m=Admin&c=Gift&a=coupon`
- **请求方法**: POST
- **功能说明**: 获取优惠券列表

### 6.2 更新优惠券
- **接口路径**: `c=Gift&a=coupon_update`
- **完整路径**: `/api/?m=Admin&c=Gift&a=coupon_update`
- **请求方法**: POST
- **功能说明**: 更新优惠券信息

### 6.3 删除优惠券
- **接口路径**: `c=Gift&a=coupon_delete`
- **完整路径**: `/api/?m=Admin&c=Gift&a=coupon_delete`
- **请求方法**: POST
- **功能说明**: 删除优惠券

### 6.4 优惠券记录
- **接口路径**: `c=Gift&a=couponLog`
- **完整路径**: `/api/?m=Admin&c=Gift&a=couponLog`
- **请求方法**: POST
- **功能说明**: 获取优惠券使用记录

### 6.5 添加优惠券记录
- **接口路径**: `c=Gift&a=couponLogAdd`
- **完整路径**: `/api/?m=Admin&c=Gift&a=couponLogAdd`
- **请求方法**: POST
- **功能说明**: 添加优惠券记录

### 6.6 删除优惠券记录
- **接口路径**: `c=Gift&a=couponLog_delete`
- **完整路径**: `/api/?m=Admin&c=Gift&a=couponLog_delete`
- **请求方法**: POST
- **功能说明**: 删除优惠券记录

### 6.7 添加优惠券
- **接口路径**: `c=Gift&a=addyxyh`
- **完整路径**: `/api/?m=Admin&c=Gift&a=addyxyh`
- **请求方法**: POST
- **功能说明**: 批量添加优惠券

### 6.8 给指定用户添加优惠券
- **接口路径**: `c=Gift&a=addyxyhbyuser`
- **完整路径**: `/api/?m=Admin&c=Gift&a=addyxyhbyuser`
- **请求方法**: POST
- **功能说明**: 给指定用户添加优惠券

### 6.9 抽奖列表
- **接口路径**: `c=Gift&a=lottery`
- **完整路径**: `/api/?m=Admin&c=Gift&a=lottery`
- **请求方法**: POST
- **功能说明**: 获取抽奖活动列表

### 6.10 保存抽奖
- **接口路径**: `c=Gift&a=lottery_save`
- **完整路径**: `/api/?m=Admin&c=Gift&a=lottery_save`
- **请求方法**: POST
- **功能说明**: 保存抽奖配置

### 6.11 抽奖记录
- **接口路径**: `c=Gift&a=lotteryLog`
- **完整路径**: `/api/?m=Admin&c=Gift&a=lotteryLog`
- **请求方法**: POST
- **功能说明**: 获取抽奖记录

### 6.12 奖品列表
- **接口路径**: `c=Gift&a=prize`
- **完整路径**: `/api/?m=Admin&c=Gift&a=prize`
- **请求方法**: POST
- **功能说明**: 获取奖品列表

### 6.13 更新奖品
- **接口路径**: `c=Gift&a=prize_update`
- **完整路径**: `/api/?m=Admin&c=Gift&a=prize_update`
- **请求方法**: POST
- **功能说明**: 更新奖品信息

### 6.14 删除奖品
- **接口路径**: `c=Gift&a=prize_delete`
- **完整路径**: `/api/?m=Admin&c=Gift&a=prize_delete`
- **请求方法**: POST
- **功能说明**: 删除奖品

### 6.15 奖品记录
- **接口路径**: `c=Gift&a=prizeLog`
- **完整路径**: `/api/?m=Admin&c=Gift&a=prizeLog`
- **请求方法**: POST
- **功能说明**: 获取奖品发放记录

### 6.16 红包列表
- **接口路径**: `c=Gift&a=redpack`
- **完整路径**: `/api/?m=Admin&c=Gift&a=redpack`
- **请求方法**: POST
- **功能说明**: 获取红包列表

### 6.17 更新红包
- **接口路径**: `c=Gift&a=redpack_update`
- **完整路径**: `/api/?m=Admin&c=Gift&a=redpack_update`
- **请求方法**: POST
- **功能说明**: 更新红包信息

### 6.18 删除红包
- **接口路径**: `c=Gift&a=redpack_delete`
- **完整路径**: `/api/?m=Admin&c=Gift&a=redpack_delete`
- **请求方法**: POST
- **功能说明**: 删除红包

### 6.19 红包记录
- **接口路径**: `c=Gift&a=redpackLog`
- **完整路径**: `/api/?m=Admin&c=Gift&a=redpackLog`
- **请求方法**: POST
- **功能说明**: 获取红包领取记录

---

## 7. News - 新闻/文章管理

### 7.1 新闻分类列表
- **接口路径**: `c=News&a=category`
- **完整路径**: `/api/?m=Admin&c=News&a=category`
- **请求方法**: POST
- **功能说明**: 获取新闻分类列表（树形结构）

### 7.2 更新新闻分类
- **接口路径**: `c=News&a=category_update`
- **完整路径**: `/api/?m=Admin&c=News&a=category_update`
- **请求方法**: POST
- **功能说明**: 更新新闻分类信息

### 7.3 删除新闻分类
- **接口路径**: `c=News&a=category_delete`
- **完整路径**: `/api/?m=Admin&c=News&a=category_delete`
- **请求方法**: POST
- **功能说明**: 删除新闻分类

### 7.4 文章列表
- **接口路径**: `c=News&a=article`
- **完整路径**: `/api/?m=Admin&c=News&a=article`
- **请求方法**: POST
- **功能说明**: 获取文章列表

### 7.5 更新文章
- **接口路径**: `c=News&a=article_update`
- **完整路径**: `/api/?m=Admin&c=News&a=article_update`
- **请求方法**: POST
- **功能说明**: 更新文章内容

### 7.6 删除文章
- **接口路径**: `c=News&a=article_delete`
- **完整路径**: `/api/?m=Admin&c=News&a=article_delete`
- **请求方法**: POST
- **功能说明**: 删除文章

### 7.7 公告列表
- **接口路径**: `c=News&a=notice`
- **完整路径**: `/api/?m=Admin&c=News&a=notice`
- **请求方法**: POST
- **功能说明**: 获取公告列表

### 7.8 更新公告
- **接口路径**: `c=News&a=notice_update`
- **完整路径**: `/api/?m=Admin&c=News&a=notice_update`
- **请求方法**: POST
- **功能说明**: 更新公告内容

### 7.9 删除公告
- **接口路径**: `c=News&a=notice_delete`
- **完整路径**: `/api/?m=Admin&c=News&a=notice_delete`
- **请求方法**: POST
- **功能说明**: 删除公告

### 7.10 社区列表
- **接口路径**: `c=News&a=community`
- **完整路径**: `/api/?m=Admin&c=News&a=community`
- **请求方法**: POST
- **功能说明**: 获取社区内容列表

### 7.11 更新社区
- **接口路径**: `c=News&a=community_update`
- **完整路径**: `/api/?m=Admin&c=News&a=community_update`
- **请求方法**: POST
- **功能说明**: 更新社区内容

### 7.12 删除社区
- **接口路径**: `c=News&a=community_delete`
- **完整路径**: `/api/?m=Admin&c=News&a=community_delete`
- **请求方法**: POST
- **功能说明**: 删除社区内容

---

## 8. Sys - 系统管理

### 8.1 系统参数设置
- **接口路径**: `c=Sys&a=pset`
- **完整路径**: `/api/?m=Admin&c=Sys&a=pset`
- **请求方法**: POST
- **功能说明**: 获取系统参数配置

### 8.2 更新系统参数
- **接口路径**: `c=Sys&a=pset_update`
- **完整路径**: `/api/?m=Admin&c=Sys&a=pset_update`
- **请求方法**: POST
- **功能说明**: 更新系统参数配置

### 8.3 更新抽奖设置
- **接口路径**: `c=Sys&a=lottery_update`
- **完整路径**: `/api/?m=Admin&c=Sys&a=lottery_update`
- **请求方法**: POST
- **功能说明**: 更新抽奖系统配置

### 8.4 个人资料
- **接口路径**: `c=Sys&a=profile`
- **完整路径**: `/api/?m=Admin&c=Sys&a=profile`
- **请求方法**: POST
- **功能说明**: 获取个人资料

### 8.5 更新个人资料
- **接口路径**: `c=Sys&a=profile_update`
- **完整路径**: `/api/?m=Admin&c=Sys&a=profile_update`
- **请求方法**: POST
- **功能说明**: 更新个人资料

### 8.6 安全设置
- **接口路径**: `c=Sys&a=safety`
- **完整路径**: `/api/?m=Admin&c=Sys&a=safety`
- **请求方法**: POST
- **功能说明**: 获取安全设置

### 8.7 更新安全设置
- **接口路径**: `c=Sys&a=safety_update`
- **完整路径**: `/api/?m=Admin&c=Sys&a=safety_update`
- **请求方法**: POST
- **功能说明**: 更新安全设置（如密码修改）

### 8.8 权限列表
- **接口路径**: `c=Sys&a=oauth`
- **完整路径**: `/api/?m=Admin&c=Sys&a=oauth`
- **请求方法**: POST
- **功能说明**: 获取权限配置列表

### 8.9 更新权限
- **接口路径**: `c=Sys&a=oauth_update`
- **完整路径**: `/api/?m=Admin&c=Sys&a=oauth_update`
- **请求方法**: POST
- **功能说明**: 更新权限配置

### 8.10 节点列表
- **接口路径**: `c=Sys&a=node`
- **完整路径**: `/api/?m=Admin&c=Sys&a=node`
- **请求方法**: POST
- **功能说明**: 获取菜单节点列表

### 8.11 更新节点
- **接口路径**: `c=Sys&a=node_update`
- **完整路径**: `/api/?m=Admin&c=Sys&a=node_update`
- **请求方法**: POST
- **功能说明**: 更新菜单节点

### 8.12 删除节点
- **接口路径**: `c=Sys&a=node_delete`
- **完整路径**: `/api/?m=Admin&c=Sys&a=node_delete`
- **请求方法**: POST
- **功能说明**: 删除菜单节点

### 8.13 翻译列表
- **接口路径**: `c=Sys&a=trans`
- **完整路径**: `/api/?m=Admin&c=Sys&a=trans`
- **请求方法**: POST
- **功能说明**: 获取翻译配置列表

### 8.14 更新翻译
- **接口路径**: `c=Sys&a=trans_update`
- **完整路径**: `/api/?m=Admin&c=Sys&a=trans_update`
- **请求方法**: POST
- **功能说明**: 更新翻译配置

### 8.15 删除翻译
- **接口路径**: `c=Sys&a=trans_delete`
- **完整路径**: `/api/?m=Admin&c=Sys&a=trans_delete`
- **请求方法**: POST
- **功能说明**: 删除翻译配置

### 8.16 后台设置列表
- **接口路径**: `c=Sys&a=bset`
- **完整路径**: `/api/?m=Admin&c=Sys&a=bset`
- **请求方法**: POST
- **功能说明**: 获取后台设置列表

### 8.17 更新后台设置
- **接口路径**: `c=Sys&a=bset_update`
- **完整路径**: `/api/?m=Admin&c=Sys&a=bset_update`
- **请求方法**: POST
- **功能说明**: 更新后台设置

### 8.18 删除后台设置
- **接口路径**: `c=Sys&a=bset_delete`
- **完整路径**: `/api/?m=Admin&c=Sys&a=bset_delete`
- **请求方法**: POST
- **功能说明**: 删除后台设置

### 8.19 日志列表
- **接口路径**: `c=Sys&a=log`
- **完整路径**: `/api/?m=Admin&c=Sys&a=log`
- **请求方法**: POST
- **功能说明**: 获取系统操作日志

---

## 9. Ext - 扩展功能

### 9.1 银行列表
- **接口路径**: `c=Ext&a=bank`
- **完整路径**: `/api/?m=Admin&c=Ext&a=bank`
- **请求方法**: POST
- **功能说明**: 获取银行列表

### 9.2 更新银行
- **接口路径**: `c=Ext&a=bank_update`
- **完整路径**: `/api/?m=Admin&c=Ext&a=bank_update`
- **请求方法**: POST
- **功能说明**: 更新银行信息

### 9.3 客服列表
- **接口路径**: `c=Ext&a=service`
- **完整路径**: `/api/?m=Admin&c=Ext&a=service`
- **请求方法**: POST
- **功能说明**: 获取客服列表

### 9.4 更新客服
- **接口路径**: `c=Ext&a=service_update`
- **完整路径**: `/api/?m=Admin&c=Ext&a=service_update`
- **请求方法**: POST
- **功能说明**: 更新客服信息

### 9.5 删除客服
- **接口路径**: `c=Ext&a=service_delete`
- **完整路径**: `/api/?m=Admin&c=Ext&a=service_delete`
- **请求方法**: POST
- **功能说明**: 删除客服

### 9.6 任务列表
- **接口路径**: `c=Ext&a=task`
- **完整路径**: `/api/?m=Admin&c=Ext&a=task`
- **请求方法**: POST
- **功能说明**: 获取任务列表

### 9.7 更新任务
- **接口路径**: `c=Ext&a=task_update`
- **完整路径**: `/api/?m=Admin&c=Ext&a=task_update`
- **请求方法**: POST
- **功能说明**: 更新任务信息

### 9.8 删除任务
- **接口路径**: `c=Ext&a=task_delete`
- **完整路径**: `/api/?m=Admin&c=Ext&a=task_delete`
- **请求方法**: POST
- **功能说明**: 删除任务

### 9.9 任务记录
- **接口路径**: `c=Ext&a=tasklog`
- **完整路径**: `/api/?m=Admin&c=Ext&a=tasklog`
- **请求方法**: POST
- **功能说明**: 获取任务完成记录

### 9.10 任务记录审核
- **接口路径**: `c=Ext&a=tasklog_check`
- **完整路径**: `/api/?m=Admin&c=Ext&a=tasklog_check`
- **请求方法**: POST
- **功能说明**: 审核任务完成记录

---

## 10. Test - 测试

### 10.1 测试日志
- **接口路径**: `c=Test&a=tlog`
- **完整路径**: `/api/?m=Admin&c=Test&a=tlog`
- **请求方法**: POST
- **功能说明**: 测试日志（测试环境）

---

## 11. Trans - 翻译

### 11.1 更新翻译
- **接口路径**: `c=Trans&a=trans_update`
- **完整路径**: `/api/?m=Admin&c=Trans&a=trans_update`
- **请求方法**: POST
- **功能说明**: 更新翻译内容

---

## 接口统计

### 按模块统计

| 模块 | 接口数量 |
|------|---------|
| 基础接口 | 8 |
| Default - 默认/首页 | 5 |
| User - 用户管理 | 22 |
| Finance - 财务管理 | 29 |
| Product - 产品管理 | 15 |
| Gift - 礼品管理 | 19 |
| News - 新闻/文章管理 | 12 |
| Sys - 系统管理 | 19 |
| Ext - 扩展功能 | 10 |
| Test - 测试 | 1 |
| Trans - 翻译 | 1 |
| **总计** | **141** |

### 接口命名规范

接口遵循以下命名规范：
- **列表查询**: `c=Module&a=entity` （如：`c=User&a=statistics`）
- **新增/更新**: `c=Module&a=entity_update` （如：`c=User&a=user_update`）
- **删除**: `c=Module&a=entity_delete` （如：`c=User&a=user_delete`）
- **审核**: `c=Module&a=entity_check` （如：`c=Finance&a=cashlog_check`）
- **特殊操作**: `c=Module&a=action` （如：`c=User&a=user_kick`）

### 技术说明

1. **请求方式**: 所有接口统一使用 POST 请求
2. **数据格式**: 请求数据使用 `application/x-www-form-urlencoded` 格式
3. **认证方式**: 使用 Token 认证，Token 放在请求头的 `Token` 字段
4. **响应格式**: 统一 JSON 格式，包含 `code`、`msg`、`data` 字段
   - `code`: 1 表示成功，其他表示失败
   - `msg`: 提示信息
   - `data`: 返回数据

### 注意事项

1. 所有接口都需要登录后才能访问（除 `login` 和 `getVcode` 接口）
2. Token 过期或无效时返回 `code=-98` 或 `code=-99`
3. 分页查询接口支持以下参数：
   - `page`: 页码
   - `s_sizes`: 每页数量
   - `s_keyword`: 关键词搜索
   - `s_start_time`: 开始时间
   - `s_end_time`: 结束时间
4. 部分接口支持批量操作，通过 `ids` 参数传递多个ID（逗号分隔）

---

**文档说明**: 本文档通过分析项目源代码自动生成，包含了所有后台管理系统的 API 接口信息。
