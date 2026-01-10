using SqlSugar;
using BxPlatform.Domain.Entities.Base;

namespace BxPlatform.Domain.Entities.Finance
{
    /// <summary>
    /// 提现记录实体(聚合根)
    /// 对应数据库: bx_finance.fin_cashlog
    /// 分表策略: 由应用层框架实现(推荐按月或按uid)
    /// </summary>
    [SugarTable("fin_cashlog")]
    public class CashLog : Entity, IAggregateRoot
    {
        /// <summary>
        /// 主键ID(自增)
        /// </summary>
        [SugarColumn(IsPrimaryKey = true, IsIdentity = true)]
        public new int Id { get; set; }

        /// <summary>
        /// 订单号(唯一)
        /// </summary>
        [SugarColumn(Length = 32, IsNullable = false)]
        public string Osn { get; set; } = string.Empty;

        /// <summary>
        /// 外部订单号
        /// </summary>
        [SugarColumn(ColumnName = "out_osn", Length = 64, IsNullable = true)]
        public string? OutOsn { get; set; }

        /// <summary>
        /// 用户ID
        /// </summary>
        [SugarColumn(IsNullable = false)]
        public int Uid { get; set; }

        /// <summary>
        /// 提现金额
        /// </summary>
        [SugarColumn(DecimalDigits = 2, IsNullable = true)]
        public decimal? Money { get; set; }

        /// <summary>
        /// 实际到账金额
        /// </summary>
        [SugarColumn(ColumnName = "real_money", DecimalDigits = 2)]
        public decimal RealMoney { get; set; }

        /// <summary>
        /// 手续费
        /// </summary>
        [SugarColumn(DecimalDigits = 2)]
        public decimal Fee { get; set; }

        /// <summary>
        /// 手续费模式(0:固定金额 1:百分比)
        /// </summary>
        [SugarColumn(ColumnName = "fee_mode")]
        public int FeeMode { get; set; }

        /// <summary>
        /// 原始余额
        /// </summary>
        [SugarColumn(ColumnName = "ori_balance", DecimalDigits = 2, IsNullable = true)]
        public decimal? OriginalBalance { get; set; }

        /// <summary>
        /// 新余额
        /// </summary>
        [SugarColumn(ColumnName = "new_balance", DecimalDigits = 2, IsNullable = true)]
        public decimal? NewBalance { get; set; }

        /// <summary>
        /// 支付类型
        /// </summary>
        [SugarColumn(ColumnName = "pay_type", Length = 255, IsNullable = true)]
        public string? PayType { get; set; }

        /// <summary>
        /// 创建时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "create_time", IsNullable = true)]
        public int? CreateTime { get; set; }

        /// <summary>
        /// 创建日期(Ymd格式,如:20260111)
        /// </summary>
        [SugarColumn(ColumnName = "create_day", IsNullable = false)]
        public int CreateDay { get; set; }

        /// <summary>
        /// 支付时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "pay_time")]
        public int PayTime { get; set; }

        /// <summary>
        /// 状态(1:待审核 3:不通过 7:取消 9:已通过)
        /// </summary>
        public int Status { get; set; }

        /// <summary>
        /// 收款手机号
        /// </summary>
        [SugarColumn(ColumnName = "receive_phone", Length = 64, IsNullable = true)]
        public string? ReceivePhone { get; set; }

        /// <summary>
        /// 收款邮箱
        /// </summary>
        [SugarColumn(ColumnName = "receive_email", Length = 128, IsNullable = true)]
        public string? ReceiveEmail { get; set; }

        /// <summary>
        /// 收款IFSC码(印度银行码)
        /// </summary>
        [SugarColumn(ColumnName = "receive_ifsc", Length = 64, IsNullable = true)]
        public string? ReceiveIfsc { get; set; }

        /// <summary>
        /// 收款类型(0:银行卡 1:UPI 2:钱包地址)
        /// </summary>
        [SugarColumn(ColumnName = "receive_type")]
        public int ReceiveType { get; set; }

        /// <summary>
        /// 收款银行ID
        /// </summary>
        [SugarColumn(ColumnName = "receive_bank_id", Length = 100)]
        public string ReceiveBankId { get; set; } = "0";

        /// <summary>
        /// 收款银行名称
        /// </summary>
        [SugarColumn(ColumnName = "receive_bank_name", Length = 128, IsNullable = true)]
        public string? ReceiveBankName { get; set; }

        /// <summary>
        /// 收款账号
        /// </summary>
        [SugarColumn(ColumnName = "receive_account", Length = 64, IsNullable = true)]
        public string? ReceiveAccount { get; set; }

        /// <summary>
        /// 收款人姓名
        /// </summary>
        [SugarColumn(ColumnName = "receive_realname", Length = 64, IsNullable = true)]
        public string? ReceiveRealname { get; set; }

        /// <summary>
        /// 收款路由号
        /// </summary>
        [SugarColumn(ColumnName = "receive_routing", Length = 64, IsNullable = true)]
        public string? ReceiveRouting { get; set; }

        /// <summary>
        /// 收款地址
        /// </summary>
        [SugarColumn(ColumnName = "receive_address", Length = 128, IsNullable = true)]
        public string? ReceiveAddress { get; set; }

        /// <summary>
        /// 收款协议类型(0:TRC20 1:ERC20 等)
        /// </summary>
        [SugarColumn(ColumnName = "receive_protocol")]
        public int ReceiveProtocol { get; set; }

        /// <summary>
        /// 收款二维码
        /// </summary>
        [SugarColumn(ColumnName = "receive_qrcode", Length = 255, IsNullable = true)]
        public string? ReceiveQrcode { get; set; }

        /// <summary>
        /// 审核备注
        /// </summary>
        [SugarColumn(ColumnName = "check_remark", Length = 128, IsNullable = true)]
        public string? CheckRemark { get; set; }

        /// <summary>
        /// 审核人ID
        /// </summary>
        [SugarColumn(ColumnName = "check_id")]
        public int CheckId { get; set; }

        /// <summary>
        /// 审核IP
        /// </summary>
        [SugarColumn(ColumnName = "check_ip", Length = 16, IsNullable = true)]
        public string? CheckIp { get; set; }

        /// <summary>
        /// 审核时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "check_time")]
        public int CheckTime { get; set; }

        /// <summary>
        /// 客户端IP
        /// </summary>
        [SugarColumn(ColumnName = "client_ip", Length = 16, IsNullable = true)]
        public string? ClientIp { get; set; }

        /// <summary>
        /// 支付状态(0:待支付 9:已支付)
        /// </summary>
        [SugarColumn(ColumnName = "pay_status")]
        public int PayStatus { get; set; }

        /// <summary>
        /// 支付消息
        /// </summary>
        [SugarColumn(ColumnName = "pay_msg", Length = 255, IsNullable = true)]
        public string? PayMsg { get; set; }
    }
}
