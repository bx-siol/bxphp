-- =====================================================
-- PostgreSQL 核心业务库 (bx_core) 迁移脚本
-- 
-- 包含内容:
-- 1. 用户相关表 (sys_user, sys_user_rauth, sys_user_token等)
-- 2. 系统配置表 (sys_config, sys_pset, sys_node等)
-- 3. 权限管理表 (sys_group, sys_access)
-- 4. 基础数据表 (cnf_area, cnf_bank, cnf_currency等)
-- 5. 产品相关表 (pro_goods, pro_category)
-- 6. 钱包基础表 (wallet_list)
-- =====================================================

-- 创建数据库
CREATE DATABASE bx_core
    WITH 
    ENCODING = 'UTF8'
    LC_COLLATE = 'zh_CN.UTF-8'
    LC_CTYPE = 'zh_CN.UTF-8'
    TEMPLATE = template0;

\c bx_core;

-- 创建扩展
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";      -- UUID生成
CREATE EXTENSION IF NOT EXISTS "pg_trgm";        -- 模糊查询优化
CREATE EXTENSION IF NOT EXISTS "btree_gin";      -- GIN索引优化
CREATE EXTENSION IF NOT EXISTS "pg_stat_statements"; -- SQL性能监控

-- =====================================================
-- 1. 用户相关表
-- =====================================================

/**
 * 系统用户表
 * 分表策略: 不分表,使用主键索引和多个复合索引优化
 * 优化: 添加BRIN索引支持范围查询
 */
