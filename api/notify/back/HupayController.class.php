<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class HupayController extends BaseController
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
		$params['notify_url'] = urldecode($params['notify_url']);

		$url = 'https://api.huilianpays.com/create_order';
		$result = $this->CurlPost($url, $params, 30);
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
		$params['notify_url'] = urldecode($params['notify_url']);
		$url = 'https://api.huilianpays.com/Payout';
		$result = $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		echo $json_str;
	}
	public function _pay()
	{
		$logpathd = LOGS_PATH . 'hupay/notify/';
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}


		$sign = paySign($params);
		file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . json_encode($params), FILE_APPEND);
		if ($params['sign'] != $sign) {
			file_put_contents($logpathd . "pay.txt", NOW_DATE . "\r\n" . 'singree' . "\r\n", FILE_APPEND);
			ReturnToJson(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['result'] == '1' ? 1 : -1,
			'osn' => $params['mch_order_no'],
			'amount' => $params['trade_amount'],
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
		$logpathd = LOGS_PATH . 'hupay/notify/';
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';
		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			ReturnToJson(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);
		file_put_contents($logpathd . "cash.txt", NOW_DATE . "\r\n" . $sign . "\r\n" . $params['sign'] . "\r\n" . var_export($params, true), FILE_APPEND);
		if ($params['sign'] != $sign) {
			ReturnToJson(-1, 'Sign error');
		}
		$pdata = [
			'osn' => $params['out_trade_no'],
			'out_osn' => $params['out_trade_no'],
			'pay_status' => $params['status'] == '1' ? 9 : 3,
			'pay_msg' => $params['real_money'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}


/*
mch_id: 24172359
notify_url: http://www.gamedreamer.in/api/index.php?m=Notify&c=hupay&a=pay
currency: INR
mch_order_no: ae485fddbfa0899d
order_date: 2023-05-02 03:00:55
goods_name: game pay100
sign_type:MD5
phone:8670947883
email:8670947883@gmail.com
mch_user_id:46f2c33b7edaadff279b9ff3136feb8d
pay_type:102
trade_amount: 100
sign:401687825a38ae7cd4cabd358e2d237a


*/