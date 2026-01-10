-- =====================================================
-- PostgreSQL 交易库 (bx_trade) 迁移脚本
-- 
-- 包含内容:
-- 1. 产品订单表 (pro_order) - 单表，分表由应用框架实现
-- 2. 用户进度表 (User_Product_Progress, Invitations)
-- 3. 审核表 (pro_audit)
--
-- 特点:
-- - 数据库层面使用单表
-- - 分表由应用层框架实现
-- - 高并发下单优化
-- =====================================================

-- 创建数据库
CREATE DATABASE bx_trade
    WITH 
    ENCODING = 'UTF8'
    LC_COLLATE = 'zh_CN.UTF-8'
    LC_CTYPE = 'zh_CN.UTF-8'
    TEMPLATE = template0;

\c bx_trade;

-- 创建扩展
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "pg_stat_statements";

-- =====================================================
-- 1. 产品订单表
-- =====================================================

/**
 * 产品订单表
 * 分表策略: 由应用层框架实现分表(推荐按月或按uid)
 * 
 * 状态说明:
 * 1:进行中 2:已暂停 3:已完成 9:已结束
 */
CREATE TABLE pro_order (
    id SERIAL PRIMARY KEY,
    osn CHAR(16) NOT NULL UNIQUE,
    cid INTEGER DEFAULT 0,
    gid INTEGER,
    uid INTEGER NOT NULL,
    days INTEGER DEFAULT 0,
    price NUMERIC(16,2) DEFAULT 0.00,
    price1 NUMERIC(16,2),
    price2 NUMERIC(16,2),
    price0 NUMERIC(16,2),
    p1 INTEGER DEFAULT 0,
    p2 INTEGER DEFAULT 0,
    p3 INTEGER DEFAULT 0,
    num INTEGER DEFAULT 0,
    money NUMERIC(16,2) DEFAULT 0.00,
    rate VARCHAR(16),
    discount NUMERIC(16,2) DEFAULT 1.00,
    w1_money NUMERIC(16,2) DEFAULT 0.00,
    w2_money NUMERIC(16,2) DEFAULT 0.00,
    is_give SMALLINT DEFAULT 0,
    status SMALLINT DEFAULT 1,
    create_day INTEGER DEFAULT 0,
    create_time INTEGER NOT NULL,
    create_id INTEGER DEFAULT 0,
    create_ip VARCHAR(16),
    reward_day INTEGER DEFAULT 0,
    reward_time INTEGER DEFAULT 0,
    total_reward NUMERIC(16,2) DEFAULT 0.00,
    total_days INTEGER DEFAULT 0,
    pid INTEGER,
    is_exchange INTEGER,
    sign TEXT
);

-- 创建索引
CREATE INDEX idx_order_osn ON pro_order(osn);
CREATE INDEX idx_order_uid ON pro_order(uid);
CREATE INDEX idx_order_gid ON pro_order(gid);
CREATE INDEX idx_order_status ON pro_order(status) WHERE status < 99;
CREATE INDEX idx_order_uid_status_day ON pro_order(uid, status, create_day);
CREATE INDEX idx_order_day_status ON pro_order(create_day, status);
CREATE INDEX idx_order_reward_day ON pro_order(reward_day) WHERE status = 1;
CREATE INDEX idx_order_create_time ON pro_order USING BRIN (create_time);
CREATE INDEX idx_order_create_day ON pro_order USING BRIN (create_day);

COMMENT ON TABLE pro_order IS '产品订单表(分表由应用层实现)';
COMMENT ON COLUMN pro_order.is_give IS '0:正常订单 1:赠送订单';
COMMENT ON COLUMN pro_order.status IS '1:进行中 9:已完成';

-- =====================================================
-- 2. 邀请和进度相关表
-- =====================================================

/**
 * 邀请记录表
 */
