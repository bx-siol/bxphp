#  AdminApi - 管理端 API 项目

## 项目概述

 AdminApi 是 BxPlatform 平台的管理端 API 项目，基于 .NET 8.0 开发，提供管理后台所需的所有接口功能。

## 技术栈

- **.NET 10.0**: 最新的 .NET 框架
- **ASP.NET Core**: Web API 框架
- **PostgreSQL**: 数据库（5个分库）
- **Redis**: 缓存和分布式锁
- **SqlSugar**: ORM 框架，支持分库分表
- **JWT**: 身份认证
- **Serilog**: 日志记录

## 项目结构

```
 AdminApi/
├── Controllers/              # 控制器
│   ├── AdminController.cs    # 基础接口（登录、登出等）
│   └── Admin/                # 管理模块
│       ├── DefaultController.cs   # 首页统计
│       ├── UserController.cs      # 用户管理
│       ├── FinanceController.cs   # 财务管理
│       ├── ProductController.cs   # 产品管理
│       ├── GiftController.cs      # 礼品管理
│       ├── NewsController.cs      # 新闻管理
│       ├── SysController.cs       # 系统管理
│       ├── ExtController.cs       # 扩展功能
│       └── TransController.cs     # 翻译
├── Extensions/               # 扩展方法
│   └── ServiceCollectionExtensions.cs
├── Middleware/              # 中间件
│   └── PhpStyleRouteMiddleware.cs  # PHP风格路由
├── Program.cs               # 程序入口
└── appsettings.json        # 配置文件
```

## 核心功能

### 1. 基础接口（AdminController）
- 登录/登出
- 获取系统配置
- 获取用户信息
- 清除缓存
- 获取验证码
- 动态修改表字段值

### 2. 首页统计（DefaultController）
- 获取首页综合数据
- 投资订单统计
- 充值提现统计
- 会员和红包统计
- 万能验证码

### 3. 用户管理（UserController）
- 用户列表/代理列表
- 用户增删改查
- 用户组管理
- 消息管理
- 实名认证审核
- 转账操作

### 4. 财务管理（FinanceController）
- 充值/支付类型管理
- 支付记录查询
- 提现记录管理
- 银行流水管理
- 钱包管理
- UTR查询

### 5. 产品管理（ProductController）
- 产品分类管理（树形结构）
- 商品管理
- 订单管理
- 返利/奖励列表

### 6. 礼品管理（GiftController）
- 优惠券管理
- 抽奖管理
- 奖品管理
- 红包管理

### 7. 新闻管理（NewsController）
- 新闻分类管理（树形结构）
- 文章管理
- 公告管理
- 社区内容管理

### 8. 系统管理（SysController）
- 系统参数配置
- 个人资料管理
- 安全设置
- 权限管理
- 菜单节点管理
- 翻译配置
- 后台设置
- 操作日志

### 9. 扩展功能（ExtController）
- 银行管理
- 客服管理
- 任务管理

## 路由说明

项目支持两种路由风格：

### 1. RESTful 风格（推荐）
```
GET  /api/Admin/Default/GetData
POST /api/Admin/User/Statistics
POST /api/Admin/Finance/Paylog
```

### 2. PHP 风格（兼容性）
通过 PhpStyleRouteMiddleware 中间件实现：
```
GET /api/?m=Admin&c=Default&a=getData
    → 转换为 /api/Admin/Default/GetData

POST /api/?m=Admin&c=User&a=statistics
    → 转换为 /api/Admin/User/Statistics
```

## 配置说明

### appsettings.json

```json
{
  "ConnectionStrings": {
    "BxCore": "PostgreSQL 核心库连接字符串",
    "BxFinance": "PostgreSQL 财务库连接字符串",
    "BxTrade": "PostgreSQL 交易库连接字符串",
    "BxMarketing": "PostgreSQL 营销库连接字符串",
    "BxLog": "PostgreSQL 日志库连接字符串",
    "Redis": "Redis连接字符串"
  },
  "JwtSettings": {
    "SecretKey": "JWT密钥（至少32字符）",
    "Issuer": " Admin",
    "Audience": "BxPlatformAdmins",
    "ExpirationMinutes": 480
  },
  "AdminSettings": {
    "EnableIpWhitelist": false,
    "IpWhitelist": [],
    "MaxLoginAttempts": 5,
    "LockoutMinutes": 30
  }
}
```

## 启动项目

### 1. 命令行启动
```bash
cd src/3.Presentation/ AdminApi
dotnet run
```

### 2. Visual Studio 启动
打开解决方案，设置  AdminApi 为启动项目，按 F5 运行。

### 3. 访问地址
- **Swagger 文档**: http://localhost:5100
- **API 地址**: http://localhost:5100/api

## 认证说明

### JWT Token 使用

