<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class QuickpayController extends BaseController
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
		$logpathd = LOGS_PATH . 'quickpay/notify/';
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}


		$sign = paySign($params);
		file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sys_sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sys_sign'] != $sign) {
			file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			ReturnToJson(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['status'] == 'PAYED' ? 1 : -1,
			'osn' => $params['outTradeSn'],
			'amount' =>  $params['payedAmount'] / 100,
			'successStr' => 'SUCCESS'
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
		$logpathd = LOGS_PATH . 'quickpay/notify/';
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		file_put_contents($logpathd . "cash.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sys_sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sys_sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}
		$pdata = [
			'osn' => $params['outTradeSn'],
			'out_osn' => $params['tradeSn'],
			'pay_status' => $params['status'] == 'SUCCEED' ? 9 : 3,
			'pay_msg' => $params['tradeSn'],
			'successStr' => 'SUCCESS',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
