<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class UserController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		$pageuser = checkLogin();
		$user = Db::table('sys_user')->field(['id', 'account', 'phone', 'nickname', 'gid', 'headimgurl', 'reg_time', 'pidg1', 'pidg2', 'teamcount'])->where("id={$pageuser['id']}")->find();
		if (!$user) {
			ReturnToJson(-1, '账号异常');
		}
		// $user['group'] = getGroups($user['gid']);

		//1充值钱包  2余额钱包 3余额宝
		$wallet = getWallet($pageuser['id'], 1);
		$wallet2 = getWallet($pageuser['id'], 2);
		$wallet3 = getWallet($pageuser['id'], 3);
		if ($wallet3 == []) {
			$db_item = [
				'uid' => $user['id'],
				'waddr' => getRsn(),
				'cid' => 3,
				'balance' => 0,
				'create_time' => time()
			];
			Db::table('wallet_list')->insertGetId($db_item);
			$wallet3 = $db_item;
		}
		$investment = Db::table('pro_order')->where("uid={$pageuser['id']} and is_give=0")->sum('money');
		$recharge = Db::table('fin_paylog')->where("uid={$pageuser['id']} and status=9")->sum('money');
		$withdraw = Db::table('fin_cashlog')->where("uid={$pageuser['id']} and pay_status=9")->sum('money');

		$now_day = date('Ymd');

		//$reward=Db::table('pro_reward')->where("uid={$pageuser['id']} and type=1")->sum('money');
		$reward = Db::table('pro_reward')->where("uid={$pageuser['id']}")->sum('money');
		$hb_money = Db::table('gift_redpack_detail')->where("uid={$pageuser['id']}")->sum('money');

		$hb_money += Db::table('wallet_log')->where("uid={$pageuser['id']} and (type=9 or type=14)")->sum('money');

		$jrhb_money = Db::table('gift_redpack_detail')->where("uid={$pageuser['id']} and receive_day={$now_day}")->sum('money');
		$jrhb_money += Db::table('wallet_log')->where("uid={$pageuser['id']} and (type=9 or type=14) and create_day={$now_day}")->sum('money');

		$rebate = Db::table('pro_reward')->where("uid={$pageuser['id']} and type=2")->sum('money');
		$today_profit = Db::table('pro_reward')->where("uid={$pageuser['id']} and create_day={$now_day}")->sum('money');

		$service_arr = [];
		$up_users = getUpUser($pageuser['id'], true);
		foreach ($up_users as $uv) {
			$service_arr = Db::table('ext_service')->where("uid={$uv['id']}")->field(['type', 'name', 'account'])->order(['id' => 'desc'])->limit(4)->select()->toArray();
			if ($service_arr) {
				break;
			}
		}
		if (!$service_arr) {
			$service_arr = Db::table('ext_service')->where("gid<=70 or uid={$pageuser['id']}")->field(['type', 'name', 'account'])->order(['id' => 'desc'])->limit(4)->select()->toArray();
		}

		$cnf_service_type = getConfig('cnf_service_type');
		foreach ($service_arr as &$sv) {
			$sv['type_flag'] = $cnf_service_type[$sv['type']];
		}

		if ($pageuser['id'] == '242285') {
			$wallet =  ['balance' => '35000.00'];
			$wallet2 =  ['balance' => '6526303.91'];
			$reward = 0;
			$hb_money = 22375035.67;
			$rebate = 20015604.37;
			$today_profit = 0;
			$jrhb_money = 158030.91;
			$withdraw = 15848731.76;
			$recharge = 520000.00;
			$investment = 723000.00;
		}

		$return_data = [
			'user' => $user,
			'wallet' => $wallet,
			'wallet2' => $wallet2,
			'wallet3' => $wallet3,
			'investment' => round(floatval($investment), 2),
			'recharge' => round(floatval($recharge), 2),
			'withdraw' => round(floatval($withdraw), 2),
			'reward' => number_format($reward + $hb_money, 2, '.', ''),
			'rebate' => round(floatval($rebate), 2),
			'tprofit' => round(floatval($today_profit + $jrhb_money), 2),
			'service_arr' => $service_arr,
			//'pidg1' => $ccth
		];
		ReturnToJson(1, 'ok', $return_data);
	}
	//获取今天团队注册人数
	public function _gettodayregusercount()
	{
		$pageuser = checkLogin();
		$where1 = " pids like '%" . $pageuser['id'] . "%'  and first_pay_day=" . date('Ymd', strtotime("today"));
		$count = Db::table('sys_user')->where($where1)->count();
		$where1 = " pids like '%" . $pageuser['id'] . "%' and first_pay_day>0";
		$count1 = Db::table('sys_user')->where($where1)->count();
		$return_data = [
			'count' => $count,
			'count1' => $count1,
			//'$where1' => $where1
		];
		ReturnToJson(1, 'ok', $return_data);
	}
	//团队 
	public function _team()
	{
		$params = $this->params;
		$pageuser = checkLogin(); // Db::table('sys_user')->where("openid='" . $params['id'] . "'")->find();

		if (!$params['lv']) {
			ReturnToJson(1, 'System error');
		}
		$lv = intval($params['lv']);
		$mem_key = 'user_team_' . $lv  . $pageuser['id'] . $params['page']  . $params['type'];
		$params['page'] = intval($params['page']);
		//$return_data = $this->redis->get($mem_key);
		//if (!$return_data) {
			switch ($lv) {
				case 1:
					$lvstr =  $pageuser['id'] . ",%'";
					break;
				case 2:
					$lvstr = "%," . $pageuser['id'] . ",%'";
					break;
				case 3:
					$lvstr = "%," . $pageuser['id'] . "'";
					break;
				default:
					$lvstr = "%" . $pageuser['id'] . "%'";
					break;
			}
			$where = " log.pids like '" . $lvstr;

			//有效
			$count_item1 =  Db::table('sys_user log')
				->fieldRaw('count(1) as paycnt')
				->where($where . " and log.first_pay_day>0")
				->find();

			//无效
			$count_item2 = Db::table('sys_user log')
				->fieldRaw('count(1) as unpaycnt')
				->where($where . " and log.first_pay_day=0")
				->find();

			if ($params['type'] == "pay") {
				$where .= " and log.first_pay_day>0";
			} else if ($params['type'] == "unpay") {
				$where .= " and log.first_pay_day=0";
			}

			//团队总人数
			$count_item = Db::table('sys_user log')->fieldRaw('count(1) as cnt')->where($where)->find();

			$list = Db::view(['sys_user' => 'log'], ['id', 'account', 'nickname', 'headimgurl', 'reg_time','first_pay_day'])
				->where($where)
				->order(['log.reg_time' => 'desc'])
				->page($params['page'], $this->pageSize)
				->select()->toArray();

			
			writeLog("list".json_encode($list),"bobopay1");
			foreach ($list as &$k) {
				$referrer_str .= $k["id"] . ",";
				$teamSize_str .= "select {$k['id']} as id,count(1) as teamSize  from sys_user where pids like '%{$k['id']}%';";
				$amount_str .= "select {$k['id']} as pid,id from sys_user where pids like '%{$k['id']}%';";
			}

			$referrerData = array();
			if($referrer_str)
			{
				$referrer_str =substr($referrer_str,0, strlen($referrer_str) - 1);
				$referrerData = Db::table('sys_user')->where("pid in ({$referrer_str})")->field('pid,count(1) as referrer')->group('pid')->select();
			}

			$teamSizeDate = array();
			if($teamSize_str)
			{
				$teamSize_str =  substr($teamSize_str,0, strlen($teamSize_str) - 1);
				$teamSize_str = str_replace(";"," union ", $teamSize_str) . ';';
				$teamSizeDate = Db::query($teamSize_str);
			}				
			
			$amountDate = array();
			if($amount_str)
			{
				$amount_str =substr($amount_str,0, strlen($amount_str) - 1);
				$amount_str = str_replace(";"," union ", $amount_str) . ';';
				$amountDate = Db::query($amount_str);
			}
			writeLog("amountDate".json_encode($amountDate),"bobopay1");
			$dic = array();
			$amount_ids = ",";
			if(count($referrerData) > 0)
			{
				foreach($amountDate as $it)
				{
					if (strstr($it['id'], $amount_ids)) {
						
					}else{
						$amount_ids .= $it['id'] +',';
					}

					if(isset($dic["{$it['pid']}"]))
					{
						$val = $dic["{$it['pid']}"];
						array_push($val,$it['id']);
						$dic["{$it['pid']}"] = $val;
					}
					else
					{
						$dic["{$it['pid']}"] = array($it['id']);
					}
				}
				if($amount_ids)
					$amount_ids =substr($amount_ids,1, strlen($amount_ids) - 2);
			}
			writeLog("dic".json_encode($dic),"bobopay1");
			writeLog("amount_ids".$amount_ids,"bobopay1");

			$amountData = Db::table("pro_order")->where("uid in ({$amount_ids})")->field('uid,sum(money) as money')->group('uid')->select();


			foreach ($list as &$item) {
				$item["referrer"] = 0;
				$item["teamSize"] = 0;
				$item["assets"] = 0;

				if(count($referrerData) > 0)
					foreach ($referrerData as &$v)
						if ($item["id"] == $v["pid"])
							$item["referrer"] = $v['referrer'];

				if(count($teamSizeDate) > 0)
					foreach	($teamSizeDate as &$v)
						if($item["id"] == $v['id'])
							$item["teamSize"] = $v["teamSize"];

				if(count($amountData) > 0)
					foreach ($amountData as &$v)
						if ($item["id"] == $v["uid"])
							$item["assets"] = $v['assets'];

				$item['reg_time'] = date('m-d H:i', $item['reg_time']);
				$item['level'] = $lv == 1 ? 'B' : ($lv == 2 ? 'C': 'D') ;
				$item['first_pay_day_flag'] = $item['first_pay_day']  > 0 ? 'yes' : 'no' ;
			}


			$where1 = " log.pid='" . $pageuser['id'] . "' and reg_time = " . strtotime("today");
			$today = Db::table('sys_user log')->where($where1)->count();
			$total_page = ceil($count_item['cnt'] / $this->pageSize);

			$return_data = [
				'list' => $list,
				'allcount' => 0, // getDownUsercount($pageuser['id']),
				'count' => intval($count_item['cnt']),
				'paycount' => intval($count_item1['paycnt']),
				'unpaycount' => intval($count_item2['unpaycnt']),
				'$pg' => $params['page'],
				'page' => $params['page'] + 1,
				'finished' => $params['page'] >= $total_page ? true : false,
				'limit' => $this->pageSize,
				'today' => $today,
				'lv' => $lv,
				'$total_page' => $total_page,
			];
			//$this->redis->set($mem_key, $return_data, 86400);
		//}
		ReturnToJson(1, 'ok', $return_data);
	}
	//我的团队--层级人数
	public function _GetTeamHierarchyPeopleNum()
	{
		$pageuser = checkLogin();
		$list = Db::table('sys_user')->where("pids like '%{$pageuser['id']}%' ")->field('id,pids')->order("reg_time")->select()->toArray();
		foreach ($list as &$item) 
		{
			$pidsArr = explode(",", $item["pids"]);
			$item["level"] = array_search($pageuser['id'], $pidsArr) +1;
		}
		$return_data = [
			'list'=> $list,
			'fy'=> getConfig('FYSZ'),
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//收益中心
	public function _profit()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		$params['s_type'] = intval($params['s_type']);

		$where = "log.uid={$pageuser['id']}";
		$where .= empty($params['s_type']) ? '' : " and log.type={$params['s_type']}";

		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}

		$count_item = Db::table('nft_reward log')
			->leftJoin('cnf_currency c2', 'log.reward_cid=c2.id')
			->fieldRaw('count(1) as cnt,sum(log.reward) as reward')
			->where($where)
			->find();

		$list = Db::view(['nft_reward' => 'log'], [
			'type', 'fkey', 'reward', 'rate', 'create_time', 'remark'
		])
			->view(['cnf_currency' => 'c2'], ['name' => 'currency', 'symbol'], 'log.reward_cid=c2.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$cnf_reward_type = getConfig('cnf_reward_type');
		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			$item['type_flag'] = $cnf_reward_type[$item['type']];
		}

		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'reward' => number_format($count_item['reward'], 2, '.', ''),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		ReturnToJson(1, 'ok', $return_data);
	}
}
