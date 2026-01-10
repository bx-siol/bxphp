using System;
using System.Threading.Tasks;
using BxPlatform.Domain.Interfaces;
using SqlSugar;

namespace BxPlatform.Infrastructure.Repositories.Base
{
    /// <summary>
    /// 工作单元实现
    /// 基于SqlSugar的事务管理
    /// </summary>
    public class UnitOfWork : IUnitOfWork
    {
        private readonly ISqlSugarClient _db;
        private bool _disposed;

        /// <summary>
        /// 构造函数
        /// </summary>
        public UnitOfWork(ISqlSugarClient db)
        {
            _db = db ?? throw new ArgumentNullException(nameof(db));
        }

        /// <summary>
        /// 开始事务
        /// </summary>
        public async Task BeginTransactionAsync()
        {
            await Task.Run(() => _db.Ado.BeginTran());
        }

        /// <summary>
        /// 提交事务
        /// </summary>
        public async Task<bool> CommitAsync()
        {
            try
            {
                await Task.Run(() => _db.Ado.CommitTran());
                return true;
            }
            catch
            {
                await RollbackAsync();
                throw;
            }
        }

        /// <summary>
        /// 回滚事务
        /// </summary>
        public async Task RollbackAsync()
        {
            await Task.Run(() => _db.Ado.RollbackTran());
        }

        /// <summary>
        /// 保存更改(不提交事务)
        /// </summary>
        public async Task<int> SaveChangesAsync()
        {
            // SqlSugar 不需要显式调用 SaveChanges
            return await Task.FromResult(0);
        }

        /// <summary>
        /// 释放资源
        /// </summary>
        public void Dispose()
        {
            Dispose(true);
            GC.SuppressFinalize(this);
        }

        /// <summary>
        /// 释放资源
        /// </summary>
        protected virtual void Dispose(bool disposing)
        {
            if (!_disposed)
            {
                if (disposing)
                {
                    // 释放托管资源
                    _db?.Dispose();
                }
                _disposed = true;
            }
        }
    }
}
