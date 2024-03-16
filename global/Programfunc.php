<?php

use think\facade\Db;
use Curl\Curl;

// message 要记录的内容  logFile 文件名 只需要写文件名称，或者 目录/文件名
function writeLog($message, $logFile = "sys")
{
	$time = date('Y-m-d');
	$logFile = LOGS_PATH . $logFile . '/' . $time . '.log';
	$timestamp = date('Y-m-d H:i:s');
	$logContent = $timestamp . ' : ' . $message . PHP_EOL . PHP_EOL;
	// 检查目录是否存在，不存在则创建
	$directory = dirname($logFile);
	if (!file_exists($directory)) {
		mkdir($directory, 0777, true);
	}
	// 写入日志内容
	file_put_contents($logFile, $logContent, FILE_APPEND | LOCK_EX);
}
/*******缓存redis*******/

//短链接模式 读取
function redis_get($key)
{
	$redis = new MyRedis(0);
	$data = $redis->get($key);
	$redis->close();
	return $data;
}
//短链接模式 写入
function redis_set($key = '', $data = [], $time = 60 * 20)
{
	if ($data == [] || $key == '')
		return false;
	$redis = new MyRedis(0);
	$res = $redis->set($key, $data, $time);
	$redis->close();
	return $res;
}
//短链接模式 删除
function redis_rm($key = '')
{
	if ($key == '')
		return false;
	$redis = new MyRedis(0);
	$res = $redis->rm($key);
	$redis->close();
	return $res;
}

/*******缓存redis*******/



//更新用户的父id
function updateUsercpids($uid)
{
	$uid = intval($uid);
	$sql = "update sys_user inner join
	 (select  t.id, CONCAT(t.pid,',',u1.pid,',',u2.pid) vpids  
	 from sys_user t left join sys_user u1 on u1.id=t.pid left join sys_user u2 on u2.id=u1.pid ) b
	on sys_user.id=b.id
	set sys_user.pids=b.vpids where sys_user.id='{$uid}'"; //更新当前用户的 pids
	$down_arr = Db::execute($sql);
	return $down_arr;
}

//更新所有付费用户的父id
function updateUsercpids_all($uid)
{
	$uid = intval($uid);
	$sql = "update sys_user inner join 
	(select  t.id, CONCAT(t.pid,',',u1.pid,',',u2.pid) vpids  
	from sys_user t 
	left join sys_user u1 on u1.id=t.pid 
	left join sys_user u2 on u2.id=u1.pid ) b
	on sys_user.id=b.id
	set sys_user.pids=b.vpids where sys_user.first_pay_day >0";
	$down_arr = Db::execute($sql);
	return $down_arr;
}

//更新用户团队人数
function updateUserTeamCount($uid)
{
	$uid = intval($uid);
	$sql = " UPDATE sys_user
	inner join  (select {$uid} as id, COUNT(1) as counts from sys_user  where  pids like '%{$uid}%') as b
	on sys_user.id=b.id
	SET teamcount = b.counts
	WHERE sys_user.id ={$uid}";
	$down_arr = Db::execute($sql);
	return $down_arr;
}



//获取用户的代理id
function getUsercpid_gid($uid, $gid)
{
	$uid = intval($uid);
	$gid = intval($gid);
	$sql = "WITH RECURSIVE cte AS ( SELECT id, pid, gid FROM sys_user WHERE id = {$uid} UNION ALL 	SELECT t.id, t.pid, t.gid FROM sys_user t JOIN cte ON t.id = cte.pid  WHERE cte.gid = {$gid}) 
	 SELECT id FROM cte  WHERE gid = {$gid} and id !=1";
	$down_arr = Db::query($sql);
	return $down_arr;
}


/*
重置 pid 
UPDATE sys_user SET pidg1=0,pidg2=0;
UPDATE pro_reward SET pidg1=0,pidg2=0;
*/

//更新用户的代理id 
function updataUserPidGid($uid)
{
	$uid = intval($uid); //当前用户id
	$sql = "WITH RECURSIVE cte AS (SELECT id, pid, gid FROM sys_user WHERE id = {$uid}
			UNION ALL SELECT t.id, t.pid, t.gid FROM sys_user t JOIN cte ON t.id = cte.pid) 
			UPDATE sys_user
			SET pidg1 = (SELECT id FROM cte WHERE gid = 71),
			pidg2 = (SELECT id FROM cte WHERE gid = 81) 
			WHERE sys_user.id ={$uid}"; //更新当前用户的 pidg1 pidg2	   
	$down_arr = Db::execute($sql);
	return $down_arr;
}



//获取下级
function getDownUserBack($uid, $need_all = false, $agent_level = 1, $agent_level_limit = 0, $g_down_user = [])
{
	if ($agent_level_limit && $agent_level > $agent_level_limit) {
		return $g_down_user;
	}
	if ($uid) {
		$member_arr = Db::table('sys_user')->whereRaw('pid=:pid', ['pid' => $uid])->select();
		foreach ($member_arr as $mb) {
			if ($mb['id'] && $mb['id'] != $uid && !in_array($mb['id'], $g_down_user)) {
				if ($need_all) {
					$mb['agent_level'] = $agent_level;
					$g_down_user[] = $mb;
				} else {
					$g_down_user[] = $mb['id'];
				}
				$tmp_arr = getDownUser($mb['id'], $need_all, $agent_level + 1, $agent_level_limit, []);
				$g_down_user = array_merge_recursive($g_down_user, $tmp_arr);
			}
		}
	}
	return $g_down_user;
}

