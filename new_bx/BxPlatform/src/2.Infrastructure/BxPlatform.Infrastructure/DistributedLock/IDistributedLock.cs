using System;
using System.Threading.Tasks;

namespace BxPlatform.Infrastructure.DistributedLock
{
    /// <summary>
    /// 分布式锁接口
    /// 用于分布式环境下的并发控制
    /// </summary>
    public interface IDistributedLock
    {
        /// <summary>
        /// 尝试获取锁
        /// </summary>
        /// <param name="key">锁的键名</param>
        /// <param name="expiry">锁的过期时间</param>
        /// <param name="timeout">获取锁的超时时间</param>
        /// <returns>是否成功获取锁</returns>
        Task<bool> AcquireLockAsync(string key, TimeSpan expiry, TimeSpan? timeout = null);

        /// <summary>
        /// 释放锁
        /// </summary>
        /// <param name="key">锁的键名</param>
        /// <returns>是否成功释放</returns>
        Task<bool> ReleaseLockAsync(string key);

        /// <summary>
        /// 执行带锁的操作
        /// </summary>
        /// <typeparam name="T">返回值类型</typeparam>
        /// <param name="key">锁的键名</param>
        /// <param name="action">要执行的操作</param>
        /// <param name="expiry">锁的过期时间</param>
        /// <param name="timeout">获取锁的超时时间</param>
        /// <returns>操作结果</returns>
        Task<T> ExecuteWithLockAsync<T>(
            string key,
            Func<Task<T>> action,
            TimeSpan expiry,
            TimeSpan? timeout = null);

        /// <summary>
        /// 执行带锁的操作(无返回值)
        /// </summary>
        Task ExecuteWithLockAsync(
            string key,
            Func<Task> action,
            TimeSpan expiry,
            TimeSpan? timeout = null);
    }
}
