# BxPlatform .NET 项目 - 快速开始指南

## 📋 项目简介

BxPlatform 是一个采用 DDD(领域驱动设计)架构的分布式系统,从 PHP 重构为 .NET 8.0,支持:
- ✅ PostgreSQL 5分库分表架构
- ✅ Redis 缓存和分布式锁
- ✅ 异步编程,支持高并发
- ✅ 依赖注入和仓储模式
- ✅ 工作单元保证事务一致性

---

## 🚀 快速开始

### 1. 环境要求

| 软件 | 版本 | 说明 |
|-----|------|------|
| .NET SDK | 8.0+ | https://dotnet.microsoft.com |
| PostgreSQL | 14+ | https://www.postgresql.org |
| Redis | 7.0+ | https://redis.io |
| IDE | Visual Studio 2022 / Rider / VS Code | 可选 |

### 2. 克隆项目

```bash
cd d:\code\php\bxphp\new_bx\BxPlatform
```

### 3. 配置数据库

#### 创建PostgreSQL数据库

```sql
-- 连接到PostgreSQL
psql -U postgres

-- 创建5个数据库
CREATE DATABASE bx_core WITH ENCODING='UTF8';
CREATE DATABASE bx_finance WITH ENCODING='UTF8';
CREATE DATABASE bx_trade WITH ENCODING='UTF8';
CREATE DATABASE bx_marketing WITH ENCODING='UTF8';
CREATE DATABASE bx_log WITH ENCODING='UTF8';
```

#### 导入表结构

```bash
# 导入核心库
psql -U postgres -d bx_core -f ../PostgreSQL_doc/bx_core.sql

# 导入其他库
psql -U postgres -d bx_finance -f ../PostgreSQL_doc/bx_finance.sql
psql -U postgres -d bx_trade -f ../PostgreSQL_doc/bx_trade.sql
psql -U postgres -d bx_marketing -f ../PostgreSQL_doc/bx_marketing.sql
psql -U postgres -d bx_log -f ../PostgreSQL_doc/bx_log.sql
```

### 4. 配置Redis

```bash
# Windows: 启动Redis
redis-server

# Linux: 启动Redis
sudo systemctl start redis

# 设置密码(如果需要)
redis-cli
CONFIG SET requirepass 123456
```

### 5. 修改配置文件

编辑 `src\3.Presentation\BxPlatform.Api\appsettings.Development.json`:

```json
{
  "ConnectionStrings": {
    "BxCore": "Host=localhost;Port=5432;Database=bx_core;Username=postgres;Password=你的密码",
    "Redis": "localhost:6379,password=123456,defaultDatabase=0"
  }
}
```

### 6. 还原NuGet包

```bash
cd src\3.Presentation\BxPlatform.Api
dotnet restore
```

### 7. 运行项目

```bash
dotnet run
```

或者使用监视模式(自动重启):
```bash
dotnet watch run
```

### 8. 访问Swagger文档

浏览器打开: http://localhost:5000

---

## 📖 API文档

### 用户API

#### 1. 用户注册
```http
POST /api/v1/users/register
Content-Type: application/json

{
  "account": "testuser",
  "password": "123456",
  "nickname": "测试用户",
  "phone": "13800138000"
}
```

#### 2. 用户登录
```http
POST /api/v1/users/login
Content-Type: application/json

{
  "account": "testuser",
  "password": "123456"
}
```

#### 3. 获取用户信息
```http
GET /api/v1/users/100001
```

#### 4. 充值
```http
POST /api/v1/users/100001/recharge
Content-Type: application/json

{
  "amount": 100.00,
  "remark": "测试充值"
}
```

#### 5. 获取用户列表
```http
GET /api/v1/users?pageIndex=1&pageSize=15&status=2
```

完整API文档请访问 Swagger UI。

---

## 🏗️ 项目结构

