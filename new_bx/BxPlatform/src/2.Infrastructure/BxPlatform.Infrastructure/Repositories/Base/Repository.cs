using System;
using System.Collections.Generic;
using System.Linq.Expressions;
using System.Threading.Tasks;
using BxPlatform.Domain.Entities.Base;
using BxPlatform.Domain.Interfaces;
using SqlSugar;

namespace BxPlatform.Infrastructure.Repositories.Base
{
    /// <summary>
    /// 仓储基类实现
    /// 提供通用的CRUD操作
    /// </summary>
    public class Repository<T> : IRepository<T> where T : class, IAggregateRoot, new()
    {
        protected readonly ISqlSugarClient _db;
        protected readonly string? _tableName;

        /// <summary>
        /// 构造函数
        /// </summary>
        public Repository(ISqlSugarClient db, string? tableName = null)
        {
            _db = db ?? throw new ArgumentNullException(nameof(db));
            _tableName = tableName;
        }

        /// <summary>
        /// 获取查询对象
        /// </summary>
        protected ISugarQueryable<T> GetQueryable()
        {
            var queryable = _db.Queryable<T>();
            if (!string.IsNullOrWhiteSpace(_tableName))
            {
                queryable = queryable.AS(_tableName);
            }
            return queryable;
        }

        /// <summary>
        /// 根据ID获取实体
        /// </summary>
        public virtual async Task<T?> GetByIdAsync(int id)
        {
            return await GetQueryable()
                .Where(x => x.Id == id)
                .FirstAsync();
        }

        /// <summary>
        /// 根据条件获取单个实体
        /// </summary>
        public virtual async Task<T?> GetAsync(Expression<Func<T, bool>> predicate)
        {
            return await GetQueryable()
                .Where(predicate)
                .FirstAsync();
        }

        /// <summary>
        /// 获取所有实体
        /// </summary>
        public virtual async Task<List<T>> GetAllAsync()
        {
            return await GetQueryable().ToListAsync();
        }

        /// <summary>
        /// 根据条件获取实体列表
        /// </summary>
        public virtual async Task<List<T>> GetListAsync(Expression<Func<T, bool>> predicate)
        {
            return await GetQueryable()
                .Where(predicate)
                .ToListAsync();
        }

        /// <summary>
        /// 分页查询
        /// </summary>
        public virtual async Task<(List<T> items, int total)> GetPagedListAsync(
            Expression<Func<T, bool>>? predicate,
            int pageIndex,
            int pageSize,
            Expression<Func<T, object>>? orderBy = null,
            bool ascending = true)
        {
            var query = GetQueryable();

            if (predicate != null)
            {
                query = query.Where(predicate);
            }

            // 排序
            if (orderBy != null)
            {
                query = ascending
                    ? query.OrderBy(orderBy)
                    : query.OrderByDescending(orderBy);
            }

            int total = 0;
            var items = await query.ToPageListAsync(pageIndex, pageSize, total);

            return (items, total);
        }

        /// <summary>
        /// 添加实体
        /// </summary>
        public virtual async Task<T> AddAsync(T entity)
        {
            var insertable = _db.Insertable(entity);
            if (!string.IsNullOrWhiteSpace(_tableName))
            {
                insertable = insertable.AS(_tableName);
            }

            var result = await insertable.ExecuteReturnEntityAsync();
            return result;
        }

        /// <summary>
        /// 批量添加
        /// </summary>
        public virtual async Task<int> AddRangeAsync(List<T> entities)
        {
            if (entities == null || entities.Count == 0)
                return 0;

            var insertable = _db.Insertable(entities);
            if (!string.IsNullOrWhiteSpace(_tableName))
            {
                insertable = insertable.AS(_tableName);
            }

            return await insertable.ExecuteCommandAsync();
        }

        /// <summary>
        /// 更新实体
        /// </summary>
        public virtual async Task<bool> UpdateAsync(T entity)
        {
            var updateable = _db.Updateable(entity);
            if (!string.IsNullOrWhiteSpace(_tableName))
            {
                updateable = updateable.AS(_tableName);
            }

            return await updateable.ExecuteCommandAsync() > 0;
        }

        /// <summary>
        /// 批量更新
        /// </summary>
        public virtual async Task<int> UpdateRangeAsync(List<T> entities)
        {
            if (entities == null || entities.Count == 0)
                return 0;

            var updateable = _db.Updateable(entities);
            if (!string.IsNullOrWhiteSpace(_tableName))
            {
                updateable = updateable.AS(_tableName);
            }

            return await updateable.ExecuteCommandAsync();
        }

        /// <summary>
        /// 删除实体
        /// </summary>
        public virtual async Task<bool> DeleteAsync(T entity)
        {
            var deleteable = _db.Deleteable(entity);
            if (!string.IsNullOrWhiteSpace(_tableName))
            {
                deleteable = deleteable.AS(_tableName);
            }

            return await deleteable.ExecuteCommandAsync() > 0;
        }

        /// <summary>
        /// 根据ID删除
        /// </summary>
        public virtual async Task<bool> DeleteByIdAsync(int id)
        {
            var deleteable = _db.Deleteable<T>().Where(x => x.Id == id);
            if (!string.IsNullOrWhiteSpace(_tableName))
            {
                deleteable = deleteable.AS(_tableName);
            }

            return await deleteable.ExecuteCommandAsync() > 0;
        }

        /// <summary>
        /// 批量删除
        /// </summary>
        public virtual async Task<int> DeleteRangeAsync(Expression<Func<T, bool>> predicate)
        {
            var deleteable = _db.Deleteable<T>().Where(predicate);
            if (!string.IsNullOrWhiteSpace(_tableName))
            {
                deleteable = deleteable.AS(_tableName);
            }

            return await deleteable.ExecuteCommandAsync();
        }

        /// <summary>
        /// 判断是否存在
        /// </summary>
        public virtual async Task<bool> ExistsAsync(Expression<Func<T, bool>> predicate)
        {
            return await GetQueryable()
                .Where(predicate)
                .AnyAsync();
        }

        /// <summary>
        /// 获取数量
        /// </summary>
        public virtual async Task<int> CountAsync(Expression<Func<T, bool>>? predicate = null)
        {
            var query = GetQueryable();
            if (predicate != null)
            {
                query = query.Where(predicate);
            }
            return await query.CountAsync();
        }
    }
}
