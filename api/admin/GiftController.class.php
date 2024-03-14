<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class GiftController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}


	public function _addyxyh()
	{
		$pageuser = checkPower('Gift_couponLog_update1');
		$params = $this->params;
		$count = 0;
		if (intval($params['num']) <= 0) {
			ReturnToJson(-1, '赠送的次数需要大于0');
		}
		try {
			$count = Db::table('sys_user')->where('first_pay_day >0')->inc('lottery', intval($params['num']))->update();
		} catch (\Throwable $th) {
			ReturnToJson(-1, '操作失败', $th);
		}
		ReturnToJson(1, '操作成功 更新用户数：' . $count);
	}
	public function _addyxyhbyuser()
	{
		$pageuser = checkPower('Gift_couponLog_update1');
		$params = $this->params;
		$count = 0;
		if (intval($params['num']) <= 0) {
			ReturnToJson(-1, '赠送的次数需要大于0');
		}
		if ($params['user'] == '' || $params['user'] == null) {
			ReturnToJson(-1, '用户不能为空');
		}
		try {
			$user = Db::table('sys_user')->where(" openid='" . $params['user'] . "'")->find();
			if (!$user) ReturnToJson(-1, '用户不存在');
			$update['lottery'] = $user['lottery'] + intval($params['num']);
			$count = Db::table('sys_user')->where(" id=" . $user['id'])->update($update);
		} catch (\Throwable $th) {
			ReturnToJson(-1, '操作失败', $th);
		}
		ReturnToJson(1, '操作成功 更新用户数：' . $count);
	}

	//奖项管理
	public function _prize()
	{
		$pageuser = checkPower();
		$params = $this->params;

		$where = "1=1";
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		if ($params['s_keyword']) {
			$where .= " and (log.name like '%{$params['s_keyword']}%')";
		}

		$count_item = Db::table('gift_prize log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['gift_prize' => 'log'], ['*'])
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_prize_type = getConfig('cnf_prize_type');
		$coupon_arr = rows2arr(Db::table('coupon_list')->where("status<99")->field(['id', 'name'])->select()->toArray());
		foreach ($list as &$item) {
			//$item['create_time']=date('m-d H:i:s',$item['create_time']);
			$item['type_flag'] = $cnf_prize_type[$item['type']];
			$item['probability'] = floatval($item['probability']);
			$item['goods_name'] = '';
			if ($item['gid']) {
				$goods = Db::table('pro_goods')->where("id={$item['gid']}")->find();
				$item['goods_name'] = $goods['name'];
			}

			$item['coupon_name'] = '';
			if ($item['coupon_id']) {
				$item['coupon_name'] = $coupon_arr[$item['coupon_id']]['name'];
			}
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['type_arr'] = $cnf_prize_type;
			$return_data['goods_arr'] = Db::table('pro_goods')->where("status<99")->field(['id', 'name'])->select()->toArray();
			$return_data['coupon_arr'] = $coupon_arr;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _prize_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['type'] = intval($params['type']);
		$params['gid'] = intval($params['gid']);
		$params['coupon_id'] = intval($params['coupon_id']);
		$params['probability'] = floatval($params['probability']);
		$params['from_money'] = floatval($params['from_money']);
		$params['to_money'] = floatval($params['to_money']);
		if (!$params['name']) {
			ReturnToJson(-1, '请填写奖品名称');
		}
		if (!$params['cover']) {
			ReturnToJson(-1, '请上传奖品图片');
		}
		if ($params['probability'] < 0) {
			ReturnToJson(-1, '中奖概率不正确');
		}
		$cnf_prize_type = getConfig('cnf_prize_type');
		if (!array_key_exists($params['type'], $cnf_prize_type)) {
			ReturnToJson(-1, '未知奖品类型');
		}
		$db_item = [
			'type' => $params['type'],
			'name' => $_POST['name'],
			'cover' => $params['cover'],
			'probability' => $params['probability'],
			'update_time' => NOW_TIME,
			'from_money' => 0,
			'to_money' => 0,
			'gid' => 0,
			'coupon_id' => 0,
			'remark' => ''
		];
		try {
			$item = Db::table('gift_prize')->where("id={$item_id}")->find();
			if (!$item) {
				ReturnToJson(-1, '不存在相应的产品');
			}
			if ($params['type'] == 1) {
				if ($params['from_money'] < 0) {
					ReturnToJson(-1, '起始金额不正确');
				}
				if ($params['to_money'] < $params['from_money']) {
					ReturnToJson(-1, '结束金额不正确');
				}
				$db_item['from_money'] = $params['from_money'];
				$db_item['to_money'] = $params['to_money'];
			} elseif ($params['type'] == 2) {
				if (!$params['gid']) {
					ReturnToJson(-1, '请选择具体的产品');
				}
				$goods = Db::table('pro_goods')->where("id={$params['gid']}")->find();
				if (!$goods || $goods['status'] >= 99) {
					ReturnToJson(-1, '不存在相应的产品');
				}
				$db_item['gid'] = $params['gid'];
			} elseif ($params['type'] == 3) {
				if (!$params['remark']) {
					ReturnToJson(-1, '请填写中奖描述');
				}
				$db_item['remark'] = $params['remark'];
			} elseif ($params['type'] == 4) {
				if (!$params['remark']) {
					ReturnToJson(-1, '请填写中奖描述');
				}
				$db_item['remark'] = $params['remark'];
			} elseif ($params['type'] == 5) {
				if (!$params['coupon_id']) {
					ReturnToJson(-1, '请选择具体的代金券');
				}
				$coupon = Db::table('coupon_list')->where("id={$params['coupon_id']}")->find();
				if (!$coupon || $coupon['status'] >= 99) {
					ReturnToJson(-1, '不存在相应的代金券');
				}
				$db_item['coupon_id'] = $params['coupon_id'];
			}
			Db::table('gift_prize')->where("id={$item['id']}")->update($db_item);
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$return_data = [
			'type' => $db_item['type'],
			'type_flag' => $cnf_prize_type[$db_item['type']],
			'from_money' => $db_item['from_money'],
			'to_money' => $db_item['to_money'],
			'remark' => $db_item['remark'],
			'coupon_id' => $db_item['coupon_id'],
			'coupon_name' => $coupon ? $coupon['name'] : '',
			'gid' => $db_item['gid'],
			'goods_name' => $goods ? $goods['name'] : ''
		];
		if ($db_item['gid']) {
			$return_data['goods_name'] = $goods['name'];
		}
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _prizeLog()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_type'] = intval($params['s_type']);

		$where = "1=1";

		if ($params['s_user']) {
			$s_user = Db::table('sys_user')->where("id='{$params['s_user']}' or account='{$params['s_user']}'")->find();
			if ($s_user) {
				$where .= " and log.uid={$s_user['id']}";
			} else {
				$where .= " and log.uid=0";
			}
		}

		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in ({$uid_str})";
		}
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		$where .= empty($params['s_type']) ? '' : " and log.type={$params['s_type']}";
		if ($params['s_keyword']) {
			//$where .= " and (log.remark like '%{$params['s_keyword']}%')";

			$s_user = Db::table('sys_user')->where("id='{$params['s_keyword']}' or account='{$params['s_keyword']}'")->find();
			if ($s_user) {
				$where .= " and log.uid={$s_user['id']}";
			}
		}

		$count_item = Db::table('gift_prize_log log')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();

		$list = Db::view(['gift_prize_log' => 'log'], ['*'])
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_prize_type = getConfig('cnf_prize_type');
		//$coupon_arr=rows2arr(Db::table('coupon_list')->where("status<99")->field(['id','name'])->select()->toArray());
		$coupon_arr = [];
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['type_flag'] = $cnf_prize_type[$item['type']];
			$user = getUserinfo($item['uid']);
			$item['account'] = $user['account'];
			$item['nickname'] = $user['nickname'];
			$item['goods_name'] = '';
			if ($item['gid']) {
				$goods = Db::table('pro_goods')->where("id={$item['gid']}")->field(['name'])->find();
				$item['goods_name'] = $goods['name'];
			}
			$item['coupon_name'] = '';
			if ($item['coupon_id']) {
				$item['coupon_name'] = $coupon_arr[$item['coupon_id']]['name'];
			}
			if ($params['s_trans']) {
				$item['type_flag'] = lang2($item['type_flag']);
			}
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => number_format($count_item['money'], 2, '.', ''),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['type_arr'] = $cnf_prize_type;
			$return_data['coupon_arr'] = $coupon_arr;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//抽奖设置
	public function _lotterySet()
	{
		checkPower();
		$lottery = Db::table('gift_lottery')->where("id=1")
			->field(['rsn', 'stock_money', 'from_money', 'to_money', 'status', 'day_limit', 'week_limit', 'lottery_min'])->find();
		$return_data = [
			'lottery' => $lottery
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _lotterySetUpdate()
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
			ReturnToJson(-1, '中奖起始金额不正确');
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

	//抽奖设置
	public function _lottery()
	{
		$params = $this->params;

		$list = Db::view(['gift_prize' => 'log'], ['*'])
			->view(['pro_goods' => 'u'], ['name goodname'], 'log.gid=u.id', 'LEFT')
			->view(['coupon_list' => 'd'], ['name couponname'], 'log.coupon_id=d.id', 'LEFT')
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$goods = Db::table('pro_goods')->select();
		$coupons = Db::table('coupon_list')->select();

			foreach ($list as &$item) {
				if($item['type'] == 1)
					$item['typename']  = "余额";
				if($item['type'] == 2)
					$item['typename']  = "产品";
				if($item['type'] == 3)
					$item['typename']  = "实物";
				if($item['type'] == 4)
					$item['typename']  = "空";
				if($item['type'] == 5)
					$item['typename']  = "奖券";
			}
			
		$return_data = [
			'list' => $list,
			'limit' => $this->pageSize,
			'goods' =>$goods,
			'coupons'=> $coupons
			
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _lottery_save()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		if (!$params['name']) {
			ReturnToJson(-1, '请填写奖项名称');
		}
		if (!$params['cover']) {
			ReturnToJson(-1, '请上传奖品图片');
		}

		$db_data = [
			'type' => $params['type'],
			'name' => $params['name'],
			'cover' => $params['icon'],
			'probability' => $params['probability'],
			'from_money' => $params['from_money'],
			'to_money' => $params['to_money'],
			'gid' => $params['gid'],
			'coupon_id' => $params['coupon_id'],
			'remark' => $params['remark'],
			'buyAmountStart' => $params['buyAmountStart'],
			'buyAmountEnd' => $params['buyAmountEnd'],
		];
		Db::table('gift_prize')->whereRaw('id=:id', ['id' => $params["id"]])->update($db_data);
		ReturnToJson(1, '操作成功');
	}

	//抽奖记录
	public function _lotteryLog()
	{
		$pageuser = checkPower();
		$params = $this->params;

		$where = "1=1";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in ({$uid_str})";
		}
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		
		if ($params['s_keyword']) {
			$where .= " and (u.account='{$params['s_keyword']}' or u.nickname like '%{$params['s_keyword']}%')";
		}

		$count_item = Db::table('gift_prize_log log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();

		$list = Db::view(['gift_prize_log' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['is_user_flag'] = $item['is_user'] == 0 ? '未抽奖':'已抽奖';
			if($item['type'] == 1)
					$item['typename']  = "余额";
				if($item['type'] == 2)
					$item['typename']  = "产品";
				if($item['type'] == 3)
					$item['typename']  = "实物";
				if($item['type'] == 4)
					$item['typename']  = "空";
				if($item['type'] == 5)
					$item['typename']  = "奖券";
		}
		$return_data = [
			'list' => $list,
			'count' =>intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	//////////////////////////////////////////////////////////////////
	//券
	public function _coupon()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_status'] = intval($params['s_status']);

		$where = "log.status<99";
		$where .= empty($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		if ($params['s_keyword']) {
			$where .= " and (log.name like '%{$params['s_keyword']}%')";
		}

		$count_item = Db::table('coupon_list log')
			->fieldRaw('count(1) as cnt')
			->where($where)
			->find();

		$list = Db::view(['coupon_list' => 'log'], ['*'])
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_coupon_type = getConfig('cnf_coupon_type');
		$cnf_coupon_status = getConfig('cnf_coupon_status');
		$goods_arr = rows2arr(Db::table('pro_goods')
			->where("1=1")
			->field(['id', 'name'])
			->order(['sort' => 'desc', 'id' => 'desc'])->select()->toArray());
		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			if ($item['effective_time'] > 0) {
				$item['effective_time_flag'] = date('Y-m-d H:i:s', $item['effective_time']);
			} else {
				$item['effective_time_flag'] = '永久有效';
			}
			$item['type_flag'] = $cnf_coupon_type[$item['type']];
			$item['status_flag'] = $cnf_coupon_status[$item['status']];
			$gids = json_decode($item['gids'], true);
			if (!$gids) {
				$gids = [];
			}
			$item['gids'] = $gids;
			$goods = [];
			foreach ($gids as $gid) {
				$goods[] = $goods_arr[$gid]['name'];
			}
			$item['goods'] = $goods;
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['type_arr'] = $cnf_coupon_type;
			$return_data['status_arr'] = $cnf_coupon_status;
			$return_data['goods_arr'] = $goods_arr;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _coupon_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['type'] = intval($params['type']);
		$params['status'] = intval($params['status']);
		$params['stock_num'] = intval($params['stock_num']);
		$params['discount'] = floatval($params['discount']);
		$params['money'] = floatval($params['money']);
		$params['qx'] = intval($params['qx']);
		if (!$params['gids']) {
			$params['gids'] = [];
		}
		if (!$params['name']) {
			ReturnToJson(-1, '请填写券名称');
		}
		/*
		if($params['discount']<=0&&$params['money']<=0){
			ReturnToJson(-1,'折扣与面值必须设置一项');
		}else{
			if($params['discount']<0||$params['discount']>100){
				ReturnToJson(-1,'折扣比例不正确');
			}
			if($params['money']<0){
				ReturnToJson(-1,'面额不正确');
			}
		}*/
		if ($params['type'] == 1) {
			$params['gids'] = [];
			$params['money'] = 0;
			if ($params['discount'] <= 0) {
				ReturnToJson(-1, '折扣比例不正确');
			}
		} elseif ($params['type'] == 2) {
			$params['discount'] = 0;
			if ($params['money'] <= 0) {
				ReturnToJson(-1, '面额不正确');
			}
		}
		if ($params['stock_num'] < 0) {
			ReturnToJson(-1, '库存不正确');
		}
		if (!$params['cover']) {
			ReturnToJson(-1, '请上传券图片');
		}

		$cnf_coupon_type = getConfig('cnf_coupon_type');
		if (!array_key_exists($params['type'], $cnf_coupon_type)) {
			ReturnToJson(-1, '未知类型');
		}

		$cnf_coupon_status = getConfig('cnf_coupon_status');
		if (!array_key_exists($params['status'], $cnf_coupon_status)) {
			ReturnToJson(-1, '未知状态');
		}

		$goods_arr = rows2arr(Db::table('pro_goods')
			->where("status<99")
			->field(['id', 'name'])
			->order(['sort' => 'desc', 'id' => 'desc'])->select()->toArray());

		$gids = [];
		$goods = [];
		foreach ($params['gids'] as $gid) {
			$gid = intval($gid);
			if ($goods_arr[$gid]) {
				$gids[] = $gid;
				$goods[] = $goods_arr[$gid]['name'];
			}
		}
		$db_item = [
			'gids' => json_encode($gids),
			'status' => $params['status'],
			'type' => $params['type'],
			'name' => $_POST['name'],
			'cover' => $params['cover'],
			'discount' => $params['discount'],
			'money' => $params['money'],
			'stock_num' => $params['stock_num'],
			'update_time' => NOW_TIME,
			'effective_time' => strtotime($params['effective_time']),
			'remark' => $params['remark'],
			'qx' => $params['qx'],
		];
		Db::startTrans();
		try {
			if ($item_id) {
				$item = Db::table('coupon_list')->where("id={$item_id}")->lock(true)->find();
				if (!$item || $item['status'] >= 99) {
					ReturnToJson(-1, '不存在相应的券');
				}
				Db::table('coupon_list')->where("id={$item['id']}")->update($db_item);
			} else {
				$db_item['create_id'] = $pageuser['id'];
				$db_item['create_time'] = NOW_TIME;
				$res = Db::table('coupon_list')->insertGetId($db_item);
			}
			Db::commit();
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$return_data = [
			'effective_time' => $db_item['effective_time'],
			'effective_time_flag' => date('m-d H:i:s', $db_item['effective_time']),
			'gids' => $gids,
			'goods' => $goods,
			'type' => $db_item['type'],
			'type_flag' => $cnf_coupon_type[$db_item['type']],
			'status' => $db_item['status'],
			'status_flag' => $cnf_coupon_status[$db_item['status']]
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _coupon_delete()
	{
		checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('coupon_list');
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
	//lj
	public function _couponLog()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_cid'] = intval($params['s_cid']);
		$params['s_type'] = intval($params['s_type']);

		$where = "1=1";

		if ($params['s_user']) {
			$s_user = Db::table('sys_user')->where("id='{$params['s_user']}' or account='{$params['s_user']}'")->find();
			if ($s_user) {
				$where .= " and log.uid={$s_user['id']}";
			} else {
				$where .= " and log.uid=0";
			}
		}


		if ($pageuser['gid'] != 1) {
			$uid_arr =	getDownUser($pageuser['id'], false, $pageuser);
			$uid_str = implode(',', $uid_arr);
			if (!$uid_str) {
				$uid_str = '0';
			}
			$where .= " and log.uid in({$uid_str})";
		}

		$where .= empty($params['s_cid']) ? '' : " and log.cid={$params['s_cid']}";
		$where .= empty($params['s_type']) ? '' : " and log.type={$params['s_type']}";
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}
		if ($params['s_keyword']) {
			//$where .= " and (log.remark like '%{$params['s_keyword']}%')"; 
			$where  .= " and u.openid='{$params['s_keyword']}' ";
		}

		$count_item = Db::table('coupon_log log')
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->fieldRaw('count(1) as cnt,sum(log.num) as num,sum(log.used) as used,sum(log.money) as money')
			->where($where)
			->find();

		$list = Db::view(['coupon_log' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$coupon_list = rows2arr(Db::table('coupon_list')->where('1=1')->field(['id', 'name', 'status'])->select()->toArray());
		$goods_arr = rows2arr(Db::table('pro_goods')
			->where("1=1")
			->field(['id', 'name'])
			->order(['sort' => 'desc', 'id' => 'desc'])->select()->toArray());

		$cnf_coupon_type = getConfig('cnf_coupon_type');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			if ($item['effective_time'] > 0) {
				$item['effective_time_flag'] = date('m-d H:i:s', $item['effective_time']);
			} else {
				$item['effective_time_flag'] = '永久有效';
			}
			$item['type_flag'] = $cnf_coupon_type[$item['type']];
			$item['coupon_name'] = $coupon_list[$item['cid']]['name'];
			$gids = json_decode($item['gids'], true);
			if (!$gids) {
				$gids = [];
			}
			$goods = [];
			foreach ($gids as $gid) {
				$goods[] = $goods_arr[$gid]['name'];
			}
			$item['goods'] = $goods;
		}

		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'num' => intval($count_item['num']),
			'used' => intval($count_item['used']),
			'money' => floatval($count_item['money']),
			'limit' => $this->pageSize,
			'where' => $where,
		];
		if ($params['page'] < 2) {
			$cpn = [];
			foreach ($coupon_list as $cv) {
				if ($cv['status'] >= 99) {
					continue;
				}
				$cpn[] = $cv;
			}
			$return_data['coupon_arr'] = $cpn;
			$return_data['type_arr'] = $cnf_coupon_type;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _couponLogAdd()
	{
		$pageuser = checkPower('Gift_couponLog_update');
		$params = $this->params;
		$params['num'] = intval($params['num']);
		$params['coupon_id'] = intval($params['coupon_id']);
		if (!$params['account']) {
			ReturnToJson(-1, '缺少接收账号');
		}
		$user = Db::table('sys_user')->where("account='{$params['account']}'")->find();
		if (!$user) {
			ReturnToJson(-1, '不存在相应的接收账号');
		} else {
			if ($user['status'] != 2) {
				ReturnToJson(-1, '接收账号异常');
			}
		}
		if (!$params['coupon_id']) {
			ReturnToJson(-1, '请选择具体的代金券');
		}
		$coupon = Db::table('coupon_list')->where("id={$params['coupon_id']}")->find();
		if (!$coupon) {
			ReturnToJson(-1, '不存在相应的代金券');
		}
		if ($params['num'] < 1) {
			ReturnToJson(-1, '赠送数量不正确');
		}
		$remark = $params['remark'] ? $params['remark'] : 'System gift';
		$res = addCouponLog($user['id'], $coupon['id'], $params['num'], $remark);
		if ($res !== true) {
			ReturnToJson(-1, '系统繁忙请稍后再试:' . $res);
		}
		$return_data = [];
		ReturnToJson(1, '操作成功', $return_data);
	}

	//////////////////////////////////////////////////////////////////
	//红包码管理
	public function _redpack()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$where = "log.status<99";
		if (!checkDataAction()) {
			$where .= " and log.create_id={$pageuser['id']}";
		}
		$where .= empty($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		$where .= empty($params['s_keyword']) ? '' : " and (log.rsn='{$params['s_keyword']}' or u.account='{$params['s_keyword']}' or log.name like '%{$params['s_keyword']}%')";
		$count_item = Db::table('gift_redpack log')
			->leftJoin('sys_user u', 'log.create_id=u.id')
			->fieldRaw('count(1) as cnt,sum(log.receive_quantity) as receive_quantity,sum(log.receive_money) as receive_money')->where($where)->find();
		$list = Db::view(['gift_redpack' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname'], 'log.create_id=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_redpack_status = getConfig('cnf_redpack_status');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['status_flag'] = $cnf_redpack_status[$item['status']];
		}
		$return_data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'receive_quantity' => intval($count_item['receive_quantity']),
			'receive_money' => number_format($count_item['receive_money'], 2, '.', ''),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['status_arr'] = $cnf_redpack_status;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _redpack_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['status'] = intval($params['status']);
		$params['quantity'] = intval($params['quantity']);
		$params['total_money'] = floatval($params['total_money']);
		if (!$params['name']) {
			ReturnToJson(-1, '请填写红包名称');
		}
		if ($params['total_money'] < 0.01) {
			ReturnToJson(-1, '红包总额不正确');
		}
		if ($params['quantity'] < 1) {
			ReturnToJson(-1, '红包数量不正确');
		}
		/*
		if(!$params['icon']){
			ReturnToJson(-1,'请上传图标');
		}
		$covers=[];
		if(!$params['covers']){
			$params['covers']=[];
		}
		foreach($params['covers'] as $cv){
			if(!$cv){
				continue;
			}
			$covers[]=$cv;
		}
		*/
		$cnf_redpack_status = getConfig('cnf_redpack_status');
		if (!array_key_exists($params['status'], $cnf_redpack_status)) {
			ReturnToJson(-1, '未知状态');
		}
		$db_data = [
			'name' => $params['name'],
			'icon' => $params['icon'],
			'status' => $params['status']
		];
		try {
			$model = Db::table('gift_redpack');
			if ($item_id) {
				$item = $model->where("id={$item_id} and status<99")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				if (!checkDataAction()) {
					if ($item['create_id'] != $pageuser['id']) {
						ReturnToJson(-1, '没有操作权限');
					}
				}
				$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
				$db_data['id'] = $item_id;
			} else {
				$db_data['total_money'] = $params['total_money'];
				$db_data['quantity'] = $params['quantity'];
				$db_data['create_id'] = $pageuser['id'];
				$db_data['create_time'] = NOW_TIME;
				$db_data['create_day'] = date('Ymd', NOW_TIME);
				$db_data['rsn'] = getRsn();
				$res = $model->insertGetId($db_data);
				$db_data['id'] = $res;
				//生成红包明细
				require_once(LIB_PATH . 'Redpack.class.php');
				$redpack = new Redpack($params['total_money'], $params['quantity']);
				$resultArr = $redpack->getPack();
				foreach ($resultArr as $mv) {
					if ($mv < 0.01) {
						continue;
					}
					$gift_redpack_detail = [
						'rid' => $res,
						'rsn' => $db_data['rsn'],
						'dsn' => getRsn(),
						'money' => $mv,
						'create_id' => $pageuser['id'],
						'create_time' => NOW_TIME
					];
					Db::table('gift_redpack_detail')->insertGetId($gift_redpack_detail);
				}
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		actionLog(['opt_name' => '更新红包', 'sql_str' => json_encode($db_data)]);
		$cnf_redpack_status = getConfig('cnf_redpack_status');
		$return_data = [
			'status_flag' => $cnf_redpack_status[$db_data['status']]
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _redpack_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('gift_redpack');
		$item = $model->where("id={$item_id} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		if (!checkDataAction()) {
			if ($item['create_id'] != $pageuser['id']) {
				ReturnToJson(-1, '没有操作权限');
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
	public function _redpack_log_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('gift_redpack_detail');
		$item = $model->where("id={$item_id} and status<99")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		//		if(!checkDataAction()){
		//			if($item['create_id']!=$pageuser['id']){
		//				ReturnToJson(-1,'没有操作权限');
		//			}
		//		}
		try {
			$res = $model->whereRaw('id=:id', ['id' => $item_id])->delete();
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '操作成功');
	}

	public function _redpackLog()
	{
		$pageuser = checkPower();
		$params = $this->params;

		$where = "1=1";

		if ($params['s_user']) {
			$s_user = Db::table('sys_user')->where("id='{$params['s_user']}' or account='{$params['s_user']}'")->find();
			if ($s_user) {
				$where .= " and log.uid={$s_user['id']}";
			} else {
				$where .= " and log.uid=0";
			}
		}

		if ($pageuser['gid'] != 1) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = $pageuser['id'];
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.create_id in ({$uid_str})";
		}
		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.receive_time between {$start_time} and {$end_time}";
		}

		if ($params['money_status']) {
			if ($params['money_status'] == 1) {
				$where .= " and (log.money>='10000')";
			} else {
				$where .= " and (log.money<='10000')";
			}
		}
		$where .= " and log.uid>0";
		if ($params['s_keyword']) {
			$where .= " and (log.rsn='{$params['s_keyword']}' or u.openid='{$params['s_keyword']}') ";
		}

		$count_item = Db::table('gift_redpack_detail log')
			->view(['sys_user' => 'u'], ['account', 'nickname'], 'log.uid=u.id', 'LEFT')
			->fieldRaw('count(1) as cnt,sum(log.money) as money')
			->where($where)
			->find();
		$list = Db::view(['gift_redpack_detail' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account', 'nickname'], 'log.uid=u.id', 'LEFT')
			->view(['sys_user' => 'u1'], ['account' => 'c_account', 'nickname' => 'c_nickname'], 'log.create_id=u1.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['receive_time'] = date('m-d H:i:s', $item['receive_time']);

			// $user = getUserinfo($item['uid']);
			// $item['account'] = $user['account'];
			// $item['nickname'] = $user['nickname'];

			// $cuser = getUserinfo($item['create_id']);
			// $item['c_account'] = $cuser['account'];
			// $item['c_nickname'] = $cuser['nickname'];
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'money' => number_format($count_item['money'], 2, '.', ''),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $return_data);
	}
}
