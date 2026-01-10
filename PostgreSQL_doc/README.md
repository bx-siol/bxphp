# BX平台 PostgreSQL 数据库迁移文档

## 📋 文档总览

本目录包含从MySQL迁移到PostgreSQL的完整方案,包括5个分库的建表脚本和详细的实施指南。

**重要说明**: 
- **分库**: 由数据库层面实现,已完成
- **分表**: 由应用层框架实现(如ShardingSphere、自定义路由等)

## 🗂️ 文件列表

| 文件名 | 说明 | 用途 |
|--------|------|------|
| `00_分库分表架构设计.md` | 整体架构设计文档 | 了解分库策略和性能优化方案 |
| `bx_core.sql` | 核心业务库脚本 | 用户、产品、配置等核心数据 |
| `bx_finance.sql` | 财务库脚本 | 充值、提现记录(单表) |
| `bx_log.sql` | 日志库脚本 | 钱包流水、收益记录(单表) |
| `bx_marketing.sql` | 营销库脚本 | 红包、抽奖、优惠券等活动 |
| `bx_trade.sql` | 交易库脚本 | 产品订单(单表) |
| `99_分库分表路由实现指南.md` | PHP路由实现指南 | 应用层如何实现分库路由 |

## 🚀 快速开始

### 1. 环境准备

```bash
# Windows环境下,安装PostgreSQL 14+
# 下载地址: https://www.postgresql.org/download/windows/

# 或使用包管理器
choco install postgresql

# 设置密码为 admin8
```

### 2. 执行迁移脚本

按以下顺序执行SQL脚本:

```bash
# Windows PowerShell
cd d:\code\php\bxphp\PostgreSQL_doc

# 1. 核心业务库
psql -U postgres -f bx_core.sql

# 2. 财务库
psql -U postgres -f bx_finance.sql

# 3. 日志库
psql -U postgres -f bx_log.sql

# 4. 营销库
psql -U postgres -f bx_marketing.sql

# 5. 交易库
psql -U postgres -f bx_trade.sql
```

### 3. 验证安装

```sql
-- 连接到PostgreSQL
psql -U postgres

-- 查看所有数据库
\l

-- 应该看到以下数据库:
-- bx_core
-- bx_finance
-- bx_log
-- bx_marketing
-- bx_trade

-- 查看某个数据库的表
\c bx_core
\dt
```

## 🏗️ 架构设计

### 分库策略 (✅已实现)

将数据按业务模块拆分为 5 个独立数据库:

| 数据库 | 用途 | 核心表 |
|--------|------|--------|
| **bx_core** | 核心业务 | 用户、产品、钱包、消息、系统配置 |
| **bx_finance** | 财务 | 充值记录、提现记录、财务类型 |
| **bx_log** | 日志 | 钱包流水、收益记录 |
| **bx_marketing** | 营销 | 红包、抽奖、优惠券 |
| **bx_trade** | 交易 | 产品订单、邀请记录 |

### 分表策略 (⚠️应用层实现)

**数据库层面使用单表，分表由应用层框架实现。**

#### 推荐的分表方案（供参考）

**按用户ID哈希分表**:
- `wallet_log`: 钱包流水表（推荐16-32个分表）
- `pro_reward`: 收益记录表（推荐16-32个分表）

**按时间范围分表**:
- `fin_paylog`: 充值记录表（推荐按月分表）
- `fin_cashlog`: 提现记录表（推荐按月分表）
- `pro_order`: 产品订单表（推荐按月分表）

#### 实现方式

1. **ShardingSphere-JDBC** (推荐)
   - 功能强大，支持分库分表、读写分离
   - 配置灵活，对代码侵入小
   - 适合中大型项目

2. **PHP自定义路由**
   - 轻量级，易于控制
   - 适合小型项目或特殊需求
   - 参考 `99_分库分表路由实现指南.md`

3. **Citus扩展**
   - PostgreSQL原生分布式方案
   - 适合超大规模数据场景

## ✨ 主要特性

### 数据库层面
- ✅ **分库设计**: 5个独立业务库,降低单库压力
- ✅ **索引优化**: BRIN、GIN、复合索引
- ✅ **物化视图**: 预计算统计数据,加速报表查询
- ✅ **触发器**: 自动维护数据一致性
- ✅ **审计日志**: 完整的操作记录
- ✅ **数据归档**: 历史数据归档方案
- ✅ **性能监控**: 慢查询、表膨胀等监控视图

