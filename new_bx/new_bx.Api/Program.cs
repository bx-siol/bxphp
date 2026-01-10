using Microsoft.AspNetCore.Builder;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using SqlSugar;
using new_bx.Api.Middleware;

var builder = WebApplication.CreateBuilder(args);

// 添加控制器服务
builder.Services.AddControllers()
    .AddJsonOptions(options =>
    {
        // 配置JSON序列化选项，与PHP返回格式保持一致
        options.JsonSerializerOptions.PropertyNamingPolicy = null; // 保持属性名原样
        options.JsonSerializerOptions.DefaultIgnoreCondition = System.Text.Json.Serialization.JsonIgnoreCondition.WhenWritingNull;
    });

// 添加CORS支持（跨域）
builder.Services.AddCors(options =>
{
    options.AddDefaultPolicy(policy =>
    {
        policy.AllowAnyOrigin()
              .AllowAnyMethod()
              .AllowAnyHeader();
    });
});

// 添加SqlSugar ORM服务（支持5个PostgreSQL分库）
builder.Services.AddScoped<ISqlSugarClient>(provider =>
{
    var client = new SqlSugarScope(new List<ConnectionConfig>
    {
        // bx_core - 核心库（用户、系统配置等）
        new ConnectionConfig
        {
            ConfigId = "bx_core",
            ConnectionString = builder.Configuration.GetConnectionString("BxCore") ?? 
                              "Host=localhost;Port=5432;Database=bx_core;Username=postgres;Password=admin8",
            DbType = DbType.PostgreSQL,
            IsAutoCloseConnection = true,
            InitKeyType = InitKeyType.Attribute,
            IsShardSameThread = true
        },
        // bx_trade - 交易库
        new ConnectionConfig
        {
            ConfigId = "bx_trade",
            ConnectionString = builder.Configuration.GetConnectionString("BxTrade") ?? 
                              "Host=localhost;Port=5432;Database=bx_trade;Username=postgres;Password=admin8",
            DbType = DbType.PostgreSQL,
            IsAutoCloseConnection = true,
            InitKeyType = InitKeyType.Attribute,
            IsShardSameThread = true
        },
        // bx_finance - 财务库
        new ConnectionConfig
        {
            ConfigId = "bx_finance",
            ConnectionString = builder.Configuration.GetConnectionString("BxFinance") ?? 
                              "Host=localhost;Port=5432;Database=bx_finance;Username=postgres;Password=admin8",
            DbType = DbType.PostgreSQL,
            IsAutoCloseConnection = true,
            InitKeyType = InitKeyType.Attribute,
            IsShardSameThread = true
        },
        // bx_marketing - 营销库
        new ConnectionConfig
        {
            ConfigId = "bx_marketing",
            ConnectionString = builder.Configuration.GetConnectionString("BxMarketing") ?? 
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
            ConnectionString = builder.Configuration.GetConnectionString("BxLog") ?? 
                              "Host=localhost;Port=5432;Database=bx_log;Username=postgres;Password=admin8",
            DbType = DbType.PostgreSQL,
            IsAutoCloseConnection = true,
            InitKeyType = InitKeyType.Attribute,
            IsShardSameThread = true
        }
    });
    return client;
});

var app = builder.Build();

// 启用CORS
app.UseCors();

// 使用PHP风格路由中间件（必须在UseRouting之前）
app.UsePhpStyleRoute();

// 启用路由
app.UseRouting();

// 映射控制器
app.MapControllers();

Console.WriteLine("========================================");
Console.WriteLine("Furion用户控制器已启动");
Console.WriteLine("支持PHP风格路由：/api/?m=Admin&c=User&a=方法名");
Console.WriteLine("数据库：PostgreSQL 5分库（bx_core/bx_trade/bx_finance/bx_marketing/bx_log）");
Console.WriteLine("========================================");

app.Run();