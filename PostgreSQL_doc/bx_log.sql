-- =====================================================
-- PostgreSQL 日志库 (bx_log) 迁移脚本
-- 
-- 包含内容:
-- 1. 钱包流水表 (wallet_log) - 单表，分表由应用框架实现
-- 2. 收益记录表 (pro_reward) - 单表，分表由应用框架实现
--
-- 特点:
-- - 数据库层面使用单表
-- - 分表由应用层框架(ShardingSphere/自定义)实现
-- - 高写入性能优化
-- - 支持按时间归档
-- =====================================================

-- 创建数据库
CREATE DATABASE bx_log
    WITH 
    ENCODING = 'UTF8'
    LC_COLLATE = 'zh_CN.UTF-8'
    LC_CTYPE = 'zh_CN.UTF-8'
    TEMPLATE = template0;

\c bx_log;

-- 创建扩展
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "pg_stat_statements";

-- =====================================================
-- 1. 钱包流水表
-- =====================================================

/**
 * 钱包流水表
 * 分表策略: 由应用层框架实现分表
 * 
 * 类型说明:
 * 1=转入, 2=转出, 3=买入, 4=卖出, 6=佣金, 8=分佣, 9=抽奖, 10=红包
 * 11=充值, 12=冻结, 13=解冻, 14=购买NFT, 21=NFT收益, 22=直推收益
 * 23=动态收益, 31=提现, 33=退款, 42=任务奖励, 45=积分兑换, 
 * 151=循环奖励, 1019=积分
 */
CREATE TABLE wallet_log (
    id BIGSERIAL PRIMARY KEY,
    wid INTEGER DEFAULT 0,
    uid INTEGER NOT NULL,
    type SMALLINT DEFAULT 0,
    money NUMERIC(16,2) DEFAULT 0.00,
    ori_balance NUMERIC(16,2) DEFAULT 0.00,
    new_balance NUMERIC(16,2) DEFAULT 0.00,
    fkey VARCHAR(32),
    remark VARCHAR(128),
    create_id INTEGER DEFAULT 0,
    create_time INTEGER DEFAULT 0,
    create_day INTEGER DEFAULT 0,
    lasttime INTEGER DEFAULT 0
);

-- 创建索引
CREATE INDEX idx_wlog_uid ON wallet_log(uid);
CREATE INDEX idx_wlog_wid ON wallet_log(wid);
CREATE INDEX idx_wlog_type ON wallet_log(type);
CREATE INDEX idx_wlog_uid_type_day ON wallet_log(uid, type, create_day);
CREATE INDEX idx_wlog_create_time ON wallet_log USING BRIN (create_time);
CREATE INDEX idx_wlog_create_day ON wallet_log USING BRIN (create_day);

COMMENT ON TABLE wallet_log IS '钱包流水表(分表由应用层实现)';
COMMENT ON COLUMN wallet_log.type IS '流水类型: 1转入 2转出 6佣金 8分佣 10红包 11充值 31提现';

-- =====================================================
-- 2. 收益记录表
-- =====================================================

/**
 * 收益记录表
 * 分表策略: 由应用层框架实现分表
 * 
 * 类型说明:
 * 1=投资收益, 2=返佣
 */
CREATE TABLE pro_reward (
    id BIGSERIAL PRIMARY KEY,
    uid INTEGER DEFAULT 0,
    oid INTEGER DEFAULT 0,
    osn CHAR(16),
    type SMALLINT DEFAULT 0,
    level SMALLINT DEFAULT 0,
    base_money NUMERIC(16,2) DEFAULT 0.00,
    rate VARCHAR(16),
    money NUMERIC(16,2) DEFAULT 0.00,
    remark VARCHAR(255),
    create_time INTEGER DEFAULT 0,
    create_day INTEGER DEFAULT 0,
    pdig1 INTEGER DEFAULT 0,
    pdig2 INTEGER DEFAULT 0
);

