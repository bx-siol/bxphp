<?php

use think\facade\Db;
use \Firebase\JWT\JWT;

//登录ip校验
function checkIp($pageuser)
{
	if (!$pageuser) {
		return true;
	}
	if ($pageuser['iscom']) {
		return true;
	}
	$white_ctl = ['Login'];
	if (in_array(CONTROLLER_NAME, $white_ctl)) {
		return true;
	}

	if ($pageuser['white_ip']) {
		$ip_arr = explode(',', $pageuser['white_ip']);
		if ($ip_arr) {
			if (!in_array(CLIENT_IP, $ip_arr)) {
				ReturnToJson('-1', 'IP Forbidden');
			}
		}
	}
	return true;
}

//获取用户信息
function getUserinfo($uid)
{
	$uid = intval($uid);
	if (!$uid)
		return false;
	$mem = new MyRedis(0);
	$mem_key = RedisKeys::USER_INFO . $uid;
	$user = [];
	$user = $mem->get($mem_key);
	if (!$user || !is_array($user)) {
		$user = Db::table('sys_user')->where("id={$uid}")->find();
		if ($user)
			$mem->set($mem_key, $user);
	}
	$mem->close();
	if (!$user)
		return false;
	return $user;
}

//更新用户信息
function updateUserinfo($uid, $user = [])
{
	$uid = intval($uid);
	if (!$uid || !$user)
		return false;
	Db::table('sys_user')->where("id={$uid}")->update($user);
	$user = flushUserinfo($uid);
	return $user;
}

//刷新用户缓存信息
function flushUserinfo($uid)
{
	$uid = intval($uid);
	if (!$uid)
		return false;
	$mem = new MyRedis(0);
	$mem->rm(RedisKeys::USER_INFO . $uid);
	$mem->rmall(RedisKeys::USER_WALLET . $uid);
	$mem->close();
	$user = getUserinfo($uid);
	return $user;
}

//删除用户缓存信息
function delCashUserinfo($uid)
{
	$uid = intval($uid);
	$mem = new MyRedis(0);
	$mem_key = RedisKeys::USER_INFO . $uid;
	$mem->rm($mem_key);
	$mem->close();
	return true;
}

//根据账号获取用户信息
function getUserByAccount($account)
{
	$need_cache = false;
	$redis = new MyRedis(0);
	$rkey = 'acc2uid_' . $account;
	$uid = intval($redis->get($rkey));
	if (!$uid || strlen($account) < 10) {
		$tuser = Db::table('sys_user')->field('id')->whereRaw('account=:account or phone=:phone', ['account' => $account, 'phone' => $account])->find();
		if ($tuser['id']) {
			$uid = $tuser['id'];
			$need_cache = true;
		}
	}
	if (!$uid) {
		$redis->close();
		unset($redis);
		return false;
	}
	if ($need_cache)
		$redis->set($rkey, $uid);
	$redis->close();
	unset($redis);
	$user = getUserinfo($uid);
	return $user;
}

//根据token获取用户信息
function getUserByToken($tokenStr)
{
	if (!$tokenStr) {
		return -1;
	}
	$token_arr = [];
	try {
		$publicKey = $_ENV['rsa_pt_public'];
		$token_arr = (array) JWT::decode($tokenStr, $publicKey, ['RS256']);
	} catch (\Exception $e) {
		return $e;
	}
	if (!$token_arr || !$token_arr['time'] || !$token_arr['uid'] || !$token_arr['account'] || !$token_arr['token'] || !$token_arr['sign']) {
		return -11;
	}
	$sign = sysSign($token_arr);
	if ($sign != $token_arr['sign']) {
		return -111;
	}

	$token = filterParam($token_arr['token']);
	$tk_key = 'token_' . $token;

	$redis = new MyRedis(0);
	$sys_user_token = $redis->get($tk_key);
	//$sys_user_token=Db::table('sys_user_token')->where("token='{$token}' and status=0")->find();

	if (!$sys_user_token) {
		$redis->close();
		unset($redis);
		return -2;
	}

	//检测有效期等等
	if ($sys_user_token['update_time'] < NOW_TIME) {
		$redis->close();
		unset($redis);
		return -3;
	}

	if ($_ENV['CONFIG']['TOKEN_EXPIRE_TIME'] > 0) {
		$dest_time = NOW_TIME + $_ENV['CONFIG']['TOKEN_EXPIRE_TIME'];
	} else {
		$dest_time = NOW_TIME + 3086400;
	}

	$sys_user_token['update_time'] = $dest_time;

	$redis->hset($tk_key, 'update_time', $dest_time);
	$redis->close();
	unset($redis);

	$user = getUserinfo($sys_user_token['uid']);
	if (!$user || !is_array($user)) {
		return -4;
	}

	$user['iscom'] = $sys_user_token['iscom'];
	$user['token_ori'] = $sys_user_token['token'];
	$user['token_time'] = $sys_user_token['update_time'];
	$user['token'] = $tokenStr;
	return $user;
}

