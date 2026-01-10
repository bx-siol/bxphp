using Microsoft.AspNetCore.Builder;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using  AdminApi.Extensions;
using  AdminApi.Middleware;
using Serilog;

var builder = WebApplication.CreateBuilder(args);

// 配置Serilog日志
Log.Logger = new LoggerConfiguration()
    .ReadFrom.Configuration(builder.Configuration)
    .CreateLogger();

builder.Host.UseSerilog();

// 添加服务
builder.Services.AddControllers()
    .AddJsonOptions(options =>
    {
        // 保持属性名原样,与PHP风格一致
        options.JsonSerializerOptions.PropertyNamingPolicy = null;
        options.JsonSerializerOptions.DefaultIgnoreCondition = 
            System.Text.Json.Serialization.JsonIgnoreCondition.WhenWritingNull;
    });

// 添加Swagger文档
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen(c =>
{
    c.SwaggerDoc("v1", new() { Title = "BxPlatform Admin API", Version = "v1" });
    
    // 添加JWT认证说明
    c.AddSecurityDefinition("Bearer", new Microsoft.OpenApi.Models.OpenApiSecurityScheme
    {
        Description = "JWT Authorization header using the Bearer scheme. Example: \"Authorization: Bearer {token}\"",
        Name = "Authorization",
        In = Microsoft.OpenApi.Models.ParameterLocation.Header,
        Type = Microsoft.OpenApi.Models.SecuritySchemeType.ApiKey,
        Scheme = "Bearer"
    });

    c.AddSecurityRequirement(new Microsoft.OpenApi.Models.OpenApiSecurityRequirement
    {
        {
            new Microsoft.OpenApi.Models.OpenApiSecurityScheme
            {
                Reference = new Microsoft.OpenApi.Models.OpenApiReference
                {
                    Type = Microsoft.OpenApi.Models.ReferenceType.SecurityScheme,
                    Id = "Bearer"
                }
            },
            Array.Empty<string>()
        }
    });
    
    // 添加XML注释
    var xmlFile = $"{System.Reflection.Assembly.GetExecutingAssembly().GetName().Name}.xml";
    var xmlPath = Path.Combine(AppContext.BaseDirectory, xmlFile);
    if (File.Exists(xmlPath))
    {
        c.IncludeXmlComments(xmlPath);
    }
});

// 添加CORS
builder.Services.AddCors(options =>
{
    options.AddDefaultPolicy(policy =>
    {
        policy.AllowAnyOrigin()
              .AllowAnyMethod()
              .AllowAnyHeader();
    });
});

// 注册应用服务(扩展方法)
builder.Services.AddAdminServices();
builder.Services.AddInfrastructureServices(builder.Configuration);

var app = builder.Build();

// 配置HTTP请求管道
if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI(c =>
    {
        c.SwaggerEndpoint("/swagger/v1/swagger.json", "BxPlatform Admin API V1");
        c.RoutePrefix = string.Empty; // Swagger UI在根路径
    });
}

// 启用CORS
app.UseCors();

// 启用路由
app.UseRouting();

// PHP风格路由中间件
app.UseMiddleware<PhpStyleRouteMiddleware>();

// 启用认证和授权
app.UseAuthentication();
app.UseAuthorization();

// 映射控制器
app.MapControllers();

// 启动提示
app.Logger.LogInformation("========================================");
app.Logger.LogInformation("BxPlatform Admin API 已启动");
app.Logger.LogInformation("Swagger文档: http://localhost:5100");
app.Logger.LogInformation("API路径: /api/?m=Admin&c=Controller&a=Action");
app.Logger.LogInformation("数据库: PostgreSQL 5分库");
app.Logger.LogInformation("缓存: Redis");
app.Logger.LogInformation("========================================");

app.Run();