function getDownUsercount($uid)
{
	$uid = intval($uid);
	$sql = "select count(1) count from sys_user where pids like '%" . $uid . "%'";
	$down_arr = Db::query($sql);
	return intval($down_arr['count']);
}
function getDownUser_new($uid, $need_all = false, $where = '')
{
	$uid = intval($uid);
	if ($need_all) {
		$sql = "select id, pid, gid, account, nickname, headimgurl, reg_time, down_level+1, first_pay_day from sys_user where pids like '%" . $uid . "%'";
	} else {
		$sql = "select id from sys_user where pids like '%" . $uid . "%'";
	}
	if ($where != '') {
		$sql .= " and " . $where;
	}

	$down_arr = Db::query($sql);
	if (!$need_all) {
		$ids = [];
		foreach ($down_arr as $dv) {
			$ids[] = $dv['id'];
		}
		return $ids;
	}
	return $down_arr;
}
//获取下级
function getDownUser($uid, $need_all = false, $loinuser = [])
{
	$uid = intval($uid);
	if ($loinuser != []) {
		if ($need_all) {
			if ($loinuser['gid'] == 71) {
				$sql = "select id,pid, gid, account,nickname,headimgurl,reg_time,down_level+1,first_pay_day from sys_user where pidg1= {$loinuser['id']} order by id";
			} else if ($loinuser['gid'] == 81) {
				$sql = "select id,pid, gid, account,nickname,headimgurl,reg_time,down_level+1,first_pay_day from sys_user where pidg2= {$loinuser['id']} order by id";
			}
		} else {
			if ($loinuser['gid'] == 71) {
				$sql = "select id from sys_user where pidg1= {$loinuser['id']}";
			} else if ($loinuser['gid'] == 81) {
				$sql = "select id from sys_user where pidg2= {$loinuser['id']}";
			}
		}
	} else {
		if ($need_all) {
			$sql = "select * from (
				with RECURSIVE temp as (select id as t,id,pid,gid,account,nickname,headimgurl,reg_time,down_level,first_pay_day from sys_user
					union all
					select temp.t,a1.id,a1.pid,temp.gid,temp.account,temp.nickname,temp.headimgurl,temp.reg_time,temp.down_level+1,temp.first_pay_day from sys_user a1
					join temp on a1.id=temp.pid
				) select t as id,gid,account,nickname,headimgurl,reg_time,down_level as agent_level,first_pay_day from temp where id={$uid}
				) bb where id!={$uid} order by id";
		} else {
			$sql = "select * from (with RECURSIVE temp as (select id as t,id,pid from sys_user
					union all
					select temp.t,a1.id,a1.pid from sys_user a1
					join temp on a1.id=temp.pid
				) select t as id from temp where id={$uid}) bb where id!={$uid} order by id";
		}
	}
	$down_arr = Db::query($sql);
	if (!$need_all) {
		$ids = [];
		foreach ($down_arr as $dv) {
			$ids[] = $dv['id'];
		}
		return $ids;
	}
	return $down_arr;
}
//
function getDownUser_yx($uid, $need_all = false)
{
	$uid = intval($uid);
	if ($need_all) {
		$sql = "select * from (
			with RECURSIVE temp as (select id as t,id,pid,gid,account,nickname,headimgurl,reg_time,down_level,first_pay_day from sys_user WHERE  first_pay_day >0
				union all
				select temp.t,a1.id,a1.pid,temp.gid,temp.account,temp.nickname,temp.headimgurl,temp.reg_time,temp.down_level+1,temp.first_pay_day from sys_user a1
				join temp on a1.id=temp.pid
			) select t as id,gid,account,nickname,headimgurl,reg_time,down_level as agent_level,first_pay_day from temp where id={$uid}
			) bb where id!={$uid}";
	} else {
		$sql = "select * from (with RECURSIVE temp as (select id as t,id,pid from sys_user WHERE  first_pay_day >0
				union all
				select temp.t,a1.id,a1.pid from sys_user a1
				join temp on a1.id=temp.pid
			) select t as id from temp where id={$uid}) bb where id!={$uid}";
	}
	$down_arr = Db::query($sql);
	if (!$need_all) {
		$ids = [];
		foreach ($down_arr as $dv) {
			$ids[] = $dv['id'];
		}
		return $ids;
	}
	return $down_arr;
}



function getDownUserh5($uid, $need_all = false)
{
	$uid = intval($uid);
	if ($need_all) {
		$sql = "select * from (
			with RECURSIVE temp as (select id as t,id,pid,gid,account,nickname,headimgurl,reg_time,down_level,first_pay_day from sys_user
				union all
				select temp.t,a1.id,a1.pid,temp.gid,temp.account,temp.nickname,temp.headimgurl,temp.reg_time,temp.down_level+1,temp.first_pay_day from sys_user a1
				join temp on a1.id=temp.pid
			) select t as id,gid,account,nickname,headimgurl,reg_time,down_level as agent_level,first_pay_day from temp where id={$uid}
			) bb where id!={$uid} and agent_level<=3";
	} else {
		$sql = "select * from (with RECURSIVE temp as (select id, as t,id,pid,down_level from sys_user
				union all
				select temp.t,a1.id,a1.pid,temp.down_level+1 from sys_user a1
				join temp on a1.id=temp.pid
			) select t as id,down_level from temp where id={$uid}) bb where id!={$uid} and agent_level<=3";
	}
	$down_arr = Db::query($sql);
	if (!$need_all) {
		$ids = [];
		foreach ($down_arr as $dv) {
			$ids[] = $dv['id'];
		}
		return $ids;
	}
	return $down_arr;
}

//获取上级
function getUpUser($uid, $need_all = false, $agent_level = 1, $agent_level_limit = 0, $g_up_user = [])
{
	if ($agent_level_limit && $agent_level > $agent_level_limit + 1) {
		return $g_up_user;
	}
	$member = Db::table('sys_user')->whereRaw('id=:id', ['id' => $uid])->find();
	if ($member) {
		if ($agent_level > 1) { //把当前用户排除掉
			if ($need_all) {
				$member['agent_level'] = $agent_level - 1;
				$g_up_user[] = $member;
			} else {
				if (!in_array($member['id'], $g_up_user)) {
					$g_up_user[] = $member['id'];
				}
			}
		}
		if ($member['pid'] && $member['id'] != $member['pid']) {
			return getUpUser($member['pid'], $need_all, $agent_level + 1, $agent_level_limit, $g_up_user);
		}
	}
	return $g_up_user;
}

//////////////////////////////////////////////////////////

//获取用户分组
function getGroups($gid = 0)
{
	$mem_key = 'sys_group';
	$mem = new MyRedis(0);
	$groups = $mem->get($mem_key);
	if (!$groups) {
		$list = Db::table('sys_group')
			->field('id,name,cover')
			->where("status<99")
			->order(['sort' => 'desc', 'id' => 'asc'])->select()->toArray();
		$groups = [];
		foreach ($list as $item) {
			$groups[$item['id']] = $item;
		}
		$mem->set($mem_key, $groups, 864000);
	}
	$mem->close();
	unset($mem);
	if ($gid) {
		return $groups[$gid];
	}
	return $groups;
}

//获取用户分组索引
function getGroupsIdx()
{
	$groups = getGroups();
	$idxs = [];
	foreach ($groups as $gv) {
		$idxs[$gv['id']] = $gv['name'];
	}
	return $idxs;
}

