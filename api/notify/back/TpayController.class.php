<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class TpayController extends BaseController
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
		$flpath = LOGS_PATH . 'tpay/notify/pay' . date("Y-m-d", time()) . '.txt';

		$sign = paySign($params);
		file_put_contents($flpath, NOW_DATE . "\r\n" . $sign . "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES)  . "\r\n\r\n", FILE_APPEND);
		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['status'] == 'PAY_SUCCESS' ? 1 : -1,
			'osn' => $params['orderId'],
			'amount' =>  $params['amount'],
			'successStr' => 'ok'
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
		$log_file = LOGS_PATH . 'tpay/notify/cash' . date("Y-m-d", time()) . '.txt';
		file_put_contents($log_file, NOW_DATE . "\r\n" . var_export($params, true) . "\r\n\r\n", FILE_APPEND);

		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		//$order=Db::table('fin_cashlog')->where("osn='{$params['merchantCode']}'")->find();

		$pdata = [
			'osn' => $params['orderId'],
			'out_osn' => $params['platOrderId'],
			'pay_status' => $params['status'] == 'PAY_SUCCESS' ? 9 : 3,
			'pay_msg' => $params['msg'],
			//'amount' => $params['order_amount'],
			'successStr' => 'ok',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
