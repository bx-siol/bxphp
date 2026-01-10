using System;
using System.Threading.Tasks;

namespace  Infrastructure.Caching
{
    /// <summary>
    /// 缓存服务接口
    /// </summary>
    public interface ICacheService
    {
        /// <summary>
        /// 获取缓存
        /// </summary>
        Task<T?> GetAsync<T>(string key);

        /// <summary>
        /// 设置缓存
        /// </summary>
        Task<bool> SetAsync<T>(string key, T value, TimeSpan? expiry = null);

        /// <summary>
        /// 删除缓存
        /// </summary>
        Task<bool> RemoveAsync(string key);

        /// <summary>
        /// 判断键是否存在
        /// </summary>
        Task<bool> ExistsAsync(string key);

        /// <summary>
        /// 获取或设置缓存(缓存穿透保护)
        /// </summary>
        Task<T> GetOrSetAsync<T>(string key, Func<Task<T>> factory, TimeSpan? expiry = null);

        /// <summary>
        /// 批量删除(支持通配符)
        /// </summary>
        Task<long> RemoveByPatternAsync(string pattern);

        /// <summary>
        /// 设置缓存(如果不存在)
        /// </summary>
        Task<bool> SetIfNotExistsAsync<T>(string key, T value, TimeSpan? expiry = null);
    }
}