```
BxPlatform/
├── src/
│   ├── 1.Core/                              # 核心层
│   │   ├── BxPlatform.Domain/               # 领域层
│   │   │   ├── Entities/                   # 实体
│   │   │   ├── ValueObjects/               # 值对象
│   │   │   ├── Enums/                      # 枚举
│   │   │   └── Interfaces/                 # 仓储接口
│   │   └── BxPlatform.Application/          # 应用层
│   │       ├── Contracts/                   # 契约(接口/DTO)
│   │       └── Services/                    # 服务实现
│   │
│   ├── 2.Infrastructure/                    # 基础设施层
│   │   ├── BxPlatform.Infrastructure/       # 基础设施
│   │   │   ├── Repositories/               # 仓储实现
│   │   │   ├── Sharding/                   # 分库分表
│   │   │   ├── Caching/                    # 缓存
│   │   │   └── DistributedLock/            # 分布式锁
│   │   └── BxPlatform.Common/              # 通用工具
│   │       ├── Helpers/                    # 辅助类
│   │       └── Models/                     # 通用模型
│   │
│   └── 3.Presentation/                     # 表现层
│       └── BxPlatform.Api/                 # API项目
│           ├── Controllers/                # 控制器
│           ├── Extensions/                 # 扩展方法
│           └── Program.cs                  # 启动文件
```

---

## 🔧 核心功能

### 1. 分库分表

系统使用 5 个 PostgreSQL 数据库:

| 数据库 | 用途 | 连接池 |
|-------|------|--------|
| bx_core | 用户、认证、权限 | 100 |
| bx_finance | 充值、提现、钱包 | 80 |
| bx_trade | 订单、产品、收益 | 80 |
| bx_marketing | 红包、抽奖、优惠券 | 50 |
| bx_log | 日志、消息 | 50 |

分表规则:
- 用户表: `sys_user_{uid % 16}` (16张分表)
- 充值记录: `fin_paylog_{uid % 32}` (32张分表)
- 订单表: `pro_order_{uid % 32}` (32张分表)

使用示例:
```csharp
// 自动路由到对应分表
var user = await _userRepository.GetByIdAsync(userId);
```

### 2. Redis缓存

三级缓存架构:
```
请求 → 本地内存缓存 → Redis缓存 → PostgreSQL数据库
```

使用示例:
```csharp
// 获取或设置缓存(自动处理缓存穿透)
var user = await _cacheService.GetOrSetAsync($"user:{userId}", async () =>
{
    return await _userRepository.GetByIdAsync(userId);
}, TimeSpan.FromMinutes(30));
```

### 3. 分布式锁

防止并发操作导致的数据不一致:

```csharp
// 充值时使用分布式锁
var lockKey = $"user:balance:{userId}";
await _distributedLock.ExecuteWithLockAsync(lockKey, async () =>
{
    var user = await _userRepository.GetByIdAsync(userId);
    user.Recharge(amount);
    await _userRepository.UpdateAsync(user);
}, TimeSpan.FromSeconds(10));
```

### 4. 工作单元(事务管理)

保证数据一致性:

```csharp
using (var uow = _unitOfWork)
{
    await uow.BeginTransactionAsync();
    try
    {
        await _userRepository.UpdateAsync(user);
        await _walletRepository.UpdateAsync(wallet);
        await uow.CommitAsync();
    }
    catch
    {
        await uow.RollbackAsync();
        throw;
    }
}
```

---

## 🎯 DDD架构说明

### 领域层 (Domain)
- **职责**: 核心业务逻辑和规则
- **特点**: 不依赖任何外部框架
- **示例**:
```csharp
public class SysUser : Entity, IAggregateRoot
{
    public void Recharge(decimal amount)
    {
        if (amount <= 0)
            throw new ArgumentException("充值金额必须大于0");
        Balance += amount;
    }
}
```

