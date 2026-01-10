# BxPlatform - 基于 SqlSugar 的分表架构设计

## 📋 概述

本文档详细说明如何使用 **SqlSugar 原生分表功能**来实现高性能的分库分表架构。

---

## 🎯 SqlSugar 分表优势

### 传统手动分表 vs SqlSugar 分表

| 特性 | 手动分表 | SqlSugar分表 |
|-----|---------|-------------|
| 代码复杂度 | ❌ 高 | ✅ 低 |
| 路由逻辑 | ❌ 手动实现 | ✅ 自动处理 |
| 联合查询 | ❌ 复杂 | ✅ UNION ALL |
| 二级缓存 | ⬜ 需自己实现 | ✅ 内置支持 |
| 分表迁移 | ❌ 困难 | ✅ 支持 |
| 性能 | ⚠️  一般 | ✅ 优秀 |

---

## 🏗️ SqlSugar 分表方案

### 1. 分表策略

SqlSugar 支持多种分表策略:

#### 1.1 按范围分表 (推荐用户表)
```csharp
[SugarTable("sys_user")]
[SplitTable(SplitType.Size)]  // 按数量分表
[SplitField(nameof(Id), 5000000)]  // 每500万一张表
public class SysUser : Entity, IAggregateRoot
{
    [SugarColumn(IsPrimaryKey = true)]
    public int Id { get; set; }
    // ... 其他属性
}
```

**说明:**
- 当数据达到500万时,SqlSugar 自动创建新表 `sys_user_1`
- 第二个500万创建 `sys_user_2`
- 查询时自动路由到对应表

#### 1.2 按时间分表 (推荐日志表)
```csharp
[SugarTable("sys_log")]
[SplitTable(SplitType.Month)]  // 按月分表
public class SysLog
{
    [SplitField]  // 分表字段
    public DateTime CreateTime { get; set; }
}
```

**自动生成表:**
- `sys_log_202601`
- `sys_log_202602`
- `sys_log_202603`

#### 1.3 按自定义规则分表 (推荐订单表)
```csharp
// 按用户ID取模分32张表
public class OrderRepository
{
    public async Task<Order> GetByIdAsync(int orderId, int userId)
    {
        var tableName = $"pro_order_{userId % 32}";
        return await _db.Queryable<Order>()
            .AS(tableName)
            .Where(o => o.Id == orderId)
            .FirstAsync();
    }
}
```

---

## 🔧 完整配置示例

### 1. 数据库配置 (Program.cs)

```csharp
builder.Services.AddScoped<ISqlSugarClient>(provider =>
{
    var sqlSugar = new SqlSugarScope(new ConnectionConfig
    {
        ConfigId = "bx_core",
        ConnectionString = "Host=localhost;Database=bx_core;...",
        DbType = DbType.PostgreSQL,
        IsAutoCloseConnection = true,
        
        // 开启分表功能
        InitKeyType = InitKeyType.Attribute,
        IsShardSameThread = true,
        
        // 配置缓存
        ConfigureExternalServices = new ConfigureExternalServices
        {
            // 二级缓存(使用Redis)
            DataInfoCacheService = new RedisCache(),
            
            // 序列化配置
            SerializeService = new SerializeService()
        },
        
        // 分表配置
        MoreSettings = new ConnMoreSettings
        {
            // 启用分表
            IsAutoRemoveDataCache = true, // 自动移除缓存
            IsWithNoLockQuery = true,     // 查询不加锁
            IsAutoUpdateQueryFilter = true // 自动更新查询过滤
        }
    });
    
    // AOP配置
    sqlSugar.Aop.OnLogExecuting = (sql, pars) =>
    {
        Console.WriteLine($"[SQL] {sql}");
    };
    
    // 初始化分表
    InitializeSplitTables(sqlSugar);
    
    return sqlSugar;
});

// 初始化分表
void InitializeSplitTables(ISqlSugarClient db)
{
    // 用户表: 创建16张分表
    for (int i = 0; i < 16; i++)
    {
        var tableName = $"sys_user_{i}";
        if (!db.DbMaintenance.IsAnyTable(tableName, false))
        {
            db.CodeFirst.InitTables(typeof(SysUser), tableName);
        }
    }
    
    // 订单表: 创建32张分表
    for (int i = 0; i < 32; i++)
    {
        var tableName = $"pro_order_{i}";
        if (!db.DbMaintenance.IsAnyTable(tableName, false))
        {
            db.CodeFirst.InitTables(typeof(Order), tableName);
        }
    }
}
```

### 2. 分表配置类

```csharp
public static class SqlSugarShardingConfig
{
    /// <summary>
    /// 用户表分表规则: ID取模16
    /// </summary>
    public static string GetUserTableName(int userId)
    {
        return $"sys_user_{userId % 16}";
    }
    
    /// <summary>
    /// 订单表分表规则: 用户ID取模32
    /// </summary>
    public static string GetOrderTableName(int userId)
    {
        return $"pro_order_{userId % 32}";
    }
    
    /// <summary>
    /// 日志表分表规则: 按月
    /// </summary>
    public static string GetLogTableName(DateTime date)
    {
        return $"sys_log_{date:yyyyMM}";
    }
    
    /// <summary>
    /// 获取所有用户表(用于联合查询)
    /// </summary>
    public static List<string> GetAllUserTableNames()
    {
        return Enumerable.Range(0, 16)
            .Select(i => $"sys_user_{i}")
            .ToList();
    }
}
```

