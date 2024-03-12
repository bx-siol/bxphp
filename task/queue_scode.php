<?php
require_once(dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

$limit_time = 1200;
$config_key = 'cnf_global_smscode';

while (true) {
	$now_time = time();
	$cnf_global_smscode = getConfig($config_key);
	if ($now_time - $cnf_global_smscode['time'] < $limit_time) {
		sleep(30);
		continue;
	} else {
		$code = mt_rand(111111, 999999);
		$str = "code={$code},time={$now_time}";
		Db::table('sys_config')->where("skey='{$config_key}'")->update(['config' => $str]);
		flushBset($config_key);
		output('更新验证码成功');
	}
}


function flushBset($skey)
{
	$mem_key = 'sys_config_' . $skey;
	$memcache = new MyRedis(0);
	$res = $memcache->rm($mem_key);
	unset($memcache);
	return $res;
}
