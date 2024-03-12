<?php
!defined('ROOT_PATH') && exit;

use think\facade\Db;

class WinpayController extends BaseController
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
		$params['notifyUrl'] = urldecode($params['notifyUrl']);
		//$params['page_url'] = urldecode($params['page_url']);

		$url = 'https://payment.winpay.biz/api/pay/pay';
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
		$params['notify_url'] = urldecode($params['notify_url']);
		$url = 'https://payment.winpay.biz/api/payout/pay';
		$result =  $this->CurlPost($url, $params, 30);
		$json_str = json_encode($result, 256);
		echo $json_str;
	}
	public function _pay()
	{
		require_once APP_PATH . 'common/pay/' . strtolower(CONTROLLER_NAME) . '.php';
		$time = date("Y-m-d", time());
		$logpathd = LOGS_PATH . "winpay/notify/pay{$time}.txt";

		$jsonStr = trim(file_get_contents('php://input'));
		$params = json_decode($jsonStr, true);
		if (!$params) {
			$params = $_POST;
		}

		$sign = paySign($params);
		file_put_contents($logpathd, NOW_DATE .  PHP_EOL  . $sign .  PHP_EOL  . json_encode($params) .  PHP_EOL .  PHP_EOL, FILE_APPEND);

		if ($params['sign'] != $sign) {
			file_put_contents($logpathd, NOW_DATE .  PHP_EOL  .  'singree' .  PHP_EOL .  PHP_EOL, FILE_APPEND);
			jReturn(-1, 'Sign error');
		}

		$pdata = [
			'code' => $params['state'] == '2' ? 1 : -1,
			'osn' => $params['mchOrderNo'],
			'amount' =>  floatval($params['amount']) / 100,
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
		$logpathd = LOGS_PATH . "winpay/notify/cash{$time}.txt";
		$code = strtolower(CONTROLLER_NAME);
		require_once APP_PATH . 'common/cash/' . $code . '.php';


		$signFunc = $code . 'CashSign';
		if (!function_exists($signFunc)) {
			jReturn(-1, 'Sign func no exist');
		}
		$sign = $signFunc($params);

		file_put_contents($logpathd, NOW_DATE .  PHP_EOL  . $sign .  PHP_EOL  . json_encode($params) .  PHP_EOL .  PHP_EOL, FILE_APPEND);

		if ($params['sign'] != $sign) {
			jReturn(-1, 'Sign error');
		}

		//$order=Db::table('fin_cashlog')->where("osn='{$params['merchantCode']}'")->find();

		$pdata = [
			'osn' => $params['mchOrderNo'],
			'out_osn' => $params ['transferId'],
			'pay_status' => $params ['state'] == '2' ? 9 : 3,
			'pay_msg' => $params['msg'],
			//'amount' => $params['order_amount'],
			'successStr' => 'success',
			'failStr' => 'no'
		];
		$this->cashAct($pdata);
	}
}
