# 实体层更新说明

## 📝 更新概述

根据最新的PostgreSQL数据库结构,已完成**10个核心实体类**的创建和更新,确保.NET实体与数据库表结构的完全一致性。

## 🎯 更新时间

**2026-01-11**

## 📊 实体清单

### ✅ 已完成的实体 (10个)

#### 1. 用户模块 (bx_core) - 5个实体

| 实体类 | 数据库表 | 文件路径 | 说明 |
|--------|---------|---------|------|
| **SysUser** | sys_user | `Entities/Users/SysUser.cs` | 用户聚合根(60+字段) |
| UserRealNameAuth | sys_user_rauth | `Entities/Users/UserRealNameAuth.cs` | 实名认证 |
| UserToken | sys_user_token | `Entities/Users/UserToken.cs` | 登录令牌 |
| UserWechatInfo | sys_user_wechat | `Entities/Users/UserWechatInfo.cs` | 微信信息 |
| UserSetting | sys_user_setting | `Entities/Users/UserSetting.cs` | 用户设置 |

#### 2. 财务模块 (bx_finance) - 2个实体

| 实体类 | 数据库表 | 文件路径 | 说明 |
|--------|---------|---------|------|
| **PayLog** | fin_paylog | `Entities/Finance/PayLog.cs` | 充值记录聚合根 |
| **CashLog** | fin_cashlog | `Entities/Finance/CashLog.cs` | 提现记录聚合根 |

#### 3. 交易模块 (bx_trade) - 1个实体

| 实体类 | 数据库表 | 文件路径 | 说明 |
|--------|---------|---------|------|
| **ProductOrder** | pro_order | `Entities/Trade/ProductOrder.cs` | 订单聚合根 |

#### 4. 产品模块 (bx_core) - 1个实体

| 实体类 | 数据库表 | 文件路径 | 说明 |
|--------|---------|---------|------|
| ProductGoods | pro_goods | `Entities/Products/ProductGoods.cs` | 产品商品 |

#### 5. 钱包模块 (bx_core) - 1个实体

| 实体类 | 数据库表 | 文件路径 | 说明 |
|--------|---------|---------|------|
| UserWallet | wallet_list | `Entities/Wallet/UserWallet.cs` | 用户钱包 |

## 🔥 核心更新亮点

### 1. SysUser 实体大幅扩展

**从18个字段扩展到60+个字段**,完整覆盖所有业务场景:

```csharp
// 新增核心字段分类
✅ 二级密码管理
✅ 财务扩展字段
✅ API安全配置
✅ 禁用管理
✅ 谷歌验证器
✅ 位置信息
✅ 第三方登录
✅ 团队管理
✅ VIP系统
```

### 2. 完整的业务方法封装

```csharp
// SysUser 业务方法示例
user.ChangePassword("new_password");           // 修改密码
user.SetSecondaryPassword("secondary_pwd");    // 设置二级密码
user.Recharge(1000m);                          // 充值
user.Deduct(500m);                             // 扣款
user.FreezeBalance(200m);                      // 冻结余额
user.Enable();                                 // 启用用户
user.EnableGoogleAuth("secret_key");           // 启用谷歌验证器
user.SetAuthenticated();                       // 实名认证通过
```

### 3. SqlSugar ORM 完整配置

```csharp
[SugarTable("sys_user")]
public class SysUser : Entity, IAggregateRoot
{
    [SugarColumn(IsPrimaryKey = true)]
    public int Id { get; set; }
    
    [SugarColumn(Length = 64, IsNullable = false)]
    public string Account { get; set; }
    
    [SugarColumn(DecimalDigits = 2)]
    public decimal Balance { get; set; }
}
```

### 4. DDD 领域驱动设计

```csharp
// 聚合根标识
public class SysUser : Entity, IAggregateRoot { }

// 私有构造 + 工厂方法
private SysUser() { }
public SysUser(string account, string password) { }

// 业务逻辑封装
public void Recharge(decimal amount) 
{
    if (amount <= 0) throw new ArgumentException();
    Balance += amount;
    // 首次充值逻辑
    if (FirstPayDay == 0) { ... }
}
```

## 📁 目录结构

