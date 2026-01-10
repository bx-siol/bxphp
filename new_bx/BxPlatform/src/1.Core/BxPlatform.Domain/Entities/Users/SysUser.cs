using System;
using BxPlatform.Domain.Entities.Base;
using BxPlatform.Domain.Enums;
using SqlSugar;

namespace BxPlatform.Domain.Entities.Users
{
    /// <summary>
    /// 系统用户聚合根
    /// 对应数据库: bx_core.sys_user
    /// 数据库策略: 单表(不分表),使用索引优化
    /// 应用层分表: 可选按 uid % 16 分表
    /// </summary>
    [SugarTable("sys_user")]
    public class SysUser : Entity, IAggregateRoot
    {
        #region 基础信息

        /// <summary>
        /// 用户ID(主键,随机6位数)
        /// </summary>
        [SugarColumn(IsPrimaryKey = true)]
        public new int Id { get; set; }

        /// <summary>
        /// 父级用户ID
        /// </summary>
        [SugarColumn(ColumnName = "pid")]
        public int ParentId { get; set; }

        /// <summary>
        /// 用户组ID
        /// </summary>
        [SugarColumn(ColumnName = "gid")]
        public int GroupId { get; set; }

        /// <summary>
        /// 下级层级
        /// </summary>
        [SugarColumn(ColumnName = "down_level")]
        public int DownLevel { get; set; }

        #endregion

        #region 第三方登录

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

        #endregion

        #region 账号信息

        /// <summary>
        /// 账号(登录用户名,唯一)
        /// </summary>
        [SugarColumn(Length = 64, IsNullable = false)]
        public string Account { get; private set; }

        /// <summary>
        /// 手机号
        /// </summary>
        [SugarColumn(Length = 16, IsNullable = true)]
        public string? Phone { get; private set; }

        /// <summary>
        /// 邮箱
        /// </summary>
        [SugarColumn(Length = 128, IsNullable = true)]
        public string? Email { get; private set; }

        /// <summary>
        /// 登录密码(SHA1加密)
        /// </summary>
        [SugarColumn(Length = 64, IsNullable = true)]
        public string? Password { get; private set; }

        /// <summary>
        /// 二级密码(SHA1加密)
        /// </summary>
        [SugarColumn(ColumnName = "password2", Length = 64, IsNullable = true)]
        public string? SecondaryPassword { get; private set; }

        /// <summary>
        /// 是否设置了二级密码(0:未设置 1:已设置)
        /// </summary>
        [SugarColumn(ColumnName = "is_pwd2_set")]
        public int IsSecondaryPasswordSet { get; set; }

        #endregion

        #region 财务信息

        /// <summary>
        /// 首次充值日期(Ymd格式,如:20260111)
        /// </summary>
        [SugarColumn(ColumnName = "first_pay_day")]
        public int FirstPayDay { get; private set; }

        /// <summary>
        /// 停止佣金(0:正常 1:停止)
        /// </summary>
        [SugarColumn(ColumnName = "stop_commission")]
        public int StopCommission { get; set; }

        /// <summary>
        /// 可用余额
        /// </summary>
        [SugarColumn(DecimalDigits = 2)]
        public decimal Balance { get; private set; }

        /// <summary>
        /// 冻结余额
        /// </summary>
        [SugarColumn(ColumnName = "fz_balance", DecimalDigits = 2)]
        public decimal FrozenBalance { get; private set; }

        /// <summary>
        /// 总投资金额
        /// </summary>
        [SugarColumn(ColumnName = "total_invest", DecimalDigits = 2)]
        public decimal TotalInvest { get; private set; }

        /// <summary>
        /// 总投资金额2
        /// </summary>
        [SugarColumn(ColumnName = "total_invest2", DecimalDigits = 2)]
        public decimal TotalInvest2 { get; private set; }

        /// <summary>
        /// 抽奖次数
        /// </summary>
        public int Lottery { get; set; }

        /// <summary>
        /// 是否有效用户(0:无效 1:有效)
        /// </summary>
        [SugarColumn(ColumnName = "is_effective")]
        public int IsEffective { get; set; }

        #endregion

        #region API和安全

        /// <summary>
        /// 语言设置
        /// </summary>
        [SugarColumn(Length = 8, IsNullable = true)]
        public string? Language { get; set; }

