using Microsoft.AspNetCore.Mvc;
using BxPlatform.Application.Contracts.Users;
using BxPlatform.Application.Contracts.Users.Requests;
using BxPlatform.Common.Models;
using System.Threading.Tasks;

namespace BxPlatform.Api.Controllers.V1.Users
{
    /// <summary>
    /// 用户控制器
    /// API版本: v1
    /// 路由前缀: /api/v1/users
    /// </summary>
    [ApiController]
    [Route("api/v1/[controller]")]
    public class UsersController : ControllerBase
    {
        private readonly IUserService _userService;

        /// <summary>
        /// 构造函数
        /// </summary>
        public UsersController(IUserService userService)
        {
            _userService = userService;
        }

        /// <summary>
        /// 用户登录
        /// POST /api/v1/users/login
        /// </summary>
        [HttpPost("login")]
        public async Task<IActionResult> Login([FromBody] LoginRequest request)
        {
            try
            {
                request.LoginIp = HttpContext.Connection.RemoteIpAddress?.ToString();
                var result = await _userService.LoginAsync(request);
                return Ok(ApiResult.Success(result, "登录成功"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 用户注册
        /// POST /api/v1/users/register
        /// </summary>
        [HttpPost("register")]
        public async Task<IActionResult> Register([FromBody] RegisterRequest request)
        {
            try
            {
                request.RegisterIp = HttpContext.Connection.RemoteIpAddress?.ToString();
                var user = await _userService.RegisterAsync(request);
                return Ok(ApiResult.Success(user, "注册成功"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 获取用户信息
        /// GET /api/v1/users/{id}
        /// </summary>
        [HttpGet("{id}")]
        public async Task<IActionResult> GetUser(int id)
        {
            try
            {
                var user = await _userService.GetUserByIdAsync(id);
                if (user == null)
                    return Ok(ApiResult.Error("用户不存在"));

                return Ok(ApiResult.Success(user));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 更新用户信息
        /// PUT /api/v1/users
        /// </summary>
        [HttpPut]
        public async Task<IActionResult> UpdateUser([FromBody] UpdateUserRequest request)
        {
            try
            {
                var result = await _userService.UpdateUserAsync(request);
                return Ok(result 
                    ? ApiResult.Success("更新成功")
                    : ApiResult.Error("更新失败"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 修改密码
        /// POST /api/v1/users/{id}/change-password
        /// </summary>
        [HttpPost("{id}/change-password")]
        public async Task<IActionResult> ChangePassword(
            int id,
            [FromBody] ChangePasswordRequest request)
        {
            try
            {
                var result = await _userService.ChangePasswordAsync(
                    id, 
                    request.OldPassword, 
                    request.NewPassword);

                return Ok(result
                    ? ApiResult.Success("修改密码成功")
                    : ApiResult.Error("修改密码失败"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 充值
        /// POST /api/v1/users/{id}/recharge
        /// </summary>
        [HttpPost("{id}/recharge")]
        public async Task<IActionResult> Recharge(
            int id,
            [FromBody] RechargeRequest request)
        {
            try
            {
                var result = await _userService.RechargeAsync(
                    id,
                    request.Amount,
                    request.Remark);

                return Ok(result
                    ? ApiResult.Success("充值成功")
                    : ApiResult.Error("充值失败"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 扣款
        /// POST /api/v1/users/{id}/deduct
        /// </summary>
        [HttpPost("{id}/deduct")]
        public async Task<IActionResult> Deduct(
            int id,
            [FromBody] DeductRequest request)
        {
            try
            {
                var result = await _userService.DeductAsync(
                    id,
                    request.Amount,
                    request.Remark);

                return Ok(result
                    ? ApiResult.Success("扣款成功")
                    : ApiResult.Error("扣款失败"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 获取用户列表(分页)
        /// GET /api/v1/users
        /// </summary>
        [HttpGet]
        public async Task<IActionResult> GetUserList([FromQuery] UserQueryRequest request)
        {
            try
            {
                var result = await _userService.GetUserListAsync(request);
                return Ok(ApiResult.Success(result));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 获取用户的下级列表
        /// GET /api/v1/users/{id}/subordinates
        /// </summary>
        [HttpGet("{id}/subordinates")]
        public async Task<IActionResult> GetSubordinates(int id, [FromQuery] int level = 1)
        {
            try
            {
                var subordinates = await _userService.GetSubordinatesAsync(id, level);
                return Ok(ApiResult.Success(subordinates));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 禁用用户
        /// POST /api/v1/users/{id}/disable
        /// </summary>
        [HttpPost("{id}/disable")]
        public async Task<IActionResult> DisableUser(int id)
        {
            try
            {
                var result = await _userService.DisableUserAsync(id);
                return Ok(result
                    ? ApiResult.Success("禁用成功")
                    : ApiResult.Error("禁用失败"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 启用用户
        /// POST /api/v1/users/{id}/enable
        /// </summary>
        [HttpPost("{id}/enable")]
        public async Task<IActionResult> EnableUser(int id)
        {
            try
            {
                var result = await _userService.EnableUserAsync(id);
                return Ok(result
                    ? ApiResult.Success("启用成功")
                    : ApiResult.Error("启用失败"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }

        /// <summary>
        /// 删除用户(软删除)
        /// DELETE /api/v1/users/{id}
        /// </summary>
        [HttpDelete("{id}")]
        public async Task<IActionResult> DeleteUser(int id)
        {
            try
            {
                var result = await _userService.DeleteUserAsync(id);
                return Ok(result
                    ? ApiResult.Success("删除成功")
                    : ApiResult.Error("删除失败"));
            }
            catch (Exception ex)
            {
                return Ok(ApiResult.Error(ex.Message));
            }
        }
    }

    /// <summary>
    /// 修改密码请求
    /// </summary>
    public class ChangePasswordRequest
    {
        public string OldPassword { get; set; } = string.Empty;
        public string NewPassword { get; set; } = string.Empty;
    }

    /// <summary>
    /// 充值请求
    /// </summary>
    public class RechargeRequest
    {
        public decimal Amount { get; set; }
        public string Remark { get; set; } = string.Empty;
    }

    /// <summary>
    /// 扣款请求
    /// </summary>
    public class DeductRequest
    {
        public decimal Amount { get; set; }
        public string Remark { get; set; } = string.Empty;
    }
}
