using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using BxPlatform.Application.Contracts.Users;
using BxPlatform.Application.Contracts.Users.Dtos;
using BxPlatform.Application.Contracts.Users.Requests;
using BxPlatform.Common.Helpers;
using BxPlatform.Common.Models;
using BxPlatform.Domain.Entities.Users;
using BxPlatform.Domain.Enums;
using BxPlatform.Domain.Interfaces;
using BxPlatform.Domain.Interfaces.Repositories;
using BxPlatform.Infrastructure.Caching;
using BxPlatform.Infrastructure.DistributedLock;

namespace BxPlatform.Application.Services.Users
{
    /// <summary>
    /// 用户服务实现
    /// 处理用户相关的业务逻辑
    /// </summary>
    public class UserService : IUserService
    {
        private readonly IUserRepository _userRepository;
        private readonly IUnitOfWork _unitOfWork;
        private readonly ICacheService _cacheService;
        private readonly IDistributedLock _distributedLock;

        /// <summary>
        /// 构造函数
        /// </summary>
        public UserService(
            IUserRepository userRepository,
            IUnitOfWork unitOfWork,
            ICacheService cacheService,
            IDistributedLock distributedLock)
        {
            _userRepository = userRepository;
            _unitOfWork = unitOfWork;
            _cacheService = cacheService;
            _distributedLock = distributedLock;
        }

        /// <summary>
        /// 根据ID获取用户(带缓存)
        /// </summary>
        public async Task<UserDto?> GetUserByIdAsync(int userId)
        {
            // 从缓存获取
            var cacheKey = $"user:{userId}";
            return await _cacheService.GetOrSetAsync(cacheKey, async () =>
            {
                var user = await _userRepository.GetByIdAsync(userId);
                return user != null ? MapToDto(user) : null;
            }, TimeSpan.FromMinutes(30));
        }

        /// <summary>
        /// 根据账号获取用户
        /// </summary>
        public async Task<UserDto?> GetUserByAccountAsync(string account)
        {
            var user = await _userRepository.GetByAccountAsync(account);
            return user != null ? MapToDto(user) : null;
        }

        /// <summary>
        /// 用户登录
        /// </summary>
        public async Task<LoginResult> LoginAsync(LoginRequest request)
        {
            // 查找用户
            var user = await _userRepository.GetByAccountAsync(request.Account);
            if (user == null)
                throw new Exception("账号不存在");

            // 验证密码
            if (!EncryptionHelper.VerifyPassword(request.Password, user.Password))
                throw new Exception("密码错误");

            // 检查用户状态
            if (user.Status == UserStatus.Disabled)
                throw new Exception("账号已被禁用");

            if (user.Status == UserStatus.Deleted)
                throw new Exception("账号不存在");

            // 记录登录
            user.RecordLogin(request.LoginIp ?? "");
            await _userRepository.UpdateAsync(user);

            // 清除缓存
            await _cacheService.RemoveAsync($"user:{user.Id}");

            // 生成令牌(这里简化处理,实际应该使用JWT)
            var token = GenerateToken(user);

            return new LoginResult
            {
                User = MapToDto(user),
                Token = token,
                ExpiresIn = 7200 // 2小时
            };
        }

