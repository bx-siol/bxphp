<?php
header('content-Type: text/html; charset=utf-8');
//ini_set('date.timezone','Etc/GMT-6');
date_default_timezone_set("Asia/Kolkata");
//date_default_timezone_set("America/Bogota"); 
if (!defined('APP_DEBUG'))
	define('APP_DEBUG', false);
if (!defined('ROOT_PATH'))
	define('ROOT_PATH', dirname(__FILE__) . '/../');
if (!defined('APP_PATH'))
	define('APP_PATH', ROOT_PATH . 'api/');
define('GLOBAL_PATH', ROOT_PATH . 'global/');
define('LIB_PATH', GLOBAL_PATH . 'library/');

define('PAY_BACKURL_N', 'www.indianestle.com');
define('PAY_BACKURL_S', 'www.syngentainr.com');
// define('PAY_BACKURL_o', '47.242.112.159');

define('LOGS_PATH', ROOT_PATH . 'logs/');
define('APP_URL', $_SERVER['DOCUMENT_URI'] != null ? trim($_SERVER['DOCUMENT_URI'], '/') : 'api/index.php?');
define('NOW_TIME', time());
define('NOW_DATE', date('Y-m-d H:i:s', NOW_TIME));
if (APP_DEBUG) {
	error_reporting(E_ALL & ~E_NOTICE);
} else {
	error_reporting(0);
}

if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
	define('IS_WIN', true);
} else {
	define('IS_WIN', false);
}

if (strtolower(php_sapi_name()) == 'cli') {
	define('PHP_CLI', true);
} else {
	define('PHP_CLI', false);
}

if (!$_SERVER['REQUEST_SCHEME']) {
	$_SERVER['REQUEST_SCHEME'] = 'http';
}

require_once ROOT_PATH . 'vendor/autoload.php';
require_once GLOBAL_PATH . 'db.conf.php';
use think\facade\Db;

Db::setConfig($_ENV['DB']);

//基本类库
require_once LIB_PATH . 'MyRedis.class.php';
require_once LIB_PATH . 'Image.class.php';
require_once LIB_PATH . 'UploadFile.class.php';
require_once LIB_PATH . 'QRcode.class.php';


//公共方法
require_once GLOBAL_PATH . 'global.func.php';
require_once GLOBAL_PATH . 'common.func.php';
require_once GLOBAL_PATH . 'user.func.php';

//项目配置
if (!PHP_CLI) {
	require_once GLOBAL_PATH . 'route.ini.php';
}