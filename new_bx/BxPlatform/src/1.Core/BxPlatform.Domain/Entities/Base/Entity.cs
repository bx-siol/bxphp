using System;

namespace BxPlatform.Domain.Entities.Base
{
    /// <summary>
    /// 实体基类
    /// 所有领域实体都应该继承此类
    /// </summary>
    public abstract class Entity
    {
        /// <summary>
        /// 实体唯一标识
        /// </summary>
        public int Id { get; protected set; }

        /// <summary>
        /// 创建时间(Unix时间戳)
        /// </summary>
        public int CreateTime { get; protected set; }

        /// <summary>
        /// 更新时间(Unix时间戳)
        /// </summary>
        public int UpdateTime { get; protected set; }

        /// <summary>
        /// 是否已删除
        /// </summary>
        public bool IsDeleted { get; protected set; }

        protected Entity()
        {
            CreateTime = (int)DateTimeOffset.UtcNow.ToUnixTimeSeconds();
            UpdateTime = CreateTime;
            IsDeleted = false;
        }

        /// <summary>
        /// 标记为已删除(软删除)
        /// </summary>
        public virtual void MarkAsDeleted()
        {
            IsDeleted = true;
            UpdateTime = (int)DateTimeOffset.UtcNow.ToUnixTimeSeconds();
        }

        /// <summary>
        /// 更新时间戳
        /// </summary>
        protected void UpdateTimestamp()
        {
            UpdateTime = (int)DateTimeOffset.UtcNow.ToUnixTimeSeconds();
        }

        /// <summary>
        /// 实体相等性比较(基于ID)
        /// </summary>
        public override bool Equals(object? obj)
        {
            if (obj is not Entity other)
                return false;

            if (ReferenceEquals(this, other))
                return true;

            if (GetType() != other.GetType())
                return false;

            if (Id == 0 || other.Id == 0)
                return false;

            return Id == other.Id;
        }

        /// <summary>
        /// 获取哈希码
        /// </summary>
        public override int GetHashCode()
        {
            return (GetType().ToString() + Id).GetHashCode();
        }

        /// <summary>
        /// 相等运算符重载
        /// </summary>
        public static bool operator ==(Entity? a, Entity? b)
        {
            if (a is null && b is null)
                return true;

            if (a is null || b is null)
                return false;

            return a.Equals(b);
        }

        /// <summary>
        /// 不等运算符重载
        /// </summary>
        public static bool operator !=(Entity? a, Entity? b)
        {
            return !(a == b);
        }
    }
}