        /// <summary>
        /// 用户注册
        /// </summary>
        public async Task<UserDto> RegisterAsync(RegisterRequest request)
        {
            // 检查账号是否存在
            if (await _userRepository.IsAccountExistsAsync(request.Account))
                throw new Exception("账号已存在");

            // 处理邀请码
            int parentId = 0;
            if (!string.IsNullOrWhiteSpace(request.InviteCode))
            {
                var parent = await _userRepository.GetByInviteCodeAsync(request.InviteCode);
                if (parent != null)
                {
                    parentId = parent.Id;
                }
            }

            // 创建用户
            var password = EncryptionHelper.GetPassword(request.Password, true);
            var user = new SysUser(request.Account, password, request.Nickname, parentId);
            
            if (!string.IsNullOrWhiteSpace(request.Phone))
            {
                user.UpdateProfile(phone: request.Phone);
            }

            if (!string.IsNullOrWhiteSpace(request.RegisterIp))
            {
                user.SetRegisterIp(request.RegisterIp);
            }

            // 生成邀请码
            // 注意: 需要先保存以获取ID,然后更新邀请码

            using (var uow = _unitOfWork)
            {
                await uow.BeginTransactionAsync();
                try
                {
                    // 添加用户
                    user = await _userRepository.AddAsync(user);

                    // 如果有上级,更新上级的团队人数
                    if (parentId > 0)
                    {
                        var parent = await _userRepository.GetByIdAsync(parentId);
                        if (parent != null)
                        {
                            parent.IncrementTeamCount();
                            await _userRepository.UpdateAsync(parent);
                        }
                    }

                    await uow.CommitAsync();
                }
                catch
                {
                    await uow.RollbackAsync();
                    throw;
                }
            }

            return MapToDto(user);
        }

        /// <summary>
        /// 更新用户信息
        /// </summary>
        public async Task<bool> UpdateUserAsync(UpdateUserRequest request)
        {
            var user = await _userRepository.GetByIdAsync(request.UserId);
            if (user == null)
                throw new Exception("用户不存在");

            user.UpdateProfile(
                request.Nickname,
                request.Realname,
                request.Phone,
                request.BankCard);

            var result = await _userRepository.UpdateAsync(user);

            // 清除缓存
            if (result)
            {
                await _cacheService.RemoveAsync($"user:{request.UserId}");
            }

            return result;
        }

        /// <summary>
        /// 修改密码
        /// </summary>
        public async Task<bool> ChangePasswordAsync(int userId, string oldPassword, string newPassword)
        {
            var user = await _userRepository.GetByIdAsync(userId);
            if (user == null)
                throw new Exception("用户不存在");

            // 验证旧密码
            if (!EncryptionHelper.VerifyPassword(oldPassword, user.Password))
                throw new Exception("原密码错误");

            // 修改密码
            var encryptedPassword = EncryptionHelper.GetPassword(newPassword, true);
            user.ChangePassword(encryptedPassword);

            var result = await _userRepository.UpdateAsync(user);

            // 清除缓存
            if (result)
            {
                await _cacheService.RemoveAsync($"user:{userId}");
            }

            return result;
        }

        /// <summary>
        /// 充值(带分布式锁)
        /// </summary>
        public async Task<bool> RechargeAsync(int userId, decimal amount, string remark)
        {
            var lockKey = $"user:balance:{userId}";
            
            return await _distributedLock.ExecuteWithLockAsync(lockKey, async () =>
            {
                using (var uow = _unitOfWork)
                {
                    await uow.BeginTransactionAsync();
                    try
                    {
                        var user = await _userRepository.GetByIdAsync(userId);
                        if (user == null)
                            throw new Exception("用户不存在");

                        user.Recharge(amount);
                        await _userRepository.UpdateAsync(user);

                        // TODO: 记录充值日志到 fin_paylog

                        await uow.CommitAsync();

                        // 清除缓存
                        await _cacheService.RemoveAsync($"user:{userId}");

                        return true;
                    }
                    catch
                    {
                        await uow.RollbackAsync();
                        throw;
                    }
                }
            }, TimeSpan.FromSeconds(10));
        }