        /// <summary>
        /// API密钥
        /// </summary>
        [SugarColumn(ColumnName = "apikey", Length = 64, IsNullable = true)]
        public string? ApiKey { get; set; }

        /// <summary>
        /// 是否启用RSA(0:否 1:是)
        /// </summary>
        [SugarColumn(ColumnName = "is_rsa")]
        public int IsRsa { get; set; }

        /// <summary>
        /// RSA公钥
        /// </summary>
        [SugarColumn(ColumnName = "rsa_public", IsNullable = true, ColumnDataType = "text")]
        public string? RsaPublicKey { get; set; }

        /// <summary>
        /// RSA私钥
        /// </summary>
        [SugarColumn(ColumnName = "rsa_private", IsNullable = true, ColumnDataType = "text")]
        public string? RsaPrivateKey { get; set; }

        #endregion

        #region 个人信息

        /// <summary>
        /// 昵称
        /// </summary>
        [SugarColumn(Length = 64, IsNullable = true)]
        public string? Nickname { get; private set; }

        /// <summary>
        /// 真实姓名
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Realname { get; private set; }

        /// <summary>
        /// 实名认证状态(0:未认证 1:已认证)
        /// </summary>
        public int Authentication { get; set; }

        /// <summary>
        /// 用户序列号
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Usn { get; set; }

        /// <summary>
        /// 邀请码(唯一)
        /// </summary>
        [SugarColumn(ColumnName = "icode", Length = 8, IsNullable = true)]
        public string? InviteCode { get; private set; }

        /// <summary>
        /// 用户状态(1:禁用 2:正常 99:已删除)
        /// </summary>
        public UserStatus Status { get; private set; }

        /// <summary>
        /// 是否AI用户(0:普通用户 1:AI用户)
        /// </summary>
        [SugarColumn(ColumnName = "is_ai")]
        public int IsAi { get; set; }

        #endregion

        #region 禁用相关

        /// <summary>
        /// 禁用时间标志
        /// </summary>
        [SugarColumn(ColumnName = "forbid_time_flag", Length = 8)]
        public string ForbidTimeFlag { get; set; } = "0";

        /// <summary>
        /// 禁用时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "forbid_time")]
        public long ForbidTime { get; set; }

        /// <summary>
        /// 禁用原因
        /// </summary>
        [SugarColumn(ColumnName = "forbid_msg", Length = 128, IsNullable = true)]
        public string? ForbidMessage { get; set; }

        #endregion

        #region 谷歌验证器

        /// <summary>
        /// 是否启用谷歌验证器(0:否 1:是)
        /// </summary>
        [SugarColumn(ColumnName = "is_google")]
        public int IsGoogleAuthEnabled { get; set; }

        /// <summary>
        /// 谷歌验证器密钥
        /// </summary>
        [SugarColumn(ColumnName = "google_secret", Length = 32, IsNullable = true)]
        public string? GoogleSecret { get; set; }

        /// <summary>
        /// 谷歌验证器隐藏(0:显示 1:隐藏)
        /// </summary>
        [SugarColumn(ColumnName = "google_hide")]
        public int GoogleHide { get; set; }

        #endregion

        #region 位置信息

        /// <summary>
        /// 纬度
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Latitude { get; set; }

