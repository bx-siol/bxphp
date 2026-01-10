using Microsoft.AspNetCore.Http;
using System.Threading.Tasks;

namespace new_bx.Api.Middleware
{
    /// <summary>
    /// PHP风格路由中间件
    /// 将 /api/?m=Admin&c=User&a=user_update 转换为 MVC路由
    /// 其中：m=模块名，c=控制器名，a=方法名
    /// </summary>
    public class PhpStyleRouteMiddleware
    {
        private readonly RequestDelegate _next;

        public PhpStyleRouteMiddleware(RequestDelegate next)
        {
            _next = next;
        }

        /// <summary>
        /// 处理请求，转换PHP风格路由为标准路由
        /// </summary>
        public async Task InvokeAsync(HttpContext context)
        {
            var request = context.Request;

            // 检查是否是 /api/ 路径
            if (request.Path.StartsWithSegments("/api"))
            {
                // 尝试从查询参数获取 m、c、a
                string? module = request.Query["m"].ToString();
                string? controller = request.Query["c"].ToString();
                string? action = request.Query["a"].ToString();

                // 如果存在这些参数，修改路径和移除查询参数
                if (!string.IsNullOrEmpty(controller) && !string.IsNullOrEmpty(action))
                {
                    // 构建新的路径
                    // module可选，用于后续扩展多模块支持
                    string newPath;
                    if (!string.IsNullOrEmpty(module) && module != "Admin")
                    {
                        newPath = $"/api/{module}/{controller}/{action}";
                    }
                    else
                    {
                        newPath = $"/api/{controller}/{action}";
                    }

                    // 更新请求路径
                    context.Request.Path = newPath;

                    // 移除m、c、a参数，保留其他参数
                    var queryParams = Microsoft.AspNetCore.WebUtilities.QueryHelpers.ParseQuery(request.QueryString.Value);
                    var newQuery = new Dictionary<string, Microsoft.Extensions.Primitives.StringValues>();

                    foreach (var param in queryParams)
                    {
                        if (param.Key != "m" && param.Key != "c" && param.Key != "a")
                        {
                            newQuery[param.Key] = param.Value;
                        }
                    }

                    // 重建查询字符串
                    if (newQuery.Count > 0)
                    {
                        var queryString = Microsoft.AspNetCore.WebUtilities.QueryHelpers.AddQueryString("", newQuery);
                        context.Request.QueryString = new QueryString(queryString);
                    }
                    else
                    {
                        context.Request.QueryString = QueryString.Empty;
                    }
                }
            }

            await _next(context);
        }
    }

    /// <summary>
    /// 中间件扩展方法
    /// </summary>
    public static class PhpStyleRouteMiddlewareExtensions
    {
        /// <summary>
        /// 使用PHP风格路由中间件
        /// </summary>
        public static IApplicationBuilder UsePhpStyleRoute(this IApplicationBuilder builder)
        {
            return builder.UseMiddleware<PhpStyleRouteMiddleware>();
        }
    }
}
