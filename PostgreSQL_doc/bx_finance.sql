-- =====================================================
-- PostgreSQL 财务库 (bx_finance) 迁移脚本
-- 
-- 包含内容:
-- 1. 充值记录表 (fin_paylog) - 单表，分表由应用框架实现
-- 2. 提现记录表 (fin_cashlog) - 单表，分表由应用框架实现
-- 3. 充值类型表 (fin_ptype)
-- 4. 提现类型表 (fin_dtype)
--
-- 特点:
-- - 数据库层面使用单表
-- - 分表由应用层框架实现
-- - 强事务一致性保证
-- =====================================================

-- 创建数据库
CREATE DATABASE bx_finance
    WITH 
    ENCODING = 'UTF8'
    LC_COLLATE = 'zh_CN.UTF-8'
    LC_CTYPE = 'zh_CN.UTF-8'
    TEMPLATE = template0;

\c bx_finance;

-- 创建扩展
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_stat_statements";

-- =====================================================
-- 1. 充值记录表
-- =====================================================

/**
 * 充值记录表
 * 分表策略: 由应用层框架实现分表(推荐按月或按uid)
 */
CREATE TABLE fin_paylog (
    id SERIAL PRIMARY KEY,
    uid INTEGER NOT NULL,
    money NUMERIC(16,2) DEFAULT 0.00,
    rate VARCHAR(16) DEFAULT '1',
    real_money NUMERIC(16,2) DEFAULT 0.00,
    ori_balance NUMERIC(16,2) DEFAULT 0.00,
    new_balance NUMERIC(16,2) DEFAULT 0.00,
    osn VARCHAR(32) NOT NULL UNIQUE,
    out_osn VARCHAR(128),
    status SMALLINT DEFAULT 1,
    pay_type VARCHAR(64),
    is_first SMALLINT DEFAULT 0,
    pay_time INTEGER DEFAULT 0,
    create_time INTEGER DEFAULT 0,
    create_day INTEGER NOT NULL,
    sub_time INTEGER,
    check_time INTEGER DEFAULT 0,
    check_id INTEGER DEFAULT 0,
    check_ip VARCHAR(16),
    check_remark VARCHAR(128),
    pay_realname VARCHAR(128),
    pay_remark VARCHAR(128),
    pay_banners VARCHAR(256),
    receive_type SMALLINT DEFAULT 0,
    receive_ifsc VARCHAR(64),
    receive_upi VARCHAR(128),
    receive_bank_id INTEGER DEFAULT 0,
    receive_bank_name VARCHAR(128),
    receive_account VARCHAR(32),
    receive_realname VARCHAR(128),
    receive_routing VARCHAR(32),
    receive_protocol SMALLINT DEFAULT 0,
    receive_address VARCHAR(64),
    receive_qrcode VARCHAR(255),
    gplayerid VARCHAR(50),
    gaccount VARCHAR(50)
);

-- 创建索引
CREATE INDEX idx_paylog_uid ON fin_paylog(uid);
CREATE INDEX idx_paylog_osn ON fin_paylog(osn);
CREATE INDEX idx_paylog_status ON fin_paylog(status) WHERE status < 99;
CREATE INDEX idx_paylog_uid_status_day ON fin_paylog(uid, status, create_day);
CREATE INDEX idx_paylog_day_status ON fin_paylog(create_day, status);
CREATE INDEX idx_paylog_paytime ON fin_paylog(pay_time) WHERE pay_time > 0;
CREATE INDEX idx_paylog_create_time ON fin_paylog USING BRIN (create_time);
CREATE INDEX idx_paylog_create_day ON fin_paylog USING BRIN (create_day);

COMMENT ON TABLE fin_paylog IS '充值记录表(分表由应用层实现)';
COMMENT ON COLUMN fin_paylog.status IS '1:待支付 2:已提交 3:未到账 9:已确认';

-- =====================================================
-- 2. 提现记录表
-- =====================================================

/**
 * 提现记录表
 * 分表策略: 由应用层框架实现分表(推荐按月或按uid)
 */
