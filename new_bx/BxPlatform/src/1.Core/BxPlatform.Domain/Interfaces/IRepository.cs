using System;
using System.Collections.Generic;
using System.Linq.Expressions;
using System.Threading.Tasks;
using BxPlatform.Domain.Entities.Base;

namespace BxPlatform.Domain.Interfaces
{
    /// <summary>
    /// 仓储接口基类
    /// 定义通用的CRUD操作
    /// </summary>
    /// <typeparam name="T">聚合根类型</typeparam>
    public interface IRepository<T> where T : class, IAggregateRoot
    {
        /// <summary>
        /// 根据ID获取实体
        /// </summary>
        Task<T?> GetByIdAsync(int id);

        /// <summary>
        /// 根据条件获取单个实体
        /// </summary>
        Task<T?> GetAsync(Expression<Func<T, bool>> predicate);

        /// <summary>
        /// 获取所有实体
        /// </summary>
        Task<List<T>> GetAllAsync();

        /// <summary>
        /// 根据条件获取实体列表
        /// </summary>
        Task<List<T>> GetListAsync(Expression<Func<T, bool>> predicate);

        /// <summary>
        /// 分页查询
        /// </summary>
        Task<(List<T> items, int total)> GetPagedListAsync(
            Expression<Func<T, bool>>? predicate,
            int pageIndex,
            int pageSize,
            Expression<Func<T, object>>? orderBy = null,
            bool ascending = true);

        /// <summary>
        /// 添加实体
        /// </summary>
        Task<T> AddAsync(T entity);

        /// <summary>
        /// 批量添加
        /// </summary>
        Task<int> AddRangeAsync(List<T> entities);

        /// <summary>
        /// 更新实体
        /// </summary>
        Task<bool> UpdateAsync(T entity);

        /// <summary>
        /// 批量更新
        /// </summary>
        Task<int> UpdateRangeAsync(List<T> entities);

        /// <summary>
        /// 删除实体
        /// </summary>
        Task<bool> DeleteAsync(T entity);

        /// <summary>
        /// 根据ID删除
        /// </summary>
        Task<bool> DeleteByIdAsync(int id);

        /// <summary>
        /// 批量删除
        /// </summary>
        Task<int> DeleteRangeAsync(Expression<Func<T, bool>> predicate);

        /// <summary>
        /// 判断是否存在
        /// </summary>
        Task<bool> ExistsAsync(Expression<Func<T, bool>> predicate);

        /// <summary>
        /// 获取数量
        /// </summary>
        Task<int> CountAsync(Expression<Func<T, bool>>? predicate = null);
    }
}
