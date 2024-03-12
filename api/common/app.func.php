<?php

use think\facade\Db;

//获取上级代理
function getAgents($uid)
{
	$uid = intval($uid);
	$up_user = getUpUser($uid, true);
	$zt_account = '/';
	$agent1_account = '/';
	$agent2_account = '/';
	foreach ($up_user as $uk => $uv) {
		if ($uk == 0) {
			$zt_account = $uv['account'];
		}
		if ($uv['gid'] == 71) {
			$agent1_account = $uv['account'];
		} elseif ($uv['gid'] == 81) {
			$agent2_account = $uv['account'];
		}
	}
	$data = [
		'zt_account' => $zt_account,
		'agent1_account' => $agent1_account,
		'agent2_account' => $agent2_account
	];
	return $data;
}

//增加代金券记录
function addCouponLog($uid, $couponId, $num = 1, $remark = '')
{
	$user = checkLogin();
	$uid = intval($uid);
	$couponId = intval($couponId);
	$num = intval($num);
	if (!$uid || !$couponId || $num < 1) {
		return false;
	}
	Db::startTrans();
	try {
		$item = Db::table('coupon_list')->where("id={$couponId}")->lock(true)->find();
		if (!$item || $item['status'] >= 99) {
			throw new \Exception('不存在相应的代金券');
		}
		if ($item['stock_num'] < $num) {
			throw new \Exception('库存不足');
		}
		$db_item = [
			'stock_num' => $item['stock_num'] - $num,
			'receive_num' => $item['receive_num'] + $num
		];
		Db::table('coupon_list')->where("id={$item['id']}")->update($db_item);
		$now_time = time();
		$coupon_log = [
			'gids' => $item['gids'],
			'cid' => $item['id'],
			'type' => $item['type'],
			'uid' => $uid,
			'num' => $num,
			'discount' => $item['discount'],
			'money' => $item['money'],
			'create_time' => $now_time,
			'create_day' => date('Ymd', $now_time),
			'create_id' => $user ? $user['id'] : 0,
			'effective_time' => $item['qx'] == 0 ? $item['effective_time'] : (time() + ($item['qx'] * 60 * 24 * 60)),
			'remark' => $remark
		];

		Db::table('coupon_log')->insertGetId($coupon_log);
		Db::commit();
	} catch (\Exception $e) {
		Db::rollback();
		return $e->getMessage();
	}
	return true;
}


