【项目基础约束】
如果生成了文档说明全放在doc目录下，文件命名用中文。如果生成了多个文档就在doc目录下新建一个文件夹，命名一样要中文。
1.  技术栈版本：后端 PHP7.4（禁止使用 PHP8.0+ 新增语法/函数，需兼容 PHP7.4 废弃特性：如 ereg 系列、mysql_ 系列扩展）、MySQL8.0（需支持 InnoDB 引擎、窗口函数，遵循 MySQL8.0 认证规则，避免使用已废弃的 SQL 语法）、Redis（优先使用 string/hash/zset 数据结构，需考虑缓存过期策略、与 MySQL 数据一致性）； 
2.  项目类型：虚拟充值系统（核心业务包含：用户注册/登录、充值订单创建、支付回调处理、虚拟商品库存扣减、充值结果下发、订单数据统计、用户余额管理）。
3.  核心诉求：所有输出内容需优先兼顾「性能优化」（针对当前技术栈的性能瓶颈，如 PHP 脚本执行效率、MySQL 查询速度、Redis 缓存利用率 ），其次保证代码规范性、可维护性和业务安全性（防刷、防重复提交、数据校验、SQL 注入防护）。
4.  编码规范：
    - PHP：遵循 PSR-4 自动加载规范，合理使用命名空间，避免全局函数/变量泛滥，合理使用 OPcache 相关优化建议。
    - MySQL：表结构设计需合理建立索引（主键索引、联合索引、唯一索引，避免冗余索引），优先使用批量操作减少数据库连接次数，避免大事务、慢查询（如 select *、未加索引的 join/where）。
    - Redis：避免频繁 set/get 零散数据，合理使用批量操作（如 mget/hmget），设置合理的缓存过期时间（避免缓存雪崩），针对热点数据（如高频虚拟商品）提供缓存优化方案。 
5.  输出要求：请按照「方案概述 → 具体实现（代码/配置） → 性能优化亮点说明 → 注意事项」的结构输出内容，代码需附带详细注释，关键步骤提供可落地的操作指引，避免模糊性表述。
6. 每次扫描项目目录都不需要读取以下目录：h5、ht8888、node_modules、public、logs
7. 当前项目的数据库结构请读取根目录下的bx.sql，获取数据库结构（如表名、字段名、索引等）。
8. powershell 脚本执行权限问题：请在 powershell 中执行脚本时，添加执行权限（如 Set-ExecutionPolicy RemoteSigned），否则会报错。&& 符号好像不能执行，请用分开执行命令。

<!--  
核心目标：目前需要采用新的框架来支持高并发
1.已经把数据库进行了拆分，在PostgreSQL_doc目录下
2.新的框架所产生的文件放在new_bx文件夹中
3.依据伪静态.md中的路由规则，再根据当前的项目代码进行新的框架编写。
4.目标是为了支持10万的并发，以及后端管理端的快速统计查询。
5. -->

# 项目重构任务（第1阶段：PHP用户控制器 → Furion控制器复刻）
    原有PHP控制器核心信息示例：
   -  前端最终是拼接成以下URL：/api/?m=Admin&c=User&a=user_update  其中m是模块名，c是控制器名，a是方法名。附带的数据是通过表单提交的
   -  用户更新接口：URL=/api/?m=Admin&c=User&a=user_update，请求方法=POST  
## 一、项目上下文
1.  现有项目：基于PHP7.4的单机同步系统，控制器层包含「XXX模块」（如用户模块、订单模块），接口路径、请求方法、业务逻辑已明确（后续补充具体PHP控制器代码片段）。
2.  重构技术栈：**强制使用 .NET 10 + Furion 最新稳定版 + SqlSugar Core ORM**，兼容PostgreSQL 5个分库（bx_core/bx_trade/bx_finance/bx_marketing/bx_log）。
3.  重构策略：「先复刻、后优化」—— 第一步完整复刻PHP控制器功能，保证接口一致性；第二步预留Furion优化扩展点（依赖注入、队列、仓储层）。
4.  目录规范：所有文件存放于「new_bx」目录下，遵循Furion项目结构（具体路径：new_bx/[项目层]/[模块]/[文件名].cs）。
5.  数据库准备：PostgreSQL分库表结构存放于「PostgreSQL_doc」，已通过SqlSugar配置分库连接池（如果在复刻的过程中发现部分表缺少了字段，需要在PostgreSQL_doc中添加缺失字段的SQL语句）。
6.  核心目标：复刻后接口与原有PHP系统完全兼容（URL、参数、返回格式一致），同时预留后续优化空间，为依赖注入、队列集成铺路。