//生成省市区数据并缓存
function getArea($id = 0)
{
	$mem_key = 'cnf_area';
	$mem = new MyRedis();
	$db = null;
	$area = [];
	do {
		$area = $mem->get($mem_key);
		if (!$area) {
			$area_list = Db::table('cnf_area')->field('id,name')->select()->toArray();
			if ($area_list) {
				foreach ($area_list as $lv) {
					$area[$lv['id']] = $lv['name'];
				}
				$mem->set($mem_key, $area, 86400 * 30);
			}
		}
	} while (false);
	$mem->close();
	if ($db) {
		closeDb($db);
	}
	unset($mem, $db);
	if (!$area) {
		return false;
	}
	if ($id) {
		return $area[$id];
	}
	return $area;
}


/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list2tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0)
{
	$tree = [];
	if (is_array($list)) {
		//创建基于主键的数组引用
		$refer = [];
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] = &$list[$key];
		}
		foreach ($list as $key => $data) {
			//判断是否存在parent
			$parentId = $data[$pid];
			if ($root == $parentId) {
				$tree[] = &$list[$key];
			} else {
				if (isset ($refer[$parentId])) {
					$parent = &$refer[$parentId];
					$list[$key]['pname'] = $refer[$parentId]['name'];
					$parent[$child][] = &$list[$key];
				}
			}
		}
	}
	return $tree;
}

function getTreeItems($tree, &$items = [], $pitem = [])
{
	foreach ($tree as $tv) {
		if (!$pitem) {
			$tv['level'] = 1;
		} else {
			$tv['level'] = $pitem['level'] + 1;
		}
		$items[] = $tv;
		if ($tv['children']) {
			getTreeItems($tv['children'], $items, $tv);
		}
	}
	return $items;
}

//树状下拉选择
function list2Select($list, $rootId = 0)
{
	$select_arr = [];
	$tree = list2tree($list, 'id', 'pid', 'children', $rootId);
	$items = getTreeItems($tree);
	foreach ($items as $iv) {
		if ($iv['pid']) {
			$sp = '';
			for ($i = 0; $i < $iv['level'] - 1; $i++) {
				$sp .= '—';
			}
			$iv['name'] = '|' . $sp . $iv['name'];
		}
		unset($iv['children']);
		$select_arr[] = $iv;
	}
	return $select_arr;
}

function getTreeFields($tree, $field = 'id', $child = 'children', &$fields = [])
{
	foreach ($tree as $tv) {
		$fields[] = $tv[$field];
		if ($tv[$child]) {
			getTreeFields($tv[$child], $field, $child, $fields);
		}
	}
	return $fields;
}

function getTreeIds($list, $rootId = 0)
{
	$pk = 'id';
	$tree = list2tree($list, $pk, 'pid', 'children', $rootId);
	$ids = getTreeFields($tree, $pk, 'children');
	if ($rootId) {
		array_unshift($ids, $rootId);
	}
	return $ids;
}

//获取用户当前语言
function getLang()
{
	if (MODULE_NAME != 'Home') {
		return 'zh-cn';
	}
	$language = $_ENV['LANG_DEF'];
	$user = isLogin();
	if ($user) {
		if ($user['language']) {
			$language = $user['language'];
		}
	} else {
		session_start();
		if ($_SESSION['language']) {
			$language = $_SESSION['language'];
		}
	}
	return $language;
}

//加载语言包
function loadLang($ltype = null)
{
	if (!$ltype) {
		$ltype = getLang();
	}
	if ($ltype == 'zh-cn') {
		return [];
	}
	$file = ROOT_PATH . 'public/lang/' . $ltype . '.php';
	if (!file_exists($file)) {
		$file = ROOT_PATH . 'public/lang/en-us.php';
		if (!file_exists($file)) {
			return [];
		}
	}
	return require_once ($file);
}

//翻译
function lang($str)
{
	if (!$_ENV['lang']) {
		$_ENV['lang'] = loadLang();
	}
	$str2 = trim($_ENV['lang'][$str]);
	return !empty ($str2) ? $str2 : $str;
}

function lang2($str, $ltype = 'en-us')
{
	if (!$_ENV['lang2']) {
		$_ENV['lang2'] = loadLang($ltype);
	}
	$str2 = trim($_ENV['lang2'][$str]);
	return !empty ($str2) ? $str2 : $str;
}

//#####################短信验证码相关开始#####################


function sendSms1($phone, $content)
{
	$apiKey = "AGU89z4d";
	$apiSecret = "vcI6RL9L";
	$appId = "JkU23ao7";
	$url = "https://api.onbuka.com/v3/sendSms";
	$timeStamp = time();
	$sign = md5($apiKey . $apiSecret . $timeStamp);
	// $dataArr['appId'] = $appId;
	// $dataArr['numbers'] = '91' . $phone;
	// $dataArr['content'] = $content;
	// $dataArr['senderId'] = '';

	$pdata = [
		'appId' => $appId,
		'senderId' => '',
		'numbers' => '91' . $phone,
		'content' => $content
	];

	//$data = json_encode($dataArr);
	$headers = ['Content-Type' => 'application/json;charset=UTF-8', 'Sign' => "$sign", 'Timestamp' => "$timeStamp", 'Api-Key' => "$apiKey"];

	$result = curl_post2($url, $pdata, 30, $headers);
	$resultArr = $result['output'];
	// $file = '/www/wwwroot/bsc.com/global/text1.log';
	// file_put_contents($file, json_encode($resultArr) . '========' . $content . PHP_EOL, FILE_APPEND);
	// file_put_contents($file,$resultArr['status'].PHP_EOL,FILE_APPEND);
	// {"status":0, "array":[[00525611494223,1341559445]], "success":1, "fail":0}
	if (!$resultArr['status']) {
		return '0';
	}
	return $resultArr['status'];
}

//AZ4TWC9C    3yF7eOsj

//下发短信-对接实际的接口
function sendSms($phone, $content)
{
	$url = 'http://47.241.187.4:20003/sendsms';
	$file = '/www/wwwroot/bsc.com/global/text.log';
	$pdata = [
		'account' => 'S1J169DIR',
		'password' => 'qrs518',
		'numbers' => '91' . $phone,
		'content' => $content
	];
	$headers = [
		'Content-Type' => 'application/json;charset=UTF-8',
	];
	//file_put_contents($file, $phone.'|'.$content.PHP_EOL,FILE_APPEND); 
	$result = curl_post2($url, $pdata, 30, $headers);
	$resultArr = $result['output'];
	//file_put_contents($file,$resultArr.PHP_EOL,FILE_APPEND);
	//file_put_contents($file,$resultArr['status'].PHP_EOL,FILE_APPEND);
	//{"status":0, "array":[[00525611494223,1341559445]], "success":1, "fail":0}
	if (!$resultArr['status']) {
		return '0';
	}
	return $resultArr['status'];
}

