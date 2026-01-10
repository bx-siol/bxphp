namespace BxPlatform.Common.Models
{
    /// <summary>
    /// 统一API返回结果
    /// </summary>
    /// <typeparam name="T">数据类型</typeparam>
    public class ApiResult<T>
    {
        /// <summary>
        /// 状态码(1=成功, 其他=失败)
        /// </summary>
        public int Code { get; set; }

        /// <summary>
        /// 消息
        /// </summary>
        public string Msg { get; set; } = string.Empty;

        /// <summary>
        /// 数据
        /// </summary>
        public T? Data { get; set; }

        /// <summary>
        /// 成功响应
        /// </summary>
        public static ApiResult<T> Success(T data, string msg = "ok")
        {
            return new ApiResult<T>
            {
                Code = 1,
                Msg = msg,
                Data = data
            };
        }

        /// <summary>
        /// 失败响应
        /// </summary>
        public static ApiResult<T> Error(int code, string msg)
        {
            return new ApiResult<T>
            {
                Code = code,
                Msg = msg,
                Data = default
            };
        }

        /// <summary>
        /// 失败响应(默认-1)
        /// </summary>
        public static ApiResult<T> Error(string msg)
        {
            return Error(-1, msg);
        }
    }

    /// <summary>
    /// 无数据的API返回结果
    /// </summary>
    public class ApiResult : ApiResult<object>
    {
        /// <summary>
        /// 成功响应(无数据)
        /// </summary>
        public static new ApiResult Success(string msg = "操作成功")
        {
            return new ApiResult
            {
                Code = 1,
                Msg = msg,
                Data = null
            };
        }

        /// <summary>
        /// 成功响应(带数据)
        /// </summary>
        public static ApiResult<TData> Success<TData>(TData data, string msg = "ok")
        {
            return ApiResult<TData>.Success(data, msg);
        }
    }
}