CREATE TABLE invitations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    invited_user_id INTEGER NOT NULL,
    invitation_date DATE NOT NULL,
    reward NUMERIC(16,2) DEFAULT 0.00,
    create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_invitation_user ON invitations(user_id);
CREATE INDEX idx_invitation_product ON invitations(product_id);
CREATE INDEX idx_invitation_invited ON invitations(invited_user_id);
CREATE INDEX idx_invitation_date ON invitations(invitation_date);

COMMENT ON TABLE invitations IS '邀请记录表';

/**
 * 用户产品进度表
 */
CREATE TABLE user_product_progress (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    current_cycle INTEGER DEFAULT 1,
    current_count INTEGER DEFAULT 0,
    total_reward NUMERIC(16,2) DEFAULT 0.00,
    last_update_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, product_id)
);

CREATE INDEX idx_progress_user ON user_product_progress(user_id);
CREATE INDEX idx_progress_product ON user_product_progress(product_id);

COMMENT ON TABLE user_product_progress IS '用户产品购买进度表';

-- =====================================================
-- 3. 审核表
-- =====================================================

/**
 * 产品订单审核表
 */
CREATE TABLE pro_audit (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    touser_id INTEGER NOT NULL,
    status SMALLINT DEFAULT 0,
    time INTEGER NOT NULL,
    remark TEXT,
    type SMALLINT,
    touser_pid INTEGER,
    amount NUMERIC(16,2) DEFAULT 0.00,
    pidg1 INTEGER DEFAULT 0,
    pidg2 INTEGER DEFAULT 0,
    check_time INTEGER DEFAULT 0,
    check_id INTEGER DEFAULT 0
);

CREATE INDEX idx_audit_user ON pro_audit(user_id);
CREATE INDEX idx_audit_touser ON pro_audit(touser_id);
CREATE INDEX idx_audit_status ON pro_audit(status) WHERE status = 0;
CREATE INDEX idx_audit_time ON pro_audit USING BRIN (time);

COMMENT ON TABLE pro_audit IS '订单审核表';
COMMENT ON COLUMN pro_audit.status IS '0:待审核 1:已通过 2:已拒绝';

-- =====================================================
-- 4. 创建触发器
-- =====================================================

/**
 * 订单插入前触发器 - 自动填充字段
 */
CREATE OR REPLACE FUNCTION fn_order_before_insert()
RETURNS TRIGGER AS $$
BEGIN
    -- 自动填充create_day
    IF NEW.create_day = 0 OR NEW.create_day IS NULL THEN
        NEW.create_day := TO_CHAR(
            TO_TIMESTAMP(NEW.create_time), 
            'YYYYMMDD'
        )::INTEGER;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_order_before_insert
    BEFORE INSERT ON pro_order
    FOR EACH ROW
    EXECUTE FUNCTION fn_order_before_insert();

/**
 * 订单更新触发器 - 记录状态变更
 */
CREATE OR REPLACE FUNCTION fn_order_status_change()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.status != NEW.status THEN
        -- 记录状态变更日志
        INSERT INTO pro_order_status_log (
            order_id, old_status, new_status, change_time
        ) VALUES (
            NEW.id, OLD.status, NEW.status, 
            EXTRACT(EPOCH FROM CURRENT_TIMESTAMP)::INTEGER
        );
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

/**
 * 订单状态变更日志表
 */
CREATE TABLE pro_order_status_log (
    id BIGSERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    old_status SMALLINT,
    new_status SMALLINT,
    change_time INTEGER NOT NULL,
    remark TEXT
);

CREATE INDEX idx_order_slog_oid ON pro_order_status_log(order_id);
CREATE INDEX idx_order_slog_time ON pro_order_status_log USING BRIN (change_time);

CREATE TRIGGER trg_order_status_change
    AFTER UPDATE ON pro_order
    FOR EACH ROW
    WHEN (OLD.status IS DISTINCT FROM NEW.status)
    EXECUTE FUNCTION fn_order_status_change();

