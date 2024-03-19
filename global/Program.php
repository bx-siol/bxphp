
<?php
header('content-Type: text/html; charset=utf-8');
//ini_set('date.timezone','Etc/GMT-6');
date_default_timezone_set("Asia/Kolkata");
//date_default_timezone_set("America/Bogota"); 
if (!defined('APP_DEBUG'))
	define('APP_DEBUG', false);
if (!defined('ROOT_PATH'))
	define('ROOT_PATH', __DIR__ . '/../');
if (!defined('APP_PATH'))
	define('APP_PATH', ROOT_PATH . 'api/');

define('GLOBAL_PATH', ROOT_PATH . 'global/');
define('LIB_PATH', GLOBAL_PATH . 'lib/');
define('LOGS_PATH', ROOT_PATH . 'logs/');
define('NOW_TIME', time());
define('NOW_DATE', date('Y-m-d H:i:s', NOW_TIME));

if (APP_DEBUG) {
	error_reporting(E_ALL & ~E_NOTICE);
} else {
	error_reporting(0);
}

if (strtolower(php_sapi_name()) == 'cli') {
	define('PHP_CLI', true);
} else {
	define('PHP_CLI', false);
}

require_once ROOT_PATH . 'vendor/autoload.php';
use think\facade\Db;

//按项目规范参数
if (file_exists(ROOT_PATH . 'nestlexm')) {
	require_once GLOBAL_PATH . 'db_n.php';
} else if (file_exists(ROOT_PATH . 'syngentaxm')) {
	require_once GLOBAL_PATH . 'db_s.php';
} else {//测试服 
	require_once GLOBAL_PATH . 'db.php';
}

Db::setConfig($_ENV['DB']);

//基本类库
require_once LIB_PATH . 'MyRedis.class.php';
require_once LIB_PATH . 'Image.class.php';
require_once LIB_PATH . 'UploadFile.class.php';
require_once LIB_PATH . 'QRcode.class.php';

require_once GLOBAL_PATH . 'const/RedisKeys.php';
//公共方法
require_once GLOBAL_PATH . 'Programfunc.php';
require_once GLOBAL_PATH . 'commonfunc.php';
require_once GLOBAL_PATH . 'userfunc.php';

//项目配置
// if (!PHP_CLI) {
// 	require_once GLOBAL_PATH . 'routeini.php';
// }

//简单路由
define('CLIENT_IP', getClientIp());

$params = getParam();
$module = strtolower($params['m']);
if (!$module)
	$module = 'home';
$params['m'] = ucfirst($module);

$params['c'] = ucfirst(strtolower($params['c']));
if (!$params['c'])
	$params['c'] = 'Default';

if (!$params['a'])
	$params['a'] = 'index';

define('MODULE_NAME', $params['m']);
define('CONTROLLER_NAME', $params['c']);
define('ACTION_NAME', $params['a']);
define('NKEY', CONTROLLER_NAME . '_' . ACTION_NAME);
require_once APP_PATH . 'common/app.conf.php';

$controller = CONTROLLER_NAME . 'Controller';
$action = '_' . ACTION_NAME;

//检查文件是否存在/检查类是否存在/检查类是否存在对应的方法
$ctrl_base = APP_PATH . $module . '/BaseController.class.php';
if (!file_exists($ctrl_base))
	doExit("no such ctrl file:{$ctrl_base}");

$ctrl_file = APP_PATH . $module . '/' . $controller . '.class.php';
if (!file_exists($ctrl_file))
	doExit("no such ctrl file:{$ctrl_file}");


require_once APP_PATH . 'common/CommonCtl.class.php';

require_once $ctrl_base;
require_once $ctrl_file;

if (!class_exists($controller))
	doExit("no such class:{$controller}");

$ctl_obj = new $controller();
if (!$ctl_obj)
	doExit("new {$ctl_obj} fail");

if (!method_exists($ctl_obj, $action))
	doExit("no such {$action}");

call_user_func([$ctl_obj, $action]);
