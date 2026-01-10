using System.Threading.Tasks;
using BxPlatform.Application.Contracts.Users.Dtos;
using BxPlatform.Application.Contracts.Users.Requests;
using BxPlatform.Common.Models;

namespace BxPlatform.Application.Contracts.Users
{
    /// <summary>
    /// 用户服务接口
    /// </summary>
    public interface IUserService
    {
        /// <summary>
        /// 根据ID获取用户
        /// </summary>
        Task<UserDto?> GetUserByIdAsync(int userId);

        /// <summary>
        /// 根据账号获取用户
        /// </summary>
        Task<UserDto?> GetUserByAccountAsync(string account);

        /// <summary>
        /// 用户登录
        /// </summary>
        Task<LoginResult> LoginAsync(LoginRequest request);

        /// <summary>
        /// 用户注册
        /// </summary>
        Task<UserDto> RegisterAsync(RegisterRequest request);

        /// <summary>
        /// 更新用户信息
        /// </summary>
        Task<bool> UpdateUserAsync(UpdateUserRequest request);

        /// <summary>
        /// 修改密码
        /// </summary>
        Task<bool> ChangePasswordAsync(int userId, string oldPassword, string newPassword);

        /// <summary>
        /// 充值
        /// </summary>
        Task<bool> RechargeAsync(int userId, decimal amount, string remark);

        /// <summary>
        /// 扣款
        /// </summary>
        Task<bool> DeductAsync(int userId, decimal amount, string remark);

        /// <summary>
        /// 禁用用户
        /// </summary>
        Task<bool> DisableUserAsync(int userId);

        /// <summary>
        /// 启用用户
        /// </summary>
        Task<bool> EnableUserAsync(int userId);

        /// <summary>
        /// 删除用户(软删除)
        /// </summary>
        Task<bool> DeleteUserAsync(int userId);

        /// <summary>
        /// 获取用户列表(分页)
        /// </summary>
        Task<PagedResult<UserDto>> GetUserListAsync(UserQueryRequest request);

        /// <summary>
        /// 获取用户的下级列表
        /// </summary>
        Task<List<UserDto>> GetSubordinatesAsync(int userId, int level = 1);
    }
}
