using System;
using System.Security.Cryptography;
using System.Text;

namespace  Common.Helpers
{
    /// <summary>
    /// 加密辅助类
    /// 提供与PHP完全一致的加密算法
    /// </summary>
    public static class EncryptionHelper
    {
        /// <summary>
        /// 系统密钥(与PHP保持一致)
        /// </summary>
        private const string SYS_KEY = "asfasvcv4856e13asd35a3v1a5dv485adcxx";

        /// <summary>
        /// 加密盐(与PHP保持一致)
        /// </summary>
        private const string SALT = "_kwioxklalis";

        /// <summary>
        /// 获取加密密码(与PHP getPassword函数完全一致)
        /// </summary>
        /// <param name="password">原始密码</param>
        /// <param name="isOriginal">是否是原始密码(未经MD5处理)</param>
        /// <returns>加密后的密码</returns>
        public static string GetPassword(string password, bool isOriginal = false)
        {
            if (string.IsNullOrWhiteSpace(password))
                throw new ArgumentException("密码不能为空", nameof(password));

            string processedPassword = password;

            // 如果是原始密码,先进行MD5加密
            if (isOriginal)
            {
                processedPassword = Md5Encrypt(password);
            }

            // 组合字符串: MD5(password) + SYS_KEY + SALT
            string combinedString = processedPassword + SYS_KEY + SALT;

            // 进行SHA1加密
            return Sha1Encrypt(combinedString);
        }

        /// <summary>
        /// MD5加密(小写32位)
        /// </summary>
        public static string Md5Encrypt(string input)
        {
            if (string.IsNullOrWhiteSpace(input))
                return string.Empty;

            using (var md5 = MD5.Create())
            {
                byte[] inputBytes = Encoding.UTF8.GetBytes(input);
                byte[] hashBytes = md5.ComputeHash(inputBytes);

                // 转换为小写十六进制字符串
                StringBuilder sb = new StringBuilder();
                for (int i = 0; i < hashBytes.Length; i++)
                {
                    sb.Append(hashBytes[i].ToString("x2"));
                }
                return sb.ToString();
            }
        }

        /// <summary>
        /// SHA1加密(小写40位)
        /// </summary>
        public static string Sha1Encrypt(string input)
        {
            if (string.IsNullOrWhiteSpace(input))
                return string.Empty;

            using (var sha1 = SHA1.Create())
            {
                byte[] inputBytes = Encoding.UTF8.GetBytes(input);
                byte[] hashBytes = sha1.ComputeHash(inputBytes);

                // 转换为小写十六进制字符串
                StringBuilder sb = new StringBuilder();
                for (int i = 0; i < hashBytes.Length; i++)
                {
                    sb.Append(hashBytes[i].ToString("x2"));
                }
                return sb.ToString();
            }
        }

        /// <summary>
        /// 验证密码
        /// </summary>
        /// <param name="inputPassword">用户输入的原始密码</param>
        /// <param name="storedPassword">数据库存储的加密密码</param>
        /// <returns>是否匹配</returns>
        public static bool VerifyPassword(string inputPassword, string storedPassword)
        {
            var encryptedPassword = GetPassword(inputPassword, true);
            return encryptedPassword.Equals(storedPassword, StringComparison.OrdinalIgnoreCase);
        }

        /// <summary>
        /// 生成随机字符串
        /// </summary>
        public static string GenerateRandomString(int length)
        {
            const string chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            var random = new Random();
            var result = new char[length];
            
            for (int i = 0; i < length; i++)
            {
                result[i] = chars[random.Next(chars.Length)];
            }
            
            return new string(result);
        }

        /// <summary>
        /// 生成邀请码
        /// </summary>
        public static string GenerateInviteCode(int userId)
        {
            // 使用用户ID和时间戳生成唯一邀请码
            var timestamp = DateTimeOffset.UtcNow.ToUnixTimeSeconds();
            var raw = $"{userId}{timestamp}";
            var hash = Md5Encrypt(raw);
            
            // 取前8位
            return hash.Substring(0, 8).ToUpper();
        }
    }
}
