using System.Collections.Generic;

namespace BxPlatform.Common.Models
{
    /// <summary>
    /// 分页结果
    /// </summary>
    /// <typeparam name="T">数据类型</typeparam>
    public class PagedResult<T>
    {
        /// <summary>
        /// 数据列表
        /// </summary>
        public List<T> Items { get; set; } = new List<T>();

        /// <summary>
        /// 总记录数
        /// </summary>
        public int Total { get; set; }

        /// <summary>
        /// 当前页码
        /// </summary>
        public int PageIndex { get; set; }

        /// <summary>
        /// 每页大小
        /// </summary>
        public int PageSize { get; set; }

        /// <summary>
        /// 总页数
        /// </summary>
        public int TotalPages => (Total + PageSize - 1) / PageSize;

        /// <summary>
        /// 是否有上一页
        /// </summary>
        public bool HasPrevious => PageIndex > 1;

        /// <summary>
        /// 是否有下一页
        /// </summary>
        public bool HasNext => PageIndex < TotalPages;

        /// <summary>
        /// 构造函数
        /// </summary>
        public PagedResult()
        {
        }

        /// <summary>
        /// 构造函数
        /// </summary>
        public PagedResult(List<T> items, int total, int pageIndex, int pageSize)
        {
            Items = items;
            Total = total;
            PageIndex = pageIndex;
            PageSize = pageSize;
        }
    }
}
