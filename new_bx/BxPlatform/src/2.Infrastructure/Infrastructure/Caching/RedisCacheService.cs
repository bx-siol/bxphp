using System;
using System.Text.Json;
using System.Threading.Tasks;
using StackExchange.Redis;

namespace  Infrastructure.Caching
{
    /// <summary>
    /// Redis缓存服务实现
    /// </summary>
    public class RedisCacheService : ICacheService
    {
        private readonly IDatabase _redisDb;
        private readonly IServer _server;
        private readonly JsonSerializerOptions _jsonOptions;

        /// <summary>
        /// 构造函数
        /// </summary>
        public RedisCacheService(IConnectionMultiplexer redis)
        {
            _redisDb = redis.GetDatabase();
            _server = redis.GetServer(redis.GetEndPoints()[0]);
            
            _jsonOptions = new JsonSerializerOptions
            {
                PropertyNamingPolicy = null, // 保持原样
                WriteIndented = false
            };
        }

        /// <summary>
        /// 获取缓存
        /// </summary>
        public async Task<T?> GetAsync<T>(string key)
        {
            var value = await _redisDb.StringGetAsync(key);
            if (!value.HasValue)
                return default;

            // 明确指定使用 string 重载，避免二义性
            return JsonSerializer.Deserialize<T>(value.ToString(), _jsonOptions);
        }

        /// <summary>
        /// 设置缓存
        /// </summary>
        public async Task<bool> SetAsync<T>(string key, T value, TimeSpan? expiry = null)
        {
            var json = JsonSerializer.Serialize(value, _jsonOptions);
            return await _redisDb.StringSetAsync(key, json, expiry);
        }

        /// <summary>
        /// 删除缓存
        /// </summary>
        public async Task<bool> RemoveAsync(string key)
        {
            return await _redisDb.KeyDeleteAsync(key);
        }

        /// <summary>
        /// 判断键是否存在
        /// </summary>
        public async Task<bool> ExistsAsync(string key)
        {
            return await _redisDb.KeyExistsAsync(key);
        }

        /// <summary>
        /// 获取或设置缓存(缓存穿透保护)
        /// </summary>
        public async Task<T> GetOrSetAsync<T>(string key, Func<Task<T>> factory, TimeSpan? expiry = null)
        {
            // 尝试从缓存获取
            var cached = await GetAsync<T>(key);
            if (cached != null)
                return cached;

            // 缓存未命中,执行工厂方法
            var value = await factory();
            
            // 设置缓存
            if (value != null)
            {
                await SetAsync(key, value, expiry ?? TimeSpan.FromMinutes(30));
            }

            return value;
        }

        /// <summary>
        /// 批量删除(支持通配符)
        /// </summary>
        public async Task<long> RemoveByPatternAsync(string pattern)
        {
            var keys = _server.Keys(pattern: pattern);
            long count = 0;

            foreach (var key in keys)
            {
                if (await _redisDb.KeyDeleteAsync(key))
                    count++;
            }

            return count;
        }

        /// <summary>
        /// 设置缓存(如果不存在)
        /// </summary>
        public async Task<bool> SetIfNotExistsAsync<T>(string key, T value, TimeSpan? expiry = null)
        {
            var json = JsonSerializer.Serialize(value, _jsonOptions);
            return await _redisDb.StringSetAsync(key, json, expiry, When.NotExists);
        }
    }
}
