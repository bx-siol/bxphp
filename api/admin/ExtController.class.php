<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class ExtController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	//客服管理
	public function _service()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$where = "1=1";
		if (!checkDataAction()) {
			$where .= " and log.uid={$pageuser['id']}";
		}
		//$where.=empty($params['s_status'])?'':" and log.status={$params['s_status']}";
		$where .= empty($params['s_keyword']) ? '' : " and (log.account='{$params['s_keyword']}' or u.account='{$params['s_keyword']}')";
		$count_item = Db::table('ext_service log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt')->where($where)->find();
		$list = Db::view(['ext_service' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account' => 'u_account', 'nickname' => 'u_nickname'], 'log.uid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		$cnf_service_type = getConfig('cnf_service_type');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['type_flag'] = $cnf_service_type[$item['type']];
		}
		$return_data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['type_arr'] = $cnf_service_type;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _service_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['type'] = intval($params['type']);
		if (!$params['name']) {
			ReturnToJson(-1, '请填写客服名称');
		}
		if (!$params['account']) {
			ReturnToJson(-1, '请填写客服账号');
		}
		$cnf_service_type = getConfig('cnf_service_type');
		if (!array_key_exists($params['type'], $cnf_service_type)) {
			ReturnToJson(-1, '客服类型不正确');
		}
		/*
		if(!$params['qrcode']){
			ReturnToJson(-1,'请上传二维码');
		}
		*/
		$db_data = [
			'gid' => $pageuser['gid'],
			'uid' => $pageuser['id'],
			'name' => $params['name'],
			'account' => $params['account'],
			'qrcode' => $params['qrcode'],
			'remark' => $params['remark'],
			'type' => $params['type']
		];
		try {
			$model = Db::table('ext_service');
			if ($item_id) {
				$item = $model->where("id={$item_id}")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				if (!checkDataAction()) {
					if ($item['uid'] != $pageuser['id']) {
						ReturnToJson(-1, '没有操作权限');
					}
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
			'type_flag' => $cnf_service_type[$db_data['type']]
		];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _service_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('ext_service');
		$item = $model->where("id={$item_id}")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		if (!checkDataAction()) {
			if ($item['uid'] != $pageuser['id']) {
				ReturnToJson(-1, '没有操作权限');
			}
		}
		try {
			$res = $model->whereRaw('id=:id', ['id' => $item_id])->delete();
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '操作成功');
	}


	//////////////////////////////////////////////////////////////////////
	//任务管理
	public function _task()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$where = "1=1";
		if (!checkDataAction()) {
			$where .= " and log.uid={$pageuser['id']}";
		}
		//$where.=empty($params['s_status'])?'':" and log.status={$params['s_status']}";
		$where .= empty($params['s_keyword']) ? '' : " and (log.name='{$params['s_keyword']}' or u.account='{$params['s_keyword']}')";
		$count_item = Db::table('ext_task log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->fieldRaw('count(1) as cnt')->where($where)->find();
		$list = Db::view(['ext_task' => 'log'], ['*'])
			->view(['sys_user' => 'u'], ['account' => 'u_account', 'nickname' => 'u_nickname'], 'log.uid=u.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()
			->toArray();

		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			$item['end_time'] = $item['end_time'] == null ? '' : date('Y-m-d H:i:s', $item['end_time']);
		}
		$return_data = [
			'list' => $list,
			'count' => $count_item['cnt'],
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _task_update()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['sort'] = intval($params['sort']);
		$params['day_limit'] = intval($params['day_limit']);
		$params['award'] = floatval($params['award']);
		$params['all_limit'] = intval($params['all_limit']);
		if (!$params['name']) {
			ReturnToJson(-1, '请填写任务名称');
		}
		if ($params['day_limit'] < 1) {
			ReturnToJson(-1, '用户每日限量不正确');
		}
		if ($params['award'] < 0) {
			ReturnToJson(-1, '奖励额度不正确');
		}
		if (!$_POST['content']) {
			ReturnToJson(-1, '请填写任务要求');
		}
		$db_data = [
			'gid' => $pageuser['gid'],
			'uid' => $pageuser['id'],
			'name' => $params['name'],
			'sort' => $params['sort'],
			'award' => $params['award'],
			'day_limit' => $params['day_limit'],
			'content' => $_POST['content'],
			'img' => $params['img'],
			'end_time' => strtotime($params['end_time']),
			'type' =>  $params['type'],
			'all_limit' => $params['all_limit'],
			'ishow' => $params['ishow'],
		];
		try {
			$model = Db::table('ext_task');
			if ($item_id) {
				$item = $model->where("id={$item_id}")->find();
				if (!$item) {
					ReturnToJson(-1, '不存在相应的记录');
				}
				if (!checkDataAction()) {
					if ($item['uid'] != $pageuser['id']) {
						ReturnToJson(-1, '没有操作权限');
					}
				}
				$res = $model->whereRaw('id=:id', ['id' => $item_id])->update($db_data);
				$db_data['id'] = $item_id;
			} else {
				$db_data['create_time'] = NOW_TIME;
				$res = $model->insertGetId($db_data);
				$db_data['id'] = $res;
			}
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试' . $e->getMessage());
		}
		$return_data = [];
		ReturnToJson(1, '操作成功', $return_data);
	}

	public function _task_delete()
	{
		$pageuser = checkPower();
		$item_id = intval($this->params['id']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$model = Db::table('ext_task');
		$item = $model->where("id={$item_id}")->find();
		if (!$item) {
			ReturnToJson(-1, '该记录已删除');
		}
		if (!checkDataAction()) {
			if ($item['uid'] != $pageuser['id']) {
				ReturnToJson(-1, '没有操作权限');
			}
		}
		try {
			$res = $model->whereRaw('id=:id', ['id' => $item_id])->delete();
		} catch (\Exception $e) {
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		ReturnToJson(1, '操作成功');
	}

	public function _tasklog()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$params['s_status'] = intval($params['s_status']);

		$where = "1=1";
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = $pageuser['id'];
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

		if ($params['s_puser']) {
			$s_keyword = $params['s_puser'];
			$uid_arr = [];
			$s_puser = Db::table('sys_user')->where("id='{$s_keyword}' or account='{$s_keyword}' or email='{$s_keyword}' or realname='{$s_keyword}' or nickname='{$s_keyword}'")->find();
			if ($s_puser) {
				$uid_arr = getDownUser($s_puser['id']);
			}
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in({$uid_str})";
		}
		$where .= empty($params['s_status']) ? '' : " and log.status={$params['s_status']}";
		if ($params['s_keyword']) {
			$where .= " and (log.tsn='{$params['s_keyword']}' or u.account='{$params['s_keyword']}' or u.nickname like '%{$params['s_keyword']}%')";
		}

		$count_item = Db::table('ext_tasklog log')
			->leftJoin('sys_user u', 'log.uid=u.id')
			->leftJoin('ext_task t', 'log.tid=t.id')
			->fieldRaw('count(1) as cnt,sum(log.award) as award')
			->where($where)
			->find();

		$list = [];
		if ($pageuser['gid'] != '1') {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = 0;
			$uid_str = implode(',', $uid_arr);
			$where .= " and log.uid in ({$uid_str})";

			$list = Db::view(['ext_tasklog' => 'log'], ['*'])
				->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
				->view(['ext_task' => 't'], ['name' => 'task_name'], 'log.tid=t.id', 'LEFT')
				->where($where)
				->order(['log.id' => 'desc'])
				->page($params['page'], $this->pageSize)
				->select()
				->toArray();
		} else
			$list = Db::view(['ext_tasklog' => 'log'], ['*'])
				->view(['sys_user' => 'u'], ['account', 'nickname', 'headimgurl'], 'log.uid=u.id', 'LEFT')
				->view(['ext_task' => 't'], ['name' => 'task_name'], 'log.tid=t.id', 'LEFT')
				->where($where)
				->order(['log.id' => 'desc'])
				->page($params['page'], $this->pageSize)
				->select()
				->toArray();

		$cnf_task_status = getConfig('cnf_task_status');
		foreach ($list as &$item) {
			$item['create_time'] = date('m-d H:i:s', $item['create_time']);
			$item['status_flag'] = $cnf_task_status[$item['status']];
			if (!$item['submit_time']) {
				$item['submit_time'] = '/';
			} else {
				$item['submit_time'] = date('m-d H:i:s', $item['submit_time']);
			}
			if (!$item['check_time']) {
				$item['check_time'] = '/';
			} else {
				$item['check_time'] = date('m-d H:i:s', $item['check_time']);
			}
			$item['voucher'] = json_decode($item['voucher'], true);
			if (!$item['voucher']) {
				$item['voucher'] = [];
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
		}
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'award' => number_format($count_item['award'], 2, '.', ''),
			'limit' => $this->pageSize
		];
		if ($params['page'] < 2) {
			$return_data['status_arr'] = $cnf_task_status;
		}
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _tasklog_check()
	{
		$pageuser = checkPower();
		$params = $this->params;
		$item_id = intval($params['id']);
		$params['status'] = intval($params['status']);
		if (!$item_id) {
			ReturnToJson(-1, '缺少参数');
		}
		$item = Db::table('ext_tasklog')->where("id={$item_id}")->find();
		if (!$item) {
			ReturnToJson(-1, '参数错误');
		} else {
			if (!in_array($item['status'], [1, 2, 3])) {
				ReturnToJson(-1, '当前状态不可操作');
			}
		}
		if (!checkDataAction()) {
			$uid_arr = getDownUser($pageuser['id'], false, $pageuser);
			$uid_arr[] = $pageuser['id'];
			if (!in_array($item['uid'], $uid_arr)) {
				ReturnToJson(-1, '没有操作权限');
			}
		}
		if (!in_array($params['status'], [3, 9])) {
			ReturnToJson(-1, '未知审核状态');
		}
		Db::startTrans();
		try {
			$db_data = [
				'status' => $params['status'],
				'check_time' => NOW_TIME,
				'check_id' => $pageuser['id'],
				'check_remark' => $params['check_remark']
			];
			Db::table('ext_tasklog')->where("id={$item['id']}")->update($db_data);
			if ($params['status'] == 9) {
				$wallet = getWallet($item['uid'], 2); //到余额钱包
				if (!$wallet) {
					throw new \Exception('钱包获取异常');
				}
				$wallet = Db::table('wallet_list')->where("id={$wallet['id']}")->lock(true)->find();
				$money = $item['award'];
				$wallet_data = [
					'balance' => $wallet['balance'] + $money
				];
				//更新钱包余额
				Db::table('wallet_list')->where("id={$wallet['id']}")->update($wallet_data);
				//写入流水记录
				$result = walletLog([
					'wid' => $wallet['id'],
					'uid' => $wallet['uid'],
					'type' => 42,
					'money' => $money,
					'ori_balance' => $wallet['balance'],
					'new_balance' => $wallet_data['balance'],
					'fkey' => $item['id'],
					'remark' => 'Award'
				]);
				if (!$result) {
					throw new \Exception('流水记录写入失败');
				}
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			ReturnToJson(-1, '系统繁忙请稍后再试');
		}
		$cnf_task_status = getConfig('cnf_task_status');
		$return_data = [
			'status_flag' => $cnf_task_status[$db_data['status']],
			'check_time' => date('m-d H:i:s', NOW_TIME)
		];
		ReturnToJson(1, '操作成功', $return_data);
	}
}
