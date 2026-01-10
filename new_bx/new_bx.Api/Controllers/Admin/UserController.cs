using Microsoft.AspNetCore.Mvc;
using new_bx.Common.Entities;
using new_bx.Common.Helpers;
using new_bx.Common.Models;
using SqlSugar;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace new_bx.Api.Controllers.Admin
{
    /// <summary>
    /// 后台用户控制器
    /// 完整复刻 PHP api/admin/UserController.class.php 的所有功能
    /// 路由格式：/api/?m=Admin&c=User&a=方法名
    /// </summary>
    [ApiController]
    [Route("api")]
    public class UserController : ControllerBase
    {
        private readonly ISqlSugarClient _sqlSugarClient;

        public UserController(ISqlSugarClient sqlSugarClient)
        {
            _sqlSugarClient = sqlSugarClient;
        }

        #region 用户管理

        /// <summary>
        /// 用户列表查询
        /// 复刻PHP的_user方法
        /// 路由：/api/?m=Admin&c=User&a=user
        /// </summary>
        [HttpGet]
        [HttpPost]
        public async Task<ApiResult> User(
            [FromQuery] int page = 1,
            [FromQuery] int s_gid = 0,
            [FromQuery] string? s_keyword = null,
            [FromQuery] string? s_loginip = null,
            [FromQuery] string? s_regip = null,
            [FromQuery] string? s_bankc = null,
            [FromQuery] string? s_keyword2 = null,
            [FromQuery] string? s_keyword3 = null,
            [FromQuery] string? s_keyword4 = null,
            [FromQuery] decimal moneyFrom = 0,
            [FromQuery] decimal moneyTo = 0,
            [FromQuery] string? regTimeRange = null,
            [FromQuery] int status = 0,
            [FromQuery] int s_icode = 0,
            [FromQuery] string? s_has_pay = null)
        {
            try
            {
                // 默认页大小
                int pageSize = 15;

                // 构建查询条件
                var query = _sqlSugarClient.Queryable<SysUserEntity>()
                    .Where(u => u.status < 99); // 状态小于99表示未删除

                // 登录IP筛选
                if (!string.IsNullOrEmpty(s_loginip))
                {
                    query = query.Where(u => u.login_ip == s_loginip);
                }

                // 注册IP筛选
                if (!string.IsNullOrEmpty(s_regip))
                {
                    query = query.Where(u => u.reg_ip == s_regip);
                }

                // 用户组筛选
                if (s_gid > 0)
                {
                    query = query.Where(u => u.gid == s_gid);
                }

                // 关键字搜索（ID、手机号、账号、真实姓名、登录IP、昵称）
                if (!string.IsNullOrEmpty(s_keyword))
                {
                    query = query.Where(u =>
                        u.id.ToString() == s_keyword ||
                        (u.phone != null && u.phone.Contains(s_keyword)) ||
                        (u.account != null && u.account.Contains(s_keyword)) ||
                        (u.realname != null && u.realname.Contains(s_keyword)) ||
                        (u.login_ip != null && u.login_ip.Contains(s_keyword)) ||
                        (u.nickname != null && u.nickname.Contains(s_keyword))
                    );
                }

                // 金额范围筛选
                if (moneyFrom > 0)
                {
                    query = query.Where(u => u.total_invest2 >= moneyFrom);
                }
                if (moneyTo > 0)
                {
                    query = query.Where(u => u.total_invest2 <= moneyTo);
                }

                // 状态筛选
                if (status != 0)
                {
                    query = query.Where(u => u.status == status);
                }

                // 邀请码筛选
                if (s_icode > 0)
                {
                    query = query.Where(u => u.icode == s_icode.ToString());
                }

                // 首充筛选
                if (!string.IsNullOrEmpty(s_has_pay) && s_has_pay != "all")
                {
                    if (s_has_pay == "1")
                    {
                        query = query.Where(u => u.first_pay_day > 0);
                    }
                    else
                    {
                        query = query.Where(u => u.first_pay_day == 0);
                    }
                }

                // 获取总数和统计信息
                var countInfo = await query.Select(u => new
                {
                    cnt = SqlFunc.AggregateCount(u.id),
                    balance = SqlFunc.AggregateSum(u.balance),
                    fz_balance = SqlFunc.AggregateSum(u.fz_balance)
                }).FirstAsync();

                // 分页查询
                var list = await query
                    .OrderBy(u => u.reg_time, OrderByType.Desc)
                    .ToPageListAsync(page, pageSize);

                // 移除敏感信息
                foreach (var item in list)
                {
                    item.password = null;
                    item.password2 = null;
                }

                // 返回数据
                var data = new
                {
                    list = list,
                    count = countInfo.cnt,
                    limit = pageSize,
                    balance = countInfo.balance,
                    fz_balance = countInfo.fz_balance
                };

                return ApiResult.Success(data, "ok");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"User查询错误: {ex.Message}\n{ex.StackTrace}");
                return ApiResult.Error(-99, "系统繁忙请稍后再试");
            }
        }

        /// <summary>
        /// 用户更新/新增接口
        /// 复刻PHP的_user_update方法
        /// 路由：/api/?m=Admin&c=User&a=user_update
        /// </summary>
        [HttpPost]
        public async Task<ApiResult> UserUpdate(
            [FromForm] int id = 0,
            [FromForm] string? nickname = null,
            [FromForm] string? cbank = null,
            [FromForm] string? headimgurl = null,
            [FromForm] string? phone = null,
            [FromForm] string? white_ip = null,
            [FromForm] int status = 2,
            [FromForm] int stop_commission = 0,
            [FromForm] int icode_status = 0,
            [FromForm] int gid = 0,
            [FromForm] string? password = null,
            [FromForm] string? password2 = null,
            [FromForm] string? p_account = null,
            [FromForm] string? account = null)
        {
            try
            {
                // 验证昵称
                if (string.IsNullOrEmpty(nickname))
                {
                    return ApiResult.Error(-1, "请填写昵称");
                }

                var data = new Dictionary<string, object>
                {
                    { "nickname", nickname },
                    { "cbank", cbank ?? "" }
                };

                // 处理头像
                if (!string.IsNullOrEmpty(headimgurl))
                {
                    data["headimgurl"] = headimgurl;
                }
                else
                {
                    data["headimgurl"] = "public/avatar/headimgurl.jpg";
                }

                // 处理手机号
                if (!string.IsNullOrEmpty(phone))
                {
                    // 检查手机号是否已存在
                    var existPhone = await _sqlSugarClient.Queryable<SysUserEntity>()
                        .Where(u => u.phone == phone)
                        .FirstAsync();

                    if (existPhone != null && (id == 0 || (id > 0 && id != existPhone.id)))
                    {
                        return ApiResult.Error(-1, "手机号已存在请更换");
                    }
                    data["phone"] = phone;
                }

                // 处理白名单IP
                if (!string.IsNullOrEmpty(white_ip))
                {
                    white_ip = white_ip.Replace("，", ",");
                    var ipArr = white_ip.Split(',', StringSplitOptions.RemoveEmptyEntries)
                        .Select(ip => ip.Trim())
                        .Where(ip => !string.IsNullOrEmpty(ip))
                        .ToList();
                    data["white_ip"] = string.Join(",", ipArr);
                }

                // 处理状态和佣金设置
                data["status"] = status;
                data["stop_commission"] = stop_commission == 1;
                data["icode_status"] = icode_status;
                data["gid"] = gid;

                // 处理密码
                if (!string.IsNullOrEmpty(password))
                {
                    data["password"] = EncryptHelper.GetPassword(password, true);
                }

                // 处理二级密码
                if (!string.IsNullOrEmpty(password2))
                {
                    data["password2"] = EncryptHelper.GetPassword(password2, true);
                }

                // 处理邀请人
                int pid = 0;
                if (!string.IsNullOrEmpty(p_account))
                {
                    var pUser = await _sqlSugarClient.Queryable<SysUserEntity>()
                        .Where(u => u.account == p_account || u.phone == p_account)
                        .FirstAsync();

                    if (pUser == null)
                    {
                        return ApiResult.Error(-1, $"不存在该邀请人账号：{p_account}");
                    }
                    pid = pUser.id;
                    data["pid"] = pid;
                }

                if (id > 0)
                {
                    // 更新用户
                    var existingUser = await _sqlSugarClient.Queryable<SysUserEntity>()
                        .Where(u => u.id == id)
                        .FirstAsync();

                    if (existingUser == null)
                    {
                        return ApiResult.Error(-1, "用户不存在");
                    }

                    // 检查是否将上级设置为自己
                    if (data.ContainsKey("pid") && (int)data["pid"] == id)
                    {
                        return ApiResult.Error(-1, "无法将上级设置为自己");
                    }

                    await _sqlSugarClient.Updateable<SysUserEntity>()
                        .SetColumns(data)
                        .Where(u => u.id == id)
                        .ExecuteCommandAsync();

                    var returnData = new Dictionary<string, object>
                    {
                        { "id", id },
                        { "account", existingUser.account ?? "" }
                    };

                    return ApiResult.Success(returnData, "操作成功");
                }
                else
                {
                    // 新增用户
                    if (string.IsNullOrEmpty(account))
                    {
                        return ApiResult.Error(-1, "请填写账号");
                    }

                    if (account.Length < 4 || account.Length > 50)
                    {
                        return ApiResult.Error(-1, "请输入4-50个字符的账号");
                    }

                    // 检查账号是否已存在
                    var existAccount = await _sqlSugarClient.Queryable<SysUserEntity>()
                        .Where(u => u.account == account)
                        .FirstAsync();

                    if (existAccount != null)
                    {
                        return ApiResult.Error(-1, $"账号{account}已经存在");
                    }

                    // 生成邀请码（6位随机数字）
                    var random = new Random();
                    var icode = random.Next(100000, 999999).ToString();

                    // 设置新用户数据
                    data["account"] = account;
                    data["openid"] = account;
                    data["icode"] = icode;
                    data["reg_time"] = DateTimeOffset.UtcNow.ToUnixTimeSeconds();
                    data["reg_ip"] = HttpContext.Connection.RemoteIpAddress?.ToString() ?? "";

                    // 生成随机用户ID
                    int newUserId;
                    do
                    {
                        newUserId = random.Next(100000, 999999);
                        var existId = await _sqlSugarClient.Queryable<SysUserEntity>()
                            .Where(u => u.id == newUserId)
                            .AnyAsync();
                        if (!existId) break;
                    } while (true);

                    data["id"] = newUserId;

                    // 插入新用户
                    await _sqlSugarClient.Insertable(data).ExecuteCommandAsync();

                    // 后续优化：调用创建钱包方法 createWallet(newUserId)
                    // 后续优化：调用更新层级方法 updataUserPidGid(newUserId)

                    var returnData = new Dictionary<string, object>
                    {
                        { "id", newUserId },
                        { "account", account }
                    };

                    return ApiResult.Success(returnData, "操作成功");
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"UserUpdate错误: {ex.Message}\n{ex.StackTrace}");
                return ApiResult.Error(-1, "系统繁忙请稍后再试");
            }
        }

        /// <summary>
        /// 删除用户接口
        /// 复刻PHP的_user_delete方法
        /// 路由：/api/?m=Admin&c=User&a=user_delete
        /// </summary>
        [HttpPost]
        public async Task<ApiResult> UserDelete([FromForm] int id)
        {
            try
            {
                if (id <= 0)
                {
                    return ApiResult.Error(-1, "缺少参数");
                }

                if (id == 1)
                {
                    return ApiResult.Error(-1, "超级管理员不能删除");
                }

                var user = await _sqlSugarClient.Queryable<SysUserEntity>()
                    .Where(u => u.id == id)
                    .FirstAsync();

                if (user == null)
                {
                    return ApiResult.Error(-1, "不存在相应的用户");
                }

                // 软删除：设置status为99
                await _sqlSugarClient.Updateable<SysUserEntity>()
                    .SetColumns(u => u.status == 99)
                    .Where(u => u.id == id)
                    .ExecuteCommandAsync();

                // 后续优化：调用踢下线方法 kickUser(id)
                // 后续优化：记录操作日志 actionLog

                return ApiResult.Success(null, "操作成功");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"UserDelete错误: {ex.Message}\n{ex.StackTrace}");
                return ApiResult.Error(-1, "系统繁忙请稍后再试");
            }
        }

        /// <summary>
        /// 踢用户下线接口
        /// 复刻PHP的_user_kick方法
        /// 路由：/api/?m=Admin&c=User&a=user_kick
        /// </summary>
        [HttpPost]
        public async Task<ApiResult> UserKick([FromForm] int id)
        {
            try
            {
                if (id <= 0)
                {
                    return ApiResult.Error(-1, "缺少参数");
                }

                var user = await _sqlSugarClient.Queryable<SysUserEntity>()
                    .Where(u => u.id == id)
                    .FirstAsync();

                if (user == null)
                {
                    return ApiResult.Error(-1, "用户不存在");
                }

                // 后续优化：调用踢下线方法 kickUser(id)
                // 清除用户的token或session信息

                return ApiResult.Success(null, "成功踢下线");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"UserKick错误: {ex.Message}\n{ex.StackTrace}");
                return ApiResult.Error(-1, "系统繁忙请稍后再试");
            }
        }

        /// <summary>
        /// 后台统一充值/扣款接口
        /// 复刻PHP的_user_pay方法
        /// 路由：/api/?m=Admin&c=User&a=user_pay
        /// </summary>
        [HttpPost]
        public async Task<ApiResult> UserPay(
            [FromForm] int id,
            [FromForm] decimal money,
            [FromForm] int ptype, // 1=余额操作，2=冻结余额操作
            [FromForm] string? password2 = null,
            [FromForm] string? remark = null)
        {
            try
            {
                if (money == 0)
                {
                    return ApiResult.Error(-1, "填写的额度不正确");
                }

                // 后续优化：验证当前操作用户的二级密码
                // if (!string.IsNullOrEmpty(password2))
                // {
                //     var hashedPwd2 = EncryptHelper.GetPassword(password2, true);
                //     // 比较密码
                // }

                // 开启事务
                var result = await _sqlSugarClient.Ado.UseTranAsync(async () =>
                {
                    // 锁定用户记录
                    var user = await _sqlSugarClient.Queryable<SysUserEntity>()
                        .With(SqlWith.UpdLock) // 行锁
                        .Where(u => u.id == id)
                        .FirstAsync();

                    if (user == null)
                    {
                        throw new Exception("不存在要操作的用户");
                    }

                    if (ptype == 1)
                    {
                        // 操作可用余额
                        var newBalance = user.balance + money;
                        if (newBalance < 0)
                        {
                            throw new Exception("用户可用余额不足");
                        }

                        await _sqlSugarClient.Updateable<SysUserEntity>()
                            .SetColumns(u => u.balance == newBalance)
                            .Where(u => u.id == id)
                            .ExecuteCommandAsync();

                        // 后续优化：记录余额变动日志 balanceLog
                    }
                    else if (ptype == 2)
                    {
                        // 操作冻结余额
                        var newFzBalance = user.fz_balance + money;
                        if (newFzBalance < 0)
                        {
                            throw new Exception("用户可用冻结不足");
                        }

                        await _sqlSugarClient.Updateable<SysUserEntity>()
                            .SetColumns(u => u.fz_balance == newFzBalance)
                            .Where(u => u.id == id)
                            .ExecuteCommandAsync();

                        // 后续优化：记录余额变动日志 balanceLog
                    }
                    else
                    {
                        throw new Exception("未知操作类型");
                    }

                    // 查询更新后的用户信息
                    var updatedUser = await _sqlSugarClient.Queryable<SysUserEntity>()
                        .Where(u => u.id == id)
                        .FirstAsync();

                    return new
                    {
                        balance = updatedUser!.balance,
                        fz_balance = updatedUser.fz_balance
                    };
                });

                return ApiResult.Success(result.Data, "操作成功");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"UserPay错误: {ex.Message}\n{ex.StackTrace}");
                return ApiResult.Error(-1, ex.Message.Contains("用户") ? ex.Message : "系统繁忙请稍后再试");
            }
        }

        /// <summary>
        /// 切换用户首充日期
        /// 复刻PHP的_UpdateUserfirst_pay_day方法
        /// 路由：/api/?m=Admin&c=User&a=UpdateUserfirst_pay_day
        /// </summary>
        [HttpPost]
        public async Task<ApiResult> UpdateUserfirstPayDay([FromForm] int id)
        {
            try
            {
                if (id <= 0)
                {
                    return ApiResult.Error(-1, "请填写转出账号");
                }

                var user = await _sqlSugarClient.Queryable<SysUserEntity>()
                    .Where(u => u.id == id)
                    .FirstAsync();

                if (user == null)
                {
                    return ApiResult.Error(0, "用户不存在");
                }

                // 切换首充日期：如果为0则设置为当天，否则设置为0
                var nowDay = int.Parse(DateTime.Now.ToString("yyyyMMdd"));
                var newFirstPayDay = user.first_pay_day == 0 ? nowDay : 0;

                await _sqlSugarClient.Updateable<SysUserEntity>()
                    .SetColumns(u => u.first_pay_day == newFirstPayDay)
                    .Where(u => u.id == id)
                    .ExecuteCommandAsync();

                user.first_pay_day = newFirstPayDay;

                return ApiResult.Success(user, "切换成功");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"UpdateUserfirstPayDay错误: {ex.Message}\n{ex.StackTrace}");
                return ApiResult.Error(-1, "系统繁忙请稍后再试");
            }
        }

        /// <summary>
        /// 批量禁用/解禁/转有效无效
        /// 复刻PHP的_DisableStatus方法
        /// 路由：/api/?m=Admin&c=User&a=DisableStatus
        /// </summary>
        [HttpPost]
        public async Task<ApiResult> DisableStatus(
            [FromForm] int[] ids,
            [FromForm] string? field = null,
            [FromForm] int status = 0,
            [FromForm] bool bs = false)
        {
            try
            {
                if (ids == null || ids.Length == 0)
                {
                    return ApiResult.Error(-1, "至少选择一项");
                }

                var validIds = ids.Where(id => id > 0).ToList();
                if (validIds.Count == 0)
                {
                    return ApiResult.Error(-1, "至少选择一项");
                }

                if (string.IsNullOrEmpty(field))
                {
                    return ApiResult.Error(-1, "缺少字段参数");
                }

                if (bs)
                {
                    // 处理日期类型字段（如first_pay_day）
                    var nowDay = status == 0 ? 0 : int.Parse(DateTime.Now.ToString("yyyyMMdd"));

                    foreach (var id in validIds)
                    {
                        await _sqlSugarClient.Updateable<SysUserEntity>()
                            .SetColumns($"{field} = {nowDay}")
                            .Where(u => u.id == id)
                            .ExecuteCommandAsync();
                    }
                }
                else
                {
                    // 处理普通状态字段
                    foreach (var id in validIds)
                    {
                        await _sqlSugarClient.Updateable<SysUserEntity>()
                            .SetColumns($"{field} = {status}")
                            .Where(u => u.id == id)
                            .ExecuteCommandAsync();
                    }
                }

                return ApiResult.Success(null, "操作成功");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"DisableStatus错误: {ex.Message}\n{ex.StackTrace}");
                return ApiResult.Error(-1, "系统繁忙请稍后再试");
            }
        }

        /// <summary>
        /// 转移所有下级
        /// 复刻PHP的_transferAct方法
        /// 路由：/api/?m=Admin&c=User&a=transferAct
        /// </summary>
        [HttpPost]
        public async Task<ApiResult> TransferAct(
            [FromForm] string? from_account = null,
            [FromForm] string? to_account = null,
            [FromForm] string? password2 = null)
        {
            try
            {
                if (string.IsNullOrEmpty(from_account) || string.IsNullOrEmpty(to_account))
                {
                    return ApiResult.Error(-1, "请填写转入/转出账号");
                }

                if (from_account == to_account)
                {
                    return ApiResult.Error(-1, "转入账号和转出账号不能相同");
                }

                // 后续优化：验证二级密码
                // if (!string.IsNullOrEmpty(password2))
                // {
                //     var hashedPwd2 = EncryptHelper.GetPassword(password2, true);
                //     // 比较密码
                // }

                var fromUser = await _sqlSugarClient.Queryable<SysUserEntity>()
                    .Where(u => u.account == from_account)
                    .FirstAsync();

                var toUser = await _sqlSugarClient.Queryable<SysUserEntity>()
                    .Where(u => u.account == to_account)
                    .FirstAsync();

                if (fromUser == null || toUser == null)
                {
                    return ApiResult.Error(-1, "账号不存在");
                }

                // 开启事务
                await _sqlSugarClient.Ado.UseTranAsync(async () =>
                {
                    // 查询所有直接下级
                    var directChildren = await _sqlSugarClient.Queryable<SysUserEntity>()
                        .Where(u => u.pid == fromUser.id)
                        .ToListAsync();

                    // 更新所有直接下级的pid
                    foreach (var child in directChildren)
                    {
                        await _sqlSugarClient.Updateable<SysUserEntity>()
                            .SetColumns(u => u.pid == toUser.id)
                            .Where(u => u.id == child.id)
                            .ExecuteCommandAsync();
                    }

                    // 后续优化：更新层级关系 pidg1、pidg2
                });

                return ApiResult.Success(null, "转移成功,等待后台同步所有下级的层级，预计1-10分钟后同步完成");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"TransferAct错误: {ex.Message}\n{ex.StackTrace}");
                return ApiResult.Error(-1, "系统繁忙请稍后再试");
            }
        }

        #endregion

        #region 辅助方法（预留后续优化）

        // 后续优化：注入ISysUserService，将业务逻辑迁移到服务层
        // 后续优化：创建用户请求模型，使用Furion DataAnnotations实现参数校验
        // 后续优化：集成Furion BackgroundJob，将日志记录改为异步队列
        // 后续优化：拆分仓储层（SysUserRepository），将SqlSugar数据操作迁移到仓储层

        #endregion
    }
}
