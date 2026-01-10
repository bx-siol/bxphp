using SqlSugar;

namespace  Domain.Entities.Users
{
    /// <summary>
    /// 用户实名认证实体
    /// 对应数据库: bx_core.sys_user_rauth
    /// </summary>
    [SugarTable("sys_user_rauth")]
    public class UserRealNameAuth
    {
        /// <summary>
        /// 用户ID(主键,外键关联sys_user)
        /// </summary>
        [SugarColumn(IsPrimaryKey = true)]
        public int Uid { get; set; }

        /// <summary>
        /// 认证类型(0:身份证 1:护照 2:驾驶证)
        /// </summary>
        public int Type { get; set; }

        /// <summary>
        /// 真实姓名
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Realname { get; set; }

        /// <summary>
        /// 证件号码
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Number { get; set; }

        /// <summary>
        /// 证件正面照
        /// </summary>
        [SugarColumn(Length = 128, IsNullable = true)]
        public string? Front { get; set; }

        /// <summary>
        /// 证件背面照
        /// </summary>
        [SugarColumn(Length = 128, IsNullable = true)]
        public string? Back { get; set; }

        /// <summary>
        /// 审核状态(1:待审核 2:未通过 3:已认证)
        /// </summary>
        public int Status { get; set; }

        /// <summary>
        /// 创建时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "create_time")]
        public int CreateTime { get; set; }

        /// <summary>
        /// 更新时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "update_time")]
        public int UpdateTime { get; set; }

        /// <summary>
        /// 审核时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "check_time")]
        public int CheckTime { get; set; }

        /// <summary>
        /// 审核备注
        /// </summary>
        [SugarColumn(ColumnName = "check_remark", Length = 128, IsNullable = true)]
        public string? CheckRemark { get; set; }
    }
}
