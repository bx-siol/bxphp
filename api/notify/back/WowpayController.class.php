<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class WowpayController extends BaseController
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
		$fliepath =	LOGS_PATH . 'wowpay/notify/pay' . date("Y-m-d", time()) . '.txt';
		file_put_contents($fliepath, NOW_DATE . "\r\n" . var_export($params, true) . "\r\n\r\n", FILE_APPEND);

		$sign = paySign($params);
		if ($params['sign'] != $sign) {
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
	public function _payAuto()
	{
		// $user = checkLogin();
		// if (!$user) {
		// 	jReturn(-1, '提交失败，请先打开后台登录后提交');
		// }
		// if ($user['gid'] == 92) {
		// 	jReturn(-1, 'error');
		// }
		$osn =  $_POST["osn"] ?? $_GET["osn"];
		$order = Db::table('fin_paylog')->where("osn='{$osn}'")->find();
		$fliepath =	LOGS_PATH . 'notify/_payAuto' . date("Y-m-d", time()) . '.txt';
		// file_put_contents($fliepath, NOW_DATE . "\r\n" . $user['id'] . " ===== " . $osn . "\r\n", FILE_APPEND);
		$pdata = [
			'code' => 1,
			'osn' => $order['osn'],
			'amount' => $order['money'],
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
		$log_file = LOGS_PATH . 'wowpay/notify/cash' . date("Y-m-d", time()) . '.txt';
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
