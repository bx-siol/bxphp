# BX平台 .NET 项目创建脚本
# 执行方式: .\创建项目脚本.ps1

Write-Host "开始创建 BxPlatform 项目结构..." -ForegroundColor Green

$rootPath = "D:\code\php\bxphp\new_bx"
Set-Location $rootPath

# 创建解决方案
Write-Host "创建解决方案..." -ForegroundColor Yellow
dotnet new sln -n BxPlatform -o .

# 创建目录结构
$directories = @(
    "src\1.Core",
    "src\2.Infrastructure", 
    "src\3.Presentation",
    "src\4.Tests"
)

foreach ($dir in $directories) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "  创建目录: $dir" -ForegroundColor Gray
    }
}

# 1. 领域层
Write-Host "`n创建领域层..." -ForegroundColor Yellow
Set-Location "src\1.Core"
dotnet new classlib -n BxPlatform.Domain -f net8.0
Remove-Item "BxPlatform.Domain\Class1.cs" -ErrorAction SilentlyContinue
Set-Location "..\..\"
dotnet sln add "src\1.Core\BxPlatform.Domain\BxPlatform.Domain.csproj"

# 2. 应用层
Write-Host "创建应用层..." -ForegroundColor Yellow
Set-Location "src\1.Core"
dotnet new classlib -n BxPlatform.Application -f net8.0
Remove-Item "BxPlatform.Application\Class1.cs" -ErrorAction SilentlyContinue
Set-Location "..\..\"
dotnet sln add "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj"

# 3. 基础设施层
Write-Host "创建基础设施层..." -ForegroundColor Yellow
Set-Location "src\2.Infrastructure"
dotnet new classlib -n BxPlatform.Infrastructure -f net8.0
Remove-Item "BxPlatform.Infrastructure\Class1.cs" -ErrorAction SilentlyContinue
Set-Location "..\..\"
dotnet sln add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj"

# 4. 通用工具库
Write-Host "创建通用工具库..." -ForegroundColor Yellow
Set-Location "src\2.Infrastructure"
dotnet new classlib -n BxPlatform.Common -f net8.0
Remove-Item "BxPlatform.Common\Class1.cs" -ErrorAction SilentlyContinue
Set-Location "..\..\"
dotnet sln add "src\2.Infrastructure\BxPlatform.Common\BxPlatform.Common.csproj"

# 5. API层
Write-Host "创建API层..." -ForegroundColor Yellow
Set-Location "src\3.Presentation"
dotnet new webapi -n BxPlatform.Api -f net8.0 --no-https
Remove-Item "BxPlatform.Api\WeatherForecast.cs" -ErrorAction SilentlyContinue
Remove-Item "BxPlatform.Api\Controllers\WeatherForecastController.cs" -ErrorAction SilentlyContinue
Set-Location "..\..\"
dotnet sln add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj"

# 6. 管理后台API
Write-Host "创建管理后台API..." -ForegroundColor Yellow
Set-Location "src\3.Presentation"
dotnet new webapi -n BxPlatform.Admin.Api -f net8.0 --no-https
Remove-Item "BxPlatform.Admin.Api\WeatherForecast.cs" -ErrorAction SilentlyContinue
Remove-Item "BxPlatform.Admin.Api\Controllers\WeatherForecastController.cs" -ErrorAction SilentlyContinue
Set-Location "..\..\"
dotnet sln add "src\3.Presentation\BxPlatform.Admin.Api\BxPlatform.Admin.Api.csproj"

# 7. 单元测试
Write-Host "创建测试项目..." -ForegroundColor Yellow
Set-Location "src\4.Tests"
dotnet new xunit -n BxPlatform.Domain.Tests -f net8.0
dotnet new xunit -n BxPlatform.Application.Tests -f net8.0
Set-Location "..\..\"
dotnet sln add "src\4.Tests\BxPlatform.Domain.Tests\BxPlatform.Domain.Tests.csproj"
dotnet sln add "src\4.Tests\BxPlatform.Application.Tests\BxPlatform.Application.Tests.csproj"

# 添加项目引用关系
Write-Host "`n配置项目引用关系..." -ForegroundColor Yellow

