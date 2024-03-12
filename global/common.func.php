<?php
use think\facade\Db;
use Zxing\QrReader;


//生成二维码
function genQrcode($str)
{
	$qrcode = 'uploads/qrcode/' . date('Ym') . '/' . getRsn($str) . '.jpg';
	$qrcode_path = ROOT_PATH . $qrcode;
	if (!file_exists($qrcode_path)) {
		if (!is_dir(dirname($qrcode_path))) {
			mkdir(dirname($qrcode_path), 0755, true);
		}
		QRcode::png($str, $qrcode_path, 'H', 14, 0);
	}
	return $qrcode;
}

//获取所有币种
function getCurrency($id = 0)
{
	$where = '';
	$id = intval($id);
	if ($id) {
		$where = "id={$id} and status=2";
	} else {
		$where = "status=2";
	}

	$currency_arr = Db::table('cnf_currency')->where($where)->select()->toArray();
	if ($id) {
		if (count($currency_arr) > 0) {
			return $currency_arr[0];
		} else {
			return [];
		}
	}
	return $currency_arr;
}


//创建钱包
function createWallet($uid, $cid = 0)
{
	$uid = intval($uid);
	$user = Db::table('sys_user')->field(['id', 'status'])->where("id={$uid}")->find();
	if (!$user) {
		return false;
	}
	$currency_arr = getCurrency();
	Db::startTrans();
	try {
		foreach ($currency_arr as $cv) {
			$db_item = [
				'uid' => $user['id'],
				'waddr' => getRsn(),
				'cid' => $cv['id'],
				'create_time' => time()
			];
			Db::table('wallet_list')->insertGetId($db_item);
		}
	} catch (\Exception $e) {
		Db::rollback();
		return false;
	}
	Db::commit();
}
function createWalletBycid($uid, $cid = 0)
{
	$uid = intval($uid);
	$user = Db::table('sys_user')->field(['id', 'status'])->where("id={$uid}")->find();
	if (!$user) {
		return false;
	}

	Db::startTrans();
	try {
		$db_item = [
			'uid' => $user['id'],
			'waddr' => getRsn(),
			'cid' => $cid,
			'create_time' => time()
		];
		Db::table('wallet_list')->insertGetId($db_item);
	} catch (\Exception $e) {
		Db::rollback();
		return false;
	}
	Db::commit();
}
//获取用户的钱包
function getWallet($uid, $cid = 0, $type = '')
{
	$uid = intval($uid);
	if (!$uid) {
		return [];
	}
	$where = "uid={$uid}";
	$cid = intval($cid);
	if ($cid) {
		$where .= " and cid={$cid}";
	}
	$list = Db::table('wallet_list')->where($where)->select()->toArray();
	if (!$list) {
		createWalletBycid($uid, $cid);
		if ($type != '') {
			return [];
		}
		return getWallet($uid, $cid, '1');
	}
	if ($cid) {
		return $list[0];
	}
	return $list;
}

//钱包流水日志
function walletLog($pdata = [])
{
	$pdata['wid'] = intval($pdata['wid']);
	$pdata['uid'] = intval($pdata['uid']);
	if (!$pdata['wid'] || !$pdata['uid']) {
		return false;
	}
	$type = intval($pdata['type']);
	$cnf_balance_type = getConfig('cnf_balance_type');
	if (!array_key_exists($type, $cnf_balance_type)) {
		return false;
	}
	$pageuser = checkLogin();
	if (!$pageuser) {
		$pageuser = [];
	}
	$now_time = time();
	$db_item = [
		'wid' => $pdata['wid'],
		'uid' => $pdata['uid'],
		'type' => $type,
		'fkey' => $pdata['fkey'],
		'money' => floatval($pdata['money']),
		'create_time' => $now_time,
		'create_day' => date('Ymd', $now_time),
		'create_id' => intval($pageuser['id']),
		'remark' => $pdata['remark'],
		'ori_balance' => $pdata['ori_balance'],
		'new_balance' => $pdata['new_balance']
	];
	try {
		$res = Db::table('wallet_log')->insertGetId($db_item);
	} catch (\Exception $e) {
		return false;
	}
	$db_item['id'] = $res;
	return $db_item;
}

