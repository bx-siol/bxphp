<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class SysController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//##################平台设置##################
	public function _pset()
	{
		checkPower();
		$bank_arr = Db::table('cnf_bank')->where('status=2')->select()->toArray();
		$pset_list = Db::table('sys_pset')->select()->toArray();
		$pset_arr = [];
		foreach ($pset_list as &$hv) {
			$config_decode = json_decode($hv['config'], true);
			if (!is_array($config_decode)) {
				$config_decode = $hv['config'];
			}
			$pset_arr[$hv['skey']] = $config_decode;
		}

		//用户分组
		$groups = getGroups();
		$group_arr = [];
		foreach ($groups as $gv) {
			if ($gv['id'] >= 86 && $gv['id'] <= 90) {
				$group_arr[] = $gv;
			}
		}
		rsort($group_arr);
		$return_data = [
			'group_arr' => $group_arr,
			'bank_arr' => $bank_arr,
			'currency_arr' => getCurrency()
		];
		$return_data = array_merge($return_data, $pset_arr);
		$lottery = Db::table('gift_lottery')->where("id=1")->field(['rsn', 'stock_money', 'from_money', 'to_money', 'status', 'day_limit', 'week_limit', 'lottery_min'])->find();

		$lottery['url'] = REQUEST_SCHEME . '://' . HTTP_HOST . "/#/h5/lottery/{$lottery['rsn']}";
		$return_data['lottery'] = $lottery;
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _pset_update()
	{
		checkPower();
		$params = $this->params;
		$white_items = [
			'service' => '客服信息',
			'contact' => '联系信息',
			'copyright' => '版权信息',
			'indexKv' => '首页轮播图',
			'app' => 'APP相关',
			'project' => '项目相关',
			'shop' => '商城相关',
			'balance' => '余额相关'
		];
		$data_arr = [];
		foreach ($params as $key => $val) {
			$key = trim($key);
			if (!array_key_exists($key, $white_items)) {
				continue;
			}
			$data_arr[$key] = $val;
		}
		foreach ($data_arr as $dk => $dv) {
			$check = Db::table('sys_pset')->whereRaw('skey=:skey', ['skey' => $dk])->find();
			if (is_array($dv)) {
				$config = json_encode($dv, 256);
			} else {
				$config = $dv;
			}
			$sys_pset = [
				'name' => $white_items[$dk],
				'config' => $config,
				'update_time' => NOW_TIME
			];
			if (!$check) {
				$sys_pset['skey'] = $dk;
				$sys_pset['create_time'] = NOW_TIME;
				$res = Db::table('sys_pset')->insert($sys_pset);
			} else {
				$res = Db::table('sys_pset')->where("id={$check['id']}")->update($sys_pset);
			}
		}
		ReturnToJson(1, '保存成功');
	}


	public function _lottery_update()
	{
		checkPower();
		$params = $this->params;
		$params['stock_money'] = floatval($params['stock_money']);
		$params['from_money'] = floatval($params['from_money']);
		$params['to_money'] = floatval($params['to_money']);
		$params['day_limit'] = intval($params['day_limit']);
		$params['week_limit'] = intval($params['week_limit']);
		$params['lottery_min'] = intval($params['lottery_min']);
		$params['status'] = intval($params['status']);
		if ($params['stock_money'] < 0) {
			ReturnToJson(-1, '库存额度不正确');
		}
		if ($params['from_money'] <= 0) {
			ReturnToJson(-1, '单个红包起始金额不正确');
		}
		if ($params['from_money'] > $params['to_money']) {
			ReturnToJson(-1, '起始金额不正确');
		}
		if ($params['day_limit'] < 0 || $params['from_money'] < 0) {
			ReturnToJson(-1, '单用户领取限制不正确');
		}
		if (!in_array($params['status'], [1, 3])) {
			ReturnToJson(-1, '未知状态');
		}
		$db_item = [
			'stock_money' => $params['stock_money'],
			'from_money' => $params['from_money'],
			'to_money' => $params['to_money'],
			'day_limit' => $params['day_limit'],
			'week_limit' => $params['week_limit'],
			'lottery_min' => $params['lottery_min'],
			'status' => $params['status']
		];
		try {
			Db::table('gift_lottery')->where("id=1")->update($db_item);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '操作成功');
	}
	//##################日志管理开始##################

	public function _log()
	{
		checkPower();
		$params = $this->params;
		$where = '1=1';
		if ($params['s_start_time'] && $params['s_end_time'] && $params['s_start_time'] <= $params['s_end_time']) {
			$s_start_time = strtotime($params['s_start_time'] . ' 00:00:01');
			$s_end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			$where .= " and log.create_time between {$s_start_time} and {$s_end_time}";
		}
		$where .= empty($params['s_keyword']) ? '' : " and (u.account='{$params['s_keyword']}' 
		or log.create_ip='{$params['s_keyword']}' 
		or u.nickname like '%{$params['s_keyword']}%'
		or log.opt_name like '%{$params['s_keyword']}%' 
		or log.sql_str like '%{$params['s_keyword']}%')";
		$count_item = Db::table('sys_log log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['sys_log' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['nickname', 'account'], 'log.uid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['nickname'] = $item['account'];
			$item['sql_str'] = stripslashes($item['sql_str']);
		}
		$data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		ReturnToJson(1, 'ok', $data);
	}

	//##################日志管理结束##################


	//##################节点管理##################

	public function _node()
	{
		checkPower();
		$params = $this->params;
		$this->pageSize = 20;
		$where = '1=1';
		if (is_numeric($params['s_type'])) {
			$params['s_type'] = intval($params['s_type']);
			$where .= " and log.type='{$params['s_type']}'";
		}
		if (is_numeric($params['s_public'])) {
			$params['s_public'] = intval($params['s_public']);
			$where .= " and log.public='{$params['s_public']}'";
		}
		$where .= empty($params['s_keyword']) ? '' : " and (log.nkey='{$params['s_keyword']}' or log.name like '%{$params['s_keyword']}%')";
		$count_item = Db::table('sys_node log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['sys_node' => 'log'], ['*'])
			->where($where)
			->orderRaw("CONCAT(log.pre_path,'-',log.id),log.sort desc")
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$top_node = Db::table('sys_node')->field(['id', 'name'])->where('pid=0')->select()->toArray();
		$top_node = rows2arr($top_node);

		$yes_no = getConfig('yes_or_no');
		foreach ($list as &$item) {
			if ($item['create_time']) {
				$item['create_time'] = date('m-d H:i', $item['create_time']);
			}
			$item['pname'] = $top_node[$item['pid']]['name'];
			$item['type_flag'] = $yes_no[$item['type']];
			$item['public_flag'] = $yes_no[$item['public']];
		}
		$return_data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize,
			'top' => $top_node
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _node_update()
	{
		$pageuser = checkPower();
		if ($pageuser['id'] != 1 && $pageuser['id'] != 2) {
			ReturnToJson(-1, '没有权限操作' . ($pageuser['id']));
		}
		$params = $this->params;
		$item_id = intval($params['id']);
		$pid = intval($params['pid']);
		if (!$params['nkey']) {
			ReturnToJson(-1, '请填写NKEY');
		}
		if (!$params['name']) {
			ReturnToJson(-1, '请填写节点名称');
		}
		$sys_node = [
			'name' => $params['name'],
			'pid' => intval($params['pid']),
			'type' => intval($params['type']),
			'public' => intval($params['public']),
			'sort' => intval($params['sort']),
			'nkey' => $params['nkey'],
			'ico' => $_POST['ico'],
			'url' => $_POST['url'],
			'remark' => $params['remark']
		];
		$pre_path = '0';
		if ($pid) {
			$p_node = Db::table('sys_node')->field(['id', 'pre_path'])->whereRaw('id=:id', ['id' => $pid])->find();
			$pre_path = $p_node['pre_path'] . '-' . $pid;
		}
		$sys_node['pre_path'] = $pre_path;
		try {
			if ($item_id) {
				if ($pid == $item_id) {
					ReturnToJson(-1, '父节点不能设置成自己');
				} else {
					if ($pid) {
						//检查自己是否有子节点，如果有子节点不允许直接将自己设置成其他节点的子节点
						$check_sub = Db::table('sys_node')->field(['id'])->whereRaw('pid=:pid', ['pid' => $item_id])->find();
						if ($check_sub) {
							ReturnToJson(-1, '该节点下面有子节点，请先清除');
						}
					}
				}
				$res = Db::table('sys_node')->whereRaw('id=:id', ['id' => $item_id])->update($sys_node);
			} else {
				$sys_node['create_time'] = NOW_TIME;
				$res = Db::table('sys_node')->insert($sys_node);
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		actionLog(['opt_name' => '更新节点', 'sql_str' => Db::getLastSql()]);
		$yes_or_no = getConfig('yes_or_no');
		$return_data = [
			'public_flag' => $yes_or_no[$sys_node['public']],
			'type_flag' => $yes_or_no[$sys_node['type']]
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _node_delete()
	{
		$pageuser = checkPower();
		if ($pageuser['id'] != 1) {
			ReturnToJson(-1, '没有权限操作');
		}
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		if ($item_id < 50) {
			//ReturnToJson(-1,'系统基础节点不能删除');
		}
		Db::startTrans();
		try {
			$item = Db::table('sys_node')->whereRaw('id=:id', ['id' => $item_id])->lock(true)->find();
			if (!$item) {
				ReturnToJson(-1, '该节点已经删除');
			}
			Db::table('sys_node')->where("id={$item['id']} or pid={$item['id']}")->delete();
			Db::commit();
			actionLog(['opt_name' => '删除节点', 'sql_str' => json_encode($item)]);
			ReturnToJson(1, '操作成功');
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
	}
	//##################节点管理结束##################


	//##################配置管理开始##################
	public function _bset()
	{
		checkPower();
		$params = $this->params;
		$where = 'is_show=1';
		if (is_numeric($params['s_single'])) {
			$params['s_single'] = intval($params['s_single']);
			$where .= " and log.single={$params['s_single']}";
		}
		$where .= empty($params['s_keyword']) ? '' : " and (log.skey like '{$params['s_keyword']}' or log.name like '%{$params['s_keyword']}%')";

		$count_item = Db::table('sys_config log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['sys_config' => 'log'], ['*'])
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$yes_no = getConfig('yes_or_no');
		foreach ($list as &$item) {
			$item['single_flag'] = $yes_no[$item['single']];
			$item['config_flag'] = nl2br($item['config']);
		}
		$data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		ReturnToJson(1, 'ok', $data);
	}

	public function _bset_update()
	{
		$pageuser = checkPower();
		if ($pageuser['id'] != 1) {
			ReturnToJson(-1, '没有权限操作');
		}
		$params = $this->params;
		$params['name'] = $_POST['name'];
		$item_id = intval($params['id']);
		if (!$params['skey']) {
			ReturnToJson(-1, '请填写SKEY');
		}
		if (!$params['name']) {
			ReturnToJson(-1, '请填写配置名称');
		}
		$sys_config = [
			'name' => $params['name'],
			'single' => intval($params['single']),
			'skey' => $params['skey'],
			'config' => $_POST['config'],
			'update_time' => NOW_TIME
		];
		try {
			if ($item_id) {
				$res = Db::table('sys_config')->whereRaw('id=:id', ['id' => $item_id])->update($sys_config);
			} else {
				$res = Db::table('sys_config')->insert($sys_config);
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '没有修改或系统繁忙!请检查SKEY是否有重复！');
		}
		$this->flushBset($params['skey']);
		actionLog(['opt_name' => '更新配置', 'sql_str' => Db::getLastSql()]);

		if ($params['skey'] == 'wx_gzh_config') {
			$wx_gzh_config = getConfig('wx_gzh_config');
			$sys_gzh = [
				'appid' => $wx_gzh_config['appid'],
				'appsecret' => $wx_gzh_config['appsecret'],
				'access_token_time' => NOW_TIME - 7200,
				'jsapi_ticket_time' => NOW_TIME - 7200
			];
			Db::table('sys_gzh')->where('id=1')->update($sys_gzh);
		} elseif ($params['skey'] == 'wx_mini_config') {
			$wx_mini_config = getConfig('wx_mini_config');
			$sys_gzh = [
				'appid' => $wx_mini_config['appid'],
				'appsecret' => $wx_mini_config['appsecret'],
				'access_token_time' => NOW_TIME - 7200,
				'jsapi_ticket_time' => NOW_TIME - 7200
			];
			Db::table('sys_gzh')->where('id=2')->update($sys_gzh);
		} elseif ($params['skey'] == 'wx_mapp_config') {
			$wx_mapp_config = getConfig('wx_mapp_config');
			$sys_gzh = [
				'appid' => $wx_mapp_config['appid'],
				'appsecret' => $wx_mapp_config['appsecret'],
				'access_token_time' => NOW_TIME - 7200,
				'jsapi_ticket_time' => NOW_TIME - 7200
			];
			Db::table('sys_gzh')->where('id=3')->update($sys_gzh);
		}
		$yes_no = getConfig('yes_or_no');
		$return_data = [
			'config_flag' => nl2br($sys_config['config']),
			'single_flag' => $yes_no[$sys_config['single']]
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _bset_delete()
	{
		$pageuser = checkPower();
		if ($pageuser['id'] != 1) {
			ReturnToJson(-1, '没有权限操作');
		}
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		if ($item_id < 256) {
			ReturnToJson(-1, '系统基础配置不能删除');
		}
		$item = Db::table('sys_config')->whereRaw('id=:id', ['id' => $item_id])->find();
		if (!$item) {
			ReturnToJson(-1, '该配置已删除');
		}
		try {
			Db::table('sys_config')->where("id={$item['id']}")->delete();
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$this->flushBset($item['skey']);
		actionLog(['opt_name' => '删除配置', 'sql_str' => json_encode($item)]);
		ReturnToJson(1, '操作成功');
	}

	//刷新配置
	private function flushBset($skey)
	{
		$mem_key = 'sys_config_' . $skey;
		$memcache = new MyRedis(0);
		return $memcache->rm($mem_key);
	}


	//##################配置管理结束##################

	//##################权限管理开始##################

	public function _oauth()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$account = $params['s_account'];
		$gid = intval($params['s_gid']);

		if ($account) {
			$user = Db::table('sys_user')->field(['id', 'gid'])->whereRaw('account=:account', ['account' => $account])->find();
			if (!$user) {
				ReturnToJson(-1, '不存在账号：' . $account);
			}
			$access = Db::table('sys_access')
				->field(['node_ids'])
				->where("uid={$user['id']} or gid={$user['gid']}")
				->select()
				->toArray();
		} else {
			if ($gid) {
				$access = Db::table('sys_access')
					->field(['node_ids'])
					->whereRaw('gid=:gid', ['gid' => $gid])
					->select()
					->toArray();
			} else {
				$access = [];
			}
		}

		$access_ids_arr = [];
		foreach ($access as $acv) {
			if (!$acv['node_ids']) {
				continue;
			}
			$tmp_node_ids = explode(',', $acv['node_ids']);
			foreach ($tmp_node_ids as $tv) {
				$i_tv = intval($tv);
				if ($i_tv) {
					$access_ids_arr[] = $i_tv;
				}
			}
		}
		if ($access_ids_arr) {
			$access_ids_arr = array_unique($access_ids_arr);
		}

		$node_arr = Db::table('sys_node')
			->field(['id', 'pid', 'nkey', 'name', 'type', 'public'])
			->orderRaw("CONCAT(pre_path,'-',id),pid asc,sort,id")
			->select()
			->toArray();

		$list_arr = [];
		$checked = [];
		foreach ($node_arr as $node) {
			if ($node['type']) {
				$node['type_flag'] = ' 【菜单】';
			} else {
				$node['type_flag'] = '';
			}
			if ($node['public']) {
				$node['public_flag'] = ' 【公共】';
			} else {
				$node['public_flag'] = '';
			}
			if (in_array($node['id'], $access_ids_arr)) {
				$node['oauth'] = 1;
				$checked[] = $node['id'];
			} else {
				$node['oauth'] = 0;
			}
			$tnode = [
				'id' => $node['id'],
				'label' => $node['name'] . $node['type_flag'] . $node['public_flag'],
				'oauth' => $node['oauth'],
				'type_flag' => $node['type_flag'],
				'public_flag' => $node['public_flag']
			];
			if (!$node['pid']) {
				$list_arr[$node['id']] = $tnode;
			} else {
				$list_arr[$node['pid']]['children'][] = $tnode;
			}
		}
		$list = [];
		foreach ($list_arr as $lv) {
			$list[] = $lv;
		}
		$data = [
			'list' => $list,
			'checked' => $checked,
			'sys_group' => getGroupsIdx()
		];
		ReturnToJson(1, 'ok', $data);
	}

	public function _oauth_update()
	{
		$pageuser = checkPower();
		if ($pageuser['id'] != 1 && $pageuser['id'] != 2) {
			ReturnToJson(-1, '没有权限操作');
		}
		$params = $this->params;
		$account = $params['account'];
		$gid = intval($params['gid']);
		$oauth = $params['oauth'];
		if (!$oauth) {
			$oauth = [];
		}
		$oauth_arr = [];
		foreach ($oauth as $nid) {
			$t_nid = intval($nid);
			if ($t_nid) {
				$oauth_arr[] = $t_nid;
			}
		}
		$node_ids = implode(',', $oauth_arr);
		$sys_access = ['node_ids' => $node_ids];

		try {
			$user = null;
			if ($account) {
				$user = Db::table('sys_user')->field(['id', 'gid'])->whereRaw('account=:account', ['account' => $account])->find();
				if (!$user) {
					ReturnToJson(-1, '不存在账号：' . $account);
				}
				$check_user = Db::table('sys_access')->field(['id'])->where("uid={$user['id']}")->find();
				if ($check_user) {
					$res = Db::table('sys_access')->where("id={$check_user['id']}")->update($sys_access);
				} else {
					$sys_access['uid'] = $user['id'];
					$res = Db::table('sys_access')->insert($sys_access);
				}
			} elseif ($gid) {
				$sys_group = getGroupsIdx();
				if (!array_key_exists($gid, $sys_group)) {
					ReturnToJson(-1, '不存在系统分组：' . $gid);
				}
				$check_group = Db::table('sys_access')->field(['id'])->whereRaw('gid=:gid', ['gid' => $gid])->find();
				if ($check_group) {
					$res = Db::table('sys_access')->where("id={$check_group['id']}")->update($sys_access);
				} else {
					$sys_access['gid'] = $gid;
					$res = Db::table('sys_access')->insert($sys_access);
				}
			} else {
				ReturnToJson(-1, '缺少授权对象，请选择分组或者账号进行授权');
			}
			if ($user) {
				$tag = 'usernodes_' . $user['id'];
				$this->redis->clear($tag);
			} else {
				$this->redis->clear(); //清理所有缓存
			}
			unset($this->redis);
			ReturnToJson(1, '操作成功');
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
	}


	//基本资料
	public function _profile()
	{
		$pageuser = checkLogin();
		$user = Db::table('sys_user')
			->field(['gid', 'account', 'phone', 'realname', 'nickname', 'headimgurl'])
			->where("id={$pageuser['id']}")->find();
		$sys_group = getGroupsIdx();
		$user['gname'] = $sys_group[$user['gid']];
		$return_data = [
			'user' => $user
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _profile_update()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		$password2_ck = getPassword($params['password2_ck']);
		if ($password2_ck != $user['password2']) {
			ReturnToJson(-1, '当前二级密码不正确');
		}
		$sys_user = [];
		if ($params['realname']) {
			$sys_user['realname'] = $params['realname'];
		}
		if ($params['nickname']) {
			$sys_user['nickname'] = $params['nickname'];
		} else {
			ReturnToJson(-1, '请填写昵称');
		}
		if ($params['headimgurl']) {
			$sys_user['headimgurl'] = $params['headimgurl'];
		} else {
			ReturnToJson(-1, '请上传头像');
		}
		try {
			Db::table('sys_user')->where("id={$user['id']}")->update($sys_user);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		flushUserinfo($pageuser['id']);
		ReturnToJson(1, '操作成功');
	}

	//安全设置
	public function _safety()
	{
		$pageuser = checkLogin();
		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		// $ga = new PHPGangsta_GoogleAuthenticator();
		// if (!$user['google_secret']) {
		// 	$secret = $ga->createSecret();
		// 	$sys_user = ['google_secret' => $secret];
		// 	Db::table('sys_user')->where("id={$user['id']}")->update($sys_user);
		// 	$user['google_secret'] = $secret;
		// }
		// $google_qrcode = $ga->getQRCodeGoogleUrl($user['account'], $user['google_secret']);
		unset($user['password'], $user['password2']);
		$return_data = [
			'user' => $user,
			// 'google_qrcode' => $google_qrcode
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _safety_update()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$sys_user = [];
		if ($params['phone']) {
			if (!isPhone($params['phone'])) {
				ReturnToJson(-1, '手机号格式不正确');
			}
			if ($params['phone'] == $pageuser['phone']) {
				//ReturnToJson(-1,'新手机号没有变化');
			}
			if (!$params['pcode']) {
				//ReturnToJson('-1','请填写短信验证码');
			}
			$user = Db::table('sys_user')->field(['id'])->whereRaw('phone=:phone', ['phone' => $params['phone']])->find();
			if ($user && $user['id'] != $pageuser['id']) {
				ReturnToJson(-1, '该手机号已被占用');
			}
			/*
									   $check_res=checkPhoneCode(['stype'=>$params['stype'],'phone'=>$params['phone'],'code'=>$params['pcode']]);
									   if($check_res['code']!='1'){
										   exit(json_encode($check_res));
									   }*/
			$sys_user['phone'] = $params['phone'];
		}

		$user = Db::table('sys_user')->where("id={$pageuser['id']}")->find();
		if ($params['password']) {
			$sys_user['password'] = getPassword($params['password']);
		}
		if ($params['password2']) {
			$sys_user['password2'] = getPassword($params['password2']);
		}

		$password2_ck = getPassword($params['password2_ck']);
		if ($password2_ck != $user['password2']) {
			ReturnToJson(-1, '当前二级密码不正确');
		}

		// $sys_user['is_google'] = intval($params['is_google']);
		$white_ip = str_replace('，', ',', trim($params['white_ip']));
		$white_ip_arr = explode(',', $white_ip);
		$ip_arr = [];
		foreach ($white_ip_arr as $ip) {
			$ip = trim($ip);
			if ($ip) {
				$ip_arr[] = $ip;
			}
		}
		$sys_user['white_ip'] = implode(',', array_unique($ip_arr));
		if (!$sys_user) {
			ReturnToJson(-1, '没有任何修改');
		}
		try {
			Db::table('sys_user')->where("id={$pageuser['id']}")->update($sys_user);
		} catch (\Exception $e) {
			ReturnToJson('-1', '系统繁忙请稍后再试');
		}
		flushUserinfo($pageuser['id']);
		//踢所有端下线
		//kickUser($pageuser['id']);
		ReturnToJson('1', '操作成功');
	}

	//隐藏谷歌配置信息
	public function _google_hide()
	{
		// $pageuser = checkLogin();
		// $sys_user = ['google_hide' => 1];
		// try {
		// 	Db::table('sys_user')->where("id={$pageuser['id']}")->update($sys_user);
		// } catch (\Exception $e) {
		// 	ReturnToJson('-1', '系统繁忙请稍后再试');
		// }
		ReturnToJson('1', '操作成功');
	}

	//##################语言翻译开始##################
	public function _trans()
	{
		checkPower();
		$params = $this->params;
		$where = '1=1';
		$where .= empty($params['s_keyword']) ? '' : " and (log.cn like '%{$params['s_keyword']}%')";

		$count_item = Db::table('sys_lang log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['sys_lang' => 'log'], ['*'])
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i', $item['create_time']);
		}
		$data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		ReturnToJson(1, 'ok', $data);
	}

	public function _trans_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		if (!$params['cn']) {
			ReturnToJson(-1, '请填写简体中文');
		}
		/*
						  if(!$params['tw']){
							  ReturnToJson(-1,'请填写繁体中文');
						  }
						  if(!$params['en']){
							  ReturnToJson(-1,'请填写英文');
						  }*/
		$sys_lang = [
			'cn' => $params['cn'],
			'tw' => $params['tw'],
			'en' => $params['en'],
			'es' => $params['es'],
			'update_time' => NOW_TIME
		];
		try {
			if ($item_id) {
				$res = Db::table('sys_lang')->whereRaw('id=:id', ['id' => $item_id])->update($sys_lang);
			} else {
				$sys_lang['create_time'] = NOW_TIME;
				$res = Db::table('sys_lang')->insert($sys_lang);
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$this->genLangFile();
		ReturnToJson(1, '操作成功');
	}

	public function _trans_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$item = Db::table('sys_lang')->whereRaw('id=:id', ['id' => $item_id])->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		try {
			Db::table('sys_lang')->where("id={$item['id']}")->delete();
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$this->genLangFile();
		actionLog(['opt_name' => '删除翻译', 'sql_str' => json_encode($item)]);
		ReturnToJson(1, '操作成功');
	}

	private function genLangFile()
	{
		$lang_arr = Db::table('sys_lang')->limit(1000)->select()->toArray();
		$tw = [];
		$en = [];
		$es = [];
		foreach ($lang_arr as $lv) {
			if ($lv['tw']) {
				$tw[$lv['cn']] = $lv['tw'];
			}
			if ($lv['en']) {
				$en[$lv['cn']] = $lv['en'];
			}
			if ($lv['es']) {
				$es[$lv['cn']] = $lv['es'];
			}
		}
		file_put_contents(ROOT_PATH . 'public/lang/zh-tw.php', "<?php\nreturn " . var_export($tw, true) . ";\n?>");
		file_put_contents(ROOT_PATH . 'public/lang/en-us.php', "<?php\nreturn " . var_export($en, true) . ";\n?>");
		file_put_contents(ROOT_PATH . 'public/lang/sp-es.php', "<?php\nreturn " . var_export($es, true) . ";\n?>");
		return true;
	}
}
