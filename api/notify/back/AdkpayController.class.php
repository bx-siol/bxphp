<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class AdkpayController extends BaseController
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
		$time = date("Y-m-d", time());
		$log_file = LOGS_PATH .  strtolower(CONTROLLER_NAME) . '/notify/pay' . CONTROLLER_NAME . $time . '.txt';
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}

		$ooc = $params; //json_decode(stripslashes($params['data']), true);
		$sign = paySign(['merchantId' => $ooc['merchantId'], 'merchantOrderId' => $ooc['merchantOrderId'], 'amount' => $ooc['amount']]);
		file_put_contents($log_file, NOW_DATE . "\r\n" . var_export($params, true) . "\r\n" . $sign . "\r\n", FILE_APPEND);
		if ($ooc['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}
		$pdata = [
			'code' => $ooc['status'] == '1' ? 1 : -1,
			'osn' => $ooc['merchantOrderId'],
			'amount' =>  $ooc['amount'],
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
		$log_file = LOGS_PATH  . ($code) . '/notify//cash' . $code . '.txt';

		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}

		$ooc = $params; //json_decode(stripslashes($params['data']), true);
		$sign = $signFunc(['merchantId' => $ooc['merchantId'], 'merchantOrderId' => $ooc['merchantOrderId'], 'amount' => $ooc['amount']]);
		file_put_contents($log_file, NOW_DATE . "\r\n" . var_export($params, true) . "\r\n" . $sign . "\r\n", FILE_APPEND);
		if ($ooc['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'osn' => $ooc['merchantOrderId'],
			'out_osn' => $ooc['orderId'],
			'pay_status' => $ooc['status'] == '1' ? 9 : 3,
			'pay_msg' => $ooc['msg'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
