<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class UserController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//##################用户管理开始##################
	public function _UpdateUserfirst_pay_day()
	{
		$pageuser = checkPower('User_user_update_yx');
		$params = $this->params;
		if (!$params['id']) {
			ReturnToJson(-1, '请填写转出账号');
		}
		$now_day = date('Ymd', NOW_TIME);
		$user = Db::table('sys_user')->where(' id=' . $params['id'])->find();
		if (!$user)
			ReturnToJson(0, '用户不存在');
		$user['first_pay_day'] = ($user['first_pay_day'] == 0 ? $now_day : 0);
		Db::table('sys_user')->where(' id=' . $user['id'])->update(['first_pay_day' => $user['first_pay_day']]);
		ReturnToJson(1, '切换成功', $user);
	}
	public function _user()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$return_data = [];
		$sys_group = getGroupsIdx();
		if (intval($params['page']) < 2) {
			$sys_group_arr = [];
			foreach ($sys_group as $key => $value) {
				if ($pageuser['gid'] > 1 && $key <= $pageuser['gid']) {
					continue;
				}
				$sys_group_arr[$key] = $value;
			}

			$return_data = [
				'user' => $pageuser,
				'sys_group' => $sys_group_arr
			];
		}

		$params['s_gid'] = intval($params['s_gid']);
		$where = "log.status<99";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_str = implode(',', $uid_arr);
			if (!$uid_str) {
				$uid_str = '0';
			}
			$where .= " and log.id in({$uid_str})";
		}


		if ($params['s_loginip']) {
			$where .= " and log.login_ip ='{$params['s_loginip']}'";
		}

		if ($params['s_regip']) { //reg_ip
			$where .= " and log.reg_ip ='{$params['s_regip']}'";
		}



		if ($params['s_bankc']) {
			$s_bankc = $params['s_bankc'];
			$userarrs = Db::view(['cnf_banklog' => 'log'], ['uid'])
				->whereRaw("log.account='{$s_bankc}'")
				->select()
				->toArray();
			foreach ($userarrs as $dv) {
				$ids[] = $dv['uid'];
			}
			$ids[] = 0;
			$uids_str = implode(',', $ids);
			$where .= " and log.id in({$uids_str})";
		}

		if ($params['s_keyword2']) {
			$s_keyword2 = $params['s_keyword2'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword2}' or account='{$s_keyword2}'")->find();
			if ($s_puser) {
				$uid_arr = getDownUser_new($s_puser['id']);
			}
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.id in({$uid_str})";
		}

		if ($params['s_keyword3']) {
			$s_keyword2 = $params['s_keyword3'];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword2}' or account='{$s_keyword2}'")->find();
			$ids_arr = getUpUser($s_puser['id']);
			$ids_arr[] = 0;
			$ids_str = implode(',', $ids_arr);
			$where .= " and log.id in ({$ids_str})";
		}

		if ($params['s_keyword4']) {
			$s_keyword2 = $params['s_keyword4'];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword2}' or account='{$s_keyword2}'")->find();
			if (!$s_puser) {
				$s_puser = ['id' => -1];
			}
			$zt_arr = Db::table('sys_user')->where("pid={$s_puser['id']}")->field(['id'])->select()->toArray();
			$ids_arr = [0];
			foreach ($zt_arr as $zv) {
				$ids_arr[] = $zv['id'];
			}
			$ids_str = implode(',', $ids_arr);
			$where .= " and log.id in ({$ids_str})";
		}
		if ($params['moneyFrom'] > 0)
			$where .= " and log.total_invest2 >= {$params['moneyFrom']}";

		if ($params['moneyTo'] > 0)
			$where .= " and log.total_invest2 <= {$params['moneyTo']}";

		if ($params['regTimeRange']) {
			$start_time = strtotime($params['regTimeRange'][0]);
			$end_time = strtotime($params['regTimeRange'][1]);
			$where .= " and log.reg_time between {$start_time} and {$end_time}";
		}

		if ($params['status'] != 0)
			$where .= " and log.status={$params['status']}";

		$where .= empty ($params['s_gid']) ? '' : " and log.gid={$params['s_gid']}";
		$where .= empty ($params['s_keyword']) ? '' : " and (log.id='{$params['s_keyword']}'  
		or log.phone='{$params['s_keyword']}' 
		or log.account like '%{$params['s_keyword']}%' 
		or log.realname like '%{$params['s_keyword']}%' 
		or log.login_ip like '%{$params['s_keyword']}%' 
		or log.nickname like '%{$params['s_keyword']}%')";
		//$where.=empty($params['s_keyword2'])?'':" and pu.account='{$params['s_keyword2']}'";

		if (isset ($params['s_has_pay']) && $params['s_has_pay'] != 'all') {
			if ($params['s_has_pay']) {
				$where .= " and log.first_pay_day>0";
			} else {
				$where .= " and log.first_pay_day=0";
			}
		}

		$count_item = Db::table('sys_user log')
			//->leftJoin('sys_user pu', 'log.pid=pu.id')
			->fieldRaw('count(1) as cnt,sum(log.balance) as balance,sum(log.fz_balance) as fz_balance')
			->where($where)
			->find();
		$list = Db::view(['sys_user' => 'log'], ['*'])
			->view(['sys_user' => 'pu'], ['account' => 'p_account', 'nickname' => 'p_nickname', 'realname' => 'p_realname'], 'log.pid=pu.id', 'LEFT')
			->view(['cnf_banklog' => 'bk'], ['account' => 'bk_account'], 'log.id=bk.uid', 'LEFT')
			->where($where)
			->order(['log.reg_time' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$yes_or_no = getConfig('yes_or_no');
		$account_status = getConfig('account_status');
		foreach ($list as &$item) {
			//createWallet($item['id']);
			unset($item['password'], $item['password2']);
			$item['gname'] = $sys_group[$item['gid']];
			$item['status_flag'] = $account_status[$item['status']];
			$item['icode_status_flag'] = $item['icode_status'] == 0 ? '正常' : '禁用';
			// $item['is_google_flag'] = $yes_or_no[$item['is_google']];
			$item['stop_commission_flag'] = $yes_or_no[$item['stop_commission']];
			$item['reg_time'] = date('Y-m-d H:i:s', $item['reg_time']);
			$item['xf'] = Db::table('pro_order')->where(' uid=' . $item['id'])->sum('money');
			if ($item['login_time']) {
				$item['login_time'] = date('Y-m-d H:i:s', $item['login_time']);
			}
			if ($item['usn']) {
				$item['pre_usn'] = substr($item['usn'], 0, strlen($item['usn']) - 2);
				$item['suf_usn'] = substr($item['usn'], -2);
			}
		}
		$data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize,
			'balance' => (float) $count_item['balance'],
			'fz_balance' => (float) $count_item['fz_balance'],
			'$uids_str' => $uids_str,
			'where1' => $uid_arr,
			'where' => $where,
		];
		$return_data = array_merge($return_data, $data);
		if ($params['page'] < 2) {
			$return_data['p_usn'] = $pageuser['usn'];
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _user_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		if (!$params['nickname']) {
			ReturnToJson(-1, '请填写昵称');
		}
		$data = [
			'nickname' => $params['nickname'],
			'cbank' => $params['cbank']
		];
		// $data['gift'] = 0;
		if ($params['headimgurl']) {
			$data['headimgurl'] = $params['headimgurl'];
		} else {
			$data['headimgurl'] = 'public/avatar/headimgurl.jpg';
		}

		if ($params['phone']) {
			if (!isPhone($params['phone'])) {
				ReturnToJson(-1, '请填写正确的手机号');
			}
			$check_phone = Db::table('sys_user')->whereRaw('phone=:phone', ['phone' => $params['phone']])->find();
			if ($check_phone) {
				if (!$item_id || ($item_id && $item_id != $check_phone['id'])) {
					ReturnToJson(-1, '手机号已存在请更换');
				}
			}
			$data['phone'] = $params['phone'];
		}
		if ($pageuser['gid'] == 1) {
			// $is_google = intval($params['is_google']);
			// if ($is_google > 1) {
			// 	$data['google_hide'] = 0;
			// 	$data['google_secret'] = '';
			// } else {
			// 	$data['is_google'] = $is_google;
			// }

			$white_ip = str_replace('，', ',', trim($params['white_ip']));
			$white_ip_arr = explode(',', $white_ip);
			$ip_arr = [];
			foreach ($white_ip_arr as $ip) {
				$ip = trim($ip);
				if ($ip) {
					$ip_arr[] = $ip;
				}
			}

			$data['white_ip'] = implode(',', $ip_arr);
		}

		if ($pageuser['gid'] <= 71) {
			$data['status'] = intval($params['status']);
			$data['stop_commission'] = intval($params['stop_commission']);
		}

		$data['icode_status'] = intval($params['icode_status']);
		$data['gid'] = intval($params['gid']);
		$sys_group = getGroupsIdx();
		if (!array_key_exists($data['gid'], $sys_group)) {
			ReturnToJson(-1, '不存在相应分组');
		}
		if ($pageuser['gid'] != 1) {
			if ($data['gid'] <= $pageuser['gid']) {
				ReturnToJson(-1, '您的级别不足以设置该所属分组');
			}
		}

		if ($params['password']) {
			$data['password'] = getPassword($params['password']);
		}
		if ($params['password2']) {
			$data['password2'] = getPassword($params['password2']);
		}

		//邀请人判断
		$p_user = [];
		if ($pageuser['gid'] == 1) {
			if ($params['p_account']) {
				$p_user = Db::table('sys_user')->whereRaw('account=:account or phone=:phone', ['account' => $params['p_account'], 'phone' => $params['p_account']])->find();
				if ($p_user['id']) {
					//被编辑者的下级
					if ($item_id) {
						$down_ids = getDownUser($item_id);
						if (in_array($p_user['id'], $down_ids)) {
							ReturnToJson(-1, '邀请人不能是该用户的下级');
						}
					}
					$data['pid'] = $p_user['id'];
				} else {
					ReturnToJson(-1, '不存在该邀请人账号：' . $params['p_account']);
				}
			}
		} else {
			if (!$item_id) {
				$data['pid'] = $pageuser['id'];
			}
		}

		//检测编号

		if (!$item_id) {
			if (!$params['account']) {
				ReturnToJson(-1, '请填写账号');
			}
			if (utf8_strlen($params['account']) < 4 || utf8_strlen($params['account']) > 50) {
				ReturnToJson('-1', '请输入4-50个字符的账号');
			}
			//检查帐号是否已经存在
			$account = Db::table('sys_user')->whereRaw('account=:account', ['account' => $params['account']])->find();
			if ($account['id']) {
				ReturnToJson(-1, "账号{$params['account']}已经存在");
			}

			$data['icode'] = genIcode();
			$data['account'] = $params['account'];
			$data['openid'] = $params['account'];
			$data['reg_time'] = NOW_TIME;
			$data['reg_ip'] = CLIENT_IP;
		} else {
			if ($pageuser['gid'] > 41) {
				$uid_arr = getDownUser($pageuser['id']);
				if (!in_array($item_id, $uid_arr)) {
					ReturnToJson(-1, '不是自己下级用户无法编辑');
				}
			}
			// if ($item_id == 1) {
			// 	if ($pageuser['id'] != 1) {
			// 		ReturnToJson(-1, '没有修改该账号的权限');
			// 	}
			// 	$data['gid'] = 1;
			// 	$data['status'] = 2;
			// } else {
			// 	//用户被禁用同时踢下线
			// 	if ($data['status'] != 2) {
			// 		kickUser($item_id);
			// 	}
			// }
		}
		try {
			if ($item_id) {
				if ($data['pid'] == $item_id) {
					ReturnToJson(-1, '无法将上级设置为自己');
				}
				$res = Db::table('sys_user')->whereRaw('id=:id', ['id' => $item_id])->update($data);
				$user = Db::table('sys_user')->whereRaw('id=:id', ['id' => $item_id])->find();
				$data['account'] = $user['account'];
				$data['id'] = $item_id;
			} else {
				$res = false;
				do {
					$data['id'] = mt_rand(100000, 999999);
					$res = Db::table('sys_user')->insertGetId($data);
					if ($res)
						break;
				} while (true);
				createWallet($res); //创建钱包 
				$data['id'] = $res;
				updataUserPidGid($res);
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试', ['msgc' => $e->getMessage()]);
		}

		//刷新用户信息缓存
		flushUserinfo($data['id']);
		actionLog(['opt_name' => '更新用户', 'sql_str' => json_encode($data, 256)]);
		$return_data = [
			'p_account' => $p_user['account'],
			'p_nickname' => $p_user['nickname'],
			'gname' => $sys_group[$data['gid']]
		];
		$yse_or_no = getConfig('yes_or_no');
		$account_status = getConfig('account_status');
		// if (isset($data['is_google'])) {
		// 	$return_data['is_google_flag'] = $yse_or_no[$data['is_google']];
		// }
		if (isset ($data['status'])) {
			$return_data['status_flag'] = $account_status[$data['status']];
		}
		if (isset ($data['usn'])) {
			$return_data['usn'] = $data['usn'];
		}
		if (isset ($data['stop_commission'])) {
			$return_data['stop_commission_flag'] = $yse_or_no[$data['stop_commission']];
		}
		if (isset ($data['icode_status'])) {
			$return_data['icode_status_flag'] = $data['icode_status'] == 0 ? '正常' : '禁用';
		}

		ReturnToJson(1, '操作成功', $return_data);
	}

	//删除
	public function _user_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		if ($item_id == 1) {
			ReturnToJson(-1, '超级管理员不能删除');
		}
		if ($pageuser['gid'] > 41) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			if (!in_array($item_id, $uid_arr)) {
				ReturnToJson(-1, '不是自己的用户无法删除');
			}
		}
		$item = Db::table('sys_user')->whereRaw('id=:id', ['id' => $item_id])->find();
		if (!$item) {
			ReturnToJson('-1', '不存在相应的用户');
		}
		$sys_user = ['status' => 99];
		updateUserinfo($item_id, $sys_user);
		kickUser($item_id);
		actionLog(['opt_name' => '删除用户', 'sql_str' => json_encode($item, 256)]);
		ReturnToJson(1, '操作成功');
	}

	//踢下线
	public function _user_kick()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		if ($pageuser['gid'] != 1) {
			$uid_arr = getDownUser($pageuser['id']);
			if (!in_array($item_id, $uid_arr)) {
				ReturnToJson(-1, '不是自己的用户无法踢下线');
			}
		}
		$res = kickUser($item_id);
		if ($res === false) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '成功踢下线');
	}

	//后台统一充值余
	public function _user_pay()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$userid = intval($params['id']);
		$money = floatval($params['money']);
		if ($money == 0) {
			ReturnToJson(-1, '填写的额度不正确');
		}
		$pageuser = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		$password2 = getPassword($params['password2']);
		if ($pageuser['password2'] != $password2) {
			ReturnToJson(-1, '二级密码不正确');
		}
		Db::startTrans();
		try {
			$user = Db::table('sys_user')->whereRaw('id=:id', ['id' => $userid])->lock(true)->find();
			if (!$user) {
				ReturnToJson('-1', '不存在要操作的用户');
			}

			if ($pageuser['gid'] > 41) {
				ReturnToJson('-1', '未开放充值类型');
			}
			$sys_user = [];
			if ($params['ptype'] == 1) {
				$sys_user['balance'] = $user['balance'] + $money;
				if ($sys_user['balance'] < 0) {
					ReturnToJson(-1, '用户可用余额不足');
				}
				$res2 = balanceLog($user, 1, 2, $money, $user['id'], $params['remark']);
			} elseif ($params['ptype'] == 2) {
				$sys_user['fz_balance'] = $user['fz_balance'] + $money;
				if ($sys_user['fz_balance'] < 0) {
					ReturnToJson(-1, '用户可用冻结不足');
				}
				$res2 = balanceLog($user, 2, 3, $money, $user['id'], $params['remark']);
			} else {
				ReturnToJson(-1, '未知操作类型');
			}
			$res = Db::table('sys_user')->where("id={$user['id']}")->update($sys_user);
			if (!$res || !$res2 || !$res2) {
				throw new \Exception('系统繁忙请稍后再试');
			}
			Db::commit();
			//刷新用户信息缓存
			$new_user = flushUserinfo($user['id']);
			$return_data = [
				'balance' => $new_user['balance'],
				'fz_balance' => $new_user['fz_balance']
			];
			ReturnToJson(1, '操作成功', $return_data);
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
	}

	//转移所有下级
	public function _transferAct()
	{
		$pageuser = checkPower();
		$params = $this->params;

		$from_account = $params['from_account'] ?? null;
		$to_account = $params['to_account'] ?? null;
		$password2 = getPassword($params['password2'] ?? '');

		if (!$from_account || !$to_account) {
			ReturnToJson(-1, '请填写转入/转出账号');
		}
		if ($from_account == $to_account) {
			ReturnToJson(-1, '转入账号和转出账号不能相同');
		}

		if ($pageuser['password2'] != $password2) {
			ReturnToJson(-1, '二级密码不正确');
		}

		$from_user = Db::table('sys_user')->where("account='{$from_account}'")->find();
		$to_user = Db::table('sys_user')->where("account='{$to_account}'")->find();

		if (!$from_user || !$to_user) {
			ReturnToJson(-1, '账号不存在');
		}
		$down_ids = getDownUser($from_user['id']);
		$uid_str = implode(',', $down_ids);
		if (in_array($to_user['id'], $down_ids)) {
			ReturnToJson(-1, '转入账号不能是转出账号的下级');
		}
		$sq = 0;
		Db::startTrans();
		try {
			$list = Db::table('sys_user')->where("pid={$from_user['id']}")->select()->toArray(); //所有下级
			foreach ($list as $item) { //更新所有下级的pid
				$sys_user = [
					'pid' => $to_user['id'],
				];
				Db::table('sys_user')->where("id={$item['id']}")->update($sys_user);
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		sleep(1);
		Db::table('sys_user')->where(' id in(' . $uid_str . ')')->update(['pidg1' => 0]);
		foreach ($down_ids as $item) { // 更新所有下级的pids pidg1 pidg2	 
			$sql = "WITH RECURSIVE cte AS (SELECT id, pid, gid FROM sys_user WHERE id = {$item}
						UNION ALL SELECT t.id, t.pid, t.gid FROM sys_user t JOIN cte ON t.id = cte.pid) 
						UPDATE sys_user
						SET pidg1 = (SELECT id FROM cte WHERE gid = 71),
						pidg2 = (SELECT id FROM cte WHERE gid = 81) 
						WHERE sys_user.id ={$item}"; //更新当前用户的 pidg1 pidg2	   
			$sq += Db::execute($sql);
		}
		$sq += Db::execute($sql);
		ReturnToJson(1, '转移成功,等待后台同步所有下级的层级，预计1-10分钟后同步完成 需要更新层级数量：' . $sq, ['$down_ids' => $down_ids, '$t1' => $sq]);
	}

	public function _transferAct3()
	{
		$pageuser = checkPower();
		$sys_user = $this->params;
		if (!$sys_user['from_account']) {
			ReturnToJson(-1, '请填写要同步的账户');
		}
		$sys_user = Db::table('sys_user')->where("account='{$sys_user['from_account']}'")->find();
		//更新sys_user表的pidg1 pidg2
		$down_arr = Db::table('sys_user')->where("account='{$sys_user['account']}'")->update(['pidg1' => 0, 'pidg2' => 0]);
		ReturnToJson(1, '同步成功 数量：' . $down_arr);
	}


	public function _transferAct2()
	{
		$pageuser = checkPower();
		$params = $this->params;
		if (!$params['deft']) {
			ReturnToJson(-1, '没有需要同步的账户');
		}

		Db::startTrans();
		try {
			$deft = explode(',', $params['deft']);
			foreach ($deft as $key => $value) {
				updataUserPidGid($value);
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '同步成功');
	}

	public function _transferAct1()
	{
		$pageuser = checkPower();
		$params = $this->params;
		if (!$params['from_account']) {
			ReturnToJson(-1, '请填写转出账号');
		}
		if ($pageuser['password2'] != getPassword($params['password2'])) {
			ReturnToJson(-1, '二级密码不正确');
		}
		$from_user = Db::table('sys_user')->where("account='{$params['from_account']}'")->find();
		if (!$from_user) {
			ReturnToJson(-1, '不存在代理账号');
		}
		$arr1 = getDownUser($from_user['id']);
		$arr2 = getDownUser($from_user['id'], false, $from_user);
		ReturnToJson(1, '转移成功', ['arr1' => $arr1, 'arr2' => $arr2]);
	}

	//批量禁用和解禁，转有效无效
	public function _DisableStatus()
	{
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
		if ($params['bs']) {
			if ($params['status'] == 0)
				$now_day = 0;
			else
				$now_day = date('Ymd', NOW_TIME);

			foreach ($ids as $item_id) {
				Db::table('sys_user')->where("id={$item_id}")->update(["{$params['field']}" => $now_day]);
			}
		} else {
			foreach ($ids as $item_id) {
				Db::table('sys_user')->where("id={$item_id}")->update(["{$params['field']}" => $params['status']]);
			}
		}
		ReturnToJson(1, '操作成功');
	}

	//##################用户管理结束##################

	//实名认证	
	public function _rauth()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_status'] = intval($params['s_status']);

		$where = "log.status>0";
		if ($pageuser['gid'] > 41) {
			$uid_arr = getDownUser($pageuser['id']);
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}
		$where .= empty ($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		$where .= empty ($params['s_keyword']) ? '' : " and (u.account='{$params['s_keyword']}' or log.realname='{$params['s_keyword']}')";

		$count_item = Db::table('sys_user_rauth log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt')
			->whereRaw($where)
			->find();
		$list = Db::view(['sys_user_rauth' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->whereRaw($where)
			->order(['log.create_time' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_rauth_status = getConfig('cnf_rauth_status');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i', $item['create_time']);
			$item['update_time'] = date('m-d H:i', $item['update_time']);
			if ($item['check_time']) {
				$item['check_time'] = date('m-d H:i', $item['check_time']);
			} else {
				$item['check_time'] = '/';
			}
			$item['status_flag'] = $cnf_rauth_status[$item['status']];
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['rauth_status'] = $cnf_rauth_status;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _rauth_check()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['status'] = intval($params['status']);
		$cnf_rauth_status = getConfig('cnf_rauth_status');
		if (!array_key_exists($params['status'], $cnf_rauth_status)) {
			ReturnToJson(-1, '未知审核状态');
		}
		$item_id = intval($params['uid']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('sys_user_rauth');
		$item = $model->whereRaw('uid=:id', ['id' => $item_id])->find();
		if (!$item) {
			ReturnToJson(-1, '不存在相应的记录');
		}
		if (!in_array($item['status'], [1, 2])) {
			ReturnToJson(-1, '当前状态不可操作');
		}
		if ($pageuser['gid'] > 41) {
			$uid_arr = getDownUser($pageuser['id']);
			if (!in_array($item['uid'], $uid_arr)) {
				ReturnToJson(-1, '不是自己的用户无法操作');
			}
		}
		$db_data = [
			'status' => $params['status'],
			'check_remark' => $params['check_remark'],
			'check_time' => NOW_TIME
		];
		try {
			$model->where("uid={$item['uid']}")->update($db_data);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		updateUserinfo($item['uid'], ['realname' => $item['realname']]);
		$cnf_rauth_status = getConfig('cnf_rauth_status');
		$return_data = [
			'status' => $db_data['status'],
			'status_flag' => $cnf_rauth_status[$db_data['status']],
			'check_time' => date('m-d H:i', $db_data['check_time'])
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	//用户分组
	public function _group()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$where = "log.status<99";
		//$where.=empty($params['s_status'])?'':" and log.status={$params['s_status']}";
		$where .= empty ($params['s_keyword']) ? '' : " and (log.name like '%{$params['s_keyword']}%')";

		$count_item = Db::table('sys_group log')
			//->leftJoin('sys_user u','log.uid=u.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();
		$list = Db::view(['sys_group' => 'log'], ['*'])
			//->view(['sys_user'=>'u'],['account','nickname','headimgurl'],'log.uid=u.id','LEFT')
			->where($where)
			->order(['log.sort' => 'desc', 'log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i', $item['create_time']);
		}
		$data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		ReturnToJson(1, 'ok', $data);
	}

	public function _group_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['nid'] = intval($params['nid']);
		$params['sort'] = intval($params['sort']);
		if ($params['nid'] < 0) {
			ReturnToJson(-1, '请填写正确的ID');
		}
		if (!$params['name']) {
			ReturnToJson(-1, '请填写名称');
		}
		if (!$params['cover']) {
			ReturnToJson(-1, '请上传图标');
		}
		$db_data = [
			'name' => $params['name'],
			'remark' => $params['remark'],
			'sort' => $params['sort'] ? $params['sort'] : 100,
			'cover' => $params['cover']
		];
		if ($params['nid'] && $params['nid'] != 1) {
			$db_data['id'] = $params['nid'];
		}
		try {
			$model = Db::table('sys_group');
			if ($item_id) {
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
		$this->flushGroups();
		actionLog(['opt_name' => '更新', 'sql_str' => json_encode($db_data)]);
		$return_data = [];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _group_delete()
	{
		checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		} else {
			if ($item_id == 1) {
				ReturnToJson(-1, '超管组不可删除');
			}
		}
		$model = Db::table('sys_group');
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
		$this->flushGroups();
		ReturnToJson(1, '操作成功');
	}

	private function flushGroups()
	{
		$this->redis->rm('sys_group');
	}


	//邀请链接
	public function _ulink()
	{
		$pageuser = checkPower();
		$url = REQUEST_SCHEME . '://' . HTTP_HOST . "/#/register?icode={$pageuser['icode']}";
		$return_data = [
			'url' => $url,
			'qrcode' => genQrcode($url)
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//用户留言
	public function _message()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$where = "1=1";
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		$where .= empty ($params['s_keyword']) ? '' : " and (u.account='{$params['s_keyword']}' or log.title like '%{$params['s_keyword']}%')";

		$count_item = Db::table('msg_list log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt')
			->where($where)->find();
		$list = Db::view(['msg_list' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['covers'] = json_decode($item['covers'], true);
			if (!$item['covers']) {
				$item['covers'] = [];
			}
		}
		$data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $data);
	}

	public function _message_delete()
	{
		checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('msg_list');
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

	public function _message_log()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		$params['mid'] = intval($params['mid']);

		$where = "log.mid={$params['mid']}";
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}

		$count_item = Db::table('msg_list_log log')
			->leftJoin('sys_user u', 'log.fuid=u.id')
			->fieldRaw('count(1) as cnt')
			->where($where)->find();
		$list = Db::view(['msg_list_log' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.fuid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
		}
		$data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		Db::table('msg_list')->where("id={$params['mid']}")->update(['is_new' => 0]);
		ReturnToJson(1, 'ok', $data);
	}

	public function _message_reply()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['mid'] = intval($params['mid']);
		if (!$params['mid']) {
			ReturnToJson(-1, '缺少参数');
		}
		if (!$params['content']) {
			ReturnToJson(-1, '请填写回复内容');
		}
		$item = Db::table('msg_list')->where("id={$params['mid']} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, '不存在相应的记录');
		}
		try {
			$msg_list_log = [
				'fuid' => 0,
				'mid' => $item['id'],
				'content' => $params['content'],
				'create_time' => NOW_TIME
			];
			Db::table('msg_list_log')->insertGetId($msg_list_log);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '操作成功');
	}

	//会员统计
	public function _statistics()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['page'] = intval($params['page']);
		$this->pageSize = 20;


		$where = "log.status<99";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_str = implode(',', $uid_arr);
			if (!$uid_str) {
				$uid_str = '0';
			}
			$where .= " and log.id in({$uid_str})";
		}



		if ($params['s_loginip']) {
			$where .= " and log.login_ip ='{$params['s_loginip']}'";
		}

		if ($params['s_regip']) { //reg_ip
			$where .= " and log.reg_ip ='{$params['s_regip']}'";
		}
		if ($params['s_keyword2']) {
			$s_keyword2 = $params['s_keyword2'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword2}' or openid='{$s_keyword2}' or account='{$s_keyword2}'")->find();
			if ($s_puser) {
				$uid_arr = getDownUser_new($s_puser['id'], false, ' first_pay_day>0');
			}
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.id in({$uid_str})";
		}

		if ($params['s_keyword3']) {
			$s_keyword3 = $params['s_keyword3'];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword3}' or account='{$s_keyword3}' or email='{$s_keyword3}' or realname='{$s_keyword3}' or nickname='{$s_keyword3}'")->find();
			if ($s_puser) {
				$where .= " and log.pid={$s_puser['id']}";
			} else {
				$where .= " and log.pid=0";
			}
		}

		if (isset ($params['s_xiaofei'])) {
			if ($params['s_xiaofei'] == 1) {
				$where .= " and log.total_invest2>0";
			} else {
				$where .= " and log.total_invest2<=0";
			}
		}

		if ($params['regTimeRange']) {
			$start_time = strtotime($params['regTimeRange'][0]);
			$end_time = strtotime($params['regTimeRange'][1]);
			$where .= " and log.reg_time between {$start_time} and {$end_time}";
		}

		$where .= empty ($params['s_keyword']) ? '' : " and (log.account like '%{$params['s_keyword']}%' or log.nickname like '%{$params['s_keyword']}%')";

		$params['s_money_from'] = floatval($params['s_money_from']);
		$params['s_money_to'] = floatval($params['s_money_to']);
		if ($params['s_money_from'] > 0 && $params['s_money_to'] > 0) {
			if ($params['s_money_from'] > $params['s_money_to']) {
				ReturnToJson(-1, '起始金额不能小于结束金额');
			}
			$where .= " and log.total_invest2 between {$params['s_money_from']} and {$params['s_money_to']}";
		}

		$where .= " and log.gid>81";
		$count_item = Db::table('sys_user log')
			->leftJoin('sys_user pu', 'log.pid=pu.id')
			->fieldRaw('count(1) as cnt')
			->where($where)->find();


		$rediskey = 'statistics_' . $params['page'] . $pageuser['id'] . $params['page'] . $where;
		$redisrest = $this->redis->get($rediskey);

		if ($redisrest) {
			$list = $redisrest;
		} else {
			$list = Db::view(['sys_user' => 'log'], ['*'])
				->view(['sys_user' => 'pu'], ['account' => 'p_account', 'nickname' => 'p_nickname', 'realname' => 'p_realname'], 'log.pid=pu.id', 'LEFT')

				->where($where)
				->order(['log.teamcount' => 'desc'])
				->page($params['page'], $this->pageSize)
				->select()->toArray();

			$sys_group = getGroupsIdx();
			foreach ($list as &$item) {
				$item['reg_time'] = date('y-m-d H:i:s', $item['reg_time']);
				$item['login_time'] = date('y-m-d H:i:s', $item['login_time']);
				$item['gname'] = $sys_group[$item['gid']];
				$item['tdyj'] = Db::table('pro_reward')->where("uid={$item['id']} and type=2")->sum('money');
				$item['zsy'] = Db::table('pro_reward')->where("uid={$item['id']}")->sum('money');
				$item['zxf'] = $item['total_invest2'];
				$item['zcz'] = Db::table('fin_paylog')->where("uid={$item['id']} and status=9")->sum('money');
				$item['ztx'] = Db::table('fin_cashlog')->where("uid={$item['id']} and status in (1,9)")->sum('money');
				$down_arr = getDownUser_new($item['id'], false, ' first_pay_day>0');
				$item['tjrs'] = Db::table('sys_user')->where("pid={$item['id']} and first_pay_day>0")->count('id');
				$item['tdrs'] = $item['teamcount'];
				$down_arr[] = 0;
				$down_arr_str = implode(',', $down_arr);
				$item['tjyj'] = Db::table('pro_order')->where("uid in ({$down_arr_str})")->sum('money');
				$wallet1 = getWallet($item['id'], 1);
				$wallet2 = getWallet($item['id'], 2);
				$item['czqb'] = $wallet1['balance'];
				$item['yeqb'] = $wallet2['balance'];
				if ($item['first_pay_day']) {
				} else {
					$item['first_pay_day'] = '无';
				}
				$item['lirun'] = Db::table('wallet_log')->where("uid={$item['id']} and type in (6,8,9,10,41)")->sum('money');

				$agents = getAgents($item['id']);
				$item['zt_account'] = $agents['zt_account'];
				$item['agent1_account'] = $agents['agent1_account'];
				$item['agent2_account'] = $agents['agent2_account'];
			}

			$tk_key = $rediskey;
			$tag = $rediskey;
			$this->redis->set($tk_key, $list, 60 * 2, $tag);
		}
		$data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize,
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $data);
	}

	//代理查询
	public function _agent()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$params['type'] = intval($params['type']);
		$start_time = 0;
		$end_time = 0;
		$today_start = strtotime(date('Y-m-d 00:00:01'));
		$today_end = strtotime(date('Y-m-d 23:59:59'));
		$now_day = date('Ymd');
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:01');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
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
		}

		if ($params['type']) {
			$today_start = $start_time;
			$today_end = $end_time;
		} else {
			$today_start = strtotime(date('Y-m-d 00:00:01'));
			$today_end = strtotime(date('Y-m-d 23:59:59'));
		}

		$where = "log.status<99";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_str = implode(',', $uid_arr);
			if (!$uid_str) {
				$uid_str = '0';
			}
			$where .= " and log.id in({$uid_str})";
		} else {
			$where .= " and log.gid=71";
		}
		if ($params['s_keyword2']) {
			$s_keyword2 = $params['s_keyword2'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword2}' or account='{$s_keyword2}'")->find();
			if ($s_puser) {
				$uid_arr = getDownUser_new($s_puser['id']);
			}
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.id in({$uid_str})";
		}

		$where .= empty ($params['s_keyword']) ? '' : " and (log.account='{$params['s_keyword']}' or log.nickname like '%{$params['s_keyword']}%')";
		$where .= " and log.gid in (71,81)";

		$count_item = Db::table('sys_user log')
			->fieldRaw('count(1) as cnt')
			->where($where)->find();
		$list = Db::view(['sys_user' => 'log'], ['*'])
			->where($where)
			->order(['log.reg_time' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$sys_group = getGroupsIdx();
		$whereBase = '1=1';
		if ($start_time > 0 && $end_time > 0) {
			$whereBase .= " and (create_time between {$start_time} and {$end_time})";
			//$whereBaseUser .= " and (reg_time between {$start_time} and {$end_time})";
		}

		$zcz = $ztx = $jrcz = $jrtx = $jrcj = $jrhb = $yxhy = $jrsc = $jrzc = 0;

		foreach ($list as &$itme) {
			$itme['reg_time'] = date('m-d H:i:s', $itme['reg_time']);
			$itme['gname'] = $sys_group[$itme['gid']];
			$down_arr = getDownUser($itme['id'], false, $itme);
			$down_arr[] = 0;
			$down_str = implode(',', $down_arr);
			$where = $whereBase . " and uid in ({$down_str})";
			$pidg = '1=1';
			if ($start_time > 0 && $end_time > 0) {
				$pidg .= " and (create_time between {$start_time} and {$end_time})";
				//$whereBaseUser .= " and (reg_time between {$start_time} and {$end_time})";
			}
			if ($itme['gid'] == 71) {
				$pidg .= " and su.pidg1 = {$itme['id']} ";
			} else if ($itme['gid'] == 81) {
				$pidg .= " and su.pidg2 = {$itme['id']} ";
			}

			////////////////////
			//总充值
			$itme['zcz'] = Db::table('fin_paylog log')
				->leftJoin('sys_user su', 'log.uid=su.id')
				->where("{$pidg} and log.status=9")
				->sum('log.money');
			//今日充值
			$itme['jrcz'] = Db::table('fin_paylog log')
				->leftJoin('sys_user su', 'log.uid=su.id')
				->where("{$pidg} and log.status=9 and (pay_time between {$today_start} and {$today_end})")
				->sum('log.money');
			//今日首充
			$itme['jrsc'] = Db::table('fin_paylog log')
				->leftJoin('sys_user su', 'log.uid=su.id')
				->where("{$pidg} and is_first=1 and log.status=9 and (pay_time between {$today_start} and {$today_end})")
				->count('log.id');

			//总提现
			$itme['ztx'] = Db::table('fin_cashlog log')
				->leftJoin('sys_user su', 'log.uid=su.id')
				->where("{$pidg}  and log.status=9 and log.pay_status=9")->sum('log.money');
			//今日提现
			$itme['jrtx'] = Db::table('fin_cashlog log')
				->leftJoin('sys_user su', 'log.uid=su.id')
				->where("{$pidg} and (log.pay_time between {$today_start} and {$today_end}) and log.pay_status=9 and log.status=9")->sum('log.money');
			////////////////////
			//今日抽奖
			$itme['jrcj'] = Db::table('gift_lottery_log')->where("uid in ({$down_str}) and create_time between {$today_start} and {$today_end}")->sum('money');
			//今日红包
			$itme['jrhb'] = Db::table('gift_redpack_detail')->where("uid in ({$down_str}) and receive_time between {$today_start} and {$today_end}")->sum('money');
			//有效会员
			$itme['yxhy'] = Db::table('fin_paylog')->where("uid in ({$down_str}) and is_first=1 ")->count('id');
			//今日注册
			$itme['jrzc'] = Db::table('sys_user')->where("id in ({$down_str}) and (reg_time between {$today_start} and {$today_end}) and status<99")->count('id');
			$zcz += round(floatval($itme['zcz']), 2); //$item['zcz'];
			$ztx += round(floatval($itme['ztx']), 2); //$item['ztx'];
			$jrcz += round(floatval($itme['jrcz']), 2); //$item['jrcz'];
			$jrtx += round(floatval($itme['jrtx']), 2); //$item['jrtx'];
			$jrcj += round(floatval($itme['jrcj']), 2); //$item['jrcj'];
			$jrhb += round(floatval($itme['jrhb']), 2); //$item['jrhb'];
			$yxhy += round(floatval($itme['yxhy']), 2); //$item['yxhy'];
			$jrsc += round(floatval($itme['jrsc']), 2); //$item['jrsc'];
			$jrzc += round(floatval($itme['jrzc']), 2); //$item['jrzc'];
		}

		for ($i = 0; $i < 1; $i++) {
			$list[] = [
				'zcz' => $zcz,
				// 充值总额
				'ztx' => $ztx,
				'zjy' => number_format(floatval($zcz) - floatval($ztx), 2, '.', ''),
				'jrcz' => $jrcz,
				'jrtx' => $jrtx,
				'jrcj' => $jrcj,
				'jrhb' => $jrhb,
				'yxhy' => $yxhy,
				'jrsc' => $jrsc,
				'jrzc' => $jrzc,
				'account' => '合计'
			];
		}
		$data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize,
			'start_time' => $start_time,
			'end_time' => $end_time
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $data);
	}
}
