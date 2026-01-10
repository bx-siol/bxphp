using SqlSugar;

namespace  Domain.Entities.Wallet
{
    /// <summary>
    /// 用户钱包实体
    /// 对应数据库: bx_core.wallet_list
    /// </summary>
    [SugarTable("wallet_list")]
    public class UserWallet
    {
        /// <summary>
        /// 主键ID(自增)
        /// </summary>
        [SugarColumn(IsPrimaryKey = true, IsIdentity = true)]
        public int Id { get; set; }

        /// <summary>
        /// 钱包地址(唯一)
        /// </summary>
        [SugarColumn(ColumnName = "waddr", Length = 64, IsNullable = true)]
        public string? WalletAddress { get; set; }

        /// <summary>
        /// 用户ID
        /// </summary>
        [SugarColumn(IsNullable = false)]
        public int Uid { get; set; }

        /// <summary>
        /// 币种ID(1:充值钱包 2:余额钱包 3:积分钱包)
        /// </summary>
        [SugarColumn(ColumnName = "cid")]
        public int CurrencyId { get; set; }

        /// <summary>
        /// 可用余额
        /// </summary>
        [SugarColumn(DecimalDigits = 2)]
        public decimal Balance { get; set; }

        /// <summary>
        /// 冻结余额
        /// </summary>
        [SugarColumn(ColumnName = "fz_balance", DecimalDigits = 2)]
        public decimal FrozenBalance { get; set; }

        /// <summary>
        /// 创建时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "create_time")]
        public int CreateTime { get; set; }

        /// <summary>
        /// 最后操作时间
        /// </summary>
        [SugarColumn(ColumnName = "lasttime", Length = 20)]
        public string LastTime { get; set; } = "0";
    }
}
