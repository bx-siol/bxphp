<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class YopayController extends BaseController
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
		$log_file = LOGS_PATH .  strtolower(CONTROLLER_NAME) . '/notify//pay' . CONTROLLER_NAME . '.txt';
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}

		$ooc = json_decode(stripslashes($params['data']), true);
		$sign = paySign([$ooc['orderid'], $ooc['payamount']]);
		file_put_contents($log_file, NOW_DATE . "\r\n" . var_export($params, true) . "\r\n" . $sign . "\r\n", FILE_APPEND);
		if ($ooc['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}

		$pdata = [
			'code' => $ooc['ispay'] == '1' ? 1 : -1,
			'osn' => $ooc['orderid'],
			'amount' =>  $ooc['payamount'],
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
			ReturnToJson(-1, 'Sign func no exist');
		}

		$ooc = json_decode(stripslashes($params['data']), true);
		$sign = $signFunc([$ooc['orderid'], $ooc['amount']]);
		file_put_contents($log_file, NOW_DATE . "\r\n" . var_export($params, true) . "\r\n" . $sign . "\r\n", FILE_APPEND);
		if ($ooc['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}


		$pdata = [
			'osn' => $ooc['orderid'],
			'out_osn' => $ooc['ticket'],
			'pay_status' => $ooc['ispay'] == '1' ? 9 : 3,
			'pay_msg' => $params['msg'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