### 3. 仓储实现

```csharp
public class UserRepository : IUserRepository
{
    private readonly ISqlSugarClient _db;
    
    /// <summary>
    /// 根据ID查询(自动路由)
    /// </summary>
    public async Task<SysUser?> GetByIdAsync(int userId)
    {
        var tableName = SqlSugarShardingConfig.GetUserTableName(userId);
        
        return await _db.Queryable<SysUser>()
            .AS(tableName)
            .Where(u => u.Id == userId)
            .WithCache(60)  // SqlSugar二级缓存60秒
            .FirstAsync();
    }
    
    /// <summary>
    /// 根据账号查询(联合查询所有分表)
    /// </summary>
    public async Task<SysUser?> GetByAccountAsync(string account)
    {
        var tables = SqlSugarShardingConfig.GetAllUserTableNames();
        
        // UNION ALL 查询所有分表
        var query = _db.UnionAll(
            tables.Select(table =>
                _db.Queryable<SysUser>()
                    .AS(table)
                    .Where(u => u.Account == account)
            ).ToList()
        );
        
        return await query.WithCache(60).FirstAsync();
    }
    
    /// <summary>
    /// 插入用户(自动路由)
    /// </summary>
    public async Task<SysUser> AddAsync(SysUser user)
    {
        // 使用雪花ID生成器
        if (user.Id == 0)
        {
            user.Id = (int)new SnowFlakeNet.IdWorker(1, 1).NextId();
        }
        
        var tableName = SqlSugarShardingConfig.GetUserTableName(user.Id);
        
        await _db.Insertable(user)
            .AS(tableName)
            .ExecuteCommandAsync();
        
        return user;
    }
    
    /// <summary>
    /// 更新用户(自动路由)
    /// </summary>
    public async Task<bool> UpdateAsync(SysUser user)
    {
        var tableName = SqlSugarShardingConfig.GetUserTableName(user.Id);
        
        return await _db.Updateable(user)
            .AS(tableName)
            .RemoveDataCache()  // 移除二级缓存
            .ExecuteCommandAsync() > 0;
    }
    
    /// <summary>
    /// 批量查询(多表联合)
    /// </summary>
    public async Task<List<SysUser>> GetListByIdsAsync(List<int> userIds)
    {
        // 按分表分组
        var groupedByTable = userIds.GroupBy(id => 
            SqlSugarShardingConfig.GetUserTableName(id));
        
        var allUsers = new List<SysUser>();
        
        foreach (var group in groupedByTable)
        {
            var tableName = group.Key;
            var ids = group.ToList();
            
            var users = await _db.Queryable<SysUser>()
                .AS(tableName)
                .Where(u => ids.Contains(u.Id))
                .WithCache(60)
                .ToListAsync();
            
            allUsers.AddRange(users);
        }
        
        return allUsers;
    }
}
```

---

## 🚀 SqlSugar 高级特性

### 1. 二级缓存

```csharp
// 查询缓存(60秒)
var user = await _db.Queryable<SysUser>()
    .AS(tableName)
    .Where(u => u.Id == userId)
    .WithCache(60)  // 缓存60秒
    .FirstAsync();

// 移除缓存
await _db.Updateable(user)
    .AS(tableName)
    .RemoveDataCache()  // 更新时移除缓存
    .ExecuteCommandAsync();
```

### 2. 自动分表创建

```csharp
// CodeFirst 自动创建分表
db.CodeFirst.InitTables(typeof(SysUser), "sys_user_0");
db.CodeFirst.InitTables(typeof(SysUser), "sys_user_1");

// 批量创建
for (int i = 0; i < 16; i++)
{
    db.CodeFirst.InitTables(typeof(SysUser), $"sys_user_{i}");
}
```

### 3. 分表数据迁移

```csharp
// 从 sys_user_0 迁移数据到 sys_user_1
public async Task MigrateDataAsync()
{
    var users = await _db.Queryable<SysUser>()
        .AS("sys_user_0")
        .Where(u => u.Id % 16 == 1)  // 目标是表1的数据
        .ToListAsync();
    
    await _db.Insertable(users)
        .AS("sys_user_1")
        .ExecuteCommandAsync();
    
    await _db.Deleteable<SysUser>()
        .AS("sys_user_0")
        .Where(u => users.Select(x => x.Id).Contains(u.Id))
        .ExecuteCommandAsync();
}
```

### 4. 分表统计查询

