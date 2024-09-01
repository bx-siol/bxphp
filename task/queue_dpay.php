<?php
// define('APP_DEBUG',true);
// require_once(dirname(__FILE__).'/../global/Program.php');
// if(!PHP_CLI){
// 	exit('run in cli');
// }

// use think\facade\Db;

// error_reporting(7);

// while (true) {
// 	$now_time = time();
// 	$now_day = date('Ymd', $now_time);
// 	$list = Db::table('fin_cashlog')->where("pay_status=1 and status=9")->page(1, 5)->order(['id' => 'desc', 'pay_type_bf' => 'desc'])->select()->toArray();
// 	if (!$list) {
// 		output_task('没有数据暂停5秒');
// 		sleep(5);
// 		continue;
// 	}

// 	//查询代付通道
// 	//$dtype=Db::table('fin_dtype')->where("status=3")->orderRaw("sort desc,rand()")->find();
// 	//if(!$dtype){
// 	// output_task('没有代付通道暂停5秒');
// 	// sleep(5);
// 	// continue;
// 	//}

// 	foreach ($list as $item) {
// 		Db::startTrans();
// 		try {
// 			$item = Db::table('fin_cashlog')->where("id={$item['id']}")->lock(true)->find();
// 			if (!$item || $item['pay_status'] != 1) {
// 				Db::rollback();
// 				continue;
// 			}
// 			$dtype = $item['pay_type_bf'];
// 			output_task($dtype);
// 			if (!$dtype) {
// 				Db::table('fin_cashlog')->where('id=' . $item['id'])->update(['status' => '1']);
// 				Db::commit();
// 				continue;
// 			}
// 			$result = cashAct($item, $dtype);
// 			output_task(json_encode($item));
// 			output_task($result);
// 			if ($result === false) {
// 				Db::rollback();
// 				continue;
// 			}

// 			$fin_cashlog = [];
// 			if ($result['code'] != 1) {
// 				$fin_cashlog = [
// 					'pay_status' => 3,
// 					'pay_msg' => $result['msg']
// 				];
// 			} else {
// 				$resultArr = $result['data'];
// 				$fin_cashlog = [
// 					'pay_status' => 2,
// 					'out_osn' => $resultArr['out_osn']
// 				];
// 				if ($dtype == 'offline') {
// 					$fin_cashlog['pay_status'] = 9;
// 					$fin_cashlog['pay_msg'] = '';
// 				}
// 			}
// 			$fin_cashlog['pay_type'] = $dtype;
// 			Db::table('fin_cashlog')->where("id={$item['id']}")->update($fin_cashlog);
// 			Db::commit();
// 		} catch (\Exception $e) {
// 			Db::rollback();
// 		}
// 	}
// 	output_task('处理完一批，暂停3秒');
// 	sleep(3);
// }

// function cashAct($item, $dtype)
// {

// 	//接口
// 	$cash_file = '/www/wwwroot/php/api/api/common/cash/' . $dtype . '.php';
// 	if (!file_exists($cash_file)) {
// 		return false;
// 	}
// 	require_once $cash_file;
// 	$func_name = 'CashOrder';
// 	if (!function_exists($func_name)) {
// 		return false;
// 	}
// 	$result = $func_name($item);
// 	return $result;
// }


define('APP_DEBUG', true);
require_once(dirname(__FILE__) . '/../global/Program.php');
if (!PHP_SAPI === 'cli') {
	exit('run in cli');
}

use think\facade\Db;

define('PID_FILE', '/www/wwwroot/php/api/logs/adpay/pidfile.pid'); // 设置 PID 文件路径
define('LOG_FILE', '/www/wwwroot/php/api/logs/adpay/logfile' . date('Y-m-d') . '.log'); // 设置日志文件路径

// 检查并创建 PID 文件路径
$piddir = dirname(PID_FILE);
if (!is_dir($piddir)) {
	mkdir($piddir, 0777, true);
}

// 检查并创建日志文件路径
$logdir = dirname(LOG_FILE);
if (!is_dir($logdir)) {
	mkdir($logdir, 0777, true);
}

error_reporting(7);

function log_message($message)
{
	file_put_contents(LOG_FILE, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

function output_task($message)
{
	echo $message . PHP_EOL;
	log_message($message);
}

function create_pid_file()
{
	$pid = getmypid();
	file_put_contents(PID_FILE, $pid);
}

function clean_pid_file()
{
	if (file_exists(PID_FILE)) {
		unlink(PID_FILE);
	}
}

function signal_handler($signal)
{
	switch ($signal) {
		case SIGTERM:
		case SIGHUP:
		case SIGINT:
			output_task('停止信号接收，清理并退出...');
			clean_pid_file();
			exit;
	}
}

pcntl_signal(SIGTERM, 'signal_handler');
pcntl_signal(SIGHUP, 'signal_handler');
pcntl_signal(SIGINT, 'signal_handler');

create_pid_file();
log_message('守护进程启动');

while (true) {
	pcntl_signal_dispatch(); // 调用信号调度以处理信号

	$now_time = time();
	$now_day = date('Ymd', $now_time);
	$list = Db::table('fin_cashlog')->where("pay_status=1 and status=9")->page(1, 5)->order(['id' => 'desc', 'pay_type_bf' => 'desc'])->select()->toArray();
	if (!$list) {
		output_task('没有数据暂停5秒');
		sleep(5);
		continue;
	}

	foreach ($list as $item) {
		Db::startTrans();
		try {
			$item = Db::table('fin_cashlog')->where("id={$item['id']}")->lock(true)->find();
			if (!$item || $item['pay_status'] != 1) {
				Db::rollback();
				continue;
			}
			$dtype = $item['pay_type_bf'];
			output_task($dtype);
			if (!$dtype) {
				Db::table('fin_cashlog')->where('id=' . $item['id'])->update(['status' => '1']);
				Db::commit();
				continue;
			}
			$result = cashAct($item, $dtype);
			output_task(json_encode($item));
			output_task($result);
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
	output_task('处理完一批，暂停3秒');
	sleep(3);
}

log_message('守护进程停止');
clean_pid_file();

function cashAct($item, $dtype)
{
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
