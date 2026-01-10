using System;
using SqlSugar;

namespace new_bx.Common.Entities
{
    /// <summary>
    /// 系统用户实体类
    /// 对应数据库表 sys_user
    /// 严格按照PostgreSQL bx_core.sys_user表结构定义
    /// </summary>
    [SugarTable("sys_user")]
    public class SysUserEntity
    {
        /// <summary>
        /// 用户ID（主键）
        /// </summary>
        [SugarColumn(IsPrimaryKey = true, IsIdentity = true)]
        public int id { get; set; }

        /// <summary>
        /// 父级用户ID
        /// </summary>
        public int pid { get; set; }

        /// <summary>
        /// 用户分组ID
        /// </summary>
        public int gid { get; set; }

        /// <summary>
        /// 下级等级
        /// </summary>
        public int down_level { get; set; }

        /// <summary>
        /// OpenID
        /// </summary>
        public string? openid { get; set; }

        /// <summary>
        /// UnionID
        /// </summary>
        public string? unionid { get; set; }

        /// <summary>
        /// 账号（唯一）
        /// </summary>
        public string? account { get; set; }

        /// <summary>
        /// 手机号
        /// </summary>
        public string? phone { get; set; }

        /// <summary>
        /// 邮箱
        /// </summary>
        public string? email { get; set; }

        /// <summary>
        /// 密码（SHA1加密）
        /// </summary>
        public string? password { get; set; }

        /// <summary>
        /// 二级密码（SHA1加密）
        /// </summary>
        public string? password2 { get; set; }

        /// <summary>
        /// 是否设置了二级密码
        /// </summary>
        public bool is_pwd2_set { get; set; }

        /// <summary>
        /// 首次支付日期（格式：Ymd，如：20240101）
        /// </summary>
        public int first_pay_day { get; set; }

        /// <summary>
        /// 是否停止佣金
        /// </summary>
        public bool stop_commission { get; set; }

        /// <summary>
        /// 可用余额
        /// </summary>
        public decimal balance { get; set; }

        /// <summary>
        /// 冻结余额
        /// </summary>
        public decimal fz_balance { get; set; }

        /// <summary>
        /// 总投资额
        /// </summary>
        public decimal total_invest { get; set; }

        /// <summary>
        /// 总投资额2
        /// </summary>
        public decimal total_invest2 { get; set; }

        /// <summary>
        /// 抽奖次数
        /// </summary>
        public int lottery { get; set; }

        /// <summary>
        /// 是否有效用户
        /// </summary>
        public bool is_effective { get; set; }

        /// <summary>
        /// 语言设置
        /// </summary>
        public string? language { get; set; }

        /// <summary>
        /// API密钥
        /// </summary>
        public string? apikey { get; set; }

        /// <summary>
        /// 是否启用RSA加密
        /// </summary>
        public int is_rsa { get; set; }

        /// <summary>
        /// RSA公钥
        /// </summary>
        public string? rsa_public { get; set; }

        /// <summary>
        /// RSA私钥
        /// </summary>
        public string? rsa_private { get; set; }

        /// <summary>
        /// 昵称
        /// </summary>
        public string? nickname { get; set; }

        /// <summary>
        /// 真实姓名
        /// </summary>
        public string? realname { get; set; }

        /// <summary>
        /// 实名认证状态
        /// </summary>
        public int authentication { get; set; }

        /// <summary>
        /// 用户编号USN
        /// </summary>
        public string? usn { get; set; }

        /// <summary>
        /// 邀请码（唯一）
        /// </summary>
        public string? icode { get; set; }

        /// <summary>
        /// 邀请码状态
        /// </summary>
        public int icode_status { get; set; }

        /// <summary>
        /// 状态：1=禁用，2=正常，99=已删除
        /// </summary>
        public int status { get; set; }

        /// <summary>
        /// 是否AI用户
        /// </summary>
        public int is_ai { get; set; }

        /// <summary>
        /// 禁用时间标志
        /// </summary>
        public string? forbid_time_flag { get; set; }

        /// <summary>
        /// 禁用时间
        /// </summary>
        public long forbid_time { get; set; }

        /// <summary>
        /// 禁用原因
        /// </summary>
        public string? forbid_msg { get; set; }

        /// <summary>
        /// 是否启用Google验证
        /// </summary>
        public int is_google { get; set; }

        /// <summary>
        /// Google验证密钥
        /// </summary>
        public string? google_secret { get; set; }

        /// <summary>
        /// 隐藏Google信息
        /// </summary>
        public int google_hide { get; set; }

        /// <summary>
        /// 纬度
        /// </summary>
        public string? latitude { get; set; }

        /// <summary>
        /// 经度
        /// </summary>
        public string? longitude { get; set; }

        /// <summary>
        /// 地址
        /// </summary>
        public string? address { get; set; }

        /// <summary>
        /// 性别：0=未知，1=男，2=女
        /// </summary>
        public int sex { get; set; }

        /// <summary>
        /// 国家
        /// </summary>
        public string? country { get; set; }

        /// <summary>
        /// 省份
        /// </summary>
        public string? province { get; set; }

        /// <summary>
        /// 城市
        /// </summary>
        public string? city { get; set; }

        /// <summary>
        /// 生日（时间戳）
        /// </summary>
        public int birthday { get; set; }

        /// <summary>
        /// 注册时间（时间戳）
        /// </summary>
        public int reg_time { get; set; }

        /// <summary>
        /// 注册IP
        /// </summary>
        public string? reg_ip { get; set; }

        /// <summary>
        /// 最后登录时间（时间戳）
        /// </summary>
        public int login_time { get; set; }

        /// <summary>
        /// 最后登录IP
        /// </summary>
        public string? login_ip { get; set; }

        /// <summary>
        /// 头像URL
        /// </summary>
        public string? headimgurl { get; set; }

        /// <summary>
        /// 白名单IP列表
        /// </summary>
        public string? white_ip { get; set; }

        /// <summary>
        /// 头像URL（备用字段）
        /// </summary>
        public string? avatarurl { get; set; }

        /// <summary>
        /// 是否已计数
        /// </summary>
        public bool is_count { get; set; }

        /// <summary>
        /// 一级代理ID（gid=71的上级）
        /// </summary>
        public int pidg1 { get; set; }

        /// <summary>
        /// 二级代理ID（gid=81的上级）
        /// </summary>
        public int pidg2 { get; set; }

        /// <summary>
        /// 团队总人数
        /// </summary>
        public int teamcount { get; set; }

        /// <summary>
        /// 上级ID链（逗号分隔）
        /// </summary>
        public string? pids { get; set; }

        /// <summary>
        /// 银行卡号
        /// </summary>
        public string? cbank { get; set; }
    }
}