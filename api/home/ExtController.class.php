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
		$task['end_time'] = date('d/m/y h:i:s', $task['end_time']);
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
		// 		ReturnToJson(-1, '系统繁忙请稍后再试');
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
			ReturnToJson(-1, '系统繁忙请稍后再试');
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
				ReturnToJson(-1, '开始/结束日期选择不正确');
			}
			$where .= " and log.create_time between {$start_time} and {$end_time}";
		}

		$count_item = Db::table('ext_tasklog log')
			->leftJoin('ext_task t', 'log.tid=t.id')
			->fieldRaw('count(1) as cnt,sum(log.award) as award')
			->where($where)
			->find();

		$list = Db::view(['ext_tasklog' => 'log'], [
			'id', 'tsn', 'status', 'award', 'voucher', 'create_time', 'remark', 'check_remark'
		])
			->view(['ext_task' => 't'], ['name' => 'task_name'], 'log.tid=t.id', 'LEFT')
			->where($where)
			->order(['log.id' => 'desc'])
			->page($params['page'], $this->pageSize)
			->select()->toArray();

		$cnf_task_status = getConfig('cnf_task_status');
		foreach ($list as &$item) {
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
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
}



// 9399213529
// 6397941415
// 7389025705
// 6301896736
// 9102777401
// 8839513422
// 7908868452
// 7388812337
// 8210163887
// 6201033295
// 8867508434
// 8839491649
// 9943251836
// 9931860834
// 7561982974
// 8050626974
// 9529739468
// 6203003988
// 6291306440
// 6266009028
// 7247400168
// 9153950449
// 8340798544
// 7706063360

// SELECT id FROM `sys_user` WHERE openid in(
// 	'9002046940',
// 	'9575514619',
// 	'7019101576',
// 	'6361207245',
// 	'7415987983',
// 	'6203072234',
// 	'9792379556',
// 	'8707799675',
// 	'7362881445',
// 	'7782078026',
// 	'7843887636',
// 	'8271666224',
// 	'8839407570',
// 	'9534992164',
// 	'9611798358',
// 	'9973513683')



