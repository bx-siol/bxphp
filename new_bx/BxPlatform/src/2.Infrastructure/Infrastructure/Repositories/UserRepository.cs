using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Domain.Entities.Users;
using Domain.Enums;
using Domain.Interfaces.Repositories;
using Infrastructure.Repositories.Base;
using Infrastructure.Sharding;
using SqlSugar;
using Snowflake.Net;

namespace Infrastructure.Repositories
{
    /// <summary>
    /// 用户仓储实现
    /// 使用 SqlSugar 原生分表功能
    /// </summary>
    public class UserRepository : Repository<SysUser>, IUserRepository
    {
        /// <summary>
        /// 构造函数
        /// </summary>
        public UserRepository(ISqlSugarClient db) : base(db)
        {
        }

        /// <summary>
        /// 根据用户ID获取用户(SqlSugar自动路由到对应分表)
        /// </summary>
        public override async Task<SysUser?> GetByIdAsync(int id)
        {
            // SqlSugar 会自动根据配置路由到正确的分表
            var tableName = SqlSugarShardingConfig.GetUserTableName(id);
            return await _db.Queryable<SysUser>()
                .AS(tableName)
                .Where(u => u.Id == id)
                .WithCache(60) // 缓存60秒
                .FirstAsync();
        }

        /// <summary>
        /// 根据账号获取用户
        /// 使用 SqlSugar 的分表联合查询
        /// </summary>
        public async Task<SysUser?> GetByAccountAsync(string account)
        {
            // SqlSugar 分表联合查询 - 自动查询所有分表
            var tables = SqlSugarShardingConfig.GetAllUserTableNames();

            // 使用 UNION ALL 查询所有分表
            var query = _db.UnionAll(tables.Select(table =>
                _db.Queryable<SysUser>()
                    .AS(table)
                    .Where(u => u.Account == account)
            ).ToList());

            return await query.WithCache(60).FirstAsync();
        }

        /// <summary>
        /// 根据手机号获取用户
        /// </summary>
        public async Task<SysUser?> GetByPhoneAsync(string phone)
        {
            var tables = SqlSugarShardingConfig.GetAllUserTableNames();

            var query = _db.UnionAll(tables.Select(table =>
                _db.Queryable<SysUser>()
                    .AS(table)
                    .Where(u => u.Phone == phone)
            ).ToList());

            return await query.WithCache(60).FirstAsync();
        }

        /// <summary>
        /// 根据邀请码获取用户
        /// </summary>
        public async Task<SysUser?> GetByInviteCodeAsync(string inviteCode)
        {
            var tables = SqlSugarShardingConfig.GetAllUserTableNames();

            var query = _db.UnionAll(tables.Select(table =>
                _db.Queryable<SysUser>()
                    .AS(table)
                    .Where(u => u.InviteCode == inviteCode)
            ).ToList());

            return await query.WithCache(60).FirstAsync();
        }

        /// <summary>
        /// 检查账号是否存在
        /// </summary>
        public async Task<bool> IsAccountExistsAsync(string account)
        {
            var user = await GetByAccountAsync(account);
            return user != null;
        }

        /// <summary>
        /// 获取用户的下级列表
        /// </summary>
        public async Task<List<SysUser>> GetSubordinatesAsync(int parentId, int level = 1)
        {
            var tables = SqlSugarShardingConfig.GetAllUserTableNames();

            // 根据层级查询
            System.Linq.Expressions.Expression<Func<SysUser, bool>> condition;
            if (level == 1)
            {
                condition = u => u.ParentIdLevel1 == parentId;
            }
            else
            {
                condition = u => u.ParentIdLevel2 == parentId;
            }

            var query = _db.UnionAll(tables.Select(table =>
                _db.Queryable<SysUser>()
                    .AS(table)
                    .Where(condition)
                    .Where(u => u.Status != UserStatus.Deleted)
            ).ToList());

            return await query.WithCache(30).ToListAsync();
        }

        /// <summary>
        /// 获取用户团队人数
        /// </summary>
        public async Task<int> GetTeamCountAsync(int userId)
        {
            var user = await GetByIdAsync(userId);
            return user?.TeamCount ?? 0;
        }

        /// <summary>
        /// 批量更新用户状态
        /// </summary>
        public async Task<int> BatchUpdateStatusAsync(List<int> userIds, int status)
        {
            if (userIds == null || userIds.Count == 0)
                return 0;

            int totalUpdated = 0;

            // 按分表分组更新
            var groupedByTable = userIds.GroupBy(id => SqlSugarShardingConfig.GetUserTableName(id));

            foreach (var group in groupedByTable)
            {
                var tableName = group.Key;
                var ids = group.ToList();

                var updated = await _db.Updateable<SysUser>()
                    .AS(tableName)
                    .SetColumns(u => u.Status == (UserStatus)status)
                    .SetColumns(u => u.UpdateTime == (int)System.DateTimeOffset.UtcNow.ToUnixTimeSeconds())
                    .Where(u => ids.Contains(u.Id))
                    .RemoveDataCache() // 移除缓存
                    .ExecuteCommandAsync();

                totalUpdated += updated;
            }

            return totalUpdated;
        }

        /// <summary>
        /// 更新用户(SqlSugar自动路由到对应分表)
        /// </summary>
        public override async Task<bool> UpdateAsync(SysUser entity)
        {
            var tableName = SqlSugarShardingConfig.GetUserTableName(entity.Id);
            var result = await _db.Updateable(entity)
                .AS(tableName)
                .RemoveDataCache() // 移除SqlSugar二级缓存
                .ExecuteCommandAsync() > 0;

            return result;
        }

        /// <summary>
        /// 添加用户(SqlSugar自动路由到对应分表)
        /// 优化版本: 直接插入到目标表
        /// </summary>
        public override async Task<SysUser> AddAsync(SysUser entity)
        {
            // 使用SqlSugar的雪花ID或自增ID
            // 如果使用雪花ID,可以预先生成ID并直接插入到目标表

            // 方案1: 使用雪花ID(推荐)
            if (entity.Id == 0)
            {
                //entity.GetType().GetProperty("Id")?.SetValue(entity,
                //    new SnowFlakeNet.IdWorker(1, 1).NextId());
            }

            var tableName = SqlSugarShardingConfig.GetUserTableName(entity.Id);
            await _db.Insertable(entity)
                .AS(tableName)
                .ExecuteCommandAsync();

            return entity;

            /* 方案2: 使用自增ID(需要两步)
            var id = await _db.Insertable(entity)
                .AS("sys_user_0")
                .ExecuteReturnIdentityAsync();
            
            entity.GetType().GetProperty("Id")?.SetValue(entity, (int)id);
            var targetTable = SqlSugarShardingConfig.GetUserTableName(entity.Id);
            
            if (targetTable != "sys_user_0")
            {
                await _db.Insertable(entity).AS(targetTable).ExecuteCommandAsync();
                await _db.Deleteable<SysUser>().AS("sys_user_0")
                    .Where(u => u.Id == entity.Id).ExecuteCommandAsync();
            }
            return entity;
            */
        }
    }
}
