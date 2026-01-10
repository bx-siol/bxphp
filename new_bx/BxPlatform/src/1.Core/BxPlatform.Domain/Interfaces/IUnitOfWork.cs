using System;
using System.Threading.Tasks;

namespace BxPlatform.Domain.Interfaces
{
    /// <summary>
    /// 工作单元接口
    /// 用于管理数据库事务,保证数据一致性
    /// </summary>
    public interface IUnitOfWork : IDisposable
    {
        /// <summary>
        /// 开始事务
        /// </summary>
        Task BeginTransactionAsync();

        /// <summary>
        /// 提交事务
        /// </summary>
        Task<bool> CommitAsync();

        /// <summary>
        /// 回滚事务
        /// </summary>
        Task RollbackAsync();

        /// <summary>
        /// 保存更改(不提交事务)
        /// </summary>
        Task<int> SaveChangesAsync();
    }
}