### 应用层面
- ⚠️ **读写分离**: 主库写入,从库查询(需配置主从)
- ✅ **分库路由**: DatabaseRouter类实现库级路由
- ⚠️ **连接池**: PgBouncer 管理连接(推荐安装)
- ⚠️ **缓存层**: Redis 缓存热点数据(已有)
- ✅ **跨库聚合**: 应用层处理跨库统计
- ⚠️ **分表支持**: 可选接入ShardingSphere等框架

> 图例: ✅已完成 ⚠️需配置/可选

## 📊 性能预期

| 指标 | 优化前(MySQL) | 优化后(PostgreSQL分库) | 提升倍数 |
|------|--------------|---------------------|---------|
| 写入性能 | 1000 TPS | 3000-5000 TPS | 3-5x |
| 查询性能 | 100ms | 10-20ms | 5-10x |
| 统计报表 | 60s | 1-5s | 10-60x |
| 并发连接 | 1000 | 10000+ | 10x+ |

**关键优化点**:
- 分库降低单库压力
- 索引优化(BRIN、GIN、复合索引)
- 物化视图预计算
- 读写分离(需配置)
- 连接池(PgBouncer)

## 🔧 PHP 代码集成

### 1. 数据库配置

```php
<?php
// config/database.php
return [
    // 核心业务库
    'bx_core' => [
        'master' => [
            'type' => 'pgsql',
            'hostname' => '127.0.0.1',
            'database' => 'bx_core',
            'username' => 'postgres',
            'password' => 'admin8',
            'hostport' => '5432',
            'charset' => 'utf8',
        ],
        'slaves' => [  // 可选:从库配置
            [
                'hostname' => '192.168.1.101',
                'database' => 'bx_core',
                'username' => 'postgres',
                'password' => 'admin8',
                'hostport' => '5432',
                'charset' => 'utf8',
            ],
        ],
    ],
    
    // 财务库
    'bx_finance' => [
        'master' => [ /* 同上 */ ],
        'slaves' => [ /* 同上 */ ],
    ],
    
    // 日志库
    'bx_log' => [
        'master' => [ /* 同上 */ ],
        'slaves' => [ /* 同上 */ ],
    ],
    
    // 营销库
    'bx_marketing' => [
        'master' => [ /* 同上 */ ],
        'slaves' => [ /* 同上 */ ],
    ],
    
    // 交易库
    'bx_trade' => [
        'master' => [ /* 同上 */ ],
        'slaves' => [ /* 同上 */ ],
    ],
];
```

### 2. 路由类使用

```php
<?php
// global/lib/DatabaseRouter.class.php
require_once 'DatabaseRouter.class.php';

// ===== 示例1: 写入钱包流水 =====
$db = DatabaseRouter::getLogDb(true);  // true=使用主库
$db->table('wallet_log')->insert([
    'uid' => $uid,
    'money' => 100,
    'type' => 11,  // 充值
    'create_time' => time(),
]);

// ===== 示例2: 查询用户充值记录 =====
$db = DatabaseRouter::getFinanceDb(false);  // false=使用从库
$records = $db->table('fin_paylog')
    ->where('uid', $uid)
    ->where('status', 9)
    ->order('id', 'desc')
    ->select();

// ===== 示例3: 跨库查询 =====
// 查询用户基本信息
$userDb = DatabaseRouter::getCoreDb(false);
$user = $userDb->table('sys_user')->find($uid);

// 查询用户订单
$tradeDb = DatabaseRouter::getTradeDb(false);
$orders = $tradeDb->table('pro_order')
    ->where('uid', $uid)
    ->select();

// 应用层聚合数据
$result = [
    'user' => $user,
    'orders' => $orders,
];
```

详细使用方法请参考 `99_分库分表路由实现指南.md`

## 📋 迁移检查清单

### 基础环境准备
- [x] 1. 安装 PostgreSQL 14+
- [x] 2. 安装必要的扩展(uuid-ossp, pg_trgm, pg_stat_statements)
- [ ] 3. 配置 PostgreSQL 参数优化

### 数据库创建
- [ ] 4. 执行 `bx_core.sql` 创建核心库
- [ ] 5. 执行 `bx_finance.sql` 创建财务库
- [ ] 6. 执行 `bx_log.sql` 创建日志库
- [ ] 7. 执行 `bx_marketing.sql` 创建营销库
- [ ] 8. 执行 `bx_trade.sql` 创建交易库
- [ ] 9. 验证所有数据库和表创建成功

### 应用层改造
- [ ] 10. 更新 PHP database 配置文件(添加5个数据库配置)
- [ ] 11. 创建 DatabaseRouter 路由类
- [ ] 12. 修改业务代码(使用路由类访问数据库)
- [ ] 13. 测试所有功能模块

