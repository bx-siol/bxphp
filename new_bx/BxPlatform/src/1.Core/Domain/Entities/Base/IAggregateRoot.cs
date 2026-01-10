namespace  Domain.Entities.Base
{
    /// <summary>
    /// 聚合根标记接口
    /// 用于标识聚合根实体,聚合根是数据一致性的边界
    /// 只有聚合根才能被仓储直接访问
    /// </summary>
    public interface IAggregateRoot
    {
        /// <summary>
        /// 聚合根ID
        /// </summary>
        int Id { get; }
    }
}