/*

106494
192890
211431
220743
327911
331507
391107
421779
447757
532594
557495
571505
716580
741447
757697
870763













SELECT  f.uid, (select openid from sys_user where id= f.uid) ,f.osn  FROM `fin_paylog` f
  
WHERE  f.osn in





('847044',
'342333',
'271966',
'326568',
'690905',
'865110',
'483854',
'686783',
'880482',
'458013',
'725002',
'338182',
'823946',
'435242',
'144697',
'823084',
'236478',
'693045',
'509564',
'635562',
'106412',
'209418',
'572572',
'839811')


	

 


coupon_log


 (
'38850a9aa8a652f6',
'83b7e6935b96efc9',
'6bbd56c028bd2811',
'4943f9c463d22175',
'3a7c2c327f0ee337',
'45383d1389f71428',
'2e6874c97f9879b6',
'b9bd626580ebf023',
'203ce553fe762d44',
'9046b35792b8c28f',
'ad4925f0f0b6bd10',
'3883f6db06db14fa',
'caf4cf727b54da9a',
'bb91c2ff7f61ddb1',
'401591889b7498dd',
'b67cf4b6fc0d8bb9',
'0b3f60b88d302409',
'39f72683b04d7201',
'f88465ec80c9893c',
'5fcb7fbf0a9d35e5',
'deb37b9f69bdbbb5',
'b0b1589038fff4a5',
'78f053da2e95f6ea',
'255abbe6315003ad',
'a2f9dc8e72f5a334',
'afabdb123c599b21',
'efb282d187b8ae20',
'9176c877b4b09343',
'efefc35e11fdf003',
'71720bcc580e0730',
'e04c252df8548b86',
'ba896674a08fc905',
'3d0a939cf542be02',
'f806a8bc458881da',
'37ffd71e2e4809c3',
'5770d5fcad27292d',
'189743c10aa36d11',
'96c495acb5f4ec6a',
'89387c1b05e733e1',
'8c3e0b184ccc82a2',
'9c5cb1467a6c31a5',
'361122eaf044e89f',
'9258f1d5b068b8c8',
'83d78a72d06ff386',
'2798c04552d7928c',
'41af742d5f4ee08f',
'30dbc40c06a13bd9',
'7471738c17c4587b',
'43ad26a6ea2078e4',
'87e631c88ee3b2d5',
'd486ec593cb2e2a3',
'bdb844aea6eaafda',
'41fe45f721e8c6e6',
'09a69b45316219b5',
'9312bda3008985e5',
'e11e391dbe1cd6ef',
'0cd7ff9996cd4fb6',
'cabd5f85a6663e3a',
'678fc9d775779ef5',
'8b9f47761c14af04',
'0d187ab842ffc878',
'10184aea3cbdc2c6',
'944fe6f9948819a5',
'9d33652e7a30e3c7',
'a787633d0021ec6f',
'bbfdd5a4fab9ba18',
'a0b2fe786aa1d35a',
'00b4325a3608cc47',
'cab6a5fcd38ae87c',
'2291f5f94d7741c8',
'509414ed52661dec',
'be89f927ee20c42b',
'ff3a167200394887',
'd42c2fc587990ddd',
'5c6a8bbd50da2a40',
'8328ace78140c67f',
'72e77994426a8d80',
'37df5dbd7e1c5f1a',
'1d6fb4f429796741',
'c30f21a83aed2cc6',
'4a8b2ce171d9f178',
'9056d229853332db',
'6ee211a371d12108',
'cb218c4773cb8e4c',
'70f4a5743651315f',
'c20ad9b57d722cbb',
'b23164fba8fea168',
'8cd1878100abf0d7',
'9913bd2d1c0fa39a',
'6acbb1ca5acd9eb9',
'784d72d3db1fdcdb',
'07f343f447ffd89d',
'dcf74dfde8b31c05',
'84334fcb5b22806c',
'ffc5d066b4bbd141',
'831c442eb68df5da',
'5a9e9e3787e2ec3a',
'4d41c91da7caa215',
'ff138157ea558953',
'2029a0617c9d9634',
'ff3224620ff03417',
'd9a470cbdee823aa',
'41254e501b91d343',
'85990f26e8542369',
'2ebccc138d65a9dc',
'7db63de53a3e1da6',
'b68e5c89df015c6e',
'a6de5a6c1e027d48',
'07d89ea3072aa93a',
'6d70293168a08320',
'67cbc992d8309f80',
'a88c354e9a1491f6',
'54dc3be4ccb91402',
'40e225501b47ef15',
'ec88676921110649',
'70cb5534a812f2be',
'eab88edf257c5d54',
'e1dd7f0b907bf82b',
'494db2061169536b',
'fcc541324236795d',
'633e4f1984d60066',
'4c8e211fd16dcdf1',
'7fe37533e99cb4ae',
'e97ad977a72a3dfa',
'b66826c01cfa06b5',
'f343387536273cad',
'6d1bebcb864071ce',
'34e56927074de43e',
'c048947cde653090',
'bcd111030ea2d471',
'50c633c4ce43af0e',
'7e5f7f77e117b43c',
'9ff36453695da911',
'89e93374b5445d30'
 )





 	





	

delete from coupon_log,
fin_cashlog,
fin_paylog,
gift_lottery_log,
gift_prize_log,
gift_redpack_detail,
pro_order,
wallet_log,
wallet_list,
coupon_used,
ext_tasklog,
where uid in('847044',	
'342333',	
'271966',	
'326568',	
'690905',	
'865110',	
'483854',	
'686783',	
'880482',	
'458013',	
'725002',	
'338182',	
'823946',	
'435242',	
'144697',
'823084',
'236478',
'693045',
'509564',
'635562',
'106412',
'209418',
'572572',
'839811');




 


*/