CREATE TABLE fin_cashlog (
    id SERIAL PRIMARY KEY,
    osn VARCHAR(32) NOT NULL UNIQUE,
    out_osn VARCHAR(64),
    uid INTEGER NOT NULL,
    money NUMERIC(16,2),
    real_money NUMERIC(16,2) DEFAULT 0.00,
    fee NUMERIC(16,2) DEFAULT 0.00,
    fee_mode SMALLINT DEFAULT 0,
    ori_balance NUMERIC(16,2),
    new_balance NUMERIC(16,2),
    pay_type VARCHAR(255),
    create_time INTEGER,
    create_day INTEGER NOT NULL,
    pay_time INTEGER DEFAULT 0,
    status SMALLINT DEFAULT 1,
    receive_phone VARCHAR(64),
    receive_email VARCHAR(128),
    receive_ifsc VARCHAR(64),
    receive_type SMALLINT DEFAULT 0,
    receive_bank_id VARCHAR(100) DEFAULT '0',
    receive_bank_name VARCHAR(128),
    receive_account VARCHAR(64),
    receive_realname VARCHAR(64),
    receive_routing VARCHAR(64),
    receive_address VARCHAR(128),
    receive_protocol SMALLINT DEFAULT 0,
    receive_qrcode VARCHAR(255),
    check_remark VARCHAR(128),
    check_id INTEGER DEFAULT 0,
    check_ip VARCHAR(16),
    check_time INTEGER DEFAULT 0,
    client_ip VARCHAR(16),
    pay_status SMALLINT DEFAULT 0,
    pay_msg VARCHAR(255)
);

-- 创建索引
CREATE INDEX idx_cashlog_uid ON fin_cashlog(uid);
CREATE INDEX idx_cashlog_osn ON fin_cashlog(osn);
CREATE INDEX idx_cashlog_uid_day ON fin_cashlog(uid, create_day);
CREATE INDEX idx_cashlog_status ON fin_cashlog(status, pay_status) WHERE status < 99;
CREATE INDEX idx_cashlog_paytime ON fin_cashlog(pay_time) WHERE pay_time > 0;
CREATE INDEX idx_cashlog_create_time ON fin_cashlog USING BRIN (create_time);
CREATE INDEX idx_cashlog_create_day ON fin_cashlog USING BRIN (create_day);

COMMENT ON TABLE fin_cashlog IS '提现记录表(分表由应用层实现)';
COMMENT ON COLUMN fin_cashlog.status IS '1:待审核 3:不通过 7:取消 9:已通过';
COMMENT ON COLUMN fin_cashlog.pay_status IS '0:待支付 9:已支付';

-- =====================================================
-- 3. 充值类型配置表
-- =====================================================

/**
 * 充值类型表
 */
CREATE TABLE fin_ptype (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    type VARCHAR(255),
    sort INTEGER DEFAULT 100,
    status SMALLINT DEFAULT 1,
    min NUMERIC(16,2) DEFAULT 0,
    max NUMERIC(16,2) DEFAULT 999999,
    gametype SMALLINT DEFAULT 0,
    create_time INTEGER DEFAULT 0,
    update_time INTEGER DEFAULT 0
);

CREATE INDEX idx_ptype_status ON fin_ptype(status);
CREATE INDEX idx_ptype_type ON fin_ptype(type);

COMMENT ON TABLE fin_ptype IS '充值类型配置表';

-- =====================================================
-- 4. 提现类型配置表
-- =====================================================

/**
 * 提现类型表
 */
CREATE TABLE fin_dtype (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    type VARCHAR(255),
    sort INTEGER DEFAULT 100,
    status SMALLINT DEFAULT 1,
    create_time INTEGER DEFAULT 0,
    update_time INTEGER DEFAULT 0
);

CREATE INDEX idx_dtype_status ON fin_dtype(status);

COMMENT ON TABLE fin_dtype IS '提现类型配置表';

-- =====================================================
-- 5. 创建统计视图
-- =====================================================

/**
 * 用户充值统计视图
 */