CREATE TABLE sys_user (
    id INTEGER PRIMARY KEY DEFAULT (floor(random() * 900000) + 100000)::int,
    pid INTEGER DEFAULT 0,
    gid SMALLINT DEFAULT 0,
    down_level INTEGER DEFAULT 0,
    openid VARCHAR(32) UNIQUE,
    unionid VARCHAR(32),
    account VARCHAR(64) UNIQUE NOT NULL,
    phone VARCHAR(16),
    email VARCHAR(128),
    password VARCHAR(64),
    password2 VARCHAR(64),
    is_pwd2_set SMALLINT DEFAULT 0,
    first_pay_day INTEGER DEFAULT 0,
    stop_commission SMALLINT DEFAULT 0,
    balance NUMERIC(16,2) DEFAULT 0.00,
    fz_balance NUMERIC(16,2) DEFAULT 0.00,
    total_invest NUMERIC(16,2) DEFAULT 0.00,
    total_invest2 NUMERIC(16,2) DEFAULT 0.00,
    lottery INTEGER DEFAULT 0,
    is_effective SMALLINT DEFAULT 0,
    language VARCHAR(8),
    apikey VARCHAR(64),
    is_rsa SMALLINT DEFAULT 0,
    rsa_public TEXT,
    rsa_private TEXT,
    nickname VARCHAR(64),
    realname VARCHAR(32),
    authentication SMALLINT DEFAULT 0,
    usn VARCHAR(32),
    icode VARCHAR(8) UNIQUE,
    status SMALLINT DEFAULT 2,
    is_ai SMALLINT DEFAULT 0,
    forbid_time_flag VARCHAR(8) DEFAULT '0',
    forbid_time BIGINT DEFAULT 0,
    forbid_msg VARCHAR(128),
    is_google SMALLINT DEFAULT 0,
    google_secret VARCHAR(32),
    google_hide SMALLINT DEFAULT 0,
    latitude VARCHAR(32),
    longitude VARCHAR(32),
    address VARCHAR(200),
    sex SMALLINT DEFAULT 0,
    country VARCHAR(32),
    province VARCHAR(32),
    city VARCHAR(32),
    birthday INTEGER DEFAULT 0,
    reg_time INTEGER NOT NULL,
    reg_ip VARCHAR(16),
    login_time INTEGER,
    login_ip VARCHAR(16),
    headimgurl TEXT,
    white_ip TEXT,
    avatarurl TEXT,
    is_count SMALLINT DEFAULT 0,
    pidg1 INTEGER DEFAULT 0,
    pidg2 INTEGER DEFAULT 0,
    teamcount INTEGER DEFAULT 0,
    pids VARCHAR(500),
    cbank INTEGER DEFAULT 0,
    icode_status INTEGER DEFAULT 0,
    vip SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 用户表索引优化
CREATE INDEX idx_user_pid ON sys_user(pid) WHERE status < 99;
CREATE INDEX idx_user_phone ON sys_user(phone) WHERE phone IS NOT NULL;
CREATE INDEX idx_user_unionid ON sys_user(unionid) WHERE unionid IS NOT NULL;
CREATE INDEX idx_user_is_ai ON sys_user(is_ai) WHERE is_ai = 1;
CREATE INDEX idx_user_status_gid ON sys_user(status, gid) WHERE status < 99;
CREATE INDEX idx_user_first_pay ON sys_user(pid, first_pay_day) WHERE first_pay_day > 0;
CREATE INDEX idx_user_reg_time ON sys_user USING BRIN (reg_time); -- 时间范围查询
CREATE INDEX idx_user_pids_gin ON sys_user USING GIN (pids gin_trgm_ops); -- 团队查询优化

-- PIDS 搜索优化(用于团队查询)
CREATE INDEX idx_user_pids_pattern ON sys_user(pids text_pattern_ops) WHERE pids IS NOT NULL;

-- 用户表注释
COMMENT ON TABLE sys_user IS '系统用户表';
COMMENT ON COLUMN sys_user.id IS '用户ID';
COMMENT ON COLUMN sys_user.pid IS '父级用户ID';
COMMENT ON COLUMN sys_user.pidg1 IS '71级代理ID';
COMMENT ON COLUMN sys_user.pidg2 IS '81级代理ID';
COMMENT ON COLUMN sys_user.pids IS '上级ID链,逗号分隔';
COMMENT ON COLUMN sys_user.first_pay_day IS '首次充值日期(Ymd格式)';
COMMENT ON COLUMN sys_user.teamcount IS '团队人数';

/**
 * 用户实名认证表
 */
CREATE TABLE sys_user_rauth (
    uid INTEGER PRIMARY KEY,
    type SMALLINT DEFAULT 0,
    realname VARCHAR(32),
    number VARCHAR(32),
    front VARCHAR(128),
    back VARCHAR(128),
    status SMALLINT DEFAULT 1,
    create_time INTEGER DEFAULT 0,
    update_time INTEGER DEFAULT 0,
    check_time INTEGER DEFAULT 0,
    check_remark VARCHAR(128),
    FOREIGN KEY (uid) REFERENCES sys_user(id) ON DELETE CASCADE
);

CREATE INDEX idx_rauth_status ON sys_user_rauth(status) WHERE status > 0;

COMMENT ON TABLE sys_user_rauth IS '用户实名认证表';
COMMENT ON COLUMN sys_user_rauth.status IS '1:待审核 2:未通过 3:已认证';

/**
 * 用户登录令牌表
 */
CREATE TABLE sys_user_token (
    id SERIAL PRIMARY KEY,
    account VARCHAR(32),
    uid INTEGER DEFAULT 0,
    token CHAR(32) UNIQUE NOT NULL,
    create_time INTEGER,
    update_time INTEGER,
    status SMALLINT DEFAULT 0,
    iscom SMALLINT DEFAULT 0
);

CREATE INDEX idx_token_uid ON sys_user_token(uid) WHERE status = 0;
CREATE INDEX idx_token_create_time ON sys_user_token USING BRIN (create_time);

COMMENT ON TABLE sys_user_token IS '用户登录令牌表';

/**
 * 用户微信信息表
 */
CREATE TABLE sys_user_wechat (
    id SERIAL PRIMARY KEY,
    openid VARCHAR(32),
    unionid VARCHAR(32),
    nickname VARCHAR(64),
    sex SMALLINT,
    country VARCHAR(32),
    province VARCHAR(32),
    city VARCHAR(32),
    subscribe SMALLINT DEFAULT 0,
    subscribe_time INTEGER,
    subscribe_scene VARCHAR(32),
    avatarurl TEXT,
    headimgurl TEXT,
    update_time INTEGER
);

CREATE INDEX idx_wechat_openid ON sys_user_wechat(openid);
CREATE INDEX idx_wechat_unionid ON sys_user_wechat(unionid);

COMMENT ON TABLE sys_user_wechat IS '用户微信信息表';

/**
 * 用户设置表
 */
CREATE TABLE sys_user_setting (
    uid INTEGER PRIMARY KEY,
    execution_max NUMERIC(16,2),
    execution_min NUMERIC(16,2),
    price_deviation NUMERIC(16,2),
    password_free_amount NUMERIC(16,2),
    FOREIGN KEY (uid) REFERENCES sys_user(id) ON DELETE CASCADE
);

COMMENT ON TABLE sys_user_setting IS '用户个性化设置表';

/**
 * 用户问题答案表
 */
CREATE TABLE sys_user_answer (
    id SERIAL PRIMARY KEY,
    uid INTEGER,
    q1 INTEGER,
    a1 VARCHAR(255),
    q2 INTEGER,
    a2 VARCHAR(255),
    q3 INTEGER,
    a3 VARCHAR(255),
    create_time INTEGER
);

CREATE INDEX idx_answer_uid ON sys_user_answer(uid);

/**
 * 安全问题表
 */
CREATE TABLE sys_user_question (
    id SERIAL PRIMARY KEY,
    question VARCHAR(255)
);

/**
 * 问题日志表
 */
CREATE TABLE sys_user_question_log (
    id SERIAL PRIMARY KEY,
    uid INTEGER,
    token VARCHAR(32),
    create_time INTEGER
);

-- =====================================================
-- 2. 系统配置表
-- =====================================================

/**
 * 系统配置表
 */
CREATE TABLE sys_config (
    id SERIAL PRIMARY KEY,
    skey VARCHAR(32) UNIQUE,
    name VARCHAR(128),
    single SMALLINT DEFAULT 0,
    config TEXT,
    update_time INTEGER,
    is_show SMALLINT DEFAULT 1
);

CREATE INDEX idx_config_skey ON sys_config(skey);

COMMENT ON TABLE sys_config IS '系统配置表';

/**
 * 项目设置表
 */
CREATE TABLE sys_pset (
    id SERIAL PRIMARY KEY,
    name VARCHAR(128),
    skey VARCHAR(32) UNIQUE,
    config TEXT,
    create_time INTEGER DEFAULT 0,
    update_time INTEGER DEFAULT 0
);

COMMENT ON TABLE sys_pset IS '项目设置表';

/**
 * 系统节点表(权限节点)
 */
CREATE TABLE sys_node (
    id SERIAL PRIMARY KEY,
    pid INTEGER DEFAULT 0,
    name VARCHAR(32),
    nkey VARCHAR(32) UNIQUE,
    type SMALLINT DEFAULT 0,
    ico VARCHAR(255),
    public SMALLINT DEFAULT 0,
    sort SMALLINT DEFAULT 100,
    pre_path VARCHAR(64),
    url VARCHAR(128),
    remark VARCHAR(128),
    create_time INTEGER
);

CREATE INDEX idx_node_pid ON sys_node(pid);
CREATE INDEX idx_node_type ON sys_node(type);

COMMENT ON TABLE sys_node IS '系统节点表(菜单/权限)';

/**
 * 系统日志表
 */
CREATE TABLE sys_log (
    id SERIAL PRIMARY KEY,
    uid INTEGER,
    opt_name VARCHAR(255),
    sql_str TEXT,
    create_time INTEGER,
    create_ip VARCHAR(16)
);

CREATE INDEX idx_log_uid ON sys_log(uid);
CREATE INDEX idx_log_time ON sys_log USING BRIN (create_time);

COMMENT ON TABLE sys_log IS '系统操作日志表';

/**
 * 语言包表
 */
CREATE TABLE sys_lang (
    id SERIAL PRIMARY KEY,
    cn VARCHAR(128) UNIQUE,
    tw VARCHAR(128),
    en VARCHAR(255),
    es VARCHAR(255),
    mx VARCHAR(255),
    create_time INTEGER,
    update_time INTEGER DEFAULT 0
);

COMMENT ON TABLE sys_lang IS '多语言包表';

/**
 * 短信验证码表
 */
CREATE TABLE sys_vcode (
    id SERIAL PRIMARY KEY,
    stype SMALLINT,
    phone VARCHAR(16),
    code VARCHAR(8),
    status SMALLINT DEFAULT 0,
    verify_num SMALLINT DEFAULT 0,
    create_time INTEGER,
    create_day INTEGER DEFAULT 0,
    verify_time INTEGER DEFAULT 0,
    create_ip VARCHAR(16),
    scon VARCHAR(256)
);

CREATE INDEX idx_vcode_sp ON sys_vcode(stype, phone);
CREATE INDEX idx_vcode_day ON sys_vcode(create_day);

COMMENT ON TABLE sys_vcode IS '短信验证码表';

/**
 * 邮箱验证码表
 */
CREATE TABLE sys_mcode (
    id SERIAL PRIMARY KEY,
    stype SMALLINT,
    email VARCHAR(64),
    code VARCHAR(8),
    status SMALLINT DEFAULT 0,
    verify_num SMALLINT DEFAULT 0,
    create_time INTEGER,
    create_day INTEGER DEFAULT 0,
    verify_time INTEGER DEFAULT 0,
    create_ip VARCHAR(16),
    scon TEXT
);

CREATE INDEX idx_mcode_sp ON sys_mcode(stype, email);

COMMENT ON TABLE sys_mcode IS '邮箱验证码表';

/**
 * 系统通知表
 */
CREATE TABLE sys_notice (
    id SERIAL PRIMARY KEY,
    create_time INTEGER,
    status SMALLINT DEFAULT 0,
    params TEXT,
    remark VARCHAR(128),
    send_time INTEGER DEFAULT 0,
    send_msg TEXT
);

CREATE INDEX idx_notice_status ON sys_notice(status);

COMMENT ON TABLE sys_notice IS '系统通知任务表';

-- =====================================================
-- 3. 权限管理表
-- =====================================================

/**
 * 用户组表
 */
CREATE TABLE sys_group (
    id SERIAL PRIMARY KEY,
    name VARCHAR(64),
    sort INTEGER DEFAULT 100,
    status SMALLINT DEFAULT 1,
    cover VARCHAR(128),
    remark VARCHAR(255),
    create_time INTEGER DEFAULT 0
);

CREATE INDEX idx_group_status ON sys_group(status) WHERE status < 99;

COMMENT ON TABLE sys_group IS '用户分组表';

/**
 * 权限访问表
 */
CREATE TABLE sys_access (
    id SERIAL PRIMARY KEY,
    uid INTEGER DEFAULT 0,
    gid INTEGER DEFAULT 0,
    node_ids TEXT
);

CREATE INDEX idx_access_uid ON sys_access(uid);
CREATE INDEX idx_access_gid ON sys_access(gid);

COMMENT ON TABLE sys_access IS '用户权限访问表';

/**
 * 微信公众号表
 */
CREATE TABLE sys_gzh (
    id SERIAL PRIMARY KEY,
    uid INTEGER DEFAULT 0,
    name VARCHAR(32),
    appid VARCHAR(32) UNIQUE,
    appsecret VARCHAR(64),
    mch_id VARCHAR(16),
    pay_key VARCHAR(32),
    token VARCHAR(32),
    access_token_time INTEGER,
    access_token TEXT,
    jsapi_ticket_time INTEGER,
    jsapi_ticket TEXT,
    status SMALLINT DEFAULT 1,
    gh_id VARCHAR(32),
    biz VARCHAR(32),
    tpl_id VARCHAR(64)
);

COMMENT ON TABLE sys_gzh IS '微信公众号配置表';

-- =====================================================
-- 4. 基础数据表
-- =====================================================

/**
 * 地区表
 */
CREATE TABLE cnf_area (
    id INTEGER PRIMARY KEY,
    pid INTEGER DEFAULT 0,
    name VARCHAR(64)
);

CREATE INDEX idx_area_pid ON cnf_area(pid);

COMMENT ON TABLE cnf_area IS '地区表';

/**
 * 银行表
 */
CREATE TABLE cnf_bank (
    id SERIAL PRIMARY KEY,
    code VARCHAR(32),
    name VARCHAR(128),
    status SMALLINT DEFAULT 2
);

COMMENT ON TABLE cnf_bank IS '银行列表表';

/**
 * 银行卡信息表
 */
CREATE TABLE cnf_banklog (
    id SERIAL PRIMARY KEY,
    type SMALLINT DEFAULT 0,
    uid INTEGER DEFAULT 0,
    ifsc VARCHAR(128),
    upi VARCHAR(128),
    province_id INTEGER DEFAULT 0,
    city_id INTEGER DEFAULT 0,
    bank_id VARCHAR(100),
    bank_name VARCHAR(128),
    account VARCHAR(32),
    realname VARCHAR(128),
    routing VARCHAR(32),
    phone VARCHAR(20) DEFAULT '',
    idcard VARCHAR(20) DEFAULT '',
    email VARCHAR(128),
    branch_name VARCHAR(128),
    create_time INTEGER,
    create_id INTEGER DEFAULT 0,
    sort SMALLINT DEFAULT 1000,
    status SMALLINT DEFAULT 1,
    currency_id INTEGER,
    protocal SMALLINT DEFAULT 0,
    address VARCHAR(256),
    qrcode VARCHAR(128),
    remark VARCHAR(255)
);

CREATE INDEX idx_banklog_uid ON cnf_banklog(uid);
CREATE INDEX idx_banklog_status ON cnf_banklog(status, uid) WHERE status = 2;

COMMENT ON TABLE cnf_banklog IS '银行卡/钱包信息表';
COMMENT ON COLUMN cnf_banklog.type IS '1:银行卡 2:支付宝 3:微信 4:USDT';

/**
 * 币种表
 */
CREATE TABLE cnf_currency (
    id SERIAL PRIMARY KEY,
    name VARCHAR(16),
    code VARCHAR(8),
    symbol VARCHAR(4),
    icon VARCHAR(128),
    status SMALLINT DEFAULT 2
);

COMMENT ON TABLE cnf_currency IS '币种配置表';

/**
 * 省市表
 */
CREATE TABLE cnf_pc (
    id SERIAL PRIMARY KEY,
    pid INTEGER DEFAULT 0,
    name VARCHAR(64)
);

COMMENT ON TABLE cnf_pc IS '省市表';

/**
 * 行情数据表
 */
CREATE TABLE cnf_market (
    id SERIAL PRIMARY KEY,
    period VARCHAR(8),
    period_id INTEGER,
    symbol VARCHAR(32),
    base_currency VARCHAR(16),
    quote_currency VARCHAR(16),
    open VARCHAR(16),
    close VARCHAR(16),
    low VARCHAR(16),
    high VARCHAR(16),
    amount VARCHAR(64) DEFAULT '0',
    count INTEGER DEFAULT 0,
    vol VARCHAR(32) DEFAULT '0',
    rise VARCHAR(16),
    update_time INTEGER DEFAULT 0,
    UNIQUE (period_id, symbol, period)
);

CREATE INDEX idx_market_sp ON cnf_market(symbol, period);

COMMENT ON TABLE cnf_market IS '市场行情数据表';

-- =====================================================
-- 5. 产品相关表
-- =====================================================

/**
 * 产品分类表
 */
CREATE TABLE pro_category (
    id SERIAL PRIMARY KEY,
    pid INTEGER DEFAULT 0,
    name VARCHAR(64),
    sort SMALLINT DEFAULT 1000,
    status SMALLINT DEFAULT 2,
    cover VARCHAR(128),
    remark VARCHAR(255),
    create_time INTEGER DEFAULT 0,
    update_time INTEGER DEFAULT 0
);

CREATE INDEX idx_category_pid ON pro_category(pid);
CREATE INDEX idx_category_status ON pro_category(status) WHERE status = 2;

COMMENT ON TABLE pro_category IS '产品分类表';

/**
 * 产品表
 */
CREATE TABLE pro_goods (
    id SERIAL PRIMARY KEY,
    gsn CHAR(16) UNIQUE,
    cid INTEGER DEFAULT 0,
    name VARCHAR(255),
    is_hot SMALLINT DEFAULT 0,
    days INTEGER DEFAULT 0,
    price NUMERIC(16,2) DEFAULT 0.00,
    price1 NUMERIC(16,2),
    price2 NUMERIC(16,2),
    price0 NUMERIC(16,2),
    rate VARCHAR(16),
    scale NUMERIC(16,2) DEFAULT 0.00,
    invested NUMERIC(16,2) DEFAULT 0.00,
    v_invested NUMERIC(16,2) DEFAULT 0.00,
    invest_min NUMERIC(16,2) DEFAULT 0.00,
    invest_limit INTEGER DEFAULT 0,
    bonus_type SMALLINT DEFAULT 1,
    status SMALLINT DEFAULT 1,
    guarantors VARCHAR(255),
    lt_from_money NUMERIC(16,2) DEFAULT 0.00,
    lt_to_money NUMERIC(16,2) DEFAULT 0.00,
    sort INTEGER DEFAULT 1000,
    icon VARCHAR(128),
    covers TEXT,
    content TEXT,
    create_time INTEGER DEFAULT 0,
    buyday INTEGER,
    dayout INTEGER,
    is_normal INTEGER DEFAULT 0,
    is_xskc INTEGER DEFAULT 0,
    gift INTEGER DEFAULT 0,
    goodsindex INTEGER DEFAULT 0,
    pointshop INTEGER DEFAULT 0,
    kc INTEGER,
    sjcjcs INTEGER DEFAULT 0,
    sendupnum INTEGER,
    sendnum INTEGER,
    gifttoself INTEGER,
    gifttopuser INTEGER,
    dssj INTEGER,
    djs INTEGER,
    selfbg INTEGER,
    selfintegral INTEGER,
    integral INTEGER,
    yaoqing INTEGER,
    fgcjcs INTEGER,
    cjcs INTEGER,
    cvip INTEGER,
    firstgive1 INTEGER,
    firstgive2 INTEGER,
    firstgive3 INTEGER,
    firstgive4 INTEGER,
    firstgive5 INTEGER,
    loop1 NUMERIC(16,2) DEFAULT 0.00,
    loop2 NUMERIC(16,2) DEFAULT 0.00,
    loop3 NUMERIC(16,2) DEFAULT 0.00,
    loop4 NUMERIC(16,2) DEFAULT 0.00,
    loop5 NUMERIC(16,2) DEFAULT 0.00
);

CREATE INDEX idx_goods_status_cid ON pro_goods(status, cid) WHERE status > 1 AND status < 99;
CREATE INDEX idx_goods_hot ON pro_goods(is_hot, status) WHERE is_hot = 1;
CREATE INDEX idx_goods_pointshop ON pro_goods(pointshop, status) WHERE pointshop = 1;

COMMENT ON TABLE pro_goods IS '产品表';
COMMENT ON COLUMN pro_goods.status IS '1:下架 2:预售 3:在售 9:售罄';

/**
 * 产品用户关联表
 */
CREATE TABLE pro_guser (
    id SERIAL PRIMARY KEY,
    uid INTEGER,
    gid INTEGER,
    days INTEGER DEFAULT 0,
    num INTEGER DEFAULT 0,
    status SMALLINT DEFAULT 1,
    create_time INTEGER DEFAULT 0,
    create_id INTEGER DEFAULT 0
);

CREATE INDEX idx_guser_uid ON pro_guser(uid);
CREATE INDEX idx_guser_gid ON pro_guser(gid);

COMMENT ON TABLE pro_guser IS '产品用户权限表';

-- =====================================================
-- 6. 钱包基础表
-- =====================================================

/**
 * 钱包列表表
 */
CREATE TABLE wallet_list (
    id SERIAL PRIMARY KEY,
    waddr VARCHAR(64) UNIQUE,
    uid INTEGER NOT NULL,
    cid INTEGER DEFAULT 0,
    balance NUMERIC(16,2) DEFAULT 0.00,
    fz_balance NUMERIC(16,2) DEFAULT 0.00,
    create_time INTEGER DEFAULT 0,
    lasttime VARCHAR(20) DEFAULT '0',
    UNIQUE (uid, cid)
);

CREATE INDEX idx_wallet_uid ON wallet_list(uid);

COMMENT ON TABLE wallet_list IS '用户钱包表';
COMMENT ON COLUMN wallet_list.cid IS '币种ID: 1=充值钱包 2=余额钱包 3=积分钱包';

-- =====================================================
-- 7. 新闻/公告相关表
-- =====================================================

/**
 * 新闻分类表
 */
CREATE TABLE news_category (
    id SERIAL PRIMARY KEY,
    pid INTEGER DEFAULT 0,
    name VARCHAR(64),
    status SMALLINT DEFAULT 2,
    sort SMALLINT DEFAULT 1000,
    cover VARCHAR(128),
    remark VARCHAR(255),
    create_time INTEGER,
    update_time INTEGER
);

CREATE INDEX idx_news_cat_status ON news_category(status);

COMMENT ON TABLE news_category IS '新闻分类表';

/**
 * 新闻文章表
 */
CREATE TABLE news_article (
    id SERIAL PRIMARY KEY,
    cid INTEGER,
    title VARCHAR(128),
    create_time INTEGER,
    publish_time INTEGER,
    status SMALLINT DEFAULT 1,
    create_id INTEGER,
    is_recommend SMALLINT DEFAULT 0,
    label VARCHAR(128),
    author VARCHAR(32),
    url TEXT,
    cover VARCHAR(128),
    ndesc VARCHAR(255),
    content TEXT
);

CREATE INDEX idx_article_cid ON news_article(cid);
CREATE INDEX idx_article_status ON news_article(status);
CREATE INDEX idx_article_recommend ON news_article(is_recommend) WHERE is_recommend = 1;

COMMENT ON TABLE news_article IS '新闻文章表';

/**
 * 公告表
 */
CREATE TABLE news_notice (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255),
    text VARCHAR(255),
    sort SMALLINT,
    status SMALLINT DEFAULT 1,
    create_time INTEGER DEFAULT 0
);

