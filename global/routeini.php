<?php
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
