<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class GiftController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		echo 'ok';
	}

	public function _turntable()
	{
		$pageuser = checkLogin();
		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->field(['lottery'])->find();
		$prize_arr = Db::table('gift_prize')->where("1=1")->field(['id', 'name', 'cover', 'remark'])->select()->toArray();
		$project = getPset('project');
		$return_data = [
			'user' => $user,
			'prize_arr' => $prize_arr,
			'tip' => $project['drawtip'] ? nl2br($project['drawtip']) : ''
		];
		jReturn(1, 'ok', $return_data);
	}

	public function _turntableAct()
	{
		$pageuser = checkLogin();
		$tipMsg = 'ok';
		Db::startTrans();
		try {
			$lottery = Db::table('gift_lottery')->where("id=1")->find();
			if (!$lottery || $lottery['status'] != 3) {
				jReturn(-1, 'Stay tuned');
			}
			$now_day = date('Ymd');
			if ($lottery['day_limit'] > 0) {
				$check_day = Db::table('gift_prize_log')->where("uid={$pageuser['id']} and create_day={$now_day}")->count('id');
				if ($check_day >= $lottery['day_limit']) {
					jReturn(-1, 'The number of times to lottery has reached the maximum today');
				}
			}
			$pro_order = $user = Db::table('pro_order')->where(" uid={$pageuser['id']} and is_give=0")->count();  
			if ($pro_order == 0) {
				jReturn(-1, 'Please participate in the lottery after purchasing the product');
			}
			//检测剩余抽奖次数
			$user = Db::table('sys_user')->where("id={$pageuser['id']}")->lock(true)->find();
			if ($user['lottery'] < 1) {
				jReturn(-1, 'You currently have no chance of lucky draw, purchase products will get more chance to draw.');
			} 
			$sys_user = [
				'lottery' => $user['lottery'] - 1
			];
			Db::table('sys_user')->where("id={$user['id']}")->update($sys_user);

			$gift_prize_log = [
				'uid' => $user['id'],
				'type' => 0,
				'money' => 0,
				'gid' => 0,
				'prize_name' => '',
				'prize_cover' => '',
				'remark' => '',
				'create_day' => $now_day,
				'create_time' => NOW_TIME,
				'create_ip' => CLIENT_IP
			];

			//查询除概率大于0的奖品
			$prize_arr = Db::table('gift_prize')->where("1=1 and probability>0")->order(['probability' => 'asc'])->select()->toArray();
			$total = 0;
			foreach ($prize_arr as $pv) {
				$total += $pv['probability'] * 100;
			}

			$count = 0;
			$prize = [];
			$rand = mt_rand(1, $total);
			foreach ($prize_arr as $pv) {
				$count += $pv['probability'] * 100;
				if ($rand <= $count) {
					$prize = $pv;
					break;
				}
			}
			if (!$prize) {
				$prize = Db::table('gift_prize')->where("type=4")->orderRaw("rand()")->find();
			}

			if ($prize) {
				$gift_prize_log['type'] = $prize['type'];
				$gift_prize_log['remark'] = $prize['remark'];
				$gift_prize_log['prize_name'] = $prize['name'];
				$gift_prize_log['prize_cover'] = $prize['cover'];
			}

			if ($prize['type'] == 1) { //金额
				$money = $this->getRandMoney($prize['from_money'], $prize['to_money']);
				$money = number_format($money, 2, '.', '');

				$gift_prize_log['money'] = $money;
				$res = Db::table('gift_prize_log')->insertGetId($gift_prize_log);

				$wallet = getWallet($user['id'], 2, 1);
				if (!$wallet) {
					throw new \Exception('钱包获取异常');
				}
				$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
				$wallet_data = [
					'balance' => $wallet['balance'] + $money
				];
				//更新钱包余额
				Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
				//写入流水记录
				$result = walletLog([
					'wid' => $wallet['id'],
					'uid' => $wallet['uid'],
					'cid' => $wallet['cid'],
					'wtype' => $wallet['type'],
					'type' => 9,
					'money' => $money,
					'ori_balance' => $wallet['balance'],
					'new_balance' => $wallet_data['balance'],
					'fkey' => $res,
					'remark' => 'Lottery'
				]);
				if (!$result) {
					throw new \Exception('流水记录写入失败');
				}
				$tipMsg = "Money:{$money}";
			} elseif ($prize['type'] == 2) { //产品
				//giveGoods($user['id'],$prize['gid'],1,'抽奖');
				addGoodsOrder($user['id'], $prize['gid'], 1, -1, 'lottery', 1);
				$gift_prize_log['gid'] = $prize['gid'];
				$res = Db::table('gift_prize_log')->insertGetId($gift_prize_log);
				$tipMsg = "Product:{$prize['name']}";
			} elseif ($prize['type'] == 3) { //实物
				$tipMsg = $prize['remark'];
				$res = Db::table('gift_prize_log')->insertGetId($gift_prize_log);
			} elseif ($prize['type'] == 4) { //空
				$tipMsg = $prize['remark'];
				$res = Db::table('gift_prize_log')->insertGetId($gift_prize_log);
			} elseif ($prize['type'] == 5) { //代金券
				addCouponLog($user['id'], $prize['coupon_id'], 1, $prize['remark']);
				$gift_prize_log['coupon_id'] = $prize['coupon_id'];
				$res = Db::table('gift_prize_log')->insertGetId($gift_prize_log);
				$tipMsg = "Coupon: {$prize['name']}";
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			jReturn(-1, '系统繁忙请稍后再试');
		}
		$return_data = [
			'id' => $prize['id'] ? $prize['id'] : 0,
			'lottery' => $sys_user['lottery']
		];
		jReturn(1, $tipMsg, $return_data);
	}

	//////////////////////////////////////////////////////////

	//获取随机金额
	private function getRandMoney($from, $to)
	{
		$max = intval(($to - $from) * 100);
		$rand = 0;
		if ($max > 0) {
			$rand = mt_rand(1, $max);
		}
		$money = $from + ($rand / 100);
		return $money;
	}

	//领取红包
	public function _redpackAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		if (!$params['rsn']) {
			jReturn(-1, 'Please enter the redpack code');
		}
		Db::startTrans();
		try {
			$check_detail = Db::table('gift_redpack_detail')->where("rsn='{$params['rsn']}' and uid={$pageuser['id']}")->find();
			if ($check_detail) {
				jReturn(-1, 'You have received this redpack');
			}
			$item = Db::table('gift_redpack')->where("rsn='{$params['rsn']}'")->lock(true)->find();
			if (!$item) {
				jReturn(-1, 'The redpack code is incorrect');
			} else {
				if ($item['status'] != 3) {
					jReturn(-1, 'The redpack has been offline');
				}
			}
			$detail = Db::table('gift_redpack_detail')->where("rsn='{$item['rsn']}' and uid=0")->find();
			if (!$detail) {
				jReturn(-1, 'The redpack has been collected');
			}
			$detail = Db::table('gift_redpack_detail')->where("id={$detail['id']}")->lock(true)->find();
			if (!$detail || $detail['uid']) {
				jReturn(-1, 'The redpack has been collected');
			}

			if ($detail['create_id'] > 1) {
				$canReceive = false;
				$up_arr = getUpUser($pageuser['id']);
				foreach ($up_arr as $upUid) {
					if ($upUid == $detail['create_id']) {
						$canReceive = true;
						break;
					}
				}
				if (!$canReceive) {
					jReturn(-1, 'You cannot claim the red envelope');
				}
			}

			$gift_redpack = [
				'receive_money' => $item['receive_money'] + $detail['money'],
				'receive_quantity' => $item['receive_quantity'] + 1
			];

			$gift_redpack_detail = [
				'uid' => $pageuser['id'],
				'receive_time' => NOW_TIME,
				'receive_day' => date('Ymd', NOW_TIME),
				'receive_ip' => CLIENT_IP
			];
			Db::table('gift_redpack')->where("id={$item['id']}")->update($gift_redpack);
			Db::table('gift_redpack_detail')->where("id={$detail['id']}")->update($gift_redpack_detail);

			$wallet = getWallet($pageuser['id'], 2);
			if (!$wallet) {
				throw new \Exception('钱包获取异常');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] + $detail['money']
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'cid' => $wallet['cid'],
				'wtype' => $wallet['type'],
				'type' => 10,
				'money' => $detail['money'],
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $detail['id'],
				'remark' => 'Gift redemption'
			]);
			if (!$result) {
				throw new \Exception('流水记录写入失败');
			}

			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			jReturn(-1, '系统繁忙请稍后再试');
		}
		$return_data = [
			'money' => $detail['money'],
			'dsn' => $detail['dsn']
		];
		jReturn(1, '领取成功', $return_data);
	}

	//红包记录
	public function _redpackLog()
	{
		$pageuser = checkLogin();
		$params = $this->params;

		$where = "log.uid={$pageuser['id']}";
		//$where.=empty($params['s_keyword'])?'':" and (log.osn='{$params['s_keyword']}')";

		$count_item = Db::table('gift_redpack_detail log')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();

		$list = Db::view(
			['gift_redpack_detail' => 'log'],
			[
				'rsn', 'dsn', 'money', 'receive_time'
			]
		)
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['money'] = floatval($item['money']);
			$item['receive_time'] = date('m-d H:i:s', $item['receive_time']);
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => formatVal($count_item['money']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		jReturn(1, 'ok', $return_data);
	}
}
