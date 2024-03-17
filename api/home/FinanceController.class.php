<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class FinanceController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _recharge()
	{
		$pageuser = checkLogin();
		$wallet = getWallet(['id'], 1);
		$cnf_paylog_items = getConfig('cnf_paylog_items');
		$pay_items = explode(',', $cnf_paylog_items);
		if (in_array($pageuser['account'], ['8888888882', 'admin106']))
			$pay_types = Db::table('fin_ptype')->where("status=3 and gametype=0")->order(['sort' => 'desc', 'id' => 'desc'])->select()->toArray();
		else
			$pay_types = Db::table('fin_ptype')->where("status=3")->order(['sort' => 'desc', 'id' => 'desc'])->select()->toArray();

		$return_data = [
			'wallet' => $wallet,
			'pay_items' => $pay_items,
			'pay_types' => $pay_types
		];
		ReturnToJson(1, 'ok', $return_data);
	}
	// 支付通道选择
	public function _rechargeAct()
	{

		$params = $this->params;
		$pageuser = [];
		if ($params['tuser'] == 'game') {
			$pageuser = ['id' => '638492'];
			$paydb = Db::table('fin_ptype')->where('status=3 and gametype=1')->findOrEmpty();
			$params['pay_type'] = $paydb['type'];
		} else {
			$pageuser = checkLogin();
		}

		$params['money'] = floatval($params['money']);
		$cnf_paylog_items = getConfig('cnf_paylog_items');
		$pay_items = explode(',', $cnf_paylog_items);
		if ($paydb == null) {
			$paydb = Db::table('fin_ptype')->where("type='" . $params['pay_type'] . "'")->findOrEmpty();
		}
		if ($params['money'] < $paydb['min']) {
			ReturnToJson(-1, 'Recharge limit is too small');
		}
		if ($params['money'] > $paydb['max']) {
			ReturnToJson(-1, 'The recharge limit is too large');
		}
		// $balance = getPset('balance');
		// if ($params['money'] < $balance['pay']['min']) {
		// 	ReturnToJson(-1, 'Recharge limit is too small');
		// }
		// if ($params['money'] > $balance['pay']['max']) {
		// 	ReturnToJson(-1, 'The recharge limit is too large');
		// }





		$userondb = Db::table('sys_user')->lock(true)->where(' id=' . $pageuser['id'])->find();
		if (intval($userondb['gid']) != 92 && intval($userondb['gid']) != 1) {
			ReturnToJson(-1, 'System timeout, please try again');
			return;
		}
		$fin_paylog = [
			'money' => $params['money'],
			'real_money' => $params['money'],
			'pay_type' => $params['pay_type'],
			'osn' => getRsn(),
			'uid' => $userondb['id'],
			'rate' => 1,
			'create_time' => NOW_TIME,
			'gplayerId' => $params['playerId'],
			'gaccount' => $params['account'],
		];

		$banklog = [];
		if ($params['pay_type'] == 'offline') {

			// if ($params['money'] < $balance['pay']['kmin']) {
			// 	ReturnToJson(-1, 'Recharge limit is too small');
			// }
			// if ($params['money'] > $balance['pay']['kmax']) {
			// 	ReturnToJson(-1, 'The recharge limit is too large');
			// }

			$banklog = Db::view(['cnf_banklog' => 'log'], ['id', 'bank_name', 'ifsc', 'upi', 'bank_id', 'account', 'realname', 'protocal', 'address', 'qrcode'])
				// ->view(['cnf_bank' => 'bk'], ['name' => 'bank_name'], 'log.bank_id=bk.id', 'LEFT')
				->where("log.uid=0 and log.type=1 and log.status=2")
				->orderRaw("log.sort desc,rand()")->find();
			if (!$banklog) {
				ReturnToJson(-1, 'Channel is currently unavailable');
			}
			$fin_paylog['receive_type'] = 1;
			$fin_paylog['receive_ifsc'] = $banklog['ifsc'];
			$fin_paylog['receive_upi'] = $banklog['upi'];
			$fin_paylog['receive_bank_id'] = $banklog['bank_id'];
			$fin_paylog['receive_bank_name'] = $banklog['bank_name'];
			$fin_paylog['receive_account'] = $banklog['account'];
			$fin_paylog['receive_realname'] = $banklog['realname'];
		}

		try {
			$res = Db::table('fin_paylog')->insertGetId($fin_paylog);

			if ($res) {
				$sub_pay_type = 0;
				if (in_array($params['pay_type'], ['rapay101'])) {
					$file_name = 'rapay';
				} elseif (in_array($params['pay_type'], ['bobopay'])) {
					$file_name = 'bobopay';
				} elseif (in_array($params['pay_type'], ['jwpay'])) {
					$file_name = 'jwpay';
				} else {
					$pay_type_arr = explode('_', $params['pay_type']);
					$file_name = trim($pay_type_arr[0]);
					if ($pay_type_arr[1]) {
						$sub_pay_type = trim($pay_type_arr[1]);
					}
				}

				$pay_file = APP_PATH . 'common/pay/' . $file_name . '.php';
				if (!file_exists($pay_file)) {
					ReturnToJson(-1, 'Unknown recharge type:' . $params['pay_type']);
				}
				if ($params['pay_type'] == 'bobopay') {
					$sub_pay_type = 1;
				} elseif (($params['pay_type'] == 'rapay11101')) {
					$sub_pay_type = 11101;
				} elseif (($params['pay_type'] == 'jwpay')) {
					$sub_pay_type = 1;
				}

				require_once $pay_file;
				$result = payOrder($fin_paylog, $sub_pay_type);
				// if ($params['pay_type'] != 'OfflinePay') {
				// 	//file_put_contents(LOGS_PATH . $file_name . '/payResult' . date("Y-m-d", time()) . '.txt',   json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);
				// }
				if ($result['code'] != 1) {
					writeLog(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "rechargeAct/Error" . $params['pay_type']);
					ReturnToJson(-1, "Failed to initiate payment request, please try again later");
				}

				$resultArr = $result['data'];
				Db::table('fin_paylog')->where("id={$res}")->update(['out_osn' => $resultArr['out_osn']]);
				$return_data = [
					'pay_type' => $params['pay_type'],
					'osn' => $resultArr['osn'],
					'pay_url' => $resultArr['pay_url'],
					'fin_ptype' => $paydb['gametype'],
				];
				ReturnToJson(1, 'The order was submitted successfully and you are about to proceed to payment..', $return_data);
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
	}



	public function _withdraw()
	{
		$pageuser = checkLogin();
		$wallet = getWallet($pageuser['id'], 2);
		$banklog = Db::table('cnf_banklog')->where("uid={$pageuser['id']}")->find();
		$sys_pset = Db::table('sys_pset')->field('config')->where("skey='balance'")->find();
		$ptms = [];
		$ptms[] = ['id' => 1, 'name' => 'Canadian Solar Finance Department', 'min' => 120, 'max' => 50000];
		$ptms[] = ['id' => 2, 'name' => 'Enso Energy Finance Department', 'min' => 7000, 'max' => 315000];
		if (!$banklog) {
			ReturnToJson(-2, 'Please bind your bank card first.');
		} else {
			unset($banklog['id']);
		}
		$return_data = [
			'wallet' => $wallet,
			'banklog' => $banklog,
			'ptms' => $ptms,
			'sys_pset' => json_decode($sys_pset['config'], true)
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _withdrawAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		//$params['banklog_id']=intval($params['banklog_id']);
		$params['money'] = floatval($params['money']);
		$banklog = Db::table('cnf_banklog')->where("uid={$pageuser['id']}")->find();
		if (!$banklog) {
			ReturnToJson(-1, 'Please bind your bank card first.');
		}
		Db::startTrans();
		try {
			$pro_order = Db::table('pro_order')->where("uid={$pageuser['id']} and is_give=0")->find();
			if (!$pro_order) {
				ReturnToJson(-1, 'Withdrawal requires at least one product to be purchased');
			}
			$pset = getPset('balance');
			$psetCash = $pset['cash'];
			$money = $params['money'];
			if ($money < $psetCash['min'] || $money <= 0) {
				ReturnToJson(-1, 'Withdrawal amount is too small.');
			}
			if ($money > $psetCash['max']) {
				ReturnToJson(-1, 'Withdrawal amount is too large.');
			}
			if (!$psetCash['time']['weekend']) {
				$w = date('w');
				if ($w == 0 || $w == 6) {
					ReturnToJson(-1, 'No withdrawals allowed on weekends.');
				}
			}
			$from = date('Y-m-d', NOW_TIME) . ' ' . $psetCash['time']['from'];
			$to = date('Y-m-d', NOW_TIME) . ' ' . $psetCash['time']['to'];
			if (NOW_DATE < $from || NOW_DATE > $to) {
				ReturnToJson(-1, 'Withdrawal is not available at the current time.');
			}
			$wallet = getWallet($pageuser['id'], 2);
			if (!$wallet) {
				throw new \Exception('Wallet acquisition exception.');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$psetCashFee = $psetCash['fee'];
			$psetCashFee['mode'] = intval($psetCashFee['mode']);
			$fee = ($psetCashFee['percent'] / 100) * $money + floatval($psetCashFee['money']);
			if ($psetCashFee['mode'] == 1) { //从提现中出
				$new_balance = floatval($wallet['balance'] - $money);
				$real_money = $money - $fee;
				if ($real_money <= 0) {
					ReturnToJson(-1, 'The withdrawal amount is not enough to cover the handling fee.');
				}
				$minus_money = $money;
			} elseif ($psetCashFee['mode'] == 2) { //从余额中出
				$new_balance = floatval($wallet['balance'] - $money - $fee);
				$real_money = $money;
				$minus_money = $money + $fee;
			} else {
				ReturnToJson(-1, 'Unknown fee model.');
			}
			if (getPassword($params['password2']) != $pageuser['password2']) {
				ReturnToJson(-1, 'The payment password is incorrect.');
			}
			$new_balance = intval($new_balance * 100) / 100;
			if ($new_balance < 0) {
				ReturnToJson(-1, 'Your balance is insufficient.');
			}
			$wallet_data = [
				'balance' => $new_balance
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			$fin_cashlog = [
				'osn' => getRsn(),
				'uid' => $pageuser['id'],
				'money' => $money,
				'real_money' => $real_money,
				'fee' => $fee,
				'fee_mode' => $psetCashFee['mode'],
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'create_time' => NOW_TIME,
				'create_day' => date('Ymd', NOW_TIME),
				'receive_bank_id' => $banklog['bank_id'],
				'receive_type' => $banklog['type'],
				'client_ip' => CLIENT_IP
			];
			$fin_cashlog['receive_bank_name'] = $banklog['bank_name'];
			$fin_cashlog['receive_account'] = $banklog['account'];
			$fin_cashlog['receive_realname'] = $banklog['realname'];
			$fin_cashlog['receive_phone'] = $banklog['phone'];
			$fin_cashlog['receive_email'] = $banklog['email'];
			$fin_cashlog['receive_ifsc'] = $banklog['ifsc'];
			$fin_cashlog['receive_address'] = $banklog['address'];
			$res = Db::table('fin_cashlog')->insertGetId($fin_cashlog);
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 31,
				'money' => -$real_money,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $res,
				'remark' => 'Withdraw'
			]);
			if (!$result) {
				throw new \Exception('Failed to write journal records.');
			}
			Db::commit();
			$this->redis->rmall(RedisKeys::USER_WALLET . $pageuser['id']);
			$return_data = [
				'osn' => $fin_cashlog['osn'],
				'balance' => $wallet_data['balance']
			];
			ReturnToJson(1, 'Submitted successfully.', $return_data);
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
	}
	/////////////////////////////////////////////////////

	public function _pay()
	{
		$pageuser = checkLogin();
		$cnf_protocal = getConfig('cnf_protocal');
		$return_data = [
			'protocal_arr' => $cnf_protocal
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _getAddrByProtocal()
	{
		$pageuser = checkLogin();
		$protocal = intval($this->params['protocal']);
		$banklog = Db::table('cnf_banklog')->where("uid=0 and status=2 and protocal={$protocal}")->order(['sort' => 'desc'])->find();
		if (!$banklog) {
			ReturnToJson(-1, 'There is no deposit address.');
		}
		ReturnToJson(1, 'ok', $banklog);
	}

	public function _getBanklog()
	{
		$pageuser = checkLogin();
		$ptype = intval($this->params['ptype']);
		$protocol = intval($this->params['protocol']);
		$where = "log.uid=0 and log.type={$ptype}";
		if ($ptype == 4) {
			$where .= " and log.protocal={$protocol}";
		}
		$where .= " and log.status=2";
		$banklog = Db::view(['cnf_banklog' => 'log'], ['id', 'realname', 'account', 'address', 'qrcode'])
			->view(['cnf_bank' => 'bk'], ['name' => 'bank_name'], 'log.bank_id=bk.id', 'LEFT')
			->where($where)
			->order(['log.sort' => 'desc', 'log.id' => 'desc'])->find();
		if (!$banklog) {
			ReturnToJson(-1, 'There is no corresponding payment method.');
		}
		ReturnToJson(1, 'ok', $banklog);
	}

	public function _payAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['money'] = floatval($params['money']);
		$params['usdt'] = floatval($params['usdt']);
		$params['banklog_id'] = intval($params['banklog_id']);
		$item = Db::table('cnf_banklog')->where("id={$params['banklog_id']} and uid=0 and status=2")->find();
		if (!$item) {
			ReturnToJson(-1, 'The corresponding channel does not exist.');
		}

		// $check_paylog = Db::table('fin_paylog')->where("uid={$pageuser['id']} and pay_status<=2")->find();
		// if ($check_paylog) {
		// 	ReturnToJson('-1', 'You currently have an outstanding order.');
		// }
		$fin_paylog = [
			'receive_type' => $item['type'],
			'money' => $item['type'] < 4 ? $params['money'] : $params['usdt'],
			'real_money' => $item['type'] < 4 ? $params['money'] : 0,
			'osn' => getRsn(),
			'uid' => $pageuser['id'],
			'receive_address' => $item['address'],
			'receive_protocol' => $item['protocal'],
			'receive_qrcode' => $item['qrcode'],
			'rate' => $item['type'] < 4 ? 1 : '',
			'receive_bank_id' => $item['bank_id'],
			'receive_account' => $item['account'],
			'receive_realname' => $item['realname'],
			'create_time' => NOW_TIME
		];
		if ($fin_paylog['money'] > 0) {
			$balance = getPset('balance');
			if ($fin_paylog['money'] < $balance['pay']['min']) {
				ReturnToJson(-1, 'The recharge amount is too small.');
			}
			if ($fin_paylog['money'] > $balance['pay']['max']) {
				ReturnToJson(-1, 'The recharge amount is too large.');
			}
			$fin_paylog['money'] = $params['money'];
		}
		try {
			Db::table('fin_paylog')->insert($fin_paylog);
		} catch (\Exception $e) {
			ReturnToJson('-1', 'The system is busy, please try again later.');
		}
		$return_data = [
			'osn' => $fin_paylog['osn']
		];
		ReturnToJson(1, 'Submitted successfully.', $return_data);
	}

	public function _payInfo()
	{
		$pageuser = checkLogin();
		$osn = $this->params['osn'];
		if (!$osn) {
			ReturnToJson(-1, 'Missing parameters.');
		}
		$field_str = 'log.osn,log.money,log.rate,log.real_money,log.status,
		log.check_remark,log.pay_realname,log.pay_remark,log.pay_banners,log.receive_type,log.pay_type,
		log.receive_bank_id,log.receive_account,log.receive_realname,log.receive_routing,
		log.receive_address,log.receive_protocol,log.receive_qrcode,
		log.receive_bank_name,log.receive_ifsc,receive_upi,
		log.create_time,log.sub_time'; //,b.name as bank_name

		$item = Db::table('fin_paylog log')
			->field($field_str)
			// ->leftJoin('cnf_bank b', 'log.receive_bank_id=b.id')
			->where("log.osn='{$osn}' and log.uid={$pageuser['id']} and log.status<99")->find();

		if (!$item) {
			ReturnToJson(-1, 'No corresponding record exists.');
		}
		$item['create_time'] = date('m-d H:i:s', $item['create_time']);
		$item['sub_time'] = date('m-d H:i:s', $item['sub_time']);
		$cnf_paylog_status = getConfig('cnf_paylog_status');
		$cnf_protocal = getConfig('cnf_protocal');
		$item['status_flag'] = lang($cnf_paylog_status[$item['status']]);
		$item['receive_protocol_flag'] = $cnf_protocal[$item['receive_protocol']];
		$item['pay_banners'] = json_decode($item['pay_banners'], true);
		if (!$item['pay_banners']) {
			$item['pay_banners'] = [];
		}
		$item['money'] = floatval($item['money']);
		$item['rate'] = floatval($item['rate']);
		$item['real_money'] = floatval($item['real_money']);
		$return_data = [
			'item' => $item
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _payInfoUpdate()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['money'] = floatval($params['money']);
		if (!$params['osn']) {
			ReturnToJson(-1, 'Missing parameters.');
		}

		//   if($params['money']<=0){
		// 	  ReturnToJson(-1,'The recharge amount is incorrect.');
		//   }
		//   $balance=getPset('balance');
		//   if($params['money']<$balance['pay']['min']){
		// 	  ReturnToJson(-1,'The recharge amount is too small.');
		//   }
		//   if($params['money']>$balance['pay']['max']){
		// 	  ReturnToJson(-1,'The recharge amount is too large.');
		//   }
		//   if(!$params['pay_realname']){
		// 	  ReturnToJson(-1,'Please fill in your name.');
		//   }

		if (!$params['pay_remark']) {
			ReturnToJson(-1, 'Please fill in payment remarks.');
		}

		// $pay_banners = ['img' => '1'];
		if ($params['pay_banners']) {
			foreach ($params['pay_banners'] as $bv) {
				$bv = trim($bv);
				if (strlen($bv) > 10) {
					$pay_banners[] = $bv;
				}
			}
		}

		if (!$pay_banners) {
			$pay_banners = ['img' => '1'];
			//ReturnToJson(-1, 'Please upload payment voucher.');
		}
		$item = Db::table('fin_paylog')->where("osn='{$params['osn']}' and uid={$pageuser['id']}")->find();
		if (!$item) {
			ReturnToJson(-1, 'No corresponding record exists.');
		} else {
			if (!in_array($item['status'], [1, 3])) {
				ReturnToJson(-1, 'The current status of the order is inoperable.');
			}
		}

		$useitem = Db::table('fin_paylog')->where("pay_remark='{$params['pay_remark']}'")->find();
		if ($useitem) {
			ReturnToJson(-1, 'UTR already exists');
		}
		$fin_paylog = [
			//'money'=>$params['money'],
			//'pay_realname'=>$params['pay_realname'],
			'pay_remark' => $params['pay_remark'],
			'pay_banners' => json_encode($pay_banners, 256),
			'sub_time' => NOW_TIME,
			'status' => 2
		];
		try {
			Db::table('fin_paylog')->where("id={$item['id']}")->update($fin_paylog);
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		ReturnToJson(1, 'Submitted successfully.');
	}

	public function _paylog()
	{
		$pageuser = checkLogin();
		$params = $this->params;

		$where = "uid={$pageuser['id']} and log.status<99";
		$where .= empty($params['s_keyword']) ? '' : " and (log.receive_account='{$params['s_keyword']}' or log.receive_address='{$params['s_keyword']}')";

		$count_item = Db::table('fin_paylog log')
			->leftJoin('cnf_bank b', 'log.receive_bank_id=b.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(
			['fin_paylog' => 'log'],
			[
				'osn',
				'status',
				'money',
				'rate',
				'pay_type',
				'real_money',
				'create_time',
				'receive_account',
				'receive_realname',
				'receive_protocol',
				'receive_type',
				'receive_bank_id',
				'receive_bank_name',
				'receive_routing',
				'receive_qrcode',
				'receive_address'
			]
		)
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_paylog_status = getConfig('cnf_paylog_status');
		$cnf_protocal = getConfig('cnf_protocal');
		$cnf_banklog_type = getConfig('cnf_banklog_type');
		foreach ($list as &$item) {
			$item['money'] = floatval($item['money']);
			$item['rate'] = floatval($item['rate']);
			$item['real_money'] = floatval($item['real_money']);
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['status_flag'] = $cnf_paylog_status[$item['status']];
			$item['receive_protocol_flag'] = $cnf_protocal[$item['receive_protocol']];
			$item['receive_type_flag'] = $cnf_banklog_type[$item['receive_type']];
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//////////////////////////////////提现相关///////////////////////////////////
	//获取用户的钱包
	public function _getWallet()
	{
		$pageuser = checkLogin();
		$wallet = getWallet($pageuser['id'], 1);
		if (!$wallet) {
			ReturnToJson(-1, 'Getting wallet exception.');
		}
		$return_data = [
			'wallet' => $wallet
		];
		ReturnToJson(1, 'ok', $return_data);
	}


	public function _cash()
	{
		$pageuser = checkLogin();
		$banklog_type = [];
		foreach (getConfig('cnf_banklog_type') as $key => $val) {
			if (!in_array($key, [1, 4])) {
				//continue;
			}
			$banklog_type[$key] = $val;
		}
		$wallet = getWallet($pageuser['id'], 1);
		$pset = getPset('balance');
		$psetCashFee = $pset['cash']['fee'];
		$wallet['withdrawable_balance'] = $wallet['balance'];
		if ($psetCashFee['mode'] != 1) {
			$can_cash = ($wallet['balance'] - $psetCashFee['money']) / (1 + $psetCashFee['percent'] / 100);
			if ($can_cash < 0) {
				$can_cash = 0;
			}
			$wallet['withdrawable_balance'] = intval($can_cash * 100) / 100;
		}
		$banklog = Db::view(['cnf_banklog' => 'log'], ['id', 'type', 'realname', 'account', 'address', 'qrcode'])
			->view(['cnf_bank' => 'bk'], ['name' => 'bank_name'], 'log.bank_id=bk.id', 'LEFT')
			->where("log.uid={$pageuser['id']} and log.status=2")
			->order(['log.sort' => 'desc', 'log.id' => 'desc'])->find();

		$return_data = [
			'fee_rule' => $psetCashFee,
			'banklog_type' => $banklog_type,
			'wallet' => $wallet,
			'banklog' => $banklog
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _cashAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['banklog_id'] = intval($params['banklog_id']);
		$params['money'] = floatval($params['money']);
		//$banklog=Db::table('cnf_banklog')->where("id={$params['banklog_id']} and uid={$pageuser['id']}")->find();
		$banklog = Db::table('cnf_banklog')->where("uid={$pageuser['id']}")->order(['id' => 'desc'])->find();
		if (!$banklog) {
			ReturnToJson(-1, 'Please bind your bank card first.');
		}
		Db::startTrans();
		try {
			$pset = getPset('balance');
			$psetCash = $pset['cash'];
			//$user=Db::table('sys_user')->where("id={$pageuser['id']}")->lock(true)->find();

			$money = $params['money'];
			if ($money < $psetCash['min'] || $money <= 0) {
				ReturnToJson(-1, 'Withdrawal amount is too small.');
			}
			if ($money > $psetCash['max']) {
				ReturnToJson(-1, 'Withdrawal amount is too large.');
			}
			if (!$psetCash['time']['weekend']) {
				$w = date('w');
				if ($w == 0 || $w == 6) {
					ReturnToJson(-1, 'No withdrawals allowed on weekends.');
				}
			}
			$from = date('Y-m-d', NOW_TIME) . ' ' . $psetCash['time']['from'];
			$to = date('Y-m-d', NOW_TIME) . ' ' . $psetCash['time']['to'];
			if (NOW_DATE < $from || NOW_DATE > $to) {
				ReturnToJson(-1, 'Withdrawal is not available at the current time.');
			}
			$wallet = getWallet($pageuser['id'], 1);
			if (!$wallet) {
				throw new \Exception('Wallet acquisition exception.');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$psetCashFee = $psetCash['fee'];
			$psetCashFee['mode'] = intval($psetCashFee['mode']);
			$fee = ($psetCashFee['percent'] / 100) * $money + floatval($psetCashFee['money']);
			if ($psetCashFee['mode'] == 1) { //从提现中出
				$new_balance = floatval($wallet['balance'] - $money);
				$real_money = $money - $fee;
				if ($real_money <= 0) {
					ReturnToJson(-1, 'The withdrawal amount is not enough to cover the handling fee.');
				}
				$minus_money = $money;
			} elseif ($psetCashFee['mode'] == 2) { //从余额中出
				$new_balance = floatval($wallet['balance'] - $money - $fee);
				$real_money = $money;
				$minus_money = $money + $fee;
			} else {
				ReturnToJson(-1, 'Unknown fee model.');
			}
			if (getPassword($params['password2']) != $pageuser['password2']) {
				ReturnToJson(-1, 'The payment password is incorrect.');
			}
			$new_balance = intval($new_balance * 100) / 100;
			if ($new_balance < 0) {
				ReturnToJson(-1, 'Your balance is insufficient.', $new_balance);
			}
			$wallet_data = [
				'balance' => $new_balance
			];
			//更新钱包余额
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			//写入流水记录
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 31,
				'money' => $real_money,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $to_user['id'],
				'remark' => 'Withdraw'
			]);
			if (!$result) {
				throw new \Exception('Failed to write journal records.');
			}
			$fin_cashlog = [
				'osn' => getRsn(),
				'uid' => $pageuser['id'],
				'money' => $money,
				'real_money' => $real_money,
				'fee' => $fee,
				'fee_mode' => $psetCashFee['mode'],
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'create_time' => NOW_TIME,
				'create_day' => date('Ymd', NOW_TIME),
				'receive_type' => $banklog['type'],
				'client_ip' => CLIENT_IP
			];

			// if ($banklog['type'] == 1) {
			// 	$fin_cashlog['receive_bank_id'] = $banklog['bank_id'];
			// 	$fin_cashlog['receive_account'] = $banklog['account'];
			// 	$fin_cashlog['receive_realname'] = $banklog['realname'];
			// 	$fin_cashlog['receive_routing'] = $banklog['routing'];
			// } elseif ($banklog['type'] > 1 && $banklog['type'] < 4) {
			// 	$fin_cashlog['receive_account'] = $banklog['account'];
			// 	$fin_cashlog['receive_realname'] = $banklog['realname'];
			// 	$fin_cashlog['receive_qrcode'] = $banklog['qrcode'];
			// } else {
			// 	$fin_cashlog['receive_protocol'] = $banklog['protocal'];
			// 	$fin_cashlog['receive_address'] = $banklog['address'];
			// 	$fin_cashlog['receive_qrcode'] = $banklog['qrcode'];
			// }



			$fin_cashlog['receive_account'] = $banklog['account'];
			$fin_cashlog['receive_realname'] = $banklog['realname'];
			$fin_cashlog['receive_phone'] = $banklog['phone'];
			$fin_cashlog['receive_email'] = $banklog['email'];
			$fin_cashlog['receive_ifsc'] = $banklog['ifsc'];
			$fin_cashlog['receive_address'] = $banklog['address'];
			Db::table('fin_cashlog')->insertGetId($fin_cashlog);
			Db::commit();
			$this->redis->rmall(RedisKeys::USER_WALLET . $pageuser['id']);
			$return_data = [
				'osn' => $fin_cashlog['osn'],
				'balance' => $wallet_data['balance']
			];
			ReturnToJson(1, 'Submitted successfully.', $return_data);
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
	}

	public function _cashInfo()
	{
		$pageuser = checkLogin();
		$osn = $this->params['osn'];
		if (!$osn) {
			ReturnToJson(-1, 'Missing parameters.');
		}
		$field_str = 'log.osn,log.money,log.real_money,log.status,log.check_remark,
		log.receive_type,log.receive_bank_id,log.receive_account,log.receive_realname,log.receive_routing,
		log.receive_protocol,log.receive_address,log.receive_qrcode,
		log.create_time,b.name as bank_name';
		$item = Db::table('fin_cashlog log')
			->field($field_str)
			->leftJoin('cnf_bank b', 'log.receive_bank_id=b.id')
			->where("log.osn='{$osn}' and log.uid={$pageuser['id']} and log.status<99")->find();
		if (!$item) {
			ReturnToJson(-1, 'No corresponding record exists.'); //Db::getLastSql()
		}
		$item['create_time'] = date('m-d H:i:s', $item['create_time']);
		$cnf_cashlog_status = getConfig('cnf_cashlog_status');
		$cnf_banklog_type = getConfig('cnf_banklog_type');
		$item['status_flag'] = $cnf_cashlog_status[$item['status']];
		$item['receive_type_flag'] = $cnf_banklog_type[$item['receive_type']];
		if ($item['receive_type'] == 4) {
			$cnf_protocal = getConfig('cnf_protocal');
			$item['receive_protocol_flag'] = $cnf_protocal[$item['receive_protocol']];
		}
		$item['money'] = floatval($item['money']);
		$item['real_money'] = floatval($item['real_money']);
		$return_data = [
			'item' => $item
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _cashlog()
	{
		$pageuser = checkLogin();
		$params = $this->params;

		$where = "uid={$pageuser['id']} and log.status<99";
		$where .= empty($params['s_keyword']) ? '' : " and (log.receive_account='{$params['s_keyword']}' or log.receive_address='{$params['s_keyword']}')";

		$count_item = Db::table('fin_cashlog log')
			->leftJoin('cnf_bank b', 'log.receive_bank_id=b.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(
			['fin_cashlog' => 'log'],
			[
				'osn',
				'status',
				'pay_status',
				'money',
				'real_money',
				'create_time',
				'pay_time',
				'receive_type',
				'receive_account',
				'receive_realname',
				'receive_protocol',
				'receive_bank_id',
				'receive_bank_name',
				'receive_routing',
				'receive_qrcode',
				'receive_address',
				'receive_phone',
				'receive_email',
				'receive_ifsc'
			]
		)
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_cashlog_status = getConfig('cnf_cashlog_status');
		$cnf_cashlog_pay_status = getConfig('cnf_cashlog_pay_status');
		$cnf_banklog_type = getConfig('cnf_banklog_type');
		$cnf_protocal = getConfig('cnf_protocal');
		foreach ($list as &$item) {
			$item['money'] = floatval($item['money']);
			$item['real_money'] = floatval($item['real_money']);
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['status_flag'] = $cnf_cashlog_status[$item['status']];
			$item['pay_status_flag'] = $cnf_cashlog_pay_status[$item['pay_status']];

			if (!$item['pay_time']) {
				$item['pay_time'] = '/';
			} else {
				$item['pay_time'] = date('m-d H:i:s', $item['pay_time']);
			}

			if ($item['status'] == 9) {
				if ($item['pay_status'] < 9) {
					$item['status_flag'] = 'Waiting payment';
				}
			}
			$item['receive_type_flag'] = $cnf_banklog_type[$item['receive_type']];
			if ($item['receive_type'] == 4) {
				$item['receive_protocol_flag'] = $cnf_protocal[$item['receive_protocol']];
			}
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//############################################################
	public function _balancelog()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['s_type'] = intval($params['s_type']);
		$params['s_day'] = intval($params['s_day']);

		$where = "log.uid={$pageuser['id']}";
		if ($params['s_type']) {
			$where .= " and log.type=" . $params['s_type'];
		}
		$start_time = 0;
		$end_time = 0;
		if ($params['s_day'] == 0) {
			//$start_time=strtotime(date('Y-m-d 00:00:01'));
			//$end_time=strtotime(date('Y-m-d 23:59:59'));
		} elseif ($params['s_day'] == 1) {
			$start_time = strtotime(date('Y-m-d 00:00:01', strtotime("-1 day")));
			$end_time = strtotime(date('Y-m-d 23:59:59', strtotime("-1 day")));
		} elseif ($params['s_day'] == 7) {
			$start_time = strtotime(date('Y-m-d 00:00:01', strtotime("-7 day")));
			$end_time = time();
		} elseif ($params['s_day'] == 15) {
			$start_time = strtotime(date('Y-m-d 00:00:01', strtotime("-15 day")));
			$end_time = time();
		} elseif ($params['s_day'] == -1) {
			$start_time = strtotime($params['s_start'] . ' 00:00:01');
			$end_time = strtotime($params['s_end'] . ' 23:59:59');
		}
		if ($start_time > 0 && $end_time > 0) {
			if ($start_time > $end_time) {
				ReturnToJson(-1, 'Wrong date selection.');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		//$where.=empty($params['s_keyword'])?'':" and (log.remark='{$params['s_keyword']}')";

		$count_item = Db::table('wallet_log log')
			->leftJoin('wallet_list w', 'log.wid=w.id')
			->leftJoin('cnf_currency c', 'w.cid=c.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['wallet_log' => 'log'], ['type', 'money', 'remark', 'create_time', 'id', 'ori_balance', 'new_balance'])
			->view(['wallet_list' => 'w'], ['waddr'], 'log.wid=w.id', 'LEFT')
			->view(['cnf_currency' => 'c'], ['name' => 'currency_name'], 'w.cid=c.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_balance_type = getConfig('cnf_balance_type');
		foreach ($list as &$item) {
			$item['money'] = floatval($item['money']);
			$item['ori_balance'] = floatval($item['ori_balance']);
			$item['new_balance'] = floatval($item['new_balance']);
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['type_flag'] = $cnf_balance_type[$item['type']];
		}
		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//############################################################
	public function _reward()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['s_type'] = intval($params['s_type']);
		$params['s_day'] = intval($params['s_day']);

		$where = "log.uid={$pageuser['id']}";
		$start_time = 0;
		$end_time = 0;
		if ($params['s_day'] == 0) {
			$start_time = strtotime(date('Y-m-d 00:00:01'));
			$end_time = strtotime(date('Y-m-d 23:59:59'));
		} elseif ($params['s_day'] == 1) {
			$start_time = strtotime(date('Y-m-d 00:00:01', strtotime("-1 day")));
			$end_time = strtotime(date('Y-m-d 23:59:59', strtotime("-1 day")));
		} elseif ($params['s_day'] == 7) {
			$start_time = strtotime(date('Y-m-d 00:00:01', strtotime("-7 day")));
			$end_time = time();
		} elseif ($params['s_day'] == 15) {
			$start_time = strtotime(date('Y-m-d 00:00:01', strtotime("-15 day")));
			$end_time = time();
		} elseif ($params['s_day'] == -1) {
			$start_time = strtotime($params['s_start'] . ' 00:00:01');
			$end_time = strtotime($params['s_end'] . ' 23:59:59');
		}
		if ($start_time > 0 && $end_time > 0) {
			if ($start_time > $end_time) {
				ReturnToJson(-1, 'Wrong date selection.');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		$where .= empty($params['s_type']) ? '' : " and log.type={$params['s_type']}";
		//$where.=empty($params['s_keyword'])?'':" and (log.remark='{$params['s_keyword']}')";

		$count_item = Db::table('pro_reward log')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();

		$list = Db::view(
			['pro_reward' => 'log'],
			[
				'type',
				'money',
				'remark',
				'create_time',
				'level',
				'base_money',
				'rate'
			]
		)
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_reward_type = getConfig('cnf_reward_type');
		foreach ($list as &$item) {
			$item['money'] = floatval($item['money']);
			$item['base_money'] = floatval($item['base_money']);
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['type_flag'] = lang($cnf_reward_type[$item['type']]);
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
		}
		ReturnToJson(1, 'ok', $return_data);
	}
}