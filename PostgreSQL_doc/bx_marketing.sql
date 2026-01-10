-- =====================================================
-- PostgreSQL 营销库 (bx_marketing) 迁移脚本
-- 
-- 包含内容:
-- 1. 红包相关表 (gift_redpack, gift_redpack_detail)
-- 2. 抽奖相关表 (gift_lottery, gift_lottery_log)
-- 3. 奖品相关表 (gift_prize, gift_prize_log)
-- 4. 优惠券相关表 (coupon_list, coupon_log)
--
-- 特点:
-- - 高并发读写优化
-- - 库存扣减防超卖
-- - 支持活动数据归档
-- =====================================================

-- 创建数据库
CREATE DATABASE bx_marketing
    WITH 
    ENCODING = 'UTF8'
    LC_COLLATE = 'zh_CN.UTF-8'
    LC_CTYPE = 'zh_CN.UTF-8'
    TEMPLATE = template0;

\c bx_marketing;

-- 创建扩展
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "pg_stat_statements";

-- =====================================================
-- 1. 红包相关表
-- =====================================================

/**
 * 红包主表
 */
CREATE TABLE gift_redpack (
    id SERIAL PRIMARY KEY,
    rsn CHAR(16) UNIQUE NOT NULL,
    name VARCHAR(255),
    status SMALLINT DEFAULT 1,
    total_money NUMERIC(16,2) DEFAULT 0.00,
    quantity INTEGER DEFAULT 0,
    receive_money NUMERIC(16,2) DEFAULT 0.00,
    receive_quantity INTEGER DEFAULT 0,
    create_id INTEGER DEFAULT 0,
    create_day INTEGER DEFAULT 0,
    create_time INTEGER DEFAULT 0,
    icon VARCHAR(255)
);

CREATE INDEX idx_redpack_status ON gift_redpack(status) WHERE status = 1;
CREATE INDEX idx_redpack_create_time ON gift_redpack USING BRIN (create_time);

COMMENT ON TABLE gift_redpack IS '红包主表';
COMMENT ON COLUMN gift_redpack.status IS '1:有效 2:已抢完 9:已过期';

/**
 * 红包明细表
 */
CREATE TABLE gift_redpack_detail (
    id SERIAL PRIMARY KEY,
    rid INTEGER NOT NULL,
    rsn CHAR(16),
    dsn CHAR(16) UNIQUE,
    money NUMERIC(16,2),
    create_time INTEGER DEFAULT 0,
    uid INTEGER DEFAULT 0,
    receive_time INTEGER DEFAULT 0,
    receive_day INTEGER DEFAULT 0,
    receive_ip VARCHAR(16),
    FOREIGN KEY (rid) REFERENCES gift_redpack(id) ON DELETE CASCADE
);

CREATE INDEX idx_redpack_detail_rid ON gift_redpack_detail(rid);
CREATE INDEX idx_redpack_detail_rsn ON gift_redpack_detail(rsn);
CREATE INDEX idx_redpack_detail_uid ON gift_redpack_detail(uid) WHERE uid > 0;
CREATE INDEX idx_redpack_detail_receive_day ON gift_redpack_detail(receive_day) WHERE receive_day > 0;

COMMENT ON TABLE gift_redpack_detail IS '红包明细表';

-- =====================================================
-- 2. 抽奖相关表
-- =====================================================

/**
 * 抽奖活动表
 */
CREATE TABLE gift_lottery (
    id SERIAL PRIMARY KEY,
    rsn CHAR(16) UNIQUE,
    from_money NUMERIC(16,2) DEFAULT 0.00,
    to_money NUMERIC(16,2) DEFAULT 0.00,
    stock_money NUMERIC(16,2) DEFAULT 0.00,
    receive_money NUMERIC(16,2) DEFAULT 0.00,
    receive_num INTEGER DEFAULT 0,
    day_limit INTEGER DEFAULT 0,
    week_limit INTEGER DEFAULT 0,
    lottery_min INTEGER DEFAULT 0,
    status SMALLINT DEFAULT 1,
    create_time INTEGER
);

CREATE INDEX idx_lottery_status ON gift_lottery(status) WHERE status = 1;

COMMENT ON TABLE gift_lottery IS '抽奖活动表';

/**
 * 抽奖记录表
 */