//获取验证码短信
function getPhoneCode($data)
{
	if (!$data['stype'] || !$data['phone']) {
		return ['code' => '-1', 'msg' => '缺少验证参数'];
	}
	$limit_time = NOW_TIME - 60; //60秒以内不能重复获取
	$cnt = Db::table('sys_vcode')->whereRaw(
		'phone=:phone and stype=:stype and create_time>=:create_time',
		[
			'phone' => $data['phone'],
			'stype' => $data['stype'],
			'create_time' => $limit_time
		]
	)->count();
	if ($cnt > 0) {
		return ['code' => '-1', 'msg' => '获取验证码过于频繁，请稍后再试'];
	}
	$sys_sms = getConfig('sys_sms');
	$code = rand(123456, 999999);
	//$content=str_replace('{$code}',$code,$sys_sms['tpl']);
	$content = "Your OTP is {$code}";
	$result = [];

	$result = sendSms1($data['phone'], $content);
	// if ($data['phone'] == '1234567897') {

	// } else {
	// 	$result = sendSms($data['phone'], $content);
	// }
	if ($result != '0') { //短信发送失败
		return [
			'code' => '-1',
			//'msg'=>'短信发送失败',
			'msg' => 'SMS sending failed: ' . $result
			//['result'=>$result,'content'=>$content]
		];
	}
	//记录
	$sys_vcode = [
		'code' => $code,
		'phone' => $data['phone'],
		'stype' => $data['stype'],
		'create_time' => NOW_TIME,
		'create_day' => date('Ymd', NOW_TIME),
		'create_ip' => CLIENT_IP,
		'scon' => $content
	];
	$res = Db::table('sys_vcode')->insert($sys_vcode);
	if (!$res) {
		return ['code' => '-1', 'msg' => '系统繁忙请稍后再试'];
	}
	return ['code' => '1', 'msg' => '发送成功'];
}

//校验验证码
function checkPhoneCode($data)
{
	if (!$data['stype'] || !$data['code'] || !$data['phone']) {
		return ['code' => '-1', 'msg' => '缺少验证参数'];
	}
	$key = "WN_CODE" . $data['code'];
	$redis = new MyRedis();
	if ($redis->has($key)) {
		$redis->rm($key);
		return ['code' => '1', 'msg' => '验证通过'];
	}
	$cnf_global_smscode = getConfig('cnf_global_smscode');
	if ($data['code'] == $cnf_global_smscode['code']) {
		return ['code' => '1', 'msg' => '验证通过'];
	}
	$item = Db::table('sys_vcode')->whereRaw(
		'phone=:phone and stype=:stype',
		[
			'phone' => $data['phone'],
			'stype' => $data['stype']
		]
	)->order(['id' => 'desc'])->find();
	if (!$item['id']) {
		return ['code' => '-1', 'msg' => '该短信验证码不正确'];
	}
	if ($item['status'] || $item['verify_num'] > 2) {
		return ['code' => '-1', 'msg' => '请重新获取短信验证码'];
	}
	//查到验证码且验证使用未达到限制次数
	$msg = '';
	$sys_vcode = ['verify_num' => $item['verify_num'] + 1];
	if ($data['code'] == $item['code']) {
		//检测验证码有效期
		if (NOW_TIME - $item['create_time'] > 1800) {
			$msg = '该短信验证码已失效';
			$sys_vcode['status'] = 1;
		} else {
			$sys_vcode['status'] = 2;
		}
	} else {
		$msg = '该短信验证码不正确';
		if ($sys_vcode['verify_num'] > 2) {
			$sys_vcode['status'] = 1;
		}
	}
	$sys_vcode['verify_time'] = NOW_TIME;
	$res = Db::table('sys_vcode')->where("id={$item['id']}")->save($sys_vcode);
	if (!$res) {
		$msg = '该短信验证码不正确';
	}
	if ($msg) {
		return ['code' => '-1', 'msg' => $msg];
	}
	return ['code' => '1', 'msg' => '验证通过'];
}
//#####################短信验证码相关结束#####################

//#####################公共函数开始#####################

//记录操作日志
function actionLog($data = [])
{
	if ($data['logUid']) {
		$uid = $data['logUid'];
		unset($data['logUid']);
	} else {
		$user = checkLogin();
		if (!$user) {
			return false;
		}
		if ($user['iscom']) {
			return true;
		}
		$uid = $user['id'];
	}
	$default_data = [
		'uid' => $uid,
		'create_time' => NOW_TIME,
		'create_ip' => CLIENT_IP
		//'opt_name'=>'',
		//'sql_str'=>''
	];
	$sys_log = array_merge($data, $default_data);
	$sys_log['sql_str'] = addslashes($sys_log['sql_str']);
	$res = Db::table('sys_log')->insert($sys_log);
	return $res;
}

// //获取RPC客户端
// function getRpc($ctlName = '')
// {
// 	if (!$ctlName) {
// 		$ctlName = 'Default';
// 	}
// 	$url = trim($_ENV['RPC']['URL'], '?') . '?c=' . $ctlName;
// 	//$client = new Yar_Client($url);
// 	//return $client;
// }

//获取伪唯一随机序列号
function getRsn($str = '', $num = 16)
{
	if (!$str) {
		$microtime = microtime();
		$str = md5($microtime . SYS_KEY . mt_rand(100000, 999999));
	} else {
		$str = md5($str);
	}
	if ($num == 16) {
		return substr($str, 8, 16);
	}
	return $str;
}

//系统签名
function sysSign($pdata)
{
	$str = '';
	if ($pdata) {
		ksort($pdata);
		foreach ($pdata as $pk => $pv) {
			if ($pk == 'sign') {
				continue;
			}
			$str .= "{$pk}={$pv}&";
		}
	}
	$str .= 'key=' . SYS_KEY;
	return md5($str);
}

//获取系统配置
function getConfig($skey)
{
	if (!$skey) {
		return false;
	}
	$mem_key = 'sys_config_' . $skey;
	$memcache = new MyRedis(0);
	$config_result = $memcache->get($mem_key);
	if (!$config_result) {
		$config = Db::table('sys_config')->whereRaw("skey=:skey", ['skey' => $skey])->find();
		if (!$config) {
			return false;
		}
		if ($config['single']) {
			$config_result = $config['config'];
		} else {
			$config_tmp = (explode(',', $config['config']));
			$config_arr = [];
			foreach ($config_tmp as $cv) {
				$cv_arr = explode('=', $cv);
				$cv_key = trim($cv_arr[0]);
				if ($cv_key === '') {
					continue;
				}
				$config_arr[$cv_key] = trim($cv_arr[1]);
			}
			$config_result = $config_arr;
		}
		$memcache->set($mem_key, $config_result, 7200);
	}
	$memcache->close();
	return $config_result;
}

