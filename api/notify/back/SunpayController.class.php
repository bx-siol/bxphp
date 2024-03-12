<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class SunpayController extends BaseController
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
		$logpathd = LOGS_PATH . 'sunpay/notify/';
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}


		$sign = paySign($params);
		//file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['tradeResult'] == '1' ? 1 : -1,
			'osn' => $params['mchOrderNo'],
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
		if (!$params) {
			$params = $_GET;
		}
		$logpathd = LOGS_PATH . 'sunpay/notify/';
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		file_put_contents($logpathd . "cash.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}
		$pdata = [
			'osn' => $params['merTransferId'],
			'out_osn' => $params['tradeNo'],
			'pay_status' => $params['tradeResult'] == '1' ? 9 : 3,
			'pay_msg' => $params['respCode'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
