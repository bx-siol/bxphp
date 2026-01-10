using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authorization;

namespace  AdminApi.Controllers;

/// <summary>
/// 管理端-基础接口控制器
/// </summary>
[ApiController]
[Route("api/[controller]")]
public class AdminController : BaseController
{
    /// <summary>
    /// 获取系统配置
    /// POST /api/Admin/GetConfig 或 /api/?m=Admin&a=getConfig
    /// </summary>
    [HttpPost("GetConfig")]
    [HttpGet("GetConfig")]
    [AllowAnonymous]
    public IActionResult GetConfig()
    {
        try
        {
            // TODO: 实现获取系统配置
            var data = new
            {
                site_name = "BxPlatform 管理后台",
                version = "1.0.0",
                // ... 其他配置信息
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取系统配置失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 管理员登录
    /// POST /api/Admin/Login 或 /api/?m=Admin&a=login
    /// </summary>
    [HttpPost("Login")]
    [AllowAnonymous]
    public IActionResult Login()
    {
        try
        {
            var account = GetParam("account");
            var password = GetParam("password");
            var vcode = GetParam("vcode");

            if (string.IsNullOrEmpty(account))
            {
                return Fail("请输入账号");
            }

            if (string.IsNullOrEmpty(password))
            {
                return Fail("请输入密码");
            }

            // TODO: 实现登录逻辑
            // 1. 验证账号密码
            // 2. 验证验证码
            // 3. 生成JWT Token
            // 4. 记录登录日志

            var data = new
            {
                token = "sample_token_here",
                user = new
                {
                    id = 1,
                    account = account,
                    nickname = "管理员",
                    gid = 1
                }
            };

            return Success(data, "登录成功");
        }
        catch (Exception ex)
        {
            return Fail($"登录失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 管理员登出
    /// POST /api/Admin/Logout 或 /api/?m=Admin&a=logout
    /// </summary>
    [HttpPost("Logout")]
    public IActionResult Logout()
    {
        try
        {
            // TODO: 实现登出逻辑
            // 1. 删除当前用户的token
            // 2. 清理session

            return Success(null, "登出成功");
        }
        catch (Exception ex)
        {
            return Fail($"登出失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 获取当前登录用户信息
    /// POST /api/Admin/Userinfo 或 /api/?m=Admin&a=userinfo
    /// </summary>
    [HttpPost("Userinfo")]
    [HttpGet("Userinfo")]
    public IActionResult Userinfo()
    {
        try
        {
            // TODO: 实现获取用户信息
            var data = new
            {
                id = CurrentUserId,
                account = CurrentUserAccount,
                gid = CurrentUserGroupId,
                nickname = "",
                avatar = ""
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取用户信息失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 清除缓存
    /// POST /api/Admin/ClearCache 或 /api/?m=Admin&a=clearCache
    /// </summary>
    [HttpPost("ClearCache")]
    public IActionResult ClearCache()
    {
        try
        {
            // TODO: 实现缓存清理逻辑
            // 1. 如果是超级管理员(gid=1)，清理所有缓存
            // 2. 否则只清理当前用户的节点缓存

            return Success(null, "缓存清理成功");
        }
        catch (Exception ex)
        {
            return Fail($"清理缓存失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 获取图形验证码
    /// POST /api/Admin/GetVcode 或 /api/?m=Admin&a=getVcode
    /// </summary>
    [HttpPost("GetVcode")]
    [HttpGet("GetVcode")]
    [AllowAnonymous]
    public IActionResult GetVcode()
    {
        try
        {
            // TODO: 实现图形验证码生成
            var data = new
            {
                vcode_key = Guid.NewGuid().ToString("N"),
                vcode_img = "data:image/png;base64,..."  // base64图片
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取验证码失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 动态修改数据表字段值
    /// POST /api/Admin/ChangeTableVal 或 /api/?m=Admin&a=changeTableVal
    /// </summary>
    [HttpPost("ChangeTableVal")]
    public IActionResult ChangeTableVal()
    {
        try
        {
            var table = GetParam("table");
            var idName = GetParam("id_name");
            var idValue = GetParam("id_value");
            var field = GetParam("field");
            var value = GetParam("value");

            if (string.IsNullOrEmpty(table) || string.IsNullOrEmpty(idName) ||
                string.IsNullOrEmpty(idValue) || string.IsNullOrEmpty(field))
            {
                return Fail("参数不完整");
            }

            // TODO: 实现动态修改表字段值
            // 注意：需要严格校验table和field，防止SQL注入

            return Success(null, "操作成功");
        }
        catch (Exception ex)
        {
            return Fail($"操作失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 获取PC信息
    /// POST /api/Admin/GetPc 或 /api/?m=Admin&a=getPc
    /// </summary>
    [HttpPost("GetPc")]
    [HttpGet("GetPc")]
    public IActionResult GetPc()
    {
        try
        {
            // TODO: 实现获取PC信息
            var data = new
            {
                // PC相关信息
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取PC信息失败: {ex.Message}");
        }
    }
}
