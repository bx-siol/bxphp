using SqlSugar;
using System;
using System.Collections.Generic;

namespace Infrastructure.Sharding
{
    /// <summary>
    /// SqlSugar 分表配置类
    /// 使用 SqlSugar 原生分表功能
    /// </summary>
    public static class SqlSugarShardingConfig
    {
        /// <summary>
        /// 配置用户表分表规则
        /// 按用户ID取模分16张表
        /// </summary>
        public static void ConfigureUserSharding(ISqlSugarClient db)
        {
            // 用户表分表配置 - 按ID取模分表
            db.Aop.DataExecuting = (oldValue, entityInfo) =>
            {
                // 在插入或更新时自动计算分表
                if (entityInfo.EntityName == "SysUser" && entityInfo.PropertyName == "Id")
                {
                    var id = Convert.ToInt32(oldValue);
                    var tableIndex = id % 16;
                    entityInfo.EntityValue = id;
                }
            };
        }

        /// <summary>
        /// 初始化分表
        /// 自动创建16张用户表
        /// </summary>
        public static void InitializeUserTables(ISqlSugarClient db)
        {
            // 使用 SqlSugar 的分表功能自动创建表
            for (int i = 0; i < 16; i++)
            {
                var tableName = $"sys_user_{i}";

                // 如果表不存在则创建
                if (!db.DbMaintenance.IsAnyTable(tableName, false))
                {
                    db.CodeFirst
                        .As(typeof(Domain.Entities.Users.SysUser), tableName)
                        .InitTables(typeof(Domain.Entities.Users.SysUser));
                }
            }
        }

        /// <summary>
        /// 获取用户表名
        /// 根据用户ID计算分表名称
        /// </summary>
        public static string GetUserTableName(int userId)
        {
            return $"sys_user_{userId % 16}";
        }

        /// <summary>
        /// 配置充值记录分表 (32张表)
        /// </summary>
        public static string GetPayLogTableName(int userId)
        {
            return $"fin_paylog_{userId % 32}";
        }

        /// <summary>
        /// 配置提现记录分表 (32张表)
        /// </summary>
        public static string GetCashLogTableName(int userId)
        {
            return $"fin_cashlog_{userId % 32}";
        }

        /// <summary>
        /// 配置钱包表分表 (16张表)
        /// </summary>
        public static string GetWalletTableName(int userId)
        {
            return $"wallet_list_{userId % 16}";
        }

        /// <summary>
        /// 配置订单表分表 (32张表)
        /// </summary>
        public static string GetOrderTableName(int userId)
        {
            return $"pro_order_{userId % 32}";
        }

        /// <summary>
        /// 获取所有用户分表名称
        /// </summary>
        public static List<string> GetAllUserTableNames()
        {
            var tables = new List<string>(16);
            for (int i = 0; i < 16; i++)
            {
                tables.Add($"sys_user_{i}");
            }
            return tables;
        }

        /// <summary>
        /// 配置按时间分表(日志表)
        /// </summary>
        public static string GetLogTableName(DateTime date)
        {
            return $"sys_log_{date:yyyyMM}";
        }

        /// <summary>
        /// 配置按时间分表(日志表) - 当前月份
        /// </summary>
        public static string GetLogTableName()
        {
            return GetLogTableName(DateTime.Now);
        }
    }
}
