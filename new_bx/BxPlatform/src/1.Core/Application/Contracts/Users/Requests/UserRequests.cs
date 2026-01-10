using System.ComponentModel.DataAnnotations;

namespace  Application.Contracts.Users.Requests
{
    /// <summary>
    /// 登录请求
    /// </summary>
    public class LoginRequest
    {
        /// <summary>
        /// 账号
        /// </summary>
        [Required(ErrorMessage = "账号不能为空")]
        public string Account { get; set; } = string.Empty;

        /// <summary>
        /// 密码
        /// </summary>
        [Required(ErrorMessage = "密码不能为空")]
        public string Password { get; set; } = string.Empty;

        /// <summary>
        /// 登录IP
        /// </summary>
        public string? LoginIp { get; set; }
    }

    /// <summary>
    /// 注册请求
    /// </summary>
    public class RegisterRequest
    {
        /// <summary>
        /// 账号
        /// </summary>
        [Required(ErrorMessage = "账号不能为空")]
        [StringLength(50, MinimumLength = 4, ErrorMessage = "账号长度为4-50个字符")]
        public string Account { get; set; } = string.Empty;

        /// <summary>
        /// 密码
        /// </summary>
        [Required(ErrorMessage = "密码不能为空")]
        [StringLength(50, MinimumLength = 6, ErrorMessage = "密码长度为6-50个字符")]
        public string Password { get; set; } = string.Empty;

        /// <summary>
        /// 昵称
        /// </summary>
        public string? Nickname { get; set; }

        /// <summary>
        /// 手机号
        /// </summary>
        [Phone(ErrorMessage = "手机号格式不正确")]
        public string? Phone { get; set; }

        /// <summary>
        /// 邀请码
        /// </summary>
        public string? InviteCode { get; set; }

        /// <summary>
        /// 注册IP
        /// </summary>
        public string? RegisterIp { get; set; }
    }

    /// <summary>
    /// 更新用户请求
    /// </summary>
    public class UpdateUserRequest
    {
        /// <summary>
        /// 用户ID
        /// </summary>
        [Required(ErrorMessage = "用户ID不能为空")]
        public int UserId { get; set; }

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
        [Phone(ErrorMessage = "手机号格式不正确")]
        public string? Phone { get; set; }

        /// <summary>
        /// 银行卡号
        /// </summary>
        public string? BankCard { get; set; }
    }

    /// <summary>
    /// 用户查询请求
    /// </summary>
    public class UserQueryRequest
    {
        /// <summary>
        /// 关键字(账号/手机号/昵称)
        /// </summary>
        public string? Keyword { get; set; }

        /// <summary>
        /// 状态(0=全部,1=禁用,2=正常)
        /// </summary>
        public int Status { get; set; }

        /// <summary>
        /// 用户组ID
        /// </summary>
        public int? GroupId { get; set; }

        /// <summary>
        /// 页码
        /// </summary>
        public int PageIndex { get; set; } = 1;

        /// <summary>
        /// 每页大小
        /// </summary>
        public int PageSize { get; set; } = 15;
    }
}