## 二、当前阶段核心任务
1.  **Furion 控制器创建**：创建**Controller.cs，继承Furion的BaseApiController，实现原php中的接口的复刻
2.  **SqlSugar数据操作复刻**：在控制器中直接注入SqlSugarDbContext，使用bx_core分库客户端，复刻ThinkORM操作：
   -  登录接口：查询sys_user表的account字段，对比MD5加密后的密码，返回用户数据。
   -  详情接口：根据id查询sys_user表，返回用户详情数据。
   -  复刻PHP的空值判断、异常处理逻辑（如账号不存在返回{code:-1,msg:"账号不存在"}）。
   -  注意以上只是示例，实际的执行需要根据php代码来复刻。
3.  **返回格式对齐**：封装ApiResult通用返回类，保证返回格式与PHP完全一致（code、msg、data字段，数据类型对齐）。
4.  **预留扩展点**：
   -  预留用户服务层接口（ISysUserService），标注后续将业务逻辑拆分到服务层，实现依赖注入。
   -  预留登录日志队列接口，标注后续将日志记录改为Furion BackgroundJob异步队列。
   -  预留参数校验扩展，标注后续使用Furion DataAnnotations实现参数校验。
5.  **功能验证**：提供测试步骤，验证两个接口的返回结果与PHP接口完全一致。

## 三、约束要求
1.  **复刻优先**：
   -  严格按照PHP的逻辑实现，密码加密必须使用MD5（与PHP的md5()函数结果一致），禁止使用其他加密方式。
   -  暂时在控制器中直接编写数据操作和业务逻辑，不拆分仓储层/服务层，与PHP控制器结构对齐。
   -  返回错误码与PHP完全一致（如账号不存在返回code=-1，密码错误返回code=-2），禁止自定义错误码。
2.  **技术栈约束**：
   -  路由：使用Furion注解路由（@ApiController、@HttpPost、@HttpGet("{id}")），配合已配置的伪静态中间件。
   -  ORM：使用SqlSugarDbContext的BxCoreClient，查询sys_user表，使用SqlSugar的Queryable、FirstAsync等方法，禁止原生SQL。
   -  异步：所有数据操作使用SqlSugar异步方法，控制器方法标记async/await，保证高并发基础。
   -  兼容：.NET 10语法，C#代码兼容PHP7.4对应的业务逻辑（如时间戳转换、字符串处理）。
3.  **扩展预留**：
   -  预留的服务层接口需添加详细注释，示例：「// 后续优化：注入ISysUserService，将业务逻辑迁移到服务层」。
   -  日志记录部分暂时同步实现，预留队列调用入口，示例：「// 后续优化：使用Furion.BackgroundJob.Enqueue(() => RecordLoginLogAsync(userId));」。

## 四、交付物要求（以下是示例，并不是最终交付物）
1.  **完整代码文件**：
   -  new_bx/new_bx.Common/Models/ApiResult.cs（通用返回结果类，保证与PHP返回格式一致）。
   -  new_bx/new_bx.Api/Controllers/Core/UserController.cs（完整的Furion用户控制器，包含两个接口、SqlSugar操作、扩展点预留）。
   -  new_bx/new_bx.Common/Helpers/EncryptHelper.cs（MD5加密工具类，与PHP md5()函数结果一致）。
2.  **PHP vs .NET 复刻对比表**：
   | 对比项         | PHP控制器实现                          | Furion控制器实现                          |
   |----------------|---------------------------------------|------------------------------------------|
   | 登录接口URL    | /user/login.html（POST）               | /user/login.html（POST，Furion注解路由）  |
   | 密码校验       | md5($password) 对比数据库密码          | EncryptHelper.Md5Encrypt(password) 对比数据库密码 |
   | 数据操作       | ThinkORM::table('sys_user')->where('account', $account)->find() | _dbContext.BxCoreClient.Queryable<SysUserEntity>().Where(u => u.Account == account).FirstAsync() |
   | 返回格式       | {code:200,msg:"登录成功",data:{...}}  | {code:200,msg:"登录成功",data:{...}}（完全一致） |
   | 预留扩展点     | 无                                    | 服务层注入、队列日志、参数校验           |
3.  **功能验证步骤**：
   -  步骤1：启动Furion服务（dotnet run --project new_bx/new_bx.Api）。
   -  步骤2：使用PostMan请求POST http://localhost:5000/user/login.html，传入account=test、password=123456，对比PHP接口返回结果。
   -  步骤3：使用PostMan请求GET http://localhost:5000/user/detail/1.html，对比PHP接口返回的用户详情数据。
   -  步骤4：验证错误场景（账号不存在、密码错误），确认返回的code和msg与PHP完全一致。
4.  **后续优化指南**：
   -  步骤1：创建ISysUserService和SysUserService，将控制器中的业务逻辑迁移到服务层，实现依赖注入。
   -  步骤2：集成Furion BackgroundJob，将登录日志记录改为异步队列，提升高并发性能。
   -  步骤3：创建用户请求模型（UserLoginRequest），使用Furion DataAnnotations实现参数校验，替换硬编码校验。
   -  步骤4：拆分仓储层（SysUserRepository），将SqlSugar数据操作迁移到仓储层，实现业务与数据操作解耦。