        /// <summary>
        /// 扣款(带分布式锁)
        /// </summary>
        public async Task<bool> DeductAsync(int userId, decimal amount, string remark)
        {
            var lockKey = $"user:balance:{userId}";
            
            return await _distributedLock.ExecuteWithLockAsync(lockKey, async () =>
            {
                using (var uow = _unitOfWork)
                {
                    await uow.BeginTransactionAsync();
                    try
                    {
                        var user = await _userRepository.GetByIdAsync(userId);
                        if (user == null)
                            throw new Exception("用户不存在");

                        user.Deduct(amount);
                        await _userRepository.UpdateAsync(user);

                        // TODO: 记录扣款日志

                        await uow.CommitAsync();

                        // 清除缓存
                        await _cacheService.RemoveAsync($"user:{userId}");

                        return true;
                    }
                    catch
                    {
                        await uow.RollbackAsync();
                        throw;
                    }
                }
            }, TimeSpan.FromSeconds(10));
        }

        /// <summary>
        /// 禁用用户
        /// </summary>
        public async Task<bool> DisableUserAsync(int userId)
        {
            var user = await _userRepository.GetByIdAsync(userId);
            if (user == null)
                throw new Exception("用户不存在");

            user.Disable();
            var result = await _userRepository.UpdateAsync(user);

            if (result)
            {
                await _cacheService.RemoveAsync($"user:{userId}");
            }

            return result;
        }

        /// <summary>
        /// 启用用户
        /// </summary>
        public async Task<bool> EnableUserAsync(int userId)
        {
            var user = await _userRepository.GetByIdAsync(userId);
            if (user == null)
                throw new Exception("用户不存在");

            user.Enable();
            var result = await _userRepository.UpdateAsync(user);

            if (result)
            {
                await _cacheService.RemoveAsync($"user:{userId}");
            }

            return result;
        }

        /// <summary>
        /// 删除用户(软删除)
        /// </summary>
        public async Task<bool> DeleteUserAsync(int userId)
        {
            if (userId == 1)
                throw new Exception("超级管理员不能删除");

            var user = await _userRepository.GetByIdAsync(userId);
            if (user == null)
                throw new Exception("用户不存在");

            user.MarkAsDeleted();
            var result = await _userRepository.UpdateAsync(user);

            if (result)
            {
                await _cacheService.RemoveAsync($"user:{userId}");
            }

            return result;
        }

        /// <summary>
        /// 获取用户列表(分页)
        /// </summary>
        public async Task<PagedResult<UserDto>> GetUserListAsync(UserQueryRequest request)
        {
            // 构建查询条件
            // 注意: 这里简化处理,实际应该在仓储层实现更复杂的查询

            var (items, total) = await _userRepository.GetPagedListAsync(
                u => u.Status != UserStatus.Deleted,
                request.PageIndex,
                request.PageSize,
                u => u.Id,
                false);

            var dtos = items.Select(MapToDto).ToList();

            return new PagedResult<UserDto>(dtos, total, request.PageIndex, request.PageSize);
        }

        /// <summary>
        /// 获取用户的下级列表
        /// </summary>
        public async Task<List<UserDto>> GetSubordinatesAsync(int userId, int level = 1)
        {
            var subordinates = await _userRepository.GetSubordinatesAsync(userId, level);
            return subordinates.Select(MapToDto).ToList();
        }

        #region 私有方法

        /// <summary>
        /// 实体映射到DTO
        /// </summary>
        private UserDto MapToDto(SysUser user)
        {
            return new UserDto
            {
                Id = user.Id,
                Account = user.Account,
                Nickname = user.Nickname,
                Realname = user.Realname,
                Phone = user.Phone,
                Balance = user.Balance,
                FrozenBalance = user.FrozenBalance,
                Status = (int)user.Status,
                GroupId = user.GroupId,
                ParentId = user.ParentIdLevel1,
                TeamCount = user.TeamCount,
                LoginIp = user.LoginIp,
                LoginTime = user.LoginTime,
                CreateTime = user.CreateTime,
                InviteCode = user.InviteCode,
                TotalInvest = user.TotalInvest
            };
        }

        /// <summary>
        /// 生成令牌(简化版,实际应使用JWT)
        /// </summary>
        private string GenerateToken(SysUser user)
        {
            return Guid.NewGuid().ToString("N");
        }

        #endregion
    }
}
