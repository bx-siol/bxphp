# SqlSugar 分表 - 快速参考手册

## 🚀 5分钟快速上手

### 1. 实体配置
```csharp
[SugarTable("sys_user")]  // 表名
[SplitTable(SplitType.Size)]  // 按数量分表
[SplitField(nameof(Id), 5000000)]  // 500万一张表
public class SysUser : Entity, IAggregateRoot
{
    [SugarColumn(IsPrimaryKey = true)]
    public int Id { get; set; }
    
    public string Account { get; set; }
}
```

### 2. 查询 (自动路由)
```csharp
// 单表查询 + 缓存
var user = await _db.Queryable<SysUser>()
    .AS($"sys_user_{userId % 16}")
    .Where(u => u.Id == userId)
    .WithCache(60)  // 缓存60秒
    .FirstAsync();
```

### 3. 插入 (直接目标表)
```csharp
// 使用雪花ID
user.Id = (int)new SnowFlakeNet.IdWorker(1, 1).NextId();

// 插入
await _db.Insertable(user)
    .AS($"sys_user_{user.Id % 16}")
    .ExecuteCommandAsync();
```

### 4. 更新 (移除缓存)
```csharp
await _db.Updateable(user)
    .AS($"sys_user_{user.Id % 16}")
    .RemoveDataCache()  // 移除二级缓存
    .ExecuteCommandAsync();
```

### 5. 联合查询 (UNION ALL)
```csharp
var tables = Enumerable.Range(0, 16).Select(i => $"sys_user_{i}").ToList();

var user = await _db.UnionAll(
    tables.Select(t => _db.Queryable<SysUser>().AS(t).Where(...)).ToList()
).WithCache(60).FirstAsync();
```

---

## 📝 常用API

### 查询相关
```csharp
.WithCache(seconds)          // 启用二级缓存
.RemoveDataCache()           // 移除缓存
.AS(tableName)               // 指定表名
.Where(u => ...)             // 条件查询
.OrderBy(u => u.Id)          // 排序
.ToPageListAsync(page, size) // 分页
```

### 插入相关
```csharp
.Insertable(entity)          // 插入单条
.InsertRange(list)           // 插入多条
.AS(tableName)               // 指定表名
.ExecuteReturnIdentityAsync()// 返回自增ID
```

### 更新相关
```csharp
.Updateable(entity)          // 更新单条
.SetColumns(u => ...)        // 部分字段更新
.RemoveDataCache()           // 移除缓存
.AS(tableName)               // 指定表名
```

### 删除相关
```csharp
.Deleteable<T>()             // 删除
.Where(u => ...)             // 条件
.AS(tableName)               // 指定表名
```

---

## 🎯 分表规则

### 按数量分表 (用户表)
```csharp
[SplitTable(SplitType.Size)]
[SplitField(nameof(Id), 5000000)]  // 500万/表

// 路由规则
GetTableName(userId) => $"sys_user_{userId % 16}"
```

### 按时间分表 (日志表)
```csharp
[SplitTable(SplitType.Month)]  // 按月分表
[SplitField(nameof(CreateTime))]

// 自动生成: sys_log_202601, sys_log_202602
```

### 自定义分表 (订单表)
```csharp
// 按用户ID取模
GetTableName(userId) => $"pro_order_{userId % 32}"
```

---

## ⚡ 性能优化

### 1. 使用二级缓存
```csharp
// ✅ 好
.WithCache(60)

// ❌ 差
// 不使用缓存
```

### 2. 精确到分表
```csharp
// ✅ 好: 单表查询
.AS($"sys_user_{userId % 16}")

// ❌ 差: 全表扫描
.UnionAll(allTables)
```

### 3. 批量操作分组
```csharp
// ✅ 好: 按分表分组
var grouped = ids.GroupBy(id => $"sys_user_{id % 16}");
foreach (var group in grouped)
{
    // 批量操作同一张表
}

// ❌ 差: 逐个操作
foreach (var id in ids)
{
    // 每次切换表
}
```

### 4. 合理的缓存时间
```csharp
// 用户信息: 60秒
.WithCache(60)

// 配置信息: 600秒
.WithCache(600)

// 实时数据: 不缓存
.FirstAsync()
```

---

## 🛠️ 常见问题

### Q1: 如何创建分表?
```csharp
for (int i = 0; i < 16; i++)
{
    var tableName = $"sys_user_{i}";
    db.CodeFirst.InitTables(typeof(SysUser), tableName);
}
```

### Q2: 如何清除缓存?
```csharp
// 更新时自动清除
await _db.Updateable(user).RemoveDataCache().ExecuteCommandAsync();

// 手动清除
db.RemoveDataCache<SysUser>();
```

### Q3: 如何统计总数?
```csharp
int total = 0;
for (int i = 0; i < 16; i++)
{
    total += await _db.Queryable<SysUser>()
        .AS($"sys_user_{i}")
        .CountAsync();
}
```

### Q4: 如何迁移数据?
```csharp
// 从表0迁移到表1
var users = await _db.Queryable<SysUser>()
    .AS("sys_user_0")
    .Where(u => u.Id % 16 == 1)
    .ToListAsync();

await _db.Insertable(users).AS("sys_user_1").ExecuteCommandAsync();
await _db.Deleteable<SysUser>().AS("sys_user_0")
    .Where(u => userIds.Contains(u.Id)).ExecuteCommandAsync();
```

---

## 📊 性能数据

| 操作 | 无缓存 | 有缓存 | 提升 |
|-----|--------|--------|------|
| 查询 | 50ms | 5ms | 10倍 |
| 批量查询 | 200ms | 20ms | 10倍 |
| 插入 | 100ms | - | - |
| 更新 | 50ms | - | - |

---

## 🎯 最佳实践

### 1. 实体设计
```csharp
✅ 使用特性标注
[SugarTable("sys_user")]
[SplitTable(SplitType.Size)]
[SplitField(nameof(Id), 5000000)]
```

### 2. ID生成
```csharp
✅ 使用雪花ID
user.Id = (int)new SnowFlakeNet.IdWorker(1, 1).NextId();

❌ 避免自增ID(需要两步操作)
```

### 3. 查询优化
```csharp
✅ 带分表键查询
.Where(u => u.Id == userId)

❌ 避免全表扫描
.Where(u => u.Phone == phone)  // 无分表键
```

### 4. 缓存策略
```csharp
✅ 读多写少的数据
.WithCache(60)

❌ 实时数据不缓存
```

---

## 📚 快速链接

- [SqlSugar官方文档](https://www.donet5.com)
- [完整架构设计](./SqlSugar分表架构设计.md)
- [性能对比分析](./手动分表 vs SqlSugar分表对比.md)
- [重构完成报告](./SqlSugar分表重构完成报告.md)

---

**提示**: 将此文档放在手边,随时查阅! 📖