//获取平台设置
function getPset($skey, $db = null)
{
	$item = Db::table('sys_pset')->whereRaw('skey=:skey', ['skey' => $skey])->find();
	$config = [];
	if ($item['config']) {
		$config = json_decode($item['config'], true);
		if (!$config) {
			$config = [];
		}
	}
	return $config;
}

//生成密码
function getPassword($pwd, $is_ori = false)
{
	if ($is_ori) {
		$password = sha1(md5($pwd) . SYS_KEY . '_kwioxklalis');
	} else {
		$password = sha1($pwd . SYS_KEY . '_kwioxklalis');
	}
	return $password;
}

//获取参数
function getParam($paramName = '')
{
	if (!empty ($paramName)) {
		$paramValue = filterParam($_REQUEST[$paramName]);
		return $paramValue;
	}
	$params = filterParam($_REQUEST);
	return $params;
}

//过滤方法
function filterParam($paramValue)
{
	if (is_array($paramValue)) {
		$tmp_arr = [];
		foreach ($paramValue as $key => $val) {
			$tmp_arr[$key] = filterParam($val);
		}
		return $tmp_arr;
	} else {
		$paramValue = trim($paramValue);
		if ($paramValue !== '') {
			$paramValue = addslashes($paramValue);
			$paramValue = str_replace("%", "\%", $paramValue); // 把' % '过滤掉
			//$paramValue = nl2br($paramValue);    // 回车转换
			$paramValue = htmlspecialchars($paramValue, ENT_QUOTES);
		} else {
			$paramValue = '';
		}
		return $paramValue;
	}
}

//获取当前时间-精确到毫秒
function getMstime()
{
	list($msec, $sec) = explode(' ', microtime());
	$msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
	return $msectime;
}