-- =====================================================
-- 5. 创建统计视图
-- =====================================================

/**
 * 用户订单统计视图
 */
CREATE OR REPLACE VIEW v_user_order_stats AS
SELECT 
    uid,
    COUNT(*) as total_orders,
    SUM(money) as total_amount,
    SUM(w1_money + w2_money) as total_paid,
    SUM(total_reward) as total_reward,
    COUNT(CASE WHEN status = 1 THEN 1 END) as active_orders,
    COUNT(CASE WHEN status = 9 THEN 1 END) as completed_orders,
    MIN(create_time) as first_order_time,
    MAX(create_time) as last_order_time
FROM pro_order
WHERE status < 99
GROUP BY uid;

COMMENT ON VIEW v_user_order_stats IS '用户订单统计视图';

/**
 * 产品销售统计视图
 */
CREATE OR REPLACE VIEW v_product_sales_stats AS
SELECT 
    gid,
    COUNT(*) as order_count,
    SUM(num) as total_quantity,
    SUM(money) as total_amount,
    COUNT(DISTINCT uid) as buyer_count,
    AVG(money) as avg_order_amount,
    MIN(create_time) as first_sale_time,
    MAX(create_time) as last_sale_time
FROM pro_order
WHERE status < 99 AND is_give = 0
GROUP BY gid;

COMMENT ON VIEW v_product_sales_stats IS '产品销售统计视图';

/**
 * 每日订单汇总物化视图
 */
CREATE MATERIALIZED VIEW mv_daily_order_summary AS
SELECT 
    create_day,
    COUNT(*) as order_count,
    COUNT(DISTINCT uid) as buyer_count,
    SUM(money) as total_amount,
    SUM(w1_money) as w1_total,
    SUM(w2_money) as w2_total,
    SUM(num) as total_quantity,
    AVG(money) as avg_amount,
    COUNT(CASE WHEN is_give = 0 THEN 1 END) as normal_orders,
    COUNT(CASE WHEN is_give = 1 THEN 1 END) as gift_orders,
    SUM(CASE WHEN p3 = 1 THEN money ELSE 0 END) as first_buy_amount,
    COUNT(CASE WHEN p3 = 1 THEN 1 END) as first_buy_count
FROM pro_order
WHERE status < 99
GROUP BY create_day
ORDER BY create_day DESC;

CREATE UNIQUE INDEX ON mv_daily_order_summary(create_day);

COMMENT ON MATERIALIZED VIEW mv_daily_order_summary IS '每日订单汇总';

/**
 * 需要发放收益的订单视图
 */
CREATE OR REPLACE VIEW v_orders_need_reward AS
SELECT 
    id,
    osn,
    uid,
    gid,
    money,
    rate,
    days,
    total_days,
    total_reward,
    reward_day,
    create_day
FROM pro_order
WHERE status = 1
AND (
    (reward_day > 0 AND reward_day < TO_CHAR(CURRENT_DATE, 'YYYYMMDD')::INTEGER)
    OR (reward_day = 0 AND create_day < TO_CHAR(CURRENT_DATE, 'YYYYMMDD')::INTEGER)
)
AND total_days < days;

COMMENT ON VIEW v_orders_need_reward IS '需要发放收益的订单';

-- =====================================================
-- 6. 创建辅助函数
-- =====================================================

/**
 * 获取用户订单总额
 */
CREATE OR REPLACE FUNCTION get_user_total_order(p_uid INTEGER)
RETURNS NUMERIC AS $$
BEGIN
    RETURN COALESCE(
        (SELECT SUM(money) FROM pro_order 
         WHERE uid = p_uid AND status < 99 AND is_give = 0),
        0
    );
END;
$$ LANGUAGE plpgsql STABLE;

/**
 * 获取用户有效订单数
 */
CREATE OR REPLACE FUNCTION get_user_active_orders(p_uid INTEGER)
RETURNS INTEGER AS $$
BEGIN
    RETURN COALESCE(
        (SELECT COUNT(*) FROM pro_order 
         WHERE uid = p_uid AND status = 1),
        0
    );