COMMENT ON TABLE news_notice IS '滚动公告表';

/**
 * 反馈表
 */
CREATE TABLE news_feedback (
    id SERIAL PRIMARY KEY,
    problem SMALLINT DEFAULT 0,
    uid INTEGER,
    phone VARCHAR(16),
    status SMALLINT DEFAULT 1,
    title VARCHAR(255),
    content VARCHAR(255),
    covers TEXT,
    create_time INTEGER DEFAULT 0
);

CREATE INDEX idx_feedback_uid ON news_feedback(uid);

COMMENT ON TABLE news_feedback IS '用户反馈表';

-- =====================================================
-- 8. 消息相关表
-- =====================================================

/**
 * 消息列表表
 */
CREATE TABLE msg_list (
    id SERIAL PRIMARY KEY,
    msn CHAR(16),
    uid INTEGER,
    title VARCHAR(255),
    phone VARCHAR(16),
    email VARCHAR(128),
    covers TEXT,
    status SMALLINT DEFAULT 1,
    content TEXT,
    is_new SMALLINT DEFAULT 0,
    create_time INTEGER
);

CREATE INDEX idx_msg_uid ON msg_list(uid);
CREATE INDEX idx_msg_new ON msg_list(is_new) WHERE is_new = 1;

COMMENT ON TABLE msg_list IS '用户消息列表';