//获取用户的收款方式
function getBanklog($uid, $types = 0)
{
	$uid = intval($uid);
	$pageSize = 50;
	$page = 1;
	$where = "log.uid={$uid} and log.status=2";
	if ($types) {
		if (is_array($types)) {
			$types_arr = [];
			foreach ($types as $tv) {
				$tv = intval($tv);
				if ($tv) {
					$types_arr[] = $tv;
				}
			}
			if ($types_arr) {
				$types_str = implode(',', $types_arr);
				$where .= " and log.type in ({$types_str})";
			} else {
				$where .= " and log.type=0";
			}
		} else {
			$types = intval($types);
			$where .= " and log.type={$types}";
		}
	}
	$list = Db::view(['cnf_banklog' => 'log'], ['id', 'type', 'realname', 'account', 'bank_id', 'qrcode'])
		->view(['cnf_bank' => 'bk'], ['name' => 'bank_name'], 'log.bank_id=bk.id', 'LEFT')
		//->view(['cnf_area'=>'p'],['name'=>'province_name'],'log.province_id=p.id','LEFT')
		//->view(['cnf_area'=>'c'],['name'=>'city_name'],'log.city_id=c.id','LEFT')
		->where($where)
		->order(['log.sort' => 'desc', 'log.id' => 'desc'])
		->page($page, $pageSize)
		->select()
		->toArray();
	if (!$list) {
		$list = [];
	}
	$cnf_banklog_type = getConfig('cnf_banklog_type');
	foreach ($list as &$item) {
		$item['type_flag'] = $cnf_banklog_type[$item['type']];
	}
	return $list;
}
function updateWalletBalanceAndLog($id, $money, $cid, $type, $remark)
{
	$wallet = Db::table('wallet_list')->where('uid=' . $id . ' and cid=' . $cid)->lock(true)->find();
	if ($wallet == []) {
		$id = Db::table('wallet_list')->insertGetId(['uid' => $id, 'waddr' => getRsn(), 'cid' => $cid, 'balance' => 0, 'create_time' => time(),]);
		$wallet = Db::table('wallet_list')->where('id=' . $id)->lock(true)->find();
	}
	$oriBalance = $wallet['balance'];
	$newBalance = $wallet['balance'] + $money;
	$wallet_data = ['balance' => $newBalance];
	$result = Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
	walletLog([
		'wid' => $wallet['id'],
		'uid' => $id,
		'type' => $type,
		'money' => $money,
		'ori_balance' => $oriBalance,
		'new_balance' => $newBalance,
		'fkey' => '',
		'remark' => $remark
	]);
	if (!$result) {
		throw new \Exception('流水记录写入失败');
	}
}

//账变记录
function balanceLog($user, $balanceType, $subType, $money, $fkey = '', $remark = '')
{
	if (!$user['id']) {
		return false;
	}

	$subType = intval($subType);
	$cnf_balance_type = getConfig('cnf_balance_type');
	if (!array_key_exists($subType, $cnf_balance_type)) {
		return false;
	}

	$pageuser = checkLogin();
	if (!$pageuser) {
		$pageuser = [];
	}
	$now_time = time();
	$fin_balance_log = [
		'uid' => intval($user['id']),
		'btype' => $balanceType,
		'type' => $subType,
		'fkey' => $fkey,
		'money' => $money,
		'create_time' => $now_time,
		'create_day' => date('Ymd', $now_time),
		'create_id' => intval($pageuser['id']),
		'remark' => $remark
	];
	if ($balanceType == 1) {
		$fin_balance_log['ori_balance'] = $user['balance'];
		$fin_balance_log['new_balance'] = $user['balance'] + $money;
	} elseif ($balanceType == 2) {
		$fin_balance_log['ori_balance'] = $user['fz_balance'];
		$fin_balance_log['new_balance'] = $user['fz_balance'] + $money;
	} else {
		return false;
	}
	$res = Db::table('fin_balance_log')->insertGetId($fin_balance_log);
	if (!$res) {
		return false;
	}
	$fin_balance_log['id'] = $res;
	return $fin_balance_log;
}