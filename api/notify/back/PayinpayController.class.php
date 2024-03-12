<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class PayinpayController extends BaseController
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
		$logpathd = LOGS_PATH . 'payinpay/notify/';
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}


		$sign = paySign($params);
		file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['returncode'] == '00' ? 1 : -1,
			'osn' => $params['orderid'],
			'amount' =>  $params['amount'],
			'successStr' => 'OK'
		];

		$this->payAct($pdata);
	}


	public function _bkcash()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_GET;
		}
		if (!$params) {
			$params = $_POST;
		}
		$pdata = [
			'out_trade_no' => $params['id'],
		];
		$this->curc_post('https://payin.pro/Payment_SeriPay_df2callback.html', $pdata);
	}
	protected function curc_post($url, $data)
	{

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}

	public function _cash()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$logpathd = LOGS_PATH . 'payinpay/notify/';
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		file_put_contents($logpathd . "cash.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}
		$pdata = [
			'osn' => $params['orderId'],
			'out_osn' => $params['remarks'],
			'pay_status' => $params['status'] == 'processed' ? 9 : 3,
			'pay_msg' => $params['remarks'],
			//'amount' => $params['order_amount'],
			'successStr' => 'OK',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
