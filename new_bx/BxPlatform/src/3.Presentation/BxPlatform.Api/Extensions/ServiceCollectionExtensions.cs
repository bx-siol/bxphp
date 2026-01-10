using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using SqlSugar;
using StackExchange.Redis;
using BxPlatform.Application.Contracts.Users;
using BxPlatform.Application.Services.Users;
using BxPlatform.Domain.Interfaces;
using BxPlatform.Domain.Interfaces.Repositories;
using BxPlatform.Infrastructure.Caching;
using BxPlatform.Infrastructure.DistributedLock;
using BxPlatform.Infrastructure.Repositories;
using BxPlatform.Infrastructure.Repositories.Base;

namespace BxPlatform.Api.Extensions
{
    /// <summary>
    /// 服务注册扩展
    /// </summary>
    public static class ServiceCollectionExtensions
    {
        /// <summary>
        /// 注册应用层服务
        /// </summary>
        public static IServiceCollection AddApplicationServices(this IServiceCollection services)
        {
            // 注册应用服务
            services.AddScoped<IUserService, UserService>();
            
            // TODO: 注册其他服务
            // services.AddScoped<IFinanceService, FinanceService>();
            // services.AddScoped<ITradeService, TradeService>();

            return services;
        }

        /// <summary>
        /// 注册基础设施层服务
        /// </summary>
        public static IServiceCollection AddInfrastructureServices(
            this IServiceCollection services,
            IConfiguration configuration)
        {
            // 1. 注册SqlSugar(PostgreSQL 5分库 + 分表功能)
            services.AddScoped<ISqlSugarClient>(provider =>
            {
                var connectionConfigs = new List<ConnectionConfig>
                {
                    // bx_core - 核心库
                    new ConnectionConfig
                    {
                        ConfigId = "bx_core",
                        ConnectionString = configuration.GetConnectionString("BxCore") ??
                            "Host=localhost;Port=5432;Database=bx_core;Username=postgres;Password=admin8",
                        DbType = DbType.PostgreSQL,
                        IsAutoCloseConnection = true,
                        InitKeyType = InitKeyType.Attribute,
                        IsShardSameThread = true,
                        
                        // SqlSugar分表配置
                        MoreSettings = new ConnMoreSettings
                        {
                            IsAutoRemoveDataCache = true, // 自动移除缓存
                            IsWithNoLockQuery = true,     // 查询不加锁
                            IsAutoUpdateQueryFilter = true // 自动更新查询过滤
                        },
                        
                        // 配置缓存(使用内存缓存)
                        ConfigureExternalServices = new ConfigureExternalServices
                        {
                            // 二级缓存
                            DataInfoCacheService = new SqlSugarCacheService()
                        }
                    },
                    // bx_finance - 财务库
                    new ConnectionConfig
                    {
                        ConfigId = "bx_finance",
                        ConnectionString = configuration.GetConnectionString("BxFinance") ??
                            "Host=localhost;Port=5432;Database=bx_finance;Username=postgres;Password=admin8",
                        DbType = DbType.PostgreSQL,
                        IsAutoCloseConnection = true,
                        InitKeyType = InitKeyType.Attribute,
                        IsShardSameThread = true
                    },
                    // bx_trade - 交易库
                    new ConnectionConfig
                    {
                        ConfigId = "bx_trade",
                        ConnectionString = configuration.GetConnectionString("BxTrade") ??
                            "Host=localhost;Port=5432;Database=bx_trade;Username=postgres;Password=admin8",
                        DbType = DbType.PostgreSQL,
                        IsAutoCloseConnection = true,
                        InitKeyType = InitKeyType.Attribute,
                        IsShardSameThread = true
                    },
                    // bx_marketing - 营销库
                    new ConnectionConfig
                    {
                        ConfigId = "bx_marketing",
                        ConnectionString = configuration.GetConnectionString("BxMarketing") ??
                            "Host=localhost;Port=5432;Database=bx_marketing;Username=postgres;Password=admin8",
                        DbType = DbType.PostgreSQL,
                        IsAutoCloseConnection = true,
                        InitKeyType = InitKeyType.Attribute,
                        IsShardSameThread = true
                    },
                    // bx_log - 日志库
                    new ConnectionConfig
                    {
                        ConfigId = "bx_log",
                        ConnectionString = configuration.GetConnectionString("BxLog") ??
                            "Host=localhost;Port=5432;Database=bx_log;Username=postgres;Password=admin8",
                        DbType = DbType.PostgreSQL,
                        IsAutoCloseConnection = true,
                        InitKeyType = InitKeyType.Attribute,
                        IsShardSameThread = true
                    }
                };

                var sqlSugar = new SqlSugarScope(connectionConfigs);

                // 开发环境打印SQL
                if (configuration.GetValue<bool>("Logging:EnableSqlLog"))
                {
                    sqlSugar.Aop.OnLogExecuting = (sql, pars) =>
                    {
                        Console.WriteLine($"[SQL] {sql}");
                        if (pars != null && pars.Length > 0)
                        {
                            Console.WriteLine($"[Params] {string.Join(", ", pars.Select(p => $"{p.ParameterName}={p.Value}"))}");
                        }
                    };
                }

                // 初始化分表(创建表结构)
                InitializeSplitTables(sqlSugar);

                return sqlSugar;
            });

            return services;
        }