END;
$$ LANGUAGE plpgsql STABLE;

/**
 * 检查用户是否首次购买
 */
CREATE OR REPLACE FUNCTION is_first_purchase(
    p_uid INTEGER,
    p_gid INTEGER DEFAULT NULL
)
RETURNS BOOLEAN AS $$
DECLARE
    order_count INTEGER;
BEGIN
    IF p_gid IS NULL THEN
        SELECT COUNT(*) INTO order_count
        FROM pro_order
        WHERE uid = p_uid AND is_give = 0;
    ELSE
        SELECT COUNT(*) INTO order_count
        FROM pro_order
        WHERE uid = p_uid AND gid = p_gid AND is_give = 0;
    END IF;
    
    RETURN order_count = 0;
END;
$$ LANGUAGE plpgsql STABLE;

-- =====================================================
-- 7. 数据归档
-- =====================================================

/**
 * 订单归档表
 */
CREATE TABLE pro_order_archive (
    LIKE pro_order INCLUDING ALL
);

-- 为归档表创建索引
CREATE INDEX idx_order_arch_uid ON pro_order_archive(uid);
CREATE INDEX idx_order_arch_day ON pro_order_archive USING BRIN (create_day);

/**
 * 归档已完成订单
 */
CREATE OR REPLACE FUNCTION archive_completed_orders(
    p_months_ago INTEGER DEFAULT 12
)
RETURNS BIGINT AS $$
DECLARE
    cutoff_day INTEGER;
    archived_count BIGINT;
BEGIN
    cutoff_day := TO_CHAR(
        CURRENT_DATE - INTERVAL '1 month' * p_months_ago,
        'YYYYMMDD'
    )::INTEGER;
    
    WITH moved AS (
        DELETE FROM pro_order
        WHERE create_day < cutoff_day 
        AND status = 9
        AND total_days >= days
        RETURNING *
    )
    INSERT INTO pro_order_archive
    SELECT * FROM moved;
    
    GET DIAGNOSTICS archived_count = ROW_COUNT;
    
    RETURN archived_count;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION archive_completed_orders IS '归档已完成的订单';

-- =====================================================
-- 8. 性能监控
-- =====================================================

/**
 * 订单性能监控视图
 */
CREATE OR REPLACE VIEW v_order_performance AS
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
WHERE tablename LIKE 'pro_order%'
ORDER BY n_dead_tup DESC;

COMMENT ON VIEW v_order_performance IS '订单表性能监控';

-- =====================================================
-- 9. 性能优化配置
-- =====================================================

ALTER TABLE pro_order SET (
    fillfactor = 85,
    autovacuum_vacuum_scale_factor = 0.1
);

ALTER TABLE user_product_progress SET (
    fillfactor = 70,
    autovacuum_vacuum_scale_factor = 0.05
);

-- =====================================================
-- 10. 定时任务
-- =====================================================

/**
 * 使用pg_cron创建定时任务
 */
-- 每天凌晨4点刷新物化视图
-- SELECT cron.schedule('refresh-order-summary', '0 4 * * *',
--     'REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_order_summary;'
-- );

-- 每月1号凌晨5点归档12个月前的已完成订单
-- SELECT cron.schedule('archive-orders', '0 5 1 * *',
--     'SELECT archive_completed_orders(12);'
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
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

-- 显示今日订单统计
SELECT 
    COUNT(*) as today_orders,
    COUNT(DISTINCT uid) as today_buyers,
    SUM(money) as today_amount,
    AVG(money) as avg_amount
FROM pro_order
WHERE create_day = TO_CHAR(CURRENT_DATE, 'YYYYMMDD')::INTEGER;

COMMENT ON DATABASE bx_trade IS 'BX平台交易库 - 产品订单(分表由应用层实现)';
