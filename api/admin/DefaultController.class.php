<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class DefaultController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//清理用户的所有缓存
	public function _clearCache()
	{
		$pageuser = checkLogin();
		if ($pageuser['gid'] == 1) {
			$this->redis->clear();
		} else {
			$tag = 'usernodes_' . $pageuser['id'];
			$this->redis->clear($tag);
		}
		ReturnToJson(1, '缓存清理成功');
	}

	//获取后台首页数据
	public function _getData()
	{
		$pageuser = checkLogin();
		$uid_arr = [];
		$uWhere = '';
		$uWhere2 = '';
		$uWhere3 = '';
		$pidg = '1=1';
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$uWhere = " and uid in ({$uid_str})";
			$uWhere2 = " and id in ({$uid_str})";
			$uWhere22 = " and uid in ({$uid_str})";
			$uWhere3 = " and log.uid in ({$uid_str})";

			if ($pageuser['gid'] == 71) {
				$pidg = " su.pidg1 = {$pageuser['id']} ";
			} else if ($pageuser['gid'] == 81) {
				$pidg = " su.pidg2 = {$pageuser['id']} ";
			}
		}

		$params = $this->params;
		$params['type'] = intval($params['type']);
		$start_time = 0;
		$end_time = 0;
		$today_start = strtotime(date('Y-m-d 00:00:01'));
		$today_end = strtotime(date('Y-m-d 23:59:59'));
		if ($params['start_time'] && $params['end_time'] && $params['start_time'] != 'Invalid Date') {
			$start_time = strtotime($params['start_time'] . ' 00:00:01');
			$end_time = strtotime($params['end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始日期不能超过结束日期');
			}
		}
		if ($params['type'] == 1) {
			$start_time = strtotime(date('Y-m-d 00:00:01'));
			$end_time = strtotime(date('Y-m-d 23:59:59'));
		} elseif ($params['type'] == 2) {
			$start_time = strtotime(date("Y-m-d", strtotime("-1 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d", strtotime("-1 day")) . ' 23:59:59');
		} elseif ($params['type'] == 3) {
			$start_time = strtotime(date("Y-m-d", strtotime("-7 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d") . ' 23:59:59');
		} elseif ($params['type'] == 4) {
			$start_time = strtotime(date("Y-m-d", strtotime("-30 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d") . ' 23:59:59');
		} elseif ($params['type'] == 5) {
			$start_time = strtotime(date('Y-m-d', strtotime('-1 month')) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d", strtotime("-1 month")) . ' 23:59:59');
		} elseif ($params['type'] == 6) {
			$start_time = strtotime(date('Y-m-01') . ' 00:00:01');
			$end_time = strtotime(date('Y-m-31') . ' 23:59:59');
		} else {
		}

		if ($params['type']) {
			$today_start = $start_time;
			$today_end = $end_time;
		} else {
			if ($start_time > 0 && $end_time > 0) {
				$today_start = $start_time;
				$today_end = $end_time;
			} else {
				$today_start = strtotime(date('Y-m-d 00:00:01'));
				$today_end = strtotime(date('Y-m-d 23:59:59'));
			}
		}

		$now_day = date('Ymd');
		$where = '1=1';
		$where2 = '1=1';
		$whereCash = '1=1';
		$whereRedpack = '1=1';
		if ($start_time > 0 && $end_time > 0) {
			$where .= " and create_time between {$start_time} and {$end_time}";
			$where2 .= " and log.create_time between {$start_time} and {$end_time}";
			$whereCash .= " and pay_time between {$start_time} and {$end_time}";
			$whereRedpack .= " and receive_time between {$start_time} and {$end_time}";
		}
		$re_balance = Db::table('wallet_list')->where($where . "{$uWhere} and cid=1")->sum('balance');
		$ba_balance = Db::table('wallet_list')->where($where . "{$uWhere} and cid=2")->sum('balance');

		$balance_item = Db::table('wallet_list log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('sum(log.balance) as balance')
			->where($where2 . $uWhere3 . " and u.gid=92 and u.first_pay_day>0")
			->find();

		//$invest=Db::table('pro_order')->where($where.$uWhere)->fieldRaw("count(1) as cnt,sum(money) as money")->find();

		$invest_item = Db::table('pro_order log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where2 . $uWhere3 . " and log.is_give=0 and u.gid=92 and u.first_pay_day>0")
			->find();

		$invest_today = Db::table('pro_order')->where($where . $uWhere . " and create_time between {$today_start} and {$today_end} and is_give=0")->fieldRaw("count(1) as cnt,sum(num) as num,sum(money) as money")->find();
		$reward = Db::table('pro_reward')->where($where . "{$uWhere} and type=1")->fieldRaw("count(1) as cnt,sum(money) as money")->find();

		//$where.
		//总充值
		$total_pay_money = Db::table('fin_paylog')->leftJoin('sys_user su', 'fin_paylog.uid=su.id')
			->where("{$pidg} and fin_paylog.status=9")->sum('fin_paylog.money');

		//今天充值
		$today_pay_money = Db::table('fin_paylog')->leftJoin('sys_user su', 'fin_paylog.uid=su.id')
			->where("{$pidg} and fin_paylog.status=9 and (pay_time between {$today_start} and {$today_end})")->sum('fin_paylog.money');
		//$sql=Db::getLastSql();

		$first_start = date('Ymd', $today_start);
		$first_end = date('Ymd', $today_end);
		//$today_first_pay = Db::table('sys_user')->where("first_pay_day between {$first_start} and {$first_end} {$uWhere2}")->count('id');

		//今天首次充值
		$today_first_pay = Db::table('fin_paylog log')
			->leftJoin('sys_user su', 'log.uid=su.id')
			->where("{$pidg} and is_first=1 and log.status=9 and (pay_time between {$today_start} and {$today_end})")
			->count('log.id');

		//总提现
		$total_cash_money = Db::table('fin_cashlog')->where($whereCash . "{$uWhere} and status=9 and pay_status=9")->sum('money');
		//$total_cash_money=Db::table('fin_cashlog')->where($whereCash."{$uWhere} and status=9 and pay_status=9 and (pay_time between {$today_start} and {$today_end})")->sum('money');
		//今天提现
		$today_cash_money = Db::table('fin_cashlog')->where($whereCash . "{$uWhere} and status=9 and pay_status=9 and (pay_time between {$today_start} and {$today_end})")->sum('money');
		//未审核提现
		$uncheck_cash_money = Db::table('fin_cashlog')->where($where . "{$uWhere} and (status=1 or (status=9 and pay_status<9))")->sum('money'); //and (create_time between {$today_start} and {$today_end})

		//抽奖
		$total_lottery_money = Db::table('gift_lottery_log')->where($where . $uWhere)->sum('money');
		//今天抽奖
		$today_lottery_money = Db::table('gift_lottery_log')->where($where . "{$uWhere} and create_day={$now_day}")->sum('money');

		//红包
		$total_redpack_money = Db::table('gift_redpack_detail')->where($whereRedpack . "{$uWhere} and uid>0")->sum('money');
		//今天红包
		$today_redpack_money = Db::table('gift_redpack_detail')->where($whereRedpack . "{$uWhere} and receive_time between {$today_start} and {$today_end} and uid>0")->sum('money');

		$user_where = '1=1';
		$user_where_pay = '1=1';
		$user_where_pay1 = '1=1';
		if ($start_time > 0 && $end_time > 0) {
			$user_where .= " and reg_time between {$start_time} and {$end_time}";
			$start_pay_day = date('Ymd', $start_time);
			$end_pay_day = date('Ymd', $end_time);
			$user_where_pay .= " and first_pay_day between {$start_pay_day} and {$end_pay_day}";

			$start_pay_day = date('Ymd', $start_time);
			$end_pay_day = date('Ymd', $end_time);

			$user_where_pay1 .= " and pay_time between {$start_time} and {$end_time}";
		}
		$user_where .= " and gid=92";

		$user_where_pay .= " and gid=92";
		//总会员
		$total_member = Db::table('sys_user')->where($user_where . $uWhere2)->count('id');
		//今天会员
		$today_member = Db::table('sys_user')->where("reg_time between {$today_start} and {$today_end} and gid=92 {$uWhere2}")->count('id');
		//有效会员
		$effective_member = Db::table('fin_paylog')->where("{$user_where_pay1} and is_first=1 {$uWhere22}")->count('id');
		//验证码
		$cnf_global_smscode = getConfig('cnf_global_smscode');

		$return_data = [
			're_balance' => number_format($re_balance, 2, '.', ''),
			'ba_balance' => number_format($ba_balance, 2, '.', ''),
			//'total_balance'=>number_format($re_balance+$ba_balance,2,'.',''),
			'total_balance' => number_format($balance_item['balance'], 2, '.', ''),
			'invest_count_today' => intval($invest_today['cnt']),
			//'invest_count'=>intval($invest['cnt']),
			'invest_count' => intval($invest_item['cnt']),
			//'invest_money'=>floatval($invest['money']),
			'invest_money' => floatval($invest_item['money']),
			'reward_money' => floatval($reward['money']),
			'total_pay_money' => floatval($total_pay_money),
			'today_pay_money' => floatval($today_pay_money),
			'today_first_pay' => intval($today_first_pay),
			'total_cash_money' => floatval($total_cash_money),
			'today_cash_money' => floatval($today_cash_money),
			'uncheck_cash_money' => floatval($uncheck_cash_money),
			'total_lottery_money' => floatval($total_lottery_money),
			'today_lottery_money' => floatval($today_lottery_money),
			'total_redpack_money' => floatval($total_redpack_money),
			'today_redpack_money' => floatval($today_redpack_money),
			'total_member' => intval($total_member),
			'today_member' => intval($today_member),
			'effective_member' => intval($effective_member),
			'sms_code' => $cnf_global_smscode['code'],
			'total_jy' => number_format(floatval($total_pay_money) - floatval($total_cash_money), 2, '.', ''),
			// 总结余
			'sql' => $uWhere2
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//获取后台首页数据
	public function _getData1()
	{
		$pageuser = checkLogin();
		$uid_arr = [];
		$uWhere = '';
		$uWhere2 = '';
		$uWhere3 = '';
		$pidg = '1=1';
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$uWhere = " and uid in ({$uid_str})";
			$uWhere2 = " and id in ({$uid_str})";
			$uWhere22 = " and uid in ({$uid_str})";
			$uWhere3 = " and log.uid in ({$uid_str})";

			if ($pageuser['gid'] == 71) {
				$pidg = " su.pidg1 = {$pageuser['id']} ";
			} else if ($pageuser['gid'] == 81) {
				$pidg = " su.pidg2 = {$pageuser['id']} ";
			}
		}

		$params = $this->params;
		$params['type'] = intval($params['type']);
		$start_time = 0;
		$end_time = 0;
		$today_start = strtotime(date('Y-m-d 00:00:01'));
		$today_end = strtotime(date('Y-m-d 23:59:59'));
		if ($params['start_time'] && $params['end_time'] && $params['start_time'] != 'Invalid Date') {
			$start_time = strtotime($params['start_time'] . ' 00:00:01');
			$end_time = strtotime($params['end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始日期不能超过结束日期');
			}
		}
		if ($params['type'] == 1) {
			$start_time = strtotime(date('Y-m-d 00:00:01'));
			$end_time = strtotime(date('Y-m-d 23:59:59'));
		} elseif ($params['type'] == 2) {
			$start_time = strtotime(date("Y-m-d", strtotime("-1 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d", strtotime("-1 day")) . ' 23:59:59');
		} elseif ($params['type'] == 3) {
			$start_time = strtotime(date("Y-m-d", strtotime("-7 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d") . ' 23:59:59');
		} elseif ($params['type'] == 4) {
			$start_time = strtotime(date("Y-m-d", strtotime("-30 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d") . ' 23:59:59');
		} elseif ($params['type'] == 5) {
			$start_time = strtotime(date('Y-m-d', strtotime('-1 month')) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d", strtotime("-1 month")) . ' 23:59:59');
		} elseif ($params['type'] == 6) {
			$start_time = strtotime(date('Y-m-01') . ' 00:00:01');
			$end_time = strtotime(date('Y-m-31') . ' 23:59:59');
		} else {
		}

		if ($params['type']) {
			$today_start = $start_time;
			$today_end = $end_time;
		} else {
			if ($start_time > 0 && $end_time > 0) {
				$today_start = $start_time;
				$today_end = $end_time;
			} else {
				$today_start = strtotime(date('Y-m-d 00:00:01'));
				$today_end = strtotime(date('Y-m-d 23:59:59'));
			}
		}
		$now_day = date('Ymd');
		$where = '1=1';
		$where2 = '1=1';
		$whereCash = '1=1';
		$whereRedpack = '1=1';
		if ($start_time > 0 && $end_time > 0) {
			$where .= " and create_time between {$start_time} and {$end_time}";
			$where2 .= " and log.create_time between {$start_time} and {$end_time}";
			$whereCash .= " and pay_time between {$start_time} and {$end_time}";
			$whereRedpack .= " and receive_time between {$start_time} and {$end_time}";
		}
		// $re_balance = Db::table('wallet_list')->where($where . "{$uWhere} and cid=1")->sum('balance');
		// $ba_balance = Db::table('wallet_list')->where($where . "{$uWhere} and cid=2")->sum('balance');
		$balance_item = Db::table('wallet_list log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('sum(log.balance) as balance')
			->where($where2 . $uWhere3 . " and u.gid=92 and u.first_pay_day>0")
			->find();
		//$invest=Db::table('pro_order')->where($where.$uWhere)->fieldRaw("count(1) as cnt,sum(money) as money")->find();
		$invest_item = Db::table('pro_order log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where2 . $uWhere3 . " and log.is_give=0 and u.gid=92 and u.first_pay_day>0")
			->find();
		$invest_today = Db::table('pro_order')->where($where . $uWhere . " and create_time between {$today_start} and {$today_end} and is_give=0")->fieldRaw("count(1) as cnt,sum(num) as num,sum(money) as money")->find();
		
		//今日订单分红
		$reward_money_today =  Db::table('pro_reward')->where("create_time between {$today_start} and {$today_end} and type=1")->sum('money');

		$reward = Db::table('pro_reward')->where($where . "{$uWhere} and type=1")->fieldRaw("count(1) as cnt,sum(money) as money")->find();
		$return_data = [
			'total_balance' => number_format($balance_item['balance'], 2, '.', ''),
			'invest_count_today' => intval($invest_today['cnt']),
			'invest_count' => intval($invest_item['cnt']),
			'invest_money' => floatval($invest_item['money']),
			'reward_money' => floatval($reward['money']),
			'reward_money_today' => floatval($reward_money_today),
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//获取后台首页数据
	public function _getData2()
	{
		$pageuser = checkLogin();
		$uid_arr = [];
		$uWhere = '';
		$uWhere2 = '';
		$uWhere3 = '';
		$pidg = '1=1';
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$uWhere = " and uid in ({$uid_str})";
			$uWhere2 = " and id in ({$uid_str})";
			$uWhere22 = " and uid in ({$uid_str})";
			$uWhere3 = " and log.uid in ({$uid_str})";

			if ($pageuser['gid'] == 71) {
				$pidg = " su.pidg1 = {$pageuser['id']} ";
			} else if ($pageuser['gid'] == 81) {
				$pidg = " su.pidg2 = {$pageuser['id']} ";
			}
		}

		$params = $this->params;
		$params['type'] = intval($params['type']);
		$start_time = 0;
		$end_time = 0;
		$today_start = strtotime(date('Y-m-d 00:00:01'));
		$today_end = strtotime(date('Y-m-d 23:59:59'));
		if ($params['start_time'] && $params['end_time'] && $params['start_time'] != 'Invalid Date') {
			$start_time = strtotime($params['start_time'] . ' 00:00:01');
			$end_time = strtotime($params['end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始日期不能超过结束日期');
			}
		}
		if ($params['type'] == 1) {
			$start_time = strtotime(date('Y-m-d 00:00:01'));
			$end_time = strtotime(date('Y-m-d 23:59:59'));
		} elseif ($params['type'] == 2) {
			$start_time = strtotime(date("Y-m-d", strtotime("-1 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d", strtotime("-1 day")) . ' 23:59:59');
		} elseif ($params['type'] == 3) {
			$start_time = strtotime(date("Y-m-d", strtotime("-7 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d") . ' 23:59:59');
		} elseif ($params['type'] == 4) {
			$start_time = strtotime(date("Y-m-d", strtotime("-30 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d") . ' 23:59:59');
		} elseif ($params['type'] == 5) {
			$start_time = strtotime(date('Y-m-d', strtotime('-1 month')) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d", strtotime("-1 month")) . ' 23:59:59');
		} elseif ($params['type'] == 6) {
			$start_time = strtotime(date('Y-m-01') . ' 00:00:01');
			$end_time = strtotime(date('Y-m-31') . ' 23:59:59');
		} else {
		}

		if ($params['type']) {
			$today_start = $start_time;
			$today_end = $end_time;
		} else {
			if ($start_time > 0 && $end_time > 0) {
				$today_start = $start_time;
				$today_end = $end_time;
			} else {
				$today_start = strtotime(date('Y-m-d 00:00:01'));
				$today_end = strtotime(date('Y-m-d 23:59:59'));
			}
		}

		$now_day = date('Ymd');
		$where = '1=1';
		$where2 = '1=1';
		$whereCash = '1=1';
		$whereRedpack = '1=1';
		if ($start_time > 0 && $end_time > 0) {
			$where .= " and create_time between {$start_time} and {$end_time}";
			$where2 .= " and log.create_time between {$start_time} and {$end_time}";
			$whereCash .= " and pay_time between {$start_time} and {$end_time}";
			$whereRedpack .= " and receive_time between {$start_time} and {$end_time}";
		}


		//总充值
		$total_pay_money = Db::table('fin_paylog')->leftJoin('sys_user su', 'fin_paylog.uid=su.id')
			->where("{$pidg} and fin_paylog.status=9")->sum('fin_paylog.money');

		//今天充值
		$today_pay_money = Db::table('fin_paylog')->leftJoin('sys_user su', 'fin_paylog.uid=su.id')
			->where("{$pidg} and fin_paylog.status=9 and (pay_time between {$today_start} and {$today_end})")->sum('fin_paylog.money');
		//$sql=Db::getLastSql();

		$first_start = date('Ymd', $today_start);
		$first_end = date('Ymd', $today_end);
		//$today_first_pay = Db::table('sys_user')->where("first_pay_day between {$first_start} and {$first_end} {$uWhere2}")->count('id');

		//今天首次充值
		$today_first_pay = Db::table('fin_paylog log')
			->leftJoin('sys_user su', 'log.uid=su.id')
			->where("{$pidg} and is_first=1 and log.status=9 and (pay_time between {$today_start} and {$today_end})")
			->count('log.id');
		//总提现
		$total_cash_money = Db::table('fin_cashlog')->where($whereCash . "{$uWhere} and status=9 and pay_status=9")->sum('money');
		//$total_cash_money=Db::table('fin_cashlog')->where($whereCash."{$uWhere} and status=9 and pay_status=9 and (pay_time between {$today_start} and {$today_end})")->sum('money');
		//今天提现
		$today_cash_money = Db::table('fin_cashlog')->where($whereCash . "{$uWhere} and status=9 and pay_status=9 and (pay_time between {$today_start} and {$today_end})")->sum('money');
		//未审核提现
		$uncheck_cash_money = Db::table('fin_cashlog')->where($where . "{$uWhere} and (status=1 or (status=9 and pay_status<9))")->sum('money'); //and (create_time between {$today_start} and {$today_end})








		$return_data = [
			'total_pay_money' => floatval($total_pay_money),
			'today_pay_money' => floatval($today_pay_money),
			'today_first_pay' => intval($today_first_pay),
			'total_cash_money' => floatval($total_cash_money),
			'today_cash_money' => floatval($today_cash_money),
			'total_jy' => number_format(floatval($total_pay_money) - floatval($total_cash_money), 2, '.', ''),
			'uncheck_cash_money' => floatval($uncheck_cash_money),



		];
		ReturnToJson(1, 'ok', $return_data);
	}


	//获取后台首页数据
	public function _getData3()
	{
		$pageuser = checkLogin();
		$uid_arr = [];
		$uWhere = '';
		$uWhere2 = '';
		$uWhere3 = '';
		$pidg = '1=1';
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$uWhere = " and uid in ({$uid_str})";
			$uWhere2 = " and id in ({$uid_str})";
			$uWhere22 = " and uid in ({$uid_str})";
			$uWhere3 = " and log.uid in ({$uid_str})";

			if ($pageuser['gid'] == 71) {
				$pidg = " su.pidg1 = {$pageuser['id']} ";
			} else if ($pageuser['gid'] == 81) {
				$pidg = " su.pidg2 = {$pageuser['id']} ";
			}
		}

		$params = $this->params;
		$params['type'] = intval($params['type']);
		$start_time = 0;
		$end_time = 0;
		$today_start = strtotime(date('Y-m-d 00:00:01'));
		$today_end = strtotime(date('Y-m-d 23:59:59'));
		if ($params['start_time'] && $params['end_time'] && $params['start_time'] != 'Invalid Date') {
			$start_time = strtotime($params['start_time'] . ' 00:00:01');
			$end_time = strtotime($params['end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始日期不能超过结束日期');
			}
		}
		if ($params['type'] == 1) {
			$start_time = strtotime(date('Y-m-d 00:00:01'));
			$end_time = strtotime(date('Y-m-d 23:59:59'));
		} elseif ($params['type'] == 2) {
			$start_time = strtotime(date("Y-m-d", strtotime("-1 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d", strtotime("-1 day")) . ' 23:59:59');
		} elseif ($params['type'] == 3) {
			$start_time = strtotime(date("Y-m-d", strtotime("-7 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d") . ' 23:59:59');
		} elseif ($params['type'] == 4) {
			$start_time = strtotime(date("Y-m-d", strtotime("-30 day")) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d") . ' 23:59:59');
		} elseif ($params['type'] == 5) {
			$start_time = strtotime(date('Y-m-d', strtotime('-1 month')) . ' 00:00:01');
			$end_time = strtotime(date("Y-m-d", strtotime("-1 month")) . ' 23:59:59');
		} elseif ($params['type'] == 6) {
			$start_time = strtotime(date('Y-m-01') . ' 00:00:01');
			$end_time = strtotime(date('Y-m-31') . ' 23:59:59');
		} else {
		}

		if ($params['type']) {
			$today_start = $start_time;
			$today_end = $end_time;
		} else {
			if ($start_time > 0 && $end_time > 0) {
				$today_start = $start_time;
				$today_end = $end_time;
			} else {
				$today_start = strtotime(date('Y-m-d 00:00:01'));
				$today_end = strtotime(date('Y-m-d 23:59:59'));
			}
		}

		$now_day = date('Ymd');
		$where = '1=1';
		$where2 = '1=1';
		$whereCash = '1=1';
		$whereRedpack = '1=1';
		if ($start_time > 0 && $end_time > 0) {
			$where .= " and create_time between {$start_time} and {$end_time}";
			$where2 .= " and log.create_time between {$start_time} and {$end_time}";
			$whereCash .= " and pay_time between {$start_time} and {$end_time}";
			$whereRedpack .= " and receive_time between {$start_time} and {$end_time}";
		}

		// //抽奖
		// $total_lottery_money = Db::table('gift_lottery_log')->where($where . $uWhere)->sum('money');
		// //今天抽奖
		// $today_lottery_money = Db::table('gift_lottery_log')->where($where . "{$uWhere} and create_day={$now_day}")->sum('money');

		// //红包
		// $total_redpack_money = Db::table('gift_redpack_detail')->where($whereRedpack . "{$uWhere} and uid>0")->sum('money');

		//今天红包
		$today_redpack_money = Db::table('gift_redpack_detail')->where($whereRedpack . "{$uWhere} and receive_time between {$today_start} and {$today_end} and uid>0")->sum('money');


		$user_where = '1=1';
		$user_where_pay = '1=1';
		$user_where_pay1 = '1=1';
		if ($start_time > 0 && $end_time > 0) {
			$user_where .= " and reg_time between {$start_time} and {$end_time}";
			$start_pay_day = date('Ymd', $start_time);
			$end_pay_day = date('Ymd', $end_time);
			$user_where_pay .= " and first_pay_day between {$start_pay_day} and {$end_pay_day}";

			$start_pay_day = date('Ymd', $start_time);
			$end_pay_day = date('Ymd', $end_time);

			$user_where_pay1 .= " and pay_time between {$start_time} and {$end_time}";
		}
		$user_where .= " and gid=92";

		$user_where_pay .= " and gid=92";

		//总会员
		$total_member = Db::table('sys_user')->where($user_where . $uWhere2)->count('id');
		//今天会员
		$today_member = Db::table('sys_user')->where("reg_time between {$today_start} and {$today_end} and gid=92 {$uWhere2}")->count('id');
		//有效会员
		$effective_member = Db::table('fin_paylog')->where("{$user_where_pay1} and is_first=1 {$uWhere22}")->count('id');

		$return_data = [
			// 'total_lottery_money' => floatval($total_lottery_money),
			// 'today_lottery_money' => floatval($today_lottery_money),
			// 'total_redpack_money' => floatval($total_redpack_money),

			'today_redpack_money' => floatval($today_redpack_money),
			'total_member' => intval($total_member),
			'today_member' => intval($today_member),
			'effective_member' => intval($effective_member),
		];
		ReturnToJson(1, 'ok', $return_data);
	}

		//万能验证码
		public function _yzm()
		{
			//验证码
			$cnf_global_smscode =  mt_rand(100000, 999999); // minValue为最小值，maxValue为最大值
			$key = "WN_CODE" . $cnf_global_smscode;
			$this->redis->set($key,$cnf_global_smscode,600);
			$return_data = [
				'sms_code' => $cnf_global_smscode,
			];
			ReturnToJson(1, 'ok', $return_data);
		}
}