-- ----------------------------
-- Chat2DB export data , export time: 2026-01-11 00:10:16
-- ----------------------------
SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for table cnf_area
-- ----------------------------
DROP TABLE IF EXISTS `cnf_area`;
CREATE TABLE `cnf_area` (
  `id` int(11) NOT NULL,
  `pid` int(11) DEFAULT '0',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table cnf_bank
-- ----------------------------
DROP TABLE IF EXISTS `cnf_bank`;
CREATE TABLE `cnf_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '2',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table cnf_banklog
-- ----------------------------
DROP TABLE IF EXISTS `cnf_banklog`;
CREATE TABLE `cnf_banklog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) DEFAULT '0' COMMENT '1=银行卡,2=支付宝,3=微信,4=USDT',
  `uid` int(11) DEFAULT '0',
  `ifsc` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `upi` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `province_id` int(11) DEFAULT '0',
  `city_id` int(11) DEFAULT '0',
  `bank_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `account` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '银行账号',
  `realname` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '持卡人姓名',
  `routing` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '路由号',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '' COMMENT '手机号',
  `idcard` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '' COMMENT '身份证号',
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `branch_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '支行名称',
  `create_time` int(11) DEFAULT NULL,
  `create_id` int(11) DEFAULT '0',
  `sort` mediumint(9) DEFAULT '1000',
  `status` tinyint(4) DEFAULT '1',
  `currency_id` int(11) DEFAULT NULL COMMENT '币种id',
  `protocal` tinyint(1) DEFAULT '0' COMMENT '协议',
  `address` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '钱包地址',
  `qrcode` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '收款码',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39271 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table cnf_currency
