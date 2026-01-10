using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authorization;
using Common.Models;
using System.Security.Claims;

namespace AdminApi.Controllers;

/// <summary>
/// 管理端控制器基类
/// </summary>
[ApiController]
[Route("api/[controller]")]
[Authorize]
public abstract class BaseController : ControllerBase
{
    /// <summary>
    /// 获取当前登录管理员ID
    /// </summary>
    protected int CurrentUserId
    {
        get
        {
            var userIdClaim = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
            return int.TryParse(userIdClaim, out var userId) ? userId : 0;
        }
    }

    /// <summary>
    /// 获取当前登录管理员账号
    /// </summary>
    protected string CurrentUserAccount
    {
        get
        {
            return User.FindFirst(ClaimTypes.Name)?.Value ?? string.Empty;
        }
    }

    /// <summary>
    /// 获取当前管理员用户组ID
    /// </summary>
    protected int CurrentUserGroupId
    {
        get
        {
            var groupIdClaim = User.FindFirst("gid")?.Value;
            return int.TryParse(groupIdClaim, out var groupId) ? groupId : 0;
        }
    }

    /// <summary>
    /// 返回成功结果
    /// </summary>
    protected IActionResult Success(object? data = null, string message = "操作成功")
    {
        return Ok(ApiResult.Success(data, message));
    }

    /// <summary>
    /// 返回失败结果
    /// </summary>
    protected IActionResult Fail(string message, int code = 0)
    {
        return Ok(ApiResult.Error(code, message));
    }

    /// <summary>
    /// 返回分页结果
    /// </summary>
    protected IActionResult PagedSuccess<T>(PagedResult<T> pagedResult)
    {
        return Ok(ApiResult.Success(new
        {
            list = pagedResult.Items,
            total = pagedResult.Total,
            //page = pagedResult.Page,
            pageSize = pagedResult.PageSize,
            totalPages = pagedResult.TotalPages
        }));
    }

    /// <summary>
    /// 获取请求参数（兼容PHP风格，支持form和query）
    /// </summary>
    protected string GetParam(string key, string defaultValue = "")
    {
        // 优先从Form获取
        if (Request.HasFormContentType && Request.Form.ContainsKey(key))
        {
            return Request.Form[key].ToString();
        }

        // 其次从Query获取
        if (Request.Query.ContainsKey(key))
        {
            return Request.Query[key].ToString();
        }

        return defaultValue;
    }

    /// <summary>
    /// 获取整型参数
    /// </summary>
    protected int GetIntParam(string key, int defaultValue = 0)
    {
        var value = GetParam(key);
        return int.TryParse(value, out var result) ? result : defaultValue;
    }

    /// <summary>
    /// 获取长整型参数
    /// </summary>
    protected long GetLongParam(string key, long defaultValue = 0)
    {
        var value = GetParam(key);
        return long.TryParse(value, out var result) ? result : defaultValue;
    }

    /// <summary>
    /// 获取decimal参数
    /// </summary>
    protected decimal GetDecimalParam(string key, decimal defaultValue = 0)
    {
        var value = GetParam(key);
        return decimal.TryParse(value, out var result) ? result : defaultValue;
    }

    /// <summary>
    /// 获取布尔参数
    /// </summary>
    protected bool GetBoolParam(string key, bool defaultValue = false)
    {
        var value = GetParam(key);
        if (string.IsNullOrEmpty(value)) return defaultValue;

        // 支持多种格式: "1", "true", "yes", "on"
        return value == "1" ||
               value.Equals("true", StringComparison.OrdinalIgnoreCase) ||
               value.Equals("yes", StringComparison.OrdinalIgnoreCase) ||
               value.Equals("on", StringComparison.OrdinalIgnoreCase);
    }

    /// <summary>
    /// 获取分页参数
    /// </summary>
    protected (int page, int pageSize) GetPageParams()
    {
        var page = GetIntParam("page", 1);
        var pageSize = GetIntParam("s_sizes", 20);

        // 确保参数有效
        page = page < 1 ? 1 : page;
        pageSize = pageSize < 1 ? 20 : (pageSize > 100 ? 100 : pageSize);

        return (page, pageSize);
    }

    /// <summary>
    /// 获取搜索关键词
    /// </summary>
    protected string GetSearchKeyword()
    {
        return GetParam("s_keyword", "").Trim();
    }

    /// <summary>
    /// 获取开始时间
    /// </summary>
    protected DateTime? GetStartTime()
    {
        var value = GetParam("s_start_time");
        return DateTime.TryParse(value, out var result) ? result : null;
    }

    /// <summary>
    /// 获取结束时间
    /// </summary>
    protected DateTime? GetEndTime()
    {
        var value = GetParam("s_end_time");
        return DateTime.TryParse(value, out var result) ? result : null;
    }
}
