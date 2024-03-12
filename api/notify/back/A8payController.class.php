<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class A8payController extends BaseController
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
		$logpathd = LOGS_PATH . 'a8pay/notify/p' . $time;
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$sign = dpay($params);
		file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if (!$sign) {
			//file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . 'singree' . "\r\n", FILE_APPEND);
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['status'] == 'success' ? 1 : -1,
			'osn' => $params['merchantOrderNo'],
			'amount' => $params['payAmount'],
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
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . 'a8pay/notify/c' . $time;
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$sign = dpay($params);
		file_put_contents($logpathd . "cash.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if (!$sign) {
			jReturn(-1, 'Sign error');
		}
		$pdata = [
			'osn' => $params['merchantOrderNo'],
			'out_osn' => $params['orderNo'],
			'pay_status' => $params['status'] == 'success' ? 9 : 3,
			'pay_msg' => $params['message'],
			//'amount' => $params['order_amount'],
			'successStr' => 'SUCCESS',
			'failStr' => 'SUCCESS'
		];
		$this->cashAct($pdata);
	}
}