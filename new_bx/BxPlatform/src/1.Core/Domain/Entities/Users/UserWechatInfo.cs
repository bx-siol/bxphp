using SqlSugar;

namespace  Domain.Entities.Users
{
    /// <summary>
    /// 用户微信信息实体
    /// 对应数据库: bx_core.sys_user_wechat
    /// </summary>
    [SugarTable("sys_user_wechat")]
    public class UserWechatInfo
    {
        /// <summary>
        /// 主键ID
        /// </summary>
        [SugarColumn(IsPrimaryKey = true, IsIdentity = true)]
        public int Id { get; set; }

        /// <summary>
        /// 微信OpenID
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? OpenId { get; set; }

        /// <summary>
        /// 微信UnionID
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? UnionId { get; set; }

        /// <summary>
        /// 昵称
        /// </summary>
        [SugarColumn(Length = 64, IsNullable = true)]
        public string? Nickname { get; set; }

        /// <summary>
        /// 性别(0:未知 1:男 2:女)
        /// </summary>
        public int Sex { get; set; }

        /// <summary>
        /// 国家
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Country { get; set; }

        /// <summary>
        /// 省份
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Province { get; set; }

        /// <summary>
        /// 城市
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? City { get; set; }

        /// <summary>
        /// 是否关注公众号(0:未关注 1:已关注)
        /// </summary>
        public int Subscribe { get; set; }

        /// <summary>
        /// 关注时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "subscribe_time")]
        public int SubscribeTime { get; set; }

        /// <summary>
        /// 关注场景
        /// </summary>
        [SugarColumn(ColumnName = "subscribe_scene", Length = 32, IsNullable = true)]
        public string? SubscribeScene { get; set; }

        /// <summary>
        /// 头像URL
        /// </summary>
        [SugarColumn(ColumnName = "avatarurl", IsNullable = true, ColumnDataType = "text")]
        public string? AvatarUrl { get; set; }

        /// <summary>
        /// 微信头像URL
        /// </summary>
        [SugarColumn(ColumnName = "headimgurl", IsNullable = true, ColumnDataType = "text")]
        public string? HeadImageUrl { get; set; }

        /// <summary>
        /// 更新时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "update_time")]
        public int UpdateTime { get; set; }
    }
}
