<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class KapayController extends BaseController
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
		$logpathd = LOGS_PATH . "kapay/notify/pay{$time}.txt";

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}

		$sign = paySign($params);

		file_put_contents($logpathd, "\r\n" . $sign . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\r\n\r\n", FILE_APPEND);

		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['status'] == '1' ? 1 : -1,
			'osn' => $params['merchantOrderId'],
			'amount' => $params['amount'],
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
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . "kapay/notify/cash{$time}.txt";
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
		if (in_array($params['status'], ['6']))
			$params['tradeResult'] = 1;
		else if (in_array($params['status'], ['4', '7', '8', '9']))
			$params['tradeResult'] = 0;
		else {
			echo 'received';
			exit;
		}
		$pdata = [
			'osn' => $params['shanghu_order'],
			'out_osn' => $params['order_num'],
			'pay_status' => $params['tradeResult'] == '1' ? 9 : 3,
			'pay_msg' => $params['respCode'],
			'successStr' => 'received',
			'failStr' => 'received'
		];
		$this->cashAct($pdata);
	}
}