function getEndOfDay()
{
	$now = time();
	$endOfDay = strtotime('tomorrow') - 1; // 'tomorrow' 获取的是明天的凌晨0点，减去1秒得到今天23:59:59
	$secondsTillEndOfDay = $endOfDay - $now;
	return $secondsTillEndOfDay;
}
function isLogin()
{
	$tokenStr = getParam('token'); //参数
	if (!$tokenStr) {
		$tokenStr = trim($_SERVER['HTTP_TOKEN']); //header
	}
	if (!$tokenStr) {
		return false;
	}
	$user = getUserByToken($tokenStr);

	if (!$user || !is_array($user)) {
		return false;
	}
	return $user;
}

//检查登录
function checkLogin()
{
	$user = isLogin();
	if ($user) {
		return $user;
	}
	if (CONTROLLER_NAME == 'Login') {
		return false;
	}
	if (isAjax()) {
		ReturnToJson('-98', '请先登录');
	} else {
		session_start();
		$_SESSION['backurl'] = REQUEST_SCHEME . '://' . HTTP_HOST . $_SERVER['REQUEST_URI'];
		$url = REQUEST_SCHEME . '://' . HTTP_HOST . '/#/login';
		header("Location:{$url}");
		exit;
	}
}

//生成token
function getToken($user, $iscom = 0)
{
	if (!$user['id'] || !$user['account']) {
		return false;
	}
	if ($_ENV['CONFIG']['TOKEN_EXPIRE_TIME'] > 0) {
		$dest_time = NOW_TIME + $_ENV['CONFIG']['TOKEN_EXPIRE_TIME'];
	} else {
		$dest_time = NOW_TIME + 3086400;
	}
	$token_ori = md5(getRsn());
	$sys_user_token = [
		'uid' => $user['id'],
		'account' => $user['account'],
		'token' => $token_ori,
		'create_time' => NOW_TIME,
		'update_time' => $dest_time,
		'iscom' => $iscom,
		'gid' => $user['gid'],
		'pid' => $user['pid'],
		'pidg1' => $user['pidg1'],
		'pidg2' => $user['pidg2'],
	];
	$redis = new MyRedis(0);
	$tk_key = 'token_' . $token_ori;
	$tag = 'uid2token_' . $user['id'];
	$res = $redis->set($tk_key, $sys_user_token, null, $tag);
	$redis->close();
	unset($redis);
	if ($res === false) {
		return false;
	}
	$token_arr = [
		'uid' => $sys_user_token['uid'],
		'account' => $sys_user_token['account'],
		'token' => $sys_user_token['token'],
		'time' => NOW_TIME
	];
	$token_arr['sign'] = sysSign($token_arr);
	$privateKey = $_ENV['rsa_pt_private'];
	$token = JWT::encode($token_arr, $privateKey, 'RS256');
	return $token;
}

//清除token
function clearToken($uid)
{
	$redis = new MyRedis(0);
	$tag = 'uid2token_' . $uid;
	$redis->clear($tag);
	$redis->close();
	unset($redis);
	return true;
}

//执行退出清理
function doLogout()
{
	$user = checkLogin();
	if (!$user) {
		return true;
	}
	$log_arr = [
		'account' => $user['account'],
		'login_time' => $user['login_time'],
		'login_ip' => $user['login_ip'],
		'token' => $user['token_ori']
	];
	actionLog(['opt_name' => '退出', 'sql_str' => json_encode($log_arr)]);
	//清理token
	clearToken($user['id']);
	//清理节点缓存
	$mem = new MyRedis();
	$tag = 'usernodes_' . $user['id'];
	$mem->clear($tag);
	$mem->close();
	unset($mem);
	return true;
}

//踢用户下线
function kickUser($uid)
{
	$uid = intval($uid);
	if (!$uid) {
		return false;
	}
	return clearToken($uid);
}