### 数据迁移
- [ ] 14. 备份现有MySQL数据
- [ ] 15. 使用 pgloader 或自定义脚本迁移数据
- [ ] 16. 验证数据完整性和一致性
- [ ] 17. 对比MySQL和PostgreSQL数据差异

### 性能优化 (可选)
- [ ] 18. 安装并配置 PgBouncer 连接池
- [ ] 19. 配置 PostgreSQL 主从复制(读写分离)
- [ ] 20. 配置 Redis 缓存策略
- [ ] 21. (可选)接入 ShardingSphere 实现分表

### 运维监控
- [ ] 22. 配置定时任务(物化视图刷新、数据归档)
- [ ] 23. 设置监控报警(慢查询、连接数、磁盘使用)
- [ ] 24. 性能测试与压测
- [ ] 25. 制定应急预案和回滚方案

### 上线发布
- [ ] 26. 灰度发布(小流量验证)
- [ ] 27. 全量切换
- [ ] 28. 持续监控一周

## ❓ 常见问题

### 1. 为什么不在数据库层面实现分表?

**答**: 
- **灵活性**: 应用层分表更容易调整分表策略,不需要修改数据库结构
- **可维护性**: 数据库结构更简洁,DDL操作更简单
- **框架支持**: ShardingSphere等成熟框架提供完善的分表功能
- **扩展性**: 可以根据业务发展灵活调整分表数量和策略

### 2. 如何选择分表框架?

| 框架/方案 | 优点 | 缺点 | 适用场景 |
|----------|------|------|---------|
| **ShardingSphere-JDBC** | 功能强大、社区活跃、文档完善 | 配置复杂、Java生态 | 中大型项目 |
| **PHP自定义路由** | 轻量级、易于控制、无依赖 | 需要自己实现所有逻辑 | 小型项目 |
| **Citus扩展** | 原生分布式、性能极高 | 部署复杂、学习成本高 | 超大规模 |

**推荐**: 如果有Java团队，使用ShardingSphere；否则使用PHP自定义路由。

### 3. 跨库关联查询怎么办?

**答**: PostgreSQL不同数据库间无法直接JOIN，需要在应用层处理：

1. **应用层聚合**: 分别查询再合并(推荐)
2. **数据冗余**: 适当冗余常用关联数据
3. **Redis缓存**: 缓存热点关联数据
4. **外部数据包装器**: 使用 postgres_fdw 扩展(性能较差)

### 4. 如何保证跨库事务一致性?

**答**: 
1. **同库事务**: 使用PostgreSQL原生事务(ACID保证)
2. **跨库操作**: 采用最终一致性方案
   - 消息队列 + 重试机制
   - 定时任务补偿
   - 事件溯源模式

### 5. 分表后如何查询?

**答**: 
- **使用ShardingSphere**: 对应用透明,不需要修改查询代码
- **使用自定义路由**: 需要通过路由函数确定表名

```php
// 自定义路由示例
$tableName = TableSharding::getUserTable('wallet_log', $uid, 16);
$db->table($tableName)->where('uid', $uid)->select();
```

### 6. 物化视图如何刷新?

**答**: 

```sql
-- 手动刷新
REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_finance_summary;

-- 定时刷新(使用pg_cron扩展)
SELECT cron.schedule(
    'refresh-daily-stats', 
    '0 2 * * *',  -- 每天凌晨2点
    'REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_finance_summary;'
);
```

### 7. 如何监控数据库性能?

**答**: 使用内置的监控视图：

```sql
-- 慢查询
SELECT * FROM v_slow_queries;

-- 表膨胀
SELECT * FROM v_table_performance;

-- 分区统计(日志库)
\c bx_log
SELECT * FROM v_table_performance;
```

## 📚 参考资源

- [PostgreSQL 官方文档](https://www.postgresql.org/docs/)
- [ShardingSphere 文档](https://shardingsphere.apache.org/document/current/cn/overview/)
- [PgBouncer 官方文档](https://www.pgbouncer.org/)
- [pgloader 数据迁移工具](https://pgloader.io/)

## 📞 技术支持

如遇到问题，请查阅:
1. 本目录下的 `00_分库分表架构设计.md` - 详细架构说明
2. `99_分库分表路由实现指南.md` - PHP代码实现指南
3. 各SQL脚本中的注释 - 详细的表结构说明

---

**最后更新**: 2026-01-11  
**版本**: v2.0 (仅分库版本)