```
src/1.Core/ Domain/Entities/
├── Base/
│   ├── Entity.cs                    # 实体基类
│   └── IAggregateRoot.cs            # 聚合根标识接口
├── Users/
│   ├── SysUser.cs                   # ⭐ 用户聚合根
│   ├── UserRealNameAuth.cs          # 实名认证
│   ├── UserToken.cs                 # 登录令牌
│   ├── UserWechatInfo.cs            # 微信信息
│   └── UserSetting.cs               # 用户设置
├── Finance/
│   ├── PayLog.cs                    # ⭐ 充值记录聚合根
│   └── CashLog.cs                   # ⭐ 提现记录聚合根
├── Trade/
│   └── ProductOrder.cs              # ⭐ 订单聚合根
├── Products/
│   └── ProductGoods.cs              # 产品商品
└── Wallet/
    └── UserWallet.cs                # 用户钱包
```

## 📖 配套文档

已创建完整的文档体系:

1. **实体更新完成报告.md**
   - 详细的实体字段说明
   - 业务方法文档
   - 更新亮点总结

2. **数据库字段映射对照表.md**
   - 数据库字段 → C#属性完整映射
   - 类型转换规则
   - 命名规范说明

3. **README.md** (本文件)
   - 快速概览
   - 目录结构
   - 下一步计划

## 🚀 下一步计划

### 阶段1: 仓储层开发 (进行中)

```
□ 创建仓储接口
  - IPayLogRepository
  - ICashLogRepository  
  - IProductOrderRepository
  - IProductGoodsRepository
  - IUserWalletRepository

□ 实现仓储类
  - 集成SqlSugar
  - 实现分表逻辑
  - 集成缓存策略
```

### 阶段2: 应用服务层

```
□ FinanceService (财务服务)
  - 充值业务流程
  - 提现业务流程
  - 余额管理

□ TradeService (交易服务)
  - 订单创建
  - 订单管理
  - 奖励发放

□ ProductService (产品服务)
  - 产品查询
  - 产品管理
```

### 阶段3: API控制器层

```
□ FinanceController
  - 充值接口
  - 提现接口

□ TradeController
  - 订单接口

□ ProductController
  - 产品接口
```

### 阶段4: 数据迁移

```
□ MySQL → PostgreSQL 迁移脚本
□ 数据完整性验证
□ 性能测试
```

## 🔍 快速查找

### 查看用户实体完整定义
```bash
cat src/1.Core/ Domain/Entities/Users/SysUser.cs
```

### 查看字段映射关系
```bash
cat 数据库字段映射对照表.md | grep "sys_user"
```

### 查看实体统计信息
```bash
cat 实体更新完成报告.md | grep "实体数量"
```

## ✅ 验证清单

- [x] 所有实体字段与数据库完全一致
- [x] SqlSugar特性配置正确
- [x] 聚合根实现 `IAggregateRoot`
- [x] 业务方法封装完整
- [x] 注释清晰完整
- [x] 命名规范符合C#约定
- [x] 配套文档完整

## 💡 使用示例

### 创建用户

```csharp
var user = new SysUser(
    account: "user001",
    password: EncryptionHelper.GetPassword("123456"),
    nickname: "测试用户",
    parentId: 0
);

user.SetRegisterIp("192.168.1.1");
user.SetInviteCode("ABC123");

await _userRepository.AddAsync(user);
```

### 用户充值

```csharp
// 获取用户
var user = await _userRepository.GetByIdAsync(userId);

// 充值
user.Recharge(1000m);

// 更新
await _userRepository.UpdateAsync(user);
```

### 创建订单

```csharp
var order = new ProductOrder
{
    Osn = GenerateOrderNumber(),
    Uid = userId,
    GoodsId = goodsId,
    Money = 1000m,
    Days = 30,
    Status = 1,
    CreateTime = (int)DateTimeOffset.UtcNow.ToUnixTimeSeconds(),
    CreateDay = int.Parse(DateTime.Now.ToString("yyyyMMdd"))
};

await _orderRepository.AddAsync(order);
```

## 📞 技术支持

如有问题,请查看:
1. `实体更新完成报告.md` - 详细技术文档
2. `数据库字段映射对照表.md` - 字段映射查询
3. `PostgreSQL_doc/` - 原始数据库设计文档

---

**更新日期**: 2026-01-11  
**版本**: v2.0  
**状态**: ✅ 实体层完成,进入仓储层开发阶段
