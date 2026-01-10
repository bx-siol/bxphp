using SqlSugar;

namespace  Domain.Entities.Products
{
    /// <summary>
    /// 产品商品实体
    /// 对应数据库: bx_core.pro_goods
    /// </summary>
    [SugarTable("pro_goods")]
    public class ProductGoods
    {
        /// <summary>
        /// 主键ID(自增)
        /// </summary>
        [SugarColumn(IsPrimaryKey = true, IsIdentity = true)]
        public int Id { get; set; }

        /// <summary>
        /// 商品序列号(16位,唯一)
        /// </summary>
        [SugarColumn(Length = 16, IsNullable = true)]
        public string? Gsn { get; set; }

        /// <summary>
        /// 分类ID
        /// </summary>
        [SugarColumn(ColumnName = "cid")]
        public int CategoryId { get; set; }

        /// <summary>
        /// 商品名称
        /// </summary>
        [SugarColumn(Length = 255, IsNullable = true)]
        public string? Name { get; set; }

        /// <summary>
        /// 是否热门(0:否 1:是)
        /// </summary>
        [SugarColumn(ColumnName = "is_hot")]
        public int IsHot { get; set; }

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
        /// 汇率
        /// </summary>
        [SugarColumn(Length = 16, IsNullable = true)]
        public string? Rate { get; set; }

        /// <summary>
        /// 利率/收益率
        /// </summary>
        [SugarColumn(DecimalDigits = 2)]
        public decimal Scale { get; set; }

        /// <summary>
        /// 已投资金额
        /// </summary>
        [SugarColumn(DecimalDigits = 2)]
        public decimal Invested { get; set; }

        /// <summary>
        /// 虚拟已投资金额
        /// </summary>
        [SugarColumn(ColumnName = "v_invested", DecimalDigits = 2)]
        public decimal VirtualInvested { get; set; }

        /// <summary>
        /// 最小投资金额
        /// </summary>
        [SugarColumn(ColumnName = "invest_min", DecimalDigits = 2)]
        public decimal InvestMin { get; set; }

        /// <summary>
        /// 投资限额
        /// </summary>
        [SugarColumn(ColumnName = "invest_limit")]
        public int InvestLimit { get; set; }

        /// <summary>
        /// 奖金类型(1:固定收益 2:浮动收益)
        /// </summary>
        [SugarColumn(ColumnName = "bonus_type")]
        public int BonusType { get; set; }

        /// <summary>
        /// 状态(0:下架 1:上架)
        /// </summary>
        public int Status { get; set; }

        /// <summary>
        /// 担保人列表
        /// </summary>
        [SugarColumn(Length = 255, IsNullable = true)]
        public string? Guarantors { get; set; }

        /// <summary>
        /// 彩票起始金额
        /// </summary>
        [SugarColumn(ColumnName = "lt_from_money", DecimalDigits = 2)]
        public decimal LotteryFromMoney { get; set; }
    }
}