/**
 * 消息回复表
 */
CREATE TABLE msg_list_log (
    id SERIAL PRIMARY KEY,
    fuid INTEGER DEFAULT 0,
    mid INTEGER,
    content TEXT,
    create_time INTEGER
);

CREATE INDEX idx_msglog_mid ON msg_list_log(mid);

COMMENT ON TABLE msg_list_log IS '消息回复记录';

-- =====================================================
-- 9. 扩展服务表
-- =====================================================

/**
 * 客服信息表
 */
CREATE TABLE ext_service (
    id SERIAL PRIMARY KEY,
    uid INTEGER DEFAULT 0,
    gid INTEGER DEFAULT 0,
    name VARCHAR(128),
    account VARCHAR(128) DEFAULT '0',
    type SMALLINT,
    qrcode VARCHAR(255),
    remark VARCHAR(255),
    create_time INTEGER DEFAULT 0
);

CREATE INDEX idx_service_uid ON ext_service(uid);

COMMENT ON TABLE ext_service IS '客服信息表';

/**
 * 任务表
 */
CREATE TABLE ext_task (
    id SERIAL PRIMARY KEY,
    uid INTEGER DEFAULT 0,
    gid INTEGER DEFAULT 0,
    name VARCHAR(255),
    day_limit INTEGER DEFAULT 1,
    sort INTEGER DEFAULT 1000,
    award NUMERIC(16,2) DEFAULT 0.00,
    create_time INTEGER DEFAULT 0,
    content TEXT
);

