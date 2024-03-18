<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class FinanceController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//手动退款
	public function _checktk()
	{
		checkPower('Finance_cashlog_checktk');
		$params = $this->params;
		$osn = $params['osn'];
		$order = Db::table('fin_cashlog')->where("osn='{$osn}'")->find();
		if (!$order) {
			ReturnToJson(-1, '订单不存在');
		}
		Db::startTrans();
		try {
			$fin_cashlog = [
				'pay_msg' => $order['pay_msg'],
				'pay_status' => 3,
				'status' => 3,
				'oldosn' => $order['osn'],
				'osn' => getRsn(),
			];
			$back_money = $order['money'];
			if ($order['fee_mode'] == 2) {
				$back_money = $order['money'] + $order['fee'];
			}
			$wallet = getWallet($order['uid'], 2);
			if (!$wallet) {
				ReturnToJson(-1, '用户信息异常');
			}
			$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
			$wallet_data = [
				'balance' => $wallet['balance'] + $back_money
			];
			Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
			$result = walletLog([
				'wid' => $wallet['id'],
				'uid' => $wallet['uid'],
				'type' => 33,
				'money' => $back_money,
				'ori_balance' => $wallet['balance'],
				'new_balance' => $wallet_data['balance'],
				'fkey' => $order['osn'],
				'remark' => 'Withdrawal refund-' . $order['osn']
			]);
			if (!$result) {
				ReturnToJson(-1, '写入财务信息失败');
			}
			$fin_cashlog['pay_msg'] .= ' | ifsc手动退款 | 退款成功 ';
			$log_arr = [
				'uid' => $order['uid'],
				'opt_name' => 'ifsc手动退款',
				'm' => $order['money'],
			];
			Db::table('fin_cashlog')->where("id={$order['id']}")->update($fin_cashlog);
			actionLog(['opt_name' => '退出', 'sql_str' => json_encode($log_arr)]);
			Db::commit();
			$this->redis->rmall(RedisKeys::USER_WALLET . $wallet['uid']);
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '处理sql异常');
		}
		ReturnToJson(1, '处理完成', $order);
	}

	public function _getwid()
	{
		$params = $this->params;
		if (!$params['uid']) {
			$order = Db::table('fin_cashlog')->where("osn='{$params['osn']}'")->find();
			$wallet = getWallet($order['uid'], 2);
			ReturnToJson(1, '处理完成', ['wid' => $wallet['id']]);
		}
		$wallet = getWallet($params['uid'], 2);
		ReturnToJson(1, '处理完成', ['wid' => $wallet['id']]);
	}

	public function _Getifsc()
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_URL, 'http://8.210.239.216:3387/?ifsc=' . $_GET['ifsc']);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($curl);
		ReturnToJson(1, 'ok', $res);
	}

	// 支付通道选择
	public function _rechargeAct()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['money'] = floatval($params['money']);
		$cnf_paylog_items = getConfig('cnf_paylog_items');
		$pay_items = explode(',', $cnf_paylog_items);

		$balance = getPset('balance');
		if ($params['money'] < $balance['pay']['min']) {
			ReturnToJson(-1, 'Recharge limit is too small');
		}
		if ($params['money'] > $balance['pay']['max']) {
			ReturnToJson(-1, 'The recharge limit is too large');
		}

		//ReturnToJson(-1,'充值金额不正确');

		// $w = date('w');
		// if ($w == 0) {
		// 	ReturnToJson(-1, '当前时间不可充值');
		// } elseif ($w == 6) {
		// 	if (NOW_DATE > date('Y-m-d 01:30:00')) {
		// 		ReturnToJson(-1, '当前时间不可充值');
		// 	}
		// } else {
		// 	if (NOW_DATE > date('Y-m-d 01:30:00') && NOW_DATE < date('Y-m-d 05:30:00')) {
		// 		ReturnToJson(-1, '当前时间不可充值');
		// 	}
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
			'uid' => $pageuser['id'],
			'rate' => 1,
			'create_time' => NOW_TIME
		];

		$banklog = [];
		if ($params['pay_type'] == 'offline') {

			if ($params['money'] < $balance['pay']['kmin']) {
				ReturnToJson(-1, 'Recharge limit is too small');
			}
			if ($params['money'] > $balance['pay']['kmax']) {
				ReturnToJson(-1, 'The recharge limit is too large');
			}

			$banklog = Db::view(['cnf_banklog' => 'log'], ['id', 'ifsc', 'upi', 'bank_id', 'account', 'realname', 'protocal', 'address', 'qrcode'])
				->view(['cnf_bank' => 'bk'], ['name' => 'bank_name'], 'log.bank_id=bk.id', 'LEFT')
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
				if ($params['pay_type'] != 'OfflinePay') {
					//file_put_contents(LOGS_PATH . $file_name . '/payResult' . date("Y-m-d", time()) . '.txt',   json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);
				}
				if ($result['code'] != 1) {
					ReturnToJson(-1, $result['msg']);
				}
				$resultArr = $result['data'];
				Db::table('fin_paylog')->where("id={$res}")->update(['out_osn' => $resultArr['out_osn']]);
				$return_data = [
					'pay_type' => $params['pay_type'],
					'osn' => $resultArr['osn'],
					'pay_url' => $resultArr['pay_url']
				];
				ReturnToJson(1, '订单提交成功，即将前往支付', $return_data);
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
	}

	//取消订单
	public function _qxdd()
	{
		$pageuser = checkPower();
		$params = $this->params;
		if (!$params['osn']) {
			ReturnToJson(-1, '请填写订单号');
		}
		$item = Db::table('fin_paylog log')->where("osn='" . $params['osn'] . "'")->find();
		if ($item['is_first'] == 1) {
			Db::table('sys_user')->where("id=" . $item['uid'])->update(['first_pay_day' => 0]);
		}
		Db::table('fin_paylog')->where("id=" . $item['id'])->update(['is_first' => 0, 'status' => 1]);
		ReturnToJson(1, '操作成功');
	}

	//充值记录
	public function _paylog()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_status'] = intval($params['s_status']);
		$params['s_receive_type'] = intval($params['s_receive_type']);
		$this->pageSize = 30;

		$where = "1=1";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}

		if ($params['s_puser']) {
			$s_keyword = $params['s_puser'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("usn='{$s_keyword}' or id='{$s_keyword}' or account='{$s_keyword}' or email='{$s_keyword}' or realname='{$s_keyword}' or nickname='{$s_keyword}'")->find();
			if ($s_puser) {
				$uid_arr = getDownUser_new($s_puser['id']);
			}
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}
		if ($params['s_spuser']) {
			$s_keyword = $params['s_spuser'];
			$s_spuser = Db::table('sys_user')->where("usn='{$s_keyword}' or id='{$s_keyword}' or account='{$s_keyword}' or email='{$s_keyword}' or realname='{$s_keyword}' or nickname='{$s_keyword}'")->find();
			if ($s_spuser) {
				$where .= " and u.pid = {$s_spuser['id']}";
			}
		}
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.pay_time between {$start_time} and {$end_time}";
		}
		$where .= empty ($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		if (isset ($params['s_is_first']) && $params['s_is_first'] != 'all') {
			$params['s_is_first'] = intval($params['s_is_first']);
			$where .= " and log.is_first={$params['s_is_first']}";
		}
		$where .= empty ($params['s_receive_type']) ? '' : " and log.receive_type={$params['s_receive_type']}";


		if ($params['s_user_account']) {
			$s_puser = Db::table('sys_user')->where(" account='{$params['s_user_account']}' ")->find();
			$where .= " and log.uid='{$s_puser['id']}'";
		}

		if ($params['s_osn']) {
			$where .= " and (log.osn='{$params['s_osn']}'  )";
		}

		if ($params['s_oldosn']) {
			$where .= " and (log.out_osn='{$params['s_oldosn']}')";
		}

		if ($params['s_paytype_s']) {
			$where .= " and (log.pay_type='{$params['s_paytype_s']}')";
		}
		if ($params['s_utr']) {
			$where .= " and (log.pay_remark='{$params['s_utr']}')";
		}

		if (!$params['s_utr'] && !$params['s_user_account'] && !$params['s_paytype_s'] && !$params['s_osn'] && !$params['s_oldosn']) {
			$where .= empty ($params['s_keyword']) ? '' : " and (log.osn='{$params['s_keyword']}' or log.out_osn='{$params['s_keyword']}' or u.account='{$params['s_keyword']}' or log.pay_type='{$params['s_keyword']}' or log.receive_account='{$params['s_keyword']}' or log.receive_address='{$params['s_keyword']}' or log.pay_realname like '%{$params['s_keyword']}%')";
		}


		if ($params['s_money_from'] >= 0 && $params['s_money_to'] > 0) {
			if ($params['s_money_from'] > $params['s_money_to']) {
				ReturnToJson(-1, '起始金额不能小于结束金额');
			}
			$where .= " and log.money between {$params['s_money_from']} and {$params['s_money_to']}";
		}

		if ($params['s_tjr'] == 1) {
			$where .= " and u2.gid=81";
		}
		// if ($params['s_tjr'] == 1) {
		// 	$where .= " and u.pid=" . $params['s_tjr'];
		// }
		$count_item = Db::table('fin_paylog log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('sys_user u2', 'u.pid=u2.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money,sum(log.real_money) as real_money')
			->where($where)
			->find();

		$list = Db::view(['fin_paylog' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'realname', 'headimgurl'], 'log.uid=u.id', 'LEFT')

			->view(['sys_user' => 'u2'], ['account' => 'p_account'], 'u.pid=u2.id', 'LEFT')
			//->view(['cnf_bank'=>'bk'],['name'=>'bank_name'],'log.receive_bank_id=bk.id','LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_paylog_status = getConfig('cnf_paylog_status');
		$cnf_protocal = getConfig('cnf_protocal');
		$cnf_banklog_type = getConfig('cnf_banklog_type');
		$yes_or_no = getConfig('yes_or_no');
		foreach ($list as &$item) {
			$item['money'] = floatval($item['money']);
			$item['real_money'] = floatval($item['real_money']);
			$item['rate'] = floatval($item['rate']);
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			if (!$item['pay_time']) {
				$item['pay_time'] = '/';
			} else {
				$item['pay_time'] = date('m-d H:i:s', $item['pay_time']);
			}
			$item['is_first_flag'] = $yes_or_no[$item['is_first']];
			$item['status_flag'] = $cnf_paylog_status[$item['status']];
			$item['receive_protocol_flag'] = $cnf_protocal[$item['receive_protocol']];
			$item['receive_type_flag'] = $cnf_banklog_type[$item['receive_type']];
			$item['pay_banners'] = json_decode($item['pay_banners'], true);
			if (!$item['pay_banners']) {
				$item['pay_banners'] = [];
			}
			$up_user = getUpUser($item['uid'], true);
			$item['agent1_account'] = '/';
			$item['agent2_account'] = '/';
			$item['zt_account'] = '/';
			foreach ($up_user as $uk => $uv) {
				if ($uk == 0) {
					$item['zt_account'] = $uv['account'];
				}
				if ($uv['gid'] == 71) {
					$item['agent1_account'] = $uv['account'];
				} elseif ($uv['gid'] == 81) {
					$item['agent2_account'] = $uv['account'];
				}
			}

			if ($params['s_trans']) {
				$item['status_flag'] = lang2($item['status_flag']);
				$item['is_first_flag'] = $item['is_first'] ? 'Yes' : 'No';
			}
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => floatval($count_item['money']),
			'real_money' => floatval($count_item['real_money']),
			'limit' => $this->pageSize,
			//'$where0' => $where,
		];
		if ($params['page'] < 2) {
			$return_data['paylog_status_arr'] = $cnf_paylog_status;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//审核
	public function _paylog_check()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['status'] = intval($params['status']);
		//$params['rate']=floatval($params['rate']);
		$params['rate'] = 1;
		$item_id = intval($params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少订单参数');
		}
		$cnf_paylog_status = getConfig('cnf_paylog_status');
		if (!array_key_exists($params['status'], $cnf_paylog_status)) {
			ReturnToJson(-1, '未知审核状态');
		}
		Db::startTrans();
		try {
			$order = Db::table('fin_paylog')->whereRaw('id=:id', ['id' => $item_id])->lock(true)->find();
			if (!$order || $order['status'] >= 99) {
				ReturnToJson(-1, '不存在相应的订单');
			}
			if (!in_array($order['status'], [1, 2, 3])) {
				ReturnToJson(-1, '该订单当前状态不可操作');
			}
			if ($order['status'] == 1 && $order['pay_type'] != 'offline') {
				ReturnToJson(-1, '该订单当前状态不可操作');
			}
			if ($pageuser['gid'] > 41) {
				$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
				if (!in_array($order['uid'], $uid_arr)) {
					ReturnToJson('-1', '非下级用户的记录无法操作');
				}
			}
			$fin_paylog = [
				'status' => $params['status'],
				'check_remark' => $params['check_remark'],
				'check_id' => $pageuser['id'],
				'check_time' => NOW_TIME,
				'check_ip' => CLIENT_IP
			];
			$res2 = true;
			$res3 = true;
			$rate = 1;
			$real_money = '/';
			if ($params['status'] == 9) { //确认到账
				$real_money = $order['real_money'];
				// if ($order['receive_type'] == 4) {
				// 	$rate = getUsdtPrice('cny');
				// 	if (!$rate) {
				// 		ReturnToJson(-1, '获取汇率失败');
				// 	}
				// 	$fin_paylog['rate'] = $rate;
				// 	$real_money = intval($rate * $order['money'] * 100) / 100;
				// 	$fin_paylog['real_money'] = $real_money;
				// }
				$fin_paylog['pay_time'] = NOW_TIME;

				$check_order = Db::table('fin_paylog')->where("uid={$order['uid']} and status=9")->find();
				if (!$check_order) {
					$fin_paylog['is_first'] = 1;
				}

				$wallet = getWallet($order['uid'], 1);
				if (!$wallet) {
					throw new \Exception('钱包获取异常');
				}
				$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
				$wallet_data = [
					'balance' => $wallet['balance'] + $real_money
				];
				//更新钱包余额
				Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
				//写入流水记录
				$result = walletLog([
					'wid' => $wallet['id'],
					'uid' => $wallet['uid'],
					'type' => 21,
					'money' => $real_money,
					'ori_balance' => $wallet['balance'],
					'new_balance' => $wallet_data['balance'],
					'fkey' => $order['osn'],
					'remark' => 'Recharge.'
				]);
				if (!$result) {
					throw new UnexpectedValueException('流水记录写入失败');
				}


				//更新订单状态
				$osn = $check_order['osn'];
				$glstr = [
					'🗣恭喜出单，业绩长虹，蒸蒸日上，大吉大利❤️🤏',
					'🗣恭喜出单，人生就是这样,耐得住寂寞才能守得住繁华,该奋斗的年龄不要选择了安逸❤️🤏',
					'🗣恭喜出单，人有理想才有希望,那怕看到的是很迷茫,只要有坚定不移的信念,就不惧艰难痛苦去勇敢面对。❤️🤏',
					'🗣恭喜出单，成功的道路上充满荆棘,苦战方能成功。祝贺你成功开首单,你成功的道路正在开启。❤️🤏',
					'🗣恭喜开单，你的努力终于有了回报，你的经历，正在谱写你的简历；你平时的坚持，藏着你未来的样子。❤️🤏',
					'🗣恭喜出单，勤奋的态度和主动积极的精神才能让你获得成功，恭喜你，你做到了！这是你的第一单，但绝对不是最后一单！❤️🤏',
					'🗣恭喜出单，你的努力终于有了回报，你的经历，正在谱写你的简历；你平时的坚持，藏着你未来的样子。❤️🤏',
					'🗣恭喜出单，人有理想才有希望,那怕看到的是很迷茫,只要有坚定不移的信念,就不惧艰难痛苦去勇敢面对。❤️🤏',
					'💥恭喜开单，你从来不知道，一个认真努力的你，可以有多么优秀。☀️ ',
					'🌈恭喜开单，每一道做对的题都是为了让你遇到更优秀的人，每一道做错的题都是为了让你遇到更匹配的人。🌞',
					'🌞恭喜开单，为此，我会一直努力下去，每天充满动力，沿途春暖花开。☄️',
					'❤️‍🔥恭喜开单，当你感觉到难的时候，就是你在进步的时候。机械、重复的工作是最低效的成长，而逼自己去做难的事、你没干过的事，就能大大提升你的潜力。❤️',
					'💞恭喜开单，成功不是将来才有的，而是从决定去做的那一刻起，持续累积而成。🔅',
					'🔱恭喜开单，不要让自己每天的时间分散，碎片化是效率的生死大敌，少刷朋友圈，少水群，因为这只会浪费你的时间。📣',
					'♥️恭喜开单，生活不会向你许诺什么，尤其不会向你许诺成功。它只会给你挣扎、痛苦和煎熬的过程。所以要给自己一个梦想，之后朝着那个方向前进。📢',
					'🗣恭喜开单，只要你不颓废，不消极，一直悄悄酝酿着乐观，培养着豁达，坚持着善良，始终朝着梦想前行，永远在路上，就没有到达不了的远方。🤭',
					'🗣恭喜开单！选一个方向，定一个时间。剩下的只管努力与坚持，时间会给我们最后的答案☀️',

					'🔥恭喜开单，没有口水与汗水，就没有成功的泪水。坚持就是胜利，顺利拿下阿三人头！😡',
					'💥恭喜开单，部门业绩大比拼，大家团结亦齐心，领导带队冲向前，各队精明又干练。看到终点齐冲刺，老板见此予鼓励，加油都是好样地，业绩比以往翻三番！🫂',
					'🥰朋友，恭喜你出单，我特邀请了几位群友前来祝贺：“开单大吉”，上午到了“万事如意”，中午来了“开心每天”，下午陪了“鸿运当头”，晚上有了“和和睦睦”！祝贺你天天开单，坐着就有单随你来！嘿嘿！😘',
					'🫂成功靠朋友，成长靠对手，成就靠团队。创意是金钱，策划显业绩，思考才致富。知道是知识，做到才智慧，多做少多说。积极激励我，多劳多收获，汗水育成果。梦想聚团队，团队铸梦想，激情快乐人',
					'💥胜利女神一定会眷顾我们，但是你不奋斗，你的才华如何配得上你的任性。不奋斗你的脚步又如何赶上家人老去的速度。不奋斗，世界这么大你怎么去看',
					'🔥恭喜出单，如果你只局限于对别人成就的羡慕和徒做无聊的叹息,从不为争取自己的理想而付出努力,那么你心中的巨人将永远沉睡。因此,只有积极的心志才能唤醒你心中酣睡的巨人,才能让你从消极走向积极,从被动走向成功!',
					'🫂我们经常不能坚持完成自己梦想,是因为我们没有毅力,我们害怕困难,不懂得怎么面对困难,对他是敬而远之。我们害怕失败。其实毅力是带给所有人夺取胜利之果的动力。如果连追求自己理想的毅力都没有的话,又怎么奢望能够像别人一样实现自己的理想,到达梦想中的终点呢?',
				];

				//	$userinfo = Db::table('sys_user')->where("id='{$order['uid']}'")->find();

				$up_user = getUpUser($order['uid'], true);
				$tjr = '/';
				$rjdl = '/';
				$pidg1 = '0';
				foreach ($up_user as $uk => $uv) {
					if ($uk == 0)
						$tjr = $uv['account'];
					if ($uv['gid'] == 81)
						$rjdl = $uv['account'];
					if ($uv['gid'] == 71)
						$pidg1 = $uv['id'];
				}
				$token = $this->token;
				$chatId = '-1001977279590';
				foreach ($this->tokens as $key => $val) {
					if ($key == $pidg1) {
						$token = $val['tk'];
						$chatId = $val['id'];
						break;
					}
				}
				$account = rand(0, count($glstr) - 1);
				$str = "订单号：{$osn}\n二级代理：{$rjdl}\n 推荐人：{$tjr}\n 订单金额：{$order['money']}\n{$glstr[$account]}";

				//$temps =	$this->send_photo('-709672358', $str);
				$temps = $this->send_photo($chatId, $str, $token);

			}
			$res = Db::table('fin_paylog')->where("id={$order['id']}")->update($fin_paylog);
			if (!$res || !$res2 || !$res3) {
				throw new \Exception('系统繁忙请稍后再试');
			}
			Db::commit();
			$this->redis->rmall(RedisKeys::USER_WALLET . $order['uid']);
			//更新首充
			$user = Db::table('sys_user')->where("id={$order['uid']}")->find();
			if (!$user['first_pay_day']) {
				updateUserinfo($user['id'], ['first_pay_day' => date('Ymd')]);
			} else {
				$this->redis->rmall(RedisKeys::USER_WALLET . $user['id']);
			}
			$cnf_paylog_status = getConfig('cnf_paylog_status');
			$return_data = [
				'rate' => $rate,
				'real_money' => $real_money,
				'status' => $fin_paylog['status'],
				'status_flag' => $cnf_paylog_status[$fin_paylog['status']],
				'check_remark' => $fin_paylog['check_remark'],
				'pay_time' => empty ($fin_paylog['pay_time']) ? '/' : date('m-d H:i:s', $fin_paylog['pay_time'])
			];
			if (isset ($fin_paylog['is_first'])) {
				$yes_or_no = getConfig('yes_or_no');
				$return_data['is_first'] = $fin_paylog['is_first'];
				$return_data['is_first_flag'] = $yes_or_no[$fin_paylog['is_first']];
			}
			ReturnToJson(1, '操作成功', $return_data);
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
	}


	public function _bank_check()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$type = intval($params['type']);
		$item_id = $params['osn'];
		if (!$item_id) {
			ReturnToJson(-1, '缺少订单参数', $params['osn']);
		}
		$item = Db::table('fin_paylog')->where('osn', $item_id)->find();
		if ($type == 1) { //到账 
			if (!$item) {
				ReturnToJson(-1, '订单不存在');
			}
			$url = 'http://47.243.82.107/api/Notify/wowpay/payAuto?osn=' . $item['osn']; //88864d4f65e54066
			$R = $this->curl_post($url, []);
			if ($R == 'success') {
				$item = Db::table('fin_paylog')->where('osn', $item_id)->find();
			}
		} else { //失败
			if (!$item) {
				ReturnToJson(-1, '订单不存在');
			}
			Db::table('fin_paylog')->where('osn', $item_id)->update(['status' => 3]);
			$item = Db::table('fin_paylog')->where('osn', $item_id)->find();
		}
		ReturnToJson(1, '成功', $item);
	}


	///手动处理充值
	public function _bank_check_onlie()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$type = intval($params['type']);//	1到账  2未到账
		$item_id = $params['osn'];
		if (!$item_id) {
			ReturnToJson(-1, '缺少订单参数', $params['osn']);
		}
		$item = Db::table('fin_paylog')->where('osn', $item_id)->find();
		if (!$item)
			ReturnToJson(-1, '订单不存在');

		if ($type == 1) { //到账 
			if (!$item)
				ReturnToJson(-1, '订单不存在');
			$token = getParam('token');
			$url = REQUEST_SCHEME . '://' . HTTP_HOST . "/api/Notify/autopay/payAuto?osn={$item['osn']}&token={$token}";  //88864d4f65e54066
			$R = $this->curl_post($url, []);
			if ($R == 'success') {
				$item = Db::table('fin_paylog')->where('osn', $item_id)->find();
			} else {
				ReturnToJson(-1, '失败', $R);
			}
		} else { //失败 
			Db::table('fin_paylog')->where('osn', $item_id)->update(['status' => 3]);
			$item = Db::table('fin_paylog')->where('osn', $item_id)->find();
		}
		ReturnToJson(1, '成功', [$item, $R]);
	}




	protected function curl_post($url, $data)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
	protected function send_photo($chatId, $text, $token)
	{
		$cnf_domain = trim(trim($_ENV['cnf_domain']), '/');
		$tt = rand(0, 1);
		$num = 0;
		$imgurl = '';
		if ($tt == 1) {
			$ttc = rand(1, 106);
			$imgurl = REQUEST_SCHEME . '://' . HTTP_HOST . '/h5/img/img (' . $ttc . ').gif';
		} else {
			$num = rand(1, 42);
			$imgurl = REQUEST_SCHEME . '://' . HTTP_HOST . '/h5/img/' . $num . '.gif';
		}
		$url = '';
		$data = [];
		if (strstr($imgurl, 'jpg') == false) {
			$url = 'https://api.telegram.org/bot' . $token . '/sendAnimation';
			$data = [
				'chat_id' => $chatId,
				'animation' => $imgurl,
				'caption' => $text,
			];
		} else {
			$url = 'https://api.telegram.org/bot' . $this->token . '/sendPhoto';
			$data = [
				'chat_id' => $chatId,
				'caption' => $text,
				'photo' => $imgurl
			];
		}
		return $this->curl_post($url, $data);
	}
	protected $token = '6063458434:AAHuS8OUB4Xy9wYtQCqKJWzCaWln6GFP_po';
	protected $tokens = [
		'232723' => ['id' => '-1001974041997', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//05
		'710849' => ['id' => '-1001974041997', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//11
		'218946' => ['id' => '-1001974041997', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//09 

		'676922' => ['id' => '-1001857373097', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//06   
		'727374' => ['id' => '-1001834084818', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//03  
		'675079' => ['id' => '-1001834084818', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//04 
		'522519' => ['id' => '-1001908498610', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//01 
		'954439' => ['id' => '-1001810740604', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//02 
		'216758' => ['id' => '-1001748709723', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//07 
		'722526' => ['id' => '-1001975703973', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//08  

		'618902' => ['id' => '-1001957422808', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//10   
		'276423' => ['id' => '-1001985646420', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//12  
		'832687' => ['id' => '-1001926855946', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//13 
		'398850' => ['id' => '-1001975941853', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'],
		//14
		'465396' => ['id' => '-1001810911853', 'tk' => '6078393344:AAHXvMbLi6VwLr8nexx3GAtwx6BI7O6DiY8'], //15
	];

	//////////////////////////////////////////////////////////////////
	//取款记录
	public function _cashlog()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_status'] = intval($params['s_status']);
		$params['s_money_from'] = floatval($params['s_money_from']);
		$params['s_money_to'] = floatval($params['s_money_to']);
		$this->pageSize = intval($params['s_sizes']) ?? 30;
		$txflg = $params['txflg'];
		$where = "1=1";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}

		if ($params['s_puser']) {
			$s_keyword = $params['s_puser'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("usn='{$s_keyword}' or id='{$s_keyword}' or account='{$s_keyword}' or email='{$s_keyword}' or realname='{$s_keyword}' or nickname='{$s_keyword}'")->find();
			if ($s_puser) {
				$uid_arr = getDownUser_new($s_puser['id']);
			}
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}

		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}

		if ($params['s_start_time2'] && $params['s_end_time2']) {
			$start_time2 = strtotime($params['s_start_time2'] . ' 00:00:00');
			$end_time2 = strtotime($params['s_end_time2'] . ' 23:59:59');
			if ($start_time2 > $end_time2) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.pay_time between {$start_time2} and {$end_time2}";
		}

		$where .= empty ($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		if (isset ($params['s_pay_status']) && $params['s_pay_status'] != 'all') {
			$params['s_pay_status'] = intval($params['s_pay_status']);
			$where .= " and log.pay_status={$params['s_pay_status']}";
		}

		if ($params['s_user_account']) {
			$s_puser = Db::table('sys_user')->where(" account='{$params['s_user_account']}' ")->find();
			$where .= " and log.uid='{$s_puser['id']}'";
		}

		if ($params['s_osn']) {
			$where .= " and (log.osn='{$params['s_osn']}'  )";
		}

		if ($params['s_oldosn']) {
			$where .= " and (log.oldosn='{$params['s_oldosn']}')";
		}

		if ($params['s_paytype_s']) {
			$where .= " and (log.pay_type_bf='{$params['s_paytype_s']}' or log.pay_type='{$params['s_paytype_s']}')";
		}

		if (!$params['s_user_account'] && !$params['s_paytype_s'] && !$params['s_osn'] && !$params['s_oldosn']) {
			$where .= empty ($params['s_keyword']) ? '' : " and (log.oldosn='{$params['s_keyword']}' or log.id='{$params['s_keyword']}' or log.osn='{$params['s_keyword']}' or u.account='{$params['s_keyword']}' or log.pay_type='{$params['s_keyword']}' or log.receive_account='{$params['s_keyword']}' or log.receive_address='{$params['s_keyword']}' or log.receive_realname like '%{$params['s_keyword']}%')";
		}



		if ($params['s_money_from'] >= 0 && $params['s_money_to'] > 0) {
			if ($params['s_money_from'] > $params['s_money_to']) {
				ReturnToJson(-1, '起始金额不能小于结束金额');
			}
			$where .= " and log.real_money between {$params['s_money_from']} and {$params['s_money_to']}";
		}

		if ($params['s_lt14']) {
			if ($params['s_lt14'] == 1) {
				$where .= " and LENGTH(receive_account)>=14";
			} else {
				$where .= " and LENGTH(receive_account)<14";
			}
		}
		//收款账号
		if ($params['receive_account']) {
			$where .= " and (log.receive_account='{$params['receive_account']}') ";
		}
		//id
		if ($params['id']) {
			$where .= " and (log.id={$params['id']}) ";
		}
		//查找对应的一级代理账号
		if ($params['pidg1']) {
			$upuser = Db::table("sys_user")->where("account={$params['pidg1']} ")->find();
			$where .= " and (u.pidg1 ={$upuser["id"]})";
		}
		//查找对应的二级代理账号
		if ($params['pidg2']) {
			$upuser = Db::table("sys_user")->where("account={$params['pidg2']} ")->find();
			$where .= " and (u.pidg2 ={$upuser["id"]})";
		}
		//查找对应的上级账号
		if ($params['pid']) {
			$upuser = Db::table("sys_user")->where("account={$params['pid']} ")->find();
			$uid_arr = getDownUser_new($upuser["id"]);
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}

		$count_item = Db::table('fin_cashlog log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money,sum(log.real_money) as real_money')
			->where($where)
			->find();

		$list = Db::view(['fin_cashlog' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl', 'pidg1', 'pidg2', 'pid'], 'log.uid=u.id', 'LEFT')
			->view(['sys_user' => 'uk'], ['account' => 'agent1_account'], 'u.pidg1=uk.id', 'LEFT')
			->view(['sys_user' => 'ud'], ['account' => 'agent2_account'], 'u.pidg2=ud.id', 'LEFT')
			->view(['cnf_bank' => 'bk'], ['name' => 'bank_name'], 'log.receive_bank_id=bk.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_cashlog_status = getConfig('cnf_cashlog_status');
		$cnf_cashlog_pay_status = getConfig('cnf_cashlog_pay_status');

		$real_money1 = 0;
		$money1 = 0;
		// $paytype = Db::view('fin_dtype')->select()->toArray();
		$cnf_protocol = getConfig('cnf_protocal');

		foreach ($list as &$item) {

			// 声明$real_money1 并且累计real_money值  
			$real_money1 += $item['real_money'];
			// 声明$real_money1 并且累计real_money值  
			$money1 += 1;

			$item['money'] = floatval($item['money']);
			$item['real_money'] = floatval($item['real_money']);
			$item['fee'] = floatval($item['fee']);
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			if (!$item['check_time']) {
				$item['check_time'] = '/';
			} else {
				$item['check_time'] = date('m-d H:i:s', $item['check_time']);
			}
			if (!$item['pay_time']) {
				$item['pay_time'] = '/';
			} else {
				$item['pay_time'] = date('m-d H:i:s', $item['pay_time']);
			}
			$item['status_flag'] = $cnf_cashlog_status[$item['status']];
			$item['pay_status_flag'] = $cnf_cashlog_pay_status[$item['pay_status']];
			$item['receive_protocol_flag'] = $cnf_protocol[$item['receive_protocol']];
			$item['zt_account'] = '/';

			if ($txflg == 1) {

			} else if ($txflg == 2) {
				$item['zt_account'] = Db::table('sys_user')->where('id', $item['pid'])->value('account');
				$item['agent1_account'] = Db::table('sys_user')->where('id', $item['pidg1'])->value('account');
				$item['agent2_account'] = Db::table('sys_user')->where('id', $item['pidg2'])->value('account');
			} else {
				$up_user = getUpUser($item['uid'], true);
				foreach ($up_user as $uk => $uv) {
					if ($uk == 0) {
						$item['zt_account'] = $uv['account'];
					}
					if ($uv['gid'] == 71) {
						$item['agent1_account'] = $uv['account'];
					} elseif ($uv['gid'] == 81) {
						$item['agent2_account'] = $uv['account'];
					}
				}
			}


			if ($params['s_trans']) {
				$item['status_flag'] = lang2($item['status_flag']);
				$item['pay_status_flag'] = lang2($item['pay_status_flag']);
			}
		}

		$dtype = Db::table('fin_dtype')->field(['type', 'id'])->where('status=1 or status=3')->select()->toArray();

		$return_data = [
			'list' => $list,
			'dtype' => $dtype,
			'count' => intval($count_item['cnt']),
			'money' => floatval($count_item['money']),
			'real_money' => floatval($count_item['real_money']),
			'money1' => ($money1),
			'real_money1' => round(floatval($real_money1), 2),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['status_arr'] = $cnf_cashlog_status;
			$return_data['pay_status_arr'] = $cnf_cashlog_pay_status;
			// $return_data['paytype_arr'] = $paytype;
		}
		ReturnToJson(1, 'ok', $return_data);
	}


	//提现记录变更
	private function cashlogCheckAct($pageuser, $item_id, $status, $remark = '', $pay_status = '', $s_paytype = '')
	{
		if ($status == 9 && $s_paytype == '') {
			return ['code' => -1, 'msg' => '代付通道不能为空'];
		}

		Db::startTrans();
		try {
			$order = Db::table('fin_cashlog')->whereRaw('id=:id', ['id' => $item_id])->lock(true)->find();
			$uid = $order['uid'];
			if (!$order || $order['status'] >= 99) {
				//ReturnToJson(-1,'不存在相应的订单');
				return ['code' => -1, 'msg' => '不存在相应的订单'];
			}
			if ($order['status'] == 3) {
				//ReturnToJson(-1,'订单当前状态不可操作');
				return ['code' => -1, 'msg' => '订单当前状态不可操作'];
			}
			if ($pageuser['gid'] > 41) {
				$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
				if (!in_array($order['uid'], $uid_arr)) {
					//ReturnToJson(-1,'非下级用户的记录无法操作');
					return ['code' => -1, 'msg' => '非下级用户的记录无法操作'];
				}
			}
			$fin_cashlog = [
				'status' => $status,
				'check_remark' => $remark,
				'check_id' => $pageuser['id'],
				'check_time' => NOW_TIME,
				'check_ip' => CLIENT_IP,
				'pay_type_bf' => $s_paytype,
			];
			if ($pay_status != '') {
				$fin_cashlog['pay_status'] = $pay_status;
			}
			$res2 = true;
			$res3 = true;
			$needBack = false;
			if ($status == 3) { //不通过
				if (!($order['pay_status'] == 0 || $order['pay_status'] == 3)) {
					//ReturnToJson(-1,'订单当前状态不可操作');
					return ['code' => -1, 'msg' => '订单当前状态不可操作'];
				}
				//不通过,不用处理任何业务
				$needBack = true;
			} elseif ($status == 9) { //已通过
				if (!($order['pay_status'] == 0 || $order['pay_status'] == 3)) {
					//ReturnToJson(-1,'订单当前状态不可操作');
					return ['code' => -1, 'msg' => '订单当前状态不可操作'];
				}
				$fin_cashlog['pay_status'] = 1;
				if ($order['pay_status'] == 3) {
					$fin_cashlog['osn'] = getRsn();
				}
			}

			if ($needBack) { //归还额度
				$back_money = $order['money'];
				if ($order['fee_mode'] == 2) {
					$back_money = $order['money'] + $order['fee'];
				}

				$wallet = getWallet($order['uid'], 2);
				if (!$wallet) {
					//ReturnToJson(-1,'钱包获取异常');
					return ['code' => -1, 'msg' => '钱包获取异常'];
				}
				$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
				$wallet_data = [
					'balance' => $wallet['balance'] + $back_money
				];
				Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
				$result = walletLog([
					'wid' => $wallet['id'],
					'uid' => $wallet['uid'],
					'type' => 33,
					'money' => $back_money,
					'ori_balance' => $wallet['balance'],
					'new_balance' => $wallet_data['balance'],
					'fkey' => $order['osn'],
					'remark' => 'Withdrawal refund-' . $order['osn']
				]);
				if (!$result) {
					throw new Exception('写入流水日志失败');
				}
			}
			$res = Db::table('fin_cashlog')->where("id={$order['id']}")->update($fin_cashlog);
			if ($res === false || !$res2 || !$res3) {
				throw new \Exception('系统繁忙请稍后再试' . $res . '-' . $res2 . '-' . $res3);
			}
			Db::commit();
			$this->redis->rmall(RedisKeys::USER_WALLET . $order['uid']);
			$cnf_cashlog_status = getConfig('cnf_cashlog_status');
			$return_data = [
				'status' => $fin_cashlog['status'],
				'status_flag' => $cnf_cashlog_status[$fin_cashlog['status']],
				'check_remark' => $fin_cashlog['check_remark'],
				'check_time' => date('m-d H:i', $fin_cashlog['check_time'])
			];
			if (isset ($fin_cashlog['pay_status'])) {
				$cnf_cashlog_pay_status = getConfig('cnf_cashlog_pay_status');
				$return_data['pay_status'] = $fin_cashlog['pay_status'];
				$return_data['pay_status_flag'] = $cnf_cashlog_pay_status[$fin_cashlog['pay_status']];
			}
			if (isset ($fin_cashlog['pay_type'])) {
				$return_data['pay_type'] = $fin_cashlog['pay_type'];
			}
			$this->redis->rmall(RedisKeys::USER_WALLET . "{$uid}");
			//ReturnToJson(1,'操作成功',$return_data);
			return ['code' => 1, 'msg' => '操作成功', 'data' => $return_data];
		} catch (\Exception $e) {
			Db::rollback();
			//ReturnToJson(-1,'系统繁忙请稍后再试'.$e->getMessage());
			return ['code' => -1, 'msg' => '系统繁忙请稍后再试' . $e->getMessage()];
		}
	}



	public function _cashlog_check2()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['status'] = intval($params['status']);
		$item_id = intval($params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少订单参数');
		}
		$power = hasPower($pageuser, 'Finance_cashlog_check');
		if (!$power) {
			if ($params['status'] != 3) {
				ReturnToJson(-1, '没有操作权限');
			} else {
				$power = hasPower($pageuser, 'Finance_cashlog_check_reject');
				if (!$power) {
					ReturnToJson(-1, '没有操作权限');
				}
			}
		}
		$status = $params['status'];
		$remark = $params['check_remark'];
		$pay_status = $params['pay_status'];
		Db::startTrans();
		try {
			$order = Db::table('fin_cashlog')->whereRaw('id=:id', ['id' => $item_id])->lock(true)->find();
			if (!$order || $order['status'] >= 99) {
				return ['code' => -1, 'msg' => '不存在相应的订单'];
			}

			if ($pageuser['gid'] > 41) {
				$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
				if (!in_array($order['uid'], $uid_arr)) {
					return ['code' => -1, 'msg' => '非下级用户的记录无法操作'];
				}
			}
			$fin_cashlog = [
				'status' => $status,
				'check_remark' => $remark,
				'check_id' => $pageuser['id'],
				'check_time' => NOW_TIME,
				'check_ip' => CLIENT_IP,
				'pay_status' => $pay_status,
				'pay_time' => ($status == 9 && $pay_status == 9) ? NOW_TIME : 0,
			];
			$res2 = true;
			$res3 = true;
			$res = Db::table('fin_cashlog')->where("id={$order['id']}")->update($fin_cashlog);
			if ($res === false || !$res2 || !$res3) {
				throw new \Exception('系统繁忙请稍后再试' . $res . '-' . $res2 . '-' . $res3);
			}
			Db::commit();
			$cnf_cashlog_status = getConfig('cnf_cashlog_status');
			$return_data = [
				'status' => $fin_cashlog['status'],
				'status_flag' => $cnf_cashlog_status[$fin_cashlog['status']],
				'check_remark' => $fin_cashlog['check_remark'],
				'check_time' => date('m-d H:i', $fin_cashlog['check_time']),
				'pay_time' => date('m-d H:i', $fin_cashlog['pay_time']),
			];
			if (isset ($fin_cashlog['pay_status'])) {
				$cnf_cashlog_pay_status = getConfig('cnf_cashlog_pay_status');
				$return_data['pay_status'] = $fin_cashlog['pay_status'];
				$return_data['pay_status_flag'] = $cnf_cashlog_pay_status[$fin_cashlog['pay_status']];
			}
			if (isset ($fin_cashlog['pay_type'])) {
				$return_data['pay_type'] = $fin_cashlog['pay_type'];
			}
			echo json_encode(['code' => 1, 'msg' => '操作成功', 'data' => $return_data]);
		} catch (\Exception $e) {
			Db::rollback();
			echo json_encode(['code' => -1, 'msg' => '系统繁忙请稍后再试' . $e->getMessage()]);
		}
	}

	//审核
	public function _cashlog_check()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['status'] = intval($params['status']);
		$item_id = intval($params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少订单参数');
		}
		if (!in_array($params['status'], [3, 9])) {
			ReturnToJson(-1, '未知审核状态');
		}
		$power = hasPower($pageuser, 'Finance_cashlog_check');
		if (!$power) {
			if ($params['status'] != 3) {
				ReturnToJson(-1, '没有操作权限');
			} else {
				$power = hasPower($pageuser, 'Finance_cashlog_check_reject');
				if (!$power) {
					ReturnToJson(-1, '没有操作权限');
				}
			}
		}
		$result = $this->cashlogCheckAct($pageuser, $item_id, $params['status'], $params['check_remark'], $params['pay_status'], $params['s_paytype']);
		echo json_encode($result, 256);
	}

	//一键审核
	public function _cashlog_check_all()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['status'] = intval($params['status']);
		$ids = [];
		if (!$this->params['ids']) {
			ReturnToJson(-1, '至少选择一项');
		}
		foreach ($this->params['ids'] as $id) {
			$id = intval($id);
			if ($id < 0) {
				continue;
			}
			$ids[] = $id;
		}
		if (!$ids) {
			ReturnToJson(-1, '至少选择一项');
		}
		if (!in_array($params['status'], [3, 9])) {
			ReturnToJson(-1, '未知审核状态');
		}
		$list = [];
		$error = [];

		if (!$params['falseflg']) {
			$params['falseflg'] = '0';
		}

		if ($params['status'] == 9 && $params['falseflg'] == '0') {
			//批量修改 fin_cashlog 的记录
			$fin_cashlog = [
				'status' => $params['status'],
				'check_remark' => $params['s_paytype'],
				'check_id' => $pageuser['id'],
				'check_time' => NOW_TIME,
				'check_ip' => CLIENT_IP,
				'pay_type_bf' => $params['s_paytype'],
				'pay_status' => 1,
			];
			$res = Db::table('fin_cashlog')->where("pay_status=0 and id in(" . implode(',', $ids) . ")")->update($fin_cashlog);
			if ($res === false) {
				ReturnToJson(-1, '系统繁忙请稍后再试');
			}
		} else {
			foreach ($ids as $item_id) {
				$result = $this->cashlogCheckAct($pageuser, $item_id, $params['status'], $params['s_paytype'], '', $params['s_paytype']);
				if ($result['code'] == 1) {
					Db::commit();
					$list[] = [
						'id' => $item_id,
						'data' => $result['data']
					];
				} else {
					Db::rollback();
					$error[] = [
						'id' => $item_id,
						'msg' => $result['msg']
					];
				}
			}
		}


		// foreach ($ids as $item_id) {
		// 	$result = $this->cashlogCheckAct($pageuser, $item_id, $params['status'], $params['s_paytype'], '', $params['s_paytype']);
		// 	if ($result['code'] == 1) {
		// 		Db::commit();
		// 		$list[] = [
		// 			'id' => $item_id,
		// 			'data' => $result['data']
		// 		];
		// 	} else {
		// 		Db::rollback();
		// 		$error[] = [
		// 			'id' => $item_id,
		// 			'msg' => $result['msg']
		// 		];
		// 	}
		// }
		$return_data = [
			'list' => $list,
			'error' => $error
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	//##################用户资产管理开始##################
	public function _wallet()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		$params['uid'] = intval($params['uid']);
		$params['s_cid'] = intval($params['s_cid']);

		$where = "1=1";

		if (!checkDataAction()) {
			//$where.=" and log.uid={$pageuser['id']}";
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = $pageuser['id'];
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}

		$where .= empty ($params['uid']) ? '' : " and log.uid={$params['uid']}";
		$where .= empty ($params['s_cid']) ? '' : " and log.cid={$params['s_cid']}";
		$where .= empty ($params['s_keyword']) ? '' : " and (log.waddr='{$params['s_keyword']}' or u.account='{$params['s_keyword']}')";

		$count_item = Db::table('wallet_list log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('cnf_currency c', 'log.cid=c.id')
			->fieldRaw('count(1) as cnt,sum(log.balance) as balance,sum(log.fz_balance) as fz_balance')
			->where($where)
			->find();

		$list = Db::view(['wallet_list' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->view(['cnf_currency' => 'c'], ['name' => 'currency', 'symbol', 'icon'], 'log.cid=c.id', 'LEFT')
			->where($where)
			->order(['log.create_time' => 'desc', 'log.uid' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$this->parseWallet($item);
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'balance' => floatval($count_item['balance']),
			'fz_balance' => floatval($count_item['fz_balance']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			if ($params['uid']) {
				$return_data['user'] = Db::table('sys_user')->field(['account', 'nickname', 'headimgurl'])->where(['id' => $params['uid']])->find();
			}
			$return_data['currency_arr'] = Db::table('cnf_currency')->where("status=2")->select()->toArray();
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	private function parseWallet(&$item)
	{
		$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
		return $item;
	}

	public function _wallet_pay()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['id'] = intval($params['id']);
		$params['type'] = intval($params['type']);
		$params['money'] = floatval($params['money']);
		if (!$params['id']) {
			ReturnToJson(-1, '缺少参数');
		}
		if (getPassword($params['password2']) != $pageuser['password2']) {
			ReturnToJson(-1, '二级密码不正确');
		}
		$return_data = [];
		Db::startTrans();
		try {
			$item = Db::table('wallet_list')->whereRaw('id=:id', ['id' => $params['id']])->lock(true)->find();
			if (!$item) {
				ReturnToJson('-1', '不存在相应的钱包');
			}
			$db_item = [];
			$ptype = 0;
			if ($params['type'] == 1) {
				if ($params['money'] == 0) {
					ReturnToJson(-1, '充值余额不能为0');
				}
				$db_item = [
					'balance' => $item['balance'] + $params['money']
				];
				if ($db_item['balance'] < 0) {
					ReturnToJson(-1, '用户余额不足');
				}
				$ptype = 11;
			} elseif ($params['type'] == 2) {
				if ($params['money'] < 0) {
					ReturnToJson(-1, '冻结不能小于0');
				}
				$db_item = [
					'balance' => $item['balance'] - $params['money'],
					'fz_balance' => $item['fz_balance'] + $params['money'],
				];
				if ($db_item['balance'] < 0) {
					ReturnToJson(-1, '可冻结额度不足');
				}
				$ptype = 12;
			} elseif ($params['type'] == 3) {
				if ($params['money'] < 0) {
					ReturnToJson(-1, '解冻不能小于0');
				}
				$db_item = [
					'balance' => $item['balance'] + $params['money'],
					'fz_balance' => $item['fz_balance'] - $params['money'],
				];
				if ($db_item['fz_balance'] < 0) {
					ReturnToJson(-1, '可解冻额度不足');
				}
				$ptype = 13;
			} else {
				ReturnToJson(-1, '未知充值类型');
			}
			Db::table('wallet_list')->where("id={$item['id']}")->update($db_item);
			$result = walletLog([
				'wid' => $item['id'],
				'uid' => $item['uid'],
				'type' => $ptype,
				'money' => $params['money'],
				'ori_balance' => $item['balance'],
				'new_balance' => $db_item['balance'],
				'remark' => $params['remark']
			]);
			if (!$result) {
				throw new \Exception('流水日志写入失败');
			}
			Db::commit();
			$this->redis->rm(RedisKeys::USER_WALLET . "{$item['uid']}_1");
			$this->redis->rm(RedisKeys::USER_WALLET . "{$item['uid']}_2");
			$this->redis->rmall(RedisKeys::USER_WALLET . "{$item['uid']}");
			$return_data['balance'] = $db_item['balance'];
			$return_data['fz_balance'] = isset ($db_item['fz_balance']) ? $db_item['fz_balance'] : $item['fz_balance'];
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试', $e->getMessage());
		}
		ReturnToJson(1, '操作成功', $return_data);
	}

	//资产账变记录
	public function _walletLog()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		$params['wid'] = intval($params['wid']);
		$params['s_cid'] = intval($params['s_cid']);
		$params['s_type'] = intval($params['s_type']);

		$where = "1=1";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = $pageuser['id'];
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}
		// if (!checkDataAction()) {
		// 	$where .= " and log.uid={$pageuser['id']}";
		// }
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		$where .= empty ($params['s_keyword']) ? '' : " and (w.waddr='{$params['s_keyword']}' or u.account='{$params['s_keyword']}' or log.remark like '%{$params['s_keyword']}%')";
		$where .= empty ($params['wid']) ? '' : " and log.wid={$params['wid']}";
		$where .= empty ($params['s_type']) ? '' : " and log.type={$params['s_type']}";
		$where .= empty ($params['s_cid']) ? '' : " and w.cid={$params['s_cid']}";

		$count_item = Db::table('wallet_log log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('wallet_list w', 'log.wid=w.id')
			->leftJoin('cnf_currency c', 'w.cid=c.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();

		$list = Db::view(['wallet_log' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->view(['wallet_list' => 'w'], [], 'log.wid=w.id', 'LEFT')
			->view(['cnf_currency' => 'c'], ['name' => 'currency', 'symbol', 'icon'], 'w.cid=c.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$this->parseWalletLog($item);
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => floatval($count_item['money']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$cnf_balance_type = getConfig('cnf_balance_type');
			$return_data['balance_type'] = $cnf_balance_type;
			if ($params['wid']) {
				$wallet = Db::view(['wallet_list' => 'log'], ['waddr', 'uid', 'balance', 'fz_balance'])
					->view(['cnf_currency' => 'c'], ['name' => 'currency', 'symbol', 'icon'], 'log.cid=c.id', 'LEFT')
					->where("log.id={$params['wid']}")->find();
				$return_data['wallet'] = $wallet;
				$return_data['user'] = Db::table('sys_user')->field(['account', 'nickname', 'headimgurl'])->where(['id' => $wallet['uid']])->find();
			}
			$return_data['currency_arr'] = Db::table('cnf_currency')->where("status=2")->select()->toArray();
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	private function parseWalletLog(&$item)
	{
		$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
		$cnf_balance_type = getConfig('cnf_balance_type');
		$item['type_flag'] = $cnf_balance_type[$item['type']];
		return $item;
	}

	//##################用户资产管理结束##################

	//收款方式管理
	public function _banklog()
	{
		$pageuser = checkPower();
		$params = $this->params;

		$params['s_type'] = intval($params['s_type']);
		$params['s_status'] = intval($params['s_status']);

		$where = "log.uid=0";
		if (!checkDataAction()) {
			//$where.=" and log.uid={$pageuser['id']}";
		}
		$where .= " and log.status<99";
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		$where .= empty ($params['s_type']) ? '' : " and log.type={$params['s_type']}";
		$where .= empty ($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		if ($params['s_keyword']) {
			if ($params['s_keyword'] == '平台') {
				$where .= " and log.uid=0";
			} else {
				$where .= " and (log.account='{$params['s_keyword']}' or u.account='{$params['s_keyword']}' or log.realname like '%{$params['s_keyword']}%')";
			}
		}

		$count_item = Db::table('cnf_banklog log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('cnf_bank bk', 'log.bank_id=bk.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['cnf_banklog' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account' => 'acc', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			// ->view(['cnf_bank' => 'bk'], ['name' => 'bank_name'], 'log.bank_id=bk.id', 'LEFT')
			->view(['cnf_area' => 'p'], ['name' => 'province_name'], 'log.province_id=p.id', 'LEFT')
			->view(['cnf_area' => 'c'], ['name' => 'city_name'], 'log.city_id=c.id', 'LEFT')
			->where($where)
			->order(['log.sort' => 'desc', 'log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_banklog_type = getConfig('cnf_banklog_type');
		$cnf_banklog_status = getConfig('cnf_banklog_status');
		$cnf_protocal = getConfig('cnf_protocal');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['type_flag'] = $cnf_banklog_type[$item['type']];
			$item['status_flag'] = $cnf_banklog_status[$item['status']];
			$item['protocal_flag'] = $cnf_protocal[$item['protocal']];
			if ($item['status'] == 2) {
				$item['status_switch'] = true;
			} else {
				$item['status_switch'] = false;
			}
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['status_arr'] = $cnf_banklog_status;
			$cnf_banklog_type = getConfig('cnf_banklog_type');
			$return_data['type_arr'] = $cnf_banklog_type;
			$return_data['bank_arr'] = Db::table('cnf_bank')->field(['id', 'name'])->where("status=2")->select()->toArray();
			$return_data['province_arr'] = Db::table('cnf_area')->field(['id', 'name'])->where("pid=0")->select()->toArray();
			$return_data['city_arr'] = [];
			$return_data['protocal_arr'] = getConfig('cnf_protocal');
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _banklog_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['bank_id'] = intval($params['bank_id']);
		$params['province_id'] = intval($params['province_id']);
		$params['city_id'] = intval($params['city_id']);
		$params['sort'] = intval($params['sort']);
		$params['status'] = intval($params['status']);
		$params['type'] = intval($params['type']);
		$params['protocal'] = intval($params['protocal']);
		$cnf_banklog_type = getConfig('cnf_banklog_type');
		if (!array_key_exists($params['type'], $cnf_banklog_type)) {
			ReturnToJson(-1, '未知类型');
		}
		$cnf_banklog_status = getConfig('cnf_banklog_status');
		if (!array_key_exists($params['status'], $cnf_banklog_status)) {
			ReturnToJson(-1, '未知状态');
		}
		if ($params['type'] == 1) {
			if (!$params['routing'] && !$params['ifsc']) {
				if (!$params['bank_id']) {
					ReturnToJson(-1, '请选择银行');
				}
				if (!$params['province_id'] || !$params['city_id']) {
					ReturnToJson(-1, '请选择省市');
				}
			}
			if (!$params['ifsc']) {
				ReturnToJson(-1, '请填写IFSC');
			}
		} else {
			if (!$params['qrcode']) {
				ReturnToJson(-1, '请上传收款码');
			}
		}

		if ($params['type'] < 4) {
			if (!$params['account']) {
				ReturnToJson(-1, '请填写账户');
			}
			if (!$params['realname']) {
				ReturnToJson(-1, '请填写姓名');
			}
		} else {
			if ($params['type'] == 4) {
				$protocal_arr = getConfig('cnf_protocal');
				if (!array_key_exists($params['protocal'], $protocal_arr)) {
					ReturnToJson(-1, '未知协议');
				}
			}
			if (!$params['address']) {
				ReturnToJson(-1, '请填写钱包地址');
			}
		}


		$db_item = [
			'bank_name' => $params['bank_name'],
			'account' => $params['account'],
			'realname' => $params['realname'],
			'sort' => empty ($params['sort']) ? 1000 : $params['sort'],
			'type' => $params['type'],
			'status' => $params['status'],
			'remark' => $params['remark'],
			'bank_id' => 0,
			'province_id' => 0,
			'city_id' => 0,
			'protocal' => 0,
			'address' => '',
			'routing' => '',
			'ifsc' => '',
			'upi' => '',
			'qrcode' => ''
		];
		if ($params['type'] == 1) {
			$db_item['bank_id'] = $params['bank_id'];
			$db_item['province_id'] = $params['province_id'];
			$db_item['city_id'] = $params['city_id'];
			$db_item['routing'] = $params['routing'];
			$db_item['ifsc'] = $params['ifsc'];
			$db_item['upi'] = $params['upi'];
		} else {
			$db_item['qrcode'] = $params['qrcode'];
		}
		if ($params['type'] == 4) {
			$db_item['protocal'] = $params['protocal'];
		}
		if ($params['type'] >= 4) {
			$db_item['address'] = $params['address'];
		}
		$model = Db::table('cnf_banklog');
		try {
			if ($item_id) {
				$item = $model->whereRaw('id=:id and status<99', ['id' => $item_id])->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				if (!checkDataAction() && $item['uid'] != $pageuser['id']) {
					//ReturnToJson(-1,'没有权限操作该记录');
				}
				$res = $model->where("id={$item['id']}")->update($db_item);
				$db_item['id'] = $item_id;
			} else {
				$db_item['uid'] = 0;
				$db_item['create_id'] = $pageuser['id'];
				$db_item['create_time'] = NOW_TIME;
				$res = $model->insertGetId($db_item);
				$db_item['id'] = $res;
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		actionLog(['opt_name' => '更新卡号', 'sql_str' => json_encode($db_item)]);
		$return_data = [
			'type_flag' => $cnf_banklog_type[$db_item['type']],
			'status_flag' => $cnf_banklog_status[$db_item['status']],
			'status_switch' => $db_item['status'] == 2 ? true : false
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _banklog_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('cnf_banklog');
		$item = $model->where("id={$item_id} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		} else {
			if (!checkDataAction() && $item['uid'] != $pageuser['id']) {
				//ReturnToJson(-1,'没有权限操作该记录');
			}
		}
		$db_item = ['status' => 99];
		try {
			$model->where("id={$item['id']}")->update($db_item);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		actionLog(['opt_name' => '删除卡号', 'sql_str' => json_encode($item, 256)]);
		ReturnToJson(1, '操作成功');
	}

	//获取某个用户的收款方式
	public function _getBanklog()
	{
		$pageuser = checkLogin();
		$uid = $pageuser['id'];
		$account = $this->params['account'];
		if ($account && checkDataAction()) {
			$user = getUserByAccount($account);
			if (!$user) {
				ReturnToJson(-1, '不存在相应的账号');
			}
			$uid = $user['id'];
		}
		$list = getBanklog($uid);
		$return_data = [
			'list' => $list
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//////////////////////////////////////////////////////////////////

	//支付通道
	public function _ptype()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$this->pageSize = 50;
		$where = "log.status<99";
		$where .= empty ($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		$where .= empty ($params['s_keyword']) ? '' : " and (log.name like '%{$params['s_keyword']}%')";
		$count_item = Db::table('fin_ptype log')
			->fieldRaw('count(1) as cnt')->where($where)->find();
		$list = Db::view(['fin_ptype' => 'log'], ['*'])
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_online_switch = getConfig('cnf_online_switch');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['update_time'] = date('m-d H:i:s', $item['update_time']);
			$item['status_flag'] = $cnf_online_switch[$item['status']];
			if ($item['status'] == 3) {
				$item['status_switch'] = true;
			} else {
				$item['status_switch'] = false;
			}
		}
		$return_data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['status_arr'] = $cnf_online_switch;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _ptype_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['status'] = intval($params['status']);
		$params['sort'] = intval($params['sort']);
		if (!$params['name']) {
			ReturnToJson(-1, '请填写通道名称');
		}
		$cnf_online_switch = getConfig('cnf_online_switch');
		if (!array_key_exists($params['status'], $cnf_online_switch)) {
			ReturnToJson(-1, '未知状态');
		}
		$db_data = [
			'name' => $params['name'],
			'type' => $params['type'],
			'max' => $params['max'],
			'min' => $params['min'],
			'sort' => $params['sort'] ? $params['sort'] : 100,
			'status' => $params['status'],
			'update_time' => NOW_TIME
		];
		try {
			$model = Db::table('fin_ptype');
			if ($item_id) {
				$item = $model->where("id={$item_id} and status<99")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
				$db_data['id'] = $item_id;
			} else {
				$db_data['create_time'] = NOW_TIME;
				$res = $model->insertGetId($db_data);
				$db_data['id'] = $res;
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$return_data = [
			'status_flag' => $cnf_online_switch[$db_data['status']]
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _ptype_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('fin_ptype');
		$item = $model->where("id={$item_id} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		$db_data = ['status' => 99];
		try {
			$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '操作成功');
	}


	//代付通道
	public function _dtype()
	{
		$pageuser = checkPower();
		$this->pageSize = 50;
		$params = $this->params;
		$where = "log.status<99";
		$where .= empty ($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		$where .= empty ($params['s_keyword']) ? '' : " and (log.name like '%{$params['s_keyword']}%')";
		$count_item = Db::table('fin_dtype log')
			->fieldRaw('count(1) as cnt')->where($where)->find();
		$list = Db::view(['fin_dtype' => 'log'], ['*'])
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_online_switch = getConfig('cnf_online_switch');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['update_time'] = date('m-d H:i:s', $item['update_time']);
			$item['status_flag'] = $cnf_online_switch[$item['status']];
			if ($item['status'] == 3) {
				$item['status_switch'] = true;
			} else {
				$item['status_switch'] = false;
			}
		}
		$return_data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['status_arr'] = $cnf_online_switch;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _dtype_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['status'] = intval($params['status']);
		$params['sort'] = intval($params['sort']);
		if (!$params['name']) {
			ReturnToJson(-1, '请填写通道名称');
		}
		$cnf_online_switch = getConfig('cnf_online_switch');
		if (!array_key_exists($params['status'], $cnf_online_switch)) {
			ReturnToJson(-1, '未知状态');
		}
		$db_data = [
			'name' => $params['name'],
			'type' => $params['type'],
			'sort' => $params['sort'] ? $params['sort'] : 100,
			'status' => $params['status'],
			'update_time' => NOW_TIME
		];
		try {
			$model = Db::table('fin_dtype');
			if ($item_id) {
				$item = $model->where("id={$item_id} and status<99")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
				$db_data['id'] = $item_id;
			} else {
				$db_data['create_time'] = NOW_TIME;
				$res = $model->insertGetId($db_data);
				$db_data['id'] = $res;
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$return_data = [
			'status_flag' => $cnf_online_switch[$db_data['status']]
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _dtype_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('fin_dtype');
		$item = $model->where("id={$item_id} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		$db_data = ['status' => 99];
		try {
			$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '操作成功');
	}
}