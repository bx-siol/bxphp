<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class JfpayController extends BaseController
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
		$time = date("Y-m-d H:i:s", time());
		$logpathd = LOGS_PATH . "jfpay/notify/pay{$time}.txt";

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		if (!$params) {
			$params = $_GET;
		}
		$sign = paySign($params);

		file_put_contents($logpathd, "\r\n" . $sign . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);

		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['code'] == '0000' ? 1 : -1,
			'osn' => $params['outtradeno'],
			'amount' => $params['fee'] / 100,
			'successStr' => '0000'
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
		$time = date("Y-m-d H:i:s", time());
		$logpathd = LOGS_PATH . "jfpay/notify/cash{$time}.txt";

		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';


		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		file_put_contents($logpathd, "\r\n" . $sign . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);

		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'osn' => $params['outtradeno'],
			'out_osn' => $params['outtradeno'],
			'pay_status' => $params['status'] == '9' ? 9 : 3,
			'pay_msg' => $params['errtxt'],
			//'amount' => $params['order_amount'],
			'successStr' => '0000',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
