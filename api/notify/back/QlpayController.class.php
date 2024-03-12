<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class QlpayController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function _index()
	{
		echo 'notify';
	}
	public function _cpay()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$params['callback_url'] = urldecode($params['callback_url']);
		// $logpathd = LOGS_PATH . "qq.txt";
		// file_put_contents($logpathd,  "\r\n" . json_encode($params, JSON_UNESCAPED_SLASHES)  . "\r\n\r\n ===============", FILE_APPEND);

		$url = 'https://gw.kirinpayment.net/pay/unifiedorder?format=json';
		$result =  $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		echo $json_str;
	}
	public function _ccash()
	{
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		$params['callback'] = urldecode($params['callback']);
		$url = 'https://gw.kirinpayment.net/mch/withdrawin';
		$result =  $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		echo $json_str;
	}


	public function _pay()
	{
		$logpathd = LOGS_PATH . 'qlpay/notify/';
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';
		$time = date("Y-m-d", time());
		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}
		//upi
		$sign = paySign($params);
		file_put_contents($logpathd . "pay{$time}.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . json_encode($params), FILE_APPEND);
		if ($params['sign'] != $sign) {
			file_put_contents($logpathd . "pay{$time}.txt", NOW_DATE . "\r\n" .  'singree' . "\r\n", FILE_APPEND);
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['callbacks'] == 'CODE_SUCCESS' ? 1 : -1,
			'osn' => $params['out_trade_no'],
			'amount' =>  $params['amount'],
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
		$logpathd = LOGS_PATH . 'qlpay/notify/';
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		file_put_contents($logpathd . "cash{$time}.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}
		$pdata = [
			'osn' => $params['out_trade_no'],
			'out_osn' => $params['order_no'],
			'pay_status' => $params['code'] == 'CODE_FINISHED' ? 9 : 3,
			'pay_msg' => $params['err_msg'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