-- ----------------------------
DROP TABLE IF EXISTS `cnf_currency`;
CREATE TABLE `cnf_currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `symbol` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '符号',
  `icon` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '2',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table cnf_market
-- ----------------------------
DROP TABLE IF EXISTS `cnf_market`;
CREATE TABLE `cnf_market` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '时间周期 1min, 5min, 15min, 30min, 60min, 4hour, 1day, 1mon, 1week, 1year',
  `period_id` int(11) DEFAULT NULL,
  `symbol` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '交易对',
  `base_currency` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '	交易对中的基础币种',
  `quote_currency` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '交易对中的报价币种',
  `open` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `close` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `low` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `high` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `amount` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '0' COMMENT '成交量',
  `count` int(11) DEFAULT '0' COMMENT '成交笔数',
  `vol` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '0' COMMENT '成交额, 即 sum(每一笔成交价 * 该笔的成交量)',
  `rise` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '涨跌幅',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `symbol` (`period_id`,`symbol`,`period`) USING BTREE,
  KEY `sp` (`symbol`,`period`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table cnf_pc
-- ----------------------------
DROP TABLE IF EXISTS `cnf_pc`;
CREATE TABLE `cnf_pc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table coupon_list
-- ----------------------------
DROP TABLE IF EXISTS `coupon_list`;
CREATE TABLE `coupon_list` (
  `qx` int(11) DEFAULT NULL COMMENT '期限',
  `gids` text COLLATE utf8_unicode_ci COMMENT '可用产品',
  `status` int(11) DEFAULT NULL COMMENT '状态',
  `type` int(11) DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cover` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount` int(11) DEFAULT NULL,
  `money` int(11) DEFAULT NULL,
  `stock_num` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `effective_time` int(11) DEFAULT NULL,
  `remark` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='券表';

-- ----------------------------
-- Table structure for table coupon_log
-- ----------------------------
DROP TABLE IF EXISTS `coupon_log`;
CREATE TABLE `coupon_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gids` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  `discount` int(11) DEFAULT NULL,
  `money` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `create_day` int(11) NOT NULL,
  `create_id` int(11) NOT NULL,
  `effective_time` int(11) DEFAULT NULL,
  `remark` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `used` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='券使用记录';

-- ----------------------------
-- Table structure for table ext_service
-- ----------------------------
DROP TABLE IF EXISTS `ext_service`;
CREATE TABLE `ext_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `gid` int(11) DEFAULT '0',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  `type` tinyint(1) DEFAULT NULL,
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table ext_task
-- ----------------------------
DROP TABLE IF EXISTS `ext_task`;
CREATE TABLE `ext_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `gid` int(11) DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `day_limit` int(11) DEFAULT '1' COMMENT '用户每日限量',
  `sort` int(11) DEFAULT '1000',
  `award` decimal(16,2) DEFAULT '0.00' COMMENT '奖励',
  `create_time` int(11) DEFAULT '0',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table ext_tasklog
-- ----------------------------
DROP TABLE IF EXISTS `ext_tasklog`;
CREATE TABLE `ext_tasklog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tsn` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uid` int(11) DEFAULT '0',
  `tid` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `award` decimal(16,2) DEFAULT '0.00',
  `voucher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_day` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT '0',
  `submit_time` int(11) DEFAULT '0',
  `check_time` int(11) DEFAULT '0',
  `check_id` int(11) DEFAULT '0',
  `check_remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `tsn` (`tsn`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table fin_cashlog
-- ----------------------------
DROP TABLE IF EXISTS `fin_cashlog`;
CREATE TABLE `fin_cashlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osn` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '单号',
  `out_osn` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `money` decimal(16,2) DEFAULT NULL,
  `real_money` decimal(16,2) DEFAULT '0.00' COMMENT '实到',
  `fee` decimal(16,2) DEFAULT '0.00',
  `fee_mode` tinyint(4) DEFAULT '0' COMMENT '手续费模式',
  `ori_balance` decimal(16,2) DEFAULT NULL,
  `new_balance` decimal(16,2) DEFAULT NULL,
  `pay_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `create_day` int(11) DEFAULT NULL,
  `pay_time` int(11) DEFAULT '0',
  `status` tinyint(4) DEFAULT '1' COMMENT '1待审核 3不通过 7取消 9已通过',
  `receive_phone` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_ifsc` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_type` tinyint(4) DEFAULT '0',
  `receive_bank_id` varchar(100) DEFAULT '0',
  `receive_bank_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_account` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_realname` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_routing` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_address` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_protocol` tinyint(4) DEFAULT '0',
  `receive_qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `check_remark` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `check_id` int(11) DEFAULT '0',
  `check_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `check_time` int(11) DEFAULT '0',
  `client_ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pay_status` tinyint(1) DEFAULT '0' COMMENT '1=待支付 9已支付',
  `pay_msg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `ud` (`uid`,`create_day`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table fin_dtype
-- ----------------------------
DROP TABLE IF EXISTS `fin_dtype`;
CREATE TABLE `fin_dtype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort` int(11) DEFAULT '100',
  `status` tinyint(1) DEFAULT '1',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table fin_paylog
-- ----------------------------
DROP TABLE IF EXISTS `fin_paylog`;
CREATE TABLE `fin_paylog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `money` decimal(16,2) DEFAULT '0.00' COMMENT '金额',
  `rate` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '1' COMMENT '汇率',
  `real_money` decimal(16,2) DEFAULT '0.00',
  `ori_balance` decimal(16,2) DEFAULT '0.00',
  `new_balance` decimal(16,2) DEFAULT '0.00',
  `osn` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '订单号',
  `out_osn` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1' COMMENT '1待支付 2已提交 3未到账 9已确认',
  `pay_type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_first` tinyint(1) DEFAULT '0',
  `pay_time` int(11) DEFAULT '0' COMMENT '支付时间',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `create_day` int(11) DEFAULT '0',
  `sub_time` int(11) DEFAULT NULL COMMENT '提交截图时间',
  `check_time` int(11) DEFAULT '0',
  `check_id` int(11) DEFAULT '0',
  `check_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `check_remark` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `pay_realname` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `pay_remark` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '用户提交备注',
  `pay_banners` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '凭证图片',
  `receive_type` tinyint(4) DEFAULT '0' COMMENT '收款类型',
  `receive_ifsc` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_upi` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_bank_id` int(11) DEFAULT '0',
  `receive_bank_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_account` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '收款账号',
  `receive_realname` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_routing` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `receive_protocol` tinyint(4) DEFAULT '0',
  `receive_address` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '收款地址',
  `receive_qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `odsn` (`osn`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `idx_uid_money_realmoney` (`uid`,`money`,`real_money`),
  KEY `idx_createday_status` (`create_day`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table fin_ptype
-- ----------------------------
DROP TABLE IF EXISTS `fin_ptype`;
CREATE TABLE `fin_ptype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort` int(11) DEFAULT '100',
  `status` tinyint(1) DEFAULT '1',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table gift_lottery
-- ----------------------------
DROP TABLE IF EXISTS `gift_lottery`;
CREATE TABLE `gift_lottery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rsn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `from_money` decimal(16,2) DEFAULT '0.00',
  `to_money` decimal(16,2) DEFAULT '0.00',
  `stock_money` decimal(16,2) DEFAULT '0.00' COMMENT '库存额度',
  `receive_money` decimal(16,2) DEFAULT '0.00' COMMENT '已领取额度',
  `receive_num` int(11) DEFAULT '0',
  `day_limit` int(11) DEFAULT '0',
  `week_limit` int(11) DEFAULT '0',
  `lottery_min` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `rsn` (`rsn`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table gift_lottery_log
-- ----------------------------
DROP TABLE IF EXISTS `gift_lottery_log`;
CREATE TABLE `gift_lottery_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `rid` int(11) DEFAULT NULL,
  `rsn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lsn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `money` decimal(16,2) DEFAULT '0.00',
  `create_day` int(11) DEFAULT '0',
  `create_week` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT '0',
  `create_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `rid` (`rid`) USING BTREE,
  KEY `rsn` (`rsn`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table gift_prize
-- ----------------------------
DROP TABLE IF EXISTS `gift_prize`;
CREATE TABLE `gift_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '奖品类型：1-余额，2-产品，3-实物，4-空，5-奖券',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '奖品名称',
  `cover` varchar(255) NOT NULL DEFAULT '' COMMENT '奖品图片',
  `probability` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '中奖概率',
  `from_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '起始金额',
  `to_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '结束金额',
  `gid` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '代金券ID',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `buyAmountStart` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '购买金额起始',
  `buyAmountEnd` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '购买金额结束',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_gid` (`gid`),
  KEY `idx_coupon_id` (`coupon_id`),
  KEY `idx_probability` (`probability`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='礼品奖品表';

-- ----------------------------
-- Table structure for table gift_prize_log
-- ----------------------------
DROP TABLE IF EXISTS `gift_prize_log`;
CREATE TABLE `gift_prize_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `money` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `prize_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `prize_cover` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `remark` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_time` int(11) NOT NULL,
  `create_day` int(11) NOT NULL,
  `create_ip` int(11) NOT NULL,
  `split_time` int(11) NOT NULL,
  `order_money` int(11) NOT NULL,
  `is_user` int(11) NOT NULL,
  `gift_prize_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for table gift_redpack
-- ----------------------------
DROP TABLE IF EXISTS `gift_redpack`;
CREATE TABLE `gift_redpack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rsn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `total_money` decimal(16,2) DEFAULT '0.00',
  `quantity` int(11) DEFAULT '0',
  `receive_money` decimal(16,2) DEFAULT '0.00',
  `receive_quantity` int(11) DEFAULT '0',
  `create_id` int(11) DEFAULT '0',
  `create_day` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT '0',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `rsn` (`rsn`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table gift_redpack_detail
-- ----------------------------
DROP TABLE IF EXISTS `gift_redpack_detail`;
CREATE TABLE `gift_redpack_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) DEFAULT NULL,
  `rsn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dsn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `money` decimal(16,2) DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `receive_time` int(11) DEFAULT '0',
  `receive_day` int(11) DEFAULT '0',
  `receive_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `dsn` (`dsn`) USING BTREE,
  KEY `rid` (`rid`) USING BTREE,
  KEY `rsn` (`rsn`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table msg_list
-- ----------------------------
DROP TABLE IF EXISTS `msg_list`;
CREATE TABLE `msg_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `covers` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` tinyint(4) DEFAULT '1',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_new` tinyint(1) DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table msg_list_log
-- ----------------------------
DROP TABLE IF EXISTS `msg_list_log`;
CREATE TABLE `msg_list_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fuid` int(11) DEFAULT '0',
  `mid` int(11) DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table news_article
-- ----------------------------
DROP TABLE IF EXISTS `news_article`;
CREATE TABLE `news_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) DEFAULT NULL,
  `title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `publish_time` int(11) DEFAULT NULL COMMENT '发布时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '1待发布 2已发布 4已删除',
  `create_id` int(11) DEFAULT NULL,
  `is_recommend` tinyint(4) DEFAULT '0' COMMENT '是否推荐',
  `label` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '作者',
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `cover` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '封面图',
  `ndesc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '摘要',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cid` (`cid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table news_category
-- ----------------------------
DROP TABLE IF EXISTS `news_category`;
CREATE TABLE `news_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT '2',
  `sort` mediumint(9) DEFAULT '1000',
  `cover` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table news_feedback
-- ----------------------------
DROP TABLE IF EXISTS `news_feedback`;
CREATE TABLE `news_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `problem` tinyint(1) DEFAULT '0',
  `uid` int(11) DEFAULT NULL,
  `phone` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `covers` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `create_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table news_notice
-- ----------------------------
DROP TABLE IF EXISTS `news_notice`;
CREATE TABLE `news_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `sort` mediumint(9) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `create_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table pro_category
-- ----------------------------
DROP TABLE IF EXISTS `pro_category`;
CREATE TABLE `pro_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort` mediumint(9) DEFAULT '1000',
  `status` tinyint(4) DEFAULT '2',
  `cover` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1017 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table pro_goods
-- ----------------------------
DROP TABLE IF EXISTS `pro_goods`;
CREATE TABLE `pro_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gsn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cid` int(11) DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_hot` tinyint(1) DEFAULT '0',
  `days` int(11) DEFAULT '0',
  `price` decimal(16,2) DEFAULT '0.00' COMMENT '单价',
  `price1` decimal(16,2) DEFAULT NULL,
  `price2` decimal(16,2) DEFAULT NULL,
  `rate` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `scale` decimal(16,2) DEFAULT '0.00' COMMENT '总规模',
  `invested` decimal(16,2) DEFAULT '0.00' COMMENT '已投资',
  `v_invested` decimal(16,2) DEFAULT '0.00' COMMENT '虚拟投资额',
  `invest_min` decimal(16,2) DEFAULT '0.00',
  `invest_limit` int(11) DEFAULT '0' COMMENT '限购数量',
  `bonus_type` tinyint(1) DEFAULT '1' COMMENT '分红方式',
  `status` tinyint(4) DEFAULT '1',
  `guarantors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '担保机构',
  `lt_from_money` decimal(16,2) DEFAULT '0.00',
  `lt_to_money` decimal(16,2) DEFAULT '0.00',
  `sort` int(11) DEFAULT '1000',
  `icon` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `covers` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `create_time` int(11) DEFAULT '0',
  `buyday` int(11) DEFAULT NULL COMMENT '只能充值钱包购买',
  `dayout` int(11) DEFAULT NULL COMMENT '多久后可领取',
  `is_normal` int(11) DEFAULT '0',
  `is_xskc` int(11) DEFAULT '0',
  `gift` int(11) DEFAULT '0',
  `goodsindex` int(11) DEFAULT '0',
  `pointshop` int(11) DEFAULT '0',
  `kc` int(11) DEFAULT NULL,
  `sjcjcs` int(11) DEFAULT '0',
  `sendupnum` int(11) DEFAULT NULL,
  `sendnum` int(11) DEFAULT NULL,
  `gifttoself` int(11) DEFAULT NULL,
  `gifttopuser` int(11) DEFAULT NULL,
  `dssj` int(11) DEFAULT NULL,
  `djs` int(11) DEFAULT NULL,
  `selfbg` int(11) DEFAULT NULL,
  `selfintegral` int(11) DEFAULT NULL,
  `Integral` int(11) DEFAULT NULL,
  `yaoqing` int(11) DEFAULT NULL,
  `fgcjcs` int(11) DEFAULT NULL,
  `cjcs` int(11) DEFAULT NULL,
  `price0` int(11) DEFAULT NULL,
  `cvip` int(11) DEFAULT NULL,
  `Firstgive1` int(11) DEFAULT NULL,
  `Firstgive2` int(11) DEFAULT NULL,
  `Firstgive3` int(11) DEFAULT NULL,
  `Firstgive4` int(11) DEFAULT NULL,
  `Firstgive5` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `gsn` (`gsn`) USING BTREE,
  KEY `idx_status_cid` (`status`,`cid`),
  KEY `idx_is_hot_status` (`is_hot`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table pro_guser
-- ----------------------------
DROP TABLE IF EXISTS `pro_guser`;
CREATE TABLE `pro_guser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `gid` int(11) DEFAULT NULL,
  `days` int(11) DEFAULT '0',
  `num` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `create_time` int(11) DEFAULT '0',
  `create_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table pro_order
-- ----------------------------
DROP TABLE IF EXISTS `pro_order`;
CREATE TABLE `pro_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cid` int(11) DEFAULT '0',
  `gid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `days` int(11) DEFAULT '0',
  `price` decimal(16,2) DEFAULT '0.00',
  `price1` decimal(16,2) DEFAULT NULL COMMENT '首次购买送自己',
  `price2` decimal(16,2) DEFAULT NULL COMMENT '首次购买送上级',
  `p1` int(11) DEFAULT '0' COMMENT '审核状态0未审1通过2拒绝',
  `p2` int(11) DEFAULT '0' COMMENT '审核状态0未审1通过2拒绝',
  `p3` int(11) DEFAULT '0' COMMENT '是否首次购买',
  `num` int(11) DEFAULT '0',
  `money` decimal(16,2) DEFAULT '0.00',
  `rate` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `discount` decimal(16,2) DEFAULT '1.00',
  `w1_money` decimal(16,2) DEFAULT '0.00',
  `w2_money` decimal(16,2) DEFAULT '0.00',
  `is_give` tinyint(1) DEFAULT '0',
  `status` tinyint(4) DEFAULT '1',
  `create_day` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  `create_id` int(11) DEFAULT '0',
  `create_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reward_day` int(11) DEFAULT '0',
  `reward_time` int(11) DEFAULT '0',
  `total_reward` decimal(16,2) DEFAULT '0.00',
  `total_days` int(11) DEFAULT '0',
  `pid` int(11) DEFAULT NULL,
  `is_exchange` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `osn` (`osn`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `idx_uid_status_createday` (`uid`,`status`,`create_day`),
  KEY `idx_createday_status` (`create_day`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table pro_reward
-- ----------------------------
DROP TABLE IF EXISTS `pro_reward`;
CREATE TABLE `pro_reward` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `oid` int(11) DEFAULT '0',
  `osn` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` tinyint(1) DEFAULT '0' COMMENT '1投资收益 2返佣',
  `level` tinyint(1) DEFAULT '0',
  `base_money` decimal(16,2) DEFAULT '0.00',
  `rate` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `money` decimal(16,2) DEFAULT '0.00',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  `create_day` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_uid_type_createtime` (`uid`,`type`,`create_time`),
  KEY `idx_type_money` (`type`,`money`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_access
-- ----------------------------
DROP TABLE IF EXISTS `sys_access`;
CREATE TABLE `sys_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `gid` int(11) DEFAULT '0',
  `node_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_config
-- ----------------------------
DROP TABLE IF EXISTS `sys_config`;
CREATE TABLE `sys_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skey` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `single` tinyint(4) DEFAULT '0' COMMENT '0或1',
  `config` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `update_time` int(11) DEFAULT NULL,
  `is_show` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `skey` (`skey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=333 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_group
-- ----------------------------
DROP TABLE IF EXISTS `sys_group`;
CREATE TABLE `sys_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `sort` int(11) DEFAULT '100',
  `status` tinyint(4) DEFAULT '1',
  `cover` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=780 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_gzh
-- ----------------------------
DROP TABLE IF EXISTS `sys_gzh`;
CREATE TABLE `sys_gzh` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '所属用户',
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '公众号名称',
  `appid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `appsecret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `mch_id` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '微信支付商户号',
  `pay_key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '微信支付密钥',
  `token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '开发者模式的token',
  `access_token_time` int(11) DEFAULT NULL COMMENT 'token的更新时间',
  `access_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `jsapi_ticket_time` int(11) DEFAULT NULL COMMENT 'ticket更新时间',
  `jsapi_ticket` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `status` tinyint(4) DEFAULT '1' COMMENT '0或1',
  `gh_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '原始id',
  `biz` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '公众号标识',
  `tpl_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '模板消息模板id',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `appid` (`appid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='微信公众号信息';

-- ----------------------------
-- Table structure for table sys_lang
-- ----------------------------
DROP TABLE IF EXISTS `sys_lang`;
CREATE TABLE `sys_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cn` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `tw` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `es` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '西班牙',
  `mx` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '墨西哥',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `cn` (`cn`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=317 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_log
-- ----------------------------
DROP TABLE IF EXISTS `sys_log`;
CREATE TABLE `sys_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `opt_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '操作名称',
  `sql_str` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `create_time` int(11) DEFAULT NULL,
  `create_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_mcode
-- ----------------------------
DROP TABLE IF EXISTS `sys_mcode`;
CREATE TABLE `sys_mcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stype` tinyint(4) DEFAULT NULL,
  `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `code` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0',
  `verify_num` tinyint(4) DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  `create_day` int(11) DEFAULT '0',
  `verify_time` int(11) DEFAULT '0',
  `create_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `scon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sp` (`stype`,`email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_node
-- ----------------------------
DROP TABLE IF EXISTS `sys_node`;
CREATE TABLE `sys_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '名称',
  `nkey` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `type` tinyint(4) DEFAULT '0' COMMENT '0节点1菜单',
  `ico` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `public` tinyint(4) DEFAULT '0' COMMENT '是否是公共节点',
  `sort` mediumint(9) DEFAULT '100',
  `pre_path` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `url` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `remark` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `nkey` (`nkey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=510 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_notice
-- ----------------------------
DROP TABLE IF EXISTS `sys_notice`;
CREATE TABLE `sys_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '0待发 1发送失败 2发送成功',
  `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT '参数',
  `remark` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `send_time` int(11) DEFAULT '0',
  `send_msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT '发送返回消息',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_pset
-- ----------------------------
DROP TABLE IF EXISTS `sys_pset`;
CREATE TABLE `sys_pset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `skey` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT '配置内容',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `skey` (`skey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC COMMENT='大厅相关杂项';

-- ----------------------------
-- Table structure for table sys_user
-- ----------------------------
DROP TABLE IF EXISTS `sys_user`;
CREATE TABLE `sys_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '父级id',
  `gid` mediumint(9) DEFAULT '0' COMMENT '用户分组',
  `down_level` int(11) DEFAULT '0',
  `openid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `unionid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `account` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '账号',
  `phone` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '手机号',
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '密码',
  `password2` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '二级密码',
  `is_pwd2_set` tinyint(1) DEFAULT '0',
  `first_pay_day` int(11) DEFAULT '0',
  `stop_commission` tinyint(1) DEFAULT '0',
  `balance` decimal(16,2) DEFAULT '0.00' COMMENT '可用余额',
  `fz_balance` decimal(16,2) DEFAULT '0.00' COMMENT '冻结中余额',
  `total_invest` decimal(16,2) DEFAULT '0.00',
  `total_invest2` decimal(16,2) DEFAULT '0.00',
  `lottery` int(11) DEFAULT '0',
  `is_effective` tinyint(1) DEFAULT '0',
  `language` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '语言',
  `apikey` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_rsa` tinyint(4) DEFAULT '0' COMMENT '是否开启rsa',
  `rsa_public` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `rsa_private` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `nickname` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '昵称',
  `realname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '真实姓名',
  `authentication` tinyint(4) DEFAULT '0' COMMENT '是否已认证',
  `usn` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '编号',
  `icode` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '邀请码',
  `status` tinyint(4) DEFAULT '2' COMMENT '1禁用2正常',
  `is_ai` tinyint(4) DEFAULT '0',
  `forbid_time_flag` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '0',
  `forbid_time` bigint(20) DEFAULT '0',
  `forbid_msg` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `is_google` tinyint(4) DEFAULT '0' COMMENT '是否开启验证',
  `google_secret` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `google_hide` tinyint(4) DEFAULT '0' COMMENT '隐藏谷歌信息',
  `latitude` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '纬度',
  `longitude` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '经度',
  `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `sex` tinyint(4) DEFAULT '0' COMMENT '1男2女0未知',
  `country` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '国家',
  `province` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '省份',
  `city` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '城市',
  `birthday` int(11) DEFAULT '0',
  `reg_time` int(11) DEFAULT NULL COMMENT '注册时间',
  `reg_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '注册ip',
  `login_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '最后登录ip',
  `headimgurl` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT '头像',
  `white_ip` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `avatarurl` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `is_count` tinyint(1) DEFAULT '0',
  `pidg1` int(11) NOT NULL,
  `pidg2` int(11) NOT NULL,
  `teamcount` int(11) NOT NULL,
  `pids` varchar(500) DEFAULT NULL,
  `cbank` int(11) DEFAULT '0',
  `icode_status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `openid` (`openid`) USING BTREE,
  UNIQUE KEY `account` (`account`) USING BTREE,
  UNIQUE KEY `icode` (`icode`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  KEY `unionid` (`unionid`) USING BTREE,
  KEY `is_ai` (`is_ai`) USING BTREE,
  KEY `idx_pid_createday` (`pid`,`first_pay_day`)
) ENGINE=InnoDB AUTO_INCREMENT=1000000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_user_answer
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_answer`;
CREATE TABLE `sys_user_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `q1` int(11) DEFAULT NULL,
  `a1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `q2` int(11) DEFAULT NULL,
  `a2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `q3` int(11) DEFAULT NULL,
  `a3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_user_question
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_question`;
CREATE TABLE `sys_user_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_user_question_log
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_question_log`;
CREATE TABLE `sys_user_question_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_user_rauth
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_rauth`;
CREATE TABLE `sys_user_rauth` (
  `uid` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT '0',
  `realname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `number` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `front` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `back` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1' COMMENT '1待审核 2未通过 3已认证',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `check_time` int(11) DEFAULT '0',
  `check_remark` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  UNIQUE KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_user_setting
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_setting`;
CREATE TABLE `sys_user_setting` (
  `uid` int(11) NOT NULL,
  `execution_max` float(16,2) DEFAULT NULL,
  `execution_min` float(16,2) DEFAULT NULL,
  `price_deviation` float(16,2) DEFAULT NULL,
  `password_free_amount` float(16,2) DEFAULT NULL,
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_user_token
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_token`;
CREATE TABLE `sys_user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `uid` int(11) DEFAULT '0',
  `token` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '0或1',
  `iscom` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `token` (`token`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_user_wechat
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_wechat`;
CREATE TABLE `sys_user_wechat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `unionid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `nickname` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `sex` tinyint(4) DEFAULT NULL,
  `country` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `province` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `city` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `subscribe` tinyint(4) DEFAULT '0',
  `subscribe_time` int(11) DEFAULT NULL,
  `subscribe_scene` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `avatarurl` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `headimgurl` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `openid` (`openid`) USING BTREE,
  KEY `unionid` (`unionid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table sys_vcode
-- ----------------------------
DROP TABLE IF EXISTS `sys_vcode`;
CREATE TABLE `sys_vcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stype` tinyint(4) DEFAULT NULL,
  `phone` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `code` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0',
  `verify_num` tinyint(4) DEFAULT '0',
  `create_time` int(11) DEFAULT NULL,
  `create_day` int(11) DEFAULT '0',
  `verify_time` int(11) DEFAULT '0',
  `create_ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `scon` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sp` (`stype`,`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=181456 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table wallet_list
-- ----------------------------
DROP TABLE IF EXISTS `wallet_list`;
CREATE TABLE `wallet_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `waddr` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT '0' COMMENT '币种id',
  `balance` decimal(16,2) DEFAULT '0.00',
  `fz_balance` decimal(16,2) DEFAULT '0.00',
  `create_time` int(11) DEFAULT '0',
  `lasttime` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `waddr` (`waddr`) USING BTREE,
  UNIQUE KEY `usymb` (`uid`,`cid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for table wallet_log
-- ----------------------------
DROP TABLE IF EXISTS `wallet_log`;
CREATE TABLE `wallet_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wid` int(11) DEFAULT '0' COMMENT '钱包id',
  `uid` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT '0' COMMENT '1=转入,2=转出,3=买入,4=卖出,11=充值,12=冻结,13=解冻,14=购买NFT卡片,21=NFT收益,22=直推收益,23=动态收益',
  `money` decimal(16,2) DEFAULT '0.00',
  `ori_balance` decimal(16,2) DEFAULT '0.00',
  `new_balance` decimal(16,2) DEFAULT '0.00',
  `fkey` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remark` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_id` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT '0',
  `create_day` int(11) DEFAULT '0',
  `lasttime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `wid` (`wid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `idx_uid_type_createday` (`uid`,`type`,`create_day`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

SET FOREIGN_KEY_CHECKS=1;
