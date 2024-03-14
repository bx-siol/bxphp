<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class StarspayController extends BaseController
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
		$time = date("Y-m-d", time());
		$filepath = LOGS_PATH . 'starspay/notify/payNotify' . $time . '.txt';

		// merchant_no + params + sign_type + timestamp + Key  str_replace(" ","",())
		$sign = paySign($params['merchant_no'] . $params['params']  . $params['sign_type'] . $params['timestamp']);
		file_put_contents($filepath, NOW_DATE . "\r\n" . json_encode($params) . "\r\n" . $sign . "\r\n\r\n", FILE_APPEND);

		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}
		$p1 = json_decode($params['params'], true);
		// var_export($p1);
		$pdata = [
			'code' => $p1['status'] == 1 ? 1 : -1,
			'osn' => $p1['merchant_ref'],
			'amount' =>  $p1['amount'],
			'successStr' => 'SUCCESS',
			'failStr' => 'fail'
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
			ReturnToJson(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
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
