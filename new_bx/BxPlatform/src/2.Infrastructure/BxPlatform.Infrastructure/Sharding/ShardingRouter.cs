using System;

namespace BxPlatform.Infrastructure.Sharding
{
    /// <summary>
    /// 分表路由器
    /// 根据业务规则计算目标表名
    /// </summary>
    public static class ShardingRouter
    {
        /// <summary>
        /// 获取用户表名(16张分表)
        /// 规则: sys_user_{uid % 16}
        /// </summary>
        public static string GetUserTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"sys_user_{userId % 16}";
        }

        /// <summary>
        /// 获取充值记录表名(32张分表)
        /// 规则: fin_paylog_{uid % 32}
        /// </summary>
        public static string GetPayLogTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"fin_paylog_{userId % 32}";
        }

        /// <summary>
        /// 获取提现记录表名(32张分表)
        /// 规则: fin_cashlog_{uid % 32}
        /// </summary>
        public static string GetCashLogTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"fin_cashlog_{userId % 32}";
        }

        /// <summary>
        /// 获取钱包表名(16张分表)
        /// 规则: wallet_list_{uid % 16}
        /// </summary>
        public static string GetWalletTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"wallet_list_{userId % 16}";
        }

        /// <summary>
        /// 获取钱包流水表名(32张分表)
        /// 规则: wallet_log_{uid % 32}
        /// </summary>
        public static string GetWalletLogTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"wallet_log_{userId % 32}";
        }

        /// <summary>
        /// 获取订单表名(32张分表)
        /// 规则: pro_order_{uid % 32}
        /// </summary>
        public static string GetOrderTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"pro_order_{userId % 32}";
        }

        /// <summary>
        /// 获取收益记录表名(16张分表)
        /// 规则: pro_reward_{uid % 16}
        /// </summary>
        public static string GetRewardTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"pro_reward_{userId % 16}";
        }

        /// <summary>
        /// 获取优惠券记录表名(8张分表)
        /// 规则: coupon_log_{uid % 8}
        /// </summary>
        public static string GetCouponLogTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"coupon_log_{userId % 8}";
        }

        /// <summary>
        /// 获取银行卡信息表名(16张分表)
        /// 规则: cnf_banklog_{uid % 16}
        /// </summary>
        public static string GetBankLogTable(int userId)
        {
            if (userId <= 0)
                throw new ArgumentException("用户ID必须大于0", nameof(userId));

            return $"cnf_banklog_{userId % 16}";
        }

        /// <summary>
        /// 获取系统日志表名(按月分区)
        /// 规则: sys_log_YYYYMM
        /// </summary>
        public static string GetSystemLogTable(DateTime date)
        {
            return $"sys_log_{date:yyyyMM}";
        }

        /// <summary>
        /// 获取系统日志表名(使用当前月份)
        /// </summary>
        public static string GetSystemLogTable()
        {
            return GetSystemLogTable(DateTime.Now);
        }

        /// <summary>
        /// 获取所有用户表名
        /// </summary>
        public static List<string> GetAllUserTables()
        {
            var tables = new List<string>(16);
            for (int i = 0; i < 16; i++)
            {
                tables.Add($"sys_user_{i}");
            }
            return tables;
        }

        /// <summary>
        /// 获取所有充值记录表名
        /// </summary>
        public static List<string> GetAllPayLogTables()
        {
            var tables = new List<string>(32);
            for (int i = 0; i < 32; i++)
            {
                tables.Add($"fin_paylog_{i}");
            }
            return tables;
        }

        /// <summary>
        /// 获取所有订单表名
        /// </summary>
        public static List<string> GetAllOrderTables()
        {
            var tables = new List<string>(32);
            for (int i = 0; i < 32; i++)
            {
                tables.Add($"pro_order_{i}");
            }
            return tables;
        }
    }
}
