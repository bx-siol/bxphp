namespace  Application.Contracts.Users.Dtos
{
    /// <summary>
    /// 用户DTO
    /// </summary>
    public class UserDto
    {
        /// <summary>
        /// 用户ID
        /// </summary>
        public int Id { get; set; }

        /// <summary>
        /// 账号
        /// </summary>
        public string Account { get; set; } = string.Empty;

        /// <summary>
        /// 昵称
        /// </summary>
        public string? Nickname { get; set; }

        /// <summary>
        /// 真实姓名
        /// </summary>
        public string? Realname { get; set; }

        /// <summary>
        /// 手机号
        /// </summary>
        public string? Phone { get; set; }

        /// <summary>
        /// 可用余额
        /// </summary>
        public decimal Balance { get; set; }

        /// <summary>
        /// 冻结余额
        /// </summary>
        public decimal FrozenBalance { get; set; }

        /// <summary>
        /// 状态(1=禁用,2=正常)
        /// </summary>
        public int Status { get; set; }

        /// <summary>
        /// 用户组ID
        /// </summary>
        public int GroupId { get; set; }

        /// <summary>
        /// 上级用户ID
        /// </summary>
        public int ParentId { get; set; }

        /// <summary>
        /// 团队人数
        /// </summary>
        public int TeamCount { get; set; }

        /// <summary>
        /// 登录IP
        /// </summary>
        public string? LoginIp { get; set; }

        /// <summary>
        /// 登录时间
        /// </summary>
        public int LoginTime { get; set; }

        /// <summary>
        /// 注册时间
        /// </summary>
        public int CreateTime { get; set; }

        /// <summary>
        /// 邀请码
        /// </summary>
        public string? InviteCode { get; set; }

        /// <summary>
        /// 总投资金额
        /// </summary>
        public decimal TotalInvest { get; set; }
    }

    /// <summary>
    /// 登录结果
    /// </summary>
    public class LoginResult
    {
        /// <summary>
        /// 用户信息
        /// </summary>
        public UserDto User { get; set; } = null!;

        /// <summary>
        /// 访问令牌
        /// </summary>
        public string Token { get; set; } = string.Empty;

        /// <summary>
        /// 令牌过期时间(秒)
        /// </summary>
        public int ExpiresIn { get; set; }
    }
}
