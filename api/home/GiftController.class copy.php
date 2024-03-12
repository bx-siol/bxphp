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

	public function _lottery()
	{
		$pageuser = checkLogin();
		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		$now_day = date('Ymd');
		$where = "log.create_day={$now_day}";
		$list = Db::view(['gift_lottery_log' => 'log'], ['money'])
			->view(['sys_user' => 'u'], ['nickname', 'account'], 'log.uid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->limit(10)
			->select()->toArray();
		$notice = [];
		foreach ($list as $item) {
			$account = substr($item['account'], -3);
			$notice[] = [
				'title' => "52*****{$account} get {$item['money']}RS"
			];
		}
		if (!$notice) {
			$notice[] = ['title' => 'Buy products and get lucky draw'];
		}
		$notice_ext = [
			'52*****652 get 280RS',
			'52*****614 get 454RS',
			'52*****354 get 254RS',
			'52*****654 get 164RS',
			'52*****652 get 280RS',
			'52*****614 get 454RS',
			'52*****354 get 254RS',
			'52*****654 get 164RS',
			'52*****614 get 254RS ',
			'52*****857 get 657RS',
			'52*****652 get 280RS',
			'52*****614 get 454RS',
			'52*****354 get 254RS',
			'52*****654 get 164RS',
			'52*****614 get 254RS ',
			'52*****857 get 657RS',
			'52*****652 get 280RS',
			'52*****614 get 454RS',
			'52*****354 get 254RS',
			'52*****654 get 164RS',
			'52*****614 get 254RS ',
			'52*****857 get 657RS',
			'52*****652 get 280RS',
			'52*****614 get 454RS',
		];
		foreach ($notice_ext as $nv) {
			$account =  rand(100, 999);
			$account1 = rand(50, 500);
			$account2 = rand(100, 999);
			$notice[] = ['title' => "{$account2}*****{$account} get {$account1}RS"];
		}
		shuffle($notice);
		$return_data = [
			'lottery' => $user['lottery'],
			'notice' => $notice
		];
		jReturn(1, 'ok', $return_data);
	}

	public function _lotteryAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['idx'] = intval($this->params['idx']);
		// if (!$params['rsn']) {
		// 	jReturn(-1, '缺少参数');
		// }
		$rsn = 'a3d044b074d37a89';
		$return_data = [];
		Db::startTrans();
		try {
			//检测剩余抽奖次数
			$user = Db::table('sys_user')->where("id={$pageuser['id']}")->lock(true)->find();
			if ($user['lottery'] < 1) {
				jReturn(-2, 'You are not entitled to participate in the lucky draw for the time being!<br>Lucky draw rules: you can participate in the lucky draw by purchasing equipment by yourself or inviting friends to join in the purchase');
			}
			$item = Db::table('gift_lottery')->where("rsn='{$rsn}'")->lock(true)->find();
			if (!$item) {
				jReturn(-1, 'There is no corresponding award');
			}
			if ($item['status'] != 3) {
				if ($item['status'] == 9) {
					jReturn(-1, 'The award ');
				} else {
					jReturn(-1, 'Currently unavailable');
				}
			}
			if ($item['stock_money'] < 0.01) {
				jReturn(-1, 'The award .');
			}
			$now_day = date('Ymd', NOW_TIME);
			$sdefaultDate = date('Y-m-d');
			$first = 1; //1 表示每周星期一为开始日期 0表示每周日为开始日期  
			//获取当前周的第几天 周日是 0 周一到周六是 1 - 6
			$w = date('w', strtotime($sdefaultDate));
			$now_week = date('Ymd', strtotime("{$sdefaultDate} -" . ($w ? $w - $first : 6) . ' days'));
			$has_limit = false;
			if ($item['day_limit'] > 0) {
				$check_day = Db::table('gift_lottery_log')->where("uid={$pageuser['id']} and rid={$item['id']} and create_day={$now_day}")->count('id');
				if ($check_day >= $item['day_limit']) {
					jReturn(-1, 'The number of times to collect has reached the maximum today');
				}
				$has_limit = true;
			}
			if ($item['week_limit'] > 0) {
				$check_week = Db::table('gift_lottery_log')->where("uid={$pageuser['id']} and rid={$item['id']} and create_week={$now_week}")->count('id');
				if ($check_week >= $item['week_limit']) {
					jReturn(-1, 'The number of collection times this week has reached the maximum');
				}
				$has_limit = true;
			}
			/*
			if(!$has_limit){
				$check=Db::table('gift_lottery_log')->where("uid={$pageuser['id']} and rid={$item['id']}")->find();
				if($check){
					jReturn(-2,'Thank you for your participation');
				}
			}*/

			/*
			$max=intval(($item['to_money']-$item['from_money'])*100);
			$rand=0;
			if($max>0){
				$rand=mt_rand(1,$max);
			}
			$money=$item['from_money']+($rand/100);
			*/
			$rand = mt_rand(1, 100);
			$money = 0;
			if ($rand < 10) {
				//once again
			} else {
				$max_price = Db::table('pro_order')->where("uid={$pageuser['id']}")->max('price');
				$from_money = $item['from_money'];
				$to_money = $item['to_money'];
				/*
				if($max_price>=7700){
					$from_money=40;
					$to_money=100;
				}elseif($max_price>=3700){
					$from_money=30;
					$to_money=60;
				}else{
					$from_money=10;
					$to_money=30;
				}*/
				$money = $this->getRandMoney($from_money, $to_money);
				$money = number_format($money, 2, '.', '');
			}

			if ($money >= 0.01) {
				if ($money > $item['stock_money']) {
					$money = $item['stock_money'];
				}
				$db_item = [
					'stock_money' => $item['stock_money'] - $money,
					'receive_money' => $item['receive_money'] + $money,
					'receive_num' => $item['receive_num'] + 1
				];
				if ($db_item['stock_money'] <= 0) {
					$db_item['status'] = 9;
				}
				Db::table('sys_user')->where("id={$pageuser['id']}")->dec('lottery')->update(); //扣除抽奖次数
				Db::table('gift_lottery')->where("id={$item['id']}")->update($db_item);
				$gift_lottery_log = [
					'uid' => $pageuser['id'],
					'rid' => $item['id'],
					'rsn' => $item['rsn'],
					'lsn' => getRsn(),
					'money' => $money,
					'create_day' => $now_day,
					'create_week' => $now_week,
					'create_time' => NOW_TIME,
					'create_ip' => CLIENT_IP
				];
				$res = Db::table('gift_lottery_log')->insertGetId($gift_lottery_log);
				$wallet = getWallet($pageuser['id'], 2);
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
			}
			$return_data['money'] = $money;
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			jReturn(-1, '系统繁忙请稍后再试');
		}
		$all_money = [];
		$empty_rand = mt_rand(1, 6);
		if ($empty_rand == $params['idx']) {
			$empty_rand = mt_rand(1, 6);
		}
		for ($i = 1; $i <= 6; $i++) {
			if ($empty_rand == $i) {
				$all_money[$i] = 'No reward';
			} else {
				$tmp_money = $this->getRandMoney(1, 500);
				$all_money[$i] = number_format($tmp_money, 2, '.', '');
			}
		}
		if ($money >= 0.01) {
			$all_money[$params['idx']] = number_format($money, 2, '.', '');
		} else {
			$all_money[$params['idx']] = 'Once more';
			$return_data['money'] = 'Once more';
		}

		$return_data['all'] = $all_money;
		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		$return_data['lottery'] = $user['lottery'];
		jReturn(1, 'Received successfully', $return_data);
	}

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
				'type' => 10,
				'money' => $detail['money'],
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $detail['id'],
				'remark' => 'Redpack'
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
}
