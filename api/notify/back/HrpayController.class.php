<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class HrpayController extends BaseController
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
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . 'hrpay/notify/' . "pay" . $time . ".txt";
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		file_put_contents($logpathd,  "\r\n" . NOW_DATE . "\r\n" . $jsonStr . "\r\n", FILE_APPEND);
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}


		$sign = paySign($params);
		file_put_contents($logpathd,  "\r\n" . NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . json_encode($params), FILE_APPEND);
		if ($params['sign'] != $sign) {
			file_put_contents($logpathd,  "\r\n" . NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			ReturnToJson(-1, 'Sign error');
		}

		$params['params'] = json_decode($params['params'], true);

		$pdata = [
			'code' => $params['params']['status'] == '1' ? 1 : -1,
			'osn' => $params['params']['merchant_ref'],
			'amount' =>  $params['params']['pay_amount'],
			'successStr' => 'SUCCESS'
		];

		$this->payAct($pdata);
	}
	public function _cash1()
	{
		$time = date("Y-m-d", time());
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$logpathd = LOGS_PATH . 'hrpay/notify/' . "cash" . $time . ".txt";
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}


		$sign = $signFunc($params);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . json_encode($params), FILE_APPEND);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}

		$params['params'] = json_decode($params['params'], true);

		$pdata = [
			'osn' => $params['params']['merchant_ref'],
			'out_osn' => $params['params']['system_ref'],
			'pay_status' => $params['params']['status'] == '1' ? 9 : 3,
			'pay_msg' =>  $params['params']['pay_amount'],
			//'amount' => $params['order_amount'],
			'successStr' => 'SUCCESS',
			'failStr' => 'no',
			'type' => 'hrpay'
		];
		file_put_contents($logpathd, NOW_DATE . "\r\n" .  json_encode($pdata), FILE_APPEND);
		$this->cashAct1($pdata);
	}
	public function _cash()
	{
		$time = date("Y-m-d", time());
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$logpathd = LOGS_PATH . 'hrpay/notify/' . "cash" . $time . ".txt";
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}


		$sign = $signFunc($params);
		file_put_contents($logpathd, NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . json_encode($params), FILE_APPEND);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}

		$params['params'] = json_decode($params['params'], true);

		$pdata = [
			'osn' => $params['params']['merchant_ref'],
			'out_osn' => $params['params']['system_ref'],
			'pay_status' => $params['params']['status'] == '1' ? 9 : 3,
			'pay_msg' =>  $params['params']['pay_amount'],
			//'amount' => $params['order_amount'],
			'successStr' => 'SUCCESS',
			'failStr' => 'no',
			'type' => 'hrpay'
		];
		file_put_contents($logpathd, NOW_DATE . "\r\n" .  json_encode($pdata), FILE_APPEND);
		$this->cashAct($pdata);
	}
}