//获取客户端ip
function getClientIp($type = 0)
{
	$type = $type ? 1 : 0;
	static $ip = NULL;
	if ($ip !== NULL)
		return $ip[$type];
	if (isset ($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$pos = array_search('unknown', $arr);
		if (false !== $pos)
			unset($arr[$pos]);
		$ip = trim($arr[0]);
	} elseif (isset ($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset ($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = ip2long($ip);
	$ip = $long ? [$ip, $long] : [$ip, 0];
	return $ip[$type];
}

//格式化返回
function ReturnToJson($code, $msg, $data = [])
{
	$return = [
		'code' => $code,
		'msg' => $msg,
		'data' => $data
	];
	if (MODULE_NAME == 'Home') {
		$return['msg'] = lang($msg);
	}
	$json_str = json_encode($return, JSON_UNESCAPED_UNICODE);
	echo $json_str;
	exit;
}

function fReturn($code, $msg, $data = [])
{
	$return = [
		'code' => $code,
		'msg' => $msg,
		'data' => $data
	];
	if (!PHP_CLI && MODULE_NAME == 'Home') {
		$return['msg'] = lang($msg);
	}
	return $return;
}

//调试方法
function p($data)
{
	echo '<pre>';
	print_r($data);
	echo '<pre>';
}

//字符串格式输出
function output($str)
{
	if (!is_string($str)) {
		$str = json_encode($str, 256);
	}
	echo date('Y-m-d H:i:s') . ':' . $str . "\n";
}

//退出程序
function doExit($str)
{
	if (APP_DEBUG) {
		exit ($str);
	}
	exit;
}

//字符串截取
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = '···')
{
	if (function_exists("mb_substr")) {
		$tmp_str = mb_substr($str, $start, $length, $charset);
		if (utf8_strlen($str) > $length && $suffix) {
			$tmp_str .= $suffix;
		}
		return $tmp_str;
	} elseif (function_exists('iconv_substr')) {
		$tmp_str = iconv_substr($str, $start, $length, $charset);
		if (utf8_strlen($str) > $length && $suffix) {
			$tmp_str .= $suffix;
		}
		return $tmp_str;
	}
	$re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
	$re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
	$re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
	$re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$slice = join("", array_slice($match[0], $start, $length));
	if (utf8_strlen($str) > $length && $suffix) {
		$slice .= $suffix;
	}
	return $slice;
}

//计算字符串长度
function utf8_strlen($string = null)
{
	//将字符串分解为单元
	preg_match_all("/./us", $string, $match);
	//返回单元个数
	return count($match[0]);
}

//单位换算
function setupSize($fileSize)
{
	$size = sprintf("%u", $fileSize);
	if ($size == 0) {
		return ("0 Bytes");
	}
	$sizename = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
	return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
}

//同步文件
function rsyncRes()
{
	//走内网
	$cmd = "/usr/bin/rsync '-e ssh -p 22' --compress -a --exclude=.svn  /www/xxx/uploads/ www@127.0.0.1:/www/xxxx/uploads/ >/dev/null";
	@exec($cmd, $info);
	return $info;
}

//二维数组排序
function arraySort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_STRING)
{
	if (is_array($arrays)) {
		foreach ($arrays as $array) {
			if (is_array($array)) {
				$key_arrays[] = $array[$sort_key];
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
	array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
	return $arrays;
}

//二维数组打乱
function shuffle_assoc($list)
{
	if (!is_array($list)) {
		return $list;
	}
	$keys = array_keys($list);
	shuffle($keys);
	$random = [];
	foreach ($keys as $key) {
		$random[$key] = $list[$key];
	}
	return $random;
}

//数据根据某个字段转换成数组
function rows2arr($data, $key = 'id')
{
	$result = [];
	foreach ($data as $dv) {
		$result[$dv[$key]] = $dv;
	}
	return $result;
}

//获取散列值
function getHash($str, $num = 5)
{
	$num = intval($num);
	if (!$num) {
		$num = 5;
	}
	$hash = sprintf('%u', crc32($str)) % $num;
	return $hash;
}

//生成邀请码
function genIcode()
{
	$icode = mt_rand(100, 999) . mt_rand(100, 999);
	$check_icode = Db::table('sys_user')->field(['id'])->where("icode='{$icode}'")->find();
	if ($check_icode['id']) {
		$icode = genIcode();
	}
	return $icode;
}

//生成验证码随机字符串
function getVarifyCode($num = 4)
{
	$str = "0123456789"; //HMWDQWERPVBNMZXC
	$code = '';
	for ($i = 0; $i < $num; $i++) {
		$code .= $str[mt_rand(0, strlen($str) - 1)];
	}
	return $code;
}

//画出图形验证码
function drawVarifyCode($code, $size = 24, $width = 100, $height = 40)
{
	$num = strlen($code);
	!$width && $width = $num * $size * 4 / 5 + 5;
	!$height && $height = $size + 10;
	$im = imagecreatetruecolor($width, $height); //画图像
	//定义要用到的颜色
	$back_color = imagecolorallocate($im, 255, 255, 255);
	$boer_color = imagecolorallocate($im, 221, 221, 221);
	$text_color = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
	//画背景
	imagefilledrectangle($im, 0, 0, $width, $height, $back_color);
	//画边框
	imagerectangle($im, 0, 0, $width - 1, $height - 1, $boer_color);
	//画干扰线
	for ($i = 0; $i < 5; $i++) {
		$font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
		imagearc($im, mt_rand(-$width, $width), mt_rand(-$height, $height), mt_rand(30, $width * 2), mt_rand(20, $height * 2), mt_rand(0, 360), mt_rand(0, 360), $font_color);
	}
	// 画干扰点
	for ($i = 0; $i < 50; $i++) {
		$font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
		imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $font_color);
	}
	// 画验证码
	@imagefttext($im, $size, 0, 10, $size + 8, $text_color, ROOT_PATH . 'public/fonts/icode.ttf', $code);
	ob_clean();
	header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
	header("Content-type: image/png;charset=gb2312");
	imagepng($im);
	imagedestroy($im);
}

//下载文件
function downloadFile($url, $savepath = '')
{
	if (!$savepath) {
		$filename = md5($url . time() . SYS_KEY);
		$savepath = 'uploads/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . getRsn($filename) . '.png';
	}
	$file_path = ROOT_PATH . $savepath;
	if (file_exists($file_path)) {
		return $savepath;
	}
	$result = curl_get($url);
	$con = $result['output'];
	if (!$con) {
		return false;
	}
	if (!is_dir(dirname($file_path))) {
		mkdir(dirname($file_path), 0755, true);
	}
	$res = file_put_contents($file_path, $con);
	if (!$res) {
		return false;
	}
	return $savepath;
}

//导出csv
function downloadCsv($filename, $str)
{
	header("Content-type:text/csv");
	header("Content-Disposition:attachment;filename=" . $filename);
	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	header('Expires:0');
	header('Pragma:public');
	echo "\xEF\xBB\xBF" . $str;
	exit;
}

//读取csv
function readCsv($file, $needIndex = false)
{
	$arr = file($file);
	$t_data = [];
	foreach ($arr as $av) {
		//$val = mb_convert_encoding($av,"UTF-8","GBK");
		$val = trim($av);
		$val = str_replace("\"", '', $val);
		$val_arr = explode(',', $val);
		$tmp_val = [];
		foreach ($val_arr as $tv) {
			$tval = trim($tv);
			$tmp_val[] = $tval;
		}
		$t_data[] = $tmp_val;
	}

	$data = [];
	$data_index = [];
	$field_idx = $t_data[0];
	foreach ($t_data as $t_key => $t_val) {
		if ($t_key == 0) {
			continue;
		}
		if ($needIndex) {
			$data_index[] = $t_val;
		}
		$tmp_ttv = [];
		foreach ($t_val as $ttk => $ttv) {
			$tmp_ttv[$field_idx[$ttk]] = $ttv;
		}
		$data[] = $tmp_ttv;
	}
	if ($needIndex) {
		return [
			'data' => $data,
			'data_field' => $field_idx,
			'data_index' => $data_index
		];
	}
	return $data;
}

//构造表单提交
function formSubmit($url, $data, $notice = '')
{
	$html = '<form id="submitForm" name="submitForm" action="' . $url . '" method="post">';
	foreach ($data as $pk => $pv) {
		$html .= '<input type="hidden" name="' . $pk . '" value="' . $pv . '"/>';
	}
	$html .= '</form>';
	$html .= '<script>document.forms["submitForm"].submit();</script>';
	$html .= empty ($notice) ? 'Submit...' : $notice;
	exit ($html);
}

//smarty模板渲染
function display($tpl, $data = array(), $return = false)
{
	$template_dir = APP_PATH . '/view/' . strtolower(MODULE_NAME) . '/';
	$path = $template_dir . $tpl;
	if (!file_exists($path)) {
		exit (lang('不存在模板'));
	}
	$smarty = new Smarty();
	$smarty->template_dir = $template_dir;
	$smarty->compile_dir = ROOT_PATH . 'cache/';
	$smarty->cache_dir = ROOT_PATH . 'cache/';
	$smarty->left_delimiter = "[[";
	$smarty->right_delimiter = "]]";
	$smarty->caching = true;
	$smarty->cache_lifetime = 300;
	if (APP_DEBUG) {
		$smarty->force_compile = true;
	}
	if (is_array($data)) {
		foreach ($data as $key => $val) {
			$smarty->assign($key, $val);
		}
	}
	if ($return) {
		return $smarty->fetch($tpl);
	} else {
		$smarty->display($tpl);
	}
	unset($smarty);
}

//curl
function curl_get($url, $timeout = 30)
{
	$arrCurlResult = [];
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //ssl检测跳过
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //ssl检测跳过
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) MicroMessenger Gecko/2008052906 Firefox/3.0'); //设置UA
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$output = curl_exec($ch);
	$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$arrCurlResult['output'] = $output; //返回结果
	$arrCurlResult['response_code'] = $responseCode; //返回http状态
	curl_close($ch);
	unset($ch);
	return $arrCurlResult;
}

//调接口替代方法
function http_fget($url, $timeout = 30)
{
	$opts = [
		'http' => [
			'method' => "GET",
			'timeout' => $timeout
		]
	];
	$res = file_get_contents($url, false, stream_context_create($opts));
	$arrCurlResult = [
		'output' => $res,
		'response_code' => 200
	];
	return $arrCurlResult;
}

function curl_post($url, $data = [], $timeout = 30, $isJosn = false)
{
	if ($isJosn == 'json') {
		$data = json_encode($data);
	}
	$arrCurlResult = [];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); //ssl检测跳过
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_REFERER, "");
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) MicroMessenger Gecko/2008052906 Firefox/3.0'); //设置UA
	if ($isJosn == 'json') {
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data)
		]);
	}
	$output = curl_exec($ch);
	$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$arrCurlResult['output'] = $output; //返回结果
	$arrCurlResult['response_code'] = $responseCode; //返回http状态
	curl_close($ch);
	unset($ch);
	return $arrCurlResult;
}