```csharp
// 统计所有分表的用户总数
public async Task<int> GetTotalUserCountAsync()
{
    var tables = SqlSugarShardingConfig.GetAllUserTableNames();
    int totalCount = 0;
    
    foreach (var table in tables)
    {
        var count = await _db.Queryable<SysUser>()
            .AS(table)
            .Where(u => u.Status != UserStatus.Deleted)
            .CountAsync();
        
        totalCount += count;
    }
    
    return totalCount;
}
```

---

## 📊 性能优化

### 1. 查询优化

```csharp
// ✅ 好的做法: 单表查询 + 缓存
var user = await _db.Queryable<SysUser>()
    .AS(SqlSugarShardingConfig.GetUserTableName(userId))
    .Where(u => u.Id == userId)
    .WithCache(60)
    .FirstAsync();

// ❌ 不好的做法: 全表扫描
var user = await _db.Queryable<SysUser>()
    .UnionAll(GetAllUserTableNames().Select(t => 
        _db.Queryable<SysUser>().AS(t)
    ).ToList())
    .Where(u => u.Phone == phone)  // 没有分表键,需要查询所有表
    .FirstAsync();
```

### 2. 批量操作优化

```csharp
// ✅ 好的做法: 按分表分组批量操作
public async Task<int> BatchUpdateAsync(List<int> userIds)
{
    var groupedByTable = userIds.GroupBy(id => 
        SqlSugarShardingConfig.GetUserTableName(id));
    
    int totalUpdated = 0;
    
    foreach (var group in groupedByTable)
    {
        var updated = await _db.Updateable<SysUser>()
            .AS(group.Key)
            .SetColumns(u => u.Status == UserStatus.Normal)
            .Where(u => group.ToList().Contains(u.Id))
            .ExecuteCommandAsync();
        
        totalUpdated += updated;
    }
    
    return totalUpdated;
}
```

### 3. 索引优化

```sql
-- 为每张分表创建索引
CREATE INDEX idx_user_0_account ON sys_user_0(account);
CREATE INDEX idx_user_1_account ON sys_user_1(account);
-- ... 其他15张表

-- 使用 SqlSugar 创建索引
for (int i = 0; i < 16; i++)
{
    var tableName = $"sys_user_{i}";
    
    if (!db.DbMaintenance.IsAnyIndex(tableName, "idx_account", false))
    {
        db.DbMaintenance.AddIndex(tableName, new[]
        {
            new DbColumnInfo { DbColumnName = "account" }
        }, "idx_account");
    }
}
```

---

## 🎯 最佳实践

### 1. ID生成策略

#### 方案A: 雪花ID (推荐)
```csharp
// 优点: 分布式、全局唯一、可排序
// 缺点: ID较长
public class SnowFlakeIdGenerator
{
    private static readonly SnowFlakeNet.IdWorker _worker = new(1, 1);
    
    public static long NextId() => _worker.NextId();
}

// 使用
user.Id = (int)SnowFlakeIdGenerator.NextId();
```

#### 方案B: 数据库自增ID
```csharp
// 优点: 简单
// 缺点: 需要两步操作(先插入获取ID,再迁移到目标表)
var id = await _db.Insertable(user)
    .AS("sys_user_0")
    .ExecuteReturnIdentityAsync();

var targetTable = SqlSugarShardingConfig.GetUserTableName((int)id);
if (targetTable != "sys_user_0")
{
    user.Id = (int)id;
    await _db.Insertable(user).AS(targetTable).ExecuteCommandAsync();
    await _db.Deleteable<SysUser>().AS("sys_user_0")
        .Where(u => u.Id == user.Id).ExecuteCommandAsync();
}
```

### 2. 分表数量选择

| 业务 | 建议分表数 | 原因 |
|-----|-----------|------|
| 用户表 | 16 | 用户增长慢,16表够用 |
| 订单表 | 32-64 | 订单增长快,需更多表 |
| 日志表 | 按月 | 历史日志可归档 |
| 消息表 | 8 | 消息量中等 |

### 3. 查询模式

```csharp
// ✅ 模式1: 根据分表键查询(最优)
var user = await GetByIdAsync(userId);  // 单表查询,性能最好

// ⚠️  模式2: 根据非分表键查询(需优化)
var user = await GetByAccountAsync(account);  // 联合查询,性能一般
// 优化方案: 建立 account -> userId 的映射表(Redis)

// ❌ 模式3: 全表扫描(避免)
var users = await GetAllUsersAsync();  // 查询所有表,性能差
```

---

## 📝 总结

使用 SqlSugar 原生分表功能的优势:

1. ✅ **代码简洁**: 自动路由,减少手动代码
2. ✅ **性能优秀**: 二级缓存、批量操作优化
3. ✅ **易于维护**: 统一的分表配置
4. ✅ **功能强大**: UNION ALL、迁移、统计
5. ✅ **生产验证**: 已被大量项目使用

**推荐配置:**
- 用户表: 16张分表 (500万/表)
- 订单表: 32张分表 (1000万/表)
- 日志表: 按月分表
- 使用雪花ID生成器
- 开启SqlSugar二级缓存

---

**文档更新**: 2026-01-11  
**维护团队**: BX开发团队
