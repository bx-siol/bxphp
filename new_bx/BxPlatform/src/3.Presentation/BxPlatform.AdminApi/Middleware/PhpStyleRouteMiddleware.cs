using Microsoft.AspNetCore.Http;
using System.Threading.Tasks;

namespace BxPlatform.AdminApi.Middleware;

/// <summary>
/// PHP风格路由中间件
/// 将 /api/?m=Admin&c=User&a=statistics 转换为 /api/Admin/User/statistics
/// </summary>
public class PhpStyleRouteMiddleware
{
    private readonly RequestDelegate _next;

    public PhpStyleRouteMiddleware(RequestDelegate next)
    {
        _next = next;
    }

    public async Task InvokeAsync(HttpContext context)
    {
        var path = context.Request.Path.Value;
        
        // 只处理 /api/ 开头的请求
        if (path != null && path.StartsWith("/api/", StringComparison.OrdinalIgnoreCase))
        {
            var query = context.Request.Query;
            
            // 检查是否有 m, c, a 参数
            if (query.ContainsKey("m") || query.ContainsKey("c") || query.ContainsKey("a"))
            {
                var module = query["m"].FirstOrDefault() ?? "Admin";
                var controller = query["c"].FirstOrDefault() ?? "Default";
                var action = query["a"].FirstOrDefault() ?? "Index";
                
                // 构建新路径: /api/Admin/User/statistics
                var newPath = $"/api/{module}/{controller}/{action}";
                context.Request.Path = newPath;
            }
        }
        
        await _next(context);
    }
}
