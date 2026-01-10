using SqlSugar;
using  Domain.Entities.Base;

namespace  Domain.Entities.Trade
{
    /// <summary>
    /// 产品订单实体(聚合根)
    /// 对应数据库: bx_trade.pro_order
    /// 分表策略: 由应用层框架实现(推荐按月或按uid)
    /// </summary>
    [SugarTable("pro_order")]
    public class ProductOrder : Entity, IAggregateRoot
    {
        /// <summary>
        /// 主键ID(自增)
        /// </summary>
        [SugarColumn(IsPrimaryKey = true, IsIdentity = true)]
        public new int Id { get; set; }

        /// <summary>
        /// 订单号(16位唯一字符)
        /// </summary>
        [SugarColumn(Length = 16, IsNullable = false)]
        public string Osn { get; set; } = string.Empty;

        /// <summary>
        /// 分类ID
        /// </summary>
        [SugarColumn(ColumnName = "cid")]
        public int CategoryId { get; set; }

        /// <summary>
        /// 产品ID
        /// </summary>
        [SugarColumn(ColumnName = "gid", IsNullable = true)]
        public int? GoodsId { get; set; }

        /// <summary>
        /// 用户ID
        /// </summary>
        [SugarColumn(IsNullable = false)]
        public int Uid { get; set; }

        /// <summary>
        /// 周期天数
        /// </summary>
        public int Days { get; set; }

        /// <summary>
        /// 价格
        /// </summary>
        [SugarColumn(DecimalDigits = 2)]
        public decimal Price { get; set; }

        /// <summary>
        /// 价格1
        /// </summary>
        [SugarColumn(ColumnName = "price1", DecimalDigits = 2, IsNullable = true)]
        public decimal? Price1 { get; set; }

        /// <summary>
        /// 价格2
        /// </summary>
        [SugarColumn(ColumnName = "price2", DecimalDigits = 2, IsNullable = true)]
        public decimal? Price2 { get; set; }

        /// <summary>
        /// 价格0
        /// </summary>
        [SugarColumn(ColumnName = "price0", DecimalDigits = 2, IsNullable = true)]
        public decimal? Price0 { get; set; }

        /// <summary>
        /// 参数1
        /// </summary>
        [SugarColumn(ColumnName = "p1")]
        public int P1 { get; set; }

        /// <summary>
        /// 参数2
        /// </summary>
        [SugarColumn(ColumnName = "p2")]
        public int P2 { get; set; }

        /// <summary>
        /// 参数3
        /// </summary>
        [SugarColumn(ColumnName = "p3")]
        public int P3 { get; set; }

        /// <summary>
        /// 数量
        /// </summary>
        public int Num { get; set; }

        /// <summary>
        /// 总金额
        /// </summary>
        [SugarColumn(DecimalDigits = 2)]
        public decimal Money { get; set; }

        /// <summary>
        /// 汇率
        /// </summary>
        [SugarColumn(Length = 16, IsNullable = true)]
        public string? Rate { get; set; }

        /// <summary>
        /// 折扣
        /// </summary>
        [SugarColumn(DecimalDigits = 2)]
        public decimal Discount { get; set; } = 1.00m;

        /// <summary>
        /// 钱包1金额
        /// </summary>
        [SugarColumn(ColumnName = "w1_money", DecimalDigits = 2)]
        public decimal Wallet1Money { get; set; }

        /// <summary>
        /// 钱包2金额
        /// </summary>
        [SugarColumn(ColumnName = "w2_money", DecimalDigits = 2)]
        public decimal Wallet2Money { get; set; }

        /// <summary>
        /// 是否赠送(0:正常订单 1:赠送订单)
        /// </summary>
        [SugarColumn(ColumnName = "is_give")]
        public int IsGive { get; set; }

        /// <summary>
        /// 订单状态(1:进行中 2:已暂停 3:已完成 9:已结束)
        /// </summary>
        public int Status { get; set; }

        /// <summary>
        /// 创建日期(Ymd格式,如:20260111)
        /// </summary>
        [SugarColumn(ColumnName = "create_day")]
        public int CreateDay { get; set; }

        /// <summary>
        /// 创建时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "create_time", IsNullable = false)]
        public int CreateTime { get; set; }

        /// <summary>
        /// 创建人ID
        /// </summary>
        [SugarColumn(ColumnName = "create_id")]
        public int CreateId { get; set; }

        /// <summary>
        /// 创建IP
        /// </summary>
        [SugarColumn(ColumnName = "create_ip", Length = 16, IsNullable = true)]
        public string? CreateIp { get; set; }

        /// <summary>
        /// 奖励日期(Ymd格式)
        /// </summary>
        [SugarColumn(ColumnName = "reward_day")]
        public int RewardDay { get; set; }

        /// <summary>
        /// 奖励时间(Unix时间戳)
        /// </summary>
        [SugarColumn(ColumnName = "reward_time")]
        public int RewardTime { get; set; }

        /// <summary>
        /// 总奖励金额
        /// </summary>
        [SugarColumn(ColumnName = "total_reward", DecimalDigits = 2)]
        public decimal TotalReward { get; set; }

        /// <summary>
        /// 已奖励天数
        /// </summary>
        [SugarColumn(ColumnName = "total_days")]
        public int TotalDays { get; set; }

        /// <summary>
        /// 父订单ID
        /// </summary>
        [SugarColumn(ColumnName = "pid", IsNullable = true)]
        public int? ParentId { get; set; }

        /// <summary>
        /// 是否兑换订单
        /// </summary>
        [SugarColumn(ColumnName = "is_exchange", IsNullable = true)]
        public int? IsExchange { get; set; }

        /// <summary>
        /// 签名
        /// </summary>
        [SugarColumn(IsNullable = true, ColumnDataType = "text")]
        public string? Sign { get; set; }
    }
}
