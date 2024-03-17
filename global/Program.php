<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set("Asia/Kolkata");

defined('APP_DEBUG') or define('APP_DEBUG', false);
defined('ROOT_PATH') or define('ROOT_PATH', __DIR__ . '/../');
defined('APP_PATH') or define('APP_PATH', ROOT_PATH . 'api/');


define('GLOBAL_PATH', ROOT_PATH . 'global/');
define('LIB_PATH', GLOBAL_PATH . 'lib/');
define('LOGS_PATH', ROOT_PATH . 'logs/');
define('NOW_TIME', time());
define('NOW_DATE', date('Y-m-d H:i:s', NOW_TIME));

error_reporting(APP_DEBUG ? E_ALL & ~E_NOTICE : 0);

define('PHP_CLI', strtolower(php_sapi_name()) === 'cli');

require_once ROOT_PATH . 'vendor/autoload.php';
use think\facade\Db;

// 环境配置文件加载逻辑优化
loadEnvironmentConfig();

Db::setConfig($_ENV['DB']);

// 动态加载基础库和公共方法
$libClasses = ['MyRedis', 'Image', 'UploadFile', 'QRcode'];
loadLibraries($libClasses, LIB_PATH);
loadFiles(GLOBAL_PATH, ['const/RedisKeys.php', 'Programfunc.php', 'commonfunc.php', 'userfunc.php']);

define('CLIENT_IP', getClientIp());
// 简单路由处理，优化代码可读性和可维护性
$routeParams = parseRouteParams();
define('MODULE_NAME', $routeParams['module']);
define('CONTROLLER_NAME', $routeParams['controller']);
define('ACTION_NAME', $routeParams['action']);
define('NKEY', CONTROLLER_NAME . '_' . ACTION_NAME);
require_once APP_PATH . 'common/app.conf.php';

loadController($routeParams);

function loadEnvironmentConfig()
{
    // 环境配置加载逻辑
    $dbConfigFile = file_exists(ROOT_PATH . 'nestlexm') ? 'db_n.php'
        : (file_exists(ROOT_PATH . 'syngentaxm') ? 'db_s.php' : 'db.php');
    require_once GLOBAL_PATH . $dbConfigFile;
}

function loadLibraries(array $libClasses, $path)
{
    foreach ($libClasses as $class) {
        require_once $path . "{$class}.class.php";
    }
}

function loadFiles($path, array $files)
{
    foreach ($files as $file) {
        require_once $path . $file;
    }
}

function parseRouteParams()
{
    $params = getParam();
    $module = ucfirst(strtolower($params['m'] ?: 'home'));
    $controller = ucfirst(strtolower($params['c'] ?: 'Default'));
    $action = ucfirst(strtolower($params['a'] ?: 'index'));

    return [
        'module' => $module,
        'controller' => $controller,
        'action' => $action,
    ];
}
function loadController($routeParams)
{
    extract($routeParams);
    $controllerClass = "{$controller}Controller";
    $actionMethod = "_{$action}";
    require_once APP_PATH . 'common/CommonCtl.class.php';

    $ctrl_base = APP_PATH . strtolower($module) . '/BaseController.class.php';
    $ctrl_file = APP_PATH . strtolower($module) . '/' . $controllerClass . '.class.php';

    if (!file_exists($ctrl_base)) {
        DExit("no such ctrl file:{$ctrl_base}");
    }

    require_once $ctrl_base;
    if (!file_exists($ctrl_file)) {
        DExit("no such ctrl file:{$ctrl_file}");
    }

    require_once $ctrl_file;

    if (!class_exists($controllerClass)) {
        DExit("no such class:{$controllerClass}");
    }

    $controllerObject = new $controllerClass();
    if (!method_exists($controllerObject, $actionMethod)) {
        DExit("no such method {$actionMethod}");
    }
    $controllerObject->$actionMethod();
}
//退出程序
function DExit($str)
{
    if (APP_DEBUG)
        exit ($str);
    exit;
}