-- 创建索引
CREATE INDEX idx_reward_uid ON pro_reward(uid);
CREATE INDEX idx_reward_oid ON pro_reward(oid);
CREATE INDEX idx_reward_type ON pro_reward(type);
CREATE INDEX idx_reward_uid_type_time ON pro_reward(uid, type, create_time);
CREATE INDEX idx_reward_type_money ON pro_reward(type, money);
CREATE INDEX idx_reward_day ON pro_reward USING BRIN (create_day);
CREATE INDEX idx_reward_create_time ON pro_reward USING BRIN (create_time);

COMMENT ON TABLE pro_reward IS '收益记录表(分表由应用层实现)';
COMMENT ON COLUMN pro_reward.type IS '1:投资收益 2:返佣';

-- =====================================================
-- 3. 创建统计视图
-- =====================================================

/**
 * 用户钱包流水汇总视图
 */
CREATE OR REPLACE VIEW v_wallet_log_summary AS
SELECT 
    uid,
    type,
    COUNT(*) as log_count,
    SUM(CASE WHEN money > 0 THEN money ELSE 0 END) as income,
    SUM(CASE WHEN money < 0 THEN money ELSE 0 END) as expense,
    SUM(money) as net_amount,
    MAX(create_time) as last_time
FROM wallet_log
GROUP BY uid, type;

COMMENT ON VIEW v_wallet_log_summary IS '用户钱包流水类型汇总';

/**
 * 用户收益汇总视图
 */
CREATE OR REPLACE VIEW v_user_reward_summary AS
SELECT 
    uid,
    type,
    COUNT(*) as reward_count,
    SUM(money) as total_reward,
    AVG(money) as avg_reward,
    MAX(create_time) as last_reward_time
FROM pro_reward
GROUP BY uid, type;

COMMENT ON VIEW v_user_reward_summary IS '用户收益类型汇总';

/**
 * 每日收益统计物化视图
 */
CREATE MATERIALIZED VIEW mv_daily_reward_stats AS
SELECT 
    create_day,
    type,
    COUNT(DISTINCT uid) as user_count,
    COUNT(*) as reward_count,
    SUM(money) as total_amount,
    AVG(money) as avg_amount,
    MIN(money) as min_amount,
    MAX(money) as max_amount
FROM pro_reward
GROUP BY create_day, type
ORDER BY create_day DESC, type;

CREATE UNIQUE INDEX ON mv_daily_reward_stats(create_day, type);

COMMENT ON MATERIALIZED VIEW mv_daily_reward_stats IS '每日收益统计(需定时刷新)';

/**
 * 每日钱包流水统计物化视图
 */
CREATE MATERIALIZED VIEW mv_daily_wallet_stats AS
SELECT 
    create_day,
    type,
    COUNT(DISTINCT uid) as user_count,
    COUNT(*) as log_count,
    SUM(CASE WHEN money > 0 THEN money ELSE 0 END) as total_income,
    SUM(CASE WHEN money < 0 THEN ABS(money) ELSE 0 END) as total_expense
FROM wallet_log
GROUP BY create_day, type
ORDER BY create_day DESC, type;

CREATE UNIQUE INDEX ON mv_daily_wallet_stats(create_day, type);

COMMENT ON MATERIALIZED VIEW mv_daily_wallet_stats IS '每日钱包流水统计(需定时刷新)';

-- =====================================================
-- 4. 创建辅助函数
-- =====================================================

/**
 * 获取用户指定类型的钱包流水总额
 */
CREATE OR REPLACE FUNCTION get_user_wallet_log_sum(
    p_uid INTEGER,
    p_type INTEGER DEFAULT NULL
)
RETURNS NUMERIC AS $$
BEGIN
    IF p_type IS NULL THEN
        RETURN COALESCE(
            (SELECT SUM(money) FROM wallet_log WHERE uid = p_uid),
            0
        );
    ELSE
        RETURN COALESCE(
            (SELECT SUM(money) FROM wallet_log WHERE uid = p_uid AND type = p_type),
            0
        );
    END IF;