function curl_post2($url, $data = [], $timeout = 30, $header = [])
{
	$arrCurlResult = [];
	$curl = new Curl();
	$curl->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) MicroMessenger Gecko/2008052906 Firefox/3.0)');
	//$curl->setHeader('Content-Type', 'application/json');
	if ($header) {
		foreach ($header as $hk => $hv) {
			$curl->setHeader($hk, $hv);
		}
	} else {
		$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
	}
	$curl->setTimeout($timeout);
	$curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
	$curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
	$curl->post($url, $data);
	if ($curl->error) {
		$arrCurlResult = [
			'code' => -1,
			'msg' => $curl->errorMessage
		];
	} else {
		$arrCurlResult = [
			'code' => 1,
			'msg' => 'ok',
			'output' => (array) $curl->response
		];
	}
	$curl->close();
	return $arrCurlResult;
}




///////////////////////////////////////////////////////////
/*
 * 获取指定目录下指定文件后缀的函数
 * @$path   文件路径
 * @$ext    文件后缀名，默认为false为不指定，如果指定，请以数组方式传入
 * @$filename   使用时请提前赋值为空数组
 * @$recursive  是否递归查找，默认为false
 * @$baseurl    是否包含路径，默认包含
 */
function getDirFileList($path, &$filename, $recursive = false, $ext = false, $baseurl = true)
{
	if (!$path) {
		die ('请传入目录路径');
	}
	$path = trim($path, '/');
	$resource = opendir($path);
	if (!$resource) {
		die ('传入的目录不正确');
	}
	//遍历目录
	while ($rows = readdir($resource)) {
		//如果指定为递归查询
		if ($recursive) {
			if (is_dir($path . '/' . $rows) && $rows != "." && $rows != "..") {
				getDirFileList($path . '/' . $rows, $filename, $resource, $ext, $baseurl);
			} elseif ($rows != "." && $rows != "..") {
				//如果指定后缀名
				if ($ext) {
					//必须为数组
					if (!is_array($ext)) {
						die ('后缀名请以数组方式传入');
					}
					//转换小写
					foreach ($ext as &$v) {
						$v = strtolower($v);
					}
					//匹配后缀
					$file_ext = strtolower(pathinfo($rows)['extension']);
					if (in_array($file_ext, $ext)) {
						//是否包含路径
						if ($baseurl) {
							$filename[] = $path . '/' . $rows;
						} else {
							$filename[] = $rows;
						}
					}
				} else {
					if ($baseurl) {
						$filename[] = $path . '/' . $rows;
					} else {
						$filename[] = $rows;
					}
				}
			}
		} else {
			//非递归查询
			if (is_file($path . '/' . $rows) && $rows != "." && $rows != "..") {
				if ($baseurl) {
					$filename[] = $path . '/' . $rows;
				} else {
					$filename[] = $rows;
				}
			}
		}
	}
}

///////////////////////////////////////////////////////////

//图片缩略 支持圆角
function scaleImg($srcImage, $desImage, $maxwidth, $maxheight, $radius = 0)
{
	$info = getimagesize($srcImage);
	$width = $info[0];
	$height = $info[1];
	if (!$info) {
		return false;
	}
	switch ($info['mime']) {
		case 'image/gif':
			$img = imagecreatefromgif($srcImage);
			break;
		case 'image/jpeg':
			$img = imagecreatefromjpeg($srcImage);
			break;
		case 'image/png':
			$img = imagecreatefrompng($srcImage);
			break;
		default:
			$img = imagecreatefrompng($srcImage);
			break;
	}
	$canvas = imagecreatetruecolor($maxwidth, $maxheight); // 创建一个真彩色图像 我把它理解为创建了一个画布
	$alpha = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
	imagefill($canvas, 0, 0, $alpha);
	imagecopyresampled($canvas, $img, 0, 0, 0, 0, $maxwidth, $maxheight, $width, $height);
	$extend = explode(".", $srcImage);
	$attach_fileext = strtolower($extend[count($extend) - 1]);
	if (!in_array($attach_fileext, array('jpg', 'png', 'jpeg'))) {
		return false;
	}
	if (file_exists($desImage)) {
		return $desImage;
	}

	if ($radius > 0) {
		imagejpeg($canvas, $desImage, 100, $alpha);
		$desImage = radiusImg($desImage, $radius, $desImage);
	} else {
		imagejpeg($canvas, $desImage, 100, $alpha);
	}
	imagedestroy($canvas);
	imagedestroy($img);
	return $desImage;
}

//图片加圆角
function radiusImg($imgpath, $radius = 10, $despath = '')
{
	$info = getimagesize($imgpath);
	$src_img = null;
	switch ($info['mime']) {
		case 'image/jpeg':
			$src_img = imagecreatefromjpeg($imgpath);
			break;
		case 'image/png':
			$src_img = imagecreatefrompng($imgpath);
			break;
	}
	if (!$src_img) {
		return false;
	}
	$w = $info[0];
	$h = $info[1];
	//$radius=$radius == 0 ? (min($w, $h)/2):$radius;
	$img = imagecreatetruecolor($w, $h);
	imagesavealpha($img, true);
	//拾取一个完全透明的颜色,最后一个参数127为全透明
	$bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
	imagefill($img, 0, 0, $bg);
	$r = $radius; //圆 角半径
	for ($x = 0; $x < $w; $x++) {
		for ($y = 0; $y < $h; $y++) {
			$rgbColor = imagecolorat($src_img, $x, $y);
			if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
				//不在四角的范围内,直接画
				imagesetpixel($img, $x, $y, $rgbColor);
			} else {
				//在四角的范围内选择画
				//上左
				$y_x = $r; //圆心X坐标
				$y_y = $r; //圆心Y坐标
				if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
				//上右
				$y_x = $w - $r; //圆心X坐标
				$y_y = $r; //圆心Y坐标
				if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
				//下左
				$y_x = $r; //圆心X坐标
				$y_y = $h - $r; //圆心Y坐标
				if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
				//下右
				$y_x = $w - $r; //圆心X坐标
				$y_y = $h - $r; //圆心Y坐标
				if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
			}
		}
	}
	if (!is_resource($img)) {
		return false;
	}
	if ($despath) {
		imagepng($img, $despath);
		imagedestroy($img);
		return $despath;
	}
	return $img;
}

///////////////////////////////////////////////////////////

//是否是微信客户端
function isWx()
{
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
		return true;
	}
	return false;
}

