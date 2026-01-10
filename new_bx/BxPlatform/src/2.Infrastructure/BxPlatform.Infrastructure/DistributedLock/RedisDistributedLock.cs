using System;
using System.Threading.Tasks;
using StackExchange.Redis;

namespace BxPlatform.Infrastructure.DistributedLock
{
    /// <summary>
    /// Redis分布式锁实现
    /// 基于Redis SET NX EX命令实现
    /// </summary>
    public class RedisDistributedLock : IDistributedLock
    {
        private readonly IDatabase _redisDb;
        private readonly string _lockValue;

        /// <summary>
        /// 构造函数
        /// </summary>
        public RedisDistributedLock(IConnectionMultiplexer redis)
        {
            _redisDb = redis.GetDatabase();
            _lockValue = Guid.NewGuid().ToString("N"); // 唯一值,防止误释放
        }

        /// <summary>
        /// 尝试获取锁
        /// </summary>
        public async Task<bool> AcquireLockAsync(string key, TimeSpan expiry, TimeSpan? timeout = null)
        {
            if (string.IsNullOrWhiteSpace(key))
                throw new ArgumentNullException(nameof(key));

            var lockKey = $"lock:{key}";
            var endTime = DateTime.UtcNow.Add(timeout ?? TimeSpan.FromSeconds(5));

            // 循环尝试获取锁,直到超时
            while (DateTime.UtcNow < endTime)
            {
                // SET key value NX EX seconds
                var acquired = await _redisDb.StringSetAsync(
                    lockKey,
                    _lockValue,
                    expiry,
                    When.NotExists);

                if (acquired)
                    return true;

                // 等待一小段时间后重试
                await Task.Delay(50);
            }

            return false;
        }

        /// <summary>
        /// 释放锁
        /// </summary>
        public async Task<bool> ReleaseLockAsync(string key)
        {
            if (string.IsNullOrWhiteSpace(key))
                throw new ArgumentNullException(nameof(key));

            var lockKey = $"lock:{key}";

            // 使用Lua脚本保证原子性: 只有持有锁的进程才能释放
            const string script = @"
                if redis.call('get', KEYS[1]) == ARGV[1] then
                    return redis.call('del', KEYS[1])
                else
                    return 0
                end";

            var result = await _redisDb.ScriptEvaluateAsync(
                script,
                new RedisKey[] { lockKey },
                new RedisValue[] { _lockValue });

            return (int)result == 1;
        }

        /// <summary>
        /// 执行带锁的操作(有返回值)
        /// </summary>
        public async Task<T> ExecuteWithLockAsync<T>(
            string key,
            Func<Task<T>> action,
            TimeSpan expiry,
            TimeSpan? timeout = null)
        {
            if (action == null)
                throw new ArgumentNullException(nameof(action));

            // 获取锁
            var acquired = await AcquireLockAsync(key, expiry, timeout);
            if (!acquired)
                throw new TimeoutException($"无法在超时时间内获取锁: {key}");

            try
            {
                // 执行业务操作
                return await action();
            }
            finally
            {
                // 释放锁
                await ReleaseLockAsync(key);
            }
        }

        /// <summary>
        /// 执行带锁的操作(无返回值)
        /// </summary>
        public async Task ExecuteWithLockAsync(
            string key,
            Func<Task> action,
            TimeSpan expiry,
            TimeSpan? timeout = null)
        {
            await ExecuteWithLockAsync(key, async () =>
            {
                await action();
                return true;
            }, expiry, timeout);
        }
    }
}
