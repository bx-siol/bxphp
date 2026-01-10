using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Authorization;

namespace  AdminApi.Controllers.Admin;

/// <summary>
/// 管理端-翻译控制器
/// </summary>
[ApiController]
[Route("api/Admin/[controller]")]
public class TransController : BaseController
{
    /// <summary>
    /// 更新翻译内容
    /// POST /api/Admin/Trans/Trans_update 或 /api/?m=Admin&c=Trans&a=trans_update
    /// </summary>
    [HttpPost("Trans_update")]
    public IActionResult TransUpdate()
    {
        try
        {
            // TODO: 实现翻译内容更新
            return Success(null, "更新成功");
        }
        catch (Exception ex)
        {
            return Fail($"更新翻译失败: {ex.Message}");
        }
    }
}
