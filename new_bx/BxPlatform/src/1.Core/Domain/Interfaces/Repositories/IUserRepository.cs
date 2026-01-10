using  Domain.Entities.Users;

namespace  Domain.Interfaces.Repositories
{
    /// <summary>
    /// 用户仓储接口
    /// 提供用户特定的查询方法
    /// </summary>
    public interface IUserRepository : IRepository<SysUser>
    {
        /// <summary>
        /// 根据账号获取用户
        /// </summary>
        Task<SysUser?> GetByAccountAsync(string account);

        /// <summary>
        /// 根据手机号获取用户
        /// </summary>
        Task<SysUser?> GetByPhoneAsync(string phone);

        /// <summary>
        /// 根据邀请码获取用户
        /// </summary>
        Task<SysUser?> GetByInviteCodeAsync(string inviteCode);

        /// <summary>
        /// 检查账号是否存在
        /// </summary>
        Task<bool> IsAccountExistsAsync(string account);

        /// <summary>
        /// 获取用户的下级列表
        /// </summary>
        Task<List<SysUser>> GetSubordinatesAsync(int parentId, int level = 1);

        /// <summary>
        /// 获取用户团队人数
        /// </summary>
        Task<int> GetTeamCountAsync(int userId);

        /// <summary>
        /// 批量更新用户状态
        /// </summary>
        Task<int> BatchUpdateStatusAsync(List<int> userIds, int status);
    }
}