COMMENT ON TABLE ext_task IS '用户任务表';

/**
 * 任务日志表
 */
CREATE TABLE ext_tasklog (
    id SERIAL PRIMARY KEY,
    tsn VARCHAR(32) UNIQUE,
    uid INTEGER DEFAULT 0,
    tid INTEGER DEFAULT 0,
    status SMALLINT DEFAULT 1,
    award NUMERIC(16,2) DEFAULT 0.00,
    voucher VARCHAR(255),
    remark VARCHAR(255),
    create_day INTEGER DEFAULT 0,
    create_time INTEGER DEFAULT 0,
    submit_time INTEGER DEFAULT 0,
    check_time INTEGER DEFAULT 0,
    check_id INTEGER DEFAULT 0,
    check_remark VARCHAR(255)
);

CREATE INDEX idx_tasklog_uid ON ext_tasklog(uid);
CREATE INDEX idx_tasklog_status ON ext_tasklog(status);

COMMENT ON TABLE ext_tasklog IS '用户任务日志';

-- =====================================================
-- 10. 创建触发器
-- =====================================================

/**
 * 自动更新时间戳触发器函数
 */
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 为sys_user表添加自动更新触发器
CREATE TRIGGER update_sys_user_updated_at
    BEFORE UPDATE ON sys_user
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- 11. 创建视图
-- =====================================================

