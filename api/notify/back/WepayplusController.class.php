<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class WepayplusController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		echo 'notify';
	}

	public function _pay()
	{
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$time = date("Y-m-d", time());
		file_put_contents(LOGS_PATH . strtolower(CONTROLLER_NAME) . '/notify/pay' . $time . '.txt', NOW_DATE . "\r\n" . var_export($params, true) . "\r\n\r\n", FILE_APPEND);

		$sign = paySign($params);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}
		file_put_contents(LOGS_PATH . strtolower(CONTROLLER_NAME) . '/notify/pay' . $time . '.txt', NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n\r\n", FILE_APPEND);

		$pdata = [
			'code' => $params['payStatus'] == '1' ? 1 : -1,
			'osn' => $params['orderNo'],
			'amount' =>  $params['amount'],
			'successStr' => 'success'
		];

		$this->payAct($pdata);
	}

	public function _cash()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}

		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$time = date("Y-m-d", time());
		$log_file = LOGS_PATH . strtolower(CONTROLLER_NAME) . '/notify/cash' . $time . '.txt';

		file_put_contents($log_file, NOW_DATE . "\r\n" . var_export($params, true) . "\r\n\r\n", FILE_APPEND);

		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}
		file_put_contents(LOGS_PATH . strtolower(CONTROLLER_NAME) . '/notify/pay' . $time . '.txt', NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n\r\n", FILE_APPEND);

		//$order=Db::table('fin_cashlog')->where("osn='{$params['merchantCode']}'")->find();

		$pdata = [
			'osn' => $params['orderNo'],
			'out_osn' => $params['tradeNo'],
			'pay_status' => $params['payStatus'] == '1' ? 9 : 3,
			'pay_msg' => $params['tradeNo'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