CREATE OR REPLACE VIEW v_user_recharge_stats AS
SELECT 
    uid,
    COUNT(*) as recharge_count,
    SUM(money) as total_amount,
    SUM(CASE WHEN is_first = 1 THEN money ELSE 0 END) as first_amount,
    MIN(CASE WHEN status = 9 THEN pay_time END) as first_recharge_time,
    MAX(CASE WHEN status = 9 THEN pay_time END) as last_recharge_time,
    SUM(CASE WHEN status = 9 THEN money ELSE 0 END) as success_amount,
    COUNT(CASE WHEN status = 9 THEN 1 END) as success_count
FROM fin_paylog
WHERE status < 99
GROUP BY uid;

COMMENT ON VIEW v_user_recharge_stats IS '用户充值统计视图';

/**
 * 用户提现统计视图
 */
CREATE OR REPLACE VIEW v_user_withdraw_stats AS
SELECT 
    uid,
    COUNT(*) as withdraw_count,
    SUM(money) as total_amount,
    SUM(CASE WHEN status = 9 AND pay_status = 9 THEN real_money ELSE 0 END) as success_amount,
    COUNT(CASE WHEN status = 9 AND pay_status = 9 THEN 1 END) as success_count,
    SUM(CASE WHEN status = 1 THEN money ELSE 0 END) as pending_amount,
    COUNT(CASE WHEN status = 1 THEN 1 END) as pending_count
FROM fin_cashlog
WHERE status < 99
GROUP BY uid;

COMMENT ON VIEW v_user_withdraw_stats IS '用户提现统计视图';

/**
 * 每日财务汇总物化视图
 */
CREATE MATERIALIZED VIEW mv_daily_finance_summary AS
SELECT 
    create_day,
    -- 充值数据
    COUNT(DISTINCT p.uid) as recharge_users,
    COUNT(p.id) as recharge_orders,
    SUM(p.money) as recharge_amount,
    SUM(CASE WHEN p.is_first = 1 THEN p.money ELSE 0 END) as first_recharge_amount,
    COUNT(CASE WHEN p.is_first = 1 THEN 1 END) as first_recharge_users,
    -- 提现数据
    COUNT(DISTINCT c.uid) as withdraw_users,
    COUNT(c.id) as withdraw_orders,
    SUM(c.money) as withdraw_amount,
    SUM(c.real_money) as withdraw_real_amount,
    SUM(c.fee) as withdraw_fee,
    -- 净收入
    COALESCE(SUM(p.money), 0) - COALESCE(SUM(c.real_money), 0) as net_income
FROM (
    SELECT create_day, uid, id, money, is_first 
    FROM fin_paylog 
    WHERE status = 9
) p
FULL OUTER JOIN (
    SELECT create_day, uid, id, money, real_money, fee 
    FROM fin_cashlog 
    WHERE status = 9 AND pay_status = 9
) c ON p.create_day = c.create_day
GROUP BY create_day
ORDER BY create_day DESC;

-- 创建唯一索引支持并发刷新
CREATE UNIQUE INDEX ON mv_daily_finance_summary(create_day);

COMMENT ON MATERIALIZED VIEW mv_daily_finance_summary IS '每日财务汇总(需定时刷新)';

-- =====================================================
-- 6. 创建触发器
-- =====================================================

/**
 * 充值记录插入后触发器 - 记录首充标记
 */
CREATE OR REPLACE FUNCTION fn_paylog_after_insert()
RETURNS TRIGGER AS $$
BEGIN
    -- 如果是成功的充值,检查是否首充
    IF NEW.status = 9 AND NEW.is_first = 0 THEN
        -- 检查是否存在之前的成功充值
        IF NOT EXISTS (
            SELECT 1 FROM fin_paylog 
            WHERE uid = NEW.uid 
            AND id < NEW.id 
            AND status = 9
        ) THEN
            -- 标记为首充
            UPDATE fin_paylog SET is_first = 1 WHERE id = NEW.id;
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_paylog_after_insert
    AFTER INSERT ON fin_paylog
    FOR EACH ROW
    EXECUTE FUNCTION fn_paylog_after_insert();

-- =====================================================
-- 7. 创建辅助函数
-- =====================================================

/**
 * 获取用户充值总额
 */