CREATE TABLE gift_lottery_log (
    id SERIAL PRIMARY KEY,
    uid INTEGER,
    rid INTEGER,
    rsn CHAR(16),
    lsn CHAR(16),
    money NUMERIC(16,2) DEFAULT 0.00,
    create_day INTEGER DEFAULT 0,
    create_week INTEGER DEFAULT 0,
    create_time INTEGER DEFAULT 0,
    create_ip VARCHAR(16)
);

CREATE INDEX idx_lottery_log_uid ON gift_lottery_log(uid);
CREATE INDEX idx_lottery_log_rid ON gift_lottery_log(rid);
CREATE INDEX idx_lottery_log_rsn ON gift_lottery_log(rsn);
CREATE INDEX idx_lottery_log_day_uid ON gift_lottery_log(create_day, uid);

COMMENT ON TABLE gift_lottery_log IS '抽奖记录表';

-- =====================================================
-- 3. 奖品相关表
-- =====================================================

/**
 * 奖品配置表
 */
CREATE TABLE gift_prize (
    id SERIAL PRIMARY KEY,
    type SMALLINT NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL DEFAULT '',
    cover VARCHAR(255) NOT NULL DEFAULT '',
    probability NUMERIC(10,6) NOT NULL DEFAULT 0.000000,
    from_money NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    to_money NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    gid INTEGER NOT NULL DEFAULT 0,
    coupon_id INTEGER NOT NULL DEFAULT 0,
    remark VARCHAR(255) NOT NULL DEFAULT '',
    buy_amount_start NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    buy_amount_end NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    create_time INTEGER NOT NULL DEFAULT 0,
    update_time INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_prize_type ON gift_prize(type);
CREATE INDEX idx_prize_gid ON gift_prize(gid) WHERE gid > 0;
CREATE INDEX idx_prize_coupon ON gift_prize(coupon_id) WHERE coupon_id > 0;
CREATE INDEX idx_prize_probability ON gift_prize(probability);

COMMENT ON TABLE gift_prize IS '奖品配置表';
COMMENT ON COLUMN gift_prize.type IS '1:余额 2:产品 3:实物 4:空 5:奖券';

/**
 * 奖品记录表
 */
CREATE TABLE gift_prize_log (
    id SERIAL PRIMARY KEY,
    uid INTEGER NOT NULL,
    type INTEGER NOT NULL,
    money NUMERIC(16,2) NOT NULL,
    gid INTEGER NOT NULL,
    coupon_id INTEGER NOT NULL,
    prize_name VARCHAR(200) NOT NULL,
    prize_cover VARCHAR(200) NOT NULL,
    remark VARCHAR(200),
    create_time INTEGER NOT NULL,
    create_day INTEGER NOT NULL,
    create_ip VARCHAR(16),
    split_time VARCHAR(50),
    order_money NUMERIC(16,2) NOT NULL,
    is_user SMALLINT NOT NULL,
    gift_prize_id INTEGER NOT NULL
);

CREATE INDEX idx_prize_log_uid ON gift_prize_log(uid);
CREATE INDEX idx_prize_log_day ON gift_prize_log(create_day);
CREATE INDEX idx_prize_log_type ON gift_prize_log(type);

COMMENT ON TABLE gift_prize_log IS '用户中奖记录表';

-- =====================================================
-- 4. 优惠券相关表
-- =====================================================

/**
 * 优惠券配置表
 */
CREATE TABLE coupon_list (
    id SERIAL PRIMARY KEY,
    qx INTEGER,
    gids TEXT,
    status INTEGER,
    type INTEGER,
    name VARCHAR(50),
    cover VARCHAR(200),
    discount NUMERIC(10,2),
    money NUMERIC(16,2),
    stock_num INTEGER,
    update_time INTEGER,
    effective_time INTEGER,
    remark VARCHAR(200)
);

CREATE INDEX idx_coupon_status ON coupon_list(status) WHERE status = 1;
CREATE INDEX idx_coupon_type ON coupon_list(type);

COMMENT ON TABLE coupon_list IS '优惠券配置表';

/**
 * 优惠券领取记录表
 */
CREATE TABLE coupon_log (
    id SERIAL PRIMARY KEY,
    gids INTEGER NOT NULL,
    cid INTEGER NOT NULL,
    type INTEGER NOT NULL,
    uid INTEGER NOT NULL,
    num INTEGER NOT NULL,
    discount NUMERIC(10,2),
    money NUMERIC(16,2) NOT NULL,
    create_time INTEGER NOT NULL,
    create_day INTEGER NOT NULL,
    create_id INTEGER NOT NULL,
    effective_time INTEGER,
    remark VARCHAR(200),
    status INTEGER,
    used INTEGER DEFAULT 0
);

CREATE INDEX idx_coupon_log_uid ON coupon_log(uid);
CREATE INDEX idx_coupon_log_cid ON coupon_log(cid);
CREATE INDEX idx_coupon_log_status ON coupon_log(status, uid) WHERE status IN (1, 2);
CREATE INDEX idx_coupon_log_effective ON coupon_log(effective_time) WHERE effective_time > 0;

COMMENT ON TABLE coupon_log IS '优惠券领取记录表';
COMMENT ON COLUMN coupon_log.status IS '1:未使用 2:部分使用 9:已使用';

/**
 * 优惠券使用记录表
 */
CREATE TABLE coupon_used (
    id SERIAL PRIMARY KEY,
    cid INTEGER NOT NULL,
    clid INTEGER NOT NULL,
    uid INTEGER NOT NULL,
    gid INTEGER NOT NULL,
    oid INTEGER NOT NULL,
    num INTEGER NOT NULL,
    discount NUMERIC(10,2),
    money NUMERIC(16,2) NOT NULL,
    create_day INTEGER NOT NULL,
    create_time INTEGER NOT NULL
);

CREATE INDEX idx_coupon_used_uid ON coupon_used(uid);
CREATE INDEX idx_coupon_used_clid ON coupon_used(clid);
CREATE INDEX idx_coupon_used_oid ON coupon_used(oid);

COMMENT ON TABLE coupon_used IS '优惠券使用记录表';

-- =====================================================
-- 5. 创建触发器 - 防超卖
-- =====================================================

/**
 * 红包领取触发器 - 防超领
 */
CREATE OR REPLACE FUNCTION fn_redpack_detail_check()
RETURNS TRIGGER AS $$
DECLARE
    v_redpack RECORD;
BEGIN
    -- 锁定红包记录
    SELECT * INTO v_redpack
    FROM gift_redpack
    WHERE id = NEW.rid
    FOR UPDATE;
    
    -- 检查是否还有剩余
    IF v_redpack.receive_quantity >= v_redpack.quantity THEN
        RAISE EXCEPTION '红包已抢完';
    END IF;
    
    -- 更新红包统计
    UPDATE gift_redpack
    SET 
        receive_quantity = receive_quantity + 1,
        receive_money = receive_money + NEW.money,
        status = CASE 
            WHEN receive_quantity + 1 >= quantity THEN 2
            ELSE status
        END
    WHERE id = NEW.rid;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_redpack_detail_check
    BEFORE INSERT ON gift_redpack_detail
    FOR EACH ROW
    WHEN (NEW.uid > 0)
    EXECUTE FUNCTION fn_redpack_detail_check();

/**
 * 优惠券领取触发器 - 库存检查
 */
CREATE OR REPLACE FUNCTION fn_coupon_log_check()
RETURNS TRIGGER AS $$
DECLARE
    v_coupon RECORD;
BEGIN
    SELECT * INTO v_coupon
    FROM coupon_list
    WHERE id = NEW.cid
    FOR UPDATE;
    
    IF v_coupon.stock_num IS NOT NULL AND v_coupon.stock_num <= 0 THEN
        RAISE EXCEPTION '优惠券库存不足';
    END IF;
    
    IF v_coupon.stock_num IS NOT NULL THEN
        UPDATE coupon_list
        SET stock_num = stock_num - NEW.num
        WHERE id = NEW.cid;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_coupon_log_check
    BEFORE INSERT ON coupon_log
    FOR EACH ROW
    EXECUTE FUNCTION fn_coupon_log_check();

-- =====================================================
-- 6. 创建抽奖相关函数
-- =====================================================

/**
 * 抽奖函数
 */
CREATE OR REPLACE FUNCTION do_lottery(
    p_uid INTEGER,
    p_rsn CHAR(16)
)
RETURNS TABLE(
    success BOOLEAN,
    message TEXT,
    prize_id INTEGER,
    prize_money NUMERIC
) AS $$
DECLARE
    v_lottery RECORD;
    v_prize RECORD;
    v_today INTEGER;
    v_week INTEGER;
    v_today_count INTEGER;
    v_week_count INTEGER;
    v_random INTEGER;
    v_total_prob INTEGER := 0;
    v_current_prob INTEGER := 0;
BEGIN
    -- 获取抽奖活动信息
    SELECT * INTO v_lottery
    FROM gift_lottery
    WHERE rsn = p_rsn AND status = 1
    FOR UPDATE;
    
    IF NOT FOUND THEN
        RETURN QUERY SELECT FALSE, '活动不存在或已结束'::TEXT, 0, 0::NUMERIC;
        RETURN;
    END IF;
    
    -- 检查库存
    IF v_lottery.receive_money >= v_lottery.stock_money THEN
        RETURN QUERY SELECT FALSE, '奖池已空'::TEXT, 0, 0::NUMERIC;
        RETURN;
    END IF;
    
    -- 检查今日/本周限制
    v_today := TO_CHAR(CURRENT_DATE, 'YYYYMMDD')::INTEGER;
    v_week := EXTRACT(WEEK FROM CURRENT_DATE)::INTEGER;
    
    IF v_lottery.day_limit > 0 THEN
        SELECT COUNT(*) INTO v_today_count
        FROM gift_lottery_log
        WHERE uid = p_uid AND rid = v_lottery.id AND create_day = v_today;
        
        IF v_today_count >= v_lottery.day_limit THEN
            RETURN QUERY SELECT FALSE, '今日抽奖次数已用完'::TEXT, 0, 0::NUMERIC;
            RETURN;
        END IF;
    END IF;
    
    IF v_lottery.week_limit > 0 THEN
        SELECT COUNT(*) INTO v_week_count
        FROM gift_lottery_log
        WHERE uid = p_uid AND rid = v_lottery.id AND create_week = v_week;
        
        IF v_week_count >= v_lottery.week_limit THEN
            RETURN QUERY SELECT FALSE, '本周抽奖次数已用完'::TEXT, 0, 0::NUMERIC;
            RETURN;
        END IF;
    END IF;
    
    -- 抽奖逻辑(简化版,实际应该更复杂)
    v_random := floor(random() * (v_lottery.to_money - v_lottery.from_money) + v_lottery.from_money)::NUMERIC;
    
    -- 记录抽奖结果
    INSERT INTO gift_lottery_log (
        uid, rid, rsn, lsn, money, create_day, create_week, create_time
    ) VALUES (
        p_uid,
        v_lottery.id,
        p_rsn,
        uuid_generate_v4()::TEXT,
        v_random,
        v_today,
        v_week,
        EXTRACT(EPOCH FROM CURRENT_TIMESTAMP)::INTEGER
    );
    
    -- 更新抽奖活动统计
    UPDATE gift_lottery
    SET 
        receive_money = receive_money + v_random,
        receive_num = receive_num + 1
    WHERE id = v_lottery.id;
    
    RETURN QUERY SELECT TRUE, '抽奖成功'::TEXT, v_lottery.id, v_random;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION do_lottery IS '执行抽奖';

-- =====================================================
-- 7. 创建统计视图
-- =====================================================

/**
 * 红包统计视图
 */
CREATE OR REPLACE VIEW v_redpack_stats AS
SELECT 
    r.id,
    r.rsn,
    r.name,
    r.total_money,
    r.quantity,
    r.receive_money,
    r.receive_quantity,
    ROUND(r.receive_money / NULLIF(r.total_money, 0) * 100, 2) as receive_percent,
    ROUND(r.receive_quantity::NUMERIC / NULLIF(r.quantity, 0) * 100, 2) as quantity_percent,
    r.status,
    r.create_time,
    COUNT(DISTINCT d.uid) as user_count
FROM gift_redpack r
LEFT JOIN gift_redpack_detail d ON r.id = d.rid AND d.uid > 0
GROUP BY r.id, r.rsn, r.name, r.total_money, r.quantity, r.receive_money, 
         r.receive_quantity, r.status, r.create_time;

COMMENT ON VIEW v_redpack_stats IS '红包活动统计视图';

/**
 * 优惠券使用率统计
 */
CREATE OR REPLACE VIEW v_coupon_usage_stats AS
SELECT 
    cl.id,
    cl.name,
    cl.type,
    COUNT(DISTINCT clog.uid) as received_users,
    SUM(clog.num) as total_received,
    SUM(clog.used) as total_used,
    ROUND(SUM(clog.used)::NUMERIC / NULLIF(SUM(clog.num), 0) * 100, 2) as usage_rate,
    SUM(CASE WHEN clog.status = 9 THEN clog.num ELSE 0 END) as fully_used_count
FROM coupon_list cl
LEFT JOIN coupon_log clog ON cl.id = clog.cid
GROUP BY cl.id, cl.name, cl.type;

COMMENT ON VIEW v_coupon_usage_stats IS '优惠券使用率统计';

/**
 * 每日营销活动汇总物化视图
 */
CREATE MATERIALIZED VIEW mv_daily_marketing_summary AS
SELECT 
    create_day,
    -- 红包数据
    COUNT(DISTINCT CASE WHEN source = 'redpack' THEN uid END) as redpack_users,
    SUM(CASE WHEN source = 'redpack' THEN amount ELSE 0 END) as redpack_amount,
    -- 抽奖数据
    COUNT(DISTINCT CASE WHEN source = 'lottery' THEN uid END) as lottery_users,
    SUM(CASE WHEN source = 'lottery' THEN amount ELSE 0 END) as lottery_amount,
    -- 优惠券数据
    COUNT(DISTINCT CASE WHEN source = 'coupon' THEN uid END) as coupon_users,
    SUM(CASE WHEN source = 'coupon' THEN amount ELSE 0 END) as coupon_amount,
    -- 总计
    COUNT(DISTINCT uid) as total_users,
    SUM(amount) as total_amount
FROM (
    SELECT receive_day as create_day, uid, money as amount, 'redpack'::TEXT as source
    FROM gift_redpack_detail WHERE uid > 0
    UNION ALL
    SELECT create_day, uid, money as amount, 'lottery'::TEXT
    FROM gift_lottery_log
    UNION ALL
    SELECT create_day, uid, money as amount, 'coupon'::TEXT
    FROM coupon_log
) t
GROUP BY create_day
ORDER BY create_day DESC;

CREATE UNIQUE INDEX ON mv_daily_marketing_summary(create_day);

COMMENT ON MATERIALIZED VIEW mv_daily_marketing_summary IS '每日营销活动汇总';

-- =====================================================
-- 8. 性能优化
-- =====================================================

-- 设置表存储参数
ALTER TABLE gift_redpack SET (
    fillfactor = 80,
    autovacuum_vacuum_scale_factor = 0.05
);

ALTER TABLE coupon_list SET (
    fillfactor = 80
);

-- =====================================================
-- 9. 数据归档
-- =====================================================

/**
 * 归档已结束的活动数据
 */
CREATE TABLE gift_redpack_archive (
    LIKE gift_redpack INCLUDING ALL
);

CREATE TABLE gift_lottery_archive (
    LIKE gift_lottery INCLUDING ALL
);

/**
 * 归档函数
 */
CREATE OR REPLACE FUNCTION archive_expired_activities(
    p_days_ago INTEGER DEFAULT 90
)
RETURNS TABLE(
    archived_redpacks INTEGER,
    archived_lotteries INTEGER
) AS $$
DECLARE
    cutoff_time INTEGER;
    redpack_count INTEGER;
    lottery_count INTEGER;
BEGIN
    cutoff_time := EXTRACT(EPOCH FROM (CURRENT_DATE - INTERVAL '1 day' * p_days_ago))::INTEGER;
    
    -- 归档红包
    WITH moved AS (
        DELETE FROM gift_redpack
        WHERE create_time < cutoff_time AND status IN (2, 9)
        RETURNING *
    )
    INSERT INTO gift_redpack_archive
    SELECT * FROM moved;
    
    GET DIAGNOSTICS redpack_count = ROW_COUNT;
    
    -- 归档抽奖
    WITH moved AS (
        DELETE FROM gift_lottery
        WHERE create_time < cutoff_time AND status != 1
        RETURNING *
    )
    INSERT INTO gift_lottery_archive
    SELECT * FROM moved;
    
    GET DIAGNOSTICS lottery_count = ROW_COUNT;
    
    RETURN QUERY SELECT redpack_count, lottery_count;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION archive_expired_activities IS '归档过期的营销活动';

-- =====================================================
-- 脚本结束
-- =====================================================

COMMENT ON DATABASE bx_marketing IS 'BX平台营销库 - 红包/抽奖/优惠券等营销活动';