/**
 * 用户完整信息视图
 */
CREATE OR REPLACE VIEW v_user_full_info AS
SELECT 
    u.id,
    u.account,
    u.nickname,
    u.realname,
    u.phone,
    u.email,
    u.balance,
    u.fz_balance,
    u.total_invest,
    u.total_invest2,
    u.gid,
    g.name as group_name,
    u.pid,
    pu.account as parent_account,
    u.pidg1,
    u.pidg2,
    u.teamcount,
    u.first_pay_day,
    u.status,
    u.reg_time,
    u.login_time,
    r.status as rauth_status
FROM sys_user u
LEFT JOIN sys_group g ON u.gid = g.id
LEFT JOIN sys_user pu ON u.pid = pu.id
LEFT JOIN sys_user_rauth r ON u.id = r.uid
WHERE u.status < 99;

COMMENT ON VIEW v_user_full_info IS '用户完整信息视图';

-- =====================================================
-- 12. 创建分区表(示例 - 系统日志按月分区)
-- =====================================================

-- 注意: 此处仅为示例,实际使用时需要根据数据量决定是否分区

-- =====================================================
-- 13. 初始化数据
-- =====================================================

-- 插入默认管理员账号
INSERT INTO sys_user (id, account, password, password2, nickname, gid, status, reg_time, reg_ip, icode)
VALUES (1, 'admin', '14e1b600b1fd579f47433b88e8d85291', '14e1b600b1fd579f47433b88e8d85291', '超级管理员', 1, 2, extract(epoch from now())::int, '127.0.0.1', 'ADMIN001');

-- 插入默认用户组
INSERT INTO sys_group (id, name, sort, status, create_time) VALUES
(1, '超级管理员', 1, 2, extract(epoch from now())::int),
(71, '一级代理', 71, 2, extract(epoch from now())::int),
(81, '二级代理', 81, 2, extract(epoch from now())::int),
(92, '普通用户', 92, 2, extract(epoch from now())::int);

-- =====================================================
-- 14. 性能优化配置
-- =====================================================

-- 设置表的存储参数
ALTER TABLE sys_user SET (
    fillfactor = 90,  -- 预留10%空间用于HOT更新
    autovacuum_vacuum_scale_factor = 0.1
);

ALTER TABLE wallet_list SET (
    fillfactor = 80,  -- 余额更新频繁,预留更多空间
    autovacuum_vacuum_scale_factor = 0.05
);

-- =====================================================
-- 脚本结束
-- =====================================================

-- 显示数据库信息
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

COMMENT ON DATABASE bx_core IS 'BX平台核心业务库 - 包含用户、配置、产品等核心数据';