# Application -> Domain
dotnet add "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj" reference "src\1.Core\BxPlatform.Domain\BxPlatform.Domain.csproj"

# Infrastructure -> Domain, Application, Common
dotnet add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj" reference "src\1.Core\BxPlatform.Domain\BxPlatform.Domain.csproj"
dotnet add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj" reference "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj"
dotnet add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj" reference "src\2.Infrastructure\BxPlatform.Common\BxPlatform.Common.csproj"

# Api -> Application, Infrastructure, Common
dotnet add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj" reference "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj"
dotnet add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj" reference "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj"
dotnet add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj" reference "src\2.Infrastructure\BxPlatform.Common\BxPlatform.Common.csproj"

# Admin.Api -> Application, Infrastructure, Common
dotnet add "src\3.Presentation\BxPlatform.Admin.Api\BxPlatform.Admin.Api.csproj" reference "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj"
dotnet add "src\3.Presentation\BxPlatform.Admin.Api\BxPlatform.Admin.Api.csproj" reference "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj"
dotnet add "src\3.Presentation\BxPlatform.Admin.Api\BxPlatform.Admin.Api.csproj" reference "src\2.Infrastructure\BxPlatform.Common\BxPlatform.Common.csproj"

# Tests -> 相应层
dotnet add "src\4.Tests\BxPlatform.Domain.Tests\BxPlatform.Domain.Tests.csproj" reference "src\1.Core\BxPlatform.Domain\BxPlatform.Domain.csproj"
dotnet add "src\4.Tests\BxPlatform.Application.Tests\BxPlatform.Application.Tests.csproj" reference "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj"

Write-Host "`n安装NuGet包..." -ForegroundColor Yellow

# Domain层包
Write-Host "  Domain层依赖..." -ForegroundColor Gray
dotnet add "src\1.Core\BxPlatform.Domain\BxPlatform.Domain.csproj" package MediatR

# Application层包
Write-Host "  Application层依赖..." -ForegroundColor Gray
dotnet add "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj" package AutoMapper
dotnet add "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj" package FluentValidation
dotnet add "src\1.Core\BxPlatform.Application\BxPlatform.Application.csproj" package MediatR

# Infrastructure层包
Write-Host "  Infrastructure层依赖..." -ForegroundColor Gray
dotnet add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj" package SqlSugarCore
dotnet add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj" package Npgsql
dotnet add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj" package StackExchange.Redis
dotnet add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj" package Dapper
dotnet add "src\2.Infrastructure\BxPlatform.Infrastructure\BxPlatform.Infrastructure.csproj" package Polly

# Common层包
Write-Host "  Common层依赖..." -ForegroundColor Gray
dotnet add "src\2.Infrastructure\BxPlatform.Common\BxPlatform.Common.csproj" package Newtonsoft.Json
dotnet add "src\2.Infrastructure\BxPlatform.Common\BxPlatform.Common.csproj" package Serilog

# API层包
Write-Host "  API层依赖..." -ForegroundColor Gray
dotnet add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj" package Swashbuckle.AspNetCore
dotnet add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj" package Serilog.AspNetCore
dotnet add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj" package Serilog.Sinks.Console
dotnet add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj" package Serilog.Sinks.File
dotnet add "src\3.Presentation\BxPlatform.Api\BxPlatform.Api.csproj" package Microsoft.AspNetCore.Authentication.JwtBearer

# Admin API层包
Write-Host "  Admin API层依赖..." -ForegroundColor Gray
dotnet add "src\3.Presentation\BxPlatform.Admin.Api\BxPlatform.Admin.Api.csproj" package Swashbuckle.AspNetCore
dotnet add "src\3.Presentation\BxPlatform.Admin.Api\BxPlatform.Admin.Api.csproj" package Serilog.AspNetCore

# 编译检查
Write-Host "`n编译项目..." -ForegroundColor Yellow
dotnet build

Write-Host "`n项目结构创建完成!" -ForegroundColor Green
Write-Host "解决方案位置: $rootPath\BxPlatform.sln" -ForegroundColor Cyan
Write-Host "`n下一步: 开始创建领域实体和仓储接口" -ForegroundColor Yellow
