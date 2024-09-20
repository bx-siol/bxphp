<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class ExtController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _okkhfalnv()
	{
		$walletp_data = ['lottery' => 0];
		$d = Db::table('sys_user')->where(' lottery>0 ')->update($walletp_data);
		$return_data = ['task' => $d];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _task()
	{
		// $pageuser = checkLogin();
		// $task = [];
		// $up_users = getUpUser($pageuser['id'], true);
		// foreach ($up_users as $uv) {
		// 	$task = Db::table('ext_task')->where("uid={$uv['id']}")->field(['id', 'name', 'content'])->order(['sort' => 'desc', 'id' => 'desc'])->find();
		// 	if ($task) {
		// 		break;
		// 	}
		// }
		// if (!$task) {
		// 	$task = Db::table('ext_task')->where("gid<=70 or uid={$pageuser['id']}")->field(['id', 'name', 'content'])->order(['sort' => 'desc', 'id' => 'desc'])->find();
		// }
		// if (!$task) {
		// 	ReturnToJson(-1, 'Unpublished tasks');
		// }
		// $return_data = [
		// 	'task' => $task
		// ];
		// ReturnToJson(1, 'ok', $return_data);

		$pageuser = checkLogin();
		$task = [];
		$task = Db::table('ext_task')->field(['id', 'img', 'name'])->where(' ishow=1')->order(['sort' => 'desc', 'id' => 'desc'])->select()->toArray();;
		if (!$task) {
			ReturnToJson(-1, 'Unpublished tasks');
		}
		$return_data = [
			'list' => $task
		];
		ReturnToJson(1, 'ok', $return_data);
	}


	public function _gettask()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$task = [];
		$task = Db::table('ext_task')->where(' id=' . intval($params['id']))->find();
		if (!$task) {
			ReturnToJson(-1, 'Unpublished tasks');
		}
		$task['end_time'] = date('d/m/Y h:i:s', $task['end_time']);
		$return_data = [
			'list' => $task
		];
		ReturnToJson(1, 'ok', $return_data);
	}


	//领取任务
	public function _submitTask()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$item_id = intval($params['id']);
		// $tasklog_id = intval($params['tasklog_id']);
		// if ($tasklog_id) {
		// 	try {
		// 		$tasklog = Db::table('ext_tasklog')->where("id={$tasklog_id}")->find();
		// 		if (!$tasklog) {
		// 			ReturnToJson(-1, 'No corresponding task exists');
		// 		} else {
		// 			if ($tasklog['uid'] != $pageuser['id']) {
		// 				ReturnToJson(-1, 'No operating authority');
		// 			}
		// 			if ($tasklog['status'] != 3) {
		// 				ReturnToJson(-1, 'The current state is not operational');
		// 			}
		// 		}
		// 		if (!$params['voucher']) {
		// 			ReturnToJson(-1, 'Please upload voucher');
		// 		}
		// 		$ext_tasklog = [
		// 			'voucher' => json_encode($params['voucher']),
		// 			'remark' => $params['remark'],
		// 			'status' => 2,
		// 			'submit_time' => NOW_TIME
		// 		];
		// 		Db::table('ext_tasklog')->where("id={$tasklog['id']}")->update($ext_tasklog);
		// 	} catch (\Exception $e) {
		// 		ReturnToJson(-1, 'The system is busy, please try again later.');
		// 	}
		// 	ReturnToJson(1, 'Submitted successfully');
		// } else {
		// 	if (!$item_id) {
		// 		ReturnToJson(-1, 'There are currently no tasks');
		// 	}
		// }

		if (!$params['voucher']) {
			ReturnToJson(-1, 'Please upload voucher');
		}


		$order = Db::table('pro_order')->where(' uid=' . $pageuser['id'] . ' and is_give=0')->count();

		if ($order == 0) {
			ReturnToJson(-1, 'Before submitting the task, you need to purchase at least one product');
		}
		$item = Db::table('ext_task')->where("id={$item_id}")->find();
		if (!$item) {
			ReturnToJson(-1, 'No corresponding task exists');
		}


		$now_day = date('Ymd');
		$tasklogNum = Db::table('ext_tasklog')->where("uid={$pageuser['id']} and tid={$item['id']} and create_day={$now_day}")->count('id');
		$tasklogNumall = Db::table('ext_tasklog')->where("uid={$pageuser['id']} and tid={$item['id']}")->count('id');

		// $up_users = getUpUser($pageuser['id']);
		// if (!in_array($item['uid'], $up_users) && $item['gid>70']) {
		// 	ReturnToJson(-1, 'Unknown task');
		// }

		if ($tasklogNum >= $item['day_limit']) {
			ReturnToJson(-1, 'The task has reached the limit today');
		}
		if ($tasklogNumall >= $item['all_limit']) {
			ReturnToJson(-1, 'The task has reached the limit');
		}

		//一次
		if ($item['type'] == 0) {

			if ($tasklogNumall > 0) {
				ReturnToJson(-1, 'This task can only be submitted once');
			}
		} else {
			//多次 
			$lasttime =	Db::table('ext_tasklog')->field(['create_time'])->where("uid={$pageuser['id']} and tid={$item['id']}")->order('create_time')->find();
			if ($lasttime != null &&  ($lasttime['create_time'] + (60 * 60 * 24 * 7)) > time()) {
				ReturnToJson(-1, 'This task can only once a week');
			}
		}

		if ($item['end_time'] < time()) {
			ReturnToJson(-1, 'This task has ended');
		}


		$ext_tasklog = [
			'tsn' => getRsn(),
			'uid' => $pageuser['id'],
			'tid' => $item['id'],
			'award' => $item['award'],
			'voucher' => json_encode($params['voucher']),
			'remark' => $params['remark'],
			'create_day' => $now_day,
			'create_time' => NOW_TIME,
			'submit_time' => NOW_TIME,
			'status' => 2
		];
		try {
			Db::table('ext_tasklog')->insert($ext_tasklog);
		} catch (\Exception $e) {
			ReturnToJson(-1, 'The system is busy, please try again later.');
		}
		ReturnToJson(1, 'Submitted successfully');
	}

	public function _tasklog()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$params['page'] = intval($params['page']);

		$where = "log.uid={$pageuser['id']}";
		//$where.=empty($params['s_type'])?'':" and log.type={$params['s_type']}";

		if ($params['s_start_time'] && $params['s_end_time']) {
			$start_time = strtotime($params['s_start_time'] . ' 00:00:00');
			$end_time = strtotime($params['s_end_time'] . ' 23:59:59');
			if ($start_time > $end_time) {
				ReturnToJson(-1, 'Start date or end date selection is incorrect.');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}

		$count_item = Db::table('ext_tasklog log')
			->leftJoin('ext_task t', 'log.tid=t.id')
			->fieldRaw('count(1) as cnt,sum(log.award) as award')
			->where($where)
			->find();

		$list = Db::view(['ext_tasklog' => 'log'], [
			'id',
			'tsn',
			'status',
			'award',
			'voucher',
			'create_time',
			'remark',
			'check_remark'
		])
			->view(['ext_task' => 't'], ['name' => 'task_name'], 'log.tid=t.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$cnf_task_status = getConfig('cnf_task_status');
		foreach ($list as &$item) {
			$item['create_time'] = date('d/m/Y H:i:s', $item['create_time']);
			$item['status_flag'] = $cnf_task_status[$item['status']];
			$item['voucher'] = json_decode($item['voucher'], true);
		}

		$total_page = ceil($count_item['cnt'] / $this->pageSize);
		$return_data = [
			'list' => $list,
			'count' => intval($count_item['cnt']),
			'award' => number_format($count_item['award'], 2, '.', ''),
			'page' => $params['page'] + 1,
			'finished' => $params['page'] >= $total_page ? true : false,
			'limit' => $this->pageSize
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	public function _pageData()
	{
		$pageuser = checkLogin();
		$newmember = Db::table('sys_user')->where(" pid={$pageuser['id']} and first_pay_day > 0 ")->count();

		$return_data = [
			'newmember' => $newmember,
		];
		ReturnToJson(1, 'ok', $return_data);
	}

	//无人机累计邀请注册任务
	public function _reciveValidInvitation()
	{
		$pageuser = checkLogin();
		$params = $this->params;
		$money = 0;
		if ($params['type'] == 1) {
			$type = 61;
			$money = 300;
		} else if ($params['type'] == 2) {
			$type = 62;
			$money = 800;
		} else if ($params['type'] == 3) {
			$type = 63;
			$money = 1500;
		} else if ($params['type'] == 4) {
			$type = 64;
			$money = 2500;
		} else if ($params['type'] == 5) {
			$type = 65;
			$money = 8000;
		}

		$walllog = Db::table('wallet_log')->where(" uid={$pageuser['id']} and type = {$type} ")->count();
		if ($walllog > 0)
			ReturnToJson(-1, 'Finish');

		updateWalletBalanceAndLog($pageuser['id'], $money, 2, $type, 'Cumulative invitation registration：' . $params['type']);

		ReturnToJson(1, 'Success');
	}
}