//检测手机
function isPhone($tel, $type = 'sj')
{
	return preg_match("/^[1-9][0-9]{9}$/i", $tel);
	$regxArr = [
		'sj' => '/^(\+?86-?)?(18|19|16|15|13|17|14)[0-9]{9}$/',
		'tel' => '/^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$/',
		'400' => '/^400(-\d{3,4}){2}$/'
	];
	if ($type && isset ($regxArr[$type])) {
		return preg_match($regxArr[$type], $tel) ? true : false;
	}
	foreach ($regxArr as $regx) {
		if (preg_match($regx, $tel)) {
			return true;
		}
	}
	return false;
}

//检测邮箱
function isEmail($email)
{
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return false;
	}
	$email_arr = explode('@', $email);
	$domain = array_pop($email_arr);
	if (checkdnsrr($domain, 'MX') === false) {
		return false;
	}
	return true;
}

//判断是否是金额
function isMoney($val)
{
	if ($val === '') {
		return false;
	}
	if ($val < 0) {
		return false;
	} else if ($val == 0) {
		return true;
	} else {
		if (!filter_var($val, FILTER_VALIDATE_FLOAT)) {
			return false;
		}
	}
	return true;
}

//判断是否是ajax请求 同时满足是ajax和post请求才算是ajax，异步统一使用post提交数据
function isAjax()
{
	$isAjax = isset ($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && strtolower($_SERVER['REQUEST_METHOD']) == 'post';
	return $isAjax;
}

//是否是移动端请求
function isMobileReq()
{
	$_SERVER['ALL_HTTP'] = isset ($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
	$mobile_browser = '0';
	if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
		$mobile_browser++;
	if ((isset ($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false))
		$mobile_browser++;
	if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
		$mobile_browser++;
	if (isset ($_SERVER['HTTP_PROFILE']))
		$mobile_browser++;
	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
	$mobile_agents = [
		'w3c ',
		'acs-',
		'alav',
		'alca',
		'amoi',
		'audi',
		'avan',
		'benq',
		'bird',
		'blac',
		'blaz',
		'brew',
		'cell',
		'cldc',
		'cmd-',
		'dang',
		'doco',
		'eric',
		'hipt',
		'inno',
		'ipaq',
		'java',
		'jigs',
		'kddi',
		'keji',
		'leno',
		'lg-c',
		'lg-d',
		'lg-g',
		'lge-',
		'maui',
		'maxo',
		'midp',
		'mits',
		'mmef',
		'mobi',
		'mot-',
		'moto',
		'mwbp',
		'nec-',
		'newt',
		'noki',
		'oper',
		'palm',
		'pana',
		'pant',
		'phil',
		'play',
		'port',
		'prox',
		'qwap',
		'sage',
		'sams',
		'sany',
		'sch-',
		'sec-',
		'send',
		'seri',
		'sgh-',
		'shar',
		'sie-',
		'siem',
		'smal',
		'smar',
		'sony',
		'sph-',
		'symb',
		't-mo',
		'teli',
		'tim-',
		'tosh',
		'tsm-',
		'upg1',
		'upsi',
		'vk-v',
		'voda',
		'wap-',
		'wapa',
		'wapi',
		'wapp',
		'wapr',
		'webc',
		'winw',
		'winw',
		'xda',
		'xda-'
	];
	if (in_array($mobile_ua, $mobile_agents))
		$mobile_browser++;
	if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
		$mobile_browser++;
	// Pre-final check to reset everything if the user is on Windows
	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
		$mobile_browser = 0;
	// But WP7 is also Windows, with a slightly different characteristic
	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
		$mobile_browser++;
	if ($mobile_browser > 0)
		return true;
	else
		return false;
}

function isIdcard($id)
{
	$id = strtoupper($id);
	$regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
	$arr_split = [];
	if (!preg_match($regx, $id)) {
		return false;
	}
	if (15 == strlen($id)) { //检查15位
		$regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
		@preg_match($regx, $id, $arr_split);
		//检查生日日期是否正确
		$dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
		if (!strtotime($dtm_birth)) {
			return false;
		} else {
			return true;
		}
	} else { //检查18位 
		$regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
		@preg_match($regx, $id, $arr_split);
		$dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
		if (!strtotime($dtm_birth)) {
			return false;
		} else { //检查生日日期是否正确
			//检验18位身份证的校验码是否正确。
			//校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
			$arr_int = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
			$arr_ch = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
			$sign = 0;
			for ($i = 0; $i < 17; $i++) {
				$b = (int) $id[$i];
				$w = $arr_int[$i];
				$sign += $b * $w;
			}
			$n = $sign % 11;
			$val_num = $arr_ch[$n];
			if ($val_num != substr($id, 17, 1)) {
				return false;
			} else {
				return true;
			}
		}
	}
}

///////////////////////////////////////////////////////////
//根据时间戳获取年龄
function getAge($birthTime, $needArr = false)
{
	$before = $birthTime;
	$after = time();
	if ($before > $after) {
		$b = getdate($after);
		$a = getdate($before);
	} else {
		$b = getdate($before);
		$a = getdate($after);
	}
	$n = [1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31];
	$y = $m = $d = 0;
	if ($a['mday'] >= $b['mday']) { //天相减为正
		if ($a['mon'] >= $b['mon']) { //月相减为正
			$y = $a['year'] - $b['year'];
			$m = $a['mon'] - $b['mon'];
		} else { //月相减为负，借年
			$y = $a['year'] - $b['year'] - 1;
			$m = $a['mon'] - $b['mon'] + 12;
		}
		$d = $a['mday'] - $b['mday'];
	} else { //天相减为负，借月
		if ($a['mon'] == 1) { //1月，借年
			$y = $a['year'] - $b['year'] - 1;
			$m = $a['mon'] - $b['mon'] + 12;
			$d = $a['mday'] - $b['mday'] + $n[12];
		} else {
			if ($a['mon'] == 3) { //3月，判断闰年取得2月天数
				$d = $a['mday'] - $b['mday'] + ($a['year'] % 4 == 0 ? 29 : 28);
			} else {
				$d = $a['mday'] - $b['mday'] + $n[$a['mon'] - 1];
			}
			if ($a['mon'] >= $b['mon'] + 1) { //借月后，月相减为正
				$y = $a['year'] - $b['year'];
				$m = $a['mon'] - $b['mon'] - 1;
			} else { //借月后，月相减为负，借年
				$y = $a['year'] - $b['year'] - 1;
				$m = $a['mon'] - $b['mon'] + 12 - 1;
			}
		}
	}
	if ($needArr) {
		return ['y' => $y, 'm' => $m, 'd' => $d];
	}
	return ($y == 0 ? '' : $y . '岁') . ($m == 0 ? '' : $m . '月') . ($d == 0 ? '' : $d . '天');
}
