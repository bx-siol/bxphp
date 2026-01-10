using SqlSugar;

namespace BxPlatform.Domain.Entities.Users
{
    /// <summary>
    /// 用户登录令牌实体
    /// 对应数据库: bx_core.sys_user_token
    /// </summary>
    [SugarTable("sys_user_token")]
    public class UserToken
    {
        /// <summary>
        /// 令牌ID(自增主键)
        /// </summary>
        [SugarColumn(IsPrimaryKey = true, IsIdentity = true)]
        public int Id { get; set; }

        /// <summary>
        /// 账号
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Account { get; set; }

        /// <summary>
        /// 用户ID
        /// </summary>
        public int Uid { get; set; }

        /// <summary>
        /// 令牌(32位字符,唯一)
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = false)]
        public string Token { get; set; } = string.Empty;

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
        /// 状态(0:有效 1:失效)
        /// </summary>
        public int Status { get; set; }

        /// <summary>
        /// 是否商户(0:普通用户 1:商户)
        /// </summary>
        [SugarColumn(ColumnName = "iscom")]
        public int IsMerchant { get; set; }
    }
}