### 应用层 (Application)
- **职责**: 用例编排,协调领域对象
- **特点**: 事务管理,调用领域服务
- **示例**:
```csharp
public class UserService : IUserService
{
    public async Task<bool> RechargeAsync(int userId, decimal amount)
    {
        using (var uow = _unitOfWork)
        {
            var user = await _userRepository.GetByIdAsync(userId);
            user.Recharge(amount);
            await _userRepository.UpdateAsync(user);
            await uow.CommitAsync();
        }
    }
}
```

### 基础设施层 (Infrastructure)
- **职责**: 技术实现(数据库、缓存、外部服务)
- **特点**: 具体实现细节
- **示例**:
```csharp
public class UserRepository : Repository<SysUser>, IUserRepository
{
    public override async Task<SysUser?> GetByIdAsync(int id)
    {
        var tableName = ShardingRouter.GetUserTable(id);
        return await _db.Queryable<SysUser>()
            .AS(tableName)
            .FirstAsync(u => u.Id == id);
    }
}
```

### 表现层 (Presentation)
- **职责**: API接口,接收用户请求
- **特点**: 轻量级,只做参数绑定
- **示例**:
```csharp
[HttpPost("{id}/recharge")]
public async Task<IActionResult> Recharge(int id, [FromBody] RechargeRequest request)
{
    var result = await _userService.RechargeAsync(id, request.Amount, request.Remark);
    return Ok(ApiResult.Success(result));
}
```

---

## ⚡ 性能优化

### 1. 异步编程
所有I/O操作都使用 `async/await`:
- 数据库操作: `await _repository.GetByIdAsync()`
- 缓存操作: `await _cacheService.GetAsync()`
- HTTP请求: `await httpClient.GetAsync()`

### 2. 连接池
PostgreSQL和Redis都配置了连接池:
```json
"BxCore": "Host=localhost;Port=5432;Database=bx_core;Pooling=true;MinPoolSize=10;MaxPoolSize=100"
```

### 3. 索引优化
为常用查询字段建立索引:
```sql
CREATE INDEX idx_user_0_account ON sys_user_0(account);
CREATE INDEX idx_user_0_phone ON sys_user_0(phone);
```

### 4. 缓存策略
- 用户信息: 30分钟
- 配置信息: 10分钟
- 热点数据: 永久缓存

---

## 🐛 调试技巧

### 1. 查看SQL日志

在 `appsettings.Development.json` 中启用:
```json
{
  "Logging": {
    "EnableSqlLog": true
  }
}
```

### 2. 查看Redis命令

```bash
redis-cli monitor
```

### 3. 断点调试

在 Visual Studio 中按 `F5` 启动调试模式。

---

## 📝 常见问题

### 1. 数据库连接失败
- 检查PostgreSQL是否启动: `pg_ctl status`
- 检查密码是否正确
- 检查防火墙是否阻止5432端口

### 2. Redis连接失败
- 检查Redis是否启动: `redis-cli ping`
- 检查密码是否正确
- 检查端口6379是否开放

### 3. 编译错误
```bash
# 清理并重新编译
dotnet clean
dotnet build
```

---

## 🚀 部署指南

### Docker部署

1. 构建镜像:
```bash
docker build -t bxplatform-api:latest -f Dockerfile .
```

2. 运行容器:
```bash
docker run -d -p 5000:80 \
  -e ConnectionStrings__BxCore="Host=db;Port=5432;Database=bx_core;Username=postgres;Password=admin8" \
  -e ConnectionStrings__Redis="redis:6379,password=123456" \
  --name bxplatform-api \
  bxplatform-api:latest
```

### 生产环境部署

1. 修改 `appsettings.Production.json`
2. 发布项目:
```bash
dotnet publish -c Release -o ./publish
```
3. 上传到服务器并运行:
```bash
dotnet BxPlatform.Api.dll
```

---

## 📞 技术支持

- **问题反馈**: 在项目中提Issue
- **技术讨论**: 联系开发团队
- **文档更新**: 2026-01-11

---

**祝你使用愉快!** 🎉
