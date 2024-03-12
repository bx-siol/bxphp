<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class Mx22613Controller extends BaseController
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
		file_put_contents(LOGS_PATH . 'payNotify' . CONTROLLER_NAME . '.txt', NOW_DATE . "\r\n" . var_export($params, true) . "\r\n\r\n", FILE_APPEND);

		$sign = paySign($params);
		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['status'] == '2' ? 1 : -1,
			'osn' => $params['idNumber'],
			'amount' => intval($params['amount']) / 100,
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
		$log_file = LOGS_PATH . 'dfNotify' . ucfirst($code) . '.txt';
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
			'osn' => $params['mchOrderNo'],
			'out_osn' => $params['agentpayOrderId'],
			'pay_status' => $params['status'] == '2' ? 9 : 3,
			'pay_msg' => $params['transMsg'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
