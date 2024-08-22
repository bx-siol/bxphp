<?php
require_once (dirname(__FILE__) . '/daemon.ini.php');

use think\facade\Db;

error_reporting(7);

while (true) {
	$now_time = time();
	$now_day = date('Ymd', $now_time);
	$list = Db::table('fin_cashlog')->where("pay_status=1 and status=9")->page(1, 5)->order(['id' => 'desc', 'pay_type_bf' => 'desc'])->select()->toArray();
	if (!$list) {
		output('没有数据暂停5秒');
		sleep(5);
		continue;
	}

	//查询代付通道
	//$dtype=Db::table('fin_dtype')->where("status=3")->orderRaw("sort desc,rand()")->find();
	//if(!$dtype){
	// output('没有代付通道暂停5秒');
	// sleep(5);
	// continue;
	//}

	foreach ($list as $item) {
		Db::startTrans();
		try {
			$item = Db::table('fin_cashlog')->where("id={$item['id']}")->lock(true)->find();
			if (!$item || $item['pay_status'] != 1) {
				Db::rollback();
				continue;
			}
			$dtype = $item['pay_type_bf'];
			output($dtype);
			if (!$dtype) {
				Db::table('fin_cashlog')->where('id=' . $item['id'])->update(['status' => '1']);
				Db::commit();
				continue;
			}
			$result = cashAct($item, $dtype);
			output(json_encode($item));
			output($result);
			if ($result === false) {
				Db::rollback();
				continue;
			}

			$fin_cashlog = [];
			if ($result['code'] != 1) {
				$fin_cashlog = [
					'pay_status' => 3,
					'pay_msg' => $result['msg']
				];
			} else {
				$resultArr = $result['data'];
				$fin_cashlog = [
					'pay_status' => 2,
					'out_osn' => $resultArr['out_osn']
				];
				if ($dtype == 'offline') {
					$fin_cashlog['pay_status'] = 9;
					$fin_cashlog['pay_msg'] = '';
				}
			}
			$fin_cashlog['pay_type'] = $dtype;
			Db::table('fin_cashlog')->where("id={$item['id']}")->update($fin_cashlog);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
		}
	}
	output('处理完一批，暂停3秒');
	sleep(3);
}

function cashAct($item, $dtype)
{

	//接口
	$cash_file = '/www/wwwroot/php/api/api/common/cash/' . $dtype . '.php';
	if (!file_exists($cash_file)) {
		return false;
	}
	require_once $cash_file;
	$func_name = 'CashOrder';
	if (!function_exists($func_name)) {
		return false;
	}
	$result = $func_name($item);
	return $result;
}