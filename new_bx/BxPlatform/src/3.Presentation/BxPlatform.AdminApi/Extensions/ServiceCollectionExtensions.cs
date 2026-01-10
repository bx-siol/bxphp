using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Configuration;
using BxPlatform.Application.Contracts.Users;
using BxPlatform.Application.Services.Users;
using BxPlatform.Domain.Interfaces;
using BxPlatform.Domain.Interfaces.Repositories;
using BxPlatform.Infrastructure.Repositories;
using BxPlatform.Infrastructure.Repositories.Base;
using BxPlatform.Infrastructure.Caching;
using BxPlatform.Infrastructure.DistributedLock;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.IdentityModel.Tokens;
using System.Text;

namespace BxPlatform.AdminApi.Extensions;

/// <summary>
/// 服务注册扩展类
/// </summary>
public static class ServiceCollectionExtensions
{
    /// <summary>
    /// 注册管理端应用服务
    /// </summary>
    public static IServiceCollection AddAdminServices(this IServiceCollection services)
    {
        // 注册应用服务
        services.AddScoped<IUserService, UserService>();
        
        return services;
    }

    /// <summary>
    /// 注册基础设施服务
    /// </summary>
    public static IServiceCollection AddInfrastructureServices(
        this IServiceCollection services, 
        IConfiguration configuration)
    {
        // 注册仓储
        services.AddScoped<IUserRepository, UserRepository>();
        services.AddScoped<IUnitOfWork, UnitOfWork>();
        
        // 注册缓存服务
        services.AddSingleton<ICacheService, RedisCacheService>();
        
        // 注册分布式锁
        services.AddSingleton<IDistributedLock, RedisDistributedLock>();
        
        // 注册JWT认证
        var jwtSettings = configuration.GetSection("JwtSettings");
        var secretKey = jwtSettings["SecretKey"] ?? throw new InvalidOperationException("JWT SecretKey not configured");
        
        services.AddAuthentication(options =>
        {
            options.DefaultAuthenticateScheme = JwtBearerDefaults.AuthenticationScheme;
            options.DefaultChallengeScheme = JwtBearerDefaults.AuthenticationScheme;
        })
        .AddJwtBearer(options =>
        {
            options.TokenValidationParameters = new TokenValidationParameters
            {
                ValidateIssuer = true,
                ValidateAudience = true,
                ValidateLifetime = true,
                ValidateIssuerSigningKey = true,
                ValidIssuer = jwtSettings["Issuer"],
                ValidAudience = jwtSettings["Audience"],
                IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(secretKey)),
                ClockSkew = TimeSpan.Zero
            };

            // 支持从query参数读取token (兼容PHP风格)
            options.Events = new JwtBearerEvents
            {
                OnMessageReceived = context =>
                {
                    // 优先从Header读取
                    var token = context.Request.Headers["Authorization"].FirstOrDefault()?.Split(" ").Last();
                    
                    // 其次从Query参数读取 (兼容PHP: ?token=xxx)
                    if (string.IsNullOrEmpty(token))
                    {
                        token = context.Request.Query["token"].FirstOrDefault();
                    }

                    // 最后从Form参数读取
                    if (string.IsNullOrEmpty(token) && context.Request.HasFormContentType)
                    {
                        token = context.Request.Form["token"].FirstOrDefault();
                    }

                    if (!string.IsNullOrEmpty(token))
                    {
                        context.Token = token;
                    }

                    return Task.CompletedTask;
                }
            };
        });

        services.AddAuthorization();
        
        return services;
    }
}