END;
$$ LANGUAGE plpgsql STABLE;

/**
 * 获取用户总收益
 */
CREATE OR REPLACE FUNCTION get_user_total_reward(
    p_uid INTEGER,
    p_type INTEGER DEFAULT NULL
)
RETURNS NUMERIC AS $$
BEGIN
    IF p_type IS NULL THEN
        RETURN COALESCE(
            (SELECT SUM(money) FROM pro_reward WHERE uid = p_uid),
            0
        );
    ELSE
        RETURN COALESCE(
            (SELECT SUM(money) FROM pro_reward WHERE uid = p_uid AND type = p_type),
            0
        );
    END IF;
END;
$$ LANGUAGE plpgsql STABLE;

/**
 * 获取用户今日收益
 */
CREATE OR REPLACE FUNCTION get_user_today_reward(p_uid INTEGER)
RETURNS NUMERIC AS $$
DECLARE
    today INTEGER;
BEGIN
    today := TO_CHAR(CURRENT_DATE, 'YYYYMMDD')::INTEGER;
    RETURN COALESCE(
        (SELECT SUM(money) FROM pro_reward 
         WHERE uid = p_uid AND create_day = today),
        0
    );
END;
$$ LANGUAGE plpgsql STABLE;

/**
 * 获取用户指定日期范围的收益
 */
CREATE OR REPLACE FUNCTION get_user_period_reward(
    p_uid INTEGER,
    p_start_day INTEGER,
    p_end_day INTEGER
)
RETURNS NUMERIC AS $$
BEGIN
    RETURN COALESCE(
        (SELECT SUM(money) FROM pro_reward 
         WHERE uid = p_uid 
         AND create_day >= p_start_day 
         AND create_day <= p_end_day),
        0
    );
END;
$$ LANGUAGE plpgsql STABLE;

-- =====================================================
-- 5. 创建触发器
-- =====================================================

/**
 * 钱包流水插入前触发器 - 自动填充create_day
 */
CREATE OR REPLACE FUNCTION fn_wallet_log_before_insert()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.create_day = 0 OR NEW.create_day IS NULL THEN
        NEW.create_day := TO_CHAR(
            TO_TIMESTAMP(NEW.create_time), 
            'YYYYMMDD'
        )::INTEGER;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_wallet_log_before_insert
    BEFORE INSERT ON wallet_log
    FOR EACH ROW
    EXECUTE FUNCTION fn_wallet_log_before_insert();

/**
 * 收益记录插入前触发器 - 自动填充create_day
 */
CREATE OR REPLACE FUNCTION fn_reward_before_insert()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.create_day = 0 OR NEW.create_day IS NULL THEN
        NEW.create_day := TO_CHAR(
            TO_TIMESTAMP(NEW.create_time), 
            'YYYYMMDD'
        )::INTEGER;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_reward_before_insert
    BEFORE INSERT ON pro_reward
    FOR EACH ROW
    EXECUTE FUNCTION fn_reward_before_insert();

-- =====================================================
-- 6. 数据归档方案
-- =====================================================

/**
 * 创建归档表(存储旧数据)
 */
CREATE TABLE wallet_log_archive (
    LIKE wallet_log INCLUDING ALL
);

CREATE TABLE pro_reward_archive (
    LIKE pro_reward INCLUDING ALL
);

-- 为归档表创建索引
CREATE INDEX idx_wlog_arch_uid ON wallet_log_archive(uid);
CREATE INDEX idx_wlog_arch_day ON wallet_log_archive USING BRIN (create_day);

CREATE INDEX idx_reward_arch_uid ON pro_reward_archive(uid);
CREATE INDEX idx_reward_arch_day ON pro_reward_archive USING BRIN (create_day);

/**
 * 归档旧数据函数
 */
