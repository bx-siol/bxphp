using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authorization;

namespace  AdminApi.Controllers.Admin;

/// <summary>
/// 管理端-首页控制器
/// </summary>
[ApiController]
[Route("api/Admin/[controller]")]
public class DefaultController : BaseController
{
    /// <summary>
    /// 获取首页数据 - 综合统计
    /// GET /api/Admin/Default/GetData 或 /api/?m=Admin&c=Default&a=getData
    /// </summary>
    [HttpPost("GetData")]
    [HttpGet("GetData")]
    public IActionResult GetData()
    {
        try
        {
            // TODO: 实现首页数据统计逻辑
            // 1. 获取钱包余额统计 (re_balance, ba_balance, total_balance)
            // 2. 获取投资统计 (invest_count, invest_money, reward_money)
            // 3. 获取充值统计 (total_pay_money, today_pay_money, today_first_pay)
            // 4. 获取提现统计 (total_cash_money, today_cash_money, uncheck_cash_money)
            // 5. 获取抽奖和红包统计 (total_lottery_money, today_lottery_money, total_redpack_money, today_redpack_money)
            // 6. 获取会员统计 (total_member, today_member, effective_member)
            // 7. 计算总结余 (total_jy = total_pay_money - total_cash_money)

            var data = new
            {
                re_balance = "0.00",              // 充值余额
                ba_balance = "0.00",              // 余额宝余额
                total_balance = "0.00",           // 总余额
                invest_count_today = 0,           // 今日投资订单数
                invest_count = 0,                 // 总投资订单数
                invest_money = 0.00m,             // 总投资金额
                reward_money = 0.00m,             // 总奖励金额
                total_pay_money = 0.00m,          // 总充值金额
                today_pay_money = 0.00m,          // 今日充值金额
                today_first_pay = 0,              // 今日首充人数
                total_cash_money = 0.00m,         // 总提现金额
                today_cash_money = 0.00m,         // 今日提现金额
                uncheck_cash_money = 0.00m,       // 未审核提现金额
                total_lottery_money = 0.00m,      // 总抽奖金额
                today_lottery_money = 0.00m,      // 今日抽奖金额
                total_redpack_money = 0.00m,      // 总红包金额
                today_redpack_money = 0.00m,      // 今日红包金额
                total_member = 0,                 // 总会员数
                today_member = 0,                 // 今日新增会员
                effective_member = 0,             // 有效会员数
                sms_code = "",                    // 短信验证码
                total_jy = "0.00"                 // 总结余
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取首页数据失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 获取首页数据1 - 投资订单相关统计
    /// GET /api/Admin/Default/GetData1 或 /api/?m=Admin&c=Default&a=getData1
    /// </summary>
    [HttpPost("GetData1")]
    [HttpGet("GetData1")]
    public IActionResult GetData1()
    {
        try
        {
            // TODO: 实现投资订单统计逻辑
            var data = new
            {
                total_balance = "0.00",           // 总余额
                invest_count_today = 0,           // 今日投资订单数
                invest_count = 0,                 // 总投资订单数
                invest_money = 0.00m,             // 总投资金额
                reward_money = 0.00m,             // 总奖励金额
                reward_money_today = 0.00m        // 今日奖励金额
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取数据失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 获取首页数据2 - 充值提现统计
    /// GET /api/Admin/Default/GetData2 或 /api/?m=Admin&c=Default&a=getData2
    /// </summary>
    [HttpPost("GetData2")]
    [HttpGet("GetData2")]
    public IActionResult GetData2()
    {
        try
        {
            // TODO: 实现充值提现统计逻辑
            var data = new
            {
                total_pay_money = 0.00m,          // 总充值金额
                today_pay_money = 0.00m,          // 今日充值金额
                today_first_pay = 0,              // 今日首充人数
                total_cash_money = 0.00m,         // 总提现金额
                today_cash_money = 0.00m,         // 今日提现金额
                total_jy = "0.00",                // 总结余
                uncheck_cash_money = 0.00m        // 未审核提现金额
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取数据失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 获取首页数据3 - 会员和红包统计
    /// GET /api/Admin/Default/GetData3 或 /api/?m=Admin&c=Default&a=getData3
    /// </summary>
    [HttpPost("GetData3")]
    [HttpGet("GetData3")]
    public IActionResult GetData3()
    {
        try
        {
            // TODO: 实现会员和红包统计逻辑
            var data = new
            {
                today_redpack_money = 0.00m,      // 今日红包金额
                total_member = 0,                 // 总会员数
                today_member = 0,                 // 今日新增会员
                effective_member = 0,             // 有效会员数
                Invalid_member = 0                // 无效会员数
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取数据失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 获取万能验证码
    /// GET /api/Admin/Default/Yzm 或 /api/?m=Admin&c=Default&a=yzm
    /// </summary>
    [HttpPost("Yzm")]
    [HttpGet("Yzm")]
    public IActionResult Yzm()
    {
        try
        {
            // 生成6位随机验证码
            var random = new Random();
            var smsCode = random.Next(100000, 999999).ToString();
            
            // TODO: 将验证码存储到Redis，有效期10分钟
            // var key = $"WN_CODE{smsCode}";
            // _cacheService.Set(key, smsCode, TimeSpan.FromMinutes(10));

            var data = new
            {
                sms_code = smsCode
            };

            return Success(data);
        }
        catch (Exception ex)
        {
            return Fail($"获取验证码失败: {ex.Message}");
        }
    }

    /// <summary>
    /// 清除缓存
    /// POST /api/Admin/Default/ClearCache 或 /api/?m=Admin&c=Default&a=clearCache
    /// </summary>
    [HttpPost("ClearCache")]
    public IActionResult ClearCache()
    {
        try
        {
            // TODO: 实现缓存清理逻辑
            // 1. 如果是超级管理员(gid=1)，清理所有缓存
            // 2. 否则只清理当前用户的节点缓存

            return Success(null, "缓存清理成功");
        }
        catch (Exception ex)
        {
            return Fail($"清理缓存失败: {ex.Message}");
        }
    }
}
