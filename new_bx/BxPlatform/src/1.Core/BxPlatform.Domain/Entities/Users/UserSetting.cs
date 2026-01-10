using SqlSugar;

namespace BxPlatform.Domain.Entities.Users
{
    /// <summary>
    /// 用户个性化设置实体
    /// 对应数据库: bx_core.sys_user_setting
    /// </summary>
    [SugarTable("sys_user_setting")]
    public class UserSetting
    {
        /// <summary>
        /// 用户ID(主键,外键关联sys_user)
        /// </summary>
        [SugarColumn(IsPrimaryKey = true)]
        public int Uid { get; set; }

        /// <summary>
        /// 最大执行金额
        /// </summary>
        [SugarColumn(ColumnName = "execution_max", DecimalDigits = 2, IsNullable = true)]
        public decimal? ExecutionMax { get; set; }

        /// <summary>
        /// 最小执行金额
        /// </summary>
        [SugarColumn(ColumnName = "execution_min", DecimalDigits = 2, IsNullable = true)]
        public decimal? ExecutionMin { get; set; }

        /// <summary>
        /// 价格偏差
        /// </summary>
        [SugarColumn(ColumnName = "price_deviation", DecimalDigits = 2, IsNullable = true)]
        public decimal? PriceDeviation { get; set; }

        /// <summary>
        /// 免密金额
        /// </summary>
        [SugarColumn(ColumnName = "password_free_amount", DecimalDigits = 2, IsNullable = true)]
        public decimal? PasswordFreeAmount { get; set; }
    }
}