CREATE OR REPLACE FUNCTION archive_old_logs(
    p_days_to_keep INTEGER DEFAULT 180
)
RETURNS TABLE(
    archived_wallet_logs BIGINT,
    archived_rewards BIGINT
) AS $$
DECLARE
    cutoff_day INTEGER;
    wallet_count BIGINT;
    reward_count BIGINT;
BEGIN
    -- 计算截止日期
    cutoff_day := TO_CHAR(
        CURRENT_DATE - INTERVAL '1 day' * p_days_to_keep,
        'YYYYMMDD'
    )::INTEGER;
    
    -- 归档钱包流水
    WITH moved AS (
        DELETE FROM wallet_log
        WHERE create_day < cutoff_day
        RETURNING *
    )
    INSERT INTO wallet_log_archive
    SELECT * FROM moved;
    
    GET DIAGNOSTICS wallet_count = ROW_COUNT;
    
    -- 归档收益记录
    WITH moved AS (
        DELETE FROM pro_reward
        WHERE create_day < cutoff_day
        RETURNING *
    )
    INSERT INTO pro_reward_archive
    SELECT * FROM moved;
    
    GET DIAGNOSTICS reward_count = ROW_COUNT;
    
    RETURN QUERY SELECT wallet_count, reward_count;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION archive_old_logs IS '归档超过指定天数的日志数据';

-- =====================================================
-- 7. 性能优化配置
-- =====================================================

-- 为高频更新的表设置较低的fillfactor
ALTER TABLE wallet_log SET (
    fillfactor = 80,
    autovacuum_vacuum_scale_factor = 0.05
);

ALTER TABLE pro_reward SET (
    fillfactor = 85,
    autovacuum_vacuum_scale_factor = 0.1
);

-- =====================================================
-- 8. 性能监控视图
-- =====================================================

/**
 * 表性能监控
 */
CREATE OR REPLACE VIEW v_table_performance AS
SELECT 
    schemaname,
    tablename,
    n_tup_ins as inserts,
    n_tup_upd as updates,
    n_tup_del as deletes,
    n_live_tup as live_tuples,
    n_dead_tup as dead_tuples,
    ROUND(100.0 * n_dead_tup / NULLIF(n_live_tup + n_dead_tup, 0), 2) AS dead_ratio,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size,
    last_vacuum,
    last_autovacuum,
    last_analyze,
    last_autoanalyze
FROM pg_stat_user_tables
WHERE tablename IN ('wallet_log', 'pro_reward')
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

COMMENT ON VIEW v_table_performance IS '表性能监控视图';

/**
 * 慢查询分析
 */
CREATE OR REPLACE VIEW v_slow_queries AS
SELECT 
    calls,
    total_exec_time,
    mean_exec_time,
    max_exec_time,
    ROUND((100 * total_exec_time / SUM(total_exec_time) OVER ())::numeric, 2) AS percentage,
    query
FROM pg_stat_statements
WHERE query LIKE '%wallet_log%' 
   OR query LIKE '%pro_reward%'
ORDER BY total_exec_time DESC
LIMIT 20;

COMMENT ON VIEW v_slow_queries IS '慢查询统计TOP20';

-- =====================================================
-- 9. 定时任务
-- =====================================================

/**
 * 使用pg_cron创建定时任务(需要安装pg_cron扩展)
 */
-- 每天凌晨2点刷新物化视图
-- SELECT cron.schedule('refresh-log-stats', '0 2 * * *',
--     $$
--     REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_reward_stats;
--     REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_wallet_stats;
--     $$
-- );

-- 每月1号凌晨3点归档6个月前的数据
-- SELECT cron.schedule('archive-old-logs', '0 3 1 * *',
--     'SELECT archive_old_logs(180);'
-- );

-- =====================================================
-- 脚本结束
-- =====================================================

-- 显示表信息
SELECT 
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'public'
AND tablename IN ('wallet_log', 'pro_reward')
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

COMMENT ON DATABASE bx_log IS 'BX平台日志库 - 钱包流水和收益记录(分表由应用层实现)';