        /// <summary>
        /// 初始化分表
        /// 自动创建所有分表
        /// </summary>
        private static void InitializeSplitTables(ISqlSugarClient db)
        {
            try
            {
                Console.WriteLine("[SqlSugar] 开始初始化分表...");

                // 1. 用户表: 16张分表
                for (int i = 0; i < 16; i++)
                {
                    var tableName = $"sys_user_{i}";
                    if (!db.DbMaintenance.IsAnyTable(tableName, false))
                    {
                        Console.WriteLine($"[SqlSugar] 创建分表: {tableName}");
                        db.CodeFirst.InitTables(typeof(BxPlatform.Domain.Entities.Users.SysUser), tableName);
                    }
                }

                // 2. 订单表: 32张分表 (预留,暂不创建)
                // for (int i = 0; i < 32; i++)
                // {
                //     var tableName = $"pro_order_{i}";
                //     if (!db.DbMaintenance.IsAnyTable(tableName, false))
                //     {
                //         db.CodeFirst.InitTables(typeof(Order), tableName);
                //     }
                // }

                // 3. 日志表: 创建当前月份的表
                var logTableName = $"sys_log_{DateTime.Now:yyyyMM}";
                if (!db.DbMaintenance.IsAnyTable(logTableName, false))
                {
                    Console.WriteLine($"[SqlSugar] 创建日志表: {logTableName}");
                    // db.CodeFirst.InitTables(typeof(SysLog), logTableName);
                }

                Console.WriteLine("[SqlSugar] 分表初始化完成!");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"[SqlSugar] 分表初始化失败: {ex.Message}");
                // 不影响启动,只记录错误
            }
        }
    }

    /// <summary>
    /// SqlSugar 内存缓存服务
    /// </summary>
    public class SqlSugarCacheService : ICacheService
    {
        private static readonly Microsoft.Extensions.Caching.Memory.MemoryCache _cache 
            = new(new Microsoft.Extensions.Caching.Memory.MemoryCacheOptions());

        public void Add<V>(string key, V value)
        {
            _cache.Set(key, value, TimeSpan.FromMinutes(10));
        }

        public void Add<V>(string key, V value, int cacheDurationInSeconds)
        {
            _cache.Set(key, value, TimeSpan.FromSeconds(cacheDurationInSeconds));
        }

        public bool ContainsKey<V>(string key)
        {
            return _cache.TryGetValue(key, out _);
        }

        public V Get<V>(string key)
        {
            return _cache.Get<V>(key);
        }

        public IEnumerable<string> GetAllKey<V>()
        {
            return new List<string>();
        }

        public V GetOrCreate<V>(string cacheKey, Func<V> create, int cacheDurationInSeconds = int.MaxValue)
        {
            if (_cache.TryGetValue(cacheKey, out V value))
            {
                return value;
            }

            value = create();
            _cache.Set(cacheKey, value, TimeSpan.FromSeconds(cacheDurationInSeconds));
            return value;
        }

        public void Remove<V>(string key)
        {
            _cache.Remove(key);
        }
    }
}


            // 2. 注册Redis
            var redisConnection = configuration.GetConnectionString("Redis") ?? 
                "localhost:6379,password=123456,defaultDatabase=0";
            
            services.AddSingleton<IConnectionMultiplexer>(sp =>
            {
                return ConnectionMultiplexer.Connect(redisConnection);
            });

            // 3. 注册缓存服务
            services.AddSingleton<ICacheService, RedisCacheService>();

            // 4. 注册分布式锁
            services.AddSingleton<IDistributedLock, RedisDistributedLock>();

            // 5. 注册仓储
            services.AddScoped<IUserRepository, UserRepository>();
            // TODO: 注册其他仓储

            // 6. 注册工作单元
            services.AddScoped<IUnitOfWork, UnitOfWork>();

            return services;
        }
    }
}
