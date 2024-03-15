<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class ProductController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//分类
	public function _category()
	{
		checkPower();
		$list = Db::table('pro_category')->where("status<99")->order(['sort' => 'desc'])->select()->toArray();
		$tree = list2tree($list);
		$return_data = [
			'list' => $tree
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _category_update()
	{
		checkPower();
		$params = $this->params;
		$params['id'] = intval($params['id']);
		$params['pid'] = intval($params['pid']);
		$params['sort'] = intval($params['sort']);
		$params['status'] = intval($params['status']);
		if (!$params['name']) {
			ReturnToJson(-1, '请填写分类名称');
		}
		if ($params['pid']) {
			$pitem = Db::table('pro_category')->where("id={$params['pid']} and status<99")->find();
			if (!$pitem) {
				ReturnToJson(-1, '不存在相应的父级分类');
			}
		}
		$pro_category = [
			'pid' => $params['pid'],
			'sort' => $params['sort'],
			'status' => $params['status'],
			'name' => $_POST['name'],
			'cover' => $params['cover'],
			'remark' => $params['remark'],
			'update_time' => NOW_TIME
		];
		try {
			if ($params['id']) {
				$item = Db::table('pro_category')->where("id={$params['id']} and status<99")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				Db::table('pro_category')->where("id={$item['id']}")->update($pro_category);
			} else {
				$pro_category['create_time'] = NOW_TIME;
				Db::table('pro_category')->insertGetId($pro_category);
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$this->redis->rm(RedisKeys::Goods . "category");
		ReturnToJson(1, '操作成功');
	}

	public function _category_delete()
	{
		checkPower();
		$params = $this->params;
		$params['id'] = intval($params['id']);
		$item = Db::table('pro_category')->where("id={$params['id']}")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已被删除');
		}
		$pro_category = ['status' => 99];
		$list = Db::table('pro_category')->where("status<99")->order(['sort' => 'desc'])->select()->toArray();
		$ids = getTreeIds($list, $item['id']);
		$ids_str = implode(',', $ids);
		try {
			Db::table('pro_category')->where("id in ({$ids_str})")->update($pro_category);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$this->redis->rm(RedisKeys::Goods . "category");
		ReturnToJson(1, '操作成功');
	}

	/////////////////////////////////////////////////////////
	//产品管理
	public function _goods()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$where = "log.status<99";
		$where .= empty($params['s_cid']) ? '' : " and log.cid={$params['s_cid']}";
		$where .= empty($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		if (is_numeric($params['s_is_hot'])) {
			$params['s_is_hot'] = intval($params['s_is_hot']);
			$where .= " and log.is_hot={$params['s_is_hot']}";
		}
		$where .= empty($params['s_keyword']) ? '' : " and (log.name like '%{$params['s_keyword']}%')";
		$count_item = Db::table('pro_goods log')
			->leftJoin('pro_category cat', 'log.cid=cat.id')
			->fieldRaw('count(1) as cnt')->where($where)->find();
		$list = Db::view(['pro_goods' => 'log'], ['*'])
			->view(['pro_category' => 'cat'], ['name' => 'category_name'], 'log.cid=cat.id', 'LEFT')
			->where($where)
			->order(['log.sort' => 'desc', 'log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$yes_or_no = getConfig('yes_or_no');
		$cnf_product_status = getConfig('cnf_product_status');
		foreach ($list as &$item) {
			$item['status_flag'] = $cnf_product_status[$item['status']];
			$item['goodsindex_flag'] = $yes_or_no[$item['goodsindex']];
			$item['goodsindex_switch'] = $item['goodsindex'] ? true : false;
			$item['is_hot_flag'] = $yes_or_no[$item['is_hot']];
			$item['is_xskc_flag'] = $yes_or_no[$item['is_xskc']];
			$item['is_hot_switch'] = $item['is_hot'] ? true : false;
			$item['covers'] = json_decode($item['covers'], true);
			$item['djs'] = date('Y-m-d H:i:s', $item['djs']);
			$item['dssj'] = date('Y-m-d H:i:s', $item['dssj']);
			if (!$item['covers']) {
				$item['covers'] = [];
			}
		}
		$return_data = [
			'list' => $list,
			'giftgoods' => Db::table('pro_goods')->where("cid=1031")->field(['id', 'name'])->select()->toArray(),
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$category_list = Db::table('pro_category log')
				->field(['id', 'pid', 'name'])
				->where('status<99')
				->orderRaw("log.sort desc")
				->select()->toArray();
			$category_tree = list2Select($category_list);
			$return_data['category_tree'] = $category_tree;
			$return_data['status_arr'] = $cnf_product_status;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _goods_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['cid'] = intval($params['cid']);
		$params['sort'] = intval($params['sort']);
		$params['is_hot'] = intval($params['is_hot']);
		$params['is_xskc'] = intval($params['is_xskc']);
		$params['kc'] = intval($params['kc']);
		$params['days'] = intval($params['days']);
		$params['rate'] = floatval($params['rate']);
		$params['scale'] = floatval($params['scale']);
		$params['price'] = floatval($params['price']);
		$params['price1'] = floatval($params['price1']);
		$params['price2'] = floatval($params['price2']);
		$params['invest_min'] = floatval($params['invest_min']);
		$params['invest_limit'] = intval($params['invest_limit']);
		$params['v_invested'] = floatval($params['v_invested']);

		$params['gifttopuser'] = intval($params['gifttopuser']);
		$params['gifttoself'] = intval($params['gifttoself']);


		if (!$params['name']) {
			ReturnToJson(-1, '请填写名称');
		}
		$category = [];
		if (!$params['cid']) {
			ReturnToJson(-1, '请选择分类');
		} else {
			$category = Db::table('pro_category')->where("id={$params['cid']} and status<99")->find();
			if (!$category) {
				ReturnToJson(-1, '不存在相应分类');
			}
		}
		if ($params['scale'] < 0) {
			ReturnToJson(-1, '项目金额不正确');
		}
		if ($params['price'] < 0) {
			ReturnToJson(-1, '产品单价不正确');
		}
		if ($params['price1'] < 0) {
			ReturnToJson(-1, '首购送自己单价不正确');
		}
		if ($params['price0'] < 0) {
			ReturnToJson(-1, '复购送自己单价不正确');
		}
		if ($params['price2'] < 0) {
			ReturnToJson(-1, '送上级单价不正确');
		}
		if ($params['days'] < 1) {
			ReturnToJson(-1, '产品期限不正确');
		}
		if ($params['rate'] < 0 || $params['rate'] > 1000) {
			ReturnToJson(-1, '收益率不正确');
		}
		if ($params['invest_min'] < 0) {
			ReturnToJson(-1, '起投金额不正确');
		}
		if ($params['invest_limit'] < 0) {
			ReturnToJson(-1, '限购数量不正确');
		}
		if (!$params['icon']) {
			ReturnToJson(-1, '请上传图标');
		}
		$covers = [];
		if (!$params['covers']) {
			$params['covers'] = [];
		}
		if ($params['djs']) {
			$params['djs'] = strtotime($params['djs']);
		}
		if ($params['dssj']) {
			$params['dssj'] = strtotime($params['dssj']);
		}
		foreach ($params['covers'] as $cv) {
			if (!$cv) {
				continue;
			}
			$covers[] = $cv;
		}
		$db_data = [
			'cid' => $params['cid'],
			'name' => $params['name'],
			'days' => $params['days'],
			'rate' => $params['rate'],
			'scale' => $params['scale'],
			'price' => $params['price'],
			'price0' => $params['price0'],
			'price1' => $params['price1'],
			'cjcs' => $params['cjcs'],
			'sjcjcs' => $params['sjcjcs'],
			'price2' => $params['price2'],
			'invest_limit' => $params['invest_limit'],
			'invest_min' => $params['invest_min'],
			'v_invested' => $params['v_invested'],
			'status' => $params['status'],
			'guarantors' => $params['guarantors'],
			'icon' => $params['icon'],
			'covers' => json_encode($covers, 256),
			'content' => $_POST['content'],
			'sort' => $params['sort'] ? $params['sort'] : 1000,
			'is_hot' => $params['is_hot'],
			'buyday' => $params['buyday'],
			'dayout' => $params['dayout'],
			'is_xskc' => $params['is_xskc'],
			'kc' => $params['kc'],
			'yaoqing' => $params['yaoqing'],
			'Integral' => $params['Integral'],
			'selfintegral' => $params['selfintegral'],
			'selfbg' => $params['selfbg'],
			'djs' => $params['djs'],
			'dssj' => $params['dssj'],
			'gifttopuser' => $params['gifttopuser'],
			'gifttoself' => $params['gifttoself'],
			'gift' => $params['gift'],
			'pointshop' => $params['pointshop'],

		];
		try {
			$model = Db::table('pro_goods');
			if ($item_id) {
				$item = $model->where("id={$item_id} and status<99")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				if ($item['invested'] > $params['scale']) {
					//ReturnToJson(-1,'项目金额不能小于已投资金额');
				}
				$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
				$db_data['id'] = $item_id;
				$rediskey_goods = RedisKeys::Goods . $params['gsn'];
				$item = $this->redis->rm($rediskey_goods);
			} else {
				$db_data['create_time'] = NOW_TIME;
				$db_data['gsn'] = getRsn();
				$res = $model->insertGetId($db_data);
				$db_data['id'] = $res;
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		actionLog(['opt_name' => '更新产品', 'sql_str' => json_encode($db_data)]);
		$yes_or_no = getConfig('yes_or_no');
		$cnf_product_status = getConfig('cnf_product_status');
		$return_data = [
			'is_hot_flag' => $yes_or_no[$db_data['is_hot']],
			'is_xskc_flag' => $yes_or_no[$db_data['is_xskc']],
			'status_flag' => $cnf_product_status[$db_data['status']],
			'category_name' => $category['name']
		];
		$this->redis->rmall(RedisKeys::Goods);
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _goods_delete()
	{
		checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('pro_goods');
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
		$this->redis->rmall(RedisKeys::Goods);
		ReturnToJson(1, '操作成功');
	}

	////////////////////////////////////////////////////////
	//订单列表	
	public function _order()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_cid'] = intval($params['s_cid']);
		$params['s_gid'] = intval($params['s_gid']);


		$where = "log.status<99";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}
		$where .= empty($params['s_osn']) ? '' : " and log.osn='{$params['s_osn']}'";

		if ($params['s_user']) {
			$s_user = Db::table('sys_user')->where("id='{$params['s_user']}' or account='{$params['s_user']}' or email='{$params['s_user']}' or realname='{$params['s_user']}' or nickname='{$params['s_user']}'")->find();
			if ($s_user) {
				$where .= " and log.uid={$s_user['id']}";
			} else {
				$where .= " and log.uid=0";
			}
		}

		if ($params['s_puser']) {
			$s_keyword = $params['s_puser'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword}' or account='{$s_keyword}' or email='{$s_keyword}' or realname='{$s_keyword}' or nickname='{$s_keyword}'")->find();
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

		$where .= empty($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		if ($params['s_is_give'] == '1') {
			$params['s_is_give'] = intval($params['s_is_give']);
			$where .= " and log.is_give={$params['s_is_give']}";
		} else {
			$where .= empty($params['s_cid']) ? '' : " and log.cid={$params['s_cid']}";
		}

		$where .= empty($params['s_gid']) ? '' : " and log.gid={$params['s_gid']}";



		$where .= empty($params['s_keyword']) ? '' : " and (log.osn='{$params['s_keyword']}' or u.account='{$params['s_keyword']}')";


		if ($params['t1'] == '1') {
			$where .= ' and log.p3=1';
		}
		$count_item = Db::table('pro_order log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('pro_goods g', 'log.gid=g.id')
			->leftJoin('pro_category cat', 'log.cid=cat.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money,sum(log.total_reward) as total_reward')
			->where($where)
			->find();

		$list = Db::view(['pro_order' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'realname', 'phone', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->view(['pro_goods' => 'g'], ['name' => 'goods_name'], 'log.gid=g.id', 'LEFT')
			->view(['sys_user' => 'u1'], ['account' => 'paccount'], 'u.pid=u1.id', 'LEFT')
			->view(['pro_category' => 'cat'], ['name' => 'category_name'], 'log.cid=cat.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$cnf_product_order_status = getConfig('cnf_product_order_status');
		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			$item['status_flag'] = $cnf_product_order_status[$item['status']];
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => number_format($count_item['money'], 2, '.', ''),
			'total_reward' => number_format($count_item['total_reward'], 2, '.', ''),
			'limit' => $this->pageSize,
			'$where' => $where,
		];
		if ($params['page'] < 2) {
			$category_list = Db::table('pro_category log')
				->field(['id', 'pid', 'name'])
				->where('status<99')
				->orderRaw("log.sort desc")
				->select()->toArray();
			$category_tree = list2Select($category_list);
			$return_data['category_tree'] = $category_tree;
			$goods_where = '';
			$goods_arr = [];
			if ($params['s_cid']) {

				$goods_where .= "id in (111)";

				$goods_arr = Db::table('pro_goods')->where($goods_where)->field(['id', 'name', 'price'])->select()->toArray();
				if (!$goods_arr) {
					$goods_arr = [];
				}
			}
			$return_data['goods_arr'] = $goods_arr;
			$return_data['status_arr'] = $cnf_product_order_status;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//订单更新
	public function _order_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['gid'] = intval($params['gid']);
		$params['days'] = intval($params['days']);
		$params['num'] = intval($params['num']);
		if (!$params['account']) {
			ReturnToJson(-1, '请填写用户账号');
		}
		$user = Db::table('sys_user')->where("account='{$params['account']}'")->find();
		if (!$user) {
			ReturnToJson(-1, '不存在相应的账号');
		}
		if (!checkDataAction()) {
			$down_arr = getDownUser($pageuser['id'], false, $pageuser); // getDownUser($pageuser['id'], false, $pageuser);
			if (!in_array($user['id'], $down_arr)) {
				ReturnToJson(-1, '没有该账号的操作权限');
			}
		}
		if (!$params['gid']) {
			ReturnToJson(-1, '请选择赠送设备');
		}
		$give_item = Db::table('pro_goods')->where("id={$params['gid']}")->find();
		// if (!$give_item || $give_item['status'] != 3) {
		// 	ReturnToJson(-1, '该设备已下线');
		// }
		if ($params['num'] < 1) {
			ReturnToJson(-1, '赠送数量不正确');
		}
		if ($params['days'] < 1) {
			ReturnToJson(-1, '收益天数不正确');
		}
		$db_data = [
			'days' => $params['days'],
			'num' => $params['num'],
			'money' => $give_item['price'] * $params['num']
		];
		try {
			$model = Db::table('pro_order');
			if ($item_id) {
				$item = $model->where("id={$item_id} and status<99")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				if ($item['status'] != 1) {
					ReturnToJson(-1, '当前状态不可操作');
				}
				$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
				$db_data['id'] = $item_id;
			} else {
				$db_data['cid'] = $give_item['cid'];
				$db_data['gid'] = $give_item['id'];
				$db_data['rate'] = $give_item['rate'];
				$db_data['price'] = $give_item['price'];
				$db_data['uid'] = $user['id'];
				$db_data['osn'] = getRsn();
				$db_data['create_id'] = $pageuser['id'];
				$db_data['create_ip'] = CLIENT_IP;
				$db_data['create_time'] = NOW_TIME;
				$db_data['create_day'] = date('Ymd', NOW_TIME);
				$db_data['discount'] = 1;
				$db_data['w1_money'] = 0;
				$db_data['w2_money'] = 0;
				$db_data['is_exchange'] = 1;
				$db_data['pid'] = 0;
				$db_data['is_give'] = '1'; //in_array($give_item['id'], ['1']) ? '0' : '1'
				$res = $model->insertGetId($db_data);
				$db_data['id'] = $res;
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试', $e);
		}

		$return_data = [
			'num' => $db_data['num'],
			'money' => $db_data['money'],
			'days' => $db_data['days']
		];
		$this->redis->rmall(RedisKeys::USER_ORDER . $user['id']);
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _order_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('pro_order');
		$item = $model->where("id={$item_id} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		if (!$item['is_give']) {
			ReturnToJson(-1, '非赠送设备不可删除');
		}
		if (!checkDataAction()) {
			$down_arr = getDownUser($pageuser['id'], false, $pageuser);
			if (!in_array($item['uid'], $down_arr)) {
				ReturnToJson(-1, '没有该账号的操作权限');
			}
		}
		$db_data = ['status' => 99];
		try {
			$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$this->redis->rmall(RedisKeys::USER_ORDER . $item['uid']);
		ReturnToJson(1, '操作成功');
	}

	//订单设置
	public function _order_set()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$status = intval($params['status']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		if (!in_array($status, [1, 3])) {
			ReturnToJson(-1, '未知状态');
		}
		//注意锁，和事务同步，不然容易死锁
		$item = Db::table('pro_order')->where("id={$item_id}")->field('uid')->find();
		Db::startTrans();
		try {
			$item = Db::table('pro_order')->where("id={$item_id}")->lock(true)->find();
			if (!$item || $item['status'] > 99) {
				ReturnToJson(-1, '不存在相应的订单');
			}
			if (!checkDataAction()) {
				$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
				if (!in_array($item['uid'], $uid_arr)) {
					ReturnToJson(-1, '不存在相应的订单.');
				}
			}
			$pro_order = ['status' => $status];
			Db::table('pro_order')->where("id={$item['id']}")->update($pro_order);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		actionLog(['opt_name' => '设置产品订单', 'sql_str' => Db::getLastSql()]);
		$cnf_product_order_status = getConfig('cnf_product_order_status');
		$return_data = [
			'status' => $status,
			'status_flag' => $cnf_product_order_status[$status]
		];
		$this->redis->rmall(RedisKeys::USER_ORDER . $item['uid']);
		ReturnToJson(1, '操作成功', $return_data);
	}
	//首购赠送审核
	public function _order_set1()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$status = intval($params['status']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$this->Uporder_set($item_id, $pageuser, $status);
		$cnf_product_order_status = getConfig('cnf_product_order_status');
		$return_data = [
			'status' => $status,
			'status_flag' => $cnf_product_order_status[$status]
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function Uporder_set($item_id, $pageuser, $status)
	{
		Db::startTrans();
		$now_time = NOW_TIME;
		$now_day = date('Ymd', NOW_TIME);
		try {
			$item = Db::table('pro_order')->where("id={$item_id}")->lock(true)->find();
			if (!$item || $item['status'] > 99) {
				ReturnToJson(-1, '不存在相应的订单');
			}

			if (!checkDataAction()) {
				$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
				if (!in_array($item['uid'], $uid_arr)) {
					ReturnToJson(-1, '不存在相应的订单.');
				}
			}
			$pro_order = [
				'p1' => $status
			];
			Db::table('pro_order')->where("id={$item['id']}")->update($pro_order);

			if ($status == 1) {
				$wallet2 = getWallet($item['uid'], 2); //余额钱包
				if (!$wallet2) {
					throw new \Exception('钱包获取异常');
				}
				$wallet2 = Db::table('wallet_list')->where("id={$wallet2['id']}")->lock(true)->find();
				$wallet_data2 = [
					'balance' => ($wallet2['balance']) + ($item['price1'])
				];
				//更新钱包余额
				Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
				//写入流水记录
				$result2 = walletLog([
					'wid' => $wallet2['id'],
					'uid' => $wallet2['uid'],
					'type' => 10,
					'money' => $item['price1'],
					'ori_balance' => $wallet2['balance'],
					'new_balance' => $wallet_data2['balance'],
					'fkey' => $item['id'],
					'remark' => 'First buy:' . $pro_order['osn']
				]);
				if (!$result2) {
					throw new \Exception('流水记录写入失败');
				}
				//写入收益记录
				$pro_reward2 = [
					'uid' => $item['uid'],
					'oid' => $item['id'],
					'osn' => $item['osn'],
					'type' => 2,
					'level' => 0,
					'base_money' => 0,
					'rate' => 0,
					'money' => $item['price1'],
					'remark' => $item['osn'],
					'create_time' => $now_time,
					'create_day' => $now_day
				];
				Db::table('pro_reward')->insertGetId($pro_reward2);

				//上级分佣 
				$user = Db::table('sys_user')->where("id={$item['uid']}")->find();
				$puser = Db::table('sys_user')->where("id={$user['pid']}")->find();
				//返佣

				if ($puser['stop_commission']) { //暂停佣金

				} else {
					if ($puser['gid'] < 91) { //代理以及其它管理用户不给佣金

					} else {

						$wallet2 = getWallet($puser['id'], 2);
						if (!$wallet2) {
							throw new \Exception('钱包获取异常');
						}
						$wallet2 = Db::table('wallet_list')->where("id={$wallet2['id']}")->lock(true)->find();
						$wallet_data2 = [
							'balance' => $wallet2['balance'] + $item['price2']
						];
						Db::table('wallet_list')->where("id={$wallet2['id']}")->update($wallet_data2);
						//写入流水记录
						$result2 = walletLog([
							'wid' => $wallet2['id'],
							'uid' => $wallet2['uid'],
							'type' => 8,
							'money' => $item['price2'],
							'ori_balance' => $wallet2['balance'],
							'new_balance' => $wallet_data2['balance'],
							'fkey' => $item['id'],
							'remark' => 'Commission:' . $item['osn']
						]);
						if (!$result2) {
							throw new \Exception('流水记录写入失败');
						}
						//写入收益记录
						$pro_reward2 = [
							'uid' => $puser['id'],
							'oid' => $item['id'],
							'osn' => $item['osn'],
							'type' => 2,
							'level' => 0,
							'base_money' => 0,
							'rate' => 0,
							'money' => $item['price2'],
							'remark' => $item['osn'],
							'create_time' => $now_time,
							'create_day' => $now_day
						];
						Db::table('pro_reward')->insertGetId($pro_reward2);
						$this->redis->rmall(RedisKeys::USER_WALLET . $puser['id']);
					}
				}
			}


			Db::commit();
			$this->redis->rmall(RedisKeys::USER_WALLET . $item['uid']);
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		actionLog(['opt_name' => '首购赠送审核', 'sql_str' => Db::getLastSql()]);
	}

	//一键审核
	public function _order_check_all()
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
		if (!in_array($params['status'], [1, 2])) {
			ReturnToJson(-1, '未知审核状态');
		}
		foreach ($ids as $item_id) {
			$this->Uporder_set($item_id, $pageuser, $params['status']);
		}
		ReturnToJson(1, '操作成功');
	}
	//////////////////////////////////////////////////////////
	//收益记录	
	public function _reward()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_cid'] = intval($params['s_cid']);
		$params['s_gid'] = intval($params['s_gid']);
		$params['s_type'] = intval($params['s_type']);

		$where = "1=1";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}
		$where .= empty($params['s_osn']) ? '' : " and log.osn='{$params['s_osn']}'";
		$where .= empty($params['s_type']) ? '' : " and log.type={$params['s_type']}";
		$where .= empty($params['s_cid']) ? '' : " and od.cid={$params['s_cid']}";
		$where .= empty($params['s_gid']) ? '' : " and od.gid={$params['s_gid']}";

		if ($params['s_user']) {
			$s_user = Db::table('sys_user')->where("id='{$params['s_user']}' or account='{$params['s_user']}' or email='{$params['s_user']}' or realname='{$params['s_user']}' or nickname='{$params['s_user']}'")->find();
			if ($s_user) {
				$where .= " and log.uid={$s_user['id']}";
			} else {
				$where .= " and log.uid=0";
			}
		}

		if ($params['s_puser']) {
			$s_keyword = $params['s_puser'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword}' or account='{$s_keyword}' or email='{$s_keyword}' or realname='{$s_keyword}' or nickname='{$s_keyword}'")->find();
			if ($s_puser) {
				$uid_arr = getDownUser_new($s_puser['id']); //  getDownUser_new($s_puser['id']);
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

		$where .= empty($params['s_keyword']) ? '' : " and (log.osn='{$params['s_keyword']}' or u.account='{$params['s_keyword']}')";

		$count_item = Db::table('pro_reward log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('pro_order od', 'log.oid=od.id')
			->leftJoin('pro_goods g', 'od.gid=g.id')
			->leftJoin('pro_category cat', 'od.cid=cat.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();

		$list = Db::view(['pro_reward' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'realname', 'phone', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->view(['pro_order' => 'od'], [], 'log.oid=od.id', 'LEFT')
			->view(['pro_goods' => 'g'], ['name' => 'goods_name'], 'od.gid=g.id', 'LEFT')
			->view(['pro_category' => 'cat'], ['name' => 'category_name'], 'od.cid=cat.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$cnf_reward_type = getConfig('cnf_reward_type');
		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			$item['type_flag'] = $cnf_reward_type[$item['type']];
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => number_format($count_item['money'], 2, '.', ''),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$category_list = Db::table('pro_category log')
				->field(['id', 'pid', 'name'])
				->where('status<99')
				->orderRaw("log.sort desc")
				->select()->toArray();
			$category_tree = list2Select($category_list);
			$return_data['category_tree'] = $category_tree;
			$goods_where = '1=1';
			$goods_arr = [];
			if ($params['s_cid']) {
				$goods_where .= " and cid={$params['s_cid']}";
				$goods_arr = Db::table('pro_goods')->where($goods_where)->field(['id', 'name'])->select()->toArray();
				if (!$goods_arr) {
					$goods_arr = [];
				}
			}
			$return_data['goods_arr'] = $goods_arr;
			$return_data['type_arr'] = $cnf_reward_type;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _rebate()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_cid'] = intval($params['s_cid']);
		$params['s_gid'] = intval($params['s_gid']);
		$params['s_type'] = 2;

		$where = " log.type=2 ";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}

		$where .= empty($params['s_osn']) ? '' : " and log.osn='{$params['s_osn']}'";
		$where .= empty($params['s_cid']) ? '' : " and od.cid={$params['s_cid']}";
		$where .= empty($params['s_gid']) ? '' : " and od.gid={$params['s_gid']}";

		if ($params['s_user']) {
			$s_user = Db::table('sys_user')->where("id='{$params['s_user']}' or account='{$params['s_user']}' or email='{$params['s_user']}' or realname='{$params['s_user']}' or nickname='{$params['s_user']}'")->find();
			if ($s_user) {
				$where .= " and log.uid={$s_user['id']}";
			} else {
				$where .= " and log.uid=0";
			}
		}


		if ($params['s_user1']) {
			$s_user = Db::table('sys_user')->where("id='{$params['s_user1']}' or account='{$params['s_user1']}' or email='{$params['s_user1']}' or realname='{$params['s_user1']}' or nickname='{$params['s_user1']}'")->find();
			if ($s_user) {
				$where .= " and od.uid={$s_user['id']}";
			} else {
				$where .= " and od.uid=0";
			}
		}

		if ($params['s_puser']) {
			$s_keyword = $params['s_puser'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword}' or account='{$s_keyword}' or email='{$s_keyword}' or realname='{$s_keyword}' or nickname='{$s_keyword}'")->find();
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

		$where .= empty($params['s_keyword']) ? '' : " and (log.osn='{$params['s_keyword']}' or u.account='{$params['s_keyword']}')";

		$count_item = Db::table('pro_reward log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('pro_order od', 'log.oid=od.id')
			->leftJoin('pro_goods g', 'od.gid=g.id')
			->leftJoin('pro_category cat', 'od.cid=cat.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();
		//select * from pro_reward as log LEFT join sys_user as u on log.uid=u.id LEFT join  pro_order as od on log.oid=od.id
		$list = Db::view(['pro_reward' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'realname', 'phone', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->view(['pro_order' => 'od'], ['uid'], 'log.oid=od.id', 'LEFT')
			->view(['sys_user' => 'u2'], ['account' => 'u2account', 'nickname' => 'u2nickname'], 'od.uid=u2.id', 'LEFT')

			->view(['pro_goods' => 'g'], ['name' => 'goods_name'], 'od.gid=g.id', 'LEFT')
			->view(['pro_category' => 'cat'], ['name' => 'category_name'], 'od.cid=cat.id', 'LEFT')

			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$cnf_reward_type = getConfig('cnf_reward_type');
		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			$item['type_flag'] = $cnf_reward_type[$item['type']];
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => number_format($count_item['money'], 2, '.', ''),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$category_list = Db::table('pro_category log')
				->field(['id', 'pid', 'name'])
				->where('status<99')
				->orderRaw("log.sort desc")
				->select()->toArray();
			$category_tree = list2Select($category_list);
			$return_data['category_tree'] = $category_tree;
			$goods_where = '1=1';
			$goods_arr = [];
			if ($params['s_cid']) {
				$goods_where .= " and cid={$params['s_cid']}";
				$goods_arr = Db::table('pro_goods')->where($goods_where)->field(['id', 'name'])->select()->toArray();
				if (!$goods_arr) {
					$goods_arr = [];
				}
			}
			$return_data['goods_arr'] = $goods_arr;
			$return_data['type_arr'] = $cnf_reward_type;
		}
		ReturnToJson(1, 'ok', $return_data);
	}
	public function _getGoodsByCid()
	{
		$pageuser = checkLogin();
		$cid = intval($this->params['cid']);
		$list = Db::table('pro_goods')->where("cid={$cid}")->field(['id', 'name'])->select()->toArray();
		if (!$list) {
			$list = [];
		}
		$return_data = [
			'list' => $list
		];
		ReturnToJson(1, 'ok', $return_data);
	}


	//赠送管理
	public function _guser()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$where = "log.status<99";

		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}

		$where .= empty($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		$where .= empty($params['s_keyword']) ? '' : " and (u.account='{$params['s_keyword']}' or u.nickname like '%{$params['s_keyword']}%')";
		$count_item = Db::table('pro_guser log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('pro_goods gs', 'log.gid=gs.id')
			->fieldRaw('count(1) as cnt')->where($where)->find();
		$list = Db::view(['pro_guser' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname'], 'log.uid=u.id', 'LEFT')
			->view(['pro_goods' => 'gs'], ['name' => 'goods_name', 'price'], 'log.gid=gs.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
		}
		$return_data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['goods_arr'] = Db::table('pro_goods')->where("status=3")->field(['id', 'name', 'price'])->select()->toArray();
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _guser_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['gid'] = intval($params['gid']);
		$params['days'] = intval($params['days']);
		$params['num'] = intval($params['num']);
		if (!$params['account']) {
			ReturnToJson(-1, '请填写用户账号');
		}
		$user = Db::table('sys_user')->where("account='{$params['account']}'")->find();
		if (!$user) {
			ReturnToJson(-1, '不存在相应的账号');
		}
		$check_guser = Db::table('pro_guser')->where("uid={$user['id']} and status<99")->find();
		if ($check_guser) {
			if ($item_id && $check_guser['id'] != $item_id) {
				ReturnToJson(-1, '该用户已存在记录，请进行编辑');
			}
		}
		if (!checkDataAction()) {
			$down_arr = getDownUser($pageuser['id'], false, $pageuser);
			if (!in_array($user['id'], $down_arr)) {
				ReturnToJson(-1, '没有该账号的操作权限');
			}
		}
		if (!$params['gid']) {
			ReturnToJson(-1, '请选择赠送设备');
		}
		$goods = Db::table('pro_goods')->where("id={$params['gid']}")->find();
		if (!$goods || $goods['status'] != 3) {
			ReturnToJson(-1, '该设备已下线');
		}
		if ($params['num'] < 1) {
			ReturnToJson(-1, '赠送数量不正确');
		}
		if ($params['days'] < 1) {
			ReturnToJson(-1, '收益天数不正确');
		}
		$db_data = [
			'uid' => $user['id'],
			'gid' => $params['gid'],
			'days' => $params['days'],
			'num' => $params['num']
		];
		try {
			$model = Db::table('pro_guser');
			if ($item_id) {
				$item = $model->where("id={$item_id} and status<99")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
				$db_data['id'] = $item_id;
			} else {
				$db_data['create_id'] = $pageuser['id'];
				$db_data['create_time'] = NOW_TIME;
				$res = $model->insertGetId($db_data);
				$db_data['id'] = $res;
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$cnf_redpack_status = getConfig('cnf_redpack_status');
		$return_data = [
			'account' => $user['account'],
			'nickname' => $user['nickname'],
			'goods_name' => $goods['name'],
			'price' => $goods['price']
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _guser_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('pro_guser');
		$item = $model->where("id={$item_id} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		if (!checkDataAction()) {
			$down_arr = getDownUser($pageuser['id'], false, $pageuser);
			if (!in_array($item['uid'], $down_arr)) {
				ReturnToJson(-1, '没有该账号的操作权限');
			}
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