        /// <summary>
        /// 经度
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = true)]
        public string? Longitude { get; set; }

        /// <summary>
        /// 详细地址
        /// </summary>
        [SugarColumn(Length = 200, IsNullable = true)]
        public string? Address { get; set; }

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
        /// 生日(Ymd格式)
        /// </summary>
        public int Birthday { get; set; }

        #endregion

        #region 注册和登录信息

        /// <summary>
        /// 注册时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "reg_time")]
        public int RegisterTime { get; private set; }

        /// <summary>
        /// 注册IP
        /// </summary>
        [SugarColumn(ColumnName = "reg_ip", Length = 16, IsNullable = true)]
        public string? RegisterIp { get; private set; }

        /// <summary>
        /// 最后登录时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "login_time", IsNullable = true)]
        public int? LoginTime { get; private set; }

        /// <summary>
        /// 最后登录IP
        /// </summary>
        [SugarColumn(ColumnName = "login_ip", Length = 16, IsNullable = true)]
        public string? LoginIp { get; private set; }

        #endregion

        #region 头像和图片

        /// <summary>
        /// 微信头像URL
        /// </summary>
        [SugarColumn(ColumnName = "headimgurl", IsNullable = true, ColumnDataType = "text")]
        public string? HeadImageUrl { get; set; }

        /// <summary>
        /// 白名单IP列表
        /// </summary>
        [SugarColumn(ColumnName = "white_ip", IsNullable = true, ColumnDataType = "text")]
        public string? WhiteIpList { get; set; }

        /// <summary>
        /// 头像URL
        /// </summary>
        [SugarColumn(ColumnName = "avatarurl", IsNullable = true, ColumnDataType = "text")]
        public string? AvatarUrl { get; set; }

        #endregion

        #region 团队信息

        /// <summary>
        /// 是否已统计(0:否 1:是)
        /// </summary>
        [SugarColumn(ColumnName = "is_count")]
        public int IsCounted { get; set; }

        /// <summary>
        /// 一级代理ID
        /// </summary>
        [SugarColumn(ColumnName = "pidg1")]
        public int ParentIdLevel1 { get; set; }

        /// <summary>
        /// 二级代理ID
        /// </summary>
        [SugarColumn(ColumnName = "pidg2")]
        public int ParentIdLevel2 { get; set; }

        /// <summary>
        /// 团队人数
        /// </summary>
        [SugarColumn(ColumnName = "teamcount")]
        public int TeamCount { get; private set; }

        /// <summary>
        /// 上级ID链(逗号分隔)
        /// </summary>
        [SugarColumn(Length = 500, IsNullable = true)]
        public string? Pids { get; set; }

        #endregion

        #region 其他信息

        /// <summary>
        /// 银行卡ID
        /// </summary>
        [SugarColumn(ColumnName = "cbank")]
        public int BankId { get; set; }

        /// <summary>
        /// 邀请码状态
        /// </summary>
        [SugarColumn(ColumnName = "icode_status")]
        public int InviteCodeStatus { get; set; }

        /// <summary>
        /// VIP等级
        /// </summary>
        public int Vip { get; set; }

        /// <summary>
        /// 创建时间(PostgreSQL Timestamp)
        /// </summary>
        [SugarColumn(ColumnName = "created_at", IsNullable = true)]
        public DateTime? CreatedAt { get; set; }

        /// <summary>
        /// 更新时间(PostgreSQL Timestamp)
        /// </summary>
        [SugarColumn(ColumnName = "updated_at", IsNullable = true)]
        public DateTime? UpdatedAt { get; set; }

        #endregion

        #region 构造函数

        /// <summary>
        /// EF/SqlSugar需要无参构造函数
        /// </summary>
        private SysUser() { }

        /// <summary>
        /// 创建新用户(工厂方法)
        /// </summary>
        public SysUser(string account, string password, string? nickname = null, int parentId = 0)
        {
            if (string.IsNullOrWhiteSpace(account))
                throw new ArgumentException("账号不能为空", nameof(account));

            if (string.IsNullOrWhiteSpace(password))
                throw new ArgumentException("密码不能为空", nameof(password));

            Account = account;
            Password = password;
            Nickname = nickname ?? account;
            ParentId = parentId;
            ParentIdLevel1 = parentId;
            ParentIdLevel2 = 0;
            
            // 默认值
            GroupId = 2;
            Balance = 0;
            FrozenBalance = 0;
            TotalInvest = 0;
            TotalInvest2 = 0;
            Status = UserStatus.Normal;
            TeamCount = 0;
            DownLevel = 0;
            Lottery = 0;
            IsEffective = 0;
            IsAi = 0;
            IsSecondaryPasswordSet = 0;
            StopCommission = 0;
            Authentication = 0;
            IsRsa = 0;
            IsGoogleAuthEnabled = 0;
            GoogleHide = 0;
            Sex = 0;
            Birthday = 0;
            FirstPayDay = 0;
            IsCounted = 0;
            BankId = 0;
            InviteCodeStatus = 0;
            Vip = 0;
            ForbidTimeFlag = "0";
            ForbidTime = 0;
            
            RegisterTime = (int)DateTimeOffset.UtcNow.ToUnixTimeSeconds();
            CreatedAt = DateTime.UtcNow;
            UpdatedAt = DateTime.UtcNow;
        }

        #endregion

        #region 业务方法

        /// <summary>
        /// 修改登录密码
        /// </summary>
        public void ChangePassword(string newPassword)
        {
            if (string.IsNullOrWhiteSpace(newPassword))
                throw new ArgumentException("新密码不能为空", nameof(newPassword));

            Password = newPassword;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 设置二级密码
        /// </summary>
        public void SetSecondaryPassword(string secondaryPassword)
        {
            if (string.IsNullOrWhiteSpace(secondaryPassword))
                throw new ArgumentException("二级密码不能为空", nameof(secondaryPassword));

            SecondaryPassword = secondaryPassword;
            IsSecondaryPasswordSet = 1;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 更新用户信息
        /// </summary>
        public void UpdateProfile(string? nickname = null, string? realname = null, 
            string? phone = null, string? email = null)
        {
            if (!string.IsNullOrWhiteSpace(nickname))
                Nickname = nickname;

            if (!string.IsNullOrWhiteSpace(realname))
                Realname = realname;

            if (!string.IsNullOrWhiteSpace(phone))
                Phone = phone;

            if (!string.IsNullOrWhiteSpace(email))
                Email = email;

            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 充值(增加余额)
        /// </summary>
        public void Recharge(decimal amount)
        {
            if (amount <= 0)
                throw new ArgumentException("充值金额必须大于0", nameof(amount));

            Balance += amount;
            TotalInvest += amount;
            TotalInvest2 += amount;
            
            // 首次充值
            if (FirstPayDay == 0)
            {
                FirstPayDay = int.Parse(DateTime.Now.ToString("yyyyMMdd"));
                IsEffective = 1;
            }
            
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 扣款(减少余额)
        /// </summary>
        public void Deduct(decimal amount)
        {
            if (amount <= 0)
                throw new ArgumentException("扣款金额必须大于0", nameof(amount));

            if (Balance < amount)
                throw new InvalidOperationException("余额不足");

            Balance -= amount;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 冻结余额
        /// </summary>
        public void FreezeBalance(decimal amount)
        {
            if (amount <= 0)
                throw new ArgumentException("冻结金额必须大于0", nameof(amount));

            if (Balance < amount)
                throw new InvalidOperationException("可用余额不足");

            Balance -= amount;
            FrozenBalance += amount;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 解冻余额
        /// </summary>
        public void UnfreezeBalance(decimal amount)
        {
            if (amount <= 0)
                throw new ArgumentException("解冻金额必须大于0", nameof(amount));

            if (FrozenBalance < amount)
                throw new InvalidOperationException("冻结余额不足");

            FrozenBalance -= amount;
            Balance += amount;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 禁用用户
        /// </summary>
        public void Disable(string reason = "")
        {
            Status = UserStatus.Disabled;
            ForbidTime = DateTimeOffset.UtcNow.ToUnixTimeSeconds();
            ForbidMessage = reason;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 启用用户
        /// </summary>
        public void Enable()
        {
            Status = UserStatus.Normal;
            ForbidTime = 0;
            ForbidMessage = null;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 记录登录
        /// </summary>
        public void RecordLogin(string loginIp)
        {
            LoginIp = loginIp;
            LoginTime = (int)DateTimeOffset.UtcNow.ToUnixTimeSeconds();
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 设置注册IP
        /// </summary>
        public void SetRegisterIp(string registerIp)
        {
            RegisterIp = registerIp;
        }

        /// <summary>
        /// 增加团队人数
        /// </summary>
        public void IncrementTeamCount()
        {
            TeamCount++;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 设置邀请码
        /// </summary>
        public void SetInviteCode(string inviteCode)
        {
            if (string.IsNullOrWhiteSpace(inviteCode))
                throw new ArgumentException("邀请码不能为空", nameof(inviteCode));

            InviteCode = inviteCode;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 启用谷歌验证器
        /// </summary>
        public void EnableGoogleAuth(string secret)
        {
            if (string.IsNullOrWhiteSpace(secret))
                throw new ArgumentException("谷歌验证器密钥不能为空", nameof(secret));

            GoogleSecret = secret;
            IsGoogleAuthEnabled = 1;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 实名认证通过
        /// </summary>
        public void SetAuthenticated()
        {
            Authentication = 1;
            UpdatedAt = DateTime.UtcNow;
        }

        /// <summary>
        /// 删除用户(软删除)
        /// </summary>
        public override void MarkAsDeleted()
        {
            Status = UserStatus.Deleted;
            UpdatedAt = DateTime.UtcNow;
        }

        #endregion
    }
}
