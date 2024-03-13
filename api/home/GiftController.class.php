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
			//检测剩余抽奖次数
			$user = Db::table('sys_user')->where("id={$pageuser['id']}")->lock(true)->find();
			if ($user['lottery'] < 1) {
				jReturn(-1, 'You currently have no chance of lucky draw, purchase products will get more chance to draw.');
			} 
			$sys_user = [
				'lottery' => $user['lottery'] - 1
			];
			Db::table('sys_user')->where("id={$user['id']}")->update($sys_user);

			$gift_prize_log = Db::table("gift_prize_log")->where("is_user = 0 and uid={$pageuser['id']}")->find();
			if(empty($gift_prize_log)) {
				$prize = Db::table("gift_prize")->where("type=4")->select();
				$gift_prize_log = [
					'uid' => $pageuser['id'],
					'type' => $prize['type'],
					'money' => 0,
					'gid' => $prize['gid'],
					'coupon_id' => $prize['coupon_id'],
					'prize_name' => $prize['name'],
					'prize_cover' => $prize['cover'],
					'remark' => $prize['remark'],
					'create_time' => NOW_TIME,
					'create_day' => date('Ymd', NOW_TIME),
					'create_ip' => '',
					'split_time' => date('Y-m-d H:i:s', NOW_TIME),
					'order_money' => 0,
					'is_user' => 0,
					'gift_prize_id' => $prize['id'],
				];
				Db::table('gift_prize_log')->insertGetId($gift_prize_log);	
			}
			$prize = Db::table('gift_prize')->where("id={$gift_prize_log['gift_prize_id']}")->find();
			

			if ($prize['type'] == 1) { //金额
				$money = $this->getRandMoney($prize['from_money'], $prize['to_money']);
				$money = number_format($money, 2, '.', '');
				$res = Db::table('gift_prize_log')->update(['money'=>$money,'is_user'=>1]);

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
				$goodInfo = Db::table('pro_goods')->where("id = {$prize['gid']}")->select();
				Db::table('pro_order')->insertGetId([
					'uid'=> $pageuser['id'],
					'osn'=> getRsn(),
					'pid' => $pageuser['pid'],
					'cid' => $goodInfo['cid'],
					'gid' => $prize['gid'],
					'days' => $goodInfo['days'],
					'rate' => $goodInfo['rate'],
					'price' => $goodInfo['price'],
					'price1' => 0,
					'price2' => 0,
					'p1' => 1,
					'p2' => 1,
					'p3' => 0,
					'status' => 1,
					'money' => $goodInfo['price'],
					'num' => 1,
					'create_day' => date('Ymd', NOW_TIME),
					'create_time' => NOW_TIME,
					'is_give' => 1,
					'is_exchange' => 1,
					'discount' => 1,
					'w1_money' => $goodInfo['price'],
					'w2_money' => 0,
				]);

				Db::table('gift_prize_log')->update(['gid'=>$prize['gid'],'is_user'=>1]);
				$tipMsg = "Product:{$prize['name']}";
			} elseif ($prize['type'] == 3 || $prize['type'] == 4) { //实物
				$tipMsg = $prize['remark'];
				Db::table('gift_prize_log')->update(['is_user'=>1]);
			}  elseif ($prize['type'] == 5) { //代金券
				addCouponLog($user['id'], $prize['coupon_id'], 1, $prize['remark']);				
				Db::table('gift_prize_log')->update(['coupon_id'=>$prize['coupon_id'],'is_user'=>1]);
				$tipMsg = "Coupon: {$prize['name']}";
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			jReturn(-1, '系统繁忙请稍后再试');
		}
		$return_data = [
			'giftprizelog' => $gift_prize_log,
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