1. **登录获取 Token**
```bash
POST /api/Admin/Login
{
  "account": "admin",
  "password": "password"
}

# 返回
{
  "code": 1,
  "msg": "登录成功",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "user": { ... }
  }
}
```

2. **携带 Token 访问接口**

支持三种方式：

**方式1: Header（推荐）**
```
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

**方式2: Query 参数（兼容PHP）**
```
GET /api/Admin/Default/GetData?token=eyJhbGciOiJIUzI1NiIs...
```

**方式3: Form 参数**
```
POST /api/Admin/User/Statistics
Content-Type: application/x-www-form-urlencoded

token=eyJhbGciOiJIUzI1NiIs...&page=1&s_sizes=20
```

## 响应格式

所有接口统一返回 JSON 格式：

```json
{
  "code": 1,        // 1=成功, 0=失败, -98/-99=token过期
  "msg": "操作成功", // 提示消息
  "data": {         // 返回数据
    // ...
  }
}
```

## 分页参数

列表接口支持统一的分页参数：

- `page`: 页码（默认 1）
- `s_sizes`: 每页数量（默认 20，最大 100）
- `s_keyword`: 搜索关键词
- `s_start_time`: 开始时间
- `s_end_time`: 结束时间

## 开发说明

### 1. 添加新接口

在对应的控制器中添加方法，使用特性标注：

```csharp
/// <summary>
/// 接口说明
/// </summary>
[HttpPost("MethodName")]
public IActionResult MethodName()
{
    try
    {
        // 获取参数
        var param = GetParam("param_name");
        var page = GetIntParam("page", 1);
        
        // 业务逻辑
        // ...
        
        return Success(data, "操作成功");
    }
    catch (Exception ex)
    {
        return Fail($"操作失败: {ex.Message}");
    }
}
```

### 2. 参数获取辅助方法

BaseController 提供了多个参数获取方法：

- `GetParam(key, defaultValue)`: 获取字符串参数
- `GetIntParam(key, defaultValue)`: 获取整型参数
- `GetLongParam(key, defaultValue)`: 获取长整型参数
- `GetDecimalParam(key, defaultValue)`: 获取decimal参数
- `GetBoolParam(key, defaultValue)`: 获取布尔参数
- `GetPageParams()`: 获取分页参数
- `GetSearchKeyword()`: 获取搜索关键词
- `GetStartTime()`: 获取开始时间
- `GetEndTime()`: 获取结束时间

### 3. 返回结果辅助方法

- `Success(data, message)`: 返回成功结果
- `Fail(message, code)`: 返回失败结果
- `PagedSuccess(pagedResult)`: 返回分页结果

### 4. 当前用户信息

- `CurrentUserId`: 当前登录管理员ID
- `CurrentUserAccount`: 当前登录管理员账号
- `CurrentUserGroupId`: 当前管理员用户组ID

## TODO 列表

所有控制器的业务逻辑都已框架化，但具体实现标记为 TODO，需要根据实际业务需求完善：

1. 实现数据库访问逻辑（通过 Repository）
2. 实现 Redis 缓存逻辑
3. 实现权限验证逻辑
4. 实现日志记录逻辑
5. 实现具体的业务规则

## 性能优化要点

1. **数据库层面**
   - 使用 SqlSugar 的批量操作减少数据库连接次数
   - 合理使用索引，避免慢查询
   - 使用连接池（已配置）

2. **缓存层面**
   - 热点数据缓存到 Redis
   - 设置合理的缓存过期时间
   - 使用批量操作（mget/hmget）

3. **应用层面**
   - 异步操作（async/await）
   - 合理使用分页，避免一次查询大量数据
   - 使用分布式锁避免并发问题

## 安全注意事项

1. **SQL 注入防护**: 使用参数化查询
2. **XSS 防护**: 对用户输入进行转义
3. **CSRF 防护**: 验证 Token
4. **权限验证**: 每个接口都要验证用户权限
5. **敏感信息**: 不要在日志中记录密码等敏感信息

## 日志说明

日志文件位于项目根目录的 `Logs` 文件夹：
- `admin-log-{date}.txt`: 按天滚动的日志文件

日志级别：
- **Debug**: 调试信息（仅开发环境）
- **Information**: 一般信息
- **Warning**: 警告信息
- **Error**: 错误信息

## 部署说明

### 1. 发布项目
```bash
dotnet publish -c Release -o ./publish
```

### 2. 配置 appsettings.json
修改生产环境的数据库连接字符串和其他配置。

### 3. 运行
```bash
cd publish
dotnet  AdminApi.dll
```

### 4. 使用反向代理（推荐）
使用 Nginx 或 IIS 作为反向代理，配置 HTTPS。

## 联系方式

如有问题，请联系开发团队。
