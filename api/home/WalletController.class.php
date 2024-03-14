<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class WalletController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$where = "log.uid={$pageuser['id']}";
		$count_item = Db::table('wallet_list log')
			->leftJoin('cnf_currency c', 'log.cid=c.id')
			->fieldRaw('count(1) as cnt,sum(log.balance) as balance,sum(log.fz_balance) as fz_balance')
			->where($where)
			->find();

		$list = Db::view(['wallet_list' => 'log'], ['waddr', 'balance', 'fz_balance'])
			->view(['cnf_currency' => 'c'], ['name' => 'currency', 'symbol', 'icon'], 'log.cid=c.id', 'LEFT')
			->where($where)
			->order(['log.cid' => 'asc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$price = 1;
		$total_balance = 0.00;
		foreach ($list as &$item) {
			if ($item['cid'] == 11) {
				$item['total_balance'] = $item['balance'] + $item['fz_balance'];
			} else {
				$item['total_balance'] = $item['balance'] * $price + $item['fz_balance'] * $price;
			}
			$item['total_balance'] = number_format($item['total_balance'], 2, '.', '');
			$total_balance += $item['total_balance'];
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'balance' => floatval($count_item['balance']),
			'fz_balance' => floatval($count_item['fz_balance']),
			'total_balance' => number_format($total_balance, 2, '.', ''),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['currency_arr'] = getCurrency();
			$return_data['qrcode'] = genQrcode($pageuser['id']);
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//转账
	public function _trans()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$currency = intval($params['currency']);
		$account = $params['account'];
		$quantity = floatval($params['quantity']);
		if (!$currency) {
			ReturnToJson(-1, '请选择币种');
		}
		if (!$account) {
			ReturnToJson(-1, '请填写接收方账号或ID');
		}
		if ($quantity < 0.01) {
			ReturnToJson(-1, '转账数量不正确');
		}
		$wallet = getPset('wallet');
		if ($quantity < $wallet['transfer']['min']) {
			ReturnToJson(-1, '最小转账数量为 ' . $wallet['transfer']['min']);
		}
		if ($quantity > $wallet['transfer']['max']) {
			ReturnToJson(-1, '最大转账数量为 ' . $wallet['transfer']['max']);
		}
		if (getPassword($params['password2']) != $pageuser['password2']) {
			ReturnToJson(-1, '二级密码不正确');
		}
		$currency_item = Db::table('cnf_currency')->where("id={$currency}")->find();
		if (!$currency_item) {
			ReturnToJson(-1, '不存在相应的币种');
		}

		$myOrder = Db::table('nft_order')->where("uid={$pageuser['id']} and is_experience=0")->find();
		if (!$myOrder) {
			ReturnToJson(-1, '未购买过卡片无法转账');
		}

		//检测对方账号
		$to_user = [];
		if (strlen($account) == 6) {
			$account = intval($account);
			$tuid = intval($account);
			$to_user = Db::table('sys_user')->field(['id', 'account', 'nickname'])->where("id={$account}")->find();
		} else {
			$to_user = Db::table('sys_user')->field(['id', 'account', 'nickname'])->whereRaw("account=:acc", ['acc' => $account])->find();
		}
		if (!$to_user) {
			ReturnToJson(-1, '请填写正确的账号或ID');
		}

		$toOrder = Db::table('nft_order')->where("uid={$to_user['id']} and is_experience=0")->find();
		if (!$toOrder) {
			ReturnToJson(-1, '接收方未购买过卡片无法转账');
		}

		Db::startTrans();
		try {
			//###自己###
			$wallet = getWallet($pageuser['id'], $currency_item['id']);
			if (!$wallet) {
				throw new \Exception('钱包获取异常');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] - $quantity
			];
			if ($wallet_data['balance'] < 0) {
				ReturnToJson(-1, '您的余额不足');
			}
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 2,
				'money' => $quantity,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $to_user['id'],
				'remark' => '转出-' . $to_user['nickname']
			]);
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}

			//###对方###
			$wallet2 = getWallet($to_user['id'], $currency_item['id']);
			if (!$wallet2) {
				throw new \Exception('对方钱包获取异常');
			}
			$wallet2 = Db::table('wallet_list')->where("id={$wallet2['id']}")->lock(true)->find();
			$wallet_data2 = [
				'balance' => $wallet2['balance'] + $quantity
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
			//写入流水记录
			$result2 = walletLog([
				'wid' => $wallet2['id'],
				'uid' => $wallet2['uid'],
				'type' => 1,
				'money' => $quantity,
				'ori_balance' => $wallet2['balance'],
				'new_balance' => $wallet_data2['balance'],
				'fkey' => $pageuser['id'],
				'remark' => '转入-' . $pageuser['nickname']
			]);
			if (!$result2) {
				throw new \Exception('流水记录写入失败2');
			}

			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '转账成功');
	}

	//钱包详情
	public function _info()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		$params['s_type'] = intval($params['s_type']);
		if (!$params['waddr']) {
			ReturnToJson(-1, '缺少参数');
		}

		$where = "log.uid={$pageuser['id']} and w.waddr='{$params['waddr']}'";
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		$where .= empty($params['s_type']) ? '' : " and log.type={$params['s_type']}";
		$count_item = Db::table('wallet_log log')
			->leftJoin('wallet_list w', 'log.wid=w.id')
			->leftJoin('cnf_currency c', 'w.cid=c.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();

		$list = Db::view(['wallet_log' => 'log'], ['type', 'money', 'ori_balance', 'new_balance', 'create_time', 'remark'])
			->view(['wallet_list' => 'w'], ['cid'], 'log.wid=w.id', 'LEFT')
			->view(['cnf_currency' => 'c'], ['name' => 'currency', 'symbol', 'icon'], 'w.cid=c.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$cnf_balance_type = getConfig('cnf_balance_type');
		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			$item['type_flag'] = $cnf_balance_type[$item['type']];
			if (in_array($item['type'], [2, 4, 12, 14])) {
				$item['money_flag'] = '-' . $item['money'];
			} else {
				$item['money_flag'] = '+' . $item['money'];
			}
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => floatval($count_item['money']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$wallet = Db::view(['wallet_list' => 'log'], ['waddr', 'cid', 'balance', 'fz_balance'])
				->view(['cnf_currency' => 'c'], ['name' => 'currency', 'symbol', 'icon'], 'log.cid=c.id', 'LEFT')
				->where("log.waddr='{$params['waddr']}'")->find();
			$price = 1;
			if ($wallet['cid'] != 11) {
				$wallet['balance'] = number_format($wallet['balance'] * $price, 2, '.', '');
				$wallet['fz_balance'] = number_format($wallet['fz_balance'] * $price, 2, '.', '');
			}
			$wallet['total_balance'] = number_format($wallet['balance'] + $wallet['fz_balance'], 2, '.', '');
			$return_data['wallet'] = $wallet;

			$return_data['type_arr'] = getConfig('cnf_balance_type');
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//转入余额宝
	public function _receiveyebin()
	{
		$pageuser = checkLogin();
		if (!$pageuser) {
			ReturnToJson(-1, '缺少参数');
		}
		$params = $this->params;
		if (!$params['ye']) {
			ReturnToJson(-1, '缺少参数');
		}
		$yeb = $params['ye'];
		// if ($yeb < 500) {
		// 	ReturnToJson(-1, '缺少参数');
		// }
		$now_time = NOW_TIME;
		$now_day = date('Ymd', NOW_TIME);

		/*
		51: "余额宝转入"
		52: "余额宝转出"
		53: "余额宝收益"
		*/
		$fid = 0;
		$wwere = " uid='{$pageuser['id']}' and cid='3'";
		$wallet3 = Db::table('wallet_list')->where(" uid='{$pageuser['id']}' and cid='3'")->lock(true)->find();
		if (!$wallet3) {
			$db_item = [
				'uid' => $pageuser['id'],
				'waddr' => getRsn(),
				'cid' => '3',
				'create_time' => time(),
				'balance' => 0,
				'fz_balance' => 0
			];
			$fid =	Db::table('wallet_list')->insertGetId($db_item);
			$wallet3 = Db::table('wallet_list')->where($wwere)->lock(true)->find();
		}
		if (!$wallet3) {
			throw new \Exception('钱包获取异常'); //. $fid . '|==|' . var_export($pageuser) . var_export($wallet3)
		}
		$time = date("Ymd", time());
		Db::startTrans();
		try {

			$wallet = getWallet($pageuser['id'], 2);
			if (!$wallet) {
				throw new \Exception('钱包获取异常');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			if ($wallet['balance'] < $yeb) {
				ReturnToJson(-1, 'Sorry, your credit is running low');
			}
			$wallet_data = [
				'balance' => $wallet['balance'] - $yeb,
				'lasttime' => $time
			];
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 52,
				'money' => -$yeb,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => '',
				'remark' => 'RS:-' . $yeb
			]);
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}

			$wallet3_data = [
				'balance' => $wallet3['balance'] + $yeb,
				'lasttime' => $time
			];


		


			//写入流水记录
			$result = walletLog([
				'wid' => $wallet3['id'],
				'uid' => $wallet3['uid'],
				'type' => 51,
				'money' => $yeb,
				'ori_balance' => $wallet3['balance'],
				'new_balance' => $wallet3_data['balance'],
				'fkey' => '',
				'remark' => 'RS:+' . $yeb
			]);
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			Db::table('wallet_list')->where("id={$wallet3['id']}")->update($wallet3_data);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
			//ReturnToJson(-1, var_export($e));
		}
		ReturnToJson(1, 'successfully');
	}


	//转出余额宝
	public function _receiveyebout()
	{
		$pageuser = checkLogin();
		if (!$pageuser) {
			ReturnToJson(-1, '缺少参数');
		}
		$params = $this->params;
		if (!$params['ye']) {
			ReturnToJson(-1, '缺少参数');
		}
		$yeb = $params['ye'];
		// if ($yeb < 500) {
		// 	ReturnToJson(-1, '缺少参数');
		// }
		$now_time = NOW_TIME;
		$now_day = date('Ymd', NOW_TIME);
		$time = date("Ymd", time());
		/*
		51: "余额宝转入"
		52: "余额宝转出"
		53: "余额宝收益"
		*/
		$fid = 0;
		$wwere = " uid='{$pageuser['id']}' and cid='3'";
		$wallet3 = Db::table('wallet_list')->where(" uid='{$pageuser['id']}' and cid='3'")->lock(true)->find();
		if (!$wallet3) {
			$db_item = [
				'uid' => $pageuser['id'],
				'waddr' => getRsn(),
				'cid' => '3',
				'create_time' => time(),
				'balance' => 0,
				'fz_balance' => 0
			];
			$fid =	Db::table('wallet_list')->insertGetId($db_item);
			$wallet3 = Db::table('wallet_list')->where($wwere)->lock(true)->find();
		}
		if (!$wallet3) {
			throw new \Exception('钱包获取异常'); //. $fid . '|==|' . var_export($pageuser) . var_export($wallet3)
		}

		Db::startTrans();
		try {

			$wallet = getWallet($pageuser['id'], 2);
			if (!$wallet) {
				throw new \Exception('钱包获取异常');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			if ($wallet3['balance'] < $yeb) {
				ReturnToJson(-1, 'Sorry, your credit is running low');
			}
			$wallet_data = [
				'balance' => $wallet3['balance'] - $yeb,
				'lasttime' => $time
			];
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet3['id'],
				'uid' => $wallet3['uid'],
				'type' => 52,
				'money' => -$yeb,
				'ori_balance' => $wallet3['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => '',
				'remark' => 'RS:-' . $yeb
			]);
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}

			$wallet3_data = [
				'balance' => $wallet['balance'] + $yeb,
				'lasttime' => $time
			];

			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 51,
				'money' => $yeb,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet3_data['balance'],
				'fkey' => '',
				'remark' => 'RS:+' . $yeb
			]);
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet3['id']}")->update($wallet_data);
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet3_data);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
			//ReturnToJson(-1, var_export($e));
		}
		ReturnToJson(1, 'successfully');
	}

	//读取余额宝
	public function _getyeb()
	{
		$pageuser = checkLogin();
		if (!$pageuser) {
			ReturnToJson(-1, '缺少参数');
		}
		/*
		1.余额 ok
		2.昨天收益
		3.累计收益
		4.收益率

		*/
		try {
			$wwere = " uid='{$pageuser['id']}' and cid='3'";
			$wallet3 = Db::table('wallet_list')->where(" uid='{$pageuser['id']}' and cid='3'")->find();
			if (!$wallet3) {
				$db_item = [
					'uid' => $pageuser['id'],
					'waddr' => getRsn(),
					'cid' => '3',
					'create_time' => time(),
					'balance' => 0,
					'fz_balance' => 0
				];
				$fid =	Db::table('wallet_list')->insertGetId($db_item);
				$wallet3 = Db::table('wallet_list')->where($wwere)->lock(true)->find();
			}
			if (!$wallet3) $wallet3['balance'] = 0;

			$tomorrow = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
			$time = date("Ymd", $tomorrow);

			$yesterday = Db::table('wallet_log')->where(" uid='{$pageuser['id']}' and type='53' and create_day='{$time}'")->find();
			if (!$yesterday) $yesterday['money'] = 0;

			$all = Db::table('wallet_log')->where(" uid='{$pageuser['id']}' and type='53'")->sum("money");
			$ral = getConfig('yeb_syl');
			//写入流水记录
			$result =  [
				'balance' => $wallet3['balance'],
				'yesterday' => $yesterday['money'],
				'all' => $all,
				'ral' => ($ral ?? '8.5') . '%',
			];
			ReturnToJson(1, 'success', $result);
		} catch (\Exception $th) {
			ReturnToJson(-1, 'error');
		}
	}
}