CREATE OR REPLACE FUNCTION get_user_total_recharge(p_uid INTEGER)
RETURNS NUMERIC AS $$
BEGIN
    RETURN COALESCE(
        (SELECT SUM(money) FROM fin_paylog WHERE uid = p_uid AND status = 9),
        0
    );
END;
$$ LANGUAGE plpgsql STABLE;

/**
 * 获取用户提现总额
 */
CREATE OR REPLACE FUNCTION get_user_total_withdraw(p_uid INTEGER)
RETURNS NUMERIC AS $$
BEGIN
    RETURN COALESCE(
        (SELECT SUM(real_money) FROM fin_cashlog 
         WHERE uid = p_uid AND status = 9 AND pay_status = 9),
        0
    );
END;
$$ LANGUAGE plpgsql STABLE;

/**
 * 获取用户待提现金额
 */
CREATE OR REPLACE FUNCTION get_user_pending_withdraw(p_uid INTEGER)
RETURNS NUMERIC AS $$
BEGIN
    RETURN COALESCE(
        (SELECT SUM(money) FROM fin_cashlog 
         WHERE uid = p_uid AND status IN (1, 9) AND pay_status = 0),
        0
    );
END;
$$ LANGUAGE plpgsql STABLE;

-- =====================================================
-- 8. 性能优化配置
-- =====================================================

-- 设置表存储参数
ALTER TABLE fin_ptype SET (
    fillfactor = 95  -- 配置表更新少
);

ALTER TABLE fin_dtype SET (
    fillfactor = 95
);

ALTER TABLE fin_paylog SET (
    fillfactor = 85
);

ALTER TABLE fin_cashlog SET (
    fillfactor = 85
);

-- =====================================================
-- 9. 数据安全 - 审计日志
-- =====================================================

/**
 * 创建审计日志表
 */
CREATE TABLE fin_audit_log (
    id BIGSERIAL PRIMARY KEY,
    table_name VARCHAR(50),
    record_id INTEGER,
    action VARCHAR(10),
    old_data JSONB,
    new_data JSONB,
    user_id INTEGER,
    action_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50)
);

CREATE INDEX idx_audit_table ON fin_audit_log(table_name, record_id);
CREATE INDEX idx_audit_time ON fin_audit_log USING BRIN (action_time);

COMMENT ON TABLE fin_audit_log IS '财务操作审计日志';

/**
 * 充值记录审计触发器
 */
CREATE OR REPLACE FUNCTION fn_paylog_audit()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'UPDATE' AND OLD.status != NEW.status THEN
        INSERT INTO fin_audit_log (
            table_name, record_id, action, old_data, new_data, user_id
        ) VALUES (
            'fin_paylog',
            NEW.id,
            'UPDATE',
            row_to_json(OLD)::jsonb,
            row_to_json(NEW)::jsonb,
            NEW.check_id
        );
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_paylog_audit
    AFTER UPDATE ON fin_paylog
    FOR EACH ROW
    EXECUTE FUNCTION fn_paylog_audit();

/**
 * 提现记录审计触发器
 */
CREATE TRIGGER trg_cashlog_audit
    AFTER UPDATE ON fin_cashlog
    FOR EACH ROW
    EXECUTE FUNCTION fn_paylog_audit();

-- =====================================================
-- 10. 初始化数据
-- =====================================================

-- 插入默认充值类型
INSERT INTO fin_ptype (name, type, status, min, max) VALUES
('线下充值', 'offline', 3, 100, 500000),
('在线支付', 'online', 3, 10, 100000);

-- 插入默认提现类型
INSERT INTO fin_dtype (name, type, status) VALUES
('银行卡提现', 'bank', 2),
('USDT提现', 'usdt', 2);

-- =====================================================
-- 11. 定时任务配置
-- =====================================================

/**
 * 使用pg_cron创建定时任务(需要安装pg_cron扩展)
 */
-- 每天凌晨4点刷新物化视图
-- SELECT cron.schedule('refresh-finance-summary', '0 4 * * *',
--     'REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_finance_summary;'
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

COMMENT ON DATABASE bx_finance IS 'BX平台财务库 - 充值提现记录(分表由应用层实